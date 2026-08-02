# Client Subscription Order Flow Implementation Plan

**For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans to implement plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let client choose a plan (Trial/Business/Enterprise) from dashboard + subscription page for their tenant, order/create subscription, and get an invoice. Existing active subscription → switch plan with proration (pay now) + renew in new plan at next cycle.

**Architecture:** Reuse existing `Plan` model (seeded Trial/Business/Enterprise), `BillingService::calculateProration()`/`upgrade()`, and `TenantRequestController::store()` pattern (creates tenant+subscription+invoice). New client-facing plan-picker UI replaces the raw-number upgrade modal. Register flow plan preselect wired.

**Tech Stack:** Laravel 12, Inertia Vue 3, existing BillingService

---

## Decisions (confirmed with user)
- **Prorata sekarang + renew**: switch plan → pay prorated amount now, renew in new plan at next cycle
- **Auto ke tenant tunggal**: if client has 1 tenant, use it without selection
- **Entry point**: Dashboard "Paket" card + Langganan menu
- **Tenant tanpa sub**: auto-create subscription (pending) + invoice; after pay, admin approve (existing flow)
- **Plan card + resort**: plan selection via plan cards (Trial/Business/Enterprise), then resort count

## Existing Gaps Found
1. Upgrade flow bypasses Plan model — takes raw max_resorts/price_per_resort, no plan switch
2. Dashboard hardcoded prices (starter/premium) mismatch seeded plans (Trial/Business/Enterprise)
3. `/register?plan=` param dead — AuthController ignores it
4. No "choose plan" in subscription upgrade UI — `plans` prop unused
5. `plan` column denormalized string; `plan_id` optional

## File Map

| File | Action | Responsibility |
|---|---|---|
| `app/Http/Controllers/Client/SubscriptionController.php` | Modify | `order()` — create sub+invoice for tenant w/o sub; `upgrade()` — accept plan_id, switch plan |
| `app/Http/Controllers/Client/DashboardController.php` | Modify | Pass plans to dashboard |
| `app/Http/Controllers/AuthController.php` | Modify | Read `?plan=` param, preselect plan |
| `resources/js/Pages/Client/Dashboard.vue` | Modify | Paket card → link to plan picker, dynamic price from plans |
| `resources/js/Pages/Client/Subscription.vue` | Modify | Plan card UI replacing raw-number modal |
| `resources/js/Pages/Client/RequestTenant.vue` | Modify | Reuse plan picker (already exists) — no change if reuse component |
| `resources/js/Components/PlanPicker.vue` | Create | Reusable plan selection component |
| `routes/web.php` | Modify | Add `POST /client/subscription/order` |

---

### Task 1: Create reusable PlanPicker component

**Files:**
- Create: `resources/js/Components/PlanPicker.vue`

- [ ] **Step 1: Create component**

Plan cards (Trial/Business/Enterprise) from `plans` prop, showing name, price (from `pricing_config`), features. Emits `select` with plan. Reused by Subscription page and Dashboard.

Props: `plans: any[]`, `selectedPlanId?: string`
Emit: `select(plan)`

```vue
<script setup lang="ts">
defineProps<{ plans: any[]; selectedPlanId?: string }>();
const emit = defineEmits<{ (e: 'select', plan: any): void }>();
</script>
<template>
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <button v-for="p in plans" :key="p.id" @click="emit('select', p)"
      class="p-5 rounded-xl border-2 text-left transition cursor-pointer"
      :class="selectedPlanId === p.id ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/10' : 'border-neutral-200 dark:border-neutral-700 hover:border-emerald-300'">
      <h3 class="font-bold">{{ p.name }}</h3>
      <p class="text-lg font-bold mt-1">{{ formatPrice(p) }}</p>
      <p class="text-xs text-neutral-500 mt-1">{{ p.max_resorts }} resort</p>
      <ul class="mt-3 text-xs space-y-1">
        <li v-for="f in p.features" :key="f.id">• {{ f.name }}</li>
      </ul>
    </button>
  </div>
</template>
```

- [ ] **Step 2: Build**
```bash
npm run build
```

---

### Task 2: SubscriptionController — order + plan-based upgrade

**Files:**
- Modify: `app/Http/Controllers/Client/SubscriptionController.php`

- [ ] **Step 1: Add `order()` method** — auto-create sub+invoice for tenant w/o subscription

```php
public function order(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'plan_id' => 'required|exists:plans,id',
        'tenant_id' => 'required|exists:tenants,id',
        'resort_qty' => 'required|integer|min:1',
        'billing_cycle' => 'required|in:monthly,quarterly,semiannual,yearly',
    ]);

    $tenant = Tenant::where('id', $validated['tenant_id'])
        ->where('requested_by', auth()->id())
        ->firstOrFail();

    // Tenant already has subscription → redirect to upgrade
    if ($tenant->subscription) {
        return redirect()->route('client.subscription')
            ->with('error', 'Tenant sudah punya langganan. Gunakan Ganti Paket.');
    }

    $plan = Plan::findOrFail($validated['plan_id']);
    $billing = app(BillingService::class);

    $subscription = Subscription::create([
        'user_id' => auth()->id(),
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'plan' => $plan->name,
        'billing_cycle' => $validated['billing_cycle'],
        'max_resorts' => $validated['resort_qty'],
        'price_per_resort' => $plan->pricing_config['price_per_resort'] ?? ($plan->price_per_month / max(1, $plan->max_resorts)),
        'status' => 'pending',
        'trial_ends_at' => $plan->type === 'trial' ? now()->addDays($plan->trial_days) : null,
    ]);

    $invoice = $billing->generateInvoice($subscription, null, true);

    return redirect()->route('client.invoices')
        ->with('success', 'Tagihan dibuat. Selesaikan pembayaran untuk aktivasi.');
}
```

- [ ] **Step 2: Update `upgrade()` to accept plan_id**

Current upgrade() takes raw `max_resorts`/`price_per_resort`. Add plan switch: if `plan_id` present and differs from current plan, update `plan_id` + `plan` name. Keep proration via existing `calculateProration()`.

Validate `plan_id` nullable; when present, derive `max_resorts`/`price_per_resort` from plan's pricing_config (unless resort_qty provided).

- [ ] **Step 3: Verify**
```bash
php -l app/Http/Controllers/Client/SubscriptionController.php
```

- [ ] **Step 4: Commit** (manual by user)

---

### Task 3: DashboardController — pass plans

**Files:**
- Modify: `app/Http/Controllers/Client/DashboardController.php`

- [ ] **Step 1: Add plans to dashboard payload**
```php
'plans' => \App\Models\Plan::with('features')->active()->get(),
```

---

### Task 4: AuthController — read `?plan=` param

**Files:**
- Modify: `app/Http/Controllers/AuthController.php`

- [ ] **Step 1: Register redirects with plan** — after register, if `plan` query param present, pass it to client via session or query on redirect to dashboard. Simplest: after register redirect to `/client/subscription?plan={id}` if plan param present.

---

### Task 5: Dashboard.vue — dynamic plan + link to picker

**Files:**
- Modify: `resources/js/Pages/Client/Dashboard.vue`

- [ ] **Step 1: Replace hardcoded priceLabel with dynamic from plans prop**

```js
const planLabel = computed(() => {
    const plan = props.plans?.find(p => p.name.toLowerCase() === props.subscription?.plan?.toLowerCase());
    return plan?.name ?? props.subscription?.plan ?? '-';
});
const priceLabel = computed(() => {
    const plan = props.plans?.find(p => p.name.toLowerCase() === props.subscription?.plan?.toLowerCase());
    if (!plan) return '-';
    const cfg = plan.pricing_config;
    return cfg?.price_per_resort ? `Rp${Number(cfg.price_per_resort).toLocaleString('id-ID')}/resort` : plan.price_per_month ? `Rp${Number(plan.price_per_month).toLocaleString('id-ID')}/bln` : 'Custom';
});
```

- [ ] **Step 2: Paket card** — add a "Pilih/Ganti Paket" button linking to plan picker (Subscription page with plan modal open, or new route).

---

### Task 6: Subscription.vue — plan card UI

**Files:**
- Modify: `resources/js/Pages/Client/Subscription.vue`

- [ ] **Step 1: Replace raw-number upgrade modal** with PlanPicker + resort count + cycle. When plan selected:
- Same plan → resort count change → upgrade() with max_resorts
- Different plan → upgrade() with plan_id + resort_qty → proration

- [ ] **Step 2: Add "Pilih Paket" button** for tenants without subscription → order()

---

### Task 7: Routes + verify

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Add route**
```php
Route::post('/client/subscription/order', [\App\Http\Controllers\Client\SubscriptionController::class, 'order'])->name('subscription.order');
```

- [ ] **Step 2: End-to-end verify**
```bash
# 1. Login as client with tenant, no subscription
# 2. Dashboard → Paket card → "Pilih Paket"
# 3. Select Business, resort 5, monthly → POST order
# 4. Invoice created pending → pay
# 5. Tenant active
# 6. Existing subscription → Ganti Paket → select Enterprise → proration invoice
```

---

## Rollback Plan
```bash
# git revert commits; no DB migration (plans already seeded)
```

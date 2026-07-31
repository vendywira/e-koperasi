# Subscription Grace Period Implementation Plan

**For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans to implement plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Show expired-but-in-grace-period subscriptions as "Masa Tenggang" (amber warning) in client UI, without a new DB status. Grace is **derived** from `expired` status + `ends_at` + `grace_period_days`. Differentiate first-time tenant provisioning (needs admin approval) from renewal (immediate active + extend).

**Architecture:** No migration, no new status. Subscription stays `expired`. A helper computes whether it's still within grace window: `expired && ends_at + grace_days > now()`. Scheduler `tenant:auto-suspend` unchanged — it already suspends only after grace elapses. `confirmPayment()` renews `expired` subscriptions immediately (no provisioning). Dashboard + Subscription pages show amber "Masa Tenggang" derived from dates.

**Tech Stack:** Laravel 12, Inertia Vue 3, existing scheduler patterns

---

## Decisions (confirmed with user)
- **Grace = derived** from `expired` + `ends_at` + `grace_period_days` (no new status)
- **During grace:** tenant stays usable, UI shows warning
- **Renewal in grace:** pays → immediately `active` + extend `ends_at`, no approval
- **First-time tenant:** request → invoice → pay → tenant `pending` → admin approve → provision → `active` (existing flow, unchanged)
- **Seeder:** add expired-in-grace + expired-past-grace example data

## File Map

| File | Action | Responsibility |
|---|---|---|
| `app/Models/Subscription.php` | Modify | Add `isGrace()` (derived), `graceDaysRemaining()`, `graceEndsAt()` |
| `app/Services/BillingService.php` | Modify | `confirmPayment()` renew expired subscription immediately + reactivate suspended tenant |
| `app/Http/Controllers/Client/DashboardController.php` | Modify | Expose `is_grace`, `grace_days_remaining`, `grace_ends_at` |
| `app/Http/Controllers/Client/SubscriptionController.php` | Modify | Expose same grace fields |
| `resources/js/Pages/Client/Dashboard.vue` | Modify | Amber "Masa Tenggang" badge + banner |
| `resources/js/Pages/Client/Subscription.vue` | Modify | Amber grace status display |
| `database/seeders/ClientSeeder.php` | Modify | Add expired-in-grace + expired-past-grace examples |

---

### Task 1: Subscription model — derived grace helpers

**Files:**
- Modify: `app/Models/Subscription.php`

- [ ] **Step 1: Add `isGrace()`, `graceDaysRemaining()`, `graceEndsAt()`**

```php
public function isGrace(): bool
{
    return $this->status === self::STATUS_EXPIRED
        && $this->ends_at !== null
        && $this->graceEndsAt()->isFuture();
}

public function graceDaysRemaining(): int
{
    if (!$this->ends_at) return 0;
    return max(0, now()->diffInDays($this->graceEndsAt(), false));
}

public function graceEndsAt(): \Carbon\CarbonInterface
{
    $graceDays = (int) ($this->grace_period_days ?? 7);
    return $this->ends_at->copy()->addDays($graceDays);
}
```

Also add status constants:
```php
public const STATUS_ACTIVE = 'active';
public const STATUS_EXPIRED = 'expired';
public const STATUS_CANCELLED = 'cancelled';
public const STATUS_TRIALING = 'trialing';
```

- [ ] **Step 2: Verify**
```bash
php -l app/Models/Subscription.php
```
Expected: no syntax errors.

- [ ] **Step 3: Commit**
```bash
git add app/Models/Subscription.php
git commit -m "feat: derived grace helpers on Subscription (no new status)"
```

---

### Task 2: BillingService — renew expired subscription immediately

**Files:**
- Modify: `app/Services/BillingService.php`

- [ ] **Step 1: confirmPayment — log previous status**

In `confirmPayment()` (line ~258), before DB transaction capture:
```php
$wasExpired = $subscription && in_array($subscription->status, [
    Subscription::STATUS_EXPIRED, Subscription::STATUS_CANCELLED,
]);
```

After `$subscription->update(['status' => 'active', ...])`, the existing tenant reactivation (line ~283) handles `suspended`. Renewal is immediate — no provisioning call. The existing stacking-base logic already extends `ends_at` from current value.

- [ ] **Step 2: Verify**
```bash
php -l app/Services/BillingService.php
```
Expected: no syntax errors.

- [ ] **Step 3: Commit**
```bash
git add app/Services/BillingService.php
git commit -m "feat: confirmPayment renews expired subscription immediately"
```

---

### Task 3: DashboardController + SubscriptionController — expose grace fields

**Files:**
- Modify: `app/Http/Controllers/Client/DashboardController.php`
- Modify: `app/Http/Controllers/Client/SubscriptionController.php`

- [ ] **Step 1: DashboardController — add grace fields to subscription payload**

In the subscription map (line ~77), add:
```php
'is_grace' => $subscription->isGrace(),
'grace_days_remaining' => $subscription->graceDaysRemaining(),
'grace_ends_at' => $subscription->isGrace()
    ? $subscription->graceEndsAt()->format('d M Y')
    : null,
```

- [ ] **Step 2: SubscriptionController show() — same fields**

Mirror the same 3 fields in `SubscriptionController::show()`.

- [ ] **Step 3: Verify**
```bash
php -l app/Http/Controllers/Client/DashboardController.php
php -l app/Http/Controllers/Client/SubscriptionController.php
```
Expected: no syntax errors.

- [ ] **Step 4: Commit**
```bash
git add app/Http/Controllers/Client/DashboardController.php app/Http/Controllers/Client/SubscriptionController.php
git commit -m "feat: expose derived grace fields to client UI"
```

---

### Task 4: Dashboard.vue — amber Masa Tenggang badge + banner

**Files:**
- Modify: `resources/js/Pages/Client/Dashboard.vue`

- [ ] **Step 1: Update statusColor + statusLabel computeds**

```js
const statusColor = computed(() => {
    if (!props.subscription) return 'bg-neutral-400';
    if (props.subscription.is_grace) return 'bg-amber-500';
    return props.subscription.is_active ? 'bg-emerald-500' : 'bg-red-500';
});

const statusLabel = computed(() => {
    if (!props.subscription) return 'Belum Aktif';
    if (props.subscription.is_grace) return 'Masa Tenggang';
    return props.subscription.is_active ? 'Aktif' : 'Tidak Aktif';
});
```

- [ ] **Step 2: Add grace banner** below subscription card header when `is_grace`:

```html
<div v-if="subscription?.is_grace" class="mt-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">⚠️ Masa Tenggang</p>
    <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">
        Langganan sudah lewat masa aktif. Bayar segera sebelum {{ subscription.grace_ends_at }} — tersisa {{ subscription.grace_days_remaining }} hari.
    </p>
    <Link href="/client/invoices" class="mt-2 inline-block text-xs text-amber-700 dark:text-amber-300 underline font-medium">Bayar Sekarang →</Link>
</div>
```

- [ ] **Step 3: Verify build**
```bash
npm run build
```
Expected: success.

- [ ] **Step 4: Commit**
```bash
git add resources/js/Pages/Client/Dashboard.vue
git commit -m "feat: amber grace period badge + banner on client dashboard"
```

---

### Task 5: Subscription.vue — grace status display

**Files:**
- Modify: `resources/js/Pages/Client/Subscription.vue`

- [ ] **Step 1: Read current status display** — find where subscription status rendered, add grace handling:

```js
const label = sub.is_grace ? 'Masa Tenggang' : sub.is_active ? 'Aktif' : sub.status;
```

- [ ] **Step 2: Add grace banner** mirroring Dashboard's.

- [ ] **Step 3: Verify build**
```bash
npm run build
```

- [ ] **Step 4: Commit**
```bash
git add resources/js/Pages/Client/Subscription.vue
git commit -m "feat: show grace status on subscription page"
```

---

### Task 6: ClientSeeder — add expired-in-grace + expired-past-grace examples

**Files:**
- Modify: `database/seeders/ClientSeeder.php`

- [ ] **Step 1: Add two new clients**

```php
[
    'name' => 'KSU Mitra Abadi',
    'email' => 'mitra.abadi@e-koperasi.com',
    'phone' => '081234567895',
    'plan' => 'premium',
    'status' => 'expired',     // expired but within grace: ends_at = now - 3 days
],
[
    'name' => 'Koperasi Buana Lestari',
    'email' => 'buana.lestari@e-koperasi.com',
    'phone' => '081234567896',
    'plan' => 'starter',
    'status' => 'expired',     // expired past grace: ends_at = now - 10 days
],
```

- [ ] **Step 2: Update endsAt logic** in the seeder loop — use email to differentiate:

```php
$endsAt = match (true) {
    str_contains($data['email'], 'mitra.abadi') => now()->subDays(3),   // in grace
    str_contains($data['email'], 'buana.lestari') => now()->subDays(10), // past grace
    $status === 'expired' => now()->subMonth(),
    default => now()->addMonths(6),
};
```

- [ ] **Step 3: Verify**
```bash
php artisan db:seed --class=ClientSeeder --force
```
Expected: two new clients created, one in grace (3 days past), one past grace (10 days past).

- [ ] **Step 4: Commit**
```bash
git add database/seeders/ClientSeeder.php
git commit -m "feat: seed expired-in-grace + expired-past-grace examples"
```

---

### Task 7: Verify full flow

- [ ] **Step 1: Seed**
```bash
php artisan db:seed --class=ClientSeeder --force
```

- [ ] **Step 2: Verify grace flag**
```bash
php artisan tinker --execute="
\$sub = \App\Models\Subscription::where('user_id', \App\Models\User::where('email','mitra.abadi@e-koperasi.com')->first()->id)->first();
echo 'grace: ' . (\$sub->isGrace() ? 'YES' : 'NO') . ' days_left: ' . \$sub->graceDaysRemaining() . ' ends: ' . \$sub->graceEndsAt();
"
```
Expected: `grace: YES`, days_left ~4, grace_ends_at = now+4 days.

- [ ] **Step 3: Login as mitra.abadi@e-koperasi.com** → `/client/dashboard`
Expected: amber "Masa Tenggang" badge + banner + link bayar.

- [ ] **Step 4: Simulate renewal payment**
```bash
php artisan tinker --execute="
\$sub = \App\Models\Subscription::where('user_id', \App\Models\User::where('email','mitra.abadi@e-koperasi.com')->first()->id)->first();
\$inv = \App\Models\Invoice::where('user_id', \$sub->user_id)->where('status','pending')->first();
if (\$inv) { app(\App\Services\BillingService::class)->confirmPayment(\$inv); }
echo \$sub->fresh()->status . ' / ends: ' . \$sub->fresh()->ends_at;
"
```
Expected: `active` + extended ends_at.

- [ ] **Step 5: Final commit**
```bash
git add -A
git commit -m "feat: complete derived grace period flow"
```

---

## Rollback Plan
```bash
# Nothing in DB changed (no migration, no status mutation)
# git revert the commits to remove UI/helpers
```

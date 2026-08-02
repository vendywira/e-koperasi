# Upgrade + Cycle di Satu Modal — Invoice Sinkron

**For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans to implement plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Gabung "Ganti Paket" + "Ubah Siklus" jadi satu modal. Saat submit, invoice pending **di-update in-place** dengan data terbaru (paket, resort, siklus). Subscription tetap "bayar dulu baru aktif" — update setelah invoice dibayar.

**Architecture:** `SubscriptionController@upgrade()` diterima plan_id + resort_qty + billing_cycle. Kalau ada invoice pending untuk tenant → update invoice tersebut (resort_count, price_per_resort, months, subtotal, total, items) BUKAN bikin baru. Subscription tidak berubah sampai `confirmPayment()`. `BillingService` dapat method `applyPlanChange()` yang update invoice in-place + catat target. Cycle tidak lagi method terpisah di modal (digabung).

**Tech Stack:** Laravel 12, existing BillingService

---

## Context

Sekarang:
- "Ganti Paket" dan "Ubah Siklus" tombol terpisah (2 modal)
- `upgrade()` bikin invoice prorata BARU, invoice lama (kalau pending) tidak diubah → data gak sinkron
- `changeCycle()` cuma update billing_cycle, invoice tidak disentuh

User mau: satu modal pilih paket + resort + siklus, submit → invoice pending di-update dengan data terbaru. Subscription aktif setelah bayar.

## Alur baru

1. Client buka modal "Ganti Paket" → pilih plan + resort count + billing cycle → submit
2. `upgrade()` validasi plan_id + resort_qty + billing_cycle
3. Kalau tenant punya invoice pending → `BillingService::applyPlanChange()` update invoice in-place:
   - `resort_count` = resort baru
   - `price_per_resort` = price plan baru
   - `months` = cycle months baru
   - `subtotal` / `total_amount` = resort × price × months (+ discount)
   - Update/replace invoice items (description "Langganan X — N resort × M bulan")
   - Set `due_date` sesuai
4. Subscription TIDAK berubah (masih aktif lama / pending)
5. Client bayar invoice → `confirmPayment()`:
   - Deteksi invoice updated → apply resort_count + price_per_resort + billing_cycle ke subscription
   - Renew ends_at pakai cycle baru

## File Map

| File | Action | Responsibility |
|---|---|---|
| `app/Services/BillingService.php` | Modify | `applyPlanChange()` update invoice in-place; `confirmPayment()` apply target + cycle |
| `app/Http/Controllers/Client/SubscriptionController.php` | Modify | `upgrade()` terima cycle, call applyPlanChange; hapus/ubah `changeCycle()` |
| `resources/js/Pages/Client/Plans.vue` | Modify | upgrade modal: tambah cycle selector, hapus tombol cycle terpisah |
| `resources/js/Pages/Client/Subscription.vue` | Modify | sama |

---

### Task 1: BillingService — applyPlanChange() update invoice in-place

**Files:**
- Modify: `app/Services/BillingService.php`

- [ ] **Step 1: Add applyPlanChange()**

```php
public function applyPlanChange(Subscription $subscription, int $newMaxResorts, float $newPricePerResort, string $newCycle): ?Invoice
{
    $tenant = Tenant::find($subscription->tenant_id);
    $cycle = BillingCycle::where('slug', $newCycle)->first();
    $cycleMonths = $cycle?->months ?? 1;
    $discountPct = $cycle?->discount_percent ?? 0;
    $subtotal = $newMaxResorts * $newPricePerResort * $cycleMonths;
    $discount = $subtotal * $discountPct / 100;
    $total = max(0, $subtotal - $discount);

    // Existing pending invoice → update in-place
    $invoice = Invoice::where('tenant_id', $tenant?->id)
        ->where('status', 'pending')
        ->latest()
        ->first();

    if ($invoice) {
        $invoice->update([
            'resort_count' => $newMaxResorts,
            'price_per_resort' => $newPricePerResort,
            'months' => $cycleMonths,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'total_amount' => $total,
            'due_date' => now()->addDays(14),
        ]);
        // Replace items
        $invoice->invoiceItems()->delete();
        $invoice->invoiceItems()->create([
            'description' => "Langganan {$tenant?->name} — {$newMaxResorts} resort × {$cycleMonths} bulan",
            'quantity' => 1,
            'unit_price' => $subtotal,
            'total_amount' => $subtotal,
            'type' => 'subscription',
        ]);
        if ($discount > 0) {
            $invoice->invoiceItems()->create([
                'description' => "Diskon siklus {$newCycle} ({$discountPct}%)",
                'quantity' => 1,
                'unit_price' => -$discount,
                'discount_amount' => $discount,
                'total_amount' => -$discount,
                'type' => 'discount',
            ]);
        }
        return $invoice;
    }

    // No pending invoice → generate new (existing generateInvoice)
    return $this->generateInvoice($subscription, null, true);
}
```

- [ ] **Step 2: Verify**
```bash
php -l app/Services/BillingService.php
```

---

### Task 2: BillingService — confirmPayment applies target + cycle

**Files:**
- Modify: `app/Services/BillingService.php`

- [ ] **Step 1: confirmPayment — apply resort/price/cycle from updated invoice**

In `confirmPayment()`, after proration check, add cycle update:

```php
// Apply plan change target (resort_count, price_per_resort, months→cycle)
if ($subscription) {
    $subscription->update([
        'max_resorts' => (int) $invoice->resort_count,
        'price_per_resort' => (float) $invoice->price_per_resort,
        'billing_cycle' => $this->cycleSlugForMonths((int) $invoice->months),
    ]);
}
```

Helper `cycleSlugForMonths`:
```php
private function cycleSlugForMonths(int $months): string
{
    return match ($months) {
        3 => 'quarterly',
        6 => 'semiannual',
        12 => 'yearly',
        default => 'monthly',
    };
}
```

Replace existing proration-target block (which only set max_resorts + price) with this fuller version.

- [ ] **Step 2: Verify**
```bash
php -l app/Services/BillingService.php
```

---

### Task 3: SubscriptionController — upgrade() terima cycle, hapus changeCycle dari modal

**Files:**
- Modify: `app/Http/Controllers/Client/SubscriptionController.php`

- [ ] **Step 1: upgrade() — tambah billing_cycle + call applyPlanChange**

```php
$validated = $request->validate([
    'subscription_id' => 'required|exists:subscriptions,id',
    'plan_id' => 'nullable|exists:plans,id',
    'max_resorts' => 'nullable|integer|min:1',
    'billing_cycle' => 'nullable|in:monthly,quarterly,semiannual,yearly',
]);
...
// After deriving newMax + newPrice (from plan or existing):
$newCycle = $validated['billing_cycle'] ?? $subscription->billing_cycle ?? 'monthly';

$billing->applyPlanChange($subscription, $newMax, $newPrice, $newCycle);

return redirect()->route('client.invoices')
    ->with('success', 'Perubahan paket menunggu pembayaran. Bayar invoice untuk mengaktifkan paket baru.');
```

Note: hapus `price_per_resort` dari validate (harga dari plan). `changeCycle()` bisa tetap ada buat route lama, atau hapus tombol di UI.

- [ ] **Step 2: Verify**
```bash
php -l app/Http/Controllers/Client/SubscriptionController.php
```

---

### Task 4: Frontend — gabung modal

**Files:**
- Modify: `resources/js/Pages/Client/Plans.vue`
- Modify: `resources/js/Pages/Client/Subscription.vue`

- [ ] **Step 1: Plans.vue upgrade form** — tambah cycle selector di form upgrade:

```html
<div>
    <label class="text-sm font-medium">Siklus Tagihan</label>
    <select v-model="upgradeForm.billing_cycle" ...>
        <option value="monthly">Bulanan</option>
        <option value="quarterly">3 Bulan</option>
        <option value="semiannual">6 Bulan</option>
        <option value="yearly">12 Bulan</option>
    </select>
</div>
```
Tambahkan `billing_cycle` ke `upgradeForm` (useForm). Hapus tombol "Ubah Siklus" terpisah.

- [ ] **Step 2: Subscription.vue** — sama: upgrade form tambah cycle, hapus tombol cycle.

- [ ] **Step 3: Build**
```bash
npm run build
```

---

### Task 5: Verify end-to-end

- [ ] **Step 1: Sub aktif dengan invoice pending lama**

Login client → sub dengan pending invoice.

- [ ] **Step 2: Ganti paket + cycle**
1. `/client/plans` → tenant → Business → resort 5 → cycle quarterly → submit
2. Expected: invoice pending SAMA (tidak bikin baru), tapi `resort_count=5`, `months=3`, `total` = 5×100k×3−discount
3. Verify:
```bash
php artisan tinker --execute="
\$inv = \App\Models\Invoice::where('status','pending')->latest()->first();
echo 'Invoice: ' . \$inv->invoice_number . ' resort=' . \$inv->resort_count . ' months=' . \$inv->months . ' total=' . \$inv->total_amount . PHP_EOL;
\$sub = \$inv->tenant?->subscription;
echo 'Sub (before pay): max=' . \$sub->max_resorts . ' cycle=' . \$sub->billing_cycle . PHP_EOL;
"
```
Expected: invoice updated, sub unchanged.

- [ ] **Step 3: Bayar → sub aktif + cycle baru**
```bash
php artisan tinker --execute="
app(\App\Services\BillingService::class)->confirmPayment(\$inv);
\$sub->refresh();
echo 'After pay: max=' . \$sub->max_resorts . ' price=' . \$sub->price_per_resort . ' cycle=' . \$sub->billing_cycle . ' status=' . \$sub->status . PHP_EOL;
"
```
Expected: sub max/price/cycle updated, status active.

- [ ] **Step 4: Order flow regression** — new tenant order → pay → activate.

---

## Rollback Plan
```bash
# git revert commits; no migration
# restore changeCycle() if needed
```

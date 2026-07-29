# e-Koperasi — Self-Hosted Payment UI Design

**Date:** 2026-07-29
**Status:** Draft
**Project:** e-Koperasi (Laravel 12 + Inertia.js Vue 3 + Tailwind CSS 4)
**Related:** [2026-07-28-e-koperasi-saas-billing-design](./2026-07-28-e-koperasi-saas-billing-design.md)

## 1. Context

Current payment flow redirects user to Duitku hosted page for completing payment. This spec replaces that redirect with a self-hosted payment UI inside the authenticated Client Portal. Payment info (VA number, QRIS, instructions) is displayed directly on e-Koperasi pages — full control over UX, fee breakdown, and retry flow.

Payment channel fees are synced from Duitku API and bundled into the total amount billed to the client.

## 2. Scope

- Self-hosted payment page accessible only when client is logged in
- Payment method selection grouped by type (VA, QRIS, E-Wallet) with per-channel fee display
- Initiate Duitku transaction from backend, show VA/QRIS/instructions on our page
- Change payment method mid-flow: cancel old transaction, create new one
- Persistent countdown from DB `expiry` field — survives page reload
- Auto-poll status every 30 seconds while `pending`
- Scheduler to expire stale `pending` transactions
- Fee synced from Duitku API `totalFee` + `totalFeePercent` per channel

### Out of Scope
- Invoice PDF redesign (existing template used)
- Duitku redirect/return URL handling (redirect no longer used)
- Admin-side payment UI changes

## 3. Database Changes

### Migration: Add fee columns to `payment_channels`

```php
Schema::table('payment_channels', function (Blueprint $table) {
    $table->unsignedInteger('fee_fixed')->default(0)->after('icon_url');
    $table->unsignedTinyInteger('fee_percent')->default(0)->after('fee_fixed');
});
```

### Migration: Add fee columns to `payment_transactions`

```php
Schema::table('payment_transactions', function (Blueprint $table) {
    $table->unsignedInteger('base_amount')->default(0)->after('amount');
    $table->unsignedInteger('fee_amount')->default(0)->after('base_amount');
});
```

### PaymentTransaction — status enum expansion

```
'pending', 'success', 'failed'  →  'pending', 'success', 'failed', 'expired', 'cancelled'
```

## 4. Model Changes

### PaymentChannel

```php
// casts
'fee_fixed' => 'integer',
'fee_percent' => 'integer',

public function calculateFee(int $baseAmount): int
{
    return $this->fee_fixed + (int) round($baseAmount * $this->fee_percent / 100);
}

public function totalAmount(int $baseAmount): int
{
    return $baseAmount + $this->calculateFee($baseAmount);
}
```

### PaymentTransaction — status constants

```php
const STATUS_PENDING = 'pending';
const STATUS_SUCCESS = 'success';
const STATUS_FAILED = 'failed';
const STATUS_EXPIRED = 'expired';
const STATUS_CANCELLED = 'cancelled';
```

## 5. Backend Changes

### DuitkuService

- `createInvoice()`: lookup channel, calculate `totalAmount` = baseAmount + fee, send `paymentAmount` = totalAmount to Duitku. Store `base_amount` and `fee_amount` on transaction.
- `syncPaymentChannels()`: persist `totalFee` → `fee_fixed` and `totalFeePercent` → `fee_percent` from Duitku response.

### PaymentController (Client)

| Method | Route | Purpose |
|---|---|---|
| `GET` | `/client/invoices/{id}/payment` | Show payment page for an invoice. If existing `pending` transaction exists, go straight to step 2. Include list of active channels with fees. |
| `POST` | `/client/payment/initiate` | Create/initiate payment. Accepts `invoice_id` + `payment_method`. Calls Duitku `createInvoice`, returns VA number / QRIS URL / instructions + `transaction_id`. |
| `POST` | `/client/payment/{id}/change-method` | Cancel existing transaction (status → `cancelled`), create new transaction with different channel, call Duitku again. |
| `GET` | `/client/payment/{id}/status` | Return current status + expiry — used by polling interval. |

### Duitku callback (existing, minor update)

- Handles `fee_amount` and `base_amount` through to invoice
- On `cancelled` / `expired` callback: no-op (already handled by our scheduler and change-method)

### Artisan Command: `billing:expire-transactions`

```php
// Schedule: every minute in routes/console.php
PaymentTransaction::where('status', 'pending')
    ->whereNotNull('expiry')
    ->where('expiry', '<=', now())
    ->update(['status' => 'expired']);
```

## 6. Frontend Changes

### Modified Pages

| File | Change |
|---|---|
| `Pages/Client/InvoiceDetail.vue` | Replace "Bayar via Duitku" button redirect with link to `/client/invoices/{id}/payment` |
| `Pages/Client/Payments.vue` | Link payment detail to the new self-hosted payment page |

### New Pages

| File | Route | Description |
|---|---|---|
| `Pages/Client/PaymentPage.vue` | `/client/invoices/{id}/payment` | Two-step payment page: channel selection → payment instructions |
| (no separate callback page — redirect no longer used) | | |

### Component Tree

```
ClientLayout
 └── PaymentPage.vue
      ├── Invoice Summary — nomor invoice, plan name, total tagihan
      │
      ├── Step 1: PaymentMethodSelector (inline)
      │   ├── Group: Virtual Account — channels grouped by type 'va'
      │   ├── Group: QRIS — channels grouped by type 'qris'
      │   ├── Group: E-Wallet — channels grouped by type 'ewallet'
      │   └── Each item: icon, name, fee (Rp X.XXX), total amount (Rp XX.XXX)
      │   └── "Bayar Sekarang" button — disabled until channel selected
      │
      ├── Step 2: Payment Instructions (after confirm)
      │   ├── Status banner (Menunggu / Kadaluarsa / Berhasil / Gagal)
      │   ├── Amount breakdown (base amount + fee = total)
      │   ├── VA Mode: large bank number with copy button
      │   ├── QRIS Mode: QR code image (from URL)
      │   ├── Payment instructions per channel type
      │   ├── Countdown from DB `expiry` field
      │   ├── "Ganti Metode Pembayaran" button
      │   └── "Cek Status" button (manual refresh)
      │
      └── Step 3: Success / Expired fallback
```

### Component States

| State | Condition | UI |
|---|---|---|
| Loading | Initial load | Skeleton / shimmer |
| Step 1: No selection | `!selectedCode` | All channels listed, button disabled |
| Step 1: Selected | `selectedCode` | Channel highlighted, total shown, button enabled |
| Step 1: Submitting | POST initiate | Button spinner, disabled |
| Step 1: Error | Duitku fail | Error alert + retry |
| Step 2: Waiting | `status === pending` | VA/QRIS, countdown, polling active |
| Step 2: Expired | `status === expired` | Red warning, "Bayar Ulang" → reset to step 1, polling stop |
| Step 2: Success | `status === success` | Green check + "Pembayaran Berhasil", polling stop |
| Step 2: Failed | `status === failed` | Error + "Coba Lagi" |
| Step 2: Changing method | POST change-method | Confirmation dialog |

### Persisted Countdown

```typescript
// Countdown reads from DB `expiry` field, not client local start time:
onMounted(() => {
  remaining = new Date(transaction.expiry) - Date.now();
  interval = setInterval(() => {
    remaining = new Date(transaction.expiry) - Date.now();
    if (remaining <= 0) {
      clearInterval(interval);
      status = 'expired';
    }
  }, 1000);
});
```

Reloading the page re-fetches `transaction.expiry` from the backend via the `status` endpoint, so the countdown continues correctly.

### Polling

```typescript
// Active only while status === 'pending' in Step 2
startPolling(transactionId: string) {
  pollingInterval = setInterval(async () => {
    const txn = await axios.get(`/client/payment/${transactionId}/status`);
    if (txn.status !== 'pending') {
      clearInterval(pollingInterval);
      updateUI(txn.status);
    }
  }, 30_000);
}
```

## 7. Change Method Flow

```
User clicks "Ganti Metode Pembayaran"
 → Confirmation dialog: "Pembayaran sebelumnya akan dibatalkan"
 → Confirm → POST /client/payment/{id}/change-method { new_payment_method }
   → Backend: 
     1. Set old transaction status = 'cancelled'
     2. Create new PaymentTransaction with new channel
     3. Call Duitku createInvoice with new total
     4. Return new transaction data
 → Frontend: Step 2 re-renders with new VA/QRIS + new expiry
```

## 8. Scheduler

```php
// routes/console.php
Schedule::command('billing:expire-transactions')->everyMinute();

// app/Console/Commands/BillingExpireTransactions.php
// Update all pending transactions where expiry has passed to 'expired'
```

## 9. Error Handling

| Scenario | Handling |
|---|---|
| Duitku API down (initiate) | Error message + retry button, no transaction created |
| Duitku callback never arrives | Manual "Cek Status" button + polling covers it |
| Network failure on polling | Silent fail + retry on next interval |
| Change method while callback arrives | Race: old transaction already `cancelled`, callback ignored (status check skips cancelled) |
| Expired during change method flow | Old transaction marked cancelled, new one will get new expiry |

## 10. Open Questions

- [x] Fee structure: sync from Duitku, not manual
- [x] Auth requirement: login-only, no public access
- [x] Change method: cancel old + create new
- [x] Countdown: persisted via DB expiry field

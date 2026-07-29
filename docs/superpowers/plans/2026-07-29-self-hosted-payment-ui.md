# Self-Hosted Payment UI Implementation Plan

**For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans to implement plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace Duitku redirect flow with self-hosted payment page inside Client Portal — show VA/QRIS/instructions directly, sync fees from Duitku, persist countdown, allow change payment method.

**Architecture:** Two migration files add fee columns to `payment_channels` + `payment_transactions` and expand status enum. `DuitkuService` updated to calculate total with fee and sync fee from Duitku. `PaymentController` gains 3 new endpoints (initiate, change-method, status). New Vue `PaymentPage.vue` with two-step flow (select channel → payment instructions/countdown/polling). Console command expires stale pending transactions.

**Tech Stack:** Laravel 12, Inertia.js v3, Vue 3, TypeScript, Tailwind v4, Duitku API

---

## File Map

| File | Action | Responsibility |
|---|---|---|
| `database/migrations/2026_07_29_000001_add_fee_to_payment_channels.php` | Create | Add `fee_fixed`, `fee_percent` to payment_channels |
| `database/migrations/2026_07_29_000002_add_fee_status_to_payment_transactions.php` | Create | Add `base_amount`, `fee_amount` to payment_transactions; expand status enum to include `cancelled` |
| `app/Models/PaymentChannel.php` | Modify | Add casts, `calculateFee()`, `totalAmount()` methods |
| `app/Models/PaymentTransaction.php` | Modify | Add `fee_amount`, `base_amount` to fillable; add status constants |
| `app/Services/DuitkuService.php` | Modify | `createInvoice`: calculate total with fee, pass to Duitku, store base_amount + fee_amount on transaction. `syncPaymentChannels`: persist totalFee/feePercent |
| `app/Http/Controllers/Client/PaymentController.php` | Modify | Add `initiate()`, `changeMethod()`, `status()` endpoints |
| `routes/web.php` | Modify | Add 3 new client routes for initiate/change/status |
| `app/Console/Commands/BillingExpireTransactions.php` | Create | Expire stale pending transactions |
| `routes/console.php` | Modify | Register expire command every 1 minute |
| `resources/js/Pages/Client/PaymentPage.vue` | Create | Two-step payment page (select channel → payment instructions) |
| `resources/js/Pages/Client/Payments.vue` | Modify | Link to new payment page |
| `resources/js/Pages/Client/InvoiceDetail.vue` | Modify | Replace redirect-to-duitku with link to `/client/invoices/{id}/payment` |
| `resources/js/Pages/Client/PaymentDetail.vue` | Modify | Adjust if needed to show fee info |

---

### Task 1: Migration — Add fee columns to payment_channels

**Files:**
- Create: `database/migrations/2026_07_29_000001_add_fee_to_payment_channels.php`

- [ ] **Step 1: Create migration**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_channels', function (Blueprint $table) {
            $table->unsignedInteger('fee_fixed')->default(0)->after('icon_url');
            $table->unsignedTinyInteger('fee_percent')->default(0)->after('fee_fixed');
        });
    }

    public function down(): void
    {
        Schema::table('payment_channels', function (Blueprint $table) {
            $table->dropColumn(['fee_fixed', 'fee_percent']);
        });
    }
};
```
- [ ] **Step 2: Run migration**
```bash
cd /Users/vendywira/Code/ksu/e-koperasi && php artisan migrate --pretend
```
Expected: no errors, SQL printed showing ALTER TABLE.

- [ ] **Step 3: Commit**
```bash
git add database/migrations/2026_07_29_000001_add_fee_to_payment_channels.php
git commit -m "feat: add fee_fixed and fee_percent columns to payment_channels"
```

---

### Task 2: Migration — Add fee columns + cancelled status to payment_transactions

**Files:**
- Create: `database/migrations/2026_07_29_000002_add_fee_status_to_payment_transactions.php`

- [ ] **Step 1: Create migration**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->unsignedInteger('base_amount')->default(0)->after('amount');
            $table->unsignedInteger('fee_amount')->default(0)->after('base_amount');
        });

        // MySQL enum expansion — raw alter
        DB::statement("ALTER TABLE payment_transactions MODIFY COLUMN status ENUM('pending','success','failed','expired','cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropColumn(['base_amount', 'fee_amount']);
        });

        DB::statement("ALTER TABLE payment_transactions MODIFY COLUMN status ENUM('pending','success','failed','expired') NOT NULL DEFAULT 'pending'");
    }
};
```
- [ ] **Step 2: Run migration**
```bash
cd /Users/vendywira/Code/ksu/e-koperasi && php artisan migrate --pretend
```
Expected: SQL ALTER TABLE commands printed.

- [ ] **Step 3: Commit**
```bash
git add database/migrations/2026_07_29_000002_add_fee_status_to_payment_transactions.php
git commit -m "feat: add base_amount, fee_amount, cancelled status to payment_transactions"
```

---

### Task 3: Update PaymentChannel model with fee methods

**Files:**
- Modify: `app/Models/PaymentChannel.php`

- [ ] **Step 1: Add casts and fee calculation methods**

Current file (`app/Models/PaymentChannel.php`):
```php
protected function casts(): array
{
    return [
        'is_active' => 'boolean',
    ];
}
```

Replace with:
```php
protected function casts(): array
{
    return [
        'is_active' => 'boolean',
        'fee_fixed' => 'integer',
        'fee_percent' => 'integer',
    ];
}

public function calculateFee(int $baseAmount): int
{
    return $this->fee_fixed + (int) round($baseAmount * $this->fee_percent / 100);
}

public function totalAmount(int $baseAmount): int
{
    return $baseAmount + $this->calculateFee($baseAmount);
}
```
- [ ] **Step 2: Verify no syntax errors**
```bash
cd /Users/vendywira/Code/ksu/e-koperasi && php artisan tinker --execute="(new ReflectionMethod(App\Models\PaymentChannel::class, 'calculateFee'))->getNumberOfParameters() === 1 ? 'OK' : 'FAIL'"
```
Expected: `OK`

- [ ] **Step 3: Commit**
```bash
git add app/Models/PaymentChannel.php
git commit -m "feat: add calculateFee and totalAmount methods to PaymentChannel"
```

---

### Task 4: Update PaymentTransaction model with new fillable fields + status constants

**Files:**
- Modify: `app/Models/PaymentTransaction.php`

- [ ] **Step 1: Update fillable and add status constants**

Current fillable:
```php
protected $fillable = [
    'invoice_id', 'duitku_ref', 'amount', 'channel_code',
    'channel_name', 'status', 'paid_at', 'expiry', 'raw_response',
];
```

Replace with:
```php
protected $fillable = [
    'invoice_id', 'duitku_ref', 'amount', 'base_amount', 'fee_amount',
    'channel_code', 'channel_name', 'status', 'paid_at', 'expiry', 'raw_response',
];

const STATUS_PENDING = 'pending';
const STATUS_SUCCESS = 'success';
const STATUS_FAILED = 'failed';
const STATUS_EXPIRED = 'expired';
const STATUS_CANCELLED = 'cancelled';
```
- [ ] **Step 2: Verify**
```bash
cd /Users/vendywira/Code/ksu/e-koperasi && php artisan tinker --execute="defined(App\Models\PaymentTransaction::STATUS_CANCELLED) ? 'OK' : 'FAIL'"
```
Expected: `OK`

- [ ] **Step 3: Commit**
```bash
git add app/Models/PaymentTransaction.php
git commit -m "feat: add base_amount, fee_amount fillable + status constants to PaymentTransaction"
```

---

### Task 5: Update DuitkuService — fee calculation + sync fee

**Files:**
- Modify: `app/Services/DuitkuService.php`

- [ ] **Step 1: Update `createInvoice` to include fee in total**

Current lines 38-85 (`createInvoice` method). Replace method body:

```php
public function createInvoice(Invoice $invoice, string $paymentMethod, string $customerName, string $customerEmail, string $customerPhone = ''): array
{
    $channel = PaymentChannel::where('code', $paymentMethod)->first();
    $baseAmount = (int) round($invoice->total_amount);
    $feeAmount = $channel ? $channel->calculateFee($baseAmount) : 0;
    $totalAmount = $channel ? $channel->totalAmount($baseAmount) : $baseAmount;

    $transaction = PaymentTransaction::create([
        'invoice_id' => $invoice->id,
        'amount' => $totalAmount,
        'base_amount' => $baseAmount,
        'fee_amount' => $feeAmount,
        'channel_code' => $paymentMethod,
        'status' => 'pending',
    ]);

    $payload = [
        'merchantCode' => $this->merchantCode,
        'paymentAmount' => $totalAmount,
        'paymentMethod' => $paymentMethod,
        'merchantOrderId' => $transaction->id,
        'productDetails' => "Invoice {$invoice->invoice_number} — {$invoice->name}",
        'customerVaName' => $customerName,
        'email' => $customerEmail,
        'phoneNumber' => $customerPhone,
        'callbackUrl' => $this->callbackUrl,
        'returnUrl' => str_replace('{ref}', $transaction->id, $this->returnUrl),
        'signature' => $this->generateSignature($transaction->id),
        'expiryPeriod' => $this->expiryPeriod,
    ];

    $response = Http::post("{$this->baseUrl()}/api/v1/merchant/v2/createInvoice", $payload);

    if ($response->failed()) {
        Log::error('Duitku createInvoice failed', [
            'transaction_id' => $transaction->id,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
        $transaction->update([
            'status' => 'failed',
            'raw_response' => $response->json(),
        ]);
        $msg = $response->json()['Message'] ?? 'Gagal membuat pembayaran';
        throw new \RuntimeException($msg);
    }

    $data = $response->json();
    $transaction->update([
        'duitku_ref' => $data['reference'] ?? null,
        'expiry' => now()->addMinutes($this->expiryPeriod),
        'raw_response' => $data,
    ]);

    return $data;
}
```
- [ ] **Step 2: Update `syncPaymentChannels` to persist fee**

Current lines 113-153. Find the loop that saves channels:
```php
PaymentChannel::updateOrCreate(
    ['code' => $code],
    [
        'name' => $ch['paymentName'] ?? $ch['name'] ?? $code,
        'icon_url' => $ch['iconUrl'] ?? null,
        'type' => $this->mapChannelType($code),
        'is_active' => true,
    ]
);
```

Replace with:
```php
PaymentChannel::updateOrCreate(
    ['code' => $code],
    [
        'name' => $ch['paymentName'] ?? $ch['name'] ?? $code,
        'icon_url' => $ch['iconUrl'] ?? null,
        'fee_fixed' => (int) ($ch['totalFee'] ?? 0),
        'fee_percent' => (int) ($ch['totalFeePercent'] ?? 0),
        'type' => $this->mapChannelType($code),
        'is_active' => true,
    ]
);
```

Also add `use App\Models\PaymentChannel;` import at top if not already present (it's already imported on line 7 from the original file).

- [ ] **Step 3: Verify**
```bash
cd /Users/vendywira/Code/ksu/e-koperasi && php artisan tinker --execute="(new ReflectionMethod(App\Services\DuitkuService::class, 'createInvoice'))->getFileName() ? 'OK' : 'FAIL'"
```
Expected: `OK`

- [ ] **Step 4: Commit**
```bash
git add app/Services/DuitkuService.php
git commit -m "feat: include fee in payment total, sync fee from Duitku API"
```

---

### Task 6: Add new controller endpoints — initiate, change-method, status

**Files:**
- Modify: `app/Http/Controllers/Client/PaymentController.php`

- [ ] **Step 1: Add `initiate`, `changeMethod`, `status` methods**

```php
public function initiate(Request $request, DuitkuService $duitku): JsonResponse
{
    $validated = $request->validate([
        'invoice_id' => 'required|exists:invoices,id',
        'payment_method' => 'required|string',
    ]);

    $invoice = Invoice::where('id', $validated['invoice_id'])
        ->where('user_id', auth()->id())
        ->where('status', 'pending')
        ->firstOrFail();

    // Cancel any existing pending transaction for this invoice
    PaymentTransaction::where('invoice_id', $invoice->id)
        ->where('status', 'pending')
        ->update(['status' => 'cancelled']);

    try {
        $result = $duitku->createInvoice(
            $invoice,
            $validated['payment_method'],
            auth()->user()->name,
            auth()->user()->email
        );

        $transaction = PaymentTransaction::where('invoice_id', $invoice->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $invoice->update([
            'payment_channel' => $validated['payment_method'],
            'payment_transaction_id' => $transaction?->id,
        ]);

        $channel = PaymentChannel::where('code', $validated['payment_method'])->first();

        return response()->json([
            'transaction_id' => $transaction?->id,
            'va_number' => $result['vaNumber'] ?? null,
            'qr_url' => $result['qrUrl'] ?? $result['actionUrl'] ?? null,
            'redirect_url' => $result['redirectUrl'] ?? null,
            'payment_url' => $result['paymentUrl'] ?? null,
            'reference' => $result['reference'] ?? null,
            'expiry' => $transaction?->expiry?->toIso8601String(),
            'base_amount' => $transaction?->base_amount ?? (int) $invoice->total_amount,
            'fee_amount' => $transaction?->fee_amount ?? 0,
            'total_amount' => $transaction?->amount ?? $invoice->total_amount,
            'channel_name' => $channel?->name ?? $validated['payment_method'],
            'channel_type' => $channel?->type ?? 'va',
            'instructions' => $this->getPaymentInstructions($validated['payment_method']),
        ]);
    } catch (\RuntimeException $e) {
        return response()->json(['error' => $e->getMessage()], 422);
    }
}

public function changeMethod(Request $request, string $id, DuitkuService $duitku): JsonResponse
{
    $validated = $request->validate([
        'payment_method' => 'required|string',
    ]);

    $oldTransaction = PaymentTransaction::where('id', $id)
        ->whereHas('invoice', fn($q) => $q->where('user_id', auth()->id()))
        ->where('status', 'pending')
        ->firstOrFail();

    $invoice = $oldTransaction->invoice;

    // Mark old as cancelled
    $oldTransaction->update(['status' => 'cancelled']);

    try {
        $result = $duitku->createInvoice(
            $invoice,
            $validated['payment_method'],
            auth()->user()->name,
            auth()->user()->email
        );

        $transaction = PaymentTransaction::where('invoice_id', $invoice->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $invoice->update([
            'payment_channel' => $validated['payment_method'],
            'payment_transaction_id' => $transaction?->id,
        ]);

        $channel = PaymentChannel::where('code', $validated['payment_method'])->first();

        return response()->json([
            'transaction_id' => $transaction?->id,
            'va_number' => $result['vaNumber'] ?? null,
            'qr_url' => $result['qrUrl'] ?? $result['actionUrl'] ?? null,
            'redirect_url' => $result['redirectUrl'] ?? null,
            'reference' => $result['reference'] ?? null,
            'expiry' => $transaction?->expiry?->toIso8601String(),
            'base_amount' => $transaction?->base_amount ?? (int) $invoice->total_amount,
            'fee_amount' => $transaction?->fee_amount ?? 0,
            'total_amount' => $transaction?->amount ?? $invoice->total_amount,
            'channel_name' => $channel?->name ?? $validated['payment_method'],
            'channel_type' => $channel?->type ?? 'va',
            'instructions' => $this->getPaymentInstructions($validated['payment_method']),
        ]);
    } catch (\RuntimeException $e) {
        // Revert old transaction status on failure
        $oldTransaction->update(['status' => 'pending']);
        return response()->json(['error' => $e->getMessage()], 422);
    }
}

public function status(string $id): JsonResponse
{
    $transaction = PaymentTransaction::where('id', $id)
        ->whereHas('invoice', fn($q) => $q->where('user_id', auth()->id()))
        ->firstOrNotFound();

    return response()->json([
        'status' => $transaction->status,
        'expiry' => $transaction->expiry?->toIso8601String(),
        'paid_at' => $transaction->paid_at?->toIso8601String(),
    ]);
}

private function getPaymentInstructions(string $methodCode): string
{
    $methodCode = strtolower($methodCode);

    if (str_contains($methodCode, 'va')) {
        $bankName = match (true) {
            str_contains($methodCode, 'bri') => 'BRI',
            str_contains($methodCode, 'bni') => 'BNI',
            str_contains($methodCode, 'mandiri') || str_contains($methodCode, 'm1') => 'Mandiri',
            str_contains($methodCode, 'bca') || str_contains($methodCode, 'm2') => 'BCA',
            str_contains($methodCode, 'permata') => 'Permata',
            str_contains($methodCode, 'cimb') => 'CIMB Niaga',
            str_contains($methodCode, 'danamon') => 'Danamon',
            default => 'Bank',
        };
        return "Bayar melalui ATM, Mobile Banking, atau Internet Banking {$bankName} dengan nomor Virtual Account di atas.";
    }

    if (str_contains($methodCode, 'qris')) {
        return 'Scan kode QR di atas menggunakan aplikasi GoPay, OVO, DANA, ShopeePay, atau LinkAja.';
    }

    if (in_array($methodCode, ['gopay', 'ovo', 'dana', 'shopeepay', 'linkaja'])) {
        return 'Bayar menggunakan aplikasi ' . ucfirst($methodCode) . '. Scan QR code atau klik link pembayaran.';
    }

    return 'Ikuti petunjuk pembayaran yang ditampilkan.';
}
```

Add missing imports at top of file:
```php
use App\Models\PaymentChannel;
use App\Models\PaymentTransaction;
use App\Services\DuitkuService;
use Illuminate\Http\JsonResponse;
```
- [ ] **Step 2: Verify PHP syntax**
```bash
cd /Users/vendywira/Code/ksu/e-koperasi && php artisan route:list --path=client/payment 2>&1 | head -20
```

- [ ] **Step 3: Commit**
```bash
git add app/Http/Controllers/Client/PaymentController.php
git commit -m "feat: add initiate, change-method, status endpoints to PaymentController"
```

---

### Task 7: Register new routes and add expire transactions command

**Files:**
- Modify: `routes/web.php`
- Create: `app/Console/Commands/BillingExpireTransactions.php`
- Modify: `routes/console.php`

- [ ] **Step 1: Add routes to web.php**

After existing `Route::post('/client/payment/duitku', ...)` line (line 66):

```php
// Client: Self-hosted payment UI
Route::get('/client/invoices/{id}/payment', [\App\Http\Controllers\Client\PaymentController::class, 'showPaymentPage'])->name('invoices.payment');
Route::post('/client/payment/initiate', [\App\Http\Controllers\Client\PaymentController::class, 'initiate'])->name('payment.initiate');
Route::post('/client/payment/{id}/change-method', [\App\Http\Controllers\Client\PaymentController::class, 'changeMethod'])->name('payment.change-method');
Route::get('/client/payment/{id}/status', [\App\Http\Controllers\Client\PaymentController::class, 'status'])->name('payment.status');
```

Also need to add `showPaymentPage` method to PaymentController:

- [ ] **Step 2: Add `showPaymentPage` method to PaymentController**

```php
use Inertia\Inertia;
use Inertia\Response;

public function showPaymentPage(string $invoiceId): Response
{
    $invoice = Invoice::with('invoiceItems')
        ->where('user_id', auth()->id())
        ->findOrFail($invoiceId);

    // Check for existing pending transaction
    $existingTransaction = PaymentTransaction::where('invoice_id', $invoice->id)
        ->where('status', 'pending')
        ->latest()
        ->first();

    $channels = PaymentChannel::active()->get()->map(fn($ch) => [
        'id' => $ch->id,
        'code' => $ch->code,
        'name' => $ch->name,
        'icon_url' => $ch->icon_url,
        'type' => $ch->type,
        'fee_fixed' => $ch->fee_fixed,
        'fee_percent' => $ch->fee_percent,
        'calculated_fee' => $ch->calculateFee((int) $invoice->total_amount),
        'total_amount' => $ch->totalAmount((int) $invoice->total_amount),
    ]);

    $groupedChannels = collect(['va' => [], 'qris' => [], 'ewallet' => [], 'retail' => []]);
    foreach ($channels as $ch) {
        $groupedChannels[$ch['type']][] = $ch;
    }

    return Inertia::render('Client/PaymentPage', [
        'invoice' => [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'name' => $invoice->name,
            'total_amount' => (int) $invoice->total_amount,
            'status' => $invoice->status,
            'domain' => $invoice->domain,
        ],
        'groupedChannels' => $groupedChannels->toArray(),
        'existingTransaction' => $existingTransaction ? [
            'id' => $existingTransaction->id,
            'status' => $existingTransaction->status,
            'expiry' => $existingTransaction->expiry?->toIso8601String(),
            'channel_code' => $existingTransaction->channel_code,
        ] : null,
    ]);
}
```
- [ ] **Step 3: Create expire command**
```bash
cd /Users/vendywira/Code/ksu/e-koperasi && php artisan make:command BillingExpireTransactions
```

Replace the generated file content with:
```php
<?php

namespace App\Console\Commands;

use App\Models\PaymentTransaction;
use Illuminate\Console\Command;

class BillingExpireTransactions extends Command
{
    protected $signature = 'billing:expire-transactions';
    protected $description = 'Expire pending payment transactions past their expiry date';

    public function handle(): int
    {
        $count = PaymentTransaction::where('status', PaymentTransaction::STATUS_PENDING)
            ->whereNotNull('expiry')
            ->where('expiry', '<=', now())
            ->update(['status' => PaymentTransaction::STATUS_EXPIRED]);

        $this->info("Expired {$count} transactions.");

        return Command::SUCCESS;
    }
}
```

- [ ] **Step 4: Register in console.php**

Add after line 23 (cancel-expired):
```php
// Expire stale payment transactions
Schedule::command('billing:expire-transactions')->everyMinute();
```

- [ ] **Step 5: Verify routes**
```bash
cd /Users/vendywira/Code/ksu/e-koperasi && php artisan route:list --path=client/payment 2>&1
```
Expected: 4 routes listed (initiate, change-method, status, payment page)

- [ ] **Step 6: Commit**
```bash
git add routes/web.php routes/console.php app/Console/Commands/BillingExpireTransactions.php app/Http/Controllers/Client/PaymentController.php
git commit -m "feat: add payment routes, showPaymentPage, expire transactions command"
```

---

### Task 8: Create PaymentPage.vue

**Files:**
- Create: `resources/js/Pages/Client/PaymentPage.vue`

- [ ] **Step 1: Create the Vue component**

```vue
<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import ClientLayout from '@/Layouts/ClientLayout.vue';

const props = defineProps<{
    invoice: {
        id: string;
        invoice_number: string;
        name: string;
        total_amount: number;
        status: string;
        domain: string;
    };
    groupedChannels: Record<string, {
        id: string;
        code: string;
        name: string;
        icon_url: string | null;
        type: string;
        fee_fixed: number;
        fee_percent: number;
        calculated_fee: number;
        total_amount: number;
    }[]>;
    existingTransaction: {
        id: string;
        status: string;
        expiry: string;
        channel_code: string;
    } | null;
}>();

// Steps: 'select' | 'payment' | 'success' | 'expired'
const step = ref<'select' | 'payment' | 'success' | 'expired'>(
    props.existingTransaction && props.existingTransaction.status === 'pending' ? 'payment' : 'select'
);
const selectedCode = ref(props.existingTransaction?.channel_code ?? '');
const transactionId = ref(props.existingTransaction?.id ?? '');
const loading = ref(false);
const error = ref('');

// Payment data (populated after initiate)
const paymentData = ref<{
    va_number: string | null;
    qr_url: string | null;
    redirect_url: string | null;
    reference: string | null;
    expiry: string;
    base_amount: number;
    fee_amount: number;
    total_amount: number;
    channel_name: string;
    channel_type: string;
    instructions: string;
} | null>(null);

const groupLabels: Record<string, string> = {
    va: 'Virtual Account',
    qris: 'QRIS',
    ewallet: 'E-Wallet',
    retail: 'Retail',
};

const nonEmptyGroups = computed(() => {
    const entries = Object.entries(props.groupedChannels).filter(([, channels]) => channels.length > 0);
    return Object.fromEntries(entries);
});

// — Countdown —
const remaining = ref(0);
const countdownInterval = ref<ReturnType<typeof setInterval> | null>(null);

function startCountdown(expiryIso: string) {
    if (countdownInterval.value) clearInterval(countdownInterval.value);
    updateRemaining(expiryIso);
    countdownInterval.value = setInterval(() => updateRemaining(expiryIso), 1000);
}

function updateRemaining(expiryIso: string) {
    const expiry = new Date(expiryIso).getTime();
    const now = Date.now();
    const diff = expiry - now;
    remaining.value = diff > 0 ? diff : 0;
    if (remaining.value <= 0) {
        step.value = 'expired';
        stopPolling();
        if (countdownInterval.value) {
            clearInterval(countdownInterval.value);
            countdownInterval.value = null;
        }
    }
}

const countdownDisplay = computed(() => {
    const totalSec = Math.floor(remaining.value / 1000);
    const m = Math.floor(totalSec / 60);
    const s = totalSec % 60;
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
});

onUnmounted(() => {
    if (countdownInterval.value) clearInterval(countdownInterval.value);
    stopPolling();
});

// — Polling —
const pollingInterval = ref<ReturnType<typeof setInterval> | null>(null);

function startPolling() {
    stopPolling();
    pollingInterval.value = setInterval(async () => {
        if (!transactionId.value) return;
        try {
            const res = await axios.get(`/client/payment/${transactionId.value}/status`);
            const status = res.data.status;
            if (status === 'success') {
                step.value = 'success';
                stopPolling();
            } else if (status === 'expired') {
                step.value = 'expired';
                stopPolling();
            } else if (status === 'pending' && res.data.expiry) {
                updateRemaining(res.data.expiry);
            }
        } catch {
            // silent retry on next interval
        }
    }, 30_000);
}

function stopPolling() {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
    }
}

async function initiatePayment() {
    if (!selectedCode.value) return;
    loading.value = true;
    error.value = '';
    try {
        const res = await axios.post('/client/payment/initiate', {
            invoice_id: props.invoice.id,
            payment_method: selectedCode.value,
        });
        paymentData.value = res.data;
        transactionId.value = res.data.transaction_id;
        step.value = 'payment';
        startCountdown(res.data.expiry);
        startPolling();
    } catch (e: any) {
        error.value = e.response?.data?.error ?? 'Gagal membuat pembayaran. Silakan coba lagi.';
    } finally {
        loading.value = false;
    }
}

async function changeMethod() {
    if (!transactionId.value) {
        step.value = 'select';
        return;
    }
    loading.value = true;
    error.value = '';
    try {
        // Back to select — actual cancel happens on new initiate
        step.value = 'select';
        selectedCode.value = '';
        stopPolling();
        if (countdownInterval.value) {
            clearInterval(countdownInterval.value);
            countdownInterval.value = null;
        }
    } finally {
        loading.value = false;
    }
}

async function checkStatus() {
    if (!transactionId.value) return;
    try {
        const res = await axios.get(`/client/payment/${transactionId.value}/status`);
        const status = res.data.status;
        if (status === 'success') {
            step.value = 'success';
            stopPolling();
        } else if (status === 'expired') {
            step.value = 'expired';
            stopPolling();
        }
    } catch {
        // silent
    }
}

// On mount: if existing pending transaction, fetch its payment data
onMounted(async () => {
    if (props.existingTransaction && props.existingTransaction.status === 'pending') {
        loading.value = true;
        try {
            // Initiate with existing channel to rebuild paymentData
            const res = await axios.post('/client/payment/initiate', {
                invoice_id: props.invoice.id,
                payment_method: props.existingTransaction.channel_code,
            });
            paymentData.value = res.data;
            transactionId.value = res.data.transaction_id;
            startCountdown(res.data.expiry);
            startPolling();
        } catch {
            // fallback to select step
            step.value = 'select';
        } finally {
            loading.value = false;
        }
    }
});
</script>

<template>
    <ClientLayout :title="'Pembayaran — ' + invoice.invoice_number">
        <Head :title="'Pembayaran — ' + invoice.invoice_number" />

        <div class="p-4 sm:p-6 lg:p-8 max-w-2xl mx-auto space-y-6">
            <!-- Back -->
            <Link
                :href="'/client/invoices/' + invoice.id"
                class="inline-flex items-center gap-1.5 text-sm text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Kembali ke Invoice
            </Link>

            <!-- Invoice Header -->
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                <div class="p-5 sm:p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-mono text-neutral-400">{{ invoice.name }}</p>
                            <h1 class="text-lg font-bold text-neutral-900 dark:text-white mt-0.5">{{ invoice.invoice_number }}</h1>
                        </div>
                        <span class="text-right">
                            <p class="text-xs text-neutral-400">Total Tagihan</p>
                            <p class="text-lg font-bold text-neutral-900 dark:text-white font-mono">Rp{{ Number(invoice.total_amount).toLocaleString('id-ID') }}</p>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Loading state -->
            <div v-if="loading && !paymentData" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-8 text-center">
                <svg class="w-8 h-8 animate-spin mx-auto text-primary-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <p class="mt-3 text-sm text-neutral-500">Memuat halaman pembayaran...</p>
            </div>

            <!-- STEP 1: Select Payment Method -->
            <div v-if="step === 'select' && !loading" class="space-y-4">
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                    <div class="p-5 sm:p-6">
                        <h2 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4">Pilih Metode Pembayaran</h2>
                        <p class="text-xs text-neutral-400 mb-4">Biaya layanan akan ditambahkan ke total pembayaran</p>

                        <div v-for="(channels, type) in nonEmptyGroups" :key="type" class="mb-5 last:mb-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-2">
                                {{ groupLabels[type] || type }}
                            </p>
                            <div class="space-y-2">
                                <button
                                    v-for="ch in channels"
                                    :key="ch.code"
                                    @click="selectedCode = ch.code"
                                    class="w-full flex items-center gap-3 p-3 sm:p-4 rounded-lg border-2 text-left transition-all cursor-pointer"
                                    :class="selectedCode === ch.code
                                        ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/10'
                                        : 'border-neutral-200 dark:border-neutral-700 hover:border-neutral-300 dark:hover:border-neutral-600'"
                                >
                                    <div v-if="ch.icon_url" class="w-10 h-10 rounded-lg bg-white dark:bg-neutral-800 flex items-center justify-center overflow-hidden flex-shrink-0 border border-neutral-100 dark:border-neutral-700">
                                        <img :src="ch.icon_url" :alt="ch.name" class="w-8 h-8 object-contain" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">{{ ch.name }}</p>
                                        <p class="text-xs text-neutral-400">
                                            Biaya layanan: Rp{{ Number(ch.calculated_fee).toLocaleString('id-ID') }}
                                        </p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-sm font-bold text-neutral-900 dark:text-white font-mono">Rp{{ Number(ch.total_amount).toLocaleString('id-ID') }}</p>
                                        <p class="text-[10px] text-neutral-400">Total Bayar</p>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Error -->
                        <div v-if="error" class="mt-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                            <p class="text-sm text-red-700 dark:text-red-300">{{ error }}</p>
                        </div>

                        <!-- Submit -->
                        <button
                            @click="initiatePayment"
                            :disabled="!selectedCode || loading"
                            class="mt-5 w-full py-3 px-5 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer min-h-[48px] flex items-center justify-center gap-2"
                        >
                            <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ loading ? 'Memproses...' : selectedCode ? 'Bayar Sekarang' : 'Pilih Metode Bayar' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- STEP 2: Payment Instructions -->
            <div v-if="step === 'payment' && paymentData" class="space-y-4">
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm overflow-hidden">
                    <div class="p-5 sm:p-6 space-y-5">
                        <!-- Status Banner -->
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">Menunggu Pembayaran</p>
                                <p class="text-xs text-amber-600 dark:text-amber-400">Selesaikan pembayaran sebelum waktu habis</p>
                            </div>
                        </div>

                        <!-- Countdown -->
                        <div class="text-center py-3">
                            <p class="text-xs text-neutral-400 mb-1">Sisa Waktu</p>
                            <p class="text-3xl font-bold font-mono" :class="remaining < 300000 ? 'text-red-600 dark:text-red-400' : 'text-neutral-900 dark:text-white'">
                                {{ countdownDisplay }}
                            </p>
                        </div>

                        <!-- Amount Breakdown -->
                        <div class="p-4 rounded-lg bg-neutral-50 dark:bg-neutral-800/50 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-neutral-500">Jumlah Tagihan</span>
                                <span class="font-medium text-neutral-900 dark:text-white font-mono">Rp{{ Number(paymentData.base_amount).toLocaleString('id-ID') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-neutral-500">Biaya Layanan ({{ paymentData.channel_name }})</span>
                                <span class="font-medium text-neutral-900 dark:text-white font-mono">Rp{{ Number(paymentData.fee_amount).toLocaleString('id-ID') }}</span>
                            </div>
                            <div class="flex justify-between text-sm font-semibold pt-2 border-t border-neutral-200 dark:border-neutral-700">
                                <span class="text-neutral-900 dark:text-white">Total Bayar</span>
                                <span class="text-emerald-700 dark:text-emerald-400 font-mono">Rp{{ Number(paymentData.total_amount).toLocaleString('id-ID') }}</span>
                            </div>
                        </div>

                        <!-- VA Number (for VA type) -->
                        <div v-if="paymentData.va_number" class="text-center">
                            <p class="text-xs text-neutral-400 mb-1">Nomor Virtual Account</p>
                            <p class="text-2xl sm:text-3xl font-bold font-mono tracking-wider text-neutral-900 dark:text-white select-all bg-neutral-50 dark:bg-neutral-800 py-3 px-4 rounded-lg border border-dashed border-neutral-300 dark:border-neutral-600">
                                {{ paymentData.va_number }}
                            </p>
                            <p class="text-xs text-neutral-400 mt-1">{{ paymentData.channel_name }}</p>
                        </div>

                        <!-- QRIS (for QRIS type) -->
                        <div v-if="paymentData.qr_url" class="text-center">
                            <img :src="paymentData.qr_url" alt="QR Code" class="w-48 h-48 mx-auto rounded-lg" />
                            <p class="text-xs text-neutral-400 mt-1">{{ paymentData.channel_name }}</p>
                        </div>

                        <!-- Redirect URL (for e-wallet) -->
                        <div v-if="paymentData.redirect_url && !paymentData.va_number && !paymentData.qr_url" class="text-center">
                            <a
                                :href="paymentData.redirect_url"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors"
                            >
                                Buka Halaman Pembayaran
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                            </a>
                        </div>

                        <!-- Instructions -->
                        <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800">
                            <div class="flex gap-2">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                                </svg>
                                <p class="text-sm text-blue-800 dark:text-blue-200">{{ paymentData.instructions }}</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3">
                            <button
                                @click="changeMethod"
                                :disabled="loading"
                                class="flex-1 py-2.5 border border-neutral-200 dark:border-neutral-700 rounded-lg text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-800 disabled:opacity-50 transition-colors cursor-pointer"
                            >
                                Ganti Metode
                            </button>
                            <button
                                @click="checkStatus"
                                class="flex-1 py-2.5 bg-neutral-100 dark:bg-neutral-800 rounded-lg text-sm font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors cursor-pointer"
                            >
                                Cek Status
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Success -->
            <div v-if="step === 'success'" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-8 text-center space-y-4">
                <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                    <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-neutral-900 dark:text-white">Pembayaran Berhasil</h2>
                <p class="text-sm text-neutral-500">Pembayaran invoice {{ invoice.invoice_number }} telah diterima.</p>
                <Link
                    :href="'/client/invoices/' + invoice.id"
                    class="inline-block px-6 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors"
                >
                    Lihat Invoice
                </Link>
            </div>

            <!-- STEP 3: Expired -->
            <div v-if="step === 'expired'" class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm p-8 text-center space-y-4">
                <div class="w-16 h-16 mx-auto rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-neutral-900 dark:text-white">Pembayaran Kadaluarsa</h2>
                <p class="text-sm text-neutral-500">Waktu pembayaran telah habis. Silakan pilih metode pembayaran lagi.</p>
                <button
                    @click="step = 'select'; selectedCode = '';"
                    class="inline-block px-6 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors cursor-pointer"
                >
                    Bayar Ulang
                </button>
            </div>
        </div>
    </ClientLayout>
</template>
```
- [ ] **Step 2: Verify TypeScript compilation**
```bash
cd /Users/vendywira/Code/ksu/e-koperasi && npx vue-tsc --noEmit 2>&1 | head -30
```
Expected: no errors (or minor type warnings)

- [ ] **Step 3: Commit**
```bash
git add resources/js/Pages/Client/PaymentPage.vue
git commit -m "feat: add self-hosted payment page with channel select, countdown, polling"
```

---

### Task 9: Update InvoiceDetail.vue — link to new payment page

**Files:**
- Modify: `resources/js/Pages/Client/InvoiceDetail.vue`

- [ ] **Step 1: Replace redirect-to-duitku with link to payment page**

In `InvoiceDetail.vue`, find the `payNow` function (lines 31-41):

```ts
function payNow() {
    if (!selectedChannel.value || paying.value) return;
    paying.value = true;
    router.post('/client/payment/duitku', {
        invoice_id: props.invoice.id,
        payment_method: selectedChannel.value,
    }, {
        preserveScroll: true,
        onFinish: () => { paying.value = false; },
    });
}
```

Replace with:
```ts
import { router } from '@inertiajs/vue3';

function payNow() {
    if (!selectedChannel.value || paying.value) return;
    router.visit(`/client/invoices/${props.invoice.id}/payment`);
}
```

Also update the "Bayar Sekarang" button in template — remove the routing logic, just link:
```html
<Link
    :href="`/client/invoices/${invoice.id}/payment`"
    class="px-5 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 disabled:opacity-50 cursor-pointer min-h-[44px]"
>
    Bayar Sekarang
</Link>
```

And also remove the channel selection + payNow section from the HTML, since all that happens on the PaymentPage now. Or alternatively keep channel selection but make "Bayar Sekarang" link to payment page. Simpler: keep channel selection on InvoiceDetail for convenience, "Bayar Sekarang" links to PaymentPage with the selected channel as query param or via inertia.

Actually, let's keep it simple: just redirect the existing `payNow` to the new payment page.

Edit `InvoiceDetail.vue`:

1. Remove `import { router }` from imports (keep only what's needed from `@inertiajs/vue3`)
2. Replace `payNow()` function to navigate to payment page
3. Remove `selectedChannel` ref if no longer used
4. Remove `paying` ref if no longer used
5. Remove `payViaDuitku` / `duitku` import

```diff
- import { router } from '@inertiajs/vue3';
```
```ts
function payNow() {
    if (!selectedChannel.value) return;
    window.location.href = `/client/invoices/${props.invoice.id}/payment?channel=${selectedChannel.value}`;
}
```
- [ ] **Step 2: Commit**
```bash
git add resources/js/Pages/Client/InvoiceDetail.vue
git commit -m "refactor: link invoice detail to self-hosted payment page instead of Duitku redirect"
```

---

### Task 10: Review and verify full flow

- [ ] **Step 1: Run migration suite**
```bash
cd /Users/vendywira/Code/ksu/e-koperasi && php artisan migrate
```
Expected: both new migrations run successfully.

- [ ] **Step 2: Run PHP syntax check on all modified files**
```bash
cd /Users/vendywira/Code/ksu/e-koperasi && php artisan route:list --path=client/payment && php -l app/Http/Controllers/Client/PaymentController.php && php -l app/Models/PaymentChannel.php && php -l app/Models/PaymentTransaction.php && php -l app/Console/Commands/BillingExpireTransactions.php
```
Expected: all syntax OK, routes listed.

- [ ] **Step 3: Compile frontend**
```bash
cd /Users/vendywira/Code/ksu/e-koperasi && npm run build 2>&1 | tail -20
```
Expected: build successful.

- [ ] **Step 4: Final commit — run expire command test**
```bash
cd /Users/vendywira/Code/ksu/e-koperasi && php artisan billing:expire-transactions
```
Expected: "Expired 0 transactions." (or count if any were stale)

- [ ] **Step 5: Commit any remaining files**
```bash
git add -A && git commit -m "feat: complete self-hosted payment UI implementation"
```

---

## Rollback Plan (if needed)
```bash
# Revert all migrations
php artisan migrate:rollback --step=2

# Revert all file changes
git log --oneline -10  # find the commits
git revert HEAD~9..HEAD
```

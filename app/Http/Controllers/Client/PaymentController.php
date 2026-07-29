<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentChannel;
use App\Models\PaymentTransaction;
use App\Services\DuitkuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(): Response
    {
        $payments = PaymentTransaction::whereHas('invoice', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'invoice_number' => $t->invoice?->invoice_number,
                'amount' => $t->amount,
                'status' => $t->status,
                'channel_name' => $t->channel_name,
                'paid_at' => $t->paid_at?->format('d M Y H:i'),
                'created_at' => $t->created_at->format('d M Y'),
            ]);

        return Inertia::render('Client/Payments', [
            'payments' => $payments,
        ]);
    }

    public function show(string $id): Response
    {
        $transaction = PaymentTransaction::where('id', $id)
            ->whereHas('invoice', fn($q) => $q->where('user_id', auth()->id()))
            ->with('invoice')
            ->firstOrFail();

        return Inertia::render('Client/PaymentDetail', [
            'transaction' => [
                'id' => $transaction->id,
                'invoice_number' => $transaction->invoice?->invoice_number,
                'invoice_id' => $transaction->invoice_id,
                'amount' => $transaction->amount,
                'status' => $transaction->status,
                'channel_name' => $transaction->channel_name,
                'duitku_ref' => $transaction->duitku_ref,
                'paid_at' => $transaction->paid_at?->format('d M Y H:i'),
                'expiry' => $transaction->expiry?->format('d M Y H:i'),
                'created_at' => $transaction->created_at->format('d M Y H:i'),
            ],
        ]);
    }

    public function showPaymentPage(string $invoiceId): Response
    {
        $invoice = Invoice::with('invoiceItems')
            ->where('user_id', auth()->id())
            ->findOrFail($invoiceId);

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

        $groupedChannels = ['va' => [], 'qris' => [], 'ewallet' => [], 'retail' => []];
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
            'groupedChannels' => $groupedChannels,
            'existingTransaction' => $existingTransaction ? [
                'id' => $existingTransaction->id,
                'status' => $existingTransaction->status,
                'expiry' => $existingTransaction->expiry?->toIso8601String(),
                'channel_code' => $existingTransaction->channel_code,
            ] : null,
        ]);
    }

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
            ->firstOrFail();

        return response()->json([
            'status' => $transaction->status,
            'expiry' => $transaction->expiry?->toIso8601String(),
            'paid_at' => $transaction->paid_at?->toIso8601String(),
        ]);
    }

    public function payViaDuitku(Request $request, DuitkuService $duitku): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'payment_method' => 'required|string',
        ]);

        $invoice = Invoice::where('id', $validated['invoice_id'])
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->firstOrFail();

        try {
            $result = $duitku->createInvoice(
                $invoice,
                $validated['payment_method'],
                auth()->user()->name,
                auth()->user()->email
            );

            $invoice->update([
                'payment_channel' => $validated['payment_method'],
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'redirect_url' => $result['redirectUrl'] ?? null,
                    'payment_url' => $result['paymentUrl'] ?? null,
                    'va_number' => $result['vaNumber'] ?? null,
                    'reference' => $result['reference'] ?? null,
                ]);
            }

            if (!empty($result['redirectUrl'])) {
                return redirect()->away($result['redirectUrl']);
            }
            if (!empty($result['paymentUrl'])) {
                return redirect()->away($result['paymentUrl']);
            }

            return redirect()->back()->with('success', 'Pembayaran berhasil dibuat.');
        } catch (\RuntimeException $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function getPaymentInstructions(string $methodCode): string
    {
        $methodCode = strtolower($methodCode);

        if (str_contains($methodCode, 'va')) {
            $bankName = match (true) {
                str_contains($methodCode, 'bri') => 'BRI',
                str_contains($methodCode, 'bni') => 'BNI',
                str_contains($methodCode, 'mandiri'), str_contains($methodCode, 'm1') => 'Mandiri',
                str_contains($methodCode, 'bca'), str_contains($methodCode, 'm2') => 'BCA',
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
}

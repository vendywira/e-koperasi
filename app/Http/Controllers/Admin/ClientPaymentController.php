<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use App\Services\NotificationService;
use App\Services\SiteConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientPaymentController extends Controller
{
    public function store(Request $request, int $subscriptionId): RedirectResponse
    {
        $subscription = Subscription::findOrFail($subscriptionId);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:paid,pending,failed',
            'paid_at' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'receipt_number' => 'nullable|string|max:50',
        ]);

        if (empty($validated['receipt_number'])) {
            $month = date('Ym');
            $lastSeq = Invoice::where('invoice_number', 'like', "INV-{$month}-%")
                ->orderByRaw('CAST(SUBSTRING(invoice_number, -4) AS UNSIGNED) DESC')
                ->value('invoice_number');
            $seq = $lastSeq ? (int) substr($lastSeq, -4) + 1 : 1;
            $receiptNumber = sprintf('INV-%s-%04d', $month, $seq);
        } else {
            $receiptNumber = $validated['receipt_number'];
        }

        // Find existing invoice or create placeholder
        $invoice = Invoice::firstOrCreate(
            [
                'user_id' => $subscription->user_id,
                'status' => 'pending',
                'total_amount' => (int) $validated['amount'],
            ],
            [
                'tenant_request_id' => $subscription->tenant_id,
                'tenant_id' => $subscription->tenant_id,
                'requested_by' => $subscription->user_id,
                'name' => $subscription->user?->name ?? 'Manual',
                'domain' => $subscription->tenant?->domain ?? 'manual',
                'resort_count' => $subscription->max_resorts ?? 1,
                'price_per_resort' => $subscription->price_per_resort ?? 0,
                'months' => 1,
                'subtotal' => (int) $validated['amount'],
                'discount_amount' => 0,
                'due_date' => now()->addDays(14),
                'invoice_number' => $receiptNumber,
            ]
        );

        $payment = PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'amount' => (int) $validated['amount'],
            'base_amount' => (int) $validated['amount'],
            'fee_amount' => 0,
            'status' => $validated['status'] === 'paid' ? PaymentTransaction::STATUS_SUCCESS : $validated['status'],
            'payment_type' => 'manual',
            'payment_method_name' => 'manual_transfer',
            'paid_at' => $validated['status'] === 'paid' ? $validated['paid_at'] : null,
            'notes' => $validated['notes'] ?? null,
            'receipt_number' => $receiptNumber,
        ]);

        $invoice->update(['payment_transaction_id' => $payment->id]);

        // If payment is paid, update subscription
        if ($validated['status'] === 'paid') {
            $subscription->update([
                'status' => 'active',
                'renewed_at' => now(),
                'ends_at' => $subscription->ends_at && $subscription->ends_at->isFuture()
                    ? $subscription->ends_at->addMonth()
                    : now()->addMonth(),
                'started_at' => $subscription->started_at ?? now(),
            ]);

            $invoice->update(['status' => 'paid', 'paid_at' => now()]);

            // Notify client about confirmed payment
            app(NotificationService::class)->send(
                $subscription->user,
                'payment',
                'Pembayaran Dikonfirmasi',
                "Pembayaran {$payment->receipt_number} sebesar Rp" . number_format($payment->amount, 0, ',', '.') . " telah dikonfirmasi.",
                "/client/payments/{$payment->id}",
                $payment
            );
        }

        return redirect()->back()->with('success', "Pembayaran {$receiptNumber} berhasil dicatat.");
    }
}

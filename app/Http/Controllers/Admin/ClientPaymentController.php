<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Payment;
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
        ]);

        // Generate receipt number
        $lastReceipt = Payment::where('receipt_number', 'like', 'INV-' . date('Ym') . '-%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReceipt) {
            $lastNum = (int) substr($lastReceipt->receipt_number, -4);
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }

        $receiptNumber = 'INV-' . date('Ym') . '-' . $newNum;

        $payment = $subscription->payments()->create([
            'amount' => $validated['amount'],
            'status' => $validated['status'],
            'payment_method' => 'manual_transfer',
            'paid_at' => $validated['paid_at'],
            'notes' => $validated['notes'] ?? null,
            'receipt_number' => $receiptNumber,
        ]);

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

            // Notify client about confirmed payment
            app(NotificationService::class)->send(
                $subscription->user,
                'payment',
                'Pembayaran Dikonfirmasi',
                "Pembayaran {$payment->receipt_number} sebesar {$payment->amountFormatted()} telah dikonfirmasi.",
                "/client/payments/{$payment->id}",
                $payment
            );
        }

        return redirect()->back()->with('success', "Pembayaran {$receiptNumber} berhasil dicatat.");
    }
}

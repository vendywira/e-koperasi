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

        // Find invoice for this subscription/user, or create a placeholder
        $invoice = Invoice::where('user_id', $subscription->user_id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$invoice) {
            $invoice = Invoice::create([
                'user_id' => $subscription->user_id,
                'tenant_id' => $subscription->tenant_id,
                'name' => $subscription->user?->name ?? 'Manual Payment',
                'domain' => $subscription->tenant?->domain ?? 'manual',
                'invoice_number' => $validated['receipt_number'] ?? 'MAN-' . now()->format('YmdHis'),
                'total_amount' => $validated['amount'],
                'subtotal' => $validated['amount'],
                'discount_amount' => 0,
                'status' => 'pending',
                'due_date' => now()->addDays(14),
            ]);
        }

        PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'amount' => $validated['amount'],
            'base_amount' => $validated['amount'],
            'fee_amount' => 0,
            'status' => match ($validated['status']) {
                'paid' => PaymentTransaction::STATUS_SUCCESS,
                'pending' => PaymentTransaction::STATUS_PENDING,
                default => PaymentTransaction::STATUS_FAILED,
            },
            'payment_type' => 'manual',
            'payment_method_name' => 'manual_transfer',
            'paid_at' => $validated['status'] === 'paid' ? $validated['paid_at'] : null,
            'notes' => $validated['notes'],
            'receipt_number' => $validated['receipt_number'] ?? null,
        ]);

        if ($validated['status'] === 'paid') {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => $validated['paid_at'],
            ]);
        }

        // Notify
        $notifService = app(NotificationService::class);
        $notifService->send(
            $subscription->user,
            'payment',
            'Pembayaran Manual ' . ($validated['status'] === 'paid' ? 'Dikonfirmasi' : 'Dicatat'),
            "Admin mencatat pembayaran manual sebesar " . number_format($validated['amount'], 0, ',', '.') . " — status: {$validated['status']}",
            '/client/payments',
            $invoice
        );

        return redirect()->back()->with('success', 'Pembayaran manual berhasil dicatat.');
    }
}
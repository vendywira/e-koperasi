<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
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
}
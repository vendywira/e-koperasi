<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use App\Services\BillingService;
use App\Services\DuitkuService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DuitkuController extends Controller
{
    public function __invoke(Request $request, DuitkuService $duitku, BillingService $billing)
    {
        $data = $request->all();
        Log::info('Duitku callback received', $data);

        if (!$duitku->verifyCallback($data)) {
            Log::warning('Duitku callback signature mismatch', $data);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $transaction = PaymentTransaction::find($data['merchantOrderId'] ?? '');
        if (!$transaction) {
            Log::warning('Duitku callback: transaction not found', $data);
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        $statusCode = $data['statusCode'] ?? null;
        $newStatus = match ($statusCode) {
            '00' => 'success',
            '02', '03' => 'failed',
            default => 'pending',
        };

        $transaction->update([
            'status' => $newStatus,
            'paid_at' => $newStatus === 'success' ? now() : $transaction->paid_at,
            'channel_code' => $data['paymentMethod'] ?? $transaction->channel_code,
            'channel_name' => $data['paymentMethod'] ?? $transaction->channel_name,
            'duitku_ref' => $data['reference'] ?? $transaction->duitku_ref,
            'raw_response' => $data,
        ]);

        if ($newStatus === 'success') {
            $invoice = Invoice::find($transaction->invoice_id);
            if ($invoice && $invoice->status === 'pending') {
                $billing->confirmPayment($invoice);

                try {
                    app(NotificationService::class)->send(
                        $invoice->user,
                        'payment',
                        "Pembayaran Diterima — {$invoice->invoice_number}",
                        "Pembayaran invoice {$invoice->invoice_number} sebesar Rp" . number_format($invoice->total_amount, 0, ',', '.') . " telah diterima.",
                        '/client/invoices',
                        $invoice
                    );
                } catch (\Throwable $e) {
                    Log::warning('Failed to send payment notification: ' . $e->getMessage());
                }
            }
        }

        return response()->json(['ok' => true]);
    }
}
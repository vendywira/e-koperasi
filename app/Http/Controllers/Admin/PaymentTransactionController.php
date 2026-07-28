<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Services\DuitkuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentTransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = PaymentTransaction::with('invoice');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return Inertia::render('Admin/PaymentTransactions/Index', [
            'transactions' => $transactions,
            'filters' => ['status' => $request->get('status')],
        ]);
    }

    public function checkStatus(string $id, DuitkuService $duitku): RedirectResponse
    {
        $transaction = PaymentTransaction::findOrFail($id);

        try {
            $status = $duitku->checkStatus($transaction->id);
            $newStatus = match ($status['statusCode'] ?? null) {
                '00' => 'success',
                '02', '03' => 'failed',
                default => $transaction->status,
            };
            $transaction->update(['status' => $newStatus, 'raw_response' => $status]);

            return redirect()->back()->with('success', "Status transaksi: {$newStatus}");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal cek status: ' . $e->getMessage());
        }
    }
}
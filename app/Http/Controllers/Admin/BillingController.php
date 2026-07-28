<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use App\Models\Tenant;
use App\Models\Subscription;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function index(): Response
    {
        $invoices = Invoice::with(['user', 'confirmor'])
            ->orderByRaw("FIELD(status, 'pending', 'paid', 'cancelled')")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_revenue' => Invoice::where('status', 'paid')->sum('total_amount'),
            'pending_amount' => Invoice::where('status', 'pending')->sum('total_amount'),
            'pending_count' => Invoice::where('status', 'pending')->count(),
            'paid_count' => Invoice::where('status', 'paid')->count(),
            'mrr' => Invoice::where('status', 'paid')
                ->where('paid_at', '>=', now()->subMonth())
                ->sum('total_amount'),
        ];

        return Inertia::render('Admin/Billing/Index', [
            'invoices' => $invoices,
            'stats' => $stats,
        ]);
    }

    public function transactionLog(): Response
    {
        $transactions = PaymentTransaction::with('invoice')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Billing/TransactionLog', [
            'transactions' => $transactions,
        ]);
    }
}
<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $subscription = $user->subscription;
        // Pending request yang belum punya invoice
        $invoicedIds = Invoice::whereIn('status', ['pending','paid'])
            ->pluck('tenant_id')->toArray();
        $activeTenantRequest = Tenant::where('requested_by', $user->id)
            ->where('status', 'pending')
            ->whereNotIn('id', $invoicedIds)
            ->latest()
            ->first();

        $recentPayments = $subscription
            ? $subscription->paymentTransactions()
                ->with('invoice')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(fn($t) => [
                    'id' => $t->id,
                    'invoice_id' => $t->invoice_id,
                    'invoice_number' => $t->invoice?->invoice_number,
                    'amount' => (int) $t->amount,
                    'status' => $t->status,
                    'paid_at' => $t->paid_at?->format('d M Y'),
                    'created_at' => $t->created_at->format('d M Y'),
                ])
            : [];

        // Ticket statistics
        $ticketStats = [
            'total' => Ticket::where('user_id', $user->id)->count(),
            'pending' => Ticket::where('user_id', $user->id)->where('status', 'pending')->count(),
            'in_progress' => Ticket::where('user_id', $user->id)->where('status', 'in_progress')->count(),
            'solved' => Ticket::where('user_id', $user->id)->where('status', 'solved')->count(),
            'close' => Ticket::where('user_id', $user->id)->where('status', 'close')->count(),
        ];

        // Pending invoices — unpaid
        $pendingInvoices = Invoice::where('user_id', $user->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($inv) => [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'name' => $inv->name,
                'total_amount' => (int) $inv->total_amount,
                'due_date' => $inv->due_date?->format('d M Y'),
                'created_at' => $inv->created_at->format('d M Y'),
            ]);

        return Inertia::render('Client/Dashboard', [
            'pendingRequest' => $activeTenantRequest,
            'pendingInvoices' => $pendingInvoices,
            'userTenants' => $user->ksuSubscriptions()->with('tenant')->get()->map(fn($s) => [
                'id' => $s->id,
                'tenant_name' => $s->tenant?->name,
                'tenant_domain' => $s->tenant?->domain,
                'status' => $s->tenant?->status ?? $s->status,
            ]),
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'plan' => $subscription->plan,
                'status' => $subscription->status,
                'is_active' => $subscription->isActive(),
                'is_grace' => $subscription->isGrace(),
                'grace_days_remaining' => $subscription->graceDaysRemaining(),
                'grace_ends_at' => $subscription->isGrace() ? $subscription->graceEndsAt()->format('d M Y') : null,
                'started_at' => $subscription->started_at?->format('d M Y'),
                'ends_at' => $subscription->ends_at?->format('d M Y'),
                'days_remaining' => $subscription->daysRemaining(),
                'usage_percent' => $subscription->usagePercent(),
            ] : null,
            'recentPayments' => $recentPayments,
            'ticketStats' => $ticketStats,
            'plans' => \App\Models\Plan::with('features')->active()->get(),
        ]);
    }
}

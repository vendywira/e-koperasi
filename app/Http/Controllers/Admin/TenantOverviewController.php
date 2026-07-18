<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Tenant;
use Inertia\Inertia;
use Inertia\Response;

class TenantOverviewController extends Controller
{
    public function index(): Response
    {
        // Ambil semua tenant, tapi pending yang sudah punya invoice tidak muncul sebagai tenant pending
        $invoicedTenantIds = Invoice::whereIn('status', ['pending', 'paid'])->pluck('tenant_id')->toArray();

        $tenants = Tenant::with('subscription.user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(fn($t) => !($t->status === 'pending' && in_array($t->id, $invoicedTenantIds)))
            ->values()
            ->map(fn($t) => [
                'id' => $t->id,
                'type' => 'tenant',
                'name' => $t->name,
                'domain' => $t->domain,
                'db_name' => $t->db_name,
                'status' => $t->status,
                'max_resorts' => $t->subscription?->max_resorts ?? '-',
                'plan' => $t->subscription?->plan ?? '-',
                'user_name' => $t->subscription?->user?->name,
                'user_email' => $t->subscription?->user?->email,
                'ends_at' => $t->subscription?->ends_at?->format('d M Y'),
                'created_at' => $t->created_at->format('d M Y'),
            ]);

        $invoices = Invoice::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($i) => [
                'id' => $i->id,
                'type' => 'invoice',
                'name' => $i->name,
                'domain' => $i->domain,
                'amount' => $i->total_amount,
                'status' => $i->status,
                'user_name' => $i->user?->name,
                'payment_proof' => $i->payment_proof,
                'created_at' => $i->created_at->format('d M Y'),
            ]);

        $all = collect()->concat($tenants)->concat($invoices)
            ->sortByDesc('created_at')
            ->values();

        return Inertia::render('Admin/TenantOverview/Index', [
            'items' => $all,
            'stats' => [
                'pending_requests' => Tenant::where('status', 'pending')->doesntHave('invoice')->count(),
                'active_tenants' => Tenant::whereIn('status', ['active', 'trialing'])->count(),
                'pending_tenants' => Tenant::where('status', 'pending')->doesntHave('invoice')->count(),
                'pending_invoices' => Invoice::where('status', 'pending')->count(),
                'total_tenants' => Tenant::count(),
            ],
        ]);
    }
}

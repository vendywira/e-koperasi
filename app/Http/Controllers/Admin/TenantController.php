<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Tenant::with('subscription');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%");
            });
        }

        $tenants = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Transform tenant data for display
        $tenants->getCollection()->transform(function ($t) {
            if ($t->subscription) {
                $sub = $t->subscription;
                $t->subscription->days_remaining = $sub->ends_at
                    ? max(0, now()->diffInDays($sub->ends_at, false))
                    : null;

                // Find last payment if any (via subscription model)
                $lastPayment = $sub->payments()->latest()->first();
                $t->subscription->last_payment_status = $lastPayment?->status;
                $t->subscription->last_payment_date = $lastPayment?->paid_at?->format('d M Y');
            }
            return $t;
        });

        return Inertia::render('Admin/Tenant/Index', [
            'tenants' => $tenants,
            'filters' => ['search' => $request->get('search')],
            'stats' => [
                'total' => Tenant::count(),
                'active' => Tenant::where('status', 'active')->count(),
                'suspended' => Tenant::where('status', 'suspended')->count(),
                'trialing' => Tenant::where('status', 'trialing')->count(),
                'expiring_soon' => Tenant::whereHas('subscription', function ($q) {
                    $q->where(function ($q2) {
                        $q2->where('status', 'active')->orWhere('status', 'trialing');
                    })
                        ->whereNotNull('ends_at')
                        ->where('ends_at', '<=', now()->addDays(7));
                })->count(),
                'expired' => Tenant::whereHas('subscription', function ($q) {
                    $q->where('status', 'expired')->orWhere('status', 'cancelled');
                })->count(),
                'total_revenue' => Subscription::where('type', 'ksu')
                    ->whereHas('payments', fn($q) => $q->where('status', 'paid'))
                    ->withSum(['payments as paid_sum' => fn($q) => $q->where('status', 'paid')], 'amount')
                    ->get()->sum('paid_sum'),
            ],
        ]);
    }

    public function create(): Response
    {
        $clients = User::where('role', 'client')->get(['id', 'name', 'email']);
        return Inertia::render('Admin/Tenant/Create', [
            'clients' => $clients,
        ]);
    }

    public function checkDomain(Request $request): \Illuminate\Http\JsonResponse
    {
        $domain = $request->get('domain');
        $taken = Tenant::where('domain', $domain)->exists();
        return response()->json(['taken' => $taken]);
    }

    public function show(string $id): Response
    {
        $tenant = Tenant::with('subscription')->findOrFail($id);

        if ($tenant->subscription && $tenant->subscription->ends_at) {
            $tenant->subscription->days_remaining = max(0, now()->diffInDays($tenant->subscription->ends_at, false));
        }

        // Get last payment
        $lastPayment = $tenant->subscription?->payments()->latest()->first();
        $tenant->subscription->last_payment_status = $lastPayment?->status;
        $tenant->subscription->last_payment_date = $lastPayment?->paid_at?->format('d M Y');

        return Inertia::render('Admin/Tenant/Show', ['tenant' => $tenant]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:100|unique:tenants,domain|regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
            'client_id' => 'required|exists:users,id',
            'max_resorts' => 'required|integer|min:1',
            'price_per_resort' => 'required|numeric|min:0',
            'plan' => 'required|in:monthly,yearly',
            'trial_days' => 'nullable|integer|min:0|max:90',
        ]);

        $startedAt = now();

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'domain' => $validated['domain'],
            'db_name' => 'ksu_tnt_' . str_replace('-', '_', $validated['domain']),
            'status' => 'pending',
        ]);

        $tenant->subscription()->create([
            'user_id' => $validated['client_id'],
            'plan' => $validated['plan'],
            'max_resorts' => $validated['max_resorts'],
            'price_per_resort' => $validated['price_per_resort'],
            'type' => 'ksu',
            'status' => 'pending',
            'started_at' => $startedAt,
            'ends_at' => $startedAt->copy()->addDays(30),
        ]);

        return redirect()->route('admin.tenant.index')
            ->with('success', "Tenant '{$tenant->name}' berhasil dibuat. Status pending — aktifkan setelah pembayaran.");
    }

    public function extend(Request $request, string $id): RedirectResponse
    {
        $tenant = Tenant::findOrFail($id);
        $sub = $tenant->subscription;

        if (!$sub) {
            return redirect()->back()->with('error', 'Tenant belum punya subscription.');
        }

        $validated = $request->validate([
            'extend_days' => 'required|integer|min:1|max:365',
            'notes' => 'nullable|string|max:255',
        ]);

        $extendDays = (int) $validated['extend_days'];
        $currentEnd = $sub->ends_at ? \Carbon\Carbon::parse($sub->ends_at) : now();
        $newEnd = $currentEnd->copy()->addDays($extendDays);

        $sub->update([
            'ends_at' => $newEnd,
            'status' => 'active',
        ]);

        $tenant->update(['status' => 'active']);

        return redirect()->back()->with('success',
            "Subscription '{$tenant->name}' diperpanjang {$extendDays} hari hingga {$newEnd->format('d M Y')}.");
    }

    public function toggleSuspend(string $id): RedirectResponse
    {
        $tenant = Tenant::findOrFail($id);
        $newStatus = $tenant->status === 'suspended' ? 'active' : 'suspended';

        $tenant->update(['status' => $newStatus]);

        if ($tenant->subscription) {
            $tenant->subscription->update([
                'status' => $newStatus === 'suspended' ? 'expired' : 'active',
            ]);
        }

        if ($newStatus === 'suspended') {
            return redirect()->back()->with('success', "Tenant '{$tenant->name}' di-suspend.");
        }
        return redirect()->back()->with('success', "Tenant '{$tenant->name}' diaktifkan kembali.");
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $tenant = Tenant::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:100|unique:tenants,domain,' . $id . '|regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
            'db_name' => 'required|string|max:100|unique:tenants,db_name,' . $id,
            'status' => 'required|in:active,suspended,trialing,pending',
            'max_resorts' => 'required|integer|min:1',
            'price_per_resort' => 'required|numeric|min:0',
            'plan' => 'required|in:monthly,yearly',
            'subscription_status' => 'required|in:active,expired,cancelled,trialing',
        ]);

        $tenant->update([
            'name' => $validated['name'],
            'domain' => $validated['domain'],
            'db_name' => $validated['db_name'],
            'status' => $validated['status'],
        ]);
        $tenant->subscription()->update([
            'plan' => $validated['plan'],
            'max_resorts' => $validated['max_resorts'],
            'price_per_resort' => $validated['price_per_resort'],
            'status' => $validated['subscription_status'],
        ]);

        return redirect()->route('admin.tenant.index')
            ->with('success', "Tenant '{$tenant->name}' berhasil diperbarui.");
    }

    public function destroy(string $id): RedirectResponse
    {
        $tenant = Tenant::findOrFail($id);
        $dbName = $tenant->db_name;
        $domain = $tenant->domain;

        DB::statement("DROP DATABASE IF EXISTS `{$dbName}`");
        $storagePath = storage_path("../ksu-app/storage/app/public/{$domain}");
        if (is_dir($storagePath)) {
            exec("rm -rf {$storagePath}");
        }

        $tenant->subscription()->delete();
        $tenant->delete();

        return redirect()->route('admin.tenant.index')
            ->with('success', "Tenant '{$tenant->name}' berhasil dihapus.");
    }
}

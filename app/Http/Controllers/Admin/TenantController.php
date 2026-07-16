<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
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
        return Inertia::render('Admin/Tenant/Create');
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
            'domain' => 'required|string|max:100|unique:tenants,domain',
            'max_resorts' => 'required|integer|min:1',
            'price_per_resort' => 'required|numeric|min:0',
            'plan' => 'required|in:monthly,yearly',
            'trial_days' => 'nullable|integer|min:0|max:90',
        ]);

        $artisan = base_path('../ksu-app/artisan');
        if (file_exists($artisan)) {
            $cmd = sprintf(
                "php %s tenant:create %s '%s' --max-resorts=%d --price-per-resort=%d --plan=%s 2>&1",
                escapeshellarg($artisan),
                escapeshellarg($validated['domain']),
                escapeshellarg($validated['name']),
                $validated['max_resorts'],
                $validated['price_per_resort'],
                $validated['plan']
            );
            exec($cmd, $output, $exitCode);

            if ($exitCode !== 0) {
                return redirect()->route('admin.tenant.index')
                    ->with('error', 'Gagal: ' . implode("\n", $output));
            }
            return redirect()->route('admin.tenant.index')
                ->with('success', "Tenant '{$validated['name']}' berhasil dibuat.");
        }

        $dbName = 'ksu_tnt_' . $validated['domain'];
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}`");

        $trialDays = $validated['trial_days'] ?? 14;
        $startedAt = now();

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'domain' => $validated['domain'],
            'db_name' => $dbName,
            'status' => $trialDays > 0 ? 'trialing' : 'active',
        ]);

        $tenant->subscription()->create([
            'user_id' => auth()->id(),
            'plan' => $validated['plan'],
            'max_resorts' => $validated['max_resorts'],
            'price_per_resort' => $validated['price_per_resort'],
            'type' => 'ksu',
            'status' => $trialDays > 0 ? 'trialing' : 'active',
            'started_at' => $startedAt,
            'ends_at' => $trialDays > 0 ? $startedAt->copy()->addDays($trialDays) : null,
        ]);

        return redirect()->route('admin.tenant.index')
            ->with('success', "Tenant '{$tenant->name}' berhasil dibuat (trial {$trialDays} hari).");
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
            'domain' => 'required|string|max:100|unique:tenants,domain,' . $id,
            'db_name' => 'required|string|max:100|unique:tenants,db_name,' . $id,
            'status' => 'required|in:active,suspended,trialing',
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

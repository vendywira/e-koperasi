<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class TenantRequestController extends Controller
{
    public function index(): Response
    {
        $pendingTenants = Tenant::with(['requestor', 'subscription'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/TenantRequest/Index', [
            'requests' => $pendingTenants,
        ]);
    }

    public function approve(string $id): RedirectResponse
    {
        $tenant = Tenant::with(['subscription', 'requestor'])->findOrFail($id);

        if ($tenant->status !== 'pending') {
            return redirect()->back()->with('error', 'Tenant sudah diproses.');
        }

        $dbName = $tenant->db_name;

        // 1. Buat database tenant
        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
            Log::error("Gagal create DB tenant {$dbName}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat database: ' . $e->getMessage());
        }

        // 2. Update status tenant & subscription dalam transaction
        DB::transaction(function () use ($tenant) {
            $tenant->update(['status' => 'active']);

            if ($tenant->subscription) {
                $tenant->subscription->update([
                    'status' => 'active',
                    'started_at' => now(),
                    'ends_at' => now()->addMonth(),
                ]);
            }
        });

        // 3. Notify client
        try {
            app(NotificationService::class)->send(
                $tenant->requestor,
                'tenant',
                "Tenant {$tenant->name} Aktif!",
                "Tenant Anda telah diaktifkan. Akses di https://{$tenant->domain}.e-koperasi.com",
                '/client/dashboard',
                $tenant
            );
        } catch (\Throwable $e) {
            Log::warning("Gagal kirim notifikasi tenant {$tenant->domain}: " . $e->getMessage());
        }

        return redirect()->route('admin.tenant-request.index')
            ->with('success', "Tenant '{$tenant->name}' berhasil diaktifkan.");
    }

    public function reject(string $id): RedirectResponse
    {
        $tenant = Tenant::findOrFail($id);

        if ($tenant->status !== 'pending') {
            return redirect()->back()->with('error', 'Tenant sudah diproses.');
        }

        $tenant->update(['status' => 'rejected']);

        return redirect()->route('admin.tenant-request.index')
            ->with('error', "Permintaan tenant '{$tenant->name}' ditolak.");
    }
}

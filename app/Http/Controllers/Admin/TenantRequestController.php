<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantRequest;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TenantRequestController extends Controller
{
    public function index(): Response
    {
        $requests = TenantRequest::with(['user', 'reviewer'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/TenantRequest/Index', [
            'requests' => $requests,
        ]);
    }

    public function approve(string $id): RedirectResponse
    {
        $request = TenantRequest::findOrFail($id);

        if ($request->status !== 'pending') {
            return redirect()->back()->with('error', 'Permintaan sudah diproses.');
        }

        $dbName = 'ksu_tnt_' . $request->domain;
        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal membuat database: ' . $e->getMessage());
        }

        $tenant = Tenant::create([
            'name' => $request->name,
            'domain' => $request->domain,
            'db_name' => $dbName,
            'status' => 'active',
        ]);

        $tenant->subscription()->create([
            'user_id' => $request->user_id,
            'type' => 'ksu',
            'plan' => 'monthly',
            'max_resorts' => $request->max_resorts,
            'price_per_resort' => 100000,
            'status' => 'active',
            'started_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $request->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // Notify the client
        try {
            $notifService = app(NotificationService::class);
            $notifService->send(
                $request->user,
                'tenant',
                "Tenant {$request->name} Aktif!",
                "Tenant Anda telah diaktifkan. Akses di https://{$request->domain}.ksu.app",
                '/client/dashboard',
                $tenant
            );
        } catch (\Throwable $e) {
            // notification non-critical
        }

        return redirect()->route('admin.tenant-request.index')
            ->with('success', "Tenant '{$request->name}' berhasil diaktifkan.");
    }

    public function reject(string $id): RedirectResponse
    {
        $req = TenantRequest::findOrFail($id);

        if ($req->status !== 'pending') {
            return redirect()->back()->with('error', 'Permintaan sudah diproses.');
        }

        $req->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.tenant-request.index')
            ->with('success', "Permintaan tenant '{$req->name}' ditolak.");
    }
}

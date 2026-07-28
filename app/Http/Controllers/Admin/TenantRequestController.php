<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class TenantRequestController extends Controller
{
    public function index(): Response
    {
        $pendingRequests = Tenant::with(['requestor', 'subscription', 'invoice'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'domain' => $t->domain,
                'plan' => $t->subscription?->plan ?? '-',
                'billing_cycle' => $t->subscription?->billing_cycle ?? 'monthly',
                'max_resorts' => $t->subscription?->max_resorts ?? '-',
                'price_per_resort' => $t->subscription?->price_per_resort ?? 0,
                'total_amount' => $t->invoice?->total_amount ?? 0,
                'has_invoice' => $t->invoice ? true : false,
                'invoice_status' => $t->invoice?->status ?? '-',
                'user_name' => $t->requestor?->name,
                'user_email' => $t->requestor?->email,
                'company_address' => $t->company_address,
                'company_phone' => $t->company_phone,
                'notes' => $t->notes,
                'created_at' => $t->created_at->format('d M Y'),
            ]);

        return Inertia::render('Admin/TenantRequest/Index', [
            'requests' => $pendingRequests,
        ]);
    }

    public function approve(string $id): RedirectResponse
    {
        $tenant = Tenant::with(['subscription', 'requestor', 'invoice'])->findOrFail($id);

        if ($tenant->status !== 'pending') {
            return redirect()->back()->with('error', 'Tenant sudah diproses.');
        }

        // Activate invoice
        $invoice = $tenant->invoice;
        if ($invoice && $invoice->status === 'pending') {
            $invoice->update(['status' => 'paid', 'paid_at' => now(), 'confirmed_by' => auth()->id()]);
        }

        $clientUser = $tenant->requestor;
        if (!$clientUser) {
            return redirect()->back()->with('error', 'User requester tidak ditemukan.');
        }

        $provisionFailed = false;

        // 1. Provision via ksu-app API (with company info & logo)
        try {
            $provisionService = app(\App\Services\ProvisionService::class);
            $provisionFailed = !$provisionService->provision($tenant, $clientUser);
        } catch (\Throwable $e) {
            Log::error("Provision tenant {$tenant->domain} error: " . $e->getMessage());
            $provisionFailed = true;
        }

        // 3. Update status tenant & subscription
        if (!$provisionFailed) {
            $tenant->update(['status' => 'active']);
            if ($tenant->subscription) {
                $tenant->subscription->update(['status' => 'active']);
            }
        }

        // 4. Notify client
        try {
            $message = $provisionFailed
                ? "Permintaan tenant {$tenant->name} disetujui, tetapi provisioning gagal. Admin sedang meninjau."
                : "Tenant {$tenant->name} sudah aktif! Anda bisa login ke tenant sekarang.";

            app(NotificationService::class)->send(
                $clientUser,
                'tenant',
                "Tenant {$tenant->name} Diaktifkan!",
                $message,
                '/client/dashboard',
                $tenant
            );
        } catch (\Throwable $e) {
            Log::warning("Gagal kirim notifikasi tenant {$tenant->domain}: " . $e->getMessage());
        }

        $msg = $provisionFailed
            ? "Tenant disetujui, tetapi provisioning gagal. Cek log."
            : "Tenant '{$tenant->name}' berhasil diaktifkan.";

        return redirect()->route('admin.tenant-request.index')
            ->with('success', $msg);
    }

    public function reject(string $id): RedirectResponse
    {
        $tenant = Tenant::findOrFail($id);

        if ($tenant->status !== 'pending') {
            return redirect()->back()->with('error', 'Tenant sudah diproses.');
        }

        $tenant->update(['status' => 'rejected']);

        // Cancel associated invoice
        if ($tenant->invoice && $tenant->invoice->status === 'pending') {
            $tenant->invoice->update(['status' => 'cancelled']);
        }

        return redirect()->route('admin.tenant-request.index')
            ->with('error', "Permintaan tenant '{$tenant->name}' ditolak.");
    }
}
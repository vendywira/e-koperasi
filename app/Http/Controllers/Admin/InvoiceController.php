<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\SiteConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(): Response
    {
        $invoices = Invoice::with(['user', 'confirmor'])
            ->orderByRaw("FIELD(status, 'pending', 'paid', 'cancelled')")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Invoice/Index', [
            'invoices' => $invoices,
        ]);
    }

    public function generate(string $id): RedirectResponse
    {
        $tenant = Tenant::with('subscription')->findOrFail($id);

        if ($tenant->status !== 'pending') {
            return redirect()->back()->with('error', 'Tenant sudah diproses.');
        }

        $billing = SiteConfig::get('billing', []);
        $pricePerResort = $billing['price_per_unit'] ?? 100000;
        $months = 1;
        $maxResorts = $tenant->subscription?->max_resorts ?? 1;
        $userId = $tenant->requested_by ?? $tenant->subscription?->user_id ?? auth()->id();

        Invoice::create([
            'tenant_id' => $tenant->id,
            'user_id' => $userId,
            'name' => $tenant->name,
            'domain' => $tenant->domain,
            'resort_count' => $maxResorts,
            'price_per_resort' => $pricePerResort,
            'months' => $months,
            'total_amount' => $maxResorts * $pricePerResort * $months,
            'status' => 'pending',
        ]);

        return redirect()->route('admin.invoice.index')
            ->with('success', "Invoice untuk '{$tenant->name}' berhasil dibuat.");
    }

    public function confirmPaid(string $id): RedirectResponse
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->status !== 'pending') {
            return redirect()->back()->with('error', 'Invoice sudah diproses.');
        }

        $tenant = Tenant::with('subscription')->find($invoice->tenant_id);
        if (!$tenant) {
            return redirect()->route('admin.invoice.index')
                ->with('error', 'Tenant terkait invoice tidak ditemukan.');
        }

        $clientUser = User::find($invoice->user_id);
        if (!$clientUser) {
            return redirect()->route('admin.invoice.index')
                ->with('error', 'User client tidak ditemukan.');
        }

        $dbName = $tenant->db_name;
        $provisionFailed = false;

        // 1. Create database
        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
            Log::error("Gagal create DB tenant {$dbName}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat database tenant: ' . $e->getMessage());
        }

        // 2. Provision via ksu-app API (migrate + seed + admin user)
        try {
            $ksuApiUrl = env('KSU_API_URL', config('app.url'));
            $response = Http::timeout(180)->post("{$ksuApiUrl}/api/tenants/{$tenant->domain}/provision", [
                'user' => [
                    'name' => $clientUser->name,
                    'email' => $clientUser->email,
                    'password' => $clientUser->password,
                ],
            ]);

            if (!$response->successful()) {
                Log::warning("Provision tenant {$tenant->domain} gagal: " . $response->body());
                $provisionFailed = true;
            }
        } catch (\Throwable $e) {
            Log::error("Provision tenant {$tenant->domain} error: " . $e->getMessage());
            $provisionFailed = true;
        }

        // 3. Update invoice, tenant, subscription dalam 1 DB transaction
        DB::transaction(function () use ($invoice, $tenant, $provisionFailed) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'confirmed_by' => auth()->id(),
            ]);

            if (!$provisionFailed) {
                $tenant->update(['status' => 'active']);
                if ($tenant->subscription) {
                    $tenant->subscription->update([
                        'status' => 'active',
                        'started_at' => now(),
                        'ends_at' => now()->addMonth(),
                    ]);
                }
            }
        });

        // 5. Notify client (di luar transaction — tidak kritis)
        if (!$provisionFailed) {
            try {
                app(NotificationService::class)->send(
                    $clientUser,
                    'tenant',
                    "Tenant {$tenant->name} Aktif!",
                    "Tenant Anda telah diaktifkan. Login di https://{$tenant->domain}.e-koperasi.com dengan email yang sama.",
                    '/client/dashboard',
                    $tenant
                );
            } catch (\Throwable $e) {}
        }

        $message = $provisionFailed
            ? "Pembayaran dikonfirmasi, tetapi provisioning tenant gagal. Cek log untuk detail."
            : "Pembayaran dikonfirmasi. Tenant '{$tenant->name}' sudah aktif.";

        return redirect()->route('admin.invoice.index')->with('success', $message);
    }

    // ── Client: upload payment proof ──

    public function uploadProof(Request $request, string $id): RedirectResponse
    {
        $invoice = Invoice::where('id', $id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->firstOrFail();

        $request->validate([
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $path = $request->file('payment_proof')->store('payment-proofs', 'public');

        $invoice->update(['payment_proof' => $path]);

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diupload.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BillingCycle;
use App\Models\Invoice;
use App\Models\PaymentChannel;
use App\Models\Tenant;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\SiteConfig;
use App\Services\ProvisionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    // ── Shared: index (client: own, admin: all) ──

    public function index(): Response
    {
        $user = auth()->user();

        $invoices = Invoice::with('invoiceItems', 'paymentTransactions', 'tenant', 'subscription')
            ->when($user->role !== 'admin', fn($q) => $q->where('user_id', $user->id))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($inv) => $inv->toResourceData());

        $paymentChannels = PaymentChannel::active()->get()->map(fn($ch) => [
            'id' => $ch->id,
            'code' => $ch->code,
            'name' => $ch->name,
            'type' => $ch->type,
        ]);

        $view = $user->role === 'admin' ? 'Admin/Invoice/Index' : 'Client/Invoices';


        return Inertia::render($view, [
            'invoices' => $invoices,
            'paymentChannels' => $paymentChannels,
        ]);
    }

    // ── Shared: show ──

    public function show(string $id): Response
    {
        $user = auth()->user();

        $invoice = Invoice::with('invoiceItems', 'paymentTransactions', 'tenant', 'subscription', 'user', 'confirmor')
            ->when($user->role !== 'admin', fn($q) => $q->where('user_id', $user->id))
            ->findOrFail($id);

        

        $view = $user->role === 'admin' ? 'Admin/Invoice/Show' : 'Client/InvoiceDetail';

        return Inertia::render($view, [
            'invoice' => $invoice->toResourceData(),
            'paymentChannels' => PaymentChannel::active()->get()->map(fn($ch) => [
                'id' => $ch->id,
                'code' => $ch->code,
                'name' => $ch->name,
                'type' => $ch->type,
            ]),
        ]);
    }

    // ── Shared: cancel pending invoice (client owns it, admin can cancel any) ──

    public function cancel(string $id): RedirectResponse
    {
        $user = auth()->user();

        $invoice = Invoice::where('id', $id)
            ->when($user->role !== 'admin', fn($q) => $q->where('user_id', $user->id))
            ->where('status', 'pending')
            ->firstOrFail();

        // Cancel pending payment transactions for this invoice too
        $invoice->paymentTransactions()->where('status', 'pending')->update(['status' => 'cancelled']);

        $invoice->update(['status' => 'cancelled']);

        $back = $user->role === 'admin' ? 'admin.invoice.index' : 'client.invoices';
        return redirect()->route($back)->with('success', 'Invoice dibatalkan.');
    }

    // ── Admin: generate invoice for tenant ──

    public function generate(string $id): RedirectResponse
    {
        $this->authorizeAdmin();

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

    // ── Admin: confirm payment + provision ──

    public function confirmPaid(string $id): RedirectResponse
    {
        $this->authorizeAdmin();

        $invoice = Invoice::findOrFail($id);

        if ($invoice->status !== 'pending') {
            return redirect()->back()->with('error', 'Invoice sudah diproses.');
        }

        $tenant = Tenant::with('subscription')->find($invoice->tenant_id);
        $clientUser = User::find($invoice->user_id);

        $provisionFailed = false;

        // 1. Provision via ksu-app API
        try {
            $provisionFailed = !app(ProvisionService::class)->provision($tenant, $clientUser);
        } catch (\Throwable $e) {
            Log::error("Provision tenant {$tenant->domain} error: " . $e->getMessage());
            $provisionFailed = true;
        }

        // 2. Update invoice, tenant, subscription
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

        // 3. Notify client
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
            ? "Pembayaran dikonfirmasi, tetapi provisioning tenant gagal."
            : "Pembayaran dikonfirmasi. Tenant '{$tenant->name}' sudah aktif.";

        return redirect()->route('admin.invoice.index')->with('success', $message);
    }

    // ── Shared: upload payment proof ──

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

    // ── Shared: download PDF ──

    public function download(string $id)
    {
        $user = auth()->user();

        $invoice = Invoice::with('invoiceItems', 'paymentTransactions', 'tenant', 'subscription')
            ->when($user->role !== 'admin', fn($q) => $q->where('user_id', $user->id))
            ->findOrFail($id);

        $latestPayment = $invoice->paymentTransactions->sortByDesc('created_at')->first();
        $companyName = SiteConfig::get('company.name', 'e-Koperasi');
        $companyLogo = SiteConfig::get('company.logo');

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'planName' => $invoice->subscription?->display_name ?? 'Business',
            'companyName' => $companyName,
            'companyLogo' => $companyLogo,
            'payment' => $latestPayment ? [
                'method' => $latestPayment->channel_name ?? ($latestPayment->payment_type === 'manual' ? 'Transfer Manual' : '-'),
                'payment_type' => $latestPayment->payment_type,
                'amount' => (int) $latestPayment->amount,
                'base_amount' => (int) $latestPayment->base_amount,
                'fee_amount' => (int) $latestPayment->fee_amount,
                'status' => $latestPayment->status,
                'paid_at' => $latestPayment->paid_at,
                'receipt_number' => $latestPayment->receipt_number,
            ] : null,
        ]);

        return response()->make($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="invoice-' . $invoice->invoice_number . '.pdf"',
        ]);
    }

    // ── Helpers ──

    private function authorizeAdmin(): void
    {
        abort_if(auth()->user()->role !== 'admin', 403);
    }
}

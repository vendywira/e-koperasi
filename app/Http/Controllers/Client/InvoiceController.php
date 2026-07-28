<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentChannel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(): Response
    {
        $invoices = Invoice::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($inv) {
                return [
                    'id' => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'name' => $inv->name,
                    'domain' => $inv->domain,
                    'resort_count' => $inv->resort_count,
                    'price_per_resort' => $inv->price_per_resort,
                    'months' => $inv->months,
                    'subtotal' => $inv->subtotal ?? $inv->total_amount,
                    'discount_amount' => $inv->discount_amount ?? 0,
                    'total_amount' => $inv->total_amount,
                    'status' => $inv->status,
                    'payment_proof' => $inv->payment_proof ? asset('storage/' . $inv->payment_proof) : null,
                    'due_date' => $inv->due_date?->format('d M Y'),
                    'paid_at' => $inv->paid_at?->format('d M Y'),
                    'created_at' => $inv->created_at->format('d M Y'),
                ];
            });

        $paymentChannels = PaymentChannel::active()->get()->map(fn($ch) => [
            'id' => $ch->id,
            'code' => $ch->code,
            'name' => $ch->name,
            'type' => $ch->type,
        ]);

        return Inertia::render('Client/Invoices', [
            'invoices' => $invoices,
            'paymentChannels' => $paymentChannels,
        ]);
    }

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

    public function download(string $id)
    {
        $invoice = Invoice::with('invoiceItems')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $companyName = app(\App\Services\SiteConfig::class)->get('company.name', 'e-Koperasi');
        $companyLogo = app(\App\Services\SiteConfig::class)->get('company.logo');

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'companyName' => $companyName,
            'companyLogo' => $companyLogo,
        ]);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}
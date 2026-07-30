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
        $invoices = Invoice::with('invoiceItems', 'paymentTransactions', 'tenant', 'subscription')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($inv) => $inv->toResource());

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

    public function show(string $id): Response
    {
        $invoice = Invoice::with('invoiceItems', 'paymentTransactions', 'tenant', 'subscription')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return Inertia::render('Client/InvoiceDetail', [
            'invoice' => $invoice->toResource(),
            'paymentChannels' => PaymentChannel::active()->get()->map(fn($ch) => [
                'id' => $ch->id,
                'code' => $ch->code,
                'name' => $ch->name,
                'type' => $ch->type,
            ]),
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
        $invoice = Invoice::with('invoiceItems', 'paymentTransactions', 'tenant', 'subscription')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $latestPayment = $invoice->paymentTransactions->sortByDesc('created_at')->first();
        $companyName = app(\App\Services\SiteConfig::class)->get('company.name', 'e-Koperasi');
        $companyLogo = app(\App\Services\SiteConfig::class)->get('company.logo');

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
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
}

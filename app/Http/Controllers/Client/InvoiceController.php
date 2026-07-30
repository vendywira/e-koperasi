<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentChannel;
use App\Models\PaymentTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(): Response
    {
        $invoices = Invoice::with('invoiceItems', 'paymentTransactions')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($inv) {
                $latestTxn = $inv->paymentTransactions->sortByDesc('created_at')->first();
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
                    'payment_method' => $latestTxn?->channel_name,
                    'payment_type' => $latestTxn?->payment_type,
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

    public function show(string $id): Response
    {
        $invoice = Invoice::with('invoiceItems', 'paymentTransactions')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $data = [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'name' => $invoice->name,
            'domain' => $invoice->domain,
            'resort_count' => $invoice->resort_count,
            'price_per_resort' => $invoice->price_per_resort,
            'months' => $invoice->months,
            'subtotal' => $invoice->subtotal ?? $invoice->total_amount,
            'discount_amount' => $invoice->discount_amount ?? 0,
            'total_amount' => $invoice->total_amount,
            'status' => $invoice->status,
            'payment_proof' => $invoice->payment_proof ? asset('storage/' . $invoice->payment_proof) : null,
            'due_date' => $invoice->due_date?->format('d M Y'),
            'paid_at' => $invoice->paid_at?->format('d M Y'),
            'created_at' => $invoice->created_at->format('d M Y'),
            'items' => $invoice->invoiceItems->map(fn($i) => [
                'id' => $i->id,
                'description' => $i->description,
                'quantity' => $i->quantity,
                'unit_price' => $i->unit_price,
                'discount_amount' => $i->discount_amount,
                'total_amount' => $i->total_amount,
            ]),
            'transactions' => $invoice->paymentTransactions->sortByDesc('created_at')->values()->map(fn($t) => [
                'id' => $t->id,
                'amount' => $t->amount,
                'status' => $t->status,
                'payment_type' => $t->payment_type,
                'channel_name' => $t->channel_name,
                'paid_at' => $t->paid_at?->format('d M Y H:i'),
                'expiry' => $t->expiry?->format('d M Y H:i'),
                'notes' => $t->notes,
            ]),
        ];

        return Inertia::render('Client/InvoiceDetail', [
            'invoice' => $data,
            'paymentChannels' => \App\Models\PaymentChannel::active()->get()->map(fn($ch) => [
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
        $invoice = Invoice::with('invoiceItems', 'paymentTransactions')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $companyName = app(\App\Services\SiteConfig::class)->get('company.name', 'e-Koperasi');
        $companyLogo = app(\App\Services\SiteConfig::class)->get('company.logo');

        $latestPayment = $invoice->paymentTransactions->sortByDesc('created_at')->first();

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'companyName' => $companyName,
            'companyLogo' => $companyLogo,
            'payment' => $latestPayment ? [
                'method' => $latestPayment->channel_name ?? ($latestPayment->payment_type === 'manual' ? 'Transfer Manual' : '-'),
                'payment_type' => $latestPayment->payment_type,
                'amount' => $latestPayment->amount,
                'base_amount' => $latestPayment->base_amount,
                'fee_amount' => $latestPayment->fee_amount,
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

<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
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
                    'name' => $inv->name,
                    'domain' => $inv->domain,
                    'resort_count' => $inv->resort_count,
                    'price_per_resort' => $inv->price_per_resort,
                    'months' => $inv->months,
                    'total_amount' => $inv->total_amount,
                    'status' => $inv->status,
                    'payment_proof' => $inv->payment_proof ? asset('storage/' . $inv->payment_proof) : null,
                    'paid_at' => $inv->paid_at?->format('d M Y'),
                    'created_at' => $inv->created_at->format('d M Y'),
                ];
            });

        return Inertia::render('Client/Invoices', [
            'invoices' => $invoices,
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
}

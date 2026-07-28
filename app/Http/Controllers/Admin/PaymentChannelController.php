<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentChannel;
use App\Services\DuitkuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentChannelController extends Controller
{
    public function index(): Response
    {
        $channels = PaymentChannel::orderBy('sort_order')->get();
        return Inertia::render('Admin/PaymentChannels/Index', ['channels' => $channels]);
    }

    public function sync(DuitkuService $duitku): RedirectResponse
    {
        $result = $duitku->syncPaymentChannels();
        return redirect()->back()
            ->with('success', "Payment channels synced: {$result['total']} channels.");
    }

    public function toggle(string $id): RedirectResponse
    {
        $channel = PaymentChannel::findOrFail($id);
        $channel->update(['is_active' => !$channel->is_active]);
        return redirect()->back()
            ->with('success', "Channel '{$channel->name}' " . ($channel->is_active ? 'diaktifkan' : 'dinonaktifkan') . ".");
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:payment_channels,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            PaymentChannel::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return redirect()->back()->with('success', 'Urutan berhasil diperbarui.');
    }
}
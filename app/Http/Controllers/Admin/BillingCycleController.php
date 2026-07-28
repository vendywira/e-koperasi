<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillingCycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingCycleController extends Controller
{
    public function index(): Response
    {
        $cycles = BillingCycle::orderBy('sort_order')->get();
        return Inertia::render('Admin/BillingCycles/Index', ['cycles' => $cycles]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'slug' => 'required|string|max:30|unique:billing_cycles,slug',
            'months' => 'required|integer|min:1|max:60',
            'discount_percent' => 'required|integer|min:0|max:100',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        BillingCycle::create($validated);

        return redirect()->route('admin.billing-cycle.index')
            ->with('success', 'Siklus tagihan berhasil ditambahkan.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $cycle = BillingCycle::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'slug' => 'required|string|max:30|unique:billing_cycles,slug,' . $id,
            'months' => 'required|integer|min:1|max:60',
            'discount_percent' => 'required|integer|min:0|max:100',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $cycle->update($validated);

        return redirect()->route('admin.billing-cycle.index')
            ->with('success', 'Siklus tagihan berhasil diperbarui.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $cycle = BillingCycle::findOrFail($id);
        $name = $cycle->name;
        $cycle->delete();

        return redirect()->route('admin.billing-cycle.index')
            ->with('success', "Siklus '{$name}' berhasil dihapus.");
    }
}
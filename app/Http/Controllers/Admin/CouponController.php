<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CouponController extends Controller
{
    public function index(): Response
    {
        $coupons = Coupon::orderBy('created_at', 'desc')->paginate(20);
        return Inertia::render('Admin/Coupons/Index', ['coupons' => $coupons]);
    }

    public function create(): Response
    {
        $plans = Plan::active()->get(['id', 'name']);
        return Inertia::render('Admin/Coupons/Form', ['coupon' => null, 'plans' => $plans]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'plan_ids' => 'nullable|array',
            'plan_ids.*' => 'exists:plans,id',
            'is_active' => 'boolean',
        ]);

        Coupon::create([
            'code' => strtoupper($validated['code']),
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'max_uses' => $validated['max_uses'],
            'used_count' => 0,
            'valid_from' => $validated['valid_from'],
            'valid_until' => $validated['valid_until'],
            'plan_ids' => $validated['plan_ids'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.coupon.index')
            ->with('success', 'Kupon berhasil dibuat.');
    }

    public function edit(string $id): Response
    {
        $coupon = Coupon::findOrFail($id);
        $plans = Plan::active()->get(['id', 'name']);
        return Inertia::render('Admin/Coupons/Form', ['coupon' => $coupon, 'plans' => $plans]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $coupon = Coupon::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $id,
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'plan_ids' => 'nullable|array',
            'plan_ids.*' => 'exists:plans,id',
            'is_active' => 'boolean',
        ]);

        $coupon->update([
            'code' => strtoupper($validated['code']),
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'max_uses' => $validated['max_uses'],
            'valid_from' => $validated['valid_from'],
            'valid_until' => $validated['valid_until'],
            'plan_ids' => $validated['plan_ids'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.coupon.index')
            ->with('success', 'Kupon berhasil diperbarui.');
    }

    public function destroy(string $id): RedirectResponse
    {
        Coupon::findOrFail($id)->delete();
        return redirect()->route('admin.coupon.index')
            ->with('success', 'Kupon berhasil dihapus.');
    }
}
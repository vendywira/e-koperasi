<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function index(): Response
    {
        $plans = Plan::with('features')->orderBy('sort_order')->get();
        return Inertia::render('Admin/Plans/Index', ['plans' => $plans]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Plans/Form', ['plan' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'type' => 'required|in:trial,business,enterprise',
            'pricing_config' => 'nullable|array',
            'max_resorts' => 'nullable|integer|min:0',
            'price_per_month' => 'nullable|numeric|min:0',
            'trial_days' => 'nullable|integer|min:0|max:90',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'features' => 'nullable|array',
            'features.*.feature_text' => 'required|string|max:255',
        ]);

        $plan = Plan::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'max_resorts' => $validated['max_resorts'],
            'price_per_month' => $validated['price_per_month'],
            'trial_days' => $validated['trial_days'],
            'sort_order' => $validated['sort_order'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if ($features = $validated['features'] ?? []) {
            foreach ($features as $i => $f) {
                $plan->features()->create([
                    'feature_text' => $f['feature_text'],
                    'sort_order' => $i,
                ]);
            }
        }

        return redirect()->route('admin.plan.index')
            ->with('success', "Paket '{$plan->name}' berhasil dibuat.");
    }

    public function edit(string $id): Response
    {
        $plan = Plan::with('features')->findOrFail($id);
        return Inertia::render('Admin/Plans/Form', ['plan' => $plan]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $plan = Plan::with('features')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'type' => 'required|in:trial,business,enterprise',
            'pricing_config' => 'nullable|array',
            'max_resorts' => 'nullable|integer|min:0',
            'price_per_month' => 'nullable|numeric|min:0',
            'trial_days' => 'nullable|integer|min:0|max:90',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'features' => 'nullable|array',
            'features.*.id' => 'nullable|exists:plan_features,id',
            'features.*.feature_text' => 'required|string|max:255',
        ]);

        $plan->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'max_resorts' => $validated['max_resorts'],
            'price_per_month' => $validated['price_per_month'],
            'trial_days' => $validated['trial_days'],
            'sort_order' => $validated['sort_order'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $plan->features()->delete();
        foreach ($validated['features'] ?? [] as $i => $f) {
            $plan->features()->create([
                'feature_text' => $f['feature_text'],
                'sort_order' => $i,
            ]);
        }

        return redirect()->route('admin.plan.index')
            ->with('success', "Paket '{$plan->name}' berhasil diperbarui.");
    }

    public function destroy(string $id): RedirectResponse
    {
        $plan = Plan::findOrFail($id);
        $name = $plan->name;
        $plan->features()->delete();
        $plan->delete();

        return redirect()->route('admin.plan.index')
            ->with('success', "Paket '{$name}' berhasil dihapus.");
    }
}
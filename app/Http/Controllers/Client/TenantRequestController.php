<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class TenantRequestController extends Controller
{
    public function create(): Response
    {
        $existing = Tenant::where('requested_by', auth()->id())
            ->where('status', 'pending')
            ->latest()
            ->first();

        return Inertia::render('Client/RequestTenant', [
            'existingRequest' => $existing,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:100|unique:tenants,domain|regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
            'max_resorts' => 'required|integer|min:1|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $dbName = 'ksu_tnt_' . str_replace('-', '_', $validated['domain']);

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'domain' => $validated['domain'],
            'db_name' => $dbName,
            'notes' => $validated['notes'] ?? null,
            'requested_by' => auth()->id(),
            'status' => 'pending',
        ]);

        // Mapping langsung ke client yang request
        $tenant->subscription()->create([
            'user_id' => auth()->id(),
            'type' => 'ksu',
            'plan' => 'monthly',
            'max_resorts' => $validated['max_resorts'],
            'price_per_resort' => 100000,
            'status' => 'pending',
            'started_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);

        return redirect()->route('client.dashboard')
            ->with('success', 'Permintaan tenant berhasil dikirim. Admin akan memproses dalam 1x24 jam.');
    }
}

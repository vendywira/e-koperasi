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
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email|max:255',
            'logo' => 'nullable|file|image|max:2048',
        ]);

        $dbName = config('database.tenant_prefix', '') . 'tnt_' . str_replace('-', '_', $validated['domain']);

        // Handle logo upload
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('tenants/logos', 'public');
        }

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'domain' => $validated['domain'],
            'db_name' => $dbName,
            'notes' => $validated['notes'] ?? null,
            'company_address' => $validated['company_address'] ?? null,
            'company_phone' => $validated['company_phone'] ?? null,
            'company_email' => $validated['company_email'] ?? null,
            'logo' => $logoPath,
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

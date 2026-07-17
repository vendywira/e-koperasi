<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\TenantRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantRequestController extends Controller
{
    public function create(): Response
    {
        $existing = TenantRequest::where('user_id', auth()->id())
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
            'domain' => 'required|string|max:100|unique:tenants,domain|unique:tenant_requests,domain',
            'max_resorts' => 'required|integer|min:1|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        TenantRequest::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'domain' => $validated['domain'],
            'max_resorts' => $validated['max_resorts'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('client.dashboard')
            ->with('success', 'Permintaan tenant berhasil dikirim. Admin akan memproses dalam 1x24 jam.');
    }
}

<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\BillingService;
use App\Services\SiteConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TenantRequestController extends Controller
{
    public function create(): Response
    {
        $existing = Tenant::where('requested_by', auth()->id())
            ->where('status', 'pending')
            ->latest()
            ->first();

        $plans = Plan::with('features')->active()->get()->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'description' => $p->description,
            'type' => $p->type,
            'max_resorts' => $p->max_resorts,
            'price_per_month' => (int) $p->price_per_month,
            'trial_days' => $p->trial_days,
            'pricing_config' => $p->pricing_config,
            'features' => $p->features->pluck('feature_text'),
        ]);

        return Inertia::render('Client/RequestTenant', [
            'existingRequest' => $existing,
            'plans' => $plans,
        ]);
    }

    public function checkDomain(Request $request): JsonResponse
    {
        $request->validate(['domain' => 'required|string']);

        $taken = Tenant::where('domain', $request->domain)->exists();

        $suggestions = [];
        if ($taken) {
            $base = $request->domain;
            for ($i = 1; count($suggestions) < 3; $i++) {
                $candidate = $base . '-' . $i;
                if (!Tenant::where('domain', $candidate)->exists()) {
                    $suggestions[] = $candidate;
                }
                if ($i > 20) break;
            }
            if (count($suggestions) < 3) {
                $suffixes = ['ksu', 'koperasi', 'online'];
                foreach ($suffixes as $s) {
                    if (count($suggestions) >= 3) break;
                    $candidate = $base . '-' . $s;
                    if (!Tenant::where('domain', $candidate)->exists()) {
                        $suggestions[] = $candidate;
                    }
                }
            }
        }

        return response()->json([
            'available' => !$taken,
            'suggestions' => $suggestions,
        ]);
    }

    public function store(Request $request, BillingService $billing): RedirectResponse
    {
        $planId = $request->input('plan_id');
        $planType = $planId ? Plan::where('id', $planId)->value('type') : null;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:100|unique:tenants,domain|regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
            'plan_id' => 'required|exists:plans,id',
            // Business plan requires at least 1 resort; trial/enterprise default
            'resort_qty' => $planType === 'business'
                ? 'required|integer|min:1'
                : 'nullable|integer|min:1',
            'billing_cycle' => 'nullable|in:monthly,quarterly,semiannual,yearly',
            'notes' => 'nullable|string|max:500',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email|max:255',
            'logo' => 'nullable|file|image|max:2048',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $dbName = config('database.tenant_prefix', '') . 'tnt_' . str_replace('-', '_', $validated['domain']);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('tenants/logos', 'public');
        }

        $result = DB::transaction(function () use ($validated, $plan, $dbName, $logoPath, $billing) {
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

            $isTrial = $plan->type === 'trial';
            $cycle = !empty($validated['billing_cycle']) ? $validated['billing_cycle'] : 'monthly';
            if ($plan->type !== 'business') $cycle = 'monthly';

            $subscription = $tenant->subscription()->create([
                'user_id' => auth()->id(),
                'type' => 'ksu',
                'plan_id' => $plan->id,
                'plan' => $plan->name,
                'billing_cycle' => $cycle,
                'max_resorts' => !empty($validated['resort_qty'])
                    ? (int) $validated['resort_qty']
                    : (int) ($plan->max_resorts ?: 1),
                'price_per_resort' => ($plan->pricing_config['price_per_resort'] ?? ($plan->price_per_month / max(1, $plan->max_resorts))),
                'status' => $isTrial ? 'trialing' : 'pending',
                'started_at' => now(),
                'ends_at' => $isTrial ? now()->addDays($plan->trial_days) : now()->addDays(30),
                'trial_ends_at' => $isTrial ? now()->addDays($plan->trial_days) : null,
            ]);

            $provisionMode = SiteConfig::get('config.provision_mode', 'manual');
            $isAutoProvision = $provisionMode === 'auto';

            // Trial: gratis, gak bikin invoice payable. Ikut provision_mode — manual → pending, auto → aktif + provision.
            if ($isTrial) {
                return ['tenant' => $tenant, 'invoice' => null, 'auto_activate' => $isAutoProvision];
            }

            // Non-trial: generate invoice
            $invoice = $billing->generateInvoice($subscription, null, true);

            return ['tenant' => $tenant, 'invoice' => $invoice, 'auto_provision' => ($isAutoProvision && $invoice)];
        });

        // Auto-provision: lakukan di luar transaction
        if ($result['auto_provision'] ?? false) {
            $tenant = $result['tenant'];
            $subscription = $tenant->subscription;
            $tenant->update(['status' => 'active']);
            $subscription->update(['status' => 'active', 'started_at' => now()]);
            $result['invoice']->update(['status' => 'paid', 'paid_at' => now()]);

            try {
                app(\App\Services\ProvisionService::class)->provision($tenant, auth()->user());
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Auto-provision {$tenant->domain}: " . $e->getMessage());
            }
        }

        // Trial auto: tenant aktif langsung, provision juga
        if ($result['auto_activate'] ?? false) {
            $tenant = $result['tenant'];
            $tenant->update(['status' => 'active']);

            try {
                app(\App\Services\ProvisionService::class)->provision($tenant, auth()->user());
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Auto-provision trial {$tenant->domain}: " . $e->getMessage());
            }

            return redirect()->route('client.subscription')
                ->with('success', "Trial {$plan->name} aktif! Berlaku selama {$plan->trial_days} hari.");
        }

        // Auto-provision
        if ($result['auto_provision'] ?? false) {
            return redirect()->route('client.dashboard')
                ->with('success', "Tenant {$validated['name']} aktif! Infrastruktur sedang dipersiapkan.");
        }

        // Manual: tenant pending, admin approve
        return redirect()->route('client.dashboard')
            ->with('success', "Permintaan tenant {$validated['name']} berhasil dikirim. Admin akan memproses dalam 1x24 jam.");
    }
}
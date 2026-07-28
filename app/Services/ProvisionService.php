<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProvisionService
{
    /**
     * Provision a tenant to ksu-app.
     */
    public function provision(Tenant $tenant, User $user): bool
    {
        $ksuApiUrl = config('services.ksu_app.api_url');
        $provisionUrl = rtrim($ksuApiUrl, '/') . "/api/tenants/{$tenant->domain}/provision";

        $companyLogoUrl = null;
        if ($tenant->logo) {
            try {
                $companyLogoUrl = Storage::disk('public')->url($tenant->logo);
            } catch (\Throwable $e) {
                Log::warning("Gagal generate logo URL: {$e->getMessage()}");
            }
        }

        try {
            $response = Http::timeout(300)->post($provisionUrl, [
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $user->password,
                ],
                'company' => [
                    'name' => $tenant->name,
                    'address' => $tenant->company_address ?? '',
                    'phone' => $tenant->company_phone ?? '',
                    'email' => $tenant->company_email ?? '',
                    'logo_url' => $companyLogoUrl,
                ],
            ]);

            if ($response->successful()) {
                Log::info("Provision {$tenant->domain} sukses");
                return true;
            }

            Log::warning("Provision {$tenant->domain} gagal: " . $response->body());
            return false;
        } catch (\Throwable $e) {
            Log::error("Provision {$tenant->domain} error: {$e->getMessage()}");
            return false;
        }
    }
}
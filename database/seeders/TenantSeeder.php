<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Admin Demo',
                'phone' => '081111111111',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $tenants = [
            [
                'name' => 'Koperasi Tabanan Jaya',
                'domain' => 'tabanan-jaya',
                'max_resorts' => 5,
                'price_per_resort' => 100000,
                'plan' => 'monthly',
                'status' => 'active',
                'sub_status' => 'active',
                'started_at' => now()->subMonths(2),
                'ends_at' => now()->addMonth(),
                'client_email' => 'ksu.tabanan@e-koperasi.com',
            ],
            [
                'name' => 'KSU Mekar Sari',
                'domain' => 'mekar-sari',
                'max_resorts' => 3,
                'price_per_resort' => 100000,
                'plan' => 'yearly',
                'status' => 'active',
                'sub_status' => 'active',
                'started_at' => now()->subMonths(6),
                'ends_at' => now()->addMonths(6),
                'client_email' => 'mekar.sari@e-koperasi.com',
            ],
            [
                'name' => 'Koperasi Dharma Bakti',
                'domain' => 'dharma-bakti',
                'max_resorts' => 10,
                'price_per_resort' => 100000,
                'plan' => 'monthly',
                'status' => 'trialing',
                'sub_status' => 'trialing',
                'started_at' => now(),
                'ends_at' => now()->addDays(14),
                'client_email' => 'dharma.bakti@e-koperasi.com',
            ],
            [
                'name' => 'KSU Sejahtera Bersama',
                'domain' => 'sejahtera',
                'max_resorts' => 2,
                'price_per_resort' => 100000,
                'plan' => 'monthly',
                'status' => 'active',
                'sub_status' => 'active',
                'started_at' => now()->subDays(10),
                'ends_at' => now()->addDays(20),
                'client_email' => 'sejahtera@e-koperasi.com',
            ],
        ];

        foreach ($tenants as $data) {
            $dbName = 'ksu_tnt_' . $data['domain'];

            try {
                DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            } catch (\Throwable $e) {
                // Skip if no permission
            }

            $tenant = Tenant::create([
                'name' => $data['name'],
                'domain' => $data['domain'],
                'db_name' => $dbName,
                'status' => $data['status'],
            ]);

            // Cari client user berdasarkan email, atau buat baru
            $client = User::firstOrCreate(
                ['email' => $data['client_email']],
                [
                    'name' => $data['name'],
                    'phone' => '08' . random_int(1000000000, 9999999999),
                    'password' => Hash::make('password'),
                    'role' => 'client',
                ]
            );

            Subscription::create([
                'user_id' => $client->id,
                'tenant_id' => $tenant->id,
                'type' => 'ksu',
                'plan' => $data['plan'],
                'max_resorts' => $data['max_resorts'],
                'price_per_resort' => $data['price_per_resort'],
                'status' => $data['sub_status'],
                'started_at' => $data['started_at'],
                'ends_at' => $data['ends_at'],
            ]);

            $this->command?->info("  Tenant: {$data['name']} → client: {$data['client_email']}");
        }
    }
}

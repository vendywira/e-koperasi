<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Trial',
                'description' => 'Coba gratis selama 30 hari, 1 resort. Auto-convert ke Business setelah trial.',
                'type' => 'trial',
                'pricing_config' => ['price' => 0, 'has_cycle' => false],
                'max_resorts' => 1,
                'price_per_month' => 0,
                'trial_days' => 30,
                'sort_order' => 0,
                'features' => [
                    '1 resort / koperasi',
                    'Simpan pinjam & simpanan',
                    'Dashboard & laporan dasar',
                    'Management anggota',
                    'Support via ticket',
                ],
            ],
            [
                'name' => 'Business',
                'description' => 'Paket untuk koperasi aktif. Bayar per resort per bulan.',
                'type' => 'business',
                'pricing_config' => ['price_per_resort' => 100000, 'has_cycle' => true],
                'max_resorts' => 999,
                'price_per_month' => 100000,
                'trial_days' => 0,
                'sort_order' => 1,
                'features' => [
                    'Unlimited anggota & transaksi',
                    'Laporan keuangan lengkap',
                    'Mobile app untuk anggota',
                    'Backup otomatis',
                    'Multi-resort (tambah sesuai kebutuhan)',
                    'Prioritas support',
                ],
            ],
            [
                'name' => 'Enterprise',
                'description' => 'Solusi on-premise untuk koperasi besar. Server dikelola sendiri.',
                'type' => 'enterprise',
                'pricing_config' => ['price' => 20000000, 'has_cycle' => false, 'unlimited' => true],
                'max_resorts' => 0,
                'price_per_month' => 20000000,
                'trial_days' => 0,
                'sort_order' => 2,
                'features' => [
                    'Unlimited resort & anggota',
                    'Server on-premise (dikelola client)',
                    'Seluruh fitur Business',
                    'Kustomisasi & prioritas tinggi',
                    'Dedicated support & SLA',
                    'Request fitur tambahan (di-charge terpisah)',
                ],
            ],
        ];

        foreach ($plans as $data) {
            $features = $data['features'];
            unset($data['features']);

            $plan = Plan::firstOrCreate(
                ['name' => $data['name']],
                $data
            );

            // Only seed features if this is a new plan or force update
            if ($plan->wasRecentlyCreated) {
                foreach ($features as $i => $text) {
                    $plan->features()->create([
                        'feature_text' => $text,
                        'sort_order' => $i,
                    ]);
                }
            }
        }
    }
}

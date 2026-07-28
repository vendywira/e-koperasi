<?php

namespace Database\Seeders;

use App\Models\BillingCycle;
use Illuminate\Database\Seeder;

class BillingCycleSeeder extends Seeder
{
    public function run(): void
    {
        $cycles = [
            ['name' => 'Bulanan', 'slug' => 'monthly', 'months' => 1, 'discount_percent' => 0, 'sort_order' => 0],
            ['name' => '3 Bulan', 'slug' => 'quarterly', 'months' => 3, 'discount_percent' => 5, 'sort_order' => 1],
            ['name' => '6 Bulan', 'slug' => 'semiannual', 'months' => 6, 'discount_percent' => 10, 'sort_order' => 2],
            ['name' => '12 Bulan', 'slug' => 'yearly', 'months' => 12, 'discount_percent' => 20, 'sort_order' => 3],
        ];

        foreach ($cycles as $c) {
            BillingCycle::updateOrCreate(['slug' => $c['slug']], $c);
        }
    }
}

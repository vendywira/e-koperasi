<?php

namespace Database\Seeders;

use App\Models\SiteContent;
use Illuminate\Database\Seeder;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        SiteContent::updateOrCreate(['section' => 'billing'], [
            'value' => [
                'unit' => 'resort',
                'unit_label' => 'Resort',
                'price_per_unit' => 100000,
                'currency' => 'IDR',
                'available_units' => [
                    ['key' => 'resort', 'label' => 'Resort'],
                    ['key' => 'unit', 'label' => 'Unit (cabang)'],
                    ['key' => 'nasabah', 'label' => 'Nasabah'],
                    ['key' => 'user', 'label' => 'User (pengguna)'],
                ],
                'notes' => 'Harga per unit per bulan. Admin bisa mengganti jenis unit billing kapan saja.',
            ],
        ]);
    }
}

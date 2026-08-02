<?php

namespace Database\Seeders;

use App\Models\SiteContent;
use Illuminate\Database\Seeder;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        SiteContent::updateOrCreate(['section' => 'config'], [
            'value' => [
                'provision_mode' => 'manual', // auto | manual
            ],
        ]);
    }
}

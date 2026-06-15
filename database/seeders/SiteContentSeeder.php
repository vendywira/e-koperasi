<?php

namespace Database\Seeders;

use App\Models\SiteContent;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $config = config('site', []);

        foreach ($config as $section => $value) {
            if (is_array($value)) {
                SiteContent::updateOrCreate(
                    ['section' => $section],
                    ['value' => $value]
                );
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'admin@e-koperasi.com',
            'password' => Hash::make('root'),
        ]);

        $this->call([
            SiteContentSeeder::class,
            ClientSeeder::class,
            TenantSeeder::class,
            BillingSeeder::class,
            TicketSeeder::class,
        ]);
    }
}

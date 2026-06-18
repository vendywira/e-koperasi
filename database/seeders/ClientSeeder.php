<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'name' => 'KSU Tabanan Jaya',
                'email' => 'ksu.tabanan@e-koperasi.com',
                'phone' => '081234567890',
            ],
            [
                'name' => 'Koperasi Mekar Sari',
                'email' => 'mekar.sari@e-koperasi.com',
                'phone' => '081234567891',
            ],
            [
                'name' => 'KSU Dharma Bakti',
                'email' => 'dharma.bakti@e-koperasi.com',
                'phone' => '081234567892',
            ],
            [
                'name' => 'Koperasi Sejahtera Bersama',
                'email' => 'sejahtera@e-koperasi.com',
                'phone' => '081234567893',
            ],
            [
                'name' => 'KSU Sinar Nusantara',
                'email' => 'sinar.nusantara@e-koperasi.com',
                'phone' => '081234567894',
                'plan' => 'starter',
                'status' => 'expired',
            ],
        ];

        foreach ($clients as $data) {
            $plan = $data['plan'] ?? 'premium';
            $status = $data['status'] ?? 'active';

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make('password'),
                'role' => 'client',
            ]);

            $startedAt = now()->subMonths(6);
            $endsAt = $status === 'active' ? now()->addMonths(6) : now()->subMonth();

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan' => $plan,
                'status' => $status,
                'started_at' => $startedAt,
                'ends_at' => $endsAt,
                'renewed_at' => $startedAt,
            ]);

            // Create payment history (6 months)
            for ($i = 5; $i >= 0; $i--) {
                $paidAt = now()->subMonths($i);

                $price = match ($plan) {
                    'starter' => 499000,
                    'enterprise' => 5000000,
                    default => 1500000,
                };

                $paymentStatus = $i === 0 && $status === 'expired' ? 'pending' : 'paid';

                Payment::create([
                    'subscription_id' => $subscription->id,
                    'amount' => $price,
                    'status' => $paymentStatus,
                    'payment_method' => 'manual_transfer',
                    'paid_at' => $paymentStatus === 'paid' ? $paidAt : null,
                    'notes' => $paymentStatus === 'pending' ? 'Menunggu konfirmasi pembayaran' : null,
                    'receipt_number' => 'INV-' . $paidAt->format('Ym') . '-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                ]);
            }
        }
    }
}

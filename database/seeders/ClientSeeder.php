<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    use WithoutModelEvents;

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
            // Expired within grace period (ends_at 3 days ago, within 7-day grace)
            [
                'name' => 'KSU Mitra Abadi',
                'email' => 'mitra.abadi@e-koperasi.com',
                'phone' => '081234567895',
                'plan' => 'premium',
                'status' => 'expired',
                'ends_at' => 'grace', // special marker
            ],
            // Expired past grace period (ends_at 10 days ago, tenant should be suspended)
            [
                'name' => 'Koperasi Buana Lestari',
                'email' => 'buana.lestari@e-koperasi.com',
                'phone' => '081234567896',
                'plan' => 'starter',
                'status' => 'expired',
                'ends_at' => 'past-grace', // special marker
            ],
        ];

        foreach ($clients as $data) {
            $plan = $data['plan'] ?? 'premium';
            $status = $data['status'] ?? 'active';

            // Skip existing users (idempotent)
            if (User::where('email', $data['email'])->exists()) continue;

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make('password'),
                'role' => 'client',
            ]);

            $startedAt = now()->subMonths(6);
            $endsAt = match ($data['ends_at'] ?? $status) {
                'grace' => now()->subDays(3),      // in grace period
                'past-grace' => now()->subDays(10), // past grace → tenant suspended
                'active' => now()->addMonths(6),
                default => now()->subMonth(),        // expired
            };

            // Create tenant for this subscription (needed for renewal provisioning)
            $slug = \Illuminate\Support\Str::slug($user->name);
            $tenant = \App\Models\Tenant::firstOrCreate(
                ['domain' => $slug],
                [
                    'name' => $user->name,
                    'domain' => $slug,
                    'db_name' => str_replace('-', '_', $slug) . '_db',
                    'status' => ($data['ends_at'] ?? null) === 'past-grace' ? 'suspended' : 'active',
                    'requested_by' => $user->id,
                ]
            );

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'tenant_id' => $tenant->id,
                'plan' => $plan,
                'status' => $status,
                'started_at' => $startedAt,
                'ends_at' => $endsAt,
                'renewed_at' => $startedAt,
            ]);

            // Create payment history (6 months) via Invoice + PaymentTransaction
            for ($i = 5; $i >= 0; $i--) {
                $paidAt = now()->subMonths($i);

                $price = match ($plan) {
                    'starter' => 499000,
                    'enterprise' => 5000000,
                    default => 1500000,
                };

                $paymentStatus = $i === 0 && $status === 'expired' ? 'pending' : 'paid';

                $receiptNumber = 'INV-' . $paidAt->format('Ym') . '-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);

                // Create invoice first (idempotent — skip if invoice_number exists)
                $invoice = Invoice::firstOrCreate(
                    ['invoice_number' => $receiptNumber],
                    [
                        'user_id' => $user->id,
                        'tenant_id' => $subscription->tenant_id,
                        'status' => $paymentStatus === 'paid' ? 'paid' : 'pending',
                        'total_amount' => $price,
                        'name' => $user->name,
                        'domain' => $subscription->tenant?->domain ?? 'manual',
                        'due_date' => $paidAt->copy()->addDays(14),
                        'paid_at' => $paymentStatus === 'paid' ? $paidAt : null,
                        'resort_count' => $subscription->max_resorts ?? 1,
                        'price_per_resort' => $subscription->price_per_resort ?? $price,
                        'months' => 1,
                        'subtotal' => $price,
                        'discount_amount' => 0,
                    ]
                );
                if (!$invoice->wasRecentlyCreated) continue; // already seeded

                PaymentTransaction::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $price,
                    'base_amount' => $price,
                    'fee_amount' => 0,
                    'status' => $paymentStatus === 'paid' ? PaymentTransaction::STATUS_SUCCESS : PaymentTransaction::STATUS_PENDING,
                    'payment_type' => 'manual',
                    'payment_method_name' => 'manual_transfer',
                    'paid_at' => $paymentStatus === 'paid' ? $paidAt : null,
                    'notes' => $paymentStatus === 'pending' ? 'Menunggu konfirmasi pembayaran' : null,
                    'receipt_number' => $receiptNumber,
                ]);
            }
        }
    }
}
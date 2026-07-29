<?php

use App\Models\Payment;
use App\Models\PaymentTransaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add new columns to payment_transactions
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->string('payment_type', 20)->default('gateway')->after('raw_response');
            $table->string('payment_method_name', 100)->nullable()->after('payment_type');
            $table->text('notes')->nullable()->after('payment_method_name');
            $table->text('payment_proof')->nullable()->after('notes');
            $table->uuid('confirmed_by')->nullable()->after('payment_proof');
        });

        // 2. Migrate existing Payment records into payment_transactions
        Payment::chunk(100, function ($payments) {
            foreach ($payments as $p) {
                // Find the matching invoice via subscription->user
                $sub = $p->subscription;
                if (!$sub) continue;

                $invoice = \App\Models\Invoice::where('user_id', $sub->user_id)
                    ->where('total_amount', (int) $p->amount)
                    ->where('status', $p->status === 'paid' ? 'paid' : 'pending')
                    ->latest()
                    ->first();

                // Avoid duplicates
                $exists = PaymentTransaction::where('invoice_id', $invoice?->id)
                    ->where('amount', (int) $p->amount)
                    ->where('status', $p->status === 'paid' ? 'success' : 'pending')
                    ->exists();

                if (!$invoice || $exists) continue;

                PaymentTransaction::create([
                    'invoice_id' => $invoice->id,
                    'amount' => (int) $p->amount,
                    'base_amount' => (int) $p->amount,
                    'fee_amount' => 0,
                    'channel_code' => 'manual',
                    'channel_name' => $p->payment_method === 'manual_transfer' ? 'Transfer Manual' : $p->payment_method,
                    'payment_type' => 'manual',
                    'payment_method_name' => $p->payment_method === 'manual_transfer' ? 'Transfer Manual' : $p->payment_method,
                    'status' => $p->status === 'paid' ? 'success' : 'pending',
                    'paid_at' => $p->paid_at,
                    'notes' => $p->notes,
                    'receipt_number' => $p->receipt_number,
                    'created_at' => $p->created_at,
                    'updated_at' => $p->updated_at,
                ]);
            }
        });

        // 3. Add FK for confirmed_by
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->foreign('confirmed_by')->references('id')->on('users')->nullOnDelete();
        });

        // 4. Drop payments table
        Schema::dropIfExists('payments');
    }

    public function down(): void
    {
        // Recreate payments table
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 0);
            $table->string('status')->default('paid');
            $table->string('payment_method')->default('manual_transfer');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('receipt_number')->nullable();
            $table->timestamps();
        });

        // Restore from payment_transactions where payment_type = 'manual'
        PaymentTransaction::where('payment_type', 'manual')->chunk(100, function ($txns) {
            foreach ($txns as $t) {
                $sub = \App\Models\Subscription::where('user_id', $t->invoice?->user_id)->first();
                if (!$sub) continue;
                Payment::create([
                    'subscription_id' => $sub->id,
                    'amount' => $t->amount,
                    'status' => $t->status === 'success' ? 'paid' : $t->status,
                    'payment_method' => $t->channel_code ?? 'manual_transfer',
                    'paid_at' => $t->paid_at,
                    'notes' => $t->notes,
                    'receipt_number' => $t->receipt_number ?? $t->invoice?->invoice_number,
                ]);
            }
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropColumn([
                'payment_type', 'payment_method_name',
                'notes', 'payment_proof', 'confirmed_by',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('invoice_number', 30)->nullable()->after('id');
            $table->decimal('subtotal', 12, 2)->nullable()->after('total_amount');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('subtotal');
            $table->uuid('coupon_id')->nullable()->after('discount_amount');
            $table->timestamp('due_date')->nullable()->after('status');
            $table->string('payment_channel', 50)->nullable()->after('due_date');
            $table->uuid('payment_transaction_id')->nullable()->after('payment_channel');
            $table->unique('invoice_number');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['invoice_number']);
            $table->dropColumn([
                'invoice_number', 'subtotal', 'discount_amount', 'coupon_id',
                'due_date', 'payment_channel', 'payment_transaction_id',
            ]);
        });
    }
};
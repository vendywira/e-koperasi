<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->uuid('plan_id')->nullable()->after('tenant_id');
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semiannual', 'yearly'])
                ->default('monthly')->after('price_per_resort');
            $table->unsignedInteger('grace_period_days')->default(7)->after('billing_cycle');
            $table->timestamp('cancelled_at')->nullable()->after('renewed_at');
            $table->index('plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['plan_id', 'billing_cycle', 'grace_period_days', 'cancelled_at']);
        });
    }
};
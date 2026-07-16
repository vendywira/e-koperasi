<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->uuid('tenant_id')->nullable()->unique()->after('user_id');
            $table->string('type', 20)->default('client')->after('tenant_id'); // client | ksu
            $table->integer('max_resorts')->nullable()->after('plan');
            $table->decimal('price_per_resort', 12, 2)->nullable()->after('max_resorts');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['tenant_id', 'type', 'max_resorts', 'price_per_resort']);
        });
    }
};

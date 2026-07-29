<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_channels', function (Blueprint $table) {
            $table->unsignedInteger('fee_fixed')->default(0)->after('icon_url');
            $table->unsignedTinyInteger('fee_percent')->default(0)->after('fee_fixed');
        });
    }

    public function down(): void
    {
        Schema::table('payment_channels', function (Blueprint $table) {
            $table->dropColumn(['fee_fixed', 'fee_percent']);
        });
    }
};

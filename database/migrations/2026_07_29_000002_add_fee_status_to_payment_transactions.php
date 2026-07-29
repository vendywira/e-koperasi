<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->unsignedInteger('base_amount')->default(0)->after('amount');
            $table->unsignedInteger('fee_amount')->default(0)->after('base_amount');
        });

        DB::statement("ALTER TABLE payment_transactions MODIFY COLUMN status ENUM('pending','success','failed','expired','cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropColumn(['base_amount', 'fee_amount']);
        });

        DB::statement("ALTER TABLE payment_transactions MODIFY COLUMN status ENUM('pending','success','failed','expired') NOT NULL DEFAULT 'pending'");
    }
};

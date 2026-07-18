<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('name'); // tenant name
            $table->string('domain');
            $table->integer('resort_count');
            $table->decimal('price_per_resort', 12, 2);
            $table->integer('months')->default(1);
            $table->decimal('total_amount', 12, 2);
            $table->string('status', 20)->default('pending'); // pending, paid, cancelled
            $table->timestamp('paid_at')->nullable();
            $table->text('payment_proof')->nullable(); // uploaded file path
            $table->uuid('confirmed_by')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('confirmed_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

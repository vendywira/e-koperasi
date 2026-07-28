<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_line_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('subscription_id');
            $table->enum('type', ['upgrade', 'downgrade', 'renewal', 'adjustment']);
            $table->uuid('previous_plan_id')->nullable();
            $table->uuid('new_plan_id')->nullable();
            $table->decimal('previous_price', 12, 2)->nullable();
            $table->decimal('new_price', 12, 2)->nullable();
            $table->decimal('prorated_amount', 12, 2)->nullable();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_line_items');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_cycles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 50); // Bulanan, 3 Bulan, 6 Bulan, 12 Bulan, Custom
            $table->string('slug', 30)->unique(); // monthly, quarterly, semiannual, yearly, custom
            $table->unsignedInteger('months'); // 1, 3, 6, 12
            $table->unsignedInteger('discount_percent')->default(0); // 0, 5, 10, 20
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_cycles');
    }
};
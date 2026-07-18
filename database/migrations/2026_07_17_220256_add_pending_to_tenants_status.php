<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tenants MODIFY COLUMN status ENUM('active','suspended','trialing','pending','rejected') DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tenants MODIFY COLUMN status ENUM('active','suspended','trialing') DEFAULT 'active'");
    }
};

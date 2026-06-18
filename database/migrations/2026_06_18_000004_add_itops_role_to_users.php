<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Role column already exists as string — no schema change needed.
     * This migration documents the 'it-ops' role value for the project.
     */
    public function up(): void
    {
        // Role column is already a string, no schema change needed
    }

    public function down(): void
    {
        //
    }
};

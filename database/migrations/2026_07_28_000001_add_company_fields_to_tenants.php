<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->text('company_address')->nullable()->after('notes');
            $table->string('company_phone', 20)->nullable()->after('company_address');
            $table->string('company_email', 255)->nullable()->after('company_phone');
            $table->string('logo', 255)->nullable()->after('company_email');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['company_address', 'company_phone', 'company_email', 'logo']);
        });
    }
};

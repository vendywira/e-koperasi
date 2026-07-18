<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop foreign key ke tenant_requests (tabel sudah di-drop)
        try {
            DB::statement('ALTER TABLE invoices DROP FOREIGN KEY invoices_tenant_request_id_foreign');
        } catch (\Throwable $e) {}

        // Drop index if exists
        try {
            DB::statement('ALTER TABLE invoices DROP INDEX invoices_tenant_request_id_foreign');
        } catch (\Throwable $e) {}

        // Make tenant_request_id nullable
        DB::statement('ALTER TABLE invoices MODIFY COLUMN tenant_request_id CHAR(36) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE invoices MODIFY COLUMN tenant_request_id CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE invoices ADD CONSTRAINT invoices_tenant_request_id_foreign FOREIGN KEY (tenant_request_id) REFERENCES tenant_requests(id)');
    }
};

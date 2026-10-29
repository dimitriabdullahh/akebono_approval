<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop check constraints untuk status
        DB::statement('ALTER TABLE inspection_standards DROP CONSTRAINT IF EXISTS inspection_standards_status_check');
        DB::statement('ALTER TABLE packaging_standards DROP CONSTRAINT IF EXISTS packaging_standards_status_check');
        DB::statement('ALTER TABLE part_submission_warrants DROP CONSTRAINT IF EXISTS part_submission_warrants_status_check');
    }

    public function down(): void
    {
        // Tidak perlu rollback karena constraint akan ditambahkan kembali oleh Laravel jika diperlukan
    }
};

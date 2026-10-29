<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Untuk PostgreSQL, kita perlu menggunakan ALTER TYPE
        
        // Inspection Standards
        DB::statement("ALTER TABLE inspection_standards ALTER COLUMN status TYPE VARCHAR(20)");
        DB::statement("ALTER TABLE inspection_standards ALTER COLUMN status SET DEFAULT 'PENDING'");
        
        // Packaging Standards
        DB::statement("ALTER TABLE packaging_standards ALTER COLUMN status TYPE VARCHAR(20)");
        DB::statement("ALTER TABLE packaging_standards ALTER COLUMN status SET DEFAULT 'PENDING'");
        
        // Part Submission Warrants
        DB::statement("ALTER TABLE part_submission_warrants ALTER COLUMN status TYPE VARCHAR(20)");
        DB::statement("ALTER TABLE part_submission_warrants ALTER COLUMN status SET DEFAULT 'PENDING'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan default ke DRAFT
        DB::statement("ALTER TABLE inspection_standards ALTER COLUMN status SET DEFAULT 'DRAFT'");
        DB::statement("ALTER TABLE packaging_standards ALTER COLUMN status SET DEFAULT 'DRAFT'");
        DB::statement("ALTER TABLE part_submission_warrants ALTER COLUMN status SET DEFAULT 'DRAFT'");
    }
};

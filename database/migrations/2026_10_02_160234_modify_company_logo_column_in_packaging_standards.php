<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Check if column exists first
        if (!Schema::hasColumn('packaging_standards', 'company_logo')) {
            Schema::table('packaging_standards', function (Blueprint $table) {
                $table->text('company_logo')->nullable()->after('supplier_id');
            });
        } else {
            // Modify existing column to TEXT type for base64 storage
            DB::statement('ALTER TABLE packaging_standards ALTER COLUMN company_logo TYPE TEXT');
        }
    }

    public function down()
    {
        if (Schema::hasColumn('packaging_standards', 'company_logo')) {
            DB::statement('ALTER TABLE packaging_standards ALTER COLUMN company_logo TYPE VARCHAR(255)');
        }
    }
};

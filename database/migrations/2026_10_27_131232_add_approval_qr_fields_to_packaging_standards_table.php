<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('packaging_standards', function (Blueprint $table) {
            // Approved fields
            $table->string('approved_by_npk', 50)->nullable()->after('approved_by_name');
            $table->string('approved_by_jabatan', 100)->nullable()->after('approved_by_npk');
            $table->text('approved_qr_data')->nullable()->after('approved_by_jabatan');
            
            // Checked fields
            $table->string('checked_by_npk', 50)->nullable()->after('checked_by_name');
            $table->string('checked_by_jabatan', 100)->nullable()->after('checked_by_npk');
            $table->text('checked_qr_data')->nullable()->after('checked_by_jabatan');
            
            // Prepared fields
            $table->string('prepared_by_npk', 50)->nullable()->after('prepared_by_name');
            $table->string('prepared_by_jabatan', 100)->nullable()->after('prepared_by_npk');
            $table->text('prepared_qr_data')->nullable()->after('prepared_by_jabatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packaging_standards', function (Blueprint $table) {
            $table->dropColumn([
                'approved_by_npk', 'approved_by_jabatan', 'approved_qr_data',
                'checked_by_npk', 'checked_by_jabatan', 'checked_qr_data',
                'prepared_by_npk', 'prepared_by_jabatan', 'prepared_qr_data',
            ]);
        });
    }
};

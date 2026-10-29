<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('part_submission_warrants', function (Blueprint $table) {
            // Signature QR Code field - other fields already exist
            $table->text('signature_qr_data')->nullable()->after('fax_no'); // Base64 QR code image
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('part_submission_warrants', function (Blueprint $table) {
            $table->dropColumn('signature_qr_data');
        });
    }
};

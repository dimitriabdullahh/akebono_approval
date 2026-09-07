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
        // Tabel untuk menyimpan multiple parts dalam sebuah request
        Schema::create('request_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_request_id')->constrained('document_requests')->onDelete('cascade');
            $table->string('part_name');
            $table->string('part_number');
            $table->timestamps();

            $table->index(['document_request_id']);
            $table->index('part_number');
        });

        // Hapus kolom part_name dan part_number dari document_requests
        // karena sekarang ada di tabel terpisah
        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropColumn(['part_name', 'part_number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Restore kolom yang dihapus
        Schema::table('document_requests', function (Blueprint $table) {
            $table->string('part_name')->after('model');
            $table->string('part_number')->after('part_name');
        });

        Schema::dropIfExists('request_parts');
    }
};

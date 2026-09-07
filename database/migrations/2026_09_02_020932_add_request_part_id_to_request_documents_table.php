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
        Schema::table('request_documents', function (Blueprint $table) {
            // Tambah kolom request_part_id untuk relasi ke parts
            $table->foreignId('request_part_id')
                  ->nullable()
                  ->after('document_request_id')
                  ->constrained('request_parts')
                  ->onDelete('cascade');
            
            $table->index(['request_part_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_documents', function (Blueprint $table) {
            $table->dropForeign(['request_part_id']);
            $table->dropIndex(['request_part_id', 'status']);
            $table->dropColumn('request_part_id');
        });
    }
};

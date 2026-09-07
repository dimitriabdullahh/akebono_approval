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
            $table->timestamp('submitted_at')->nullable()->after('uploaded_at')->comment('Tanggal dokumen disubmit untuk approval');
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
            $table->dropColumn('submitted_at');
        });
    }
};

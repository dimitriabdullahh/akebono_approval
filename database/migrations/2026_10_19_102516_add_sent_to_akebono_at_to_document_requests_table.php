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
        Schema::table('document_requests', function (Blueprint $table) {
            // Timestamp kapan supplier submit semua dokumen ke Akebono untuk review
            $table->timestamp('sent_to_akebono_at')->nullable()->after('submitted_at');
            
            $table->index('sent_to_akebono_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropIndex(['sent_to_akebono_at']);
            $table->dropColumn('sent_to_akebono_at');
        });
    }
};

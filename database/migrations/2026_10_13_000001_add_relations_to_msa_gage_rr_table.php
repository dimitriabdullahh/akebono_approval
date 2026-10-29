<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('msa_gage_rr', function (Blueprint $table) {
            $table->foreignId('request_document_id')->nullable()->after('id')->constrained('request_documents')->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->after('request_document_id')->constrained('suppliers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('msa_gage_rr', function (Blueprint $table) {
            $table->dropForeign(['request_document_id']);
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['request_document_id', 'supplier_id']);
        });
    }
};

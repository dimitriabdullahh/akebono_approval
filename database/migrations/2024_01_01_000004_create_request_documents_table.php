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
        Schema::create('request_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_request_id')->constrained('document_requests')->onDelete('cascade');
            $table->foreignId('document_type_id')->constrained('document_types')->onDelete('cascade');
            $table->enum('status', ['PENDING', 'SUBMITTED', 'REVISE', 'APPROVED'])->default('PENDING');
            $table->text('file_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('revision_note')->nullable();
            $table->timestamp('revision_deadline_at')->nullable();
            $table->timestamps();

            $table->index(['document_request_id', 'status']);
            $table->index(['status', 'revision_deadline_at']);
            $table->index('reviewed_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_documents');
    }
};

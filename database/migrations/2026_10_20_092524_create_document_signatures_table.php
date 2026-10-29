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
        Schema::create('document_signatures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_document_id');
            $table->enum('role_type', ['checker', 'approver', 'receiver'])->comment('Generic role type for signature');
            $table->unsignedBigInteger('employee_id')->comment('FK to employees table');
            $table->string('npk', 50)->comment('Snapshot of employee NPK at time of signing');
            $table->string('name')->comment('Snapshot of employee name at time of signing');
            $table->string('role_label')->comment('e.g., "Checker", "Approver"');
            $table->text('notes')->nullable()->comment('Optional notes, e.g., checker comments');
            $table->timestamp('signed_at')->comment('Timestamp when signature was created');
            $table->timestamps();

            // Foreign keys
            $table->foreign('request_document_id')
                ->references('id')
                ->on('request_documents')
                ->onDelete('cascade');
            
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('restrict');

            // Unique constraint: one signature per document per role type
            $table->unique(['request_document_id', 'role_type'], 'unique_document_role_signature');

            // Indexes for performance
            $table->index('request_document_id');
            $table->index('employee_id');
            $table->index('role_type');
            $table->index('signed_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_signatures');
    }
};

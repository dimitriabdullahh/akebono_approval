<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create table for storing Checker and Receiver digital signature tokens
     * for QR code verification
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checker_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_document_id')->constrained('request_documents')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            
            // Signature type: CHECKER, RECEIVER, APPROVER
            $table->string('signature_type', 20);
            
            // Token for QR code
            $table->string('token', 255)->unique();
            $table->string('verification_code', 100)->unique();
            
            // Document and employee information (denormalized for verification page)
            $table->string('employee_name');
            $table->string('employee_npk', 50);
            $table->string('division', 50);
            $table->string('role', 50);
            $table->string('document_type', 100);
            $table->string('document_reference')->nullable();
            
            // Action timestamp
            $table->timestamp('action_at');
            
            // Verification tracking
            $table->integer('verification_count')->default(0);
            $table->timestamp('last_verified_at')->nullable();
            
            $table->timestamps();

            // Indexes for quick lookup
            $table->index('token');
            $table->index('verification_code');
            $table->index(['request_document_id', 'signature_type']);
            $table->index('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checker_signatures');
    }
};

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
        Schema::create('part_submission_warrants', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('request_document_id')->constrained('request_documents')->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            
            // Step 1: Part Information
            $table->string('part_name')->nullable();
            $table->string('part_number')->nullable();
            $table->string('model')->nullable();
            $table->string('revision')->nullable();
            $table->date('date_submitted')->nullable();
            
            // Step 2: Supplier Information
            $table->string('supplier_name')->nullable();
            $table->string('supplier_address')->nullable();
            $table->string('supplier_contact')->nullable();
            $table->string('supplier_phone')->nullable();
            $table->string('supplier_email')->nullable();
            
            // Step 3: Reason & Results
            $table->text('reason_for_change')->nullable();
            $table->string('evaluation_result')->nullable();
            $table->string('durability_test')->nullable();
            $table->string('functionality_test')->nullable();
            $table->text('test_results')->nullable();
            
            // Step 4: Declaration
            $table->text('declaration_text')->nullable();
            $table->date('declaration_date')->nullable();
            $table->string('declaration_signatory')->nullable();
            
            // Internal Review Section (for admin only)
            $table->text('internal_review_notes')->nullable();
            $table->string('reviewed_by')->nullable();
            $table->date('reviewed_date')->nullable();
            $table->enum('internal_review_status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            
            // File attachments (image paths)
            $table->string('image_1')->nullable();
            $table->string('image_2')->nullable();
            $table->string('image_3')->nullable();
            
            // Status tracking
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'PENDING_REVIEW', 'APPROVED', 'REJECTED'])->default('DRAFT');
            $table->string('pdf_file_path')->nullable();
            $table->json('form_data_json')->nullable(); // Backup of all form data as JSON
            
            // Timestamps
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('request_document_id');
            $table->index('supplier_id');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('part_submission_warrants');
    }
};

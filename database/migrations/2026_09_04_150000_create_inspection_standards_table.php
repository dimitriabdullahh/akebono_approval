<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Main inspection standards table
        Schema::create('inspection_standards', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('request_document_id')->constrained('request_documents')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            
            // General Information
            $table->string('model')->nullable();
            $table->date('date_issued')->nullable();
            $table->string('ref_no')->nullable();
            $table->string('part_route')->nullable();
            $table->string('part_no')->nullable();
            $table->string('part_name')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('material')->nullable();
            $table->string('general_tolerance')->nullable();
            $table->string('weight')->nullable();
            
            // Revision info
            $table->date('revision_date')->nullable();
            $table->text('revision_record')->nullable();
            
            // Status tracking
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'PENDING_REVIEW', 'APPROVED', 'REJECTED'])->default('DRAFT');
            $table->string('pdf_file_path')->nullable();
            $table->text('form_data_json')->nullable(); // Backup of all form data as JSON
            
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

        // Inspection standard drawings table
        Schema::create('inspection_standard_drawings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_standard_id')->constrained('inspection_standards')->onDelete('cascade');
            
            // Drawing info
            $table->string('drawing_name')->nullable();
            $table->string('drawing_no')->nullable();
            $table->string('revision')->nullable();
            $table->text('description')->nullable();
            
            // File info
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('file_type'); // jpg, png, pdf, etc
            $table->integer('file_size'); // in bytes
            
            // Order in list
            $table->integer('sequence_number')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('inspection_standard_id');
        });

        // Inspection standard items table (dynamic rows)
        Schema::create('inspection_standard_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_standard_id')->constrained('inspection_standards')->onDelete('cascade');
            
            // Inspection item fields
            $table->text('inspection_item')->nullable();
            $table->string('inspection_instrument')->nullable();
            $table->text('inspection_criterion')->nullable();
            $table->string('rank')->nullable(); // A, B, C, etc
            $table->string('supplier_sampling')->nullable();
            $table->string('aaij_sampling')->nullable();
            $table->text('remark_inspection_method')->nullable();
            
            // Order in table
            $table->integer('sequence_number')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('inspection_standard_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_standard_items');
        Schema::dropIfExists('inspection_standard_drawings');
        Schema::dropIfExists('inspection_standards');
    }
};

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
        // Main packaging standards table
        Schema::create('packaging_standards', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('request_document_id')->constrained('request_documents')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            
            // Step 1: Informasi Umum
            $table->string('document_no')->nullable();
            $table->string('revision')->nullable();
            $table->date('date')->nullable();
            $table->string('customer')->nullable(); // Auto-populated, readonly
            $table->string('part_no')->nullable();
            $table->string('part_name')->nullable();
            $table->string('supplier_name')->nullable(); // Auto-populated from supplier
            $table->integer('qty')->nullable();
            
            // Step 2: Spesifikasi Kemasan
            $table->string('packaging_type')->nullable();
            $table->string('packaging_size_mm')->nullable();
            $table->integer('packaging_lot_pcs_box')->nullable();
            $table->decimal('net_weight_g_pc', 10, 2)->nullable();
            $table->decimal('total_weight_kg_box', 10, 2)->nullable();
            
            // Step 3: Visualisasi & Approval
            // Images stored as file paths
            $table->string('image_photographic')->nullable();
            $table->string('image_part_identification')->nullable();
            $table->string('image_visualisasi_packing')->nullable();
            $table->string('image_label_packing')->nullable();
            
            // Revision status (stored as JSON array)
            $table->json('revision_history')->nullable();
            
            // Approval - Prepared by (Supplier fills this)
            $table->string('prepared_by_name')->nullable();
            $table->date('prepared_by_date')->nullable();
            
            // Approval - Checked & Approved (Internal Akebono, readonly for supplier)
            $table->string('checked_by_name')->nullable();
            $table->date('checked_by_date')->nullable();
            $table->string('approved_by_name')->nullable();
            $table->date('approved_by_date')->nullable();
            
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
     */
    public function down(): void
    {
        Schema::dropIfExists('packaging_standards');
    }
};

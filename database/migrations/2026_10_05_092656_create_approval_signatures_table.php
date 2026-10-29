<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create table for storing digital signature tokens and barcodes
     * for approval verification
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_id')->constrained('document_approvals')->onDelete('cascade');
            $table->string('token', 255)->unique();
            $table->string('verification_code', 100)->unique();
            $table->text('barcode_path')->nullable(); // Path to generated barcode image
            $table->timestamp('verified_at')->nullable(); // When someone verified this signature
            $table->timestamps();

            // Indexes for quick lookup
            $table->index('token');
            $table->index('verification_code');
            $table->index('approval_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_signatures');
    }
};

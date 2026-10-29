<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create approval_tokens table for email-based approval system
     * Purchasing and PPC can approve documents via secure token links
     * without logging into the portal
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_tokens', function (Blueprint $table) {
            $table->id();
            
            // Token and ownership
            $table->string('token', 64)->unique(); // SHA256 hash of random token
            $table->string('plain_token', 255)->nullable(); // Store hashed version for security
            
            // Approval task reference
            $table->foreignId('document_approval_id')->constrained('document_approvals')->onDelete('cascade');
            $table->foreignId('request_document_id')->constrained('request_documents')->onDelete('cascade');
            
            // Recipient information
            $table->string('recipient_email')->index();
            $table->string('recipient_name')->nullable();
            $table->string('division', 50); // PURCHASING, PPC
            $table->string('scope', 50); // Same as division for email approval
            
            // Document information snapshot
            $table->string('document_type', 50); // PSW, PS
            $table->string('document_number')->nullable();
            $table->integer('document_version')->default(1);
            
            // Token lifecycle
            $table->timestamp('expires_at')->index();
            $table->timestamp('used_at')->nullable();
            $table->boolean('is_revoked')->default(false)->index();
            
            // Security tracking
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->integer('access_count')->default(0);
            $table->timestamp('last_accessed_at')->nullable();
            
            // Metadata
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance and security
            $table->index(['request_document_id', 'division']);
            $table->index(['document_approval_id', 'is_revoked']);
            $table->index(['expires_at', 'is_revoked', 'used_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_tokens');
    }
};

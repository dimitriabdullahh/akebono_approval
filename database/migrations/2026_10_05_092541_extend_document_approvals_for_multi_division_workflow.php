<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Extend existing document_approvals table to support multi-division workflow
     * with Checker/Approver roles, revision tracking, and digital signatures
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_approvals', function (Blueprint $table) {
            // Employee reference (nullable for backward compatibility with existing data)
            $table->foreignId('employee_id')->nullable()->after('user_id')->constrained('employees')->onDelete('cascade');
            
            // Division and Role for new workflow
            $table->string('division', 50)->nullable()->after('employee_id');
            $table->string('role', 50)->nullable()->after('division'); // Checker, Approver, or Received (for IS)
            
            // Stage for IS sequential workflow (RECEIVED, CHECK, APPROVE)
            $table->string('stage', 50)->nullable()->after('role');
            
            // Document type for filtering (IS, PS, PSW)
            $table->string('document_type', 50)->nullable()->after('stage');
            
            // Revision and cycle tracking
            $table->integer('revision_number')->default(1)->after('document_type');
            $table->integer('approval_cycle')->default(1)->after('revision_number');
            
            // Comment/reason field
            $table->text('comment')->nullable()->after('note');
            
            // Indexes for performance
            $table->index(['request_document_id', 'revision_number']);
            $table->index('employee_id');
            $table->index(['division', 'role']);
            $table->index('document_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_approvals', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['request_document_id', 'revision_number']);
            $table->dropIndex(['employee_id']);
            $table->dropIndex(['division', 'role']);
            $table->dropIndex(['document_type']);
            
            // Drop foreign key
            $table->dropForeign(['employee_id']);
            
            // Drop columns
            $table->dropColumn([
                'employee_id',
                'division',
                'role',
                'stage',
                'document_type',
                'revision_number',
                'approval_cycle',
                'comment',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add Checker tracking fields and e-sign token for QR code verification
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_documents', function (Blueprint $table) {
            // Checker information
            $table->foreignId('checked_by_employee_id')->nullable()->after('reviewed_at')->constrained('employees')->onDelete('set null');
            $table->timestamp('checked_at')->nullable()->after('checked_by_employee_id');
            $table->text('checker_notes')->nullable()->after('checked_at');
            
            // Checker e-sign token for QR code
            $table->string('checker_esign_token', 255)->nullable()->unique()->after('checker_notes');
            
            // Receiver information (for IS workflow)
            $table->foreignId('received_by_employee_id')->nullable()->after('checker_esign_token')->constrained('employees')->onDelete('set null');
            $table->timestamp('received_at')->nullable()->after('received_by_employee_id');
            $table->string('receiver_esign_token', 255)->nullable()->unique()->after('received_at');
            
            // Workflow stage tracking
            $table->string('workflow_stage', 50)->nullable()->after('receiver_esign_token')->comment('RECEIVED, CHECKING, CHECKED, APPROVING, APPROVED, REVISION');
            
            // Indexes for performance
            $table->index('checked_by_employee_id');
            $table->index('received_by_employee_id');
            $table->index('workflow_stage');
            $table->index('checker_esign_token');
            $table->index('receiver_esign_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_documents', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['checked_by_employee_id']);
            $table->dropIndex(['received_by_employee_id']);
            $table->dropIndex(['workflow_stage']);
            $table->dropIndex(['checker_esign_token']);
            $table->dropIndex(['receiver_esign_token']);
            
            // Drop foreign keys
            $table->dropForeign(['checked_by_employee_id']);
            $table->dropForeign(['received_by_employee_id']);
            
            // Drop columns
            $table->dropColumn([
                'checked_by_employee_id',
                'checked_at',
                'checker_notes',
                'checker_esign_token',
                'received_by_employee_id',
                'received_at',
                'receiver_esign_token',
                'workflow_stage',
            ]);
        });
    }
};

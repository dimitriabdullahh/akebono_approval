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
        Schema::create('document_request_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_request_id')->constrained('document_requests')->onDelete('cascade');
            
            // Reminder status and level
            $table->integer('reminder_level')->default(0); // 0=not started, 1=PIC, 2=Supervisor, 3=Head, 4=GM, 5=Director
            $table->string('escalation_status')->default('WAITING'); // WAITING, STOPPED, ESCALATED_TO_DIRECTOR
            
            // Timestamps untuk tracking
            $table->timestamp('initial_reminder_sent_at')->nullable();
            $table->timestamp('pic_reminder_sent_at')->nullable();
            $table->timestamp('supervisor_escalation_sent_at')->nullable();
            $table->timestamp('head_dept_escalation_sent_at')->nullable();
            $table->timestamp('gm_escalation_sent_at')->nullable();
            $table->timestamp('director_escalation_sent_at')->nullable();
            $table->timestamp('last_reminder_at')->nullable();
            $table->timestamp('next_reminder_at')->nullable();
            $table->timestamp('stopped_at')->nullable(); // Kapan reminder berhenti (ada dokumen dikirim)
            
            // Document count snapshot
            $table->integer('document_count_at_last_check')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('document_request_id');
            $table->index('reminder_level');
            $table->index('escalation_status');
            $table->index('next_reminder_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_request_reminders');
    }
};

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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_request_id')->constrained('document_requests')->onDelete('cascade');
            $table->string('type'); // REQUEST_LETTER, DOCS_SUBMITTED, REVISION_REMINDER, APPROVAL_COMPLETED
            $table->string('recipient');
            $table->string('subject');
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['SENT', 'FAILED'])->default('SENT');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['document_request_id', 'type']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_logs');
    }
};

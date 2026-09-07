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
        Schema::table('request_parts', function (Blueprint $table) {
            // Add submission status tracking fields
            $table->string('submission_status')->default('DRAFT')->after('part_number')
                ->comment('Status: DRAFT, SUBMITTED');
            $table->timestamp('last_saved_at')->nullable()->after('submission_status')
                ->comment('Last time draft was saved');
            $table->timestamp('submitted_at')->nullable()->after('last_saved_at')
                ->comment('When documents were submitted');
            $table->unsignedBigInteger('submitted_by')->nullable()->after('submitted_at')
                ->comment('User ID who submitted');
            
            // Add foreign key for submitted_by
            $table->foreign('submitted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_parts', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['submitted_by']);
            
            // Drop columns
            $table->dropColumn([
                'submission_status',
                'last_saved_at',
                'submitted_at',
                'submitted_by'
            ]);
        });
    }
};

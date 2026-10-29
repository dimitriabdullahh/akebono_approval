<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Make user_id nullable to support new employee-based workflow
     * where approvals are done by employees (employee_id) instead of users (user_id)
     *
     * @return void
     */
    public function up()
    {
        // Drop foreign key constraint
        DB::statement('ALTER TABLE document_approvals DROP CONSTRAINT document_approvals_user_id_foreign');
        
        // Make user_id nullable
        DB::statement('ALTER TABLE document_approvals ALTER COLUMN user_id DROP NOT NULL');
        
        // Re-add foreign key constraint
        DB::statement('ALTER TABLE document_approvals ADD CONSTRAINT document_approvals_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop foreign key constraint
        DB::statement('ALTER TABLE document_approvals DROP CONSTRAINT document_approvals_user_id_foreign');
        
        // Make user_id not nullable
        DB::statement('ALTER TABLE document_approvals ALTER COLUMN user_id SET NOT NULL');
        
        // Re-add foreign key constraint
        DB::statement('ALTER TABLE document_approvals ADD CONSTRAINT document_approvals_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
    }
};

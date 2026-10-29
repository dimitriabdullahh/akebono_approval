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
     * Add 'Receiver' role to employees table enum
     * This allows QA division to have Receiver role for IS documents
     *
     * @return void
     */
    public function up()
    {
        // For PostgreSQL with check constraints, we need to drop and recreate the constraint
        // First, find the constraint name
        $constraintName = DB::select("
            SELECT constraint_name 
            FROM information_schema.constraint_column_usage 
            WHERE table_name = 'employees' AND column_name = 'role'
        ");
        
        if (!empty($constraintName)) {
            $name = $constraintName[0]->constraint_name;
            // Drop old constraint
            DB::statement("ALTER TABLE employees DROP CONSTRAINT {$name}");
        }
        
        // Add new constraint with 'Receiver' included
        DB::statement("
            ALTER TABLE employees 
            ADD CONSTRAINT employees_role_check 
            CHECK (role IN ('Checker', 'Approver', 'Receiver'))
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the constraint
        DB::statement("ALTER TABLE employees DROP CONSTRAINT IF EXISTS employees_role_check");
        
        // Recreate without 'Receiver'
        DB::statement("
            ALTER TABLE employees 
            ADD CONSTRAINT employees_role_check 
            CHECK (role IN ('Checker', 'Approver'))
        ");
    }
};

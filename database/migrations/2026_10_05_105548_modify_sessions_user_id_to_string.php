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
     * @return void
     */
    public function up()
    {
        // Use raw SQL to alter column type from bigint to varchar
        DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE VARCHAR(255)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to bigint
        DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE BIGINT USING user_id::bigint');
    }
};

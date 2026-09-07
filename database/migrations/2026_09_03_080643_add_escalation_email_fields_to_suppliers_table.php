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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('supervisor_email')->nullable()->after('pic_phone');
            $table->string('supervisor_name')->nullable()->after('pic_phone');
            $table->string('head_dept_email')->nullable()->after('supervisor_email');
            $table->string('head_dept_name')->nullable()->after('supervisor_email');
            $table->string('gm_email')->nullable()->after('head_dept_email');
            $table->string('gm_name')->nullable()->after('head_dept_email');
            $table->string('director_email')->nullable()->after('gm_email');
            $table->string('director_name')->nullable()->after('gm_email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'supervisor_email',
                'supervisor_name',
                'head_dept_email',
                'head_dept_name',
                'gm_email',
                'gm_name',
                'director_email',
                'director_name',
            ]);
        });
    }
};

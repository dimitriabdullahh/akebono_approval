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
        Schema::table('packaging_standards', function (Blueprint $table) {
            if (!Schema::hasColumn('packaging_standards', 'model')) {
                $table->string('model')->nullable()->after('customer');
            }
        });
    }

    public function down()
    {
        Schema::table('packaging_standards', function (Blueprint $table) {
            if (Schema::hasColumn('packaging_standards', 'model')) {
                $table->dropColumn('model');
            }
        });
    }
};

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
            // Add new image fields
            $table->string('image_of_packaging')->nullable()->after('total_weight_kg_box');
            $table->string('part_identification_point')->nullable()->after('image_of_packaging');
            
            // Drop old image fields
            $table->dropColumn([
                'image_photographic',
                'image_part_identification',
                'image_visualisasi_packing',
                'image_label_packing'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packaging_standards', function (Blueprint $table) {
            // Restore old image fields
            $table->string('image_photographic')->nullable()->after('total_weight_kg_box');
            $table->string('image_part_identification')->nullable()->after('image_photographic');
            $table->string('image_visualisasi_packing')->nullable()->after('image_part_identification');
            $table->string('image_label_packing')->nullable()->after('image_visualisasi_packing');
            
            // Drop new image fields
            $table->dropColumn([
                'image_of_packaging',
                'part_identification_point'
            ]);
        });
    }
};

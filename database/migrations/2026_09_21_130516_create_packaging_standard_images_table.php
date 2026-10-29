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
        Schema::create('packaging_standard_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packaging_standard_id')->constrained()->onDelete('cascade');
            $table->enum('image_type', ['image_of_packaging', 'part_identification_point']);
            $table->string('file_path', 500);
            $table->string('original_filename')->nullable();
            $table->integer('file_size')->nullable(); // in bytes
            $table->integer('order')->default(0); // untuk urutan gambar
            $table->timestamps();
            
            $table->index(['packaging_standard_id', 'image_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('packaging_standard_images');
    }
};

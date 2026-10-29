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
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_mandatory')->default(true);
            $table->json('scope')->nullable(); // Array: ['QA', 'PURCHASING', 'PPC']
            $table->text('description')->nullable();
            $table->integer('order')->default(0); // Display order
            $table->timestamps();

            $table->index('code');
            $table->index('is_mandatory');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_types');
    }
};

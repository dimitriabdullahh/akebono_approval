<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('msa_gage_rr', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique();            // dipakai di URL form (/supplier/msa/{token})
            $table->string('part_no')->nullable();        // auto-populated
            $table->string('part_name')->nullable();      // auto-populated
            $table->string('status', 20)->default('draft'); // draft | submitted
            $table->json('general')->nullable();          // header laporan
            $table->json('settings')->nullable();         // appraisers, trials, parts
            $table->json('measurements')->nullable();     // data A/B/C x trial x part
            $table->json('results')->nullable();          // snapshot hasil saat submit
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('msa_gage_rr');
    }
};

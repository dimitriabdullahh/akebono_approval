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
        Schema::create('document_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_document_id')->constrained('request_documents')->onDelete('cascade');
            $table->string('team', 20); // qa | purchasing | ppc
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('note'); // Catatan revisi dari team
            $table->timestamp('revised_at'); // Kapan revisi diberikan
            $table->timestamp('resubmitted_at')->nullable(); // Kapan supplier submit ulang
            $table->timestamps();
            
            $table->index('request_document_id');
            $table->index(['team', 'revised_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_revisions');
    }
};

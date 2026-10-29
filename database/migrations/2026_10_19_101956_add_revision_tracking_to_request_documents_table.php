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
        Schema::table('request_documents', function (Blueprint $table) {
            // Tracking team yang memberi revisi (qa | purchasing | ppc)
            $table->string('revised_by_team', 20)->nullable()->after('revision_note');
            
            // User ID dari team yang memberi revisi (optional, untuk audit trail)
            $table->foreignId('revised_by_user_id')->nullable()->after('revised_by_team')->constrained('users')->onDelete('set null');
            
            // Timestamp kapan dokumen direvisi
            $table->timestamp('revised_at')->nullable()->after('revised_by_user_id');
            
            // Counter berapa kali dokumen direvisi
            $table->unsignedInteger('revision_count')->default(0)->after('revised_at');
            
            // Version tracking untuk PDF (increment setiap kali regenerate)
            $table->unsignedInteger('version')->default(1)->after('revision_count');
            
            // Path ke PDF file (untuk form-based documents: IS, PS, PSW, MSA)
            $table->string('pdf_path')->nullable()->after('version');
            
            // Index untuk performa query
            $table->index('revised_by_team');
            $table->index(['status', 'revised_by_team']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_documents', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['status', 'revised_by_team']);
            $table->dropIndex(['revised_by_team']);
            
            // Drop foreign key
            $table->dropForeign(['revised_by_user_id']);
            
            // Drop columns
            $table->dropColumn([
                'revised_by_team',
                'revised_by_user_id',
                'revised_at',
                'revision_count',
                'version',
                'pdf_path'
            ]);
        });
    }
};

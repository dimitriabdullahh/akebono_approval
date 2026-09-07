<?php

namespace App\Console\Commands;

use App\Models\DocumentRequest;
use App\Models\RequestDocument;
use App\Models\RequestPart;
use App\Models\DocumentHistory;
use App\Models\NotificationLog;
use App\Models\Supplier;
use App\Models\User;
use App\Models\RevisionConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CleanAllData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clean-all {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus SEMUA data dari database (users, suppliers, requests, dll)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!$this->option('force')) {
            $this->error('⚠️  PERINGATAN KERAS!');
            $this->error('   Ini akan menghapus SEMUA DATA termasuk:');
            $this->error('   - Users (admin, reviewers)');
            $this->error('   - Suppliers');
            $this->error('   - Requests dan semua dokumen');
            $this->error('   - Notification logs');
            $this->error('   - File uploads');
            $this->newLine();
            
            if (!$this->confirm('Apakah Anda YAKIN ingin melanjutkan?')) {
                $this->info('Pembersihan dibatalkan.');
                return 0;
            }

            if (!$this->confirm('Konfirmasi sekali lagi - HAPUS SEMUA DATA?')) {
                $this->info('Pembersihan dibatalkan.');
                return 0;
            }
        }

        $this->info('🧹 Memulai pembersihan TOTAL database...');
        $this->newLine();

        DB::beginTransaction();

        try {
            $stats = [];

            // 1. Hapus semua file yang diupload
            $this->info('📁 Menghapus file-file yang diupload...');
            $documents = RequestDocument::whereNotNull('file_path')->get();
            $fileCount = 0;
            
            foreach ($documents as $document) {
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                    $fileCount++;
                }
            }
            $stats['Files'] = $fileCount;
            $this->info("   ✓ {$fileCount} file dihapus");

            // 2. Hapus document history
            $count = DocumentHistory::count();
            DocumentHistory::truncate();
            $stats['Document History'] = $count;
            $this->info("   ✓ {$count} document history dihapus");

            // 3. Hapus notification logs
            $count = NotificationLog::count();
            NotificationLog::truncate();
            $stats['Notification Logs'] = $count;
            $this->info("   ✓ {$count} notification log dihapus");

            // 4. Hapus request documents
            $count = RequestDocument::count();
            RequestDocument::truncate();
            $stats['Request Documents'] = $count;
            $this->info("   ✓ {$count} request document dihapus");

            // 5. Hapus request parts
            $count = RequestPart::count();
            RequestPart::truncate();
            $stats['Request Parts'] = $count;
            $this->info("   ✓ {$count} request part dihapus");

            // 6. Hapus document requests
            $count = DocumentRequest::count();
            DocumentRequest::truncate();
            $stats['Document Requests'] = $count;
            $this->info("   ✓ {$count} document request dihapus");

            // 7. Hapus suppliers
            $count = Supplier::count();
            Supplier::truncate();
            $stats['Suppliers'] = $count;
            $this->info("   ✓ {$count} supplier dihapus");

            // 8. Hapus users
            $count = User::count();
            User::truncate();
            $stats['Users'] = $count;
            $this->info("   ✓ {$count} user dihapus");

            // 9. Hapus revision configs (opsional, tergantung apakah mau reset)
            $count = RevisionConfig::count();
            RevisionConfig::truncate();
            $stats['Revision Configs'] = $count;
            $this->info("   ✓ {$count} revision config dihapus");

            // 10. Bersihkan direktori uploads
            $this->info('🗑️  Membersihkan direktori uploads...');
            if (Storage::disk('public')->exists('documents')) {
                $allFiles = Storage::disk('public')->allFiles('documents');
                Storage::disk('public')->delete($allFiles);
                $this->info("   ✓ " . count($allFiles) . " file tambahan dihapus");
            }

            DB::commit();

            $this->newLine();
            $this->info('✅ PEMBERSIHAN TOTAL SELESAI!');
            $this->newLine();
            
            $tableData = [];
            foreach ($stats as $item => $count) {
                $tableData[] = [$item, $count];
            }
            
            $this->table(['Item', 'Jumlah Dihapus'], $tableData);

            $this->newLine();
            $this->info('💡 Database sudah KOSONG TOTAL!');
            $this->info('💡 Langkah selanjutnya:');
            $this->info('   1. php artisan migrate:fresh --seed  (jika mau reset struktur tabel)');
            $this->info('   2. php artisan admin:create  (buat user admin baru)');
            $this->info('   3. php artisan supplier:create  (buat supplier dan request baru)');

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}

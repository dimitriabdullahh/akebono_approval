<?php

namespace App\Console\Commands;

use App\Models\DocumentRequest;
use App\Models\RequestDocument;
use App\Models\RequestPart;
use App\Models\DocumentHistory;
use App\Models\NotificationLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CleanAllRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'requests:clean-all {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bersihkan semua data request dan file terkait';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  PERINGATAN: Ini akan menghapus SEMUA data request, dokumen, dan file. Lanjutkan?')) {
                $this->info('Pembersihan dibatalkan.');
                return 0;
            }
        }

        $this->info('🧹 Memulai pembersihan data...');
        $this->newLine();

        DB::beginTransaction();

        try {
            // 1. Hapus file-file yang diupload
            $this->info('📁 Menghapus file-file yang diupload...');
            $documents = RequestDocument::whereNotNull('file_path')->get();
            $fileCount = 0;
            
            foreach ($documents as $document) {
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                    $fileCount++;
                }
            }
            $this->info("   ✓ {$fileCount} file dihapus");

            // 2. Hapus document history
            $historyCount = DocumentHistory::count();
            DocumentHistory::truncate();
            $this->info("   ✓ {$historyCount} document history dihapus");

            // 3. Hapus notification logs
            $notifCount = NotificationLog::count();
            NotificationLog::truncate();
            $this->info("   ✓ {$notifCount} notification log dihapus");

            // 4. Hapus request documents
            $docCount = RequestDocument::count();
            RequestDocument::truncate();
            $this->info("   ✓ {$docCount} request document dihapus");

            // 5. Hapus request parts
            $partCount = RequestPart::count();
            RequestPart::truncate();
            $this->info("   ✓ {$partCount} request part dihapus");

            // 6. Hapus document requests
            $requestCount = DocumentRequest::count();
            DocumentRequest::truncate();
            $this->info("   ✓ {$requestCount} document request dihapus");

            // 7. Bersihkan direktori uploads (jika masih ada file yang tersisa)
            $this->info('🗑️  Membersihkan direktori uploads...');
            if (Storage::disk('public')->exists('documents')) {
                $allFiles = Storage::disk('public')->allFiles('documents');
                Storage::disk('public')->delete($allFiles);
                $this->info("   ✓ " . count($allFiles) . " file tambahan dihapus dari direktori");
            }

            DB::commit();

            $this->newLine();
            $this->info('✅ PEMBERSIHAN SELESAI!');
            $this->newLine();
            $this->table(
                ['Item', 'Jumlah Dihapus'],
                [
                    ['Document Requests', $requestCount],
                    ['Request Parts', $partCount],
                    ['Request Documents', $docCount],
                    ['Document History', $historyCount],
                    ['Notification Logs', $notifCount],
                    ['Files', $fileCount],
                ]
            );

            $this->newLine();
            $this->info('💡 Database sudah bersih dan siap untuk testing fitur dari awal!');
            $this->info('💡 Jalankan: php artisan supplier:create untuk membuat request baru');

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}

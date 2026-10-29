<?php

namespace App\Console\Commands;

use App\Models\PackagingStandard;
use Illuminate\Console\Command;

class ListPackagingStandards extends Command
{
    protected $signature = 'ps:list {--limit=10}';
    protected $description = 'List Packaging Standards yang ada';

    public function handle()
    {
        $limit = $this->option('limit');
        $packagingStandards = PackagingStandard::orderBy('id', 'desc')->limit($limit)->get();
        
        if ($packagingStandards->isEmpty()) {
            $this->warn('Tidak ada Packaging Standard ditemukan!');
            return 0;
        }
        
        $this->info("=== PACKAGING STANDARDS (Latest {$limit}) ===");
        $this->newLine();
        
        $rows = [];
        foreach ($packagingStandards as $ps) {
            $rows[] = [
                $ps->id,
                $ps->part_no ?? '-',
                $ps->part_name ?? '-',
                $ps->status,
                $ps->prepared_by_name ?? '(kosong)',
                $ps->checked_by_name ?? '(kosong)',
                $ps->approved_by_name ?? '(kosong)',
            ];
        }
        
        $this->table(
            ['ID', 'Part No', 'Part Name', 'Status', 'Prepared', 'Checked', 'Approved'],
            $rows
        );
        
        $this->newLine();
        $this->info('Gunakan: php artisan ps:fill-approval-names [ID] untuk mengisi nama approval');
        $this->info('Contoh: php artisan ps:fill-approval-names 1 --prepared="John Doe" --checked="Jane Smith" --approved="Bob Lee"');
        
        return 0;
    }
}

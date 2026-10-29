<?php

namespace App\Console\Commands;

use App\Models\PackagingStandard;
use Illuminate\Console\Command;

class UpdatePackagingStandardModel extends Command
{
    protected $signature = 'ps:update-model';
    protected $description = 'Update model field in existing PackagingStandard records';

    public function handle()
    {
        $this->info('Updating PackagingStandard records...');
        
        $records = PackagingStandard::whereNull('model')
            ->orWhere('model', '')
            ->with(['requestDocument.documentRequest'])
            ->get();
        
        $updated = 0;
        
        foreach ($records as $ps) {
            if ($ps->requestDocument && $ps->requestDocument->documentRequest) {
                $model = $ps->requestDocument->documentRequest->model ?? '';
                $ps->update(['model' => $model]);
                $updated++;
                $this->line("Updated PS #{$ps->id} with model: {$model}");
            }
        }
        
        $this->info("Updated {$updated} records.");
        
        return 0;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\PackagingStandard;
use Illuminate\Console\Command;

class UpdatePackagingStandardFields extends Command
{
    protected $signature = 'ps:update-fields';
    protected $description = 'Update part_no, part_name, supplier_name in existing PackagingStandard records';

    public function handle()
    {
        $this->info('Updating PackagingStandard records...');
        
        $records = PackagingStandard::with(['requestDocument.documentRequest.supplier'])->get();
        
        $updated = 0;
        
        foreach ($records as $ps) {
            if ($ps->requestDocument && $ps->requestDocument->documentRequest) {
                $dr = $ps->requestDocument->documentRequest;
                
                // Use accessor methods that get from requestParts
                $partNo = $dr->part_number; // accessor getPartNumberAttribute()
                $partName = $dr->part_name;  // accessor getPartNameAttribute()
                $supplierName = $dr->supplier ? $dr->supplier->name : null;
                
                // Skip if all are empty or default '-'
                if (($partNo === '-' || empty($partNo)) && 
                    ($partName === '-' || empty($partName)) && 
                    empty($supplierName)) {
                    $this->warn("PS #{$ps->id}: No data to update (DocumentRequest has empty fields)");
                    continue;
                }
                
                $data = [];
                if ($partNo !== '-' && !empty($partNo)) $data['part_no'] = $partNo;
                if ($partName !== '-' && !empty($partName)) $data['part_name'] = $partName;
                if (!empty($supplierName)) $data['supplier_name'] = $supplierName;
                
                if (empty($data)) {
                    $this->warn("PS #{$ps->id}: No data to update");
                    continue;
                }
                
                $ps->update($data);
                $updated++;
                
                $this->line("Updated PS #{$ps->id}:");
                if (isset($data['part_no'])) $this->line("  Part No: {$data['part_no']}");
                if (isset($data['part_name'])) $this->line("  Part Name: {$data['part_name']}");
                if (isset($data['supplier_name'])) $this->line("  Supplier: {$data['supplier_name']}");
            }
        }
        
        $this->info("Updated {$updated} records.");
        
        return 0;
    }
}

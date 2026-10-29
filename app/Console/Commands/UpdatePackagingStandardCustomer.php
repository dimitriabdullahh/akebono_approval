<?php

namespace App\Console\Commands;

use App\Models\PackagingStandard;
use Illuminate\Console\Command;

class UpdatePackagingStandardCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ps:update-customer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update customer field in all Packaging Standards to new default value';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to update customer field in Packaging Standards...');

        $oldValue = 'PT AAIJ (Purchasing & Quality Division)';
        $newValue = 'PT.AKEBONO ASTRA BRAKE INDONESIA';

        // Find all PS records with old customer value
        $packagingStandards = PackagingStandard::where('customer', $oldValue)
            ->orWhereNull('customer')
            ->get();

        $this->info("Found {$packagingStandards->count()} records to update.");

        $updated = 0;
        foreach ($packagingStandards as $ps) {
            $ps->update(['customer' => $newValue]);
            $updated++;
            $this->info("Updated PS ID: {$ps->id} - Part No: {$ps->part_no}");
        }

        $this->info("Successfully updated {$updated} Packaging Standard records.");
        $this->info("Customer field changed from: '{$oldValue}'");
        $this->info("                        to: '{$newValue}'");

        return 0;
    }
}

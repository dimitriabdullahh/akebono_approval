<?php

namespace App\Console\Commands;

use App\Models\Supplier;
use Illuminate\Console\Command;

class UpdateSupplierAddressFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suppliers:update-address-fields';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse address field and populate city, state, zip_code fields';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Updating supplier address fields...');
        
        $suppliers = Supplier::whereNotNull('address')->get();
        
        $updated = 0;
        
        foreach ($suppliers as $supplier) {
            $parsed = $this->parseAddress($supplier->address);
            
            if ($parsed) {
                $supplier->city = $parsed['city'] ?? null;
                $supplier->state = $parsed['state'] ?? null;
                $supplier->zip_code = $parsed['zip_code'] ?? null;
                
                // Update street address to remove city, state, zip
                if (!empty($parsed['street'])) {
                    $supplier->address = $parsed['street'];
                }
                
                $supplier->save();
                $updated++;
                
                $this->line("✓ Updated: {$supplier->name}");
                $this->line("  Street: {$parsed['street']}");
                $this->line("  City: {$parsed['city']}");
                $this->line("  State: {$parsed['state']}");
                $this->line("  Zip: {$parsed['zip_code']}");
                $this->newLine();
            }
        }
        
        $this->info("Updated {$updated} suppliers successfully!");
        
        return Command::SUCCESS;
    }
    
    /**
     * Parse address string into components
     * Expected format: "Jl. Street Name, City, State ZipCode"
     * or "Jl. Street Name, City, State"
     */
    private function parseAddress($address)
    {
        if (empty($address)) {
            return null;
        }
        
        // Split by comma
        $parts = array_map('trim', explode(',', $address));
        
        if (count($parts) < 2) {
            // Address doesn't have comma separators, return as is
            return [
                'street' => $address,
                'city' => null,
                'state' => null,
                'zip_code' => null,
            ];
        }
        
        $result = [
            'street' => $parts[0], // First part is street
            'city' => null,
            'state' => null,
            'zip_code' => null,
        ];
        
        // Second part is city
        if (isset($parts[1])) {
            $result['city'] = $parts[1];
        }
        
        // Third part might contain state and zip code
        if (isset($parts[2])) {
            $lastPart = $parts[2];
            
            // Try to extract zip code (5 digits)
            if (preg_match('/(\d{5})/', $lastPart, $matches)) {
                $result['zip_code'] = $matches[1];
                // Remove zip code from the string to get state
                $result['state'] = trim(str_replace($matches[1], '', $lastPart));
            } else {
                // No zip code found, whole thing is state
                $result['state'] = $lastPart;
            }
        }
        
        return $result;
    }
}

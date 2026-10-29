<?php

namespace App\Console\Commands;

use App\Models\Supplier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CheckSupplierFields extends Command
{
    protected $signature = 'suppliers:check-fields';
    protected $description = 'Check if city, state, zip_code fields exist and have data';

    public function handle()
    {
        $this->info('=== CHECKING SUPPLIERS TABLE ===');
        $this->newLine();
        
        // Check if columns exist
        $this->info('📋 Checking table structure...');
        $hasCity = Schema::hasColumn('suppliers', 'city');
        $hasState = Schema::hasColumn('suppliers', 'state');
        $hasZipCode = Schema::hasColumn('suppliers', 'zip_code');
        
        $this->line("  city column exists: " . ($hasCity ? '✓ YES' : '✗ NO'));
        $this->line("  state column exists: " . ($hasState ? '✓ YES' : '✗ NO'));
        $this->line("  zip_code column exists: " . ($hasZipCode ? '✓ YES' : '✗ NO'));
        $this->newLine();
        
        if (!$hasCity || !$hasState || !$hasZipCode) {
            $this->error('⚠️  MISSING COLUMNS! Run: php artisan migrate');
            return Command::FAILURE;
        }
        
        // Check data
        $this->info('📊 Checking supplier data (first 10)...');
        $this->newLine();
        
        $suppliers = Supplier::take(10)->get();
        
        $table = [];
        foreach ($suppliers as $supplier) {
            $table[] = [
                'Name' => substr($supplier->name, 0, 25),
                'Code' => $supplier->vendor_code,
                'City' => $supplier->city ?: '❌ EMPTY',
                'State' => $supplier->state ?: '❌ EMPTY',
                'Zip' => $supplier->zip_code ?: '❌ EMPTY',
            ];
        }
        
        $this->table(['Name', 'Code', 'City', 'State', 'Zip'], $table);
        
        // Summary
        $totalSuppliers = Supplier::count();
        $withCity = Supplier::whereNotNull('city')->count();
        $withState = Supplier::whereNotNull('state')->count();
        $withZip = Supplier::whereNotNull('zip_code')->count();
        
        $this->newLine();
        $this->info('📈 Summary:');
        $this->line("  Total suppliers: {$totalSuppliers}");
        $this->line("  With city: {$withCity}");
        $this->line("  With state: {$withState}");
        $this->line("  With zip_code: {$withZip}");
        $this->newLine();
        
        if ($withCity == 0 || $withState == 0 || $withZip == 0) {
            $this->warn('⚠️  Some fields are empty! You need to fill the data.');
            $this->info('💡 Tip: Update suppliers table manually or run a seeder.');
        } else {
            $this->info('✓ All fields have data!');
        }
        
        return Command::SUCCESS;
    }
}

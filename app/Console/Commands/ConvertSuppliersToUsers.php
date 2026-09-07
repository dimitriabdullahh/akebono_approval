<?php

namespace App\Console\Commands;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ConvertSuppliersToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supplier:convert-to-users {--force : Force conversion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert existing suppliers to user accounts with supplier role';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=================================================');
        $this->info('  Supplier to User Conversion Tool');
        $this->info('=================================================');
        $this->newLine();

        // Get all suppliers
        $suppliers = Supplier::all();
        
        if ($suppliers->isEmpty()) {
            $this->warn('No suppliers found in the database.');
            return Command::SUCCESS;
        }

        $this->info("Found {$suppliers->count()} suppliers in the database.");
        $this->newLine();

        // Check which suppliers already have users
        $suppliersWithUsers = $suppliers->filter(function($supplier) {
            return User::where('supplier_id', $supplier->id)->exists();
        });

        $suppliersWithoutUsers = $suppliers->filter(function($supplier) {
            return !User::where('supplier_id', $supplier->id)->exists();
        });

        if ($suppliersWithUsers->count() > 0) {
            $this->line("Suppliers with existing user accounts: {$suppliersWithUsers->count()}");
        }

        if ($suppliersWithoutUsers->isEmpty()) {
            $this->info('All suppliers already have user accounts. No conversion needed.');
            return Command::SUCCESS;
        }

        $this->line("Suppliers without user accounts: {$suppliersWithoutUsers->count()}");
        $this->newLine();

        // Show suppliers that will be converted
        $this->table(
            ['ID', 'Supplier Name', 'PIC Name', 'PIC Email'],
            $suppliersWithoutUsers->map(function($supplier) {
                return [
                    $supplier->id,
                    $supplier->name,
                    $supplier->pic_name,
                    $supplier->pic_email,
                ];
            })->toArray()
        );

        $this->newLine();

        // Ask for confirmation unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to create user accounts for these suppliers?', true)) {
                $this->warn('Conversion cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->newLine();
        $this->info('Creating user accounts...');
        $this->newLine();

        $defaultPassword = 'password'; // Default password
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        $bar = $this->output->createProgressBar($suppliersWithoutUsers->count());
        $bar->start();

        foreach ($suppliersWithoutUsers as $supplier) {
            try {
                // Check if email already exists (for other users)
                $existingUser = User::where('email', $supplier->pic_email)
                    ->whereNull('supplier_id')
                    ->first();

                if ($existingUser) {
                    $errors[] = [
                        'supplier' => $supplier->name,
                        'email' => $supplier->pic_email,
                        'reason' => 'Email already exists for another user'
                    ];
                    $errorCount++;
                    $bar->advance();
                    continue;
                }

                // Create user for supplier
                User::create([
                    'name' => $supplier->pic_name,
                    'email' => $supplier->pic_email,
                    'password' => Hash::make($defaultPassword),
                    'role' => 'supplier',
                    'supplier_id' => $supplier->id,
                    'is_active' => true,
                ]);

                $successCount++;
            } catch (\Exception $e) {
                $errors[] = [
                    'supplier' => $supplier->name,
                    'email' => $supplier->pic_email,
                    'reason' => $e->getMessage()
                ];
                $errorCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Show results
        $this->info('=================================================');
        $this->info('  Conversion Results');
        $this->info('=================================================');
        $this->newLine();
        
        $this->line("✓ Successfully created: <info>{$successCount}</info> user accounts");
        
        if ($errorCount > 0) {
            $this->line("✗ Failed: <error>{$errorCount}</error> user accounts");
            $this->newLine();
            
            if (!empty($errors)) {
                $this->error('Errors encountered:');
                $this->table(
                    ['Supplier', 'Email', 'Reason'],
                    array_map(function($error) {
                        return [
                            $error['supplier'],
                            $error['email'],
                            $error['reason']
                        ];
                    }, $errors)
                );
            }
        }

        $this->newLine();
        
        if ($successCount > 0) {
            $this->info('Default credentials for new supplier users:');
            $this->line("Password: <comment>{$defaultPassword}</comment>");
            $this->newLine();
            $this->warn('⚠ IMPORTANT: Please inform suppliers to change their password after first login!');
            $this->warn('⚠ You can also manually reset passwords for each supplier user.');
        }

        $this->newLine();
        $this->info('Conversion completed!');

        return Command::SUCCESS;
    }
}

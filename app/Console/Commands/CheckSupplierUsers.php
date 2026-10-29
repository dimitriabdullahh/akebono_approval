<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Supplier;
use Illuminate\Console\Command;

class CheckSupplierUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supplier:check-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all supplier users and their supplier relationship';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=================================================');
        $this->info('  Checking Supplier Users');
        $this->info('=================================================');
        $this->newLine();

        // Get all users with supplier role
        $supplierUsers = User::where('role', 'supplier')->get();

        if ($supplierUsers->isEmpty()) {
            $this->warn('No supplier users found in the database.');
            return Command::SUCCESS;
        }

        $this->info("Found {$supplierUsers->count()} supplier user(s)");
        $this->newLine();

        $problems = [];
        $valid = [];

        foreach ($supplierUsers as $user) {
            $status = '✓ OK';
            $supplierName = '-';
            $issue = null;

            if (!$user->supplier_id) {
                $status = '✗ MISSING supplier_id';
                $issue = 'supplier_id is NULL';
                $problems[] = [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'issue' => $issue
                ];
            } else {
                $supplier = Supplier::find($user->supplier_id);
                
                if (!$supplier) {
                    $status = '✗ INVALID supplier_id';
                    $issue = "supplier_id={$user->supplier_id} does not exist";
                    $problems[] = [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'issue' => $issue
                    ];
                } else {
                    $supplierName = $supplier->name;
                    $valid[] = [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'supplier' => $supplierName
                    ];
                }
            }

            $this->line(sprintf(
                "  ID: %d | Email: %-30s | Status: %s",
                $user->id,
                $user->email,
                $status
            ));
            
            if ($supplierName !== '-') {
                $this->line("           Supplier: {$supplierName}");
            }
            
            if ($issue) {
                $this->line("           Issue: {$issue}");
            }
            
            $this->newLine();
        }

        // Summary
        $this->newLine();
        $this->info('=================================================');
        $this->info('  Summary');
        $this->info('=================================================');
        $this->line("Total Supplier Users: {$supplierUsers->count()}");
        $this->line("Valid Users: " . count($valid));
        $this->line("Users with Problems: " . count($problems));
        $this->newLine();

        if (count($problems) > 0) {
            $this->error('⚠ ATTENTION: Some supplier users have issues!');
            $this->newLine();
            $this->warn('These users will NOT be able to login properly:');
            
            foreach ($problems as $problem) {
                $this->line("  - User ID {$problem['user_id']} ({$problem['email']}): {$problem['issue']}");
            }
            
            $this->newLine();
            $this->info('💡 To fix this, you can:');
            $this->line('  1. Delete invalid users: php artisan tinker, then User::find(ID)->delete()');
            $this->line('  2. Create proper supplier user: php artisan supplier:create-user');
        } else {
            $this->info('✓ All supplier users are properly configured!');
        }

        return Command::SUCCESS;
    }
}

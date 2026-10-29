<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Supplier;
use Illuminate\Console\Command;

class ValidateSupplierSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supplier:validate-setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate supplier setup and fix common issues';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=================================================');
        $this->info('  Validating Supplier Setup');
        $this->info('=================================================');
        $this->newLine();

        $issues = [];
        $fixed = 0;

        // 1. Check all supplier users
        $this->info('Checking supplier users...');
        $supplierUsers = User::where('role', 'supplier')->get();
        
        foreach ($supplierUsers as $user) {
            $userIssues = [];
            
            if (!$user->supplier_id) {
                $userIssues[] = 'Missing supplier_id';
            } elseif (!Supplier::find($user->supplier_id)) {
                $userIssues[] = "Invalid supplier_id ({$user->supplier_id})";
            }
            
            if (count($userIssues) > 0) {
                $issues[] = [
                    'type' => 'User',
                    'id' => $user->id,
                    'email' => $user->email,
                    'issues' => implode(', ', $userIssues)
                ];
            }
        }

        // 2. Check all suppliers have at least one user
        $this->info('Checking suppliers without users...');
        $suppliers = Supplier::all();
        
        foreach ($suppliers as $supplier) {
            if ($supplier->users()->count() === 0) {
                $this->warn("  ⚠ Supplier '{$supplier->name}' (ID: {$supplier->id}) has no users");
            }
        }

        $this->newLine();

        // Display results
        if (count($issues) === 0) {
            $this->info('✓ All supplier users are properly configured!');
            $this->info('✓ System is ready to use.');
            return Command::SUCCESS;
        }

        $this->error('⚠ Found ' . count($issues) . ' issue(s):');
        $this->newLine();

        foreach ($issues as $issue) {
            $this->line("  [{$issue['type']} ID: {$issue['id']}]");
            $this->line("    Email: {$issue['email']}");
            $this->line("    Issues: {$issue['issues']}");
            $this->newLine();
        }

        $this->warn('To fix these issues:');
        $this->line('  1. Delete invalid users: php artisan tinker, then User::find(ID)->delete()');
        $this->line('  2. Create proper users: php artisan supplier:create-user');
        $this->newLine();

        return Command::FAILURE;
    }
}

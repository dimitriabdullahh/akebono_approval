<?php

namespace App\Console\Commands;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSupplierUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supplier:create-user 
                            {supplier_id? : The ID of the supplier}
                            {--email= : Email for the supplier user}
                            {--name= : Name for the supplier user}
                            {--password= : Password for the supplier user (default: password)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user account for a supplier';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=================================================');
        $this->info('  Create Supplier User Account');
        $this->info('=================================================');
        $this->newLine();

        // Get supplier ID
        $supplierId = $this->argument('supplier_id');
        
        if (!$supplierId) {
            // Show list of suppliers
            $suppliers = Supplier::all();
            
            if ($suppliers->isEmpty()) {
                $this->error('No suppliers found in the database.');
                return Command::FAILURE;
            }

            $this->table(
                ['ID', 'Supplier Name', 'Vendor Code', 'PIC Name', 'PIC Email'],
                $suppliers->map(function($supplier) {
                    $hasUser = User::where('supplier_id', $supplier->id)->exists();
                    return [
                        $supplier->id,
                        $supplier->name . ($hasUser ? ' [HAS USER]' : ''),
                        $supplier->vendor_code,
                        $supplier->pic_name,
                        $supplier->pic_email,
                    ];
                })->toArray()
            );

            $supplierId = $this->ask('Enter Supplier ID');
        }

        // Find supplier
        $supplier = Supplier::find($supplierId);
        
        if (!$supplier) {
            $this->error("Supplier with ID {$supplierId} not found.");
            return Command::FAILURE;
        }

        // Check if supplier already has a user
        $existingUser = User::where('supplier_id', $supplier->id)->first();
        
        if ($existingUser) {
            $this->warn("Supplier '{$supplier->name}' already has a user account:");
            $this->line("  Name: {$existingUser->name}");
            $this->line("  Email: {$existingUser->email}");
            $this->newLine();
            
            if (!$this->confirm('Do you want to create another user for this supplier?', false)) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->newLine();
        $this->info("Creating user for: {$supplier->name}");
        $this->newLine();

        // Get user details
        $email = $this->option('email') ?? $this->ask('Email', $supplier->pic_email);
        $name = $this->option('name') ?? $this->ask('Name', $supplier->pic_name);
        $password = $this->option('password') ?? $this->secret('Password (default: password)') ?? 'password';

        // Validate
        $validator = Validator::make([
            'email' => $email,
            'name' => $name,
        ], [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->line("  - {$error}");
            }
            return Command::FAILURE;
        }

        // Create user
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'supplier',
                'supplier_id' => $supplier->id,
                'is_active' => true,
            ]);

            $this->newLine();
            $this->info('✓ User account created successfully!');
            $this->newLine();
            
            $this->line('User Details:');
            $this->line("  Name: {$user->name}");
            $this->line("  Email: {$user->email}");
            $this->line("  Role: {$user->role}");
            $this->line("  Supplier: {$supplier->name}");
            $this->newLine();
            
            $this->line('Login Credentials:');
            $this->line("  Email: <info>{$email}</info>");
            $this->line("  Password: <comment>{$password}</comment>");
            $this->newLine();
            
            $this->warn('⚠ IMPORTANT: Please inform the supplier to change their password after first login!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to create user: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

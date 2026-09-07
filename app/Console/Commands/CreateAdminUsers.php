<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateAdminUsers extends Command
{
    protected $signature = 'user:create-admins';
    protected $description = 'Create all admin users (QA, Purchasing, PPC)';

    public function handle()
    {
        $this->info('Creating admin users...');

        $admins = [
            [
                'name' => 'Admin QA',
                'email' => 'admin@akebono.local',
                'password' => 'password',
                'role' => 'admin_qa'
            ],
            [
                'name' => 'Admin Purchasing',
                'email' => 'admin.purchasing@akebono.local', 
                'password' => 'purchasing123',
                'role' => 'admin_purchasing'
            ],
            [
                'name' => 'Admin PPC',
                'email' => 'admin.ppc@akebono.local',
                'password' => 'ppc123', 
                'role' => 'admin_ppc'
            ]
        ];

        foreach ($admins as $adminData) {
            if (User::where('email', $adminData['email'])->exists()) {
                $this->warn("User {$adminData['email']} already exists!");
                continue;
            }

            User::create([
                'name' => $adminData['name'],
                'email' => $adminData['email'],
                'password' => bcrypt($adminData['password']),
                'role' => $adminData['role'],
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            $this->info("✓ Created: {$adminData['name']} ({$adminData['email']})");
            $this->line("  Password: {$adminData['password']}");
        }

        $this->newLine();
        $this->info('Admin users creation completed!');
        
        return 0;
    }
}
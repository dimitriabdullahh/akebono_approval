<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateTestUser extends Command
{
    protected $signature = 'user:create-test';
    protected $description = 'Create a test user for development';

    public function handle()
    {
        // Check if user already exists
        if (User::where('email', 'admin@akebono.local')->exists()) {
            $this->error('User already exists!');
            return 1;
        }

        User::create([
            'name' => 'Admin QA',
            'email' => 'admin@akebono.local',
            'password' => bcrypt('password'),
            'role' => 'admin_qa',
            'email_verified_at' => now(),
        ]);

        $this->info('User created successfully!');
        $this->line('Email: admin@akebono.local');
        $this->line('Password: password');

        return 0;
    }
}

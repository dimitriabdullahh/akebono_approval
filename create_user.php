<?php

require_once 'bootstrap/app.php';

$app = app();
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Create test user
\App\Models\User::create([
    'name' => 'Admin QA',
    'email' => 'admin@akebono.local',
    'password' => bcrypt('password'),
    'role' => 'admin_qa',
    'email_verified_at' => now(),
]);

echo "User created successfully!\n";
echo "Email: admin@akebono.local\n";
echo "Password: password\n";

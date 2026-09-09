<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

$user = User::where('email', 'admin@akebono.local')->first();

if ($user) {
    echo "✓ User sudah ada: admin@akebono.local\n";
} else {
    User::create([
        'name' => 'Admin QA',
        'email' => 'admin@akebono.local',
        'password' => bcrypt('password'),
        'role' => 'admin_qa',
    ]);
    echo "✓ User berhasil dibuat: admin@akebono.local / password\n";
}

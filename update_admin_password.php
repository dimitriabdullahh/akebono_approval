<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Get application instance
$app = app();

// Find admin QA user
$admin = User::where('email', 'admin@akebono.local')->first();

if ($admin) {
    // Update password
    $admin->password = Hash::make('qa123');
    $admin->save();
    
    echo "✅ PASSWORD ADMIN QA BERHASIL DIUBAH\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    echo "📧 Email        : {$admin->email}\n";
    echo "👤 Name         : {$admin->name}\n";
    echo "🔐 Password     : qa123\n";
    echo "✅ Status       : AKTIF\n\n";
    echo "═══════════════════════════════════════════════════════════\n";
} else {
    echo "❌ Admin QA user tidak ditemukan\n";
    echo "Membuat admin user baru...\n\n";
    
    $admin = User::create([
        'name' => 'Admin QA Akebono',
        'email' => 'admin@akebono.local',
        'password' => Hash::make('qa123'),
        'role' => 'admin_qa',
        'email_verified_at' => now(),
    ]);
    
    echo "✅ ADMIN QA USER BERHASIL DIBUAT\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    echo "📧 Email        : {$admin->email}\n";
    echo "👤 Name         : {$admin->name}\n";
    echo "🔐 Password     : qa123\n";
    echo "✅ Status       : AKTIF\n\n";
    echo "═══════════════════════════════════════════════════════════\n";
}

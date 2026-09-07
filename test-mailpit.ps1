# Test Mailpit Email Script
# Tes pengiriman email dan membuka web interface

Write-Host ""
Write-Host "📧 Testing Mailpit Email System" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# Cek status Mailpit
Write-Host "1️⃣  Checking Mailpit status..." -ForegroundColor Yellow
$mailpitRunning = docker ps --filter "name=mailpit" --format "{{.Names}}"

if ($mailpitRunning -ne "mailpit") {
    Write-Host "❌ Mailpit tidak berjalan!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Jalankan terlebih dahulu: .\mailpit.ps1 start" -ForegroundColor Cyan
    Write-Host ""
    exit 1
}

Write-Host "✅ Mailpit sedang berjalan" -ForegroundColor Green
Write-Host ""

# Cek konfigurasi Laravel
Write-Host "2️⃣  Checking Laravel mail configuration..." -ForegroundColor Yellow
$envContent = Get-Content .env -Raw
if ($envContent -match "MAIL_HOST=127\.0\.0\.1" -and $envContent -match "MAIL_PORT=1025") {
    Write-Host "✅ Konfigurasi email sudah benar" -ForegroundColor Green
} else {
    Write-Host "⚠️  Periksa konfigurasi MAIL_HOST dan MAIL_PORT di .env" -ForegroundColor Yellow
}
Write-Host ""

# Test email dengan artisan command
Write-Host "3️⃣  Sending test email via Laravel..." -ForegroundColor Yellow
Write-Host ""

# Cek apakah command test:email ada
$commandExists = php artisan list | Select-String "test:email"

if ($commandExists) {
    php artisan test:email
} else {
    Write-Host "⚠️  Command test:email tidak ditemukan" -ForegroundColor Yellow
    Write-Host "Mencoba alternatif lain..." -ForegroundColor Yellow
    Write-Host ""
    
    # Alternatif: gunakan tinker untuk kirim email test
    $tinkerScript = @"
use Illuminate\Support\Facades\Mail;
use App\Mail\RequestLetterMail;

try {
    Mail::raw('This is a test email from Akebono Approval System', function(`$message) {
        `$message->to('test@example.com')
                ->subject('Test Email - Mailpit');
    });
    echo "✅ Test email berhasil dikirim!\n";
} catch (Exception `$e) {
    echo "❌ Error: " . `$e->getMessage() . "\n";
}
"@
    
    $tinkerScript | php artisan tinker
}

Write-Host ""

# Buka web interface
Write-Host "4️⃣  Opening Mailpit web interface..." -ForegroundColor Yellow
Start-Sleep -Seconds 2
Start-Process "http://localhost:8025"

Write-Host "✅ Browser opened at http://localhost:8025" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Petunjuk:" -ForegroundColor Cyan
Write-Host "   - Cek inbox di Mailpit web interface" -ForegroundColor White
Write-Host "   - Email test seharusnya muncul disana" -ForegroundColor White
Write-Host "   - Jika tidak ada email, cek Laravel logs" -ForegroundColor White
Write-Host ""

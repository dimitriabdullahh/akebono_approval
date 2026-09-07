# Script untuk Download dan Setup Mailpit
# Usage: .\setup-mailpit.ps1

Write-Host "🚀 Mailpit Setup Script" -ForegroundColor Cyan
Write-Host "========================" -ForegroundColor Cyan
Write-Host ""

$mailpitDir = ".\mailpit"
$mailpitExe = "$mailpitDir\mailpit.exe"

# Cek apakah sudah terinstall
if (Test-Path $mailpitExe) {
    Write-Host "✅ Mailpit sudah terinstall di: $mailpitExe" -ForegroundColor Green
    $run = Read-Host "Jalankan Mailpit sekarang? (y/n)"
    if ($run -eq 'y') {
        Write-Host ""
        Write-Host "🚀 Starting Mailpit..." -ForegroundColor Cyan
        Write-Host "   SMTP: http://127.0.0.1:1025" -ForegroundColor Yellow
        Write-Host "   Web:  http://localhost:8025" -ForegroundColor Yellow
        Write-Host ""
        Write-Host "Press Ctrl+C to stop" -ForegroundColor Gray
        & $mailpitExe
    }
    exit
}

# Download Mailpit
Write-Host "📥 Downloading Mailpit..." -ForegroundColor Yellow

$version = "v1.20.4"  # Update sesuai versi terbaru
$downloadUrl = "https://github.com/axllent/mailpit/releases/download/$version/mailpit-windows-amd64.zip"
$zipFile = "mailpit.zip"

# Download
Invoke-WebRequest -Uri $downloadUrl -OutFile $zipFile -UseBasicParsing
Write-Host "✅ Download selesai" -ForegroundColor Green

# Extract
Write-Host "📦 Extracting..." -ForegroundColor Yellow
New-Item -ItemType Directory -Force -Path $mailpitDir | Out-Null
Expand-Archive -Path $zipFile -DestinationPath $mailpitDir -Force

# Cleanup
Remove-Item $zipFile

Write-Host "✅ Mailpit berhasil diinstall!" -ForegroundColor Green
Write-Host ""
Write-Host "🚀 Starting Mailpit..." -ForegroundColor Cyan
Write-Host "   SMTP: http://127.0.0.1:1025" -ForegroundColor Yellow
Write-Host "   Web:  http://localhost:8025" -ForegroundColor Yellow
Write-Host ""
Write-Host "Press Ctrl+C to stop" -ForegroundColor Gray
Write-Host ""

# Jalankan Mailpit
& $mailpitExe

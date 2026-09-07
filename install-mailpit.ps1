# Script untuk Download dan Setup Mailpit
Write-Host "Mailpit Setup Script" -ForegroundColor Cyan
Write-Host "====================" -ForegroundColor Cyan
Write-Host ""

# Cek apakah sudah terinstall
if (Test-Path ".\mailpit\mailpit.exe") {
    Write-Host "Mailpit sudah terinstall!" -ForegroundColor Green
    Write-Host "Menjalankan Mailpit..." -ForegroundColor Cyan
    Write-Host "SMTP: http://127.0.0.1:1025" -ForegroundColor Yellow
    Write-Host "Web:  http://localhost:8025" -ForegroundColor Yellow
    Write-Host ""
    Start-Process -FilePath ".\mailpit\mailpit.exe" -NoNewWindow
    exit
}

Write-Host "Downloading Mailpit..." -ForegroundColor Yellow

# Download URL
$url = "https://github.com/axllent/mailpit/releases/download/v1.20.4/mailpit-windows-amd64.zip"

# Download
Invoke-WebRequest -Uri $url -OutFile "mailpit.zip"
Write-Host "Download selesai!" -ForegroundColor Green

# Extract
Write-Host "Extracting..." -ForegroundColor Yellow
New-Item -ItemType Directory -Force -Path "mailpit" | Out-Null
Expand-Archive -Path "mailpit.zip" -DestinationPath "mailpit" -Force
Remove-Item "mailpit.zip"

Write-Host ""
Write-Host "Mailpit berhasil diinstall!" -ForegroundColor Green
Write-Host "Menjalankan Mailpit..." -ForegroundColor Cyan
Write-Host "SMTP: http://127.0.0.1:1025" -ForegroundColor Yellow
Write-Host "Web:  http://localhost:8025" -ForegroundColor Yellow
Write-Host ""

# Jalankan Mailpit
Start-Process -FilePath ".\mailpit\mailpit.exe" -NoNewWindow

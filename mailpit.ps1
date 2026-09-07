# Mailpit Management Script
# Usage: .\mailpit.ps1 [start|stop|restart|status|logs|open]

param(
    [Parameter(Position=0)]
    [ValidateSet('start', 'stop', 'restart', 'status', 'logs', 'open', 'help')]
    [string]$Action = 'help'
)

$ContainerName = "mailpit"
$SmtpPort = 1025
$WebPort = 8025

function Test-DockerRunning {
    try {
        docker info | Out-Null
        return $true
    } catch {
        Write-Host "❌ Docker daemon tidak berjalan!" -ForegroundColor Red
        Write-Host "Silakan start Docker Desktop terlebih dahulu." -ForegroundColor Yellow
        return $false
    }
}

function Start-Mailpit {
    if (-not (Test-DockerRunning)) { return }
    
    Write-Host "🚀 Starting Mailpit..." -ForegroundColor Cyan
    
    # Cek apakah container sudah ada
    $existing = docker ps -a --filter "name=$ContainerName" --format "{{.Names}}"
    
    if ($existing -eq $ContainerName) {
        # Container ada, cek statusnya
        $status = docker ps --filter "name=$ContainerName" --format "{{.Names}}"
        if ($status -eq $ContainerName) {
            Write-Host "✅ Mailpit sudah berjalan!" -ForegroundColor Green
        } else {
            Write-Host "▶️  Starting existing container..." -ForegroundColor Yellow
            docker start $ContainerName
            Write-Host "✅ Mailpit berhasil dijalankan!" -ForegroundColor Green
        }
    } else {
        # Container belum ada, buat baru
        Write-Host "📦 Creating new Mailpit container..." -ForegroundColor Yellow
        docker run -d `
            --name $ContainerName `
            -p ${WebPort}:8025 `
            -p ${SmtpPort}:1025 `
            --restart unless-stopped `
            axllent/mailpit
        Write-Host "✅ Mailpit berhasil dibuat dan dijalankan!" -ForegroundColor Green
    }
    
    Write-Host ""
    Write-Host "📧 SMTP Server: localhost:$SmtpPort" -ForegroundColor White
    Write-Host "🌐 Web Interface: http://localhost:$WebPort" -ForegroundColor White
    Write-Host ""
}

function Stop-Mailpit {
    if (-not (Test-DockerRunning)) { return }
    
    Write-Host "⏹️  Stopping Mailpit..." -ForegroundColor Cyan
    
    $status = docker ps --filter "name=$ContainerName" --format "{{.Names}}"
    if ($status -eq $ContainerName) {
        docker stop $ContainerName | Out-Null
        Write-Host "✅ Mailpit berhasil dihentikan!" -ForegroundColor Green
    } else {
        Write-Host "ℹ️  Mailpit sudah dalam keadaan stop." -ForegroundColor Yellow
    }
}

function Restart-Mailpit {
    Stop-Mailpit
    Start-Sleep -Seconds 2
    Start-Mailpit
}

function Get-MailpitStatus {
    if (-not (Test-DockerRunning)) { return }
    
    Write-Host "📊 Status Mailpit:" -ForegroundColor Cyan
    Write-Host ""
    
    $existing = docker ps -a --filter "name=$ContainerName" --format "{{.Names}}"
    
    if ($existing -eq $ContainerName) {
        $status = docker ps --filter "name=$ContainerName" --format "{{.Names}}"
        if ($status -eq $ContainerName) {
            Write-Host "✅ Status: RUNNING" -ForegroundColor Green
            Write-Host ""
            docker ps --filter "name=$ContainerName" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
            Write-Host ""
            Write-Host "📧 SMTP Server: localhost:$SmtpPort" -ForegroundColor White
            Write-Host "🌐 Web Interface: http://localhost:$WebPort" -ForegroundColor White
        } else {
            Write-Host "⏹️  Status: STOPPED" -ForegroundColor Yellow
            Write-Host ""
            Write-Host "Jalankan: .\mailpit.ps1 start" -ForegroundColor Cyan
        }
    } else {
        Write-Host "❌ Container Mailpit belum dibuat." -ForegroundColor Red
        Write-Host ""
        Write-Host "Jalankan: .\mailpit.ps1 start" -ForegroundColor Cyan
    }
}

function Get-MailpitLogs {
    if (-not (Test-DockerRunning)) { return }
    
    $existing = docker ps -a --filter "name=$ContainerName" --format "{{.Names}}"
    
    if ($existing -eq $ContainerName) {
        Write-Host "📋 Logs Mailpit (Ctrl+C untuk keluar):" -ForegroundColor Cyan
        Write-Host ""
        docker logs -f $ContainerName
    } else {
        Write-Host "❌ Container Mailpit tidak ditemukan." -ForegroundColor Red
    }
}

function Open-MailpitWeb {
    $status = docker ps --filter "name=$ContainerName" --format "{{.Names}}"
    
    if ($status -eq $ContainerName) {
        Write-Host "🌐 Membuka Mailpit Web Interface..." -ForegroundColor Cyan
        Start-Process "http://localhost:$WebPort"
    } else {
        Write-Host "❌ Mailpit tidak berjalan!" -ForegroundColor Red
        Write-Host "Jalankan: .\mailpit.ps1 start" -ForegroundColor Cyan
    }
}

function Show-Help {
    Write-Host ""
    Write-Host "📧 Mailpit Management Script" -ForegroundColor Cyan
    Write-Host "=============================" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Usage: .\mailpit.ps1 [command]" -ForegroundColor White
    Write-Host ""
    Write-Host "Commands:" -ForegroundColor Yellow
    Write-Host "  start      Start Mailpit container" -ForegroundColor White
    Write-Host "  stop       Stop Mailpit container" -ForegroundColor White
    Write-Host "  restart    Restart Mailpit container" -ForegroundColor White
    Write-Host "  status     Show Mailpit status" -ForegroundColor White
    Write-Host "  logs       Show Mailpit logs (live)" -ForegroundColor White
    Write-Host "  open       Open Mailpit web interface in browser" -ForegroundColor White
    Write-Host "  help       Show this help message" -ForegroundColor White
    Write-Host ""
    Write-Host "Examples:" -ForegroundColor Yellow
    Write-Host "  .\mailpit.ps1 start" -ForegroundColor Gray
    Write-Host "  .\mailpit.ps1 status" -ForegroundColor Gray
    Write-Host "  .\mailpit.ps1 open" -ForegroundColor Gray
    Write-Host ""
}

# Main Script Logic
switch ($Action) {
    'start'   { Start-Mailpit }
    'stop'    { Stop-Mailpit }
    'restart' { Restart-Mailpit }
    'status'  { Get-MailpitStatus }
    'logs'    { Get-MailpitLogs }
    'open'    { Open-MailpitWeb }
    'help'    { Show-Help }
}

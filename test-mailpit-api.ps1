# Test Mailpit API Script
# Demonstrates how to interact with Mailpit API

Write-Host ""
Write-Host "🧪 Testing Mailpit API" -ForegroundColor Cyan
Write-Host "======================" -ForegroundColor Cyan
Write-Host ""

$apiBase = "http://localhost:8025/api/v1"

# Function to format JSON output
function Show-JsonPretty {
    param([string]$json)
    $obj = $json | ConvertFrom-Json
    $obj | ConvertTo-Json -Depth 5 | Write-Host
}

# Test 1: Check Mailpit Status
Write-Host "1️⃣  Testing API Connection..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$apiBase/messages" -Method GET
    Write-Host "✅ API Connected Successfully" -ForegroundColor Green
    Write-Host "   Total messages: $($response.total)" -ForegroundColor White
    Write-Host "   Unread: $($response.unread)" -ForegroundColor White
    Write-Host ""
} catch {
    Write-Host "❌ Failed to connect to Mailpit API" -ForegroundColor Red
    Write-Host "   Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
    Write-Host "Make sure Mailpit is running: .\mailpit.ps1 start" -ForegroundColor Yellow
    exit 1
}

# Test 2: Send a test email via Laravel
Write-Host "2️⃣  Sending test email via Laravel..." -ForegroundColor Yellow

# Create temporary PHP script to send email
$phpScript = @'
<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;

try {
    Mail::raw('This is a test email sent via Mailpit API testing script.', function($message) {
        $message->to('apitest@example.com')
                ->subject('Mailpit API Test - ' . date('Y-m-d H:i:s'));
    });
    echo "SUCCESS";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
'@

$phpScript | Out-File -FilePath "temp_send_email.php" -Encoding UTF8

$result = php temp_send_email.php
Remove-Item "temp_send_email.php" -Force

if ($result -eq "SUCCESS") {
    Write-Host "✅ Test email sent successfully" -ForegroundColor Green
} else {
    Write-Host "⚠️  $result" -ForegroundColor Yellow
}
Write-Host ""

# Wait a bit for email to be processed
Start-Sleep -Seconds 2

# Test 3: Retrieve messages from API
Write-Host "3️⃣  Retrieving messages from Mailpit API..." -ForegroundColor Yellow
try {
    $messages = Invoke-RestMethod -Uri "$apiBase/messages" -Method GET
    
    if ($messages.total -gt 0) {
        Write-Host "✅ Found $($messages.total) message(s)" -ForegroundColor Green
        Write-Host ""
        
        foreach ($msg in $messages.messages) {
            Write-Host "   📧 Message Details:" -ForegroundColor Cyan
            Write-Host "      ID: $($msg.ID)" -ForegroundColor White
            Write-Host "      From: $($msg.From.Address)" -ForegroundColor White
            Write-Host "      To: $($msg.To[0].Address)" -ForegroundColor White
            Write-Host "      Subject: $($msg.Subject)" -ForegroundColor White
            Write-Host "      Date: $($msg.Created)" -ForegroundColor White
            Write-Host "      Size: $($msg.Size) bytes" -ForegroundColor White
            Write-Host ""
        }
        
        # Test 4: Get detailed message
        if ($messages.messages.Count -gt 0) {
            $firstMsgId = $messages.messages[0].ID
            Write-Host "4️⃣  Getting detailed message content..." -ForegroundColor Yellow
            
            $detail = Invoke-RestMethod -Uri "$apiBase/message/$firstMsgId" -Method GET
            Write-Host "✅ Message detail retrieved" -ForegroundColor Green
            Write-Host ""
            Write-Host "   Message ID: $($detail.ID)" -ForegroundColor White
            Write-Host "   Subject: $($detail.Subject)" -ForegroundColor White
            Write-Host "   Text Preview:" -ForegroundColor White
            Write-Host "   $($detail.Text)" -ForegroundColor Gray
            Write-Host ""
        }
    } else {
        Write-Host "ℹ️  No messages in inbox" -ForegroundColor Yellow
        Write-Host ""
    }
} catch {
    Write-Host "❌ Failed to retrieve messages" -ForegroundColor Red
    Write-Host "   Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
}

# Test 5: API Endpoints Summary
Write-Host "5️⃣  Available API Endpoints:" -ForegroundColor Yellow
Write-Host ""
Write-Host "   GET    $apiBase/messages              - List all messages" -ForegroundColor White
Write-Host "   GET    $apiBase/message/{id}          - Get message detail" -ForegroundColor White
Write-Host "   DELETE $apiBase/message/{id}          - Delete specific message" -ForegroundColor White
Write-Host "   DELETE $apiBase/messages              - Delete all messages" -ForegroundColor White
Write-Host "   GET    $apiBase/search?query=xxx      - Search messages" -ForegroundColor White
Write-Host "   GET    $apiBase/tags                  - Get all tags" -ForegroundColor White
Write-Host ""

# Test 6: Search Example
Write-Host "6️⃣  Testing Search API..." -ForegroundColor Yellow
try {
    $searchResult = Invoke-RestMethod -Uri "$apiBase/search?query=test" -Method GET
    Write-Host "✅ Search completed" -ForegroundColor Green
    Write-Host "   Found: $($searchResult.total) message(s) matching 'test'" -ForegroundColor White
    Write-Host ""
} catch {
    Write-Host "⚠️  Search failed: $($_.Exception.Message)" -ForegroundColor Yellow
    Write-Host ""
}

# Summary
Write-Host "📊 Summary:" -ForegroundColor Cyan
Write-Host "   Web Interface: http://localhost:8025" -ForegroundColor White
Write-Host "   API Base URL: $apiBase" -ForegroundColor White
Write-Host "   SMTP Server: localhost:1025" -ForegroundColor White
Write-Host ""
Write-Host "💡 Tips:" -ForegroundColor Yellow
Write-Host "   - Use Invoke-RestMethod for API calls in PowerShell" -ForegroundColor White
Write-Host "   - All endpoints return JSON format" -ForegroundColor White
Write-Host "   - Message IDs are returned in the messages list" -ForegroundColor White
Write-Host "   - Use DELETE carefully - it permanently removes messages" -ForegroundColor White
Write-Host ""

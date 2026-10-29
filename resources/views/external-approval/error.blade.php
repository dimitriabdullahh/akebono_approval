<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Error' }} - PT Akebono</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-container {
            max-width: 600px;
            padding: 20px;
        }
        .error-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            overflow: hidden;
            text-align: center;
            padding: 50px 30px;
        }
        .error-icon {
            font-size: 80px;
            color: #ef4444;
            margin-bottom: 20px;
        }
        .error-title {
            font-size: 28px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 15px;
        }
        .error-message {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .error-reason {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
            border-radius: 6px;
            color: #991b1b;
        }
        .help-box {
            background: #f9fafb;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: left;
        }
        .help-box h4 {
            color: #1f2937;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .help-box ul {
            margin: 0;
            padding-left: 20px;
            color: #6b7280;
        }
        .help-box li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <i class="ti ti-alert-triangle error-icon"></i>
            <h1 class="error-title">{{ $title ?? 'Error' }}</h1>
            <p class="error-message">
                {{ $message ?? 'An error occurred while processing your request.' }}
            </p>

            @if(isset($reason))
            <div class="error-reason">
                <strong>Reason:</strong> {{ $reason }}
            </div>
            @endif

            @if(isset($token) && $token->isExpired())
            <div class="help-box">
                <h4>⏰ This approval link has expired</h4>
                <p style="color: #6b7280; margin: 10px 0;">
                    The approval link expired on <strong>{{ $token->expires_at->format('d F Y, H:i') }}</strong>.
                </p>
                <p style="color: #6b7280; margin: 0;">
                    Please contact the QA department to request a new approval link.
                </p>
            </div>
            @elseif(isset($token) && $token->used_at)
            <div class="help-box">
                <h4>✓ This approval has already been processed</h4>
                <p style="color: #6b7280; margin: 10px 0;">
                    This approval was completed on <strong>{{ $token->used_at->format('d F Y, H:i') }}</strong>.
                </p>
                <p style="color: #6b7280; margin: 0;">
                    Each approval link can only be used once for security reasons.
                </p>
            </div>
            @else
            <div class="help-box">
                <h4>❓ Need Help?</h4>
                <ul>
                    <li>Make sure you're using the latest approval link from your email</li>
                    <li>Check that the link hasn't expired</li>
                    <li>Verify you haven't already processed this approval</li>
                    <li>Contact the QA department if you need assistance</li>
                </ul>
            </div>
            @endif

            <div style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #e5e7eb;">
                <p style="color: #6b7280; font-size: 14px; margin: 0;">
                    <strong>PT Akebono Brake Astra Indonesia</strong><br>
                    Supplier Document Approval System
                </p>
            </div>
        </div>
    </div>
</body>
</html>

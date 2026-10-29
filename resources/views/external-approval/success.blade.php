<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success - PT Akebono</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .success-container {
            max-width: 600px;
            padding: 20px;
        }
        .success-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            overflow: hidden;
            text-align: center;
            padding: 50px 30px;
        }
        .success-icon {
            font-size: 80px;
            color: #10b981;
            margin-bottom: 20px;
        }
        .success-title {
            font-size: 28px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 15px;
        }
        .success-message {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .info-box {
            background: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
            border-radius: 6px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #d1fae5;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #065f46;
        }
        .info-value {
            color: #1f2937;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            @if($approval->status === 'APPROVED')
                <i class="ti ti-circle-check success-icon"></i>
                <h1 class="success-title">✓ Document Approved Successfully</h1>
                <p class="success-message">
                    Thank you for your approval. Your decision has been recorded and the supplier will be notified.
                </p>
            @else
                <i class="ti ti-alert-circle" style="font-size: 80px; color: #f59e0b; margin-bottom: 20px;"></i>
                <h1 class="success-title">Revision Requested</h1>
                <p class="success-message">
                    Your revision request has been sent to the supplier. They will be notified to make the necessary corrections.
                </p>
            @endif

            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Division:</span>
                    <span class="info-value">{{ $approval->division }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Decision:</span>
                    <span class="info-value">
                        @if($approval->status === 'APPROVED')
                            <strong style="color: #10b981;">APPROVED</strong>
                        @else
                            <strong style="color: #f59e0b;">REVISION REQUESTED</strong>
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Timestamp:</span>
                    <span class="info-value">{{ now()->format('d F Y, H:i:s') }}</span>
                </div>
                @if($approval->status === 'APPROVED')
                <div class="info-row">
                    <span class="info-label">Digital Signature:</span>
                    <span class="info-value">✓ Generated</span>
                </div>
                @endif
            </div>

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

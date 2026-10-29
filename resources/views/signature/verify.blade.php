<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Digital Signature - Akebono Approval System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .verification-card {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .verification-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .verification-body {
            padding: 2rem;
        }
        .status-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .verified-badge {
            background: #10b981;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            display: inline-block;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .invalid-badge {
            background: #ef4444;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            display: inline-block;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #6b7280;
        }
        .info-value {
            color: #111827;
            font-weight: 500;
        }
        .verification-footer {
            background: #f9fafb;
            padding: 1.5rem 2rem;
            text-align: center;
            color: #6b7280;
            font-size: 0.875rem;
        }
        .company-logo {
            max-width: 200px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="verification-card">
        <div class="verification-header">
            <h2 class="mb-0">
                <i class="ti ti-shield-check me-2"></i>
                Digital Signature Verification
            </h2>
            <p class="mb-0 mt-2 opacity-75">Akebono Approval System</p>
        </div>

        <div class="verification-body text-center">
            @if($verified)
                <!-- Valid Signature -->
                <div class="status-icon text-success">
                    <i class="ti ti-circle-check"></i>
                </div>
                <span class="verified-badge">
                    <i class="ti ti-check me-1"></i>
                    VERIFIED
                </span>
                <h4 class="mt-3 mb-4">Signature is Valid</h4>

                <div class="text-start">
                    <div class="info-row">
                        <span class="info-label">Signature Type:</span>
                        <span class="info-value">
                            <span class="badge bg-{{ $signature->signature_type_color }}">
                                {{ $signature->signature_type_label }}
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Employee Name:</span>
                        <span class="info-value">{{ $signature->employee_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">NPK:</span>
                        <span class="info-value">{{ $signature->employee_npk }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Division:</span>
                        <span class="info-value">{{ $signature->division }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Role:</span>
                        <span class="info-value">{{ $signature->role }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Document Type:</span>
                        <span class="info-value">{{ $signature->document_type }}</span>
                    </div>
                    @if($signature->document_reference)
                    <div class="info-row">
                        <span class="info-label">Document Reference:</span>
                        <span class="info-value">{{ $signature->document_reference }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Action Date:</span>
                        <span class="info-value">{{ $signature->action_at->format('d M Y H:i') }} WIB</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Verification Code:</span>
                        <span class="info-value">
                            <code style="background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                {{ $signature->verification_code }}
                            </code>
                        </span>
                    </div>
                </div>

                <div class="alert alert-info mt-4" role="alert">
                    <i class="ti ti-info-circle me-2"></i>
                    <small>
                        This digital signature has been verified <strong>{{ $signature->verification_count }}</strong> time(s).
                        @if($signature->last_verified_at)
                            Last verified: {{ $signature->last_verified_at->diffForHumans() }}
                        @endif
                    </small>
                </div>
            @else
                <!-- Invalid Signature -->
                <div class="status-icon text-danger">
                    <i class="ti ti-alert-triangle"></i>
                </div>
                <span class="invalid-badge">
                    <i class="ti ti-x me-1"></i>
                    INVALID
                </span>
                <h4 class="mt-3 mb-4">Signature Could Not Be Verified</h4>
                
                <div class="alert alert-danger" role="alert">
                    <i class="ti ti-alert-circle me-2"></i>
                    {{ $error ?? 'The signature token is invalid, expired, or has been revoked.' }}
                </div>

                <p class="text-muted mb-0">
                    If you believe this is an error, please contact the document issuer or 
                    Akebono Quality Assurance department.
                </p>
            @endif
        </div>

        <div class="verification-footer">
            <p class="mb-1">
                <strong>PT. AKEBONO BRAKE ASTRA INDONESIA</strong>
            </p>
            <p class="mb-0">
                <i class="ti ti-shield-lock me-1"></i>
                This is an automated verification system. Signature authenticity is guaranteed by cryptographic security.
            </p>
            <p class="mt-2 mb-0">
                <small>Verified on: {{ now()->format('d F Y H:i:s') }} WIB</small>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSW Approval Request</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 650px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-header p {
            margin: 8px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .email-body {
            padding: 30px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #1f2937;
        }
        .info-card {
            background-color: #f9fafb;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-card h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #1e3a8a;
            font-weight: 600;
        }
        .info-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            width: 160px;
            color: #4b5563;
            font-size: 14px;
        }
        .info-value {
            flex: 1;
            color: #1f2937;
            font-size: 14px;
        }
        .alert-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .alert-box p {
            margin: 0;
            font-size: 14px;
            color: #92400e;
        }
        .cta-section {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            margin: 8px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background-color: #10b981;
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #059669;
        }
        .btn-secondary {
            background-color: #6b7280;
            color: #ffffff;
        }
        .btn-secondary:hover {
            background-color: #4b5563;
        }
        .instructions {
            background-color: #eff6ff;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .instructions h4 {
            margin: 0 0 12px 0;
            color: #1e40af;
            font-size: 15px;
        }
        .instructions ol {
            margin: 0;
            padding-left: 20px;
        }
        .instructions li {
            margin: 8px 0;
            font-size: 14px;
            color: #1e3a8a;
        }
        .email-footer {
            background-color: #f9fafb;
            padding: 25px 30px;
            border-top: 1px solid #e5e7eb;
            font-size: 13px;
            color: #6b7280;
            text-align: center;
        }
        .email-footer p {
            margin: 5px 0;
        }
        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 25px 0;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 10px;
            }
            .email-body {
                padding: 20px;
            }
            .info-row {
                flex-direction: column;
            }
            .info-label {
                width: 100%;
                margin-bottom: 4px;
            }
            .btn {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>📋 PSW Approval Required</h1>
            <p>Part Submission Warrant - {{ $approval->division }} Review</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                <p>Dear {{ $approval->division }} Team,</p>
                <p>A <strong>Part Submission Warrant (PSW)</strong> has been submitted and requires your approval.</p>
            </div>

            <!-- Document Information -->
            <div class="info-card">
                <h3>📄 Document Information</h3>
                <div class="info-row">
                    <span class="info-label">Document Type:</span>
                    <span class="info-value">Part Submission Warrant (PSW)</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Document Number:</span>
                    <span class="info-value">#{{ $documentData['document_number'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Submission Date:</span>
                    <span class="info-value">{{ $documentData['submission_date'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">✅ Passed QA Check - Pending {{ $approval->division }} Approval</span>
                </div>
            </div>

            <!-- Supplier & Part Information -->
            <div class="info-card">
                <h3>🏭 Supplier & Part Information</h3>
                <div class="info-row">
                    <span class="info-label">Supplier:</span>
                    <span class="info-value">{{ $documentData['supplier_name'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Model:</span>
                    <span class="info-value">{{ $documentData['model'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Part Name:</span>
                    <span class="info-value">{{ $documentData['psw_part_name'] ?? $documentData['part_name'] ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Part Number:</span>
                    <span class="info-value"><strong>{{ $documentData['psw_part_number'] ?? $documentData['part_number'] ?? '-' }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Due Date:</span>
                    <span class="info-value">{{ $documentData['due_date'] }}</span>
                </div>
            </div>

            <!-- Alert Box -->
            <div class="alert-box">
                <p><strong>⚠️ Important:</strong> This approval request will expire on <strong>{{ $expiresAt->format('d F Y, H:i') }}</strong></p>
            </div>

            <div class="divider"></div>

            <!-- Call to Action -->
            <div class="cta-section">
                <p style="margin-bottom: 20px; font-size: 15px; color: #1f2937;">
                    Please review the document and take action:
                </p>
                <a href="{{ $approvalUrl }}" class="btn btn-primary">
                    📝 Review & Approve Document
                </a>
            </div>

            <!-- Instructions -->
            <div class="instructions">
                <h4>📌 How to Review:</h4>
                <ol>
                    <li>Click the "Review & Approve Document" button above</li>
                    <li>Review the PSW document details carefully</li>
                    <li>Check all submission information for accuracy</li>
                    <li>Choose to <strong>Approve</strong> or <strong>Request Revision</strong></li>
                    <li>Add comments if needed (optional for approval, required for revision)</li>
                    <li>Confirm your decision</li>
                </ol>
            </div>

            <p style="margin-top: 25px; font-size: 14px; color: #6b7280;">
                <strong>Note:</strong> You can approve this document directly through this email without logging into the portal. 
                Your approval will be recorded with a digital signature and QR code verification.
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>PT Akebono Brake Astra Indonesia</strong></p>
            <p>Supplier Document Approval System</p>
            <p style="margin-top: 15px; font-size: 12px;">
                This is an automated email. Please do not reply directly to this message.
            </p>
            <p style="font-size: 12px;">
                If you have questions, please contact the QA department.
            </p>
        </div>
    </div>
</body>
</html>

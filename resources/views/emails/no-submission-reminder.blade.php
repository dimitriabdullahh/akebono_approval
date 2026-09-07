<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $level === 1 ? 'Reminder' : 'Escalation Level ' . $level }} - Document Submission Required</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .header {
            @if($level === 1)
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            @elseif($level === 2)
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            @elseif($level === 3)
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            @elseif($level === 4)
            background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
            @else
            background: linear-gradient(135deg, #991B1B 0%, #7F1D1D 100%);
            @endif
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .content {
            padding: 30px 20px;
        }

        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .greeting strong {
            color: #0F2942;
        }

        .section-title {
            color: #0F2942;
            font-size: 18px;
            font-weight: 600;
            margin: 25px 0 15px;
            border-bottom: 3px solid #3B82F6;
            padding-bottom: 10px;
        }

        .detail-box {
            @if($level === 1)
            background-color: #eff6ff;
            border-left: 4px solid #3B82F6;
            @elseif($level === 2)
            background-color: #fffbeb;
            border-left: 4px solid #F59E0B;
            @elseif($level === 3)
            background-color: #fef2f2;
            border-left: 4px solid #EF4444;
            @elseif($level === 4)
            background-color: #fee2e2;
            border-left: 4px solid #DC2626;
            @else
            background-color: #fef2f2;
            border-left: 4px solid #991B1B;
            @endif
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .detail-box strong {
            @if($level === 1)
            color: #1e3a8a;
            @elseif($level === 2)
            color: #92400e;
            @else
            color: #7f1d1d;
            @endif
            display: block;
            margin-bottom: 8px;
        }

        .warning-box {
            @if($level >= 3)
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            @else
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            @endif
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #7f1d1d;
        }

        .warning-box strong {
            font-size: 16px;
            display: block;
            margin-bottom: 10px;
        }

        .cta-button {
            display: inline-block;
            @if($level === 1)
            background-color: #3B82F6;
            @elseif($level === 2)
            background-color: #F59E0B;
            @else
            background-color: #EF4444;
            @endif
            color: white;
            padding: 14px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            margin: 25px 0;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        .cta-button:hover {
            @if($level === 1)
            background-color: #2563EB;
            @elseif($level === 2)
            background-color: #D97706;
            @else
            background-color: #DC2626;
            @endif
        }

        .action-list {
            list-style: none;
            margin: 15px 0;
        }

        .action-list li {
            padding: 10px 15px;
            background-color: #f8fafc;
            border-left: 4px solid #3B82F6;
            margin: 8px 0;
            border-radius: 4px;
        }

        .action-list strong {
            color: #0F2942;
        }

        .escalation-history {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .escalation-history h3 {
            color: #991b1b;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .escalation-item {
            padding: 8px 12px;
            background-color: white;
            border-left: 3px solid #ef4444;
            margin: 8px 0;
            border-radius: 3px;
        }

        .footer {
            background-color: #f1f5f9;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            font-size: 13px;
            color: #64748b;
        }

        .footer-logo {
            font-weight: 600;
            color: #0F2942;
            margin-bottom: 8px;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .detail-table thead {
            @if($level === 1)
            background-color: #1e3a8a;
            @elseif($level === 2)
            background-color: #92400e;
            @else
            background-color: #7f1d1d;
            @endif
            color: white;
        }

        .detail-table th {
            padding: 10px;
            text-align: left;
        }

        .detail-table td {
            padding: 10px;
        }

        .detail-table tbody tr:nth-child(even) {
            @if($level === 1)
            background-color: #eff6ff;
            @elseif($level === 2)
            background-color: #fffbeb;
            @else
            background-color: #fef2f2;
            @endif
        }

        .detail-table tbody tr:nth-child(odd) {
            @if($level === 1)
            background-color: #dbeafe;
            @elseif($level === 2)
            background-color: #fef3c7;
            @else
            background-color: #fee2e2;
            @endif
        }

        @media (max-width: 600px) {
            .content {
                padding: 20px 15px;
            }

            .section-title {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>
                @if($level === 1)
                    ⏰ Reminder: Document Submission Required
                @elseif($level === 2)
                    ⚠️ Escalation Level 2: Supervisor
                @elseif($level === 3)
                    🚨 Escalation Level 3: Head Department
                @elseif($level === 4)
                    🔴 Escalation Level 4: General Manager
                @else
                    ⛔ CRITICAL - Escalation Level 5: Direktur Utama
                @endif
            </h1>
            <p>Supplier Document Submission System | PT Akebono Astra Indonesia</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                @if($level === 1)
                    Dear <strong>{{ $recipientName ?? 'PIC Supplier' }}</strong>,
                @else
                    Dear <strong>{{ $recipientName ?? 'Management' }}</strong>,
                @endif
            </div>

            @if($level === 1)
                <p>This is a <strong>reminder</strong> to submit the required documents through the Supplier Portal.</p>
            @else
                <p>This is an <strong>escalation notification</strong> regarding the pending document submission from supplier.</p>
            @endif

            <!-- Detail Box -->
            <div class="detail-box">
                <strong>📌 Request Information</strong>
                <p style="margin: 8px 0;"><strong>Supplier:</strong> {{ $request->supplier->name }}</p>
                <p style="margin: 8px 0;"><strong>Vendor Code:</strong> {{ $request->supplier->vendor_code }}</p>
                <p style="margin: 8px 0;"><strong>Model:</strong> {{ $request->model }}</p>
                <p style="margin: 8px 0;"><strong>Request Created:</strong> {{ $request->created_at->format('d F Y H:i') }}</p>
                <p style="margin: 8px 0;"><strong>Due Date:</strong> {{ $request->due_date->format('d F Y') }}</p>
            </div>

            <!-- Parts Table -->
            <div class="section-title">🔧 Part List</div>
            @if($request->requestParts && $request->requestParts->count() > 0)
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;">No</th>
                            <th style="width: 50%;">Part Name</th>
                            <th style="width: 40%;">Part Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($request->requestParts as $index => $part)
                            <tr>
                                <td><strong>{{ $index + 1 }}</strong></td>
                                <td>{{ $part->part_name }}</td>
                                <td><strong>{{ $part->part_number }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <!-- Document Progress -->
            <div class="warning-box">
                <strong>
                    @if($level === 1)
                        ⚠️ NO DOCUMENTS SUBMITTED YET
                    @else
                        🚨 CRITICAL: SUPPLIER HAS NOT SUBMITTED ANY DOCUMENTS
                    @endif
                </strong>
                <p style="margin-top: 10px;">
                    <strong>Current Progress:</strong> 0 / {{ $mandatoryDocs }} Mandatory Documents<br>
                    <strong>Total Documents:</strong> 0 / {{ $totalDocs }} Documents
                </p>
                @if($level > 1)
                    <p style="margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(0,0,0,0.1);">
                        <strong>Initial Request Date:</strong> {{ $request->created_at->format('d F Y H:i') }}<br>
                        <strong>Time Elapsed:</strong> {{ $request->created_at->diffForHumans() }}
                    </p>
                @endif
            </div>

            @if($level === 5 && count($escalationHistory) > 0)
                <!-- Escalation History (Level 5 only) -->
                <div class="escalation-history">
                    <h3>📋 Escalation History</h3>
                    <p style="margin-bottom: 10px; color: #7f1d1d;">
                        The supplier has not submitted any documents despite multiple reminders and escalations:
                    </p>
                    @foreach($escalationHistory as $history)
                        <div class="escalation-item">
                            <strong>{{ $history['level'] }}</strong> → {{ $history['status'] }}
                            <br>
                            <small style="color: #64748b;">{{ $history['sent_at']->format('d F Y H:i') }}</small>
                        </div>
                    @endforeach
                    <p style="margin-top: 10px; color: #991b1b; font-weight: 600;">
                        ⛔ As of now, <strong>NO DOCUMENTS</strong> have been received from the supplier.
                    </p>
                </div>
            @endif

            @if($level > 1)
                <!-- Action Required -->
                <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
                    <strong style="color: #92400e; font-size: 16px;">📢 Action Required:</strong>
                    <p style="margin-top: 10px; color: #78350f;">
                        @if($level === 2)
                            As the Supervisor, please follow up with the PIC and ensure the documents are submitted immediately.
                        @elseif($level === 3)
                            As the Head of Department, urgent intervention is required to ensure document submission.
                        @elseif($level === 4)
                            As the General Manager, this matter requires your immediate attention and action.
                        @else
                            As the Director, this critical situation requires your direct intervention.
                        @endif
                    </p>
                </div>
            @endif

            <!-- CTA Button -->
            <div style="text-align: center;">
                @if($loginUrl)
                    <a href="{{ $loginUrl }}" class="cta-button">
                        @if($level === 1)
                            🔗 Access Supplier Portal & Submit Documents
                        @else
                            🔗 View Request Details & Take Action
                        @endif
                    </a>
                @else
                    <a href="{{ config('app.url') . '/login' }}" class="cta-button">
                        @if($level === 1)
                            🔗 Login to Supplier Portal
                        @else
                            🔗 Login & Review Request
                        @endif
                    </a>
                @endif
            </div>

            @if($level === 1)
                <!-- Instructions (Level 1 only) -->
                <div style="background-color: #f0fdf4; border-left: 4px solid #10B981; padding: 15px; margin: 20px 0; border-radius: 4px;">
                    <strong style="color: #15803d;">✓ How to Submit Documents:</strong>
                    <ul class="action-list" style="margin-top: 10px;">
                        <li><strong>1. Access Portal:</strong> Click the button above to access the Supplier Portal</li>
                        <li><strong>2. Login:</strong> Use your registered email to login</li>
                        <li><strong>3. Find Request:</strong> Locate the request with Model: <strong>{{ $request->model }}</strong></li>
                        <li><strong>4. Upload Documents:</strong> Upload all required documents (PDF, JPG, PNG max 10MB)</li>
                        <li><strong>5. Submit:</strong> Review and submit your documents for approval</li>
                    </ul>
                </div>
            @endif

            <p style="margin-top: 25px; padding-top: 25px; border-top: 1px solid #e2e8f0;">
                @if($level === 1)
                    Please submit the documents as soon as possible to avoid delays in the approval process.
                @else
                    Your immediate attention to this matter is greatly appreciated.
                @endif
            </p>

            <p style="margin-top: 15px; color: #0F2942; font-weight: 600;">
                Thank you for your cooperation.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-logo">PT Akebono Astra Indonesia</div>
            <p>Supplier Document Submission System | Quality Assurance Division</p>
            <p style="margin-top: 10px; font-size: 12px;">
                This is an automated notification. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>

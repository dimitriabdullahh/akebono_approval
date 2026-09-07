<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $level === 1 ? 'Reminder' : 'Escalation Level ' . $level }} - Bundle Submission Required</title>
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
            @else
            background-color: #fef2f2;
            border-left: 4px solid #EF4444;
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

        .progress-box {
            background-color: #f0fdf4;
            border-left: 4px solid #10B981;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .progress-box.pending {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
        }

        .progress-bar {
            background-color: #e5e7eb;
            border-radius: 10px;
            height: 24px;
            margin: 10px 0;
            overflow: hidden;
        }

        .progress-fill {
            @if($progressInfo['uploaded_documents'] == $progressInfo['total_documents'])
            background-color: #10B981;
            @elseif($progressInfo['uploaded_documents'] > 0)
            background-color: #F59E0B;
            @else
            background-color: #EF4444;
            @endif
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 12px;
            transition: width 0.3s ease;
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
                    ⏰ Reminder: Bundle Submission Required
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
            <p>Document Bundle Submission | PT Akebono Astra Indonesia</p>
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
                <p>This is a <strong>reminder</strong> to complete and submit the required document bundle through the Supplier Portal.</p>
            @else
                <p>This is an <strong>escalation notification</strong> regarding the pending document bundle submission.</p>
            @endif

            <!-- Detail Box -->
            <div class="detail-box">
                <strong style="display: block; margin-bottom: 12px;">📌 Document Request Information</strong>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 6px 0; font-weight: 600; color: #64748b; width: 30%;">Supplier:</td>
                        <td style="padding: 6px 0; color: #0F2942;">{{ $documentRequest->supplier->name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-weight: 600; color: #64748b;">Model:</td>
                        <td style="padding: 6px 0; color: #0F2942; font-weight: 600;">{{ $documentRequest->model }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-weight: 600; color: #64748b;">Total Parts:</td>
                        <td style="padding: 6px 0; color: #0F2942;">{{ $overallProgress['total_parts'] }} Part(s)</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-weight: 600; color: #64748b;">Due Date:</td>
                        <td style="padding: 6px 0; color: #DC2626; font-weight: 600;">{{ $documentRequest->due_date->format('d F Y') }}</td>
                    </tr>
                </table>
            </div>

            <!-- Overall Progress Box -->
            <div class="progress-box {{ $overallProgress['draft_parts'] > 0 ? 'pending' : '' }}">
                <strong>📊 Overall Document Bundle Progress</strong>
                <p style="margin: 8px 0;">
                    <strong>Parts Status:</strong> {{ $overallProgress['submitted_parts'] }} / {{ $overallProgress['total_parts'] }} Parts Submitted
                    <br>
                    <strong>Pending Parts:</strong> {{ $overallProgress['draft_parts'] }} Part(s) Still in Draft
                </p>
                <p style="margin: 8px 0;">
                    <strong>Total Documents Required:</strong> {{ $overallProgress['total_documents'] }} Documents
                    @if($overallProgress['mandatory_total'] > 0)
                    <br>
                    <strong>Mandatory Documents:</strong> {{ $overallProgress['mandatory_total'] }} Documents
                    @endif
                </p>
                
                <p style="margin: 8px 0;">
                    <strong>Uploaded:</strong> {{ $overallProgress['uploaded_documents'] }} / {{ $overallProgress['total_documents'] }} Documents
                    @if($overallProgress['mandatory_total'] > 0)
                    <br>
                    <strong>Mandatory Uploaded:</strong> {{ $overallProgress['mandatory_uploaded'] }} / {{ $overallProgress['mandatory_total'] }} Documents
                    @endif
                </p>
                
                @php
                    $overallPercentage = $overallProgress['total_documents'] > 0 
                        ? round(($overallProgress['uploaded_documents'] / $overallProgress['total_documents']) * 100) 
                        : 0;
                @endphp
                
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $overallPercentage }}%;">
                        {{ $overallPercentage }}%
                    </div>
                </div>
            </div>

            <!-- Individual Parts Progress -->
            @if(count($partsProgress) > 1)
            <div class="section-title">📋 Progress per Part</div>
            
            <table style="width: 100%; border-collapse: collapse; margin: 15px 0; background-color: white; border-radius: 6px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <thead>
                    <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #0F2942; font-size: 14px;">Part Name</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: #0F2942; font-size: 14px;">Part Number</th>
                        <th style="padding: 12px; text-align: center; font-weight: 600; color: #0F2942; font-size: 14px;">Documents</th>
                        <th style="padding: 12px; text-align: center; font-weight: 600; color: #0F2942; font-size: 14px;">Progress</th>
                        <th style="padding: 12px; text-align: center; font-weight: 600; color: #0F2942; font-size: 14px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($partsProgress as $partInfo)
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 12px; color: #0F2942; font-weight: 500; font-size: 14px;">
                            {{ $partInfo['part_name'] }}
                        </td>
                        <td style="padding: 12px; color: #64748b; font-size: 13px; font-family: 'Courier New', monospace;">
                            {{ $partInfo['part_number'] }}
                        </td>
                        <td style="padding: 12px; text-align: center; color: #475569; font-size: 13px;">
                            <strong>{{ $partInfo['uploaded_documents'] }}</strong> / {{ $partInfo['total_documents'] }}
                            @if($partInfo['mandatory_total'] > 0)
                            <br>
                            <span style="font-size: 11px; color: #64748b;">
                                (Mandatory: <strong>{{ $partInfo['mandatory_uploaded'] }}</strong>/{{ $partInfo['mandatory_total'] }})
                            </span>
                            @endif
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <div style="background-color: #e5e7eb; border-radius: 10px; height: 20px; overflow: hidden; min-width: 80px;">
                                <div style="
                                    width: {{ $partInfo['progress_percentage'] }}%; 
                                    height: 100%; 
                                    background-color: {{ $partInfo['submission_status'] === 'SUBMITTED' ? '#10B981' : ($partInfo['progress_percentage'] > 0 ? '#F59E0B' : '#EF4444') }};
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    color: white;
                                    font-weight: 600;
                                    font-size: 11px;
                                    transition: width 0.3s ease;
                                ">
                                    {{ $partInfo['progress_percentage'] }}%
                                </div>
                            </div>
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <span style="
                                padding: 6px 12px; 
                                border-radius: 12px; 
                                font-size: 12px; 
                                font-weight: 600;
                                background-color: {{ $partInfo['submission_status'] === 'SUBMITTED' ? '#d1fae5' : '#fef3c7' }};
                                color: {{ $partInfo['submission_status'] === 'SUBMITTED' ? '#065f46' : '#92400e' }};
                                white-space: nowrap;
                            ">
                                {{ $partInfo['submission_status'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            <!-- Warning -->
            <div class="warning-box">
                <strong>
                    @if($level === 1)
                        ⚠️ ACTION REQUIRED: Bundle Not Submitted
                    @else
                        🚨 URGENT: Bundle Submission Still Pending
                    @endif
                </strong>
                <p style="margin-top: 10px;">
                    @if($overallProgress['submitted_parts'] > 0 && $overallProgress['draft_parts'] > 0)
                        <strong>{{ $overallProgress['submitted_parts'] }} part(s)</strong> have been submitted, but 
                        <strong>{{ $overallProgress['draft_parts'] }} part(s)</strong> are still pending.
                    @elseif($overallProgress['uploaded_documents'] > 0 && $overallProgress['draft_parts'] > 0)
                        Although <strong>{{ $overallProgress['uploaded_documents'] }} document(s)</strong> have been uploaded across {{ $overallProgress['total_parts'] }} part(s), 
                        <strong>{{ $overallProgress['draft_parts'] }} part(s)</strong> have <strong>NOT been SUBMITTED</strong> yet.
                    @else
                        <strong>{{ $overallProgress['draft_parts'] }} part(s)</strong> are still pending with <strong>no submission</strong> yet.
                    @endif
                </p>
                <p style="margin-top: 10px; font-weight: 600;">
                    Please complete the upload and <strong>SUBMIT ALL PARTS</strong> to proceed with the approval process.
                </p>
            </div>

            @if($level > 1)
                <!-- Previous Reminders -->
                <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
                    <strong style="color: #92400e; font-size: 16px;">📢 Previous Reminders:</strong>
                    <p style="margin-top: 10px; color: #78350f;">
                        @if($reminder->pic_reminder_sent_at)
                            PIC was reminded on {{ $reminder->pic_reminder_sent_at->format('d F Y H:i') }}
                        @endif
                        @if($level >= 3 && $reminder->supervisor_escalation_sent_at)
                            <br>Supervisor was notified on {{ $reminder->supervisor_escalation_sent_at->format('d F Y H:i') }}
                        @endif
                        @if($level >= 4 && $reminder->head_dept_escalation_sent_at)
                            <br>Head Department was escalated on {{ $reminder->head_dept_escalation_sent_at->format('d F Y H:i') }}
                        @endif
                        @if($level >= 5 && $reminder->gm_escalation_sent_at)
                            <br>General Manager was escalated on {{ $reminder->gm_escalation_sent_at->format('d F Y H:i') }}
                        @endif
                    </p>
                    <p style="margin-top: 10px; color: #92400e; font-weight: 600;">
                        The bundle is still pending submission. Please take immediate action.
                    </p>
                </div>
            @endif

            @if($level === 5 && count($escalationHistory) > 0)
                <!-- Full Escalation History (Level 5 only) -->
                <div class="escalation-history">
                    <h3>📋 Complete Escalation History</h3>
                    @foreach($escalationHistory as $history)
                        <div class="escalation-item">
                            <strong>{{ $history['level'] }}</strong> → {{ $history['status'] }}
                            <br>
                            <small style="color: #64748b;">{{ $history['sent_at']->format('d F Y H:i') }}</small>
                        </div>
                    @endforeach
                    <p style="margin-top: 10px; color: #991b1b; font-weight: 600;">
                        ⛔ Despite all reminders, the bundle remains <strong>NOT SUBMITTED</strong>.
                    </p>
                </div>
            @endif

            <!-- CTA Button -->
            <div style="text-align: center;">
                @if($loginUrl)
                    <a href="{{ $loginUrl }}" class="cta-button">
                        🔗 Access Portal & Submit Bundle Now
                    </a>
                @else
                    <a href="{{ config('app.url') . '/login' }}" class="cta-button">
                        🔗 Login & Submit Bundle
                    </a>
                @endif
            </div>

            @if($level === 1)
                <!-- Instructions -->
                <div style="background-color: #f0fdf4; border-left: 4px solid #10B981; padding: 15px; margin: 20px 0; border-radius: 4px;">
                    <strong style="color: #15803d;">✓ How to Submit Bundle:</strong>
                    <ul class="action-list" style="margin-top: 10px;">
                        <li><strong>1. Access Portal:</strong> Click the button above</li>
                        <li><strong>2. Upload Documents:</strong> Upload all required documents for each part ({{ $overallProgress['mandatory_total'] }} mandatory total)</li>
                        <li><strong>3. Review:</strong> Check all documents are uploaded correctly for each part</li>
                        <li><strong>4. Submit Each Part:</strong> Click <strong>[SUBMIT BUNDLE]</strong> button for each part</li>
                    </ul>
                    <p style="margin-top: 10px; color: #15803d; font-weight: 600;">
                        ⚠️ Important: Save Draft does NOT complete the submission. You MUST click Submit Bundle for EACH part.
                    </p>
                </div>
            @endif

            <p style="margin-top: 25px; padding-top: 25px; border-top: 1px solid #e2e8f0;">
                @if($level === 1)
                    Please complete and submit all {{ $overallProgress['total_parts'] }} part(s) as soon as possible to avoid delays.
                @else
                    Your immediate attention and action are required to submit the pending {{ $overallProgress['draft_parts'] }} part(s).
                @endif
            </p>

            <p style="margin-top: 15px; color: #0F2942; font-weight: 600;">
                Thank you for your cooperation.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-logo">PT Akebono Astra Indonesia</div>
            <p>Document Bundle Submission System | Quality Assurance Division</p>
            <p style="margin-top: 10px; font-size: 12px;">
                This is an automated notification. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>

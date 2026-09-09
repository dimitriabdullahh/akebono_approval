<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminder Dokumen Supplier</title>
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
            max-width: 550px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .header {
            @if($level === 1)
            background-color: #3B82F6;
            @elseif($level === 2)
            background-color: #F59E0B;
            @elseif($level === 3)
            background-color: #EF4444;
            @elseif($level === 4)
            background-color: #DC2626;
            @else
            background-color: #991B1B;
            @endif
            color: white;
            padding: 25px 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 22px;
            margin: 0;
            font-weight: 600;
        }

        .header .subtitle {
            font-size: 13px;
            opacity: 0.9;
            margin-top: 5px;
        }

        .content {
            padding: 25px 20px;
        }

        .greeting {
            font-size: 15px;
            margin-bottom: 15px;
        }

        .greeting strong {
            color: #0F2942;
        }

        .intro {
            font-size: 15px;
            color: #333;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .info-box {
            background-color: #f8fafc;
            border-left: 4px solid #3B82F6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #64748b;
            font-weight: 600;
        }

        .info-value {
            color: #0F2942;
            font-weight: 600;
            text-align: right;
        }

        .info-value.highlight {
            color: #DC2626;
        }

        .progress-box {
            background-color: #fff9e6;
            border-left: 4px solid #F59E0B;
            padding: 12px;
            margin: 15px 0;
            border-radius: 4px;
            font-size: 14px;
            color: #92400e;
        }

        .cta-container {
            text-align: center;
            margin: 25px 0;
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
            padding: 13px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .cta-button:hover {
            opacity: 0.9;
        }

        .note {
            background-color: #f0fdf4;
            border-left: 4px solid #10B981;
            padding: 12px;
            margin: 15px 0;
            border-radius: 4px;
            font-size: 13px;
            color: #15803d;
        }

        .footer-text {
            font-size: 14px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }

        .footer {
            background-color: #f1f5f9;
            padding: 15px 20px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #64748b;
        }

        .footer .company {
            font-weight: 600;
            color: #0F2942;
            margin-bottom: 5px;
        }

        @media (max-width: 600px) {
            .info-row {
                flex-direction: column;
            }
            .info-value {
                text-align: left;
                margin-top: 4px;
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
                    Reminder: Submission Dokumen
                @elseif($level === 2)
                    Eskalasi Level 2
                @elseif($level === 3)
                    Eskalasi Level 3
                @elseif($level === 4)
                    Eskalasi Level 4
                @else
                    Eskalasi Kritis - Level 5
                @endif
            </h1>
            <div class="subtitle">PT Akebono Astra Indonesia</div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Sapaan -->
            <div class="greeting">
                Kepada Yth. <strong>{{ $recipientName ?? 'PIC Supplier' }}</strong>,
            </div>

            <!-- Intro Singkat -->
            <div class="intro">
                Mohon segera menyelesaikan submission dokumen bundle untuk:
            </div>

            <!-- Kotak Informasi Inti -->
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Supplier:</span>
                    <span class="info-value">{{ $documentRequest->supplier->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Model:</span>
                    <span class="info-value">{{ $documentRequest->model }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Jumlah Parts:</span>
                    <span class="info-value">{{ $overallProgress['total_parts'] }} Part</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Progress:</span>
                    <span class="info-value">{{ $overallProgress['submitted_parts'] }}/{{ $overallProgress['total_parts'] }} Submitted</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Batas Waktu:</span>
                    <span class="info-value highlight">{{ $documentRequest->due_date->format('d M Y') }}</span>
                </div>
            </div>

            <!-- Catatan Progress -->
            <div class="progress-box">
                <strong>⚠️ Status:</strong> {{ $overallProgress['draft_parts'] }} Part belum di-submit. Dokumen terupload: {{ $overallProgress['uploaded_documents'] }}/{{ $overallProgress['total_documents'] }} file.
            </div>

            <!-- CTA Button -->
            <div class="cta-container">
                @if($loginUrl)
                    <a href="{{ $loginUrl }}" class="cta-button">
                        KLIK LINK UNTUK LOGIN & SUBMIT
                    </a>
                @else
                    <a href="{{ config('app.url') . '/login' }}" class="cta-button">
                        KLIK LINK UNTUK LOGIN & SUBMIT
                    </a>
                @endif
            </div>

            <!-- Catatan Singkat -->
            <div class="note">
                ℹ️ Jika sudah login, link akan langsung membuka halaman dokumen. Pastikan semua file terupload sebelum submit.
            </div>

            <!-- Penutup -->
            <div class="footer-text">
                Terima kasih atas perhatian Anda. Jika ada pertanyaan, silakan hubungi tim QA kami.
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="company">PT Akebono Astra Indonesia</div>
            <div>Quality Assurance Division</div>
            <div style="margin-top: 8px; font-size: 11px;">
                ℹ️ Email otomatis - Mohon tidak membalas email ini
            </div>
        </div>
    </div>
</body>
</html>

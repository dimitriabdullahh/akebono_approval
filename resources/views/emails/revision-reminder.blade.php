<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengingat Revisi Dokumen</title>
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
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
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
            border-bottom: 3px solid #F59E0B;
            padding-bottom: 10px;
        }

        .detail-box {
            background-color: #fffbeb;
            border-left: 4px solid #F59E0B;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .detail-box strong {
            color: #92400e;
            display: block;
            margin-bottom: 8px;
        }

        .revision-item {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .revision-item .doc-name {
            font-weight: 600;
            color: #92400e;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .revision-note {
            background-color: white;
            border: 1px solid #fed7aa;
            padding: 12px;
            border-radius: 4px;
            margin: 10px 0;
            font-style: italic;
            color: #78350f;
        }

        .deadline-warning {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            color: #991b1b;
        }

        .deadline-warning strong {
            color: #7f1d1d;
            font-size: 16px;
        }

        .deadline-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #fed7aa;
            font-size: 14px;
        }

        .deadline-meta span {
            display: block;
        }

        .cta-button {
            display: inline-block;
            background-color: #F59E0B;
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
            background-color: #d97706;
        }

        .action-list {
            list-style: none;
            margin: 15px 0;
        }

        .action-list li {
            padding: 10px 15px;
            background-color: #f8fafc;
            border-left: 4px solid #F59E0B;
            margin: 8px 0;
            border-radius: 4px;
        }

        .action-list strong {
            color: #0F2942;
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

            .deadline-meta {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>⏰ Pengingat: Dokumen Perlu Revisi</h1>
            <p>Sistem Approval Dokumen Supplier | Batas Waktu Mendesak</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Kepada Yth,<br>
                <strong>{{ $supplier->pic_name }}</strong><br>
                {{ $supplier->name }}
            </div>

            <p><strong>⚠️ Kami ingatkan bahwa ada dokumen yang masih memerlukan revisi untuk request berikut:</strong></p>

            <!-- Detail Box -->
            <div class="detail-box">
                <strong>📌 Informasi Request</strong>
                <p style="margin: 8px 0;"><strong>Model:</strong> {{ $request->model }}</p>
                <p style="margin: 8px 0;"><strong>Project Category:</strong> {{ $request->project_category ?? '-' }}</p>
                <p style="margin: 8px 0;"><strong>Request Date:</strong> {{ $request->created_at->format('d F Y') }}</p>
                <p style="margin: 8px 0;"><strong>Deadline:</strong> {{ $request->due_date->format('d F Y') }}</p>
            </div>

            <!-- Parts Table -->
            <div class="section-title">🔧 Daftar Part</div>
            @if($request->requestParts && $request->requestParts->count() > 0)
                <table class="detail-table" style="margin-bottom: 20px; width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #92400e; color: white;">
                            <th style="padding: 10px; text-align: left; width: 10%;">No</th>
                            <th style="padding: 10px; text-align: left; width: 50%;">Part Name</th>
                            <th style="padding: 10px; text-align: left; width: 40%;">Part Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($request->requestParts as $index => $part)
                            <tr style="background-color: {{ $index % 2 == 0 ? '#fffbeb' : '#fef3c7' }};">
                                <td style="padding: 10px;"><strong>{{ $index + 1 }}</strong></td>
                                <td style="padding: 10px;">{{ $part->part_name }}</td>
                                <td style="padding: 10px;"><strong>{{ $part->part_number }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <!-- Outstanding Status -->
            <div class="revision-item">
                <div class="doc-name">
                    📝 Status: Ada <strong>{{ $revisionDocs->count() }}</strong> dokumen yang memerlukan revisi
                </div>
                <p style="margin-top: 10px; color: #92400e;">
                    Request ini belum dapat diproses lebih lanjut hingga semua dokumen yang ditolak telah diperbaiki dan diupload kembali.
                </p>
            </div>

            <!-- Deadline Warning -->
            <div class="deadline-warning">
                <strong>🚨 PENTING:</strong> Jika dokumen tidak diperbaiki sebelum deadline ({{ $request->due_date->format('d F Y') }}), proses approval akan terhenti dan dapat menunda pengiriman produk Anda. Mohon segera tindaklanjuti!
            </div>

            <!-- CTA Button -->
            <div style="text-align: center;">
                <a href="{{ $loginUrl ?? config('app.url') . '/login' }}" class="cta-button">🔗 Login & Upload Revisi Sekarang</a>
            </div>

            <!-- Instructions -->
            <div style="background-color: #f0fdf4; border-left: 4px solid #10B981; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong style="color: #15803d;">✓ Langkah-langkah Upload Revisi:</strong>
                <ul class="action-list" style="margin-top: 10px;">
                    @if($loginUrl ?? false)
                    <li><strong>1. Klik Tombol di Atas:</strong> Anda akan langsung masuk ke halaman upload dokumen</li>
                    <li><strong>2. Identifikasi Dokumen:</strong> Cari dokumen yang berstatus "REVISE" atau "REJECTED"</li>
                    <li><strong>3. Perbaiki Dokumen:</strong> Sesuaikan dokumen dengan catatan revisi yang diberikan oleh reviewer</li>
                    <li><strong>4. Upload Ulang:</strong> Unggah file yang sudah diperbaiki (PDF, JPG, PNG max 10MB)</li>
                    <li><strong>5. Konfirmasi:</strong> Pastikan semua dokumen yang ditolak sudah diupload ulang</li>
                    @else
                    <li><strong>1. Login ke Sistem:</strong> Klik tombol di atas untuk login menggunakan email: <strong>{{ $supplier->pic_email }}</strong></li>
                    <li><strong>2. Buka Dashboard:</strong> Lihat daftar request Anda dan cari request dengan Model: <strong>{{ $request->model }}</strong></li>
                    <li><strong>3. Identifikasi Dokumen:</strong> Cari dokumen yang berstatus "REVISE" atau "REJECTED" pada request tersebut</li>
                    <li><strong>4. Perbaiki Dokumen:</strong> Sesuaikan dokumen dengan catatan revisi yang diberikan oleh reviewer</li>
                    <li><strong>5. Upload Ulang:</strong> Unggah file yang sudah diperbaiki (PDF, JPG, PNG max 10MB)</li>
                    <li><strong>6. Konfirmasi:</strong> Pastikan semua dokumen yang ditolak sudah diupload ulang</li>
                    @endif
                </ul>
            </div>

            <p style="margin-top: 25px; padding-top: 25px; border-top: 1px solid #e2e8f0;">
                Jika Anda mengalami kesulitan teknis atau ada pertanyaan terkait revisi dokumen, silakan hubungi tim QA kami secara langsung.
            </p>

            <p style="margin-top: 15px; color: #0F2942; font-weight: 600;">
                Terima kasih atas perhatian dan kerjasama Anda.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-logo">PT Akebono Astra Indonesia</div>
            <p>Sistem Approval Dokumen Supplier | Quality Assurance Division</p>
            <p style="margin-top: 10px; font-size: 12px;">Email ini adalah notifikasi otomatis, mohon jangan membalas ke email ini.</p>
        </div>
    </div>
</body>
</html>

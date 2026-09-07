<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Selesai - Dokumen Disetujui</title>
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
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
            animation: bounce 0.6s ease;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
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

        .success-box {
            background: linear-gradient(135deg, #f0fdf4 0%, #dbeafe 100%);
            border: 2px solid #10B981;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            margin: 25px 0;
        }

        .success-box .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .success-box h2 {
            color: #065f46;
            font-size: 22px;
            margin-bottom: 10px;
        }

        .success-box p {
            color: #047857;
            font-size: 15px;
        }

        .section-title {
            color: #0F2942;
            font-size: 18px;
            font-weight: 600;
            margin: 25px 0 15px;
            border-bottom: 3px solid #10B981;
            padding-bottom: 10px;
        }

        .detail-box {
            background-color: #f0fdf4;
            border-left: 4px solid #10B981;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .detail-box strong {
            color: #047857;
            display: block;
            margin-bottom: 8px;
        }

        .document-list {
            list-style: none;
            margin: 15px 0;
        }

        .document-list li {
            background-color: #ecfdf5;
            border-left: 4px solid #10B981;
            padding: 12px 15px;
            margin: 10px 0;
            border-radius: 4px;
            color: #047857;
        }

        .document-list li strong {
            color: #065f46;
        }

        .next-steps {
            background-color: #eff6ff;
            border: 2px solid #bfdbfe;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }

        .next-steps h3 {
            color: #1e40af;
            font-size: 16px;
            margin-bottom: 15px;
        }

        .step-item {
            display: flex;
            margin: 12px 0;
            align-items: flex-start;
        }

        .step-number {
            background-color: #2563EB;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .step-text {
            color: #1e293b;
            font-size: 14px;
        }

        .cta-button {
            display: inline-block;
            background-color: #10B981;
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
            background-color: #059669;
        }

        .info-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            color: #78350f;
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

            .header {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .success-box {
                padding: 20px;
            }

            .success-box .icon {
                font-size: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>🎉 APPROVAL SELESAI!</h1>
            <p>Semua Dokumen Telah Disetujui | Sistem Approval Dokumen Supplier</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Kepada Yth,<br>
                <strong>{{ $supplier->pic_name }}</strong><br>
                {{ $supplier->name }}
            </div>

            <!-- Success Box -->
            <div class="success-box">
                <div class="icon">✅</div>
                <h2>Selamat!</h2>
                <p>Seluruh dokumen untuk request Anda telah <strong>berhasil disetujui</strong> oleh tim Quality Assurance, Purchasing, dan PPC.</p>
            </div>

            <!-- Detail Box -->
            <div class="detail-box">
                <strong>📌 Informasi Request</strong>
                <p style="margin: 8px 0;"><strong>Model:</strong> {{ $request->model }}</p>
            </div>

            <!-- Parts Table -->
            <div class="section-title">🔧 Daftar Part yang Disetujui</div>
            @if($request->requestParts && $request->requestParts->count() > 0)
                <table class="detail-table" style="margin-bottom: 20px;">
                    <thead>
                        <tr style="background-color: #065f46; color: white;">
                            <th style="padding: 10px; text-align: left; width: 10%;">No</th>
                            <th style="padding: 10px; text-align: left; width: 50%;">Part Name</th>
                            <th style="padding: 10px; text-align: left; width: 40%;">Part Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($request->requestParts as $index => $part)
                            <tr style="background-color: {{ $index % 2 == 0 ? '#ecfdf5' : '#f0fdf4' }};">
                                <td style="padding: 10px;"><strong>{{ $index + 1 }}</strong></td>
                                <td style="padding: 10px;">{{ $part->part_name }}</td>
                                <td style="padding: 10px;"><strong>{{ $part->part_number }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <!-- Approved Documents -->
            <div class="section-title">✅ Dokumen yang Telah Disetujui</div>
            
            <ul class="document-list">
                @foreach($approvedDocs as $doc)
                    <li>
                        <strong>✓ {{ $doc->documentType->name }}</strong><br>
                        <small style="opacity: 0.8;">Disetujui: {{ $doc->reviewed_at?->format('d M Y H:i') ?? '-' }}</small>
                    </li>
                @endforeach
            </ul>

            <!-- Next Steps -->
            <div class="next-steps">
                <h3>🎯 Langkah Selanjutnya</h3>
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-text">Tim Akebono akan segera memproses order Anda dan melakukan koordinasi lebih lanjut</div>
                </div>
                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-text">Jadwal produksi dan pengiriman akan dikonfirmasi sesuai dengan kesepakatan bersama</div>
                </div>
                <div class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-text">Dokumen yang telah disetujui dapat Anda unduh melalui portal supplier kapan saja</div>
                </div>
                <div class="step-item">
                    <div class="step-number">4</div>
                    <div class="step-text">Tim kami akan terus melakukan koordinasi untuk memastikan kelancaran proses produksi</div>
                </div>
            </div>

            <!-- CTA Button -->
            <div style="text-align: center;">
                <a href="{{ $portalUrl }}" class="cta-button">🔗 Akses Portal Supplier</a>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <strong>💡 Informasi Penting:</strong>
                <p style="margin-top: 8px;">Anda dapat mengunduh dan menyimpan dokumen yang telah disetujui melalui portal supplier. Dokumen-dokumen ini dapat digunakan untuk keperluan internal dan referensi di kemudian hari.</p>
            </div>

            <p style="margin-top: 25px; padding-top: 25px; border-top: 1px solid #e2e8f0;">
                Terima kasih telah bekerja sama dengan PT Akebono Astra Indonesia dalam proses submission dokumen ini. Kami menantikan kolaborasi yang baik dan saling menguntungkan di masa mendatang.
            </p>

            <p style="margin-top: 15px; color: #047857; font-weight: 600;">
                Salam Hormat,<br>
                Tim Quality Assurance<br>
                PT Akebono Astra Indonesia
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

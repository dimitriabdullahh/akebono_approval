<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi: Dokumen Supplier Telah Diterima</title>
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
            color: #0F2942;
            display: block;
            margin-bottom: 8px;
        }

        .document-item {
            background-color: #f8fafc;
            border-left: 4px solid #2563EB;
            padding: 12px 15px;
            margin: 10px 0;
            border-radius: 4px;
        }

        .document-item .doc-name {
            font-weight: 600;
            color: #0F2942;
            margin-bottom: 6px;
        }

        .document-item .doc-meta {
            font-size: 13px;
            color: #64748b;
        }

        .status-badge {
            display: inline-block;
            background-color: #dbeafe;
            color: #1e40af;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .action-box {
            background-color: #eff6ff;
            border: 2px solid #bfdbfe;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }

        .action-box h3 {
            color: #1e40af;
            font-size: 16px;
            margin-bottom: 12px;
        }

        .action-list {
            list-style: none;
            margin: 0;
        }

        .action-list li {
            padding: 8px 0;
            padding-left: 25px;
            position: relative;
            color: #1e293b;
        }

        .action-list li:before {
            content: "👉";
            position: absolute;
            left: 0;
        }

        .role-section {
            background-color: #f8fafc;
            border-radius: 6px;
            padding: 15px;
            margin: 10px 0;
        }

        .role-section strong {
            color: #2563EB;
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

            .section-title {
                font-size: 16px;
            }

            .action-box {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>✅ Dokumen Supplier Telah Diterima</h1>
            <p>Notifikasi Review & Approval | Sistem Approval Dokumen Supplier</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p>Kepada Tim Review & Approval,</p>

            <p>Supplier <strong>{{ $supplier->name }}</strong> telah mengunggah <strong>dokumen lengkap</strong> untuk request berikut. Dokumen siap untuk di-review sesuai dengan scope kewenangan masing-masing departemen.</p>

            <!-- Detail Box -->
            <div class="detail-box">
                <strong>📌 Informasi Request</strong>
                <p style="margin: 8px 0;"><strong>Model:</strong> {{ $request->model }}</p>
                <p style="margin: 8px 0;"><strong>Supplier:</strong> {{ $supplier->name }}</p>
            </div>

            <!-- Parts Table -->
            <div class="section-title">🔧 Daftar Part</div>
            @if($request->requestParts && $request->requestParts->count() > 0)
                <table class="detail-table" style="margin-bottom: 20px;">
                    <thead>
                        <tr style="background-color: #0F2942; color: white;">
                            <th style="padding: 10px; text-align: left; width: 10%;">No</th>
                            <th style="padding: 10px; text-align: left; width: 50%;">Part Name</th>
                            <th style="padding: 10px; text-align: left; width: 40%;">Part Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($request->requestParts as $index => $part)
                            <tr style="background-color: {{ $index % 2 == 0 ? '#f8fafc' : '#ffffff' }};">
                                <td style="padding: 10px;"><strong>{{ $index + 1 }}</strong></td>
                                <td style="padding: 10px;">{{ $part->part_name }}</td>
                                <td style="padding: 10px;"><strong>{{ $part->part_number }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <!-- Documents Submitted -->
            <div class="section-title">📄 Dokumen yang Telah Diunggah</div>
            
            @foreach($submittedDocs as $doc)
                <div class="document-item">
                    <div class="doc-name">✓ {{ $doc->documentType->name }}</div>
                    <div class="doc-meta">
                        <span class="status-badge">{{ $doc->status }}</span>
                        <span style="margin-left: 10px;">Diunggah: {{ $doc->uploaded_at?->format('d M Y H:i') ?? '-' }}</span>
                    </div>
                </div>
            @endforeach

            <!-- Action Required -->
            <div class="action-box">
                <h3>🎯 Tindakan Selanjutnya</h3>
                <p style="margin-bottom: 15px;">Silakan lakukan review terhadap dokumen-dokumen ini sesuai dengan scope kewenangan Anda:</p>
                
                <div class="role-section">
                    <strong>👤 Admin QA:</strong>
                    <p>Review dokumen-dokumen dengan QA scope dan berikan keputusan APPROVE atau MINTA REVISI</p>
                </div>

                <div class="role-section">
                    <strong>👤 Admin Purchasing:</strong>
                    <p>Review dokumen-dokumen dengan Purchasing scope dan berikan keputusan APPROVE atau MINTA REVISI</p>
                </div>

                <div class="role-section">
                    <strong>👤 Admin PPC:</strong>
                    <p>Review dokumen-dokumen dengan PPC scope dan berikan keputusan APPROVE atau MINTA REVISI</p>
                </div>

                <ul class="action-list">
                    <li>Jika dokumen sesuai dan lengkap: <strong>Klik APPROVE</strong></li>
                    <li>Jika ada yang perlu diperbaiki: <strong>Klik MINTA REVISI</strong> dan sertakan catatan perbaikan yang jelas</li>
                    <li>Supplier akan menerima notifikasi dan memiliki waktu 2 hari untuk melakukan revisi</li>
                    <li>Proses review berlanjut sampai semua dokumen wajib mendapat status APPROVED</li>
                </ul>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <strong>⏰ Informasi Penting:</strong>
                <p style="margin-top: 8px;">Deadline pengiriman dokumen adalah <strong>{{ $request->due_date->format('d F Y') }}</strong>. Pastikan semua review diselesaikan sebelum tanggal tersebut.</p>
            </div>

            <p style="margin-top: 25px; padding-top: 25px; border-top: 1px solid #e2e8f0;">
                Terima kasih atas perhatian dan kerjasama Anda dalam proses approval dokumen supplier ini.
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

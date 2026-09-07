<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi: Supplier Submit Dokumen</title>
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
            background: linear-gradient(135deg, #2563EB 0%, #1e40af 100%);
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

        .badge-count {
            display: inline-block;
            background-color: #fbbf24;
            color: #78350f;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 18px;
            font-weight: 700;
            margin: 10px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .content {
            padding: 30px 20px;
        }

        .section-title {
            color: #0F2942;
            font-size: 18px;
            font-weight: 600;
            margin: 25px 0 15px;
            border-bottom: 3px solid #2563EB;
            padding-bottom: 10px;
        }

        .detail-box {
            background-color: #eff6ff;
            border-left: 4px solid #2563EB;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .detail-box strong {
            color: #0F2942;
            display: block;
            margin-bottom: 8px;
        }

        .highlight-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }

        .highlight-box h2 {
            color: #78350f;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .highlight-box .count {
            font-size: 48px;
            font-weight: 700;
            color: #f59e0b;
            margin: 10px 0;
        }

        .document-item {
            background-color: #f8fafc;
            border-left: 4px solid #10B981;
            padding: 12px 15px;
            margin: 10px 0;
            border-radius: 4px;
            display: flex;
            align-items: center;
        }

        .document-item .doc-icon {
            font-size: 24px;
            margin-right: 12px;
        }

        .document-item .doc-info {
            flex: 1;
        }

        .document-item .doc-name {
            font-weight: 600;
            color: #0F2942;
            margin-bottom: 4px;
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

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }

        .summary-card {
            background-color: #f8fafc;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            border: 2px solid #e2e8f0;
        }

        .summary-card .number {
            font-size: 32px;
            font-weight: 700;
            color: #2563EB;
            margin-bottom: 5px;
        }

        .summary-card .label {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }

        .action-box {
            background-color: #dcfce7;
            border: 2px solid #86efac;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }

        .action-box h3 {
            color: #15803d;
            font-size: 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }

        .action-box h3:before {
            content: "⚡";
            font-size: 20px;
            margin-right: 8px;
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
            content: "✓";
            position: absolute;
            left: 0;
            color: #10B981;
            font-weight: 700;
            font-size: 16px;
        }

        .info-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            color: #78350f;
        }

        .parts-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background-color: #ffffff;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .parts-table thead tr {
            background-color: #0F2942;
            color: white;
        }

        .parts-table th {
            padding: 12px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .parts-table td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .parts-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .parts-table tbody tr:hover {
            background-color: #eff6ff;
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

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .highlight-box .count {
                font-size: 36px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>📨 Supplier Submit Dokumen</h1>
            <p>Notifikasi Real-time | Sistem Approval Dokumen Supplier</p>
            <div class="badge-count">{{ $submittedCount }} Dokumen Baru</div>
        </div>

        <!-- Content -->
        <div class="content">
            <p>Kepada Tim Review & Approval,</p>

            <p style="margin-top: 15px;">Supplier <strong>{{ $supplier->name }}</strong> baru saja mensubmit dokumen untuk request berikut. Silakan segera melakukan review dan approval.</p>

            <!-- Highlight Box -->
            <div class="highlight-box">
                <h2>📋 Jumlah Dokumen yang Di-Submit</h2>
                <div class="count">{{ $submittedCount }}</div>
                <p style="color: #78350f; font-weight: 500;">Dokumen siap untuk di-review</p>
            </div>

            <!-- Detail Box -->
            <div class="detail-box">
                <strong>📌 Informasi Request</strong>
                <p style="margin: 8px 0;"><strong>Model:</strong> {{ $request->model }}</p>
                <p style="margin: 8px 0;"><strong>Supplier:</strong> {{ $supplier->name }}</p>
                <p style="margin: 8px 0;"><strong>Vendor Code:</strong> {{ $supplier->vendor_code }}</p>
                <p style="margin: 8px 0;"><strong>Waktu Submit:</strong> {{ now()->format('d F Y, H:i') }} WIB</p>
            </div>

            <!-- Parts Table -->
            @if($request->requestParts && $request->requestParts->count() > 0)
                <div class="section-title">🔧 Daftar Part</div>
                <table class="parts-table">
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

            <!-- Documents Submitted -->
            <div class="section-title">📄 Dokumen yang Baru Di-Submit ({{ $submittedCount }})</div>
            
            @foreach($submittedDocuments as $doc)
                <div class="document-item">
                    <div class="doc-icon">📎</div>
                    <div class="doc-info">
                        <div class="doc-name">{{ $doc['name'] }}</div>
                        <div class="doc-meta">
                            @if(isset($doc['is_mandatory']) && $doc['is_mandatory'])
                                <span style="color: #dc2626; font-weight: 600;">⚠ Wajib</span>
                            @else
                                <span style="color: #6b7280;">○ Opsional</span>
                            @endif
                            <span style="margin-left: 10px;">{{ $doc['uploaded_at'] }}</span>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Summary Grid -->
            <div class="section-title">📊 Ringkasan Status Dokumen</div>
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="number">{{ $summary['submitted'] }}</div>
                    <div class="label">Total Submitted</div>
                </div>
                <div class="summary-card">
                    <div class="number">{{ $summary['pending'] }}</div>
                    <div class="label">Belum Di-submit</div>
                </div>
                <div class="summary-card">
                    <div class="number">{{ $summary['approved'] }}</div>
                    <div class="label">Sudah Approved</div>
                </div>
                <div class="summary-card">
                    <div class="number">{{ $summary['total'] }}</div>
                    <div class="label">Total Dokumen</div>
                </div>
            </div>

            <!-- Action Required -->
            <div class="action-box">
                <h3>Tindakan yang Diperlukan</h3>
                <p style="margin-bottom: 15px; color: #15803d;">Segera lakukan review terhadap dokumen-dokumen ini sesuai dengan scope kewenangan Anda:</p>
                
                <ul class="action-list">
                    <li>Login ke sistem approval dokumen supplier</li>
                    <li>Buka halaman review untuk request ini</li>
                    <li>Periksa dokumen yang baru di-submit</li>
                    <li>Berikan keputusan: <strong>APPROVE</strong> atau <strong>MINTA REVISI</strong></li>
                    <li>Jika revisi diperlukan, sertakan catatan yang jelas dan spesifik</li>
                </ul>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <strong>⏰ Pengingat:</strong>
                <p style="margin-top: 8px;">
                    Deadline pengiriman dokumen: <strong>{{ $request->due_date->format('d F Y') }}</strong>
                    <br>
                    Proses review yang cepat akan membantu supplier melakukan revisi jika diperlukan.
                </p>
            </div>

            <p style="margin-top: 25px; padding-top: 25px; border-top: 1px solid #e2e8f0;">
                Terima kasih atas perhatian dan kerjasama Anda dalam proses approval dokumen supplier.
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

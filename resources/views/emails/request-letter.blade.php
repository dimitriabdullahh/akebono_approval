<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Dokumen Supplier</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 5px;
        }
        .header {
            background-color: #0F2942;
            color: white;
            padding: 20px;
            text-align: center;
            margin: -30px -30px 30px -30px;
            border-radius: 5px 5px 0 0;
        }
        .info-box {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #0F2942;
        }
        .info-row {
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .parts-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .parts-table th {
            background-color: #0F2942;
            color: white;
            padding: 10px;
            text-align: left;
        }
        .parts-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .button {
            display: inline-block;
            background-color: #2563EB;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Request Dokumen Supplier</h2>
        </div>

        <p>Kepada Yth. <strong>{{ $supplier->pic_name }}</strong>,</p>
        
        <p>Mohon untuk mengunggah dokumen PPAP untuk:</p>

        <div class="info-box">
            <div class="info-row">
                <span class="label">Model:</span> {{ $request->model }}
            </div>
            @if($request->requestParts && $request->requestParts->count() > 0)
                @foreach($request->requestParts as $index => $part)
                <div class="info-row">
                    <span class="label">Part Name:</span> {{ $part->part_name }}
                </div>
                <div class="info-row">
                    <span class="label">Part Number:</span> {{ $part->part_number }}
                </div>
                @endforeach
            @endif
            <div class="info-row">
                <span class="label">Batas Waktu:</span> <strong style="color: #dc3545;">{{ $request->due_date->format('d F Y') }}</strong>
            </div>
        </div>

        @php
            // Get unique document types from this request
            $requiredDocuments = $request->requestDocuments
                ->unique('document_type_id')
                ->sortBy('document_type_id');
            $totalDocs = $requiredDocuments->count();
            $mandatoryDocs = $requiredDocuments->where('documentType.is_mandatory', true)->count();
            $optionalDocs = $totalDocs - $mandatoryDocs;
        @endphp

        <p>Silakan klik link di bawah ini untuk langsung mengakses portal upload dokumen:</p>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ route('supplier.request.access', ['requestId' => $request->id]) }}" style="color: #2563EB; font-size: 16px; font-weight: bold; text-decoration: underline;">
                KLIK LINK UNTUK UPLOAD DOKUMEN
            </a>
        </p>

        <p style="font-size: 14px; color: #666;">
            <strong>Catatan:</strong> Jika Anda sudah login, link akan langsung membuka halaman dokumen. 
            Jika belum login, Anda akan diminta login terlebih dahulu.
        </p>

        <p>Jika ada pertanyaan, silakan hubungi tim QA kami.</p>

        <p style="margin-top: 30px;">
            Terima kasih,<br>
            <strong>Tim Quality Assurance</strong><br>
            PT Akebono Astra Indonesia
        </p>

        <div class="footer">
            <p>Email otomatis - mohon tidak membalas email ini</p>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inspection Standard - {{ $generalInformation['partNo'] ?? 'N/A' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 6mm 6mm 6mm 6mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.2;
            color: #000 !important;
            background: white;
        }
        td, th, p, div, span {
            color: #000 !important;
        }
        .page {
            page-break-after: always;
            border: 1px solid #000;
            padding: 2px;
            margin: 0;
            min-height: 277mm;
            background: white;
        }
        .page:last-child {
            page-break-after: avoid;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            padding: 0;
        }
        table td, table th {
            border: 1.5px solid #000;
            padding: 2px 2px;
            vertical-align: middle;
            font-size: 11px;
            line-height: 1.0;
        }
        .no-border { border: none !important; }
        .bold { font-weight: bold; }
        .center { text-align: center; }
        .left { text-align: left; }

        /* HEADER SECTION - TOP */
        .header-top {
            width: 100%;
            margin-bottom: 2px;
        }
        .header-top td {
            padding: 1px 2px;
            height: 20px;
            border: none;
            line-height: 1.0;
        }
        .header-top th {
            padding: 1px 2px;
            height: 20px;
            border: none;
            line-height: 1.0;
        }
        .logo-cell {
            font-size: 56px;
            font-weight: bold;
            text-align: center;
            width: 12%;
            vertical-align: middle;
        }
        .header-label-cell {
            font-weight: bold;
            width: 12%;
            font-size: 10px;
            text-align: right;
            padding-right: 3px;
        }
        .header-value-cell {
            width: 25%;
            font-size: 11px;
            text-align: left;
            padding-left: 3px;
        }

        /* PART ROUTE TABLE */
        .route-table {
            margin-bottom: 2px;
            width: 100%;
        }
        .route-table th {
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            background-color: #fff;
            height: 14px;
            padding: 1px 2px;
            line-height: 1.0;
        }
        .route-table td {
            text-align: center;
            height: 14px;
            font-size: 10px;
            padding: 1px 2px;
            line-height: 1.0;
        }
        .route-table .col-route  { width: 28%; }
        .route-table .col-sup    { width: 14%; }
        .route-table .col-man    { width: 12%; }
        .route-table .col-mat    { width: 18%; }
        .route-table .col-tol    { width: 14%; }
        .route-table .col-wgt    { width: 14%; }

        /* TITLE BLOCK */
        .title-block {
            text-align: center;
            padding: 14px 0 10px 0;
            margin-bottom: 6px;
        }
        .title-block .brand {
            font-size: 56px;
            font-weight: bold;
            letter-spacing: 2px;
            line-height: 1.0;
            margin: 0;
            padding: 0;
        }
        .title-block .doc-name {
            font-size: 26px;
            font-weight: bold;
            margin: 2px 0;
            line-height: 1.0;
        }
        .title-block .doc-sub {
            font-size: 14px;
            font-style: italic;
            margin: 1px 0 0 0;
            line-height: 1.0;
        }

        /* PART NUMBER TABLE */
        .partno-table {
            margin-bottom: 6px;
            width: 100%;
        }
        .partno-table td {
            border: none;
            padding: 3px 3px;
            font-size: 11px;
            height: 16px;
            line-height: 1.1;
        }
        .partno-table .pn-label {
            font-weight: bold;
            width: 14%;
            white-space: nowrap;
            text-align: left;
        }
        .partno-table .pn-colon {
            width: 3%;
            text-align: center;
        }
        .partno-table .pn-value {
            border-bottom: 1px solid #000;
            font-size: 12px;
            width: 83%;
            text-align: left;
            padding-left: 6px;
        }

        /* APPROVAL TABLE */
        .approval-table {
            margin-bottom: 2px;
            width: 100%;
        }
        .approval-table th {
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            background-color: #fff;
            padding: 1px 2px;
            height: 12px;
            line-height: 1.0;
        }
        .approval-table td {
            text-align: center;
            font-size: 9px;
            height: 40px;
            padding: 2px 2px;
        }
        .approval-table .block-header {
            width: 33.33%;
        }

        /* REVISION TABLE */
        .revision-table {
            margin-bottom: 2px;
            width: 100%;
        }
        .revision-title {
            font-weight: bold;
            font-size: 9px;
            padding: 1px 2px;
            background: #e8e8e8;
            height: 10px;
        }
        .revision-table th {
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            padding: 1px 2px;
            height: 14px;
            line-height: 1.0;
            word-break: break-word;
        }
        .revision-table td {
            height: 12px;
            font-size: 8px;
            padding: 1px 1px;
        }
        .revision-table .col-req   { width: 14%; }
        .revision-table .col-mark  { width: 10%; }
        .revision-table .col-date  { width: 10%; }
        .revision-table .col-record { width: 38%; text-align: left; }
        .revision-table .col-pcr   { width: 10%; }
        .revision-table .col-sup   { width: 18%; }

        /* DISTRIBUTION TABLE */
        .distrib-table {
            margin-bottom: 2px;
            width: 100%;
        }
        .distrib-table td {
            font-size: 9px;
            text-align: center;
            height: 14px;
            padding: 1px 2px;
            line-height: 1.0;
        }
        .distrib-table .distrib-label { 
            font-weight: bold;
            width: 6%;
        }
        .distrib-table .distrib-text {
            text-align: left;
            width: 28%;
        }

        /* DRAFT/FINAL TABLE */
        .draft-final-table {
            width: 100%;
            margin-bottom: 3px;
        }
        .draft-final-table td {
            font-size: 9px;
            height: 12px;
            padding: 1px 2px;
            line-height: 1.0;
        }
        .draft-final-table .checkbox-col {
            width: 18%;
            text-align: center;
        }
        .draft-final-table .label-col {
            width: 16%;
            font-weight: bold;
            text-align: left;
        }
        .draft-final-table .value-col {
            width: 66%;
            text-align: left;
        }

        .checkbox-box {
            width: 18px;
            height: 16px;
            border: 1px solid #000;
            display: inline-block;
            margin-right: 3px;
        }

        /* PAGE 2 - DRAWING */
        .drawing-frame {
            border: 1px solid #000;
            min-height: 480px;
            padding: 6px;
            margin: 2px 0;
            page-break-inside: avoid;
        }
        .drawing-frame .dtitle {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 6px;
        }
        .drawing-item {
            margin-bottom: 8px;
            page-break-inside: avoid;
        }
        .drawing-image {
            max-width: 100%;
            max-height: 300px;
            display: block;
            margin: 4px auto;
        }
        .drawing-info { 
            font-size: 10px; 
            margin-bottom: 2px;
            line-height: 1.1;
        }
        .drawing-label { 
            font-weight: bold; 
            display: inline-block; 
            width: 85px;
            font-size: 10px;
        }

        /* PAGE 3 - INSPECTION TABLE */
        .inspection-table {
            margin-bottom: 2px;
            width: 100%;
        }
        .inspection-table th {
            font-size: 8px;
            font-weight: bold;
            background-color: #f0f0f0;
            text-align: center;
            padding: 1px 1px;
            height: 14px;
            line-height: 1.0;
        }
        .inspection-table td {
            font-size: 8px;
            height: 12px;
            padding: 1px 1px;
            vertical-align: middle;
        }
        .inspection-table .col-no    { width: 5%; text-align: center; display: none; }
        .inspection-table .col-item  { width: 22%; text-align: left; }
        .inspection-table .col-instr { width: 12%; text-align: left; }
        .inspection-table .col-crit  { width: 14%; text-align: left; }
        .inspection-table .col-rank  { width: 6%; text-align: center; }
        .inspection-table .col-samp  { width: 9%; text-align: center; }
        .inspection-table .col-rmk   { width: 33%; text-align: left; }

        /* FOOTER */
        .footer {
            text-align: center;
            font-size: 7px;
            color: #666;
            margin-top: 3px;
            padding-top: 2px;
        }

        /* SPACING UTILITIES */
        .spacer-xs { margin-bottom: 1px; }
        .spacer-sm { margin-bottom: 2px; }
        .spacer-md { margin-bottom: 4px; }
    </style>
</head>
<body>

{{-- PAGE 1 — GENERAL INFORMATION (COVER) --}}
<div class="page">
    {{-- HEADER TOP: AAIJ logo, MODEL, DATE OF ISSUED, REF. NO --}}
    <table class="header-top spacer-xs">
        <tr>
            <td rowspan="2" class="logo-cell">AAIJ</td>
            <td class="header-label-cell">MODEL :</td>
            <td class="header-value-cell">{{ $generalInformation['model'] ?? '' }}</td>
            <td class="header-label-cell">DATE OF ISSUED :</td>
            <td class="header-value-cell">{{ $generalInformation['dateIssued'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="header-label-cell"></td>
            <td class="header-value-cell"></td>
            <td class="header-label-cell">REF. NO :</td>
            <td class="header-value-cell">{{ $generalInformation['refNo'] ?? '' }}</td>
        </tr>
    </table>

    {{-- PART ROUTE TABLE --}}
    <table class="route-table spacer-xs">
        <tr>
            <th class="col-route">PART ROUTE</th>
            <th class="col-sup">SUPPLIER</th>
            <th class="col-man">MANUF.</th>
            <th class="col-mat">MATERIAL</th>
            <th class="col-tol">GENERAL TOL</th>
            <th class="col-wgt">WEIGHT</th>
        </tr>
        <tr>
            <td>{{ $generalInformation['partRoute'] ?? 'AAIJ' }}</td>
            <td>{{ $supplierInfo['supplier'] ?? '' }}</td>
            <td>{{ $generalInformation['manufacturer'] ?? '' }}</td>
            <td>{{ $generalInformation['material'] ?? '' }}</td>
            <td>{{ $generalInformation['generalTolerance'] ?? '' }}</td>
            <td>{{ $generalInformation['weight'] ?? '' }}</td>
        </tr>
    </table>

    {{-- TITLE BLOCK --}}
    <div class="title-block spacer-md">
        <div class="brand">AAIJ</div>
        <div class="doc-name">INSPECTION STANDARD</div>
        <div class="doc-sub">( FOR PURCHASED PART )</div>
    </div>

    {{-- PART NO AND PART NAME --}}
    <table class="partno-table spacer-md">
        <tr>
            <td class="pn-label">PART NO</td>
            <td class="pn-colon">:</td>
            <td class="pn-value">{{ $generalInformation['partNo'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="pn-label">PART NAME</td>
            <td class="pn-colon">:</td>
            <td class="pn-value">{{ $generalInformation['partName'] ?? '' }}</td>
        </tr>
    </table>

    {{-- APPROVAL TABLE --}}
    <table class="approval-table spacer-xs">
        <tr>
            <th colspan="3" class="block-header">Manufacturer :</th>
            <th colspan="3" class="block-header">Supplier :</th>
            <th colspan="3" class="block-header">Customer :</th>
        </tr>
        <tr>
            <th>Approved</th><th>Checked</th><th>Issued</th>
            <th>Approved</th><th>Checked</th><th>Issued</th>
            <th>Approved</th><th>Checked</th><th>Received</th>
        </tr>
        <tr>
            <td></td><td></td><td></td>
            <td></td><td></td><td></td>
            <td></td><td></td><td></td>
        </tr>
    </table>

    {{-- REVISION TABLE --}}
    <table class="revision-table spacer-xs">
        <tr>
            <td colspan="6" class="revision-title">REVISION COLUMN</td>
        </tr>
        <tr>
            <th class="col-req">REVISION REQUEST<br>AAIJ QA</th>
            <th class="col-mark">REVISE<br>MARK</th>
            <th class="col-date">DATE</th>
            <th class="col-record">REVISION RECORD</th>
            <th class="col-pcr">PCR/ECR<br>NO.</th>
            <th class="col-sup">REVISION REQUEST<br>SUPPLIER</th>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td>{{ $generalInformation['revisionDate'] ?? '' }}</td>
            <td class="left">{{ $generalInformation['revisionRecord'] ?? '' }}</td>
            <td></td>
            <td></td>
        </tr>
        @for($i = 0; $i < 8; $i++)
        <tr>
            <td></td><td></td><td></td><td></td><td></td><td></td>
        </tr>
        @endfor
    </table>

    {{-- DISTRIBUTION TABLE --}}
    <table class="distrib-table spacer-xs">
        <tr>
            <td class="distrib-label">Div.<br>Mgr.</td>
            <td class="distrib-label">Mgr.</td>
            <td class="distrib-label">Spv.</td>
            <td class="distrib-text">DISTRIBUTE TO :          DIVISION :</td>
            <td class="distrib-label">Gen.<br>Mgr.</td>
            <td class="distrib-label">Mgr.</td>
            <td class="distrib-label">Staff</td>
        </tr>
    </table>

    {{-- DRAFT / FINAL CHECKBOX --}}
    <table class="draft-final-table spacer-md">
        <tr>
            <td rowspan="2" class="checkbox-col"><span class="checkbox-box"></span>DRAFT</td>
            <td class="label-col">Customer</td>
            <td class="value-col">{{ $supplierInfo['customer'] ?? 'AAIJ (PURCHASING & QUALITY DIVISION)' }}</td>
        </tr>
        <tr>
            <td class="label-col">Supplier</td>
            <td class="value-col">{{ $supplierInfo['supplier'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="checkbox-col"><span class="checkbox-box"></span>FINAL</td>
            <td class="label-col">Manufacturer</td>
            <td class="value-col">{{ $supplierInfo['manufacturer'] ?? '' }}</td>
        </tr>
    </table>

    <div class="footer">Page 1 of 3 | Generated: {{ $generatedAt }}</div>
</div>

{{-- PAGE 2 — PART DRAWING --}}
<div class="page">
    {{-- PAGE 2 HEADER --}}
    <table class="spacer-xs" style="width:100%; border-collapse: collapse;">
        <tr>
            <td rowspan="2" class="logo-cell" style="width: 12%;">AAIJ</td>
            <td rowspan="2" style="width: 38%; font-weight: bold; font-size: 13px; text-align: center; vertical-align: middle; border: 1px solid #000; padding: 4px;">INSPECTION STANDARD</td>
            <td class="header-label-cell" style="width: 12%;">MODEL :</td>
            <td class="header-value-cell" style="width: 38%;">{{ $generalInformation['model'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="header-label-cell" style="width: 12%;">PART NO :</td>
            <td class="header-value-cell" style="width: 38%;">{{ $generalInformation['partNo'] ?? '' }}</td>
        </tr>
    </table>

    {{-- DRAWING FRAME --}}
    <div class="drawing-frame">
        <div class="dtitle">PART DRAWING :</div>
        @if(count($drawings) > 0)
            @foreach($drawings as $index => $drawing)
            <div class="drawing-item">
                <div class="drawing-info bold">Drawing {{ $index + 1 }}{{ $drawing['drawingName'] ? ': '.$drawing['drawingName'] : '' }}</div>
                @if($drawing['filePath'] && file_exists($drawing['filePath']) && in_array($drawing['fileType'], ['jpg','jpeg','png']))
                    <img src="{{ $drawing['filePath'] }}" class="drawing-image" alt="Drawing {{ $index + 1 }}">
                @elseif($drawing['fileType'] === 'pdf')
                    <div style="text-align: center; padding: 12px; background: #f5f5f5; border: 1px dashed #999; margin: 4px 0; font-size: 10px;">
                        [PDF Document: {{ $drawing['originalFilename'] }}]
                    </div>
                @endif
                @if($drawing['drawingNo'])
                    <div class="drawing-info"><span class="drawing-label">Drawing No:</span>{{ $drawing['drawingNo'] }}</div>
                @endif
                @if($drawing['revision'])
                    <div class="drawing-info"><span class="drawing-label">Revision:</span>{{ $drawing['revision'] }}</div>
                @endif
                @if($drawing['description'])
                    <div class="drawing-info"><span class="drawing-label">Description:</span>{{ $drawing['description'] }}</div>
                @endif
            </div>
            @endforeach
        @else
            <p style="text-align: center; color: #000; padding: 40px 0; font-size: 11px;">No drawings uploaded</p>
        @endif
    </div>

    @include('inspection-standard.pdf.partials.approval-footer', ['qaLabel' => 'AAIJ - QA'])

    <div class="footer">Page 2 of 3 | Generated: {{ $generatedAt }}</div>
</div>

{{-- PAGE 3 — INSPECTION ITEMS --}}
<div class="page">
    {{-- PAGE 3 HEADER --}}
    <table class="spacer-xs" style="width:100%; border-collapse: collapse;">
        <tr>
            <td rowspan="2" class="logo-cell" style="width: 12%;">AAIJ</td>
            <td rowspan="2" style="width: 38%; font-weight: bold; font-size: 13px; text-align: center; vertical-align: middle; border: 1px solid #000; padding: 4px;">INSPECTION STANDARD</td>
            <td class="header-label-cell" style="width: 12%;">MODEL :</td>
            <td class="header-value-cell" style="width: 38%;">{{ $generalInformation['model'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="header-label-cell" style="width: 12%;">PART NO :</td>
            <td class="header-value-cell" style="width: 38%;">{{ $generalInformation['partNo'] ?? '' }}</td>
        </tr>
    </table>

    {{-- INSPECTION ITEMS TABLE --}}
    <table class="inspection-table spacer-xs">
        <thead>
            <tr>
                <th rowspan="2" class="col-no">No</th>
                <th rowspan="2" class="col-item">INSPECTION ITEM</th>
                <th rowspan="2" class="col-instr">INSPECTION<br>INSTRUMENT</th>
                <th rowspan="2" class="col-crit">INSPECTION<br>CRITERION</th>
                <th rowspan="2" class="col-rank">RANK</th>
                <th colspan="2" style="width: 18%;">SAMPLING</th>
                <th rowspan="2" class="col-rmk">REMARK<br>(INSPECTION METHOD)</th>
            </tr>
            <tr>
                <th class="col-samp">SUPPLIER</th>
                <th class="col-samp">AAIJ</th>
            </tr>
        </thead>
        <tbody>
            @php $rowCount = max(count($inspectionItems), 12); @endphp
            @for($i = 0; $i < $rowCount; $i++)
                @php $item = $inspectionItems[$i] ?? null; @endphp
                <tr>
                    <td class="col-no">{{ $i + 1 }}</td>
                    <td class="col-item">{{ $item['inspectionItem'] ?? '' }}</td>
                    <td class="col-instr">{{ $item['inspectionInstrument'] ?? '' }}</td>
                    <td class="col-crit">{{ $item['inspectionCriterion'] ?? '' }}</td>
                    <td class="col-rank">{{ $item['rank'] ?? '' }}</td>
                    <td class="col-samp">{{ $item['supplierSampling'] ?? '' }}</td>
                    <td class="col-samp">{{ $item['aaijSampling'] ?? '' }}</td>
                    <td class="col-rmk">{{ $item['remarkInspectionMethod'] ?? '' }}</td>
                </tr>
            @endfor
        </tbody>
    </table>

    @include('inspection-standard.pdf.partials.approval-footer', ['qaLabel' => 'AAIJ QA'])

    <div class="footer">Page 3 of 3 | Generated: {{ $generatedAt }}</div>
</div>

</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inspection Standard - {{ $generalInformation['partNo'] ?? 'N/A' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #000;
            line-height: 1.2;
        }
        
        .page {
            position: relative;
            width: 210mm;
            height: 297mm;
            page-break-after: always;
            overflow: hidden;
        }
        
        .page:last-child {
            page-break-after: avoid;
        }
        
        .page-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm;
            height: 297mm;
            z-index: 0;
        }
        
        .page-background img {
            width: 100%;
            height: 100%;
            display: block;
        }
        
        .page-content {
            position: relative;
            z-index: 1;
            width: 210mm;
            height: 297mm;
        }
        
        .field {
            position: absolute;
            font-size: 8px;
            color: #000;
            white-space: nowrap;
        }
        
        .field-small {
            font-size: 7px;
        }
        
        .field-bold {
            font-weight: bold;
        }
        
        /* Page 3 - HTML Table for dynamic inspection items */
        .page-3-content {
            padding: 8mm 10mm;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        
        td, th {
            border: 1px solid #000;
            padding: 2px 4px;
            vertical-align: middle;
            font-size: 7px;
        }
        
        .bold { font-weight: bold; }
        .center { text-align: center; }
        .small { font-size: 6px; }
        
        .inspection-row {
            height: 55px;
        }
        
        .signature-box {
            height: 50px;
            vertical-align: bottom;
        }
    </style>
</head>
<body>

<!-- PAGE 1: General Information with Background Image -->
<div class="page">
    <div class="page-background">
        <img src="{{ public_path('images/pdf-templates/xls_page1.png') }}" alt="Page 1 Background">
    </div>
    <div class="page-content">
        <!-- Coordinates MAJOR FIX (Iteration #5) - using grid reference precisely -->
        <!-- Grid analysis: Y-axis every 100px (blue), X-axis every 100px (red) -->
        
        <!-- MODEL - PERFECT, NOT CHANGED -->
        <div class="field" style="top: 9mm; left: 68mm; font-size: 8px;">{{ $generalInformation['model'] }}</div>
        
        <!-- DATE OF ISSUED - PERFECT, NOT CHANGED -->
        <div class="field" style="top: 9mm; left: 145mm; font-size: 7px;">{{ $generalInformation['dateIssued'] }}</div>
        
        <!-- REF. NO - PERFECT, NOT CHANGED -->
        <div class="field" style="top: 16mm; left: 145mm; font-size: 7px;">{{ $generalInformation['refNo'] }}</div>
        
        <!-- PART ROUTE - MAJOR FIX: Move WAY DOWN to the empty row below header -->
        <div class="field" style="top: 25mm; left: 18mm; font-size: 7px; width: 175mm;">{{ $generalInformation['partRoute'] }}</div>
        
        <!-- Supplier/Manuf/Material Row - these are CORRECT at row below PART ROUTE -->
        <div class="field field-small" style="top: 32mm; left: 58mm;">{{ $supplierInfo['supplier'] }}</div>
        <div class="field field-small" style="top: 32mm; left: 95mm;">{{ $generalInformation['manufacturer'] }}</div>
        <div class="field field-small" style="top: 32mm; left: 125mm;">{{ $generalInformation['material'] }}</div>
        <div class="field field-small" style="top: 32mm; left: 155mm;">{{ $generalInformation['generalTolerance'] }}</div>
        <div class="field field-small" style="top: 32mm; left: 180mm;">{{ $generalInformation['weight'] }}</div>
        
        <!-- PART NO - MAJOR FIX: Based on grid, underline is around Y=600px (101mm), field should be ~95-98mm -->
        <div class="field" style="top: 97mm; left: 74mm; font-size: 8px;">{{ $generalInformation['partNo'] }}</div>
        
        <!-- PART NAME - MAJOR FIX: Underline around Y=625px (105mm), field should be ~100-103mm -->
        <div class="field" style="top: 103mm; left: 74mm; font-size: 8px;">{{ $generalInformation['partName'] }}</div>
        
        <!-- MANUFACTURER - Based on grid Y=800px (135mm), actual label row is around 138-140mm -->
        <div class="field field-small" style="top: 139mm; left: 43mm;">{{ $supplierInfo['manufacturer'] }}</div>
        
        <!-- SUPPLIER - Same row as Manufacturer -->
        <div class="field field-small" style="top: 139mm; left: 106mm;">{{ $supplierInfo['supplier'] }}</div>
        
        <!-- Revision Column - First data row in REVISION COLUMN table (Y~1000px = 169mm) -->
        @if($generalInformation['revisionDate'])
        <div class="field field-small" style="top: 169mm; left: 48mm;">{{ $generalInformation['revisionDate'] }}</div>
        @endif
        @if($generalInformation['revisionRecord'])
        <div class="field field-small" style="top: 169mm; left: 80mm; width: 95mm;">{{ $generalInformation['revisionRecord'] }}</div>
        @endif
    </div>
</div>

<!-- PAGE 2: Part Drawing with Background Image -->
<div class="page">
    <div class="page-background">
        <img src="{{ public_path('images/pdf-templates/xls_page2.png') }}" alt="Page 2 Background">
    </div>
    <div class="page-content">
        <!-- Coordinates RE-CALIBRATED based on actual PDF output -->
        
        <!-- Header Row: MODEL (after "MODEL :") -->
        <div class="field" style="top: 9mm; left: 56mm; font-size: 8px;">{{ $generalInformation['model'] }}</div>
        
        <!-- Header Row: PART NO (after "PART NO :") -->
        <div class="field" style="top: 9mm; left: 134mm; font-size: 8px;">{{ $generalInformation['partNo'] }}</div>
        
        <!-- Drawing Area - position drawings in the blank PART DRAWING space -->
        <div style="position: absolute; top: 48mm; left: 16mm; width: 178mm; height: 215mm; z-index: 1; overflow: hidden;">
            @if(count($drawings) > 0)
                @foreach($drawings as $drawing)
                <div style="margin-bottom: 6px; padding: 5px; border: 1px solid #999; background: rgba(255,255,255,0.95); font-size: 6px;">
                    <div><strong>Drawing {{ $loop->iteration }}:</strong> {{ $drawing['drawingName'] }}</div>
                    <div><strong>Drawing No:</strong> {{ $drawing['drawingNo'] }} | <strong>Rev:</strong> {{ $drawing['revision'] }}</div>
                    <div>{{ $drawing['description'] }}</div>
                    @if($drawing['filePath'] && file_exists($drawing['filePath']))
                        @if(in_array($drawing['fileType'], ['jpg', 'jpeg', 'png']))
                        <div style="text-align: center; margin-top: 4px;">
                            <img src="{{ $drawing['filePath'] }}" style="max-width: 100%; max-height: 120px; border: 1px solid #ddd;">
                        </div>
                        @else
                        <div style="text-align: center; padding: 10px; background: #f5f5f5; border: 1px dashed #ccc; margin-top: 4px;">
                            <div style="font-size: 18px;">📄</div>
                            <div style="font-size: 5px;">{{ $drawing['originalFilename'] }}</div>
                        </div>
                        @endif
                    @endif
                </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

<!-- PAGE 3: Inspection Items - HTML Table (Dynamic Content) -->
<div class="page">
    <div class="page-3-content">
        <!-- Header -->
        <table style="margin-bottom: 4px;">
            <tr>
                <td class="bold center" style="width: 12%; font-size: 16px;">AAIJ</td>
                <td class="bold small" style="width: 15%;">MODEL :</td>
                <td style="width: 43%;">{{ $generalInformation['model'] }}</td>
                <td class="bold small" style="width: 15%;">PART NO :</td>
                <td style="width: 15%;">{{ $generalInformation['partNo'] }}</td>
            </tr>
            <tr>
                <td></td>
                <td colspan="4" class="bold center" style="font-size: 11px; padding: 6px;">INSPECTION STANDARD</td>
            </tr>
        </table>

        <!-- Inspection Items Table (NO "No" COLUMN, 8 LARGE ROWS) -->
        <table style="margin-bottom: 4px;">
            <thead>
                <tr class="center bold small">
                    <th rowspan="2" style="width: 20%;">INSPECTION ITEM</th>
                    <th rowspan="2" style="width: 14%;">INSPECTION<br>INSTRUMENT</th>
                    <th rowspan="2" style="width: 18%;">INSPECTION<br>CRITERION</th>
                    <th rowspan="2" style="width: 7%;">RANK</th>
                    <th colspan="2" style="width: 20%;">SAMPLING</th>
                    <th rowspan="2" style="width: 21%;">REMARK<br>( INSPECTION METHOD )</th>
                </tr>
                <tr class="center bold small">
                    <th style="width: 10%;">SUPPLIER</th>
                    <th style="width: 10%;">AAIJ</th>
                </tr>
            </thead>
            <tbody>
                @if(count($inspectionItems) > 0)
                    @foreach($inspectionItems as $item)
                        @if($loop->iteration <= 8)
                        <tr class="inspection-row">
                            <td class="small">{{ $item['inspectionItem'] }}</td>
                            <td class="small">{{ $item['inspectionInstrument'] }}</td>
                            <td class="small">{{ $item['inspectionCriterion'] }}</td>
                            <td class="small center">{{ $item['rank'] }}</td>
                            <td class="small center">{{ $item['supplierSampling'] }}</td>
                            <td class="small center">{{ $item['aaijSampling'] }}</td>
                            <td class="small">{{ $item['remarkInspectionMethod'] }}</td>
                        </tr>
                        @endif
                    @endforeach
                    @for($i = count($inspectionItems); $i < 8; $i++)
                    <tr class="inspection-row">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @endfor
                @else
                    @for($i = 0; $i < 8; $i++)
                    <tr class="inspection-row">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @endfor
                @endif
            </tbody>
        </table>

        <!-- Approval Section -->
        <table>
            <tr class="center small">
                <th colspan="2" style="background-color: #d0d0d0;">MANUFACTURER</th>
                <th colspan="2" style="background-color: #d0d0d0;">SUPPLIER</th>
                <th colspan="3" style="background-color: #d0d0d0;">DISTRIBUTE TO : DIVISION</th>
                <th colspan="3" style="background-color: #d0d0d0;">AAIJ QA</th>
            </tr>
            <tr class="center small">
                <td style="width: 10%;">Approved</td>
                <td style="width: 10%;">Issued</td>
                <td style="width: 10%;">Approved</td>
                <td style="width: 10%;">Issued</td>
                <td style="width: 12%;">Customer</td>
                <td colspan="2" style="width: 28%;">: AAIJ (PUR & QUALITY DIV)</td>
                <td style="width: 10%;">Approved</td>
                <td style="width: 10%;">Checked</td>
                <td style="width: 10%;">Received</td>
            </tr>
            <tr class="signature-box">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>Supplier</td>
                <td colspan="2">:</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr class="small">
                <td colspan="4"></td>
                <td>Manufacturer</td>
                <td colspan="2">:</td>
                <td colspan="3"></td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>

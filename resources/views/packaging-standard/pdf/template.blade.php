<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Packaging Standard - {{ $formData['generalInformation']['partNo'] ?? $ps->part_no ?? 'N/A' }}</title>
    <style>
        @page {
            size: 792pt 612pt;
            margin: 0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
            font-family: 'Bookman Old Style', 'Times New Roman', Times, serif;
            font-size: 8.6pt;
            color: #000;
            line-height: 1.0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
        }
        td, th {
            border: 0.96pt solid #000;
            padding: 0 4pt;
            vertical-align: middle;
            font-size: 8.6pt;
            color: #000;
            font-family: 'Bookman Old Style', 'Times New Roman', Times, serif;
        }
        .outer-wrapper {
            border: 1.92pt solid #000;
            width: 782pt;
            margin: 38pt 3pt 0 3pt;
            padding: 0;
        }
        
        .b-none { border: none !important; }
        .b-t-none { border-top: none !important; }
        .b-b-none { border-bottom: none !important; }
        .b-l-none { border-left: none !important; }
        .b-r-none { border-right: none !important; }

        .bold { font-weight: bold; }
        .center { text-align: center; }
        .left { text-align: left; }
        .right { text-align: right; }

        .header-title {
            font-family: 'Bookman Old Style', 'Times New Roman', Times, serif;
            font-size: 19.6pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-align: center;
        }
        .section-header {
            text-align: center;
            font-size: 8.6pt;
            padding: 0;
            background-color: #fff;
            height: 22.8pt;
        }
        .info-label {
            font-size: 8.6pt;
        }
        .colon {
            font-size: 9.6pt;
            padding-right: 2pt;
        }
    </style>
</head>
<body>
@php
    $getImageBase64 = function($imagePath) {
        if (!$imagePath) return null;
        $fullPath = null;
        if (file_exists(storage_path('app/private/' . $imagePath))) {
            $fullPath = storage_path('app/private/' . $imagePath);
        } elseif (file_exists(storage_path('app/' . $imagePath))) {
            $fullPath = storage_path('app/' . $imagePath);
        } elseif (file_exists(public_path('storage/' . $imagePath))) {
            $fullPath = public_path('storage/' . $imagePath);
        } elseif (file_exists($imagePath)) {
            $fullPath = $imagePath;
        }
        if ($fullPath && file_exists($fullPath)) {
            $type = pathinfo($fullPath, PATHINFO_EXTENSION);
            $data = file_get_contents($fullPath);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        return null;
    };

    // Generate callout bubble exact matching reference shape
    $getCalloutBubble = function($line1, $line2 = null) {
        $scale = 2;
        $w = (int)(110 * $scale);
        $h = (int)(105 * $scale);
        $im = imagecreatetruecolor($w, $h);
        imagesavealpha($im, true);
        $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $transparent);

        // Word/Excel shape blue
        $blueFill = imagecolorallocate($im, 79, 129, 189);
        $blueBorder = imagecolorallocate($im, 56, 93, 138);
        $white = imagecolorallocate($im, 255, 255, 255);

        $bx0 = (int)(12 * $scale);
        $by0 = (int)(2 * $scale);
        $bx1 = (int)(106 * $scale);
        $by1 = (int)(68 * $scale);
        $rad = (int)(11 * $scale);

        // Fill body rounded rect
        imagefilledrectangle($im, $bx0 + $rad, $by0, $bx1 - $rad, $by1, $blueFill);
        imagefilledrectangle($im, $bx0, $by0 + $rad, $bx1, $by1 - $rad, $blueFill);
        imagefilledellipse($im, $bx0 + $rad, $by0 + $rad, $rad * 2, $rad * 2, $blueFill);
        imagefilledellipse($im, $bx1 - $rad, $by0 + $rad, $rad * 2, $rad * 2, $blueFill);
        imagefilledellipse($im, $bx0 + $rad, $by1 - $rad, $rad * 2, $rad * 2, $blueFill);
        imagefilledellipse($im, $bx1 - $rad, $by1 - $rad, $rad * 2, $rad * 2, $blueFill);

        // Fill tail
        $tailPoints = [
            (int)(53 * $scale), (int)(67 * $scale),
            (int)(28 * $scale), (int)(67 * $scale),
            0, (int)(104 * $scale)
        ];
        imagefilledpolygon($im, $tailPoints, $blueFill);

        // Outline tail
        imagesetthickness($im, (int)(2 * $scale));
        imageline($im, (int)(53 * $scale), (int)(67 * $scale), 0, (int)(104 * $scale), $blueBorder);
        imageline($im, 0, (int)(104 * $scale), (int)(28 * $scale), (int)(67 * $scale), $blueBorder);

        // Outline body
        imageline($im, $bx0 + $rad, $by0, $bx1 - $rad, $by0, $blueBorder);
        imageline($im, $bx1, $by0 + $rad, $bx1, $by1 - $rad, $blueBorder);
        imageline($im, $bx0 + $rad, $by1, (int)(28 * $scale), $by1, $blueBorder);
        imageline($im, (int)(53 * $scale), $by1, $bx1 - $rad, $by1, $blueBorder);
        imageline($im, $bx0, $by0 + $rad, $bx0, $by1 - $rad, $blueBorder);
        imagearc($im, $bx0 + $rad, $by0 + $rad, $rad * 2, $rad * 2, 180, 270, $blueBorder);
        imagearc($im, $bx1 - $rad, $by0 + $rad, $rad * 2, $rad * 2, 270, 360, $blueBorder);
        imagearc($im, $bx1 - $rad, $by1 - $rad, $rad * 2, $rad * 2, 0, 90, $blueBorder);
        imagearc($im, $bx0 + $rad, $by1 - $rad, $rad * 2, $rad * 2, 90, 180, $blueBorder);

        // Text rendering
        $font = 4;
        if ($line2) {
            $tw1 = imagefontwidth($font) * strlen($line1);
            $tw2 = imagefontwidth($font) * strlen($line2);
            $cx = ($bx0 + $bx1) / 2;
            imagestring($im, $font, (int)($cx - $tw1 / 2), (int)(18 * $scale), $line1, $white);
            imagestring($im, $font, (int)($cx - $tw2 / 2), (int)(38 * $scale), $line2, $white);
        } else {
            $tw = imagefontwidth($font) * strlen($line1);
            $cx = ($bx0 + $bx1) / 2;
            imagestring($im, $font, (int)($cx - $tw / 2), (int)(28 * $scale), $line1, $white);
        }

        ob_start();
        imagepng($im);
        $data = ob_get_clean();
        imagedestroy($im);

        return 'data:image/png;base64,' . base64_encode($data);
    };

    $photoImg = $getImageBase64($ps->image_photographic ?? $ps->image_visualisasi_packing);
    $labelImg = $getImageBase64($ps->image_part_identification ?? $ps->image_label_packing);

    $defaultPhotoBubble = $getCalloutBubble("Visualisasi", "packing");
    $defaultLabelBubble = $getCalloutBubble("Label packing");

    $docNo = $formData['generalInformation']['documentNo'] ?? $ps->document_no ?? '';
    $revision = $formData['generalInformation']['revision'] ?? $ps->revision ?? '';
    $dateVal = $ps->date ? $ps->date->format('Y-m-d') : ($formData['generalInformation']['date'] ?? '');
    $customerVal = $formData['generalInformation']['customer'] ?? $ps->customer ?? '';
    $partNoVal = $formData['generalInformation']['partNo'] ?? $ps->part_no ?? '';
    $partNameVal = $formData['generalInformation']['partName'] ?? $ps->part_name ?? '';
    $pkgTypeVal = $formData['specifications']['packagingType'] ?? $ps->packaging_type ?? '';
    $pkgSizeVal = $formData['specifications']['packagingSizeMm'] ?? $ps->packaging_size_mm ?? '';
    $pkgLotVal = $formData['specifications']['packagingLotPcsBox'] ?? $ps->packaging_lot_pcs_box ?? '';
    $netWeightVal = $formData['specifications']['netWeightGPc'] ?? $ps->net_weight_g_pc ?? '';
    $totWeightVal = $formData['specifications']['totalWeightKgBox'] ?? $ps->total_weight_kg_box ?? '';

    $materials = $formData['specifications']['materials'] ?? $formData['materials'] ?? ($ps->form_data_json['materials'] ?? []);
    if (!is_array($materials)) $materials = [];

    $revisions = $formData['visualizationApproval']['revisionHistory'] ?? $ps->revision_history ?? ($ps->form_data_json['revisionHistory'] ?? []);
    if (!is_array($revisions)) {
        $revisions = json_decode($revisions, true) ?? [];
    }
@endphp

<div class="outer-wrapper">
    <!-- TOP HEADER (UNIFIED SINGLE TABLE) -->
    <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
        <colgroup>
            <col style="width: 25.68%;">
            <col style="width: 51.72%;">
            <col style="width: 7.533%;">
            <col style="width: 7.533%;">
            <col style="width: 7.534%;">
        </colgroup>
        <!-- ROW 1: Logo / Title / Approval Headers -->
        <tr>
            <!-- Col 1: Logo -->
            <td class="center" style="width: 25.68%; height: 24pt; font-family: Arial, sans-serif; font-size: 9.8pt; border: none; border-right: 0.96pt solid #000; border-bottom: 0.96pt solid #000; vertical-align: middle;">
                YOUR COMPANY LOGO
            </td>
            <!-- Col 2: Title (rowspan 5) -->
            <td rowspan="5" class="center" style="width: 51.72%; font-family: Arial, sans-serif; font-size: 14pt; font-weight: bold; border: none; border-right: 0.96pt solid #000; border-bottom: 0.96pt solid #000; vertical-align: middle;">
                <div class="header-title" style="margin: 0; padding: 0;">PACKAGING STANDARD</div>
            </td>
            <!-- Cols 3-5: Approval Headers -->
            <td class="center" style="width: 7.533%; height: 12pt; padding: 0; font-size: 8.6pt; border: none; border-right: 0.96pt solid #000; border-bottom: 0.96pt solid #000;">Approved</td>
            <td class="center" style="width: 7.533%; height: 12pt; padding: 0; font-size: 8.6pt; border: none; border-right: 0.96pt solid #000; border-bottom: 0.96pt solid #000;">Checked</td>
            <td class="center" style="width: 7.534%; height: 12pt; padding: 0; font-size: 8.6pt; border: none; border-bottom: 0.96pt solid #000;">Prepared</td>
        </tr>

        <!-- ROW 2: Doc No / Signatures (rowspan 3) -->
        <tr>
            <!-- Col 1: Doc No -->
            <td style="height: 11.5pt; padding: 0 4pt; border: none; border-right: 0.96pt solid #000; border-bottom: 0.96pt solid #000; vertical-align: middle;">
                <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                    <colgroup>
                        <col style="width: 58%;">
                        <col style="width: 42%;">
                    </colgroup>
                    <tr>
                        <td class="b-none info-label" style="padding: 0;">Document No.</td>
                        <td class="b-none" style="padding: 0;"><span class="colon">:</span>{{ $docNo }}</td>
                    </tr>
                </table>
            </td>
            <!-- Cols 3-5: Signatures (rowspan 3) -->
            <td rowspan="3" style="vertical-align: bottom; text-align: center; font-size: 8pt; padding: 0 2pt 2pt 2pt; border: none; border-right: 0.96pt solid #000; border-bottom: 0.96pt solid #000;">
                {{ $formData['visualizationApproval']['approvedByName'] ?? $ps->approved_by_name ?? '' }}
            </td>
            <td rowspan="3" style="vertical-align: bottom; text-align: center; font-size: 8pt; padding: 0 2pt 2pt 2pt; border: none; border-right: 0.96pt solid #000; border-bottom: 0.96pt solid #000;">
                {{ $formData['visualizationApproval']['checkedByName'] ?? $ps->checked_by_name ?? '' }}
            </td>
            <td rowspan="3" style="vertical-align: bottom; text-align: center; font-size: 8pt; padding: 0 2pt 2pt 2pt; border: none; border-bottom: 0.96pt solid #000;">
                {{ $formData['visualizationApproval']['preparedByName'] ?? $ps->prepared_by_name ?? '' }}
            </td>
        </tr>

        <!-- ROW 3: Revision -->
        <tr>
            <td style="height: 11.5pt; padding: 0 4pt; border: none; border-right: 0.96pt solid #000; border-bottom: 0.96pt solid #000; vertical-align: middle;">
                <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                    <colgroup>
                        <col style="width: 58%;">
                        <col style="width: 42%;">
                    </colgroup>
                    <tr>
                        <td class="b-none info-label" style="padding: 0;">Revision</td>
                        <td class="b-none" style="padding: 0;"><span class="colon">:</span>{{ $revision }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- ROW 4: Date -->
        <tr>
            <td style="height: 11.5pt; padding: 0 4pt; border: none; border-right: 0.96pt solid #000; border-bottom: 0.96pt solid #000; vertical-align: middle;">
                <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                    <colgroup>
                        <col style="width: 58%;">
                        <col style="width: 42%;">
                    </colgroup>
                    <tr>
                        <td class="b-none info-label" style="padding: 0;">Date</td>
                        <td class="b-none" style="padding: 0;"><span class="colon">:</span>{{ $dateVal }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- ROW 5: Customer / Approval Dates -->
        <tr>
            <!-- Col 1: Customer -->
            <td style="height: 11.5pt; padding: 0 4pt; border: none; border-right: 0.96pt solid #000; border-bottom: 0.96pt solid #000; vertical-align: middle;">
                <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                    <colgroup>
                        <col style="width: 58%;">
                        <col style="width: 42%;">
                    </colgroup>
                    <tr>
                        <td class="b-none info-label" style="padding: 0;">Customer</td>
                        <td class="b-none" style="padding: 0;"><span class="colon">:</span>{{ $customerVal }}</td>
                    </tr>
                </table>
            </td>
            <!-- Cols 3-5: Approval Dates -->
            <td style="height: 12pt; text-align: center; font-size: 7pt; padding: 0; border: none; border-right: 0.96pt solid #000; border-bottom: 0.96pt solid #000; vertical-align: middle;">
                {{ $ps->approved_by_date?->format('d/m/y') ?? $formData['visualizationApproval']['approvedByDate'] ?? '' }}
            </td>
            <td style="height: 12pt; text-align: center; font-size: 7pt; padding: 0; border: none; border-right: 0.96pt solid #000; border-bottom: 0.96pt solid #000; vertical-align: middle;">
                {{ $ps->checked_by_date?->format('d/m/y') ?? $formData['visualizationApproval']['checkedByDate'] ?? '' }}
            </td>
            <td style="height: 12pt; text-align: center; font-size: 7pt; padding: 0; border: none; border-bottom: 0.96pt solid #000; vertical-align: middle;">
                {{ $ps->prepared_by_date?->format('d/m/y') ?? $formData['visualizationApproval']['preparedByDate'] ?? '' }}
            </td>
        </tr>
    </table>

    <!-- MAIN BODY (2 COLUMNS) -->
    <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
        <colgroup>
            <col style="width: 46.0%;">
            <col style="width: 54.0%;">
        </colgroup>
        <tr>
            <!-- LEFT COLUMN (46.0%) - SINGLE CONTINUOUS TABLE -->
            <td style="width: 46.0%; padding: 0; vertical-align: top; border: none; border-right: 0.96pt solid #000;">
                <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                    <!-- SPECS ROWS 1 to 5 -->
                    <tr>
                        <td style="height: 22.8pt; padding: 0 4pt; border: none; border-bottom: 0.96pt solid #000;">
                            <table style="width: 100%; table-layout: fixed;">
                                <colgroup>
                                    <col style="width: 32%;">
                                    <col style="width: 68%;">
                                </colgroup>
                                <tr>
                                    <td class="b-none info-label" style="padding: 0;">Part No.</td>
                                    <td class="b-none" style="padding: 0;"><span class="colon">:</span>{{ $partNoVal }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 22.8pt; padding: 0 4pt; border: none; border-bottom: 0.96pt solid #000;">
                            <table style="width: 100%; table-layout: fixed;">
                                <colgroup>
                                    <col style="width: 32%;">
                                    <col style="width: 68%;">
                                </colgroup>
                                <tr>
                                    <td class="b-none info-label" style="padding: 0;">Parts Name</td>
                                    <td class="b-none" style="padding: 0;"><span class="colon">:</span>{{ $partNameVal }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 22.8pt; padding: 0; border: none; border-bottom: 0.96pt solid #000;">
                            <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                                <colgroup>
                                    <col style="width: 55.82%;">
                                    <col style="width: 44.18%;">
                                </colgroup>
                                <tr>
                                    <td style="padding: 0 4pt; border: none; border-right: 0.96pt solid #000; height: 22.8pt;">
                                        <table style="width: 100%; table-layout: fixed;">
                                            <colgroup>
                                                <col style="width: 58%;">
                                                <col style="width: 42%;">
                                            </colgroup>
                                            <tr>
                                                <td class="b-none info-label" style="padding: 0;">Packaging Type</td>
                                                <td class="b-none" style="padding: 0;"><span class="colon">:</span>{{ $pkgTypeVal }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="padding: 0 4pt; border: none; height: 22.8pt;">
                                        <table style="width: 100%; table-layout: fixed;">
                                            <colgroup>
                                                <col style="width: 65%;">
                                                <col style="width: 35%;">
                                            </colgroup>
                                            <tr>
                                                <td class="b-none info-label" style="padding: 0;">Net Weight (g/pc)</td>
                                                <td class="b-none" style="padding: 0;"><span class="colon">:</span>{{ is_numeric($netWeightVal) ? number_format($netWeightVal, 2) : $netWeightVal }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 22.8pt; padding: 0; border: none; border-bottom: 0.96pt solid #000;">
                            <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                                <colgroup>
                                    <col style="width: 55.82%;">
                                    <col style="width: 44.18%;">
                                </colgroup>
                                <tr>
                                    <td style="padding: 0 4pt; border: none; border-right: 0.96pt solid #000; height: 22.8pt;">
                                        <table style="width: 100%; table-layout: fixed;">
                                            <colgroup>
                                                <col style="width: 58%;">
                                                <col style="width: 42%;">
                                            </colgroup>
                                            <tr>
                                                <td class="b-none info-label" style="padding: 0;">Packaging Size (mm)</td>
                                                <td class="b-none" style="padding: 0;"><span class="colon">:</span>{{ $pkgSizeVal }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="padding: 0 4pt; border: none; height: 22.8pt;">
                                        <table style="width: 100%; table-layout: fixed;">
                                            <colgroup>
                                                <col style="width: 65%;">
                                                <col style="width: 35%;">
                                            </colgroup>
                                            <tr>
                                                <td class="b-none info-label" style="padding: 0;">Total Weight (kg/box)</td>
                                                <td class="b-none" style="padding: 0;"><span class="colon">:</span>{{ is_numeric($totWeightVal) ? number_format($totWeightVal, 2) : $totWeightVal }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 22.8pt; padding: 0; border: none; border-bottom: 0.96pt solid #000;">
                            <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                                <colgroup>
                                    <col style="width: 55.82%;">
                                    <col style="width: 44.18%;">
                                </colgroup>
                                <tr>
                                    <td style="padding: 0 4pt; border: none; border-right: 0.96pt solid #000; height: 22.8pt;">
                                        <table style="width: 100%; table-layout: fixed;">
                                            <colgroup>
                                                <col style="width: 58%;">
                                                <col style="width: 42%;">
                                            </colgroup>
                                            <tr>
                                                <td class="b-none info-label" style="padding: 0;">Packaging Lot (pcs/box)</td>
                                                <td class="b-none" style="padding: 0;"><span class="colon">:</span>{{ $pkgLotVal }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="padding: 0 4pt; border: none; height: 22.8pt;">
                                        &nbsp;
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- SECTION 1: PART / PACKAGING MATERIALS -->
                    <tr>
                        <td class="section-header" style="height: 22.8pt; border: none; border-bottom: 0.96pt solid #000;">
                            PART / PACKAGING MATERIALS
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0; border: none; border-bottom: 0.96pt solid #000;">
                            <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                                <colgroup>
                                    <col style="width: 7.63%;">
                                    <col style="width: 28.11%;">
                                    <col style="width: 32.13%;">
                                    <col style="width: 20.08%;">
                                    <col style="width: 12.05%;">
                                </colgroup>
                                <tr>
                                    <td class="center" style="width: 7.63%; height: 11pt; padding: 0; border: none; border-right: 0.96pt solid #000;">No.</td>
                                    <td class="center" style="width: 28.11%; height: 11pt; padding: 0; border: none; border-right: 0.96pt solid #000;">Part No.</td>
                                    <td class="center" style="width: 32.13%; height: 11pt; padding: 0; border: none; border-right: 0.96pt solid #000;">Part Name</td>
                                    <td class="center" style="width: 20.08%; height: 11pt; padding: 0; border: none; border-right: 0.96pt solid #000;">Supplier</td>
                                    <td class="center" style="width: 12.05%; height: 11pt; padding: 0; border: none;">Qty</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @for($i = 1; $i <= 6; $i++)
                        @php $mat = $materials[$i - 1] ?? null; @endphp
                        <tr>
                            <td style="padding: 0; border: none; border-bottom: 0.96pt solid #000;">
                                <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                                    <colgroup>
                                        <col style="width: 7.63%;">
                                        <col style="width: 28.11%;">
                                        <col style="width: 32.13%;">
                                        <col style="width: 20.08%;">
                                        <col style="width: 12.05%;">
                                    </colgroup>
                                    <tr>
                                        <td class="center" style="width: 7.63%; height: 22.8pt; padding: 0; border: none; border-right: 0.96pt solid #000;">{{ $i }}</td>
                                        <td style="width: 28.11%; padding: 0 4pt; border: none; border-right: 0.96pt solid #000;">{{ $mat['part_no'] ?? $mat['partNo'] ?? '' }}</td>
                                        <td style="width: 32.13%; padding: 0 4pt; border: none; border-right: 0.96pt solid #000;">{{ $mat['part_name'] ?? $mat['partName'] ?? '' }}</td>
                                        <td style="width: 20.08%; padding: 0 4pt; border: none; border-right: 0.96pt solid #000;">{{ $mat['supplier'] ?? '' }}</td>
                                        <td class="center" style="width: 12.05%; padding: 0 2pt; border: none;">{{ $mat['qty'] ?? '' }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endfor

                    <!-- SECTION 2: REVISION STATUS -->
                    <tr>
                        <td class="section-header" style="height: 22.8pt; border: none; border-bottom: 0.96pt solid #000;">
                            REVISION STATUS
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0; border: none; border-bottom: 0.96pt solid #000;">
                            <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                                <colgroup>
                                    <col style="width: 7.63%;">
                                    <col style="width: 44.17%;">
                                    <col style="width: 12.05%;">
                                    <col style="width: 20.08%;">
                                    <col style="width: 16.07%;">
                                </colgroup>
                                <tr>
                                    <td class="center" style="width: 7.63%; height: 11pt; padding: 0; border: none; border-right: 0.96pt solid #000;">No.</td>
                                    <td class="center" style="width: 44.17%; height: 11pt; padding: 0; border: none; border-right: 0.96pt solid #000;">Modify Item</td>
                                    <td class="center" style="width: 12.05%; height: 11pt; padding: 0; border: none; border-right: 0.96pt solid #000;">Date</td>
                                    <td class="center" style="width: 20.08%; height: 11pt; padding: 0; border: none; border-right: 0.96pt solid #000;">Section</td>
                                    <td class="center" style="width: 16.07%; height: 11pt; padding: 0; border: none;">Person</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @for($r = 1; $r <= 5; $r++)
                        @php $rev = $revisions[$r - 1] ?? null; @endphp
                        <tr>
                            <td style="padding: 0; border: none; {{ $r == 5 ? '' : 'border-bottom: 0.96pt solid #000;' }}">
                                <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                                    <colgroup>
                                        <col style="width: 7.63%;">
                                        <col style="width: 44.17%;">
                                        <col style="width: 12.05%;">
                                        <col style="width: 20.08%;">
                                        <col style="width: 16.07%;">
                                    </colgroup>
                                    <tr>
                                        <td class="center" style="width: 7.63%; height: 22.8pt; padding: 0; border: none; border-right: 0.96pt solid #000;">{{ $r }}</td>
                                        <td style="width: 44.17%; padding: 0 4pt; border: none; border-right: 0.96pt solid #000;">{{ $rev['change'] ?? $rev['item'] ?? $rev['modify_item'] ?? '' }}</td>
                                        <td class="center" style="width: 12.05%; padding: 0 2pt; border: none; border-right: 0.96pt solid #000;">{{ $rev['date'] ?? '' }}</td>
                                        <td class="center" style="width: 20.08%; padding: 0 2pt; border: none; border-right: 0.96pt solid #000;">{{ $rev['section'] ?? '' }}</td>
                                        <td class="center" style="width: 16.07%; padding: 0 2pt; border: none;">{{ $rev['person'] ?? '' }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endfor
                </table>
            </td>

            <!-- RIGHT COLUMN (54.0%) -->
            <td style="width: 54.0%; padding: 0; vertical-align: top; border: none;">
                <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                    <!-- BOX 1: PHOTOGRAPHIC IMAGE OF PACKAGING (Height 216.2pt) -->
                    <tr>
                        <td style="height: 216.2pt; vertical-align: top; padding: 2pt 4pt; border: none; border-bottom: 0.96pt solid #000;">
                            <div style="font-size: 8.6pt; color: #000; margin-bottom: 2pt;">(Photographic Image of Packaging)</div>
                            <div style="text-align: center; margin-top: 20pt;">
                                @if($photoImg)
                                    <img src="{{ $photoImg }}" style="max-height: 165pt; max-width: 95%;" alt="Photographic Image">
                                @else
                                    <img src="{{ $defaultPhotoBubble }}" style="width: 100pt; height: auto;" alt="Visualisasi packing">
                                @endif
                            </div>
                        </td>
                    </tr>
                    
                    <!-- BOX 2: PART IDENTIFICATION POINT (Height 147.8pt) -->
                    <tr>
                        <td style="height: 147.8pt; vertical-align: top; padding: 2pt 4pt; border: none; border-bottom: 0.96pt solid #000;">
                            <div style="font-size: 8.6pt; color: #000; margin-bottom: 2pt;">(Part Identification Point)</div>
                            <div style="text-align: center; margin-top: 10pt;">
                                @if($labelImg)
                                    <img src="{{ $labelImg }}" style="max-height: 105pt; max-width: 95%;" alt="Part Identification">
                                @else
                                    <img src="{{ $defaultLabelBubble }}" style="width: 100pt; height: auto;" alt="Label packing">
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- BOX 3: AAIJ APPROVAL (Height 68.4pt) -->
                    <tr>
                        <td style="height: 68.4pt; padding: 0; vertical-align: top; border: none;">
                            <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                                <colgroup>
                                    <col style="width: 58.20%;">
                                    <col style="width: 41.80%;">
                                </colgroup>
                                <tr>
                                    <!-- AAIJ Approval Title Column (58.20%) -->
                                    <td class="left" style="width: 58.20%; vertical-align: top; padding: 2pt 4pt; font-size: 8.6pt; border: none;">
                                        AAIJ Approval
                                    </td>
                                    
                                    <!-- QA, PPC, Purchasing Table (41.80%) -->
                                    <td style="width: 41.80%; padding: 0; vertical-align: top; border: none; border-left: 0.96pt solid #000;">
                                        <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                                            <colgroup>
                                                <col style="width: 33.33%;">
                                                <col style="width: 33.33%;">
                                                <col style="width: 33.34%;">
                                            </colgroup>
                                            <tr>
                                                <td class="center" style="height: 11pt; padding: 0; font-size: 8.6pt; border: none; border-bottom: 0.96pt solid #000; border-right: 0.96pt solid #000;">QA</td>
                                                <td class="center" style="height: 11pt; padding: 0; font-size: 8.6pt; border: none; border-bottom: 0.96pt solid #000; border-right: 0.96pt solid #000;">PPC</td>
                                                <td class="center" style="height: 11pt; padding: 0; font-size: 8.6pt; border: none; border-bottom: 0.96pt solid #000;">Purchasing</td>
                                            </tr>
                                            <tr>
                                                <td style="height: 52.7pt; vertical-align: bottom; text-align: center; font-size: 8pt; padding: 0 2pt 2pt 2pt; border: none; border-bottom: 0.96pt solid #000; border-right: 0.96pt solid #000;">
                                                    @if($ps->isApproved() && $ps->approved_by_name)
                                                        {{ $ps->approved_by_name }}
                                                    @endif
                                                </td>
                                                <td style="height: 52.7pt; vertical-align: bottom; text-align: center; font-size: 8pt; padding: 0 2pt 2pt 2pt; border: none; border-bottom: 0.96pt solid #000; border-right: 0.96pt solid #000;">
                                                </td>
                                                <td style="height: 52.7pt; vertical-align: bottom; text-align: center; font-size: 8pt; padding: 0 2pt 2pt 2pt; border: none; border-bottom: 0.96pt solid #000;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="height: 11pt; text-align: center; font-size: 7pt; padding: 0; border: none; border-right: 0.96pt solid #000;">
                                                    @if($ps->isApproved() && $ps->approved_at)
                                                        {{ $ps->approved_at->format('d/m/y') }}
                                                    @endif
                                                </td>
                                                <td style="height: 11pt; text-align: center; font-size: 7pt; padding: 0; border: none; border-right: 0.96pt solid #000;">
                                                </td>
                                                <td style="height: 11pt; text-align: center; font-size: 7pt; padding: 0; border: none;">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

</body>
</html>

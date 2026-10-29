<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Packaging Standard - {{ $formData['generalInformation']['partNo'] ?? $ps->part_no ?? 'N/A' }}</title>
    <style>
        @page { size: 792pt 612pt; margin: 0; }
        html, body { margin: 0; padding: 0; background: #fff; }
        body { font-family: 'Bookman Old Style', 'TeX Gyre Bonum', 'Times New Roman', Times, serif; font-size: 8.6pt; color: #000; }
        .frame { position: absolute; left: 3.9pt; top: 39.3pt; width: 784pt; height: 532pt; }
        .outer { position: absolute; left: -0.96pt; top: -0.96pt; width: 782.08pt; height: 530.08pt; border: 1.92pt solid #000; }
        .hl { position: absolute; height: 0; border-top: 0.96pt solid #000; }
        .vl { position: absolute; width: 0; border-left: 0.96pt solid #000; }
        .t  { position: absolute; white-space: nowrap; overflow: visible; text-overflow: clip; }
        .c  { text-align: center; }
        .title { font-size: 19.6pt; font-weight: bold; letter-spacing: 0.2px; }
    </style>
</head>
<body>
@php
    /* =====================================================================
       HELPERS
       ===================================================================== */

    /* ambil file dari storage -> data URI base64 */
    $toDataUri = function ($path) {
        if (!$path) return null;
        foreach ([storage_path('app/private/' . $path), storage_path('app/' . $path),
                  storage_path('app/public/' . $path), public_path('storage/' . $path), $path] as $p) {
            if (is_file($p)) {
                $ext  = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                $mime = match ($ext) { 'jpg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'webp' => 'image/webp', default => 'image/png' };
                $data = @file_get_contents($p);
                return $data === false ? null : 'data:' . $mime . ';base64,' . base64_encode($data);
            }
        }
        \Log::warning('Packaging Standard PDF: image not found: ' . $path);
        return null;
    };

    /* skala gambar agar muat di kotak (pt), rasio tetap */
    $fit = function ($uri, $boxW, $boxH) {
        $raw  = base64_decode(substr($uri, strpos($uri, ',') + 1));
        $info = @getimagesizefromstring($raw);
        if (!$info || !$info[0] || !$info[1]) return [$boxW, $boxH];
        $r = min($boxW / $info[0], $boxH / $info[1]);
        return [$info[0] * $r, $info[1] * $r];
    };

    /* ambil nilai pertama yang tidak kosong dari beberapa kemungkinan key */
    $pick = function ($row, array $keys) {
        foreach ($keys as $k) {
            if (isset($row[$k]) && $row[$k] !== '') return $row[$k];
        }
        return '';
    };
    $fmtNum = fn ($v) => is_numeric($v) ? number_format($v, 2) : $v;

    /* AUTO-FIT TEKS ke kotak (w x h pt).
       Urutan: 1 baris ukuran normal -> perkecil font -> pecah 2..$maxLines baris (rata tengah vertikal)
       $k = estimasi lebar rata-rata huruf (em): ~0.55 huruf campur, ~0.72 huruf KAPITAL tebal.
       Mengembalikan string CSS (font-size, line-height, wrap, padding). */
    $fitText = function ($text, $w, $h = 24.3, $base = 8.6, $min = 5.5, $k = 0.55, $maxLines = 3) {
        $len = max(1, mb_strlen((string) $text));
        for ($lines = 1; $lines <= $maxLines; $lines++) {
            $fs = min($base, ($w * $lines * 0.92) / ($len * $k));
            $lh = round($fs * 1.15, 2);
            if ($fs >= $min && $lines * $lh <= $h) {
                if ($lines === 1) {
                    return 'font-size:' . round($fs, 2) . "pt;line-height:{$h}pt;white-space:nowrap;";
                }
                $pad = round(($h - $lines * $lh) / 2, 2);
                return 'font-size:' . round($fs, 2) . "pt;line-height:{$lh}pt;white-space:normal;"
                     . "word-wrap:break-word;overflow:hidden;padding-top:{$pad}pt;"
                     . 'height:' . round($h - $pad, 2) . 'pt;';
            }
        }
        return "font-size:{$min}pt;line-height:{$h}pt;white-space:nowrap;overflow:hidden;";
    };

    /* susun grid foto (auto 1 / 2 / 4 / 3 kolom) -> array item [uri, left, top, w, h] */
    $buildGrid = function ($images, $areaW, $areaH, $originX, $originY) use ($toDataUri, $fit) {
        $uris = [];
        foreach ($images as $img) {
            $u = $toDataUri($img->file_path);
            if ($u) $uris[] = $u;
        }
        $n = count($uris);
        if ($n === 0) return [];

        $spacing = 5;
        if     ($n == 1)  { $cols = 1; $rows = 1; }
        elseif ($n == 2)  { $cols = 2; $rows = 1; }
        elseif ($n <= 4)  { $cols = 2; $rows = 2; }
        else              { $cols = 3; $rows = (int) ceil($n / 3); }

        $cellW = ($areaW - $spacing * ($cols - 1)) / $cols;
        $cellH = ($areaH - $spacing * ($rows - 1)) / $rows;

        $items = [];
        foreach ($uris as $i => $u) {
            $col = $i % $cols;
            $row = (int) floor($i / $cols);
            [$w, $h] = $fit($u, $cellW, $cellH);
            $items[] = [
                'uri'  => $u,
                'left' => round($originX + $col * ($cellW + $spacing) + ($cellW - $w) / 2, 2),
                'top'  => round($originY + $row * ($cellH + $spacing) + ($cellH - $h) / 2, 2),
                'w'    => round($w, 2),
                'h'    => round($h, 2),
            ];
        }
        return $items;
    };

    /* =====================================================================
       DATA
       ===================================================================== */

    /* placeholder balon (tampil hanya jika foto belum di-upload) -> public/images/ */
    $bubblePhoto = is_file(public_path('images/ps-bubble-photo.png')) ? $toDataUri(public_path('images/ps-bubble-photo.png')) : null;
    $bubbleLabel = is_file(public_path('images/ps-bubble-label.png')) ? $toDataUri(public_path('images/ps-bubble-label.png')) : null;

    /* logo perusahaan */
    $companyLogo = null;
    if (!empty($ps->company_logo)) {
        $companyLogo = str_starts_with($ps->company_logo, 'data:image') ? $ps->company_logo : $toDataUri($ps->company_logo);
    }

    /* data form */
    $docNo        = $formData['generalInformation']['documentNo'] ?? $ps->document_no ?? '';
    $revision     = $formData['generalInformation']['revision'] ?? $ps->revision ?? '';
    $dateVal      = $ps->date ? $ps->date->format('Y-m-d') : ($formData['generalInformation']['date'] ?? '');
    $customerVal  = strtoupper($formData['generalInformation']['customer'] ?? $ps->customer ?? 'PT. AKEBONO BRAKE ASTRA INDONESIA');
    $partNoVal    = $formData['generalInformation']['partNo'] ?? $ps->part_no ?? '';
    $partNameVal  = $formData['generalInformation']['partName'] ?? $ps->part_name ?? '';
    $pkgTypeVal   = $formData['specifications']['packagingType'] ?? $ps->packaging_type ?? '';
    $pkgSizeVal   = $formData['specifications']['packagingSizeMm'] ?? $ps->packaging_size_mm ?? '';
    $pkgLotVal    = $formData['specifications']['packagingLotPcsBox'] ?? $ps->packaging_lot_pcs_box ?? '';
    $netWeightVal = $fmtNum($formData['specifications']['netWeightGPc'] ?? $ps->net_weight_g_pc ?? '');
    $totWeightVal = $fmtNum($formData['specifications']['totalWeightKgBox'] ?? $ps->total_weight_kg_box ?? '');

    /* nama approval (prioritas: field QR/name di model, fallback ke formData) */
    $approvedName = $ps->approved_by_name ?: ($formData['visualizationApproval']['approvedByName'] ?? '');
    $checkedName  = $ps->checked_by_name  ?: ($formData['visualizationApproval']['checkedByName'] ?? '');
    $preparedName = $ps->prepared_by_name ?: ($formData['visualizationApproval']['preparedByName'] ?? '');

    /* QR code approval */
    $approvedQr = $ps->approved_qr_data ?? null;
    $checkedQr  = $ps->checked_qr_data ?? null;
    $preparedQr = $ps->prepared_qr_data ?? null;

    $qaName = ($ps->isApproved() && $ps->approved_by_name) ? $ps->approved_by_name : '';
    $qaDate = ($ps->isApproved() && $ps->approved_at) ? $ps->approved_at->format('d/m/y') : '';

    $materials = $formData['specifications']['materials'] ?? $formData['materials'] ?? ($ps->form_data_json['materials'] ?? []);
    if (!is_array($materials)) $materials = [];

    $revisions = $formData['visualizationApproval']['revisionHistory'] ?? $ps->revision_history ?? ($ps->form_data_json['revisionHistory'] ?? []);
    if (!is_array($revisions)) $revisions = json_decode($revisions, true) ?? [];

    /* supplier (header kiri atas) */
    $hasSupplier  = !empty($ps->supplier);
    $supplierName = strtoupper($hasSupplier ? ($ps->supplier->name ?? 'YOUR COMPANY') : 'YOUR COMPANY LOGO');
    if ($companyLogo) {
        [$lw, $lh]    = $fit($companyLogo, 60, 24);
        $logoLeftPos  = 5;
        $textLeftPos  = $logoLeftPos + $lw + 5;
        $supTextWidth = 202.42 - $textLeftPos - 4;   // berhenti sebelum garis vertikal
    } else {
        $textLeftPos  = 2;
        $supTextWidth = 198;
    }

    /* kotak tanda tangan atas: Approved / Checked / Prepared */
    $signBoxes = [
        ['label' => 'Approved', 'left' => 610.0, 'w' => 58.3, 'qr' => $approvedQr, 'name' => $approvedName],
        ['label' => 'Checked',  'left' => 668.3, 'w' => 58.2, 'qr' => $checkedQr,  'name' => $checkedName],
        ['label' => 'Prepared', 'left' => 726.5, 'w' => 57.5, 'qr' => $preparedQr, 'name' => $preparedName],
    ];
    $qrSize  = 32;     // pt
    $qrTop   = 13.5;   // tepat di bawah garis label (11.92)
    $nameTop = 46.5;   // tepat di bawah QR
    $nameH   = 12;     // sampai 58.5 (garis bawah kotak di 59.02)

    /* foto */
    $photoItems = $buildGrid($ps->imagesByType('image_of_packaging'), 413.4, 214.3, 366.6, 83.7);
    $labelItems = $buildGrid($ps->imagesByType('part_identification_point'), 413.4, 141.8, 366.6, 314);
@endphp

<div class="frame">
    <div class="outer"></div>

    {{-- ================= GARIS HORIZONTAL ================= --}}
    <div class="hl" style="left:0pt;top:23.72pt;width:202.9pt"></div>
    <div class="hl" style="left:0pt;top:35.32pt;width:202.9pt"></div>
    <div class="hl" style="left:0pt;top:47.02pt;width:202.9pt"></div>
    <div class="hl" style="left:0pt;top:58.62pt;width:202.9pt"></div>
    <div class="hl" style="left:610pt;top:11.92pt;width:174pt"></div>
    <div class="hl" style="left:610pt;top:59.02pt;width:174pt"></div>
    <div class="hl" style="left:0pt;top:70.22pt;width:784pt"></div>
    <div class="hl" style="left:0pt;top:94.52pt;width:362.6pt"></div>
    <div class="hl" style="left:0pt;top:118.82pt;width:362.6pt"></div>
    <div class="hl" style="left:0pt;top:143.12pt;width:362.6pt"></div>
    <div class="hl" style="left:0pt;top:167.42pt;width:362.6pt"></div>
    <div class="hl" style="left:0pt;top:191.72pt;width:362.6pt"></div>
    <div class="hl" style="left:0pt;top:216.02pt;width:362.6pt"></div>
    <div class="hl" style="left:0pt;top:227.92pt;width:362.6pt"></div>
    <div class="hl" style="left:0pt;top:252.22pt;width:362.6pt"></div>
    <div class="hl" style="left:0pt;top:276.52pt;width:362.6pt"></div>
    <div class="hl" style="left:0pt;top:300.82pt;width:362.6pt"></div>
    <div class="hl" style="left:0pt;top:325.12pt;width:362.6pt"></div>
    <div class="hl" style="left:0pt;top:349.42pt;width:362.6pt"></div>
    <div class="hl" style="left:0pt;top:373.72pt;width:362.6pt"></div>
    <div class="hl" style="left:0pt;top:398.02pt;width:362.6pt"></div>
    <div class="hl" style="left:0pt;top:409.92pt;width:362.6pt"></div>
    <div class="hl" style="left:0pt;top:434.22pt;width:362.6pt"></div>
    <div class="hl" style="left:0pt;top:458.52pt;width:362.6pt"></div>
    <div class="hl" style="left:0pt;top:482.82pt;width:362.6pt"></div>
    <div class="hl" style="left:0pt;top:507.12pt;width:362.6pt"></div>
    <div class="hl" style="left:362.6pt;top:300.52pt;width:421.4pt"></div>
    <div class="hl" style="left:362.6pt;top:458.32pt;width:421.4pt"></div>
    <div class="hl" style="left:610pt;top:470.72pt;width:174pt"></div>
    <div class="hl" style="left:610pt;top:518.52pt;width:174pt"></div>

    {{-- ================= GARIS VERTIKAL ================= --}}
    <div class="vl" style="left:202.42pt;top:0pt;height:70.7pt"></div>
    <div class="vl" style="left:609.52pt;top:0pt;height:70.7pt"></div>
    <div class="vl" style="left:667.82pt;top:0pt;height:70.7pt"></div>
    <div class="vl" style="left:726.02pt;top:0pt;height:70.7pt"></div>
    <div class="vl" style="left:362.12pt;top:70.7pt;height:461.3pt"></div>
    <div class="vl" style="left:202.42pt;top:119.3pt;height:72.9pt"></div>
    <div class="vl" style="left:28.32pt;top:216.5pt;height:157.7pt"></div>
    <div class="vl" style="left:129.72pt;top:216.5pt;height:157.7pt"></div>
    <div class="vl" style="left:246.22pt;top:216.5pt;height:157.7pt"></div>
    <div class="vl" style="left:318.92pt;top:216.5pt;height:157.7pt"></div>
    <div class="vl" style="left:28.32pt;top:398.5pt;height:133.5pt"></div>
    <div class="vl" style="left:188.02pt;top:398.5pt;height:133.5pt"></div>
    <div class="vl" style="left:231.82pt;top:398.5pt;height:133.5pt"></div>
    <div class="vl" style="left:304.52pt;top:398.5pt;height:133.5pt"></div>
    <div class="vl" style="left:609.52pt;top:458.8pt;height:73.2pt"></div>
    <div class="vl" style="left:667.82pt;top:458.8pt;height:73.2pt"></div>
    <div class="vl" style="left:726.02pt;top:458.8pt;height:73.2pt"></div>

    {{-- ================= HEADER KIRI: LOGO + SUPPLIER ================= --}}
    @if($companyLogo)
        <img src="{{ $companyLogo }}" style="position:absolute;left:{{ $logoLeftPos }}pt;top:0pt;width:{{ round($lw, 2) }}pt;height:{{ round($lh, 2) }}pt;" alt="Company Logo">
    @endif
    <div class="t {{ $companyLogo ? '' : 'c' }}" style="left:{{ round($textLeftPos, 2) }}pt;top:0pt;width:{{ round($supTextWidth, 2) }}pt;height:24.2pt;font-family:Arial,Helvetica,sans-serif;font-weight:bold;{{ $fitText($supplierName, $supTextWidth, 24.2, 10, 6, 0.72, 2) }}">{{ $supplierName }}</div>

    <div class="t " style="left:2pt;top:24.2pt;width:85pt;height:11.6pt;line-height:11.6pt;">Document No.</div>
    <div class="t " style="left:89pt;top:24.2pt;width:4pt;height:11.6pt;line-height:11.6pt;">:</div>
    <div class="t " style="left:93.5pt;top:24.2pt;width:107pt;height:11.6pt;{{ $fitText($docNo, 107, 11.6, 8.6, 5, 0.55, 1) }}">{{ $docNo }}</div>

    <div class="t " style="left:2pt;top:35.8pt;width:85pt;height:11.7pt;line-height:11.7pt;">Revision</div>
    <div class="t " style="left:89pt;top:35.8pt;width:4pt;height:11.7pt;line-height:11.7pt;">:</div>
    <div class="t " style="left:93.5pt;top:35.8pt;width:107pt;height:11.7pt;{{ $fitText($revision, 107, 11.7, 8.6, 5, 0.55, 1) }}">{{ $revision }}</div>

    <div class="t " style="left:2pt;top:47.5pt;width:85pt;height:11.6pt;line-height:11.6pt;">Date</div>
    <div class="t " style="left:89pt;top:47.5pt;width:4pt;height:11.6pt;line-height:11.6pt;">:</div>
    <div class="t " style="left:93.5pt;top:47.5pt;width:107pt;height:11.6pt;{{ $fitText($dateVal, 107, 11.6, 8.6, 5, 0.55, 1) }}">{{ $dateVal }}</div>

    <div class="t " style="left:2pt;top:59.1pt;width:85pt;height:11.6pt;line-height:11.6pt;">Customer</div>
    <div class="t " style="left:89pt;top:59.1pt;width:4pt;height:11.6pt;line-height:11.6pt;">:</div>
    <div class="t " style="left:93.5pt;top:59.1pt;width:108pt;height:11.6pt;font-weight:bold;{{ $fitText($customerVal, 108, 11.6, 8.6, 5, 0.72, 1) }}">{{ $customerVal }}</div>

    {{-- ================= JUDUL ================= --}}
    @php
        /* Judul diposisikan tepat di tengah kotak (tinggi 70.7pt).
           DomPDF menurunkan teks kalau line-height-nya besar, jadi pakai line-height kecil
           (= tinggi font) dan hitung top dari tengah kotak.
           $titleNudge: koreksi halus (pt). Negatif = naik, positif = turun. */
        $titleBoxH   = 70.7;
        $titleLineH  = 24;
        $titleNudge  = 1;
        $titleTop    = round(($titleBoxH - $titleLineH) / 2 + $titleNudge, 2);
    @endphp
    <div class="t c title" style="left:202.9pt;top:{{ $titleTop }}pt;width:407.1pt;height:{{ $titleLineH }}pt;line-height:{{ $titleLineH }}pt;">PACKAGING STANDARD</div>

    {{-- ================= APPROVED / CHECKED / PREPARED (QR + nama pas di kotak) ================= --}}
    @foreach($signBoxes as $b)
        <div class="t c" style="left:{{ $b['left'] }}pt;top:0pt;width:{{ $b['w'] }}pt;height:12.4pt;line-height:12.4pt;">{{ $b['label'] }}</div>

        @if($b['qr'])
            <img src="{{ $b['qr'] }}" style="position:absolute;left:{{ round($b['left'] + ($b['w'] - $qrSize) / 2, 2) }}pt;top:{{ $qrTop }}pt;width:{{ $qrSize }}pt;height:{{ $qrSize }}pt;" alt="{{ $b['label'] }} QR">
        @endif

        @if(!empty($b['name']))
            <div class="t c" style="left:{{ $b['left'] + 2 }}pt;top:{{ $nameTop }}pt;width:{{ $b['w'] - 4 }}pt;height:{{ $nameH }}pt;font-weight:bold;{{ $fitText($b['name'], $b['w'] - 4, $nameH, 6.5, 4.5, 0.72, 2) }}">{{ $b['name'] }}</div>
        @endif
    @endforeach

    {{-- ================= INFORMASI UMUM & SPESIFIKASI ================= --}}
    <div class="t " style="left:2pt;top:70.7pt;width:116.3pt;height:24.3pt;line-height:24.3pt;">Part No.</div>
    <div class="t " style="left:118.3pt;top:70.7pt;width:4pt;height:24.3pt;line-height:24.3pt;">:</div>
    <div class="t " style="left:122.9pt;top:70.7pt;width:235pt;height:24.3pt;{{ $fitText($partNoVal, 235) }}">{{ $partNoVal }}</div>

    <div class="t " style="left:2pt;top:95pt;width:116.3pt;height:24.3pt;line-height:24.3pt;">Parts Name</div>
    <div class="t " style="left:118.3pt;top:95pt;width:4pt;height:24.3pt;line-height:24.3pt;">:</div>
    <div class="t " style="left:122.9pt;top:95pt;width:235pt;height:24.3pt;{{ $fitText($partNameVal, 235) }}">{{ $partNameVal }}</div>

    <div class="t " style="left:2pt;top:119.3pt;width:116.3pt;height:24.3pt;line-height:24.3pt;">Packaging Type</div>
    <div class="t " style="left:118.3pt;top:119.3pt;width:4pt;height:24.3pt;line-height:24.3pt;">:</div>
    <div class="t " style="left:122.9pt;top:119.3pt;width:77pt;height:24.3pt;{{ $fitText($pkgTypeVal, 77) }}">{{ $pkgTypeVal }}</div>

    <div class="t " style="left:2pt;top:143.6pt;width:116.3pt;height:24.3pt;line-height:24.3pt;font-size:7.5pt;">Packaging Size (mm)</div>
    <div class="t " style="left:118.3pt;top:143.6pt;width:4pt;height:24.3pt;line-height:24.3pt;">:</div>
    <div class="t " style="left:122.9pt;top:143.6pt;width:77pt;height:24.3pt;{{ $fitText($pkgSizeVal, 77) }}">{{ $pkgSizeVal }}</div>

    <div class="t " style="left:2pt;top:167.9pt;width:116.3pt;height:24.3pt;line-height:24.3pt;">Packaging Lot (pcs/box)</div>
    <div class="t " style="left:118.3pt;top:167.9pt;width:4pt;height:24.3pt;line-height:24.3pt;">:</div>
    <div class="t " style="left:122.9pt;top:167.9pt;width:77pt;height:24.3pt;{{ $fitText($pkgLotVal, 77) }}">{{ $pkgLotVal }}</div>

    <div class="t " style="left:205.3pt;top:119.3pt;width:101.8pt;height:24.3pt;line-height:24.3pt;">Net Weight (g/pc)</div>
    <div class="t " style="left:307.1pt;top:119.3pt;width:4pt;height:24.3pt;line-height:24.3pt;">:</div>
    <div class="t " style="left:311.7pt;top:119.3pt;width:46pt;height:24.3pt;{{ $fitText($netWeightVal, 46) }}">{{ $netWeightVal }}</div>

    <div class="t " style="left:205.3pt;top:143.6pt;width:101.8pt;height:24.3pt;line-height:24.3pt;">Total Weight (kg/box)</div>
    <div class="t " style="left:307.1pt;top:143.6pt;width:4pt;height:24.3pt;line-height:24.3pt;">:</div>
    <div class="t " style="left:311.7pt;top:143.6pt;width:46pt;height:24.3pt;{{ $fitText($totWeightVal, 46) }}">{{ $totWeightVal }}</div>

    {{-- ================= TABEL MATERIAL ================= --}}
    <div class="t c" style="left:0pt;top:192.2pt;width:362.6pt;height:24.3pt;line-height:24.3pt;">PART / PACKAGING MATERIALS</div>
    <div class="t c" style="left:0pt;top:216.5pt;width:28.8pt;height:11.9pt;line-height:11.9pt;">No.</div>
    <div class="t c" style="left:28.8pt;top:216.5pt;width:101.4pt;height:11.9pt;line-height:11.9pt;">Part No.</div>
    <div class="t c" style="left:130.2pt;top:216.5pt;width:116.5pt;height:11.9pt;line-height:11.9pt;">Part Name</div>
    <div class="t c" style="left:246.7pt;top:216.5pt;width:72.7pt;height:11.9pt;line-height:11.9pt;">Supplier</div>
    <div class="t c" style="left:319.4pt;top:216.5pt;width:43.2pt;height:11.9pt;line-height:11.9pt;">Qty</div>

    @for($i = 0; $i < 6; $i++)
        @php
            $row = $materials[$i] ?? [];
            $y   = 228.4 + $i * 24.3;
            $mPn = $pick($row, ['part_no', 'partNo']);
            $mNm = $pick($row, ['part_name', 'partName']);
            $mSp = $pick($row, ['supplier']);
            $mQt = $pick($row, ['qty']);
        @endphp
        <div class="t c" style="left:0pt;top:{{ $y }}pt;width:28.8pt;height:24.3pt;line-height:24.3pt">{{ $i + 1 }}</div>
        <div class="t" style="left:30.8pt;top:{{ $y }}pt;width:97.4pt;height:24.3pt;{{ $fitText($mPn, 97.4) }}">{{ $mPn }}</div>
        <div class="t" style="left:132.2pt;top:{{ $y }}pt;width:112.5pt;height:24.3pt;{{ $fitText($mNm, 112.5) }}">{{ $mNm }}</div>
        <div class="t" style="left:248.7pt;top:{{ $y }}pt;width:68.7pt;height:24.3pt;{{ $fitText($mSp, 68.7) }}">{{ $mSp }}</div>
        <div class="t c" style="left:319.4pt;top:{{ $y }}pt;width:43.2pt;height:24.3pt;{{ $fitText($mQt, 41) }}">{{ $mQt }}</div>
    @endfor

    {{-- ================= TABEL REVISI ================= --}}
    <div class="t c" style="left:0pt;top:374.2pt;width:362.6pt;height:24.3pt;line-height:24.3pt;">REVISION STATUS</div>
    <div class="t c" style="left:0pt;top:398.5pt;width:28.8pt;height:11.9pt;line-height:11.9pt;">No.</div>
    <div class="t c" style="left:28.8pt;top:398.5pt;width:159.7pt;height:11.9pt;line-height:11.9pt;">Modify Item</div>
    <div class="t c" style="left:188.5pt;top:398.5pt;width:43.8pt;height:11.9pt;line-height:11.9pt;">Date</div>
    <div class="t c" style="left:232.3pt;top:398.5pt;width:72.7pt;height:11.9pt;line-height:11.9pt;">Section</div>
    <div class="t c" style="left:305pt;top:398.5pt;width:57.6pt;height:11.9pt;line-height:11.9pt;">Person</div>

    @for($i = 0; $i < 5; $i++)
        @php
            $row = $revisions[$i] ?? [];
            $y   = 410.4 + $i * 24.3;
            $rIt = $pick($row, ['change', 'item', 'modify_item']);
            $rDt = $pick($row, ['date']);
            $rSc = $pick($row, ['section']);
            $rPs = $pick($row, ['person']);
        @endphp
        <div class="t c" style="left:0pt;top:{{ $y }}pt;width:28.8pt;height:24.3pt;line-height:24.3pt">{{ $i + 1 }}</div>
        <div class="t" style="left:30.8pt;top:{{ $y }}pt;width:155.7pt;height:24.3pt;{{ $fitText($rIt, 155.7) }}">{{ $rIt }}</div>
        <div class="t c" style="left:188.5pt;top:{{ $y }}pt;width:43.8pt;height:24.3pt;{{ $fitText($rDt, 41.8) }}">{{ $rDt }}</div>
        <div class="t c" style="left:232.3pt;top:{{ $y }}pt;width:72.7pt;height:24.3pt;{{ $fitText($rSc, 70.7) }}">{{ $rSc }}</div>
        <div class="t c" style="left:305pt;top:{{ $y }}pt;width:57.6pt;height:24.3pt;{{ $fitText($rPs, 55.6) }}">{{ $rPs }}</div>
    @endfor

    {{-- ================= JUDUL KOTAK FOTO ================= --}}
    <div class="t " style="left:365pt;top:70.7pt;width:415pt;height:12pt;line-height:12pt;">(Photographic Image of Packaging)</div>
    <div class="t " style="left:365pt;top:301pt;width:415pt;height:12pt;line-height:12pt;">(Part Identification Point)</div>

    {{-- ================= FOTO PACKAGING (grid otomatis) ================= --}}
    @if(count($photoItems) > 0)
        @foreach($photoItems as $i => $it)
            <img src="{{ $it['uri'] }}" style="position:absolute;left:{{ $it['left'] }}pt;top:{{ $it['top'] }}pt;width:{{ $it['w'] }}pt;height:{{ $it['h'] }}pt" alt="Photo {{ $i + 1 }}">
        @endforeach
    @elseif($bubblePhoto)
        <img src="{{ $bubblePhoto }}" style="position:absolute;left:490.2pt;top:103.44pt;width:107.1pt;height:102.24pt" alt="Visualisasi packing">
    @endif

    {{-- ================= FOTO PART IDENTIFICATION (grid otomatis) ================= --}}
    @if(count($labelItems) > 0)
        @foreach($labelItems as $i => $it)
            <img src="{{ $it['uri'] }}" style="position:absolute;left:{{ $it['left'] }}pt;top:{{ $it['top'] }}pt;width:{{ $it['w'] }}pt;height:{{ $it['h'] }}pt" alt="Label {{ $i + 1 }}">
        @endforeach
    @elseif($bubbleLabel)
        <img src="{{ $bubbleLabel }}" style="position:absolute;left:474.72pt;top:317.1pt;width:106.74pt;height:102.6pt" alt="Label packing">
    @endif

    {{-- ================= AAIJ APPROVAL: QA / PPC / PURCHASING ================= --}}
    <div class="t " style="left:365pt;top:458.8pt;width:240pt;height:12pt;line-height:12pt;">AAIJ Approval</div>
    <div class="t c" style="left:610pt;top:458.8pt;width:58.3pt;height:12.4pt;line-height:12.4pt;">QA</div>

    @if(!empty($approverSignature) && isset($qrService))
        @php
            try {
                $approverQr = $qrService->generate(
                    $approverSignature->npk,
                    $approverSignature->name,
                    $approverSignature->role_label
                );
            } catch (\Exception $e) {
                \Log::error('Failed to generate approver QR in PS PDF', [
                    'signature_id' => $approverSignature->id,
                    'error'        => $e->getMessage(),
                ]);
                $approverQr = null;
            }
            $apvDate = $approverSignature->signed_at->format('d/m/Y');
        @endphp
        @if($approverQr)
            <img src="{{ $approverQr }}" style="position:absolute;left:{{ round(610 + (58.3 - 36) / 2, 2) }}pt;top:472.5pt;width:36pt;height:36pt;" alt="QA Approver QR">
            <div class="t c" style="left:612pt;top:509pt;width:54.3pt;height:11pt;font-weight:bold;{{ $fitText($approverSignature->name, 54.3, 11, 7, 4.5, 0.72, 2) }}">{{ $approverSignature->name }}</div>
            <div class="t c" style="left:610pt;top:521pt;width:58.3pt;height:10pt;line-height:10pt;font-size:6pt;">{{ $apvDate }}</div>
        @else
            <div class="t c" style="left:612pt;top:508pt;width:54.3pt;height:10pt;{{ $fitText($approverSignature->name, 54.3, 10, 8, 4.5, 0.55, 1) }}">{{ $approverSignature->name }}</div>
            <div class="t c" style="left:610pt;top:519pt;width:58.3pt;height:13pt;line-height:13pt;font-size:7pt;">{{ $apvDate }}</div>
        @endif
    @else
        <div class="t c" style="left:612pt;top:508pt;width:54.3pt;height:10pt;{{ $fitText($qaName, 54.3, 10, 8, 4.5, 0.55, 1) }}">{{ $qaName }}</div>
        <div class="t c" style="left:610pt;top:519pt;width:58.3pt;height:13pt;line-height:13pt;font-size:7pt;">{{ $qaDate }}</div>
    @endif

    <div class="t c" style="left:668.3pt;top:458.8pt;width:58.2pt;height:12.4pt;line-height:12.4pt;">PPC</div>
    <div class="t c" style="left:726.5pt;top:458.8pt;width:57.5pt;height:12.4pt;line-height:12.4pt;">Purchasing</div>
</div>
</body>
</html>
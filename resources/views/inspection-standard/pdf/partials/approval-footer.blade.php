<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Inspection Standard - {{ $generalInformation['partNo'] ?? 'N/A' }}</title>
<style>
@page { size: 612pt 792pt; margin: 0; }
* { margin: 0; padding: 0; }
html, body { margin: 0; padding: 0; }
body { font-family: Helvetica, Arial, sans-serif; color: #000; background: #fff; }
.page { position: relative; width: 612pt; height: 790pt; overflow: hidden; }
.ln   { position: absolute; background: #000; }          /* garis tabel */
.t    { position: absolute; white-space: nowrap; }       /* teks label tetap */
.b    { font-weight: bold; }
.cell { position: absolute; overflow: hidden; }          /* sel isian dinamis */
.dimg { display: block; margin: 0 auto 4pt auto; }
.dinfo { font-size: 8pt; line-height: 10pt; }
.dlbl { display: inline-block; width: 70pt; font-weight: bold; }
/* Jika di DomPDF teks terlihat naik/turun ~0.5pt, geser semua label sekaligus di sini: */
/* .t { margin-top: 0.5pt; } */
</style>
</head>
<body>
{{-- Semua posisi (pt) diukur langsung dari vektor PDF referensi (Letter portrait 612 x 792 pt) --}}

{{-- ========== HALAMAN 1 : COVER ========== --}}
<div class="page" style="page-break-after: always;">
<div class="ln" style="left:46.7pt;top:28.1pt;width:1.9pt;height:735pt"></div>
<div class="ln" style="left:48.6pt;top:28.1pt;width:516.7pt;height:1.9pt"></div>
<div class="ln" style="left:150.5pt;top:30pt;width:1pt;height:37.4pt"></div>
<div class="ln" style="left:327.7pt;top:30pt;width:1pt;height:93.6pt"></div>
<div class="ln" style="left:563.4pt;top:30pt;width:1.9pt;height:733.1pt"></div>
<div class="ln" style="left:48.6pt;top:66.5pt;width:514.8pt;height:1pt"></div>
<div class="ln" style="left:209.6pt;top:67.4pt;width:1pt;height:56.2pt"></div>
<div class="ln" style="left:268.6pt;top:67.4pt;width:1pt;height:56.2pt"></div>
<div class="ln" style="left:416.2pt;top:67.4pt;width:1pt;height:56.2pt"></div>
<div class="ln" style="left:504.8pt;top:67.4pt;width:1pt;height:56.2pt"></div>
<div class="ln" style="left:48.6pt;top:85.2pt;width:514.8pt;height:1pt"></div>
<div class="ln" style="left:48.6pt;top:122.7pt;width:514.8pt;height:1pt"></div>
<div class="ln" style="left:254.3pt;top:276.3pt;width:192pt;height:0.1pt"></div>
<div class="ln" style="left:254.3pt;top:293.1pt;width:192pt;height:0.1pt"></div>
<div class="ln" style="left:48.6pt;top:366.8pt;width:265.2pt;height:1pt"></div>
<div class="ln" style="left:386.7pt;top:366.8pt;width:1pt;height:75.1pt"></div>
<div class="ln" style="left:387.7pt;top:366.8pt;width:175.7pt;height:1pt"></div>
<div class="ln" style="left:180pt;top:367.7pt;width:1pt;height:74.2pt"></div>
<div class="ln" style="left:312.9pt;top:367.7pt;width:1pt;height:74.2pt"></div>
<div class="ln" style="left:48.6pt;top:379.1pt;width:265.2pt;height:1pt"></div>
<div class="ln" style="left:387.7pt;top:379.1pt;width:175.7pt;height:1pt"></div>
<div class="ln" style="left:91.5pt;top:380.1pt;width:1pt;height:61.8pt"></div>
<div class="ln" style="left:135.7pt;top:380.1pt;width:1pt;height:61.8pt"></div>
<div class="ln" style="left:224.3pt;top:380.1pt;width:1pt;height:61.8pt"></div>
<div class="ln" style="left:268.6pt;top:380.1pt;width:1pt;height:61.8pt"></div>
<div class="ln" style="left:445.8pt;top:380.1pt;width:1pt;height:61.8pt"></div>
<div class="ln" style="left:504.8pt;top:380.1pt;width:1pt;height:61.8pt"></div>
<div class="ln" style="left:48.6pt;top:391.5pt;width:265.2pt;height:1pt"></div>
<div class="ln" style="left:387.7pt;top:391.5pt;width:175.7pt;height:1pt"></div>
<div class="ln" style="left:48.6pt;top:440.9pt;width:514.8pt;height:1pt"></div>
<div class="ln" style="left:48.6pt;top:453.3pt;width:514.8pt;height:1pt"></div>
<div class="ln" style="left:135.7pt;top:454.3pt;width:1pt;height:306.9pt"></div>
<div class="ln" style="left:165.3pt;top:454.3pt;width:1pt;height:208.9pt"></div>
<div class="ln" style="left:209.6pt;top:454.3pt;width:1pt;height:141.6pt"></div>
<div class="ln" style="left:445.8pt;top:454.3pt;width:1pt;height:208.9pt"></div>
<div class="ln" style="left:475.3pt;top:454.3pt;width:1pt;height:233.7pt"></div>
<div class="ln" style="left:48.6pt;top:479.2pt;width:514.8pt;height:1pt"></div>
<div class="ln" style="left:76.7pt;top:480.2pt;width:1pt;height:207.7pt"></div>
<div class="ln" style="left:106.2pt;top:480.2pt;width:1pt;height:207.7pt"></div>
<div class="ln" style="left:504.8pt;top:480.2pt;width:1pt;height:207.7pt"></div>
<div class="ln" style="left:534.3pt;top:480.2pt;width:1pt;height:207.7pt"></div>
<div class="ln" style="left:48.6pt;top:505.2pt;width:514.8pt;height:1pt"></div>
<div class="ln" style="left:48.6pt;top:527.6pt;width:514.8pt;height:1pt"></div>
<div class="ln" style="left:48.6pt;top:550pt;width:514.8pt;height:1pt"></div>
<div class="ln" style="left:48.6pt;top:572.5pt;width:514.8pt;height:1pt"></div>
<div class="ln" style="left:48.6pt;top:594.9pt;width:514.8pt;height:1pt"></div>
<div class="ln" style="left:209.6pt;top:595.9pt;width:1pt;height:21.5pt"></div>
<div class="ln" style="left:48.6pt;top:617.4pt;width:514.8pt;height:1pt"></div>
<div class="ln" style="left:209.6pt;top:618.3pt;width:1pt;height:21.5pt"></div>
<div class="ln" style="left:48.6pt;top:639.8pt;width:514.8pt;height:1pt"></div>
<div class="ln" style="left:209.6pt;top:640.8pt;width:1pt;height:21.5pt"></div>
<div class="ln" style="left:48.6pt;top:662.3pt;width:514.8pt;height:1pt"></div>
<div class="ln" style="left:48.6pt;top:687pt;width:514.8pt;height:1pt"></div>
<div class="ln" style="left:209.6pt;top:687.9pt;width:1pt;height:73.2pt"></div>
<div class="ln" style="left:61.9pt;top:699.3pt;width:1pt;height:13.3pt"></div>
<div class="ln" style="left:62.9pt;top:699.3pt;width:29.5pt;height:1pt"></div>
<div class="ln" style="left:91.5pt;top:700.3pt;width:1pt;height:12.4pt"></div>
<div class="ln" style="left:62.9pt;top:711.7pt;width:29.5pt;height:1pt"></div>
<div class="ln" style="left:136.7pt;top:711.7pt;width:426.7pt;height:1pt"></div>
<div class="ln" style="left:61.9pt;top:736.4pt;width:1pt;height:13.3pt"></div>
<div class="ln" style="left:62.9pt;top:736.4pt;width:29.5pt;height:1pt"></div>
<div class="ln" style="left:136.7pt;top:736.4pt;width:426.7pt;height:1pt"></div>
<div class="ln" style="left:91.5pt;top:737.4pt;width:1pt;height:12.4pt"></div>
<div class="ln" style="left:62.9pt;top:748.8pt;width:29.5pt;height:1pt"></div>
<div class="ln" style="left:48.6pt;top:761.2pt;width:516.7pt;height:1.9pt"></div>
<div class="t" style="left:155.67pt;top:33.57pt;font-size:9.96pt;line-height:9.96pt;">MODEL</div>
<div class="t" style="left:196.56pt;top:33.57pt;font-size:9.96pt;line-height:9.96pt;">:</div>
<div class="t" style="left:332.84pt;top:33.57pt;font-size:9.96pt;line-height:9.96pt;">DATE</div>
<div class="t" style="left:362.24pt;top:33.57pt;font-size:9.96pt;line-height:9.96pt;">OF</div>
<div class="t" style="left:378.89pt;top:33.57pt;font-size:9.96pt;line-height:9.96pt;">ISSUED</div>
<div class="t" style="left:433.39pt;top:33.57pt;font-size:9.96pt;line-height:9.96pt;">:</div>
<div class="t b" style="left:67.94pt;top:34.29pt;font-size:27.96pt;line-height:27.96pt;">AAIJ</div>
<div class="t" style="left:332.84pt;top:52.53pt;font-size:9.96pt;line-height:9.96pt;">REF.</div>
<div class="t" style="left:358.28pt;top:52.53pt;font-size:9.96pt;line-height:9.96pt;">NO</div>
<div class="t" style="left:433.39pt;top:52.53pt;font-size:9.96pt;line-height:9.96pt;">:</div>
<div class="t" style="left:215.45pt;top:70.91pt;font-size:9.96pt;line-height:9.96pt;">SUPPLIER</div>
<div class="t" style="left:280.01pt;top:70.91pt;font-size:9.96pt;line-height:9.96pt;">MANUF.</div>
<div class="t" style="left:52.33pt;top:71.39pt;font-size:9.96pt;line-height:9.96pt;">PART</div>
<div class="t" style="left:81.72pt;top:71.39pt;font-size:9.96pt;line-height:9.96pt;">ROUTE</div>
<div class="t" style="left:347.95pt;top:71.39pt;font-size:9.96pt;line-height:9.96pt;">MATERIAL</div>
<div class="t" style="left:426.55pt;top:71.39pt;font-size:9.96pt;line-height:9.96pt;">GENERAL</div>
<div class="t" style="left:476.86pt;top:71.39pt;font-size:9.96pt;line-height:9.96pt;">TOL</div>
<div class="t" style="left:515.02pt;top:71.39pt;font-size:9.96pt;line-height:9.96pt;">WEIGHT</div>
<div class="t b" style="left:269.09pt;top:151.83pt;font-size:39.96pt;line-height:39.96pt;transform:scale(0.820,1);transform-origin:left top;">AAIJ</div>
<div class="t b" style="left:205.49pt;top:196.42pt;font-size:20.04pt;line-height:20.04pt;transform:scale(0.819,1);transform-origin:left top;">INSPECTION</div>
<div class="t b" style="left:315.05pt;top:196.42pt;font-size:20.04pt;line-height:20.04pt;transform:scale(0.831,1);transform-origin:left top;">STANDARD</div>
<div class="t b" style="left:228.65pt;top:219.38pt;font-size:14.04pt;line-height:14.04pt;transform:scale(0.820,1);transform-origin:left top;">(</div>
<div class="t b" style="left:235.71pt;top:219.38pt;font-size:14.04pt;line-height:14.04pt;transform:scale(0.822,1);transform-origin:left top;">FOR</div>
<div class="t b" style="left:266.5pt;top:219.38pt;font-size:14.04pt;line-height:14.04pt;transform:scale(0.818,1);transform-origin:left top;">PURCHASED</div>
<div class="t b" style="left:345.69pt;top:219.38pt;font-size:14.04pt;line-height:14.04pt;transform:scale(0.841,1);transform-origin:left top;">PART</div>
<div class="t b" style="left:380.18pt;top:219.38pt;font-size:14.04pt;line-height:14.04pt;transform:scale(0.820,1);transform-origin:left top;">)</div>
<div class="t" style="left:153.14pt;top:261.93pt;font-size:12pt;line-height:12pt;">PART</div>
<div class="t" style="left:191.97pt;top:261.93pt;font-size:12pt;line-height:12pt;">NO</div>
<div class="t" style="left:241.73pt;top:261.93pt;font-size:12pt;line-height:12pt;">:</div>
<div class="t" style="left:153.14pt;top:278.73pt;font-size:12pt;line-height:12pt;">PART</div>
<div class="t" style="left:191.97pt;top:278.73pt;font-size:12pt;line-height:12pt;">NAME</div>
<div class="t" style="left:241.73pt;top:278.73pt;font-size:12pt;line-height:12pt;">:</div>
<div class="t" style="left:388.99pt;top:368.51pt;font-size:9pt;line-height:9pt;">Customer</div>
<div class="t" style="left:430.7pt;top:368.51pt;font-size:9pt;line-height:9pt;">:</div>
<div class="t" style="left:49.44pt;top:368.99pt;font-size:9pt;line-height:9pt;">Manufacturer</div>
<div class="t" style="left:105.03pt;top:368.99pt;font-size:9pt;line-height:9pt;">:</div>
<div class="t" style="left:182.3pt;top:368.99pt;font-size:9pt;line-height:9pt;">Supplier</div>
<div class="t" style="left:217.99pt;top:368.99pt;font-size:9pt;line-height:9pt;">:</div>
<div class="t" style="left:50.88pt;top:381.35pt;font-size:9pt;line-height:9pt;">Approved</div>
<div class="t" style="left:96.62pt;top:381.35pt;font-size:9pt;line-height:9pt;">Checked</div>
<div class="t" style="left:145.46pt;top:381.35pt;font-size:9pt;line-height:9pt;">Issued</div>
<div class="t" style="left:183.74pt;top:381.35pt;font-size:9pt;line-height:9pt;">Approved</div>
<div class="t" style="left:229.49pt;top:381.35pt;font-size:9pt;line-height:9pt;">Checked</div>
<div class="t" style="left:278.33pt;top:381.35pt;font-size:9pt;line-height:9pt;">Issued</div>
<div class="t" style="left:397.87pt;top:381.35pt;font-size:9pt;line-height:9pt;">Approved</div>
<div class="t" style="left:458.38pt;top:381.35pt;font-size:9pt;line-height:9pt;">Checked</div>
<div class="t" style="left:516.46pt;top:381.35pt;font-size:9pt;line-height:9pt;">Received</div>

{{-- QR Code E-Signatures for AAIJ (Page 1) --}}
@php
    // Data signature dari requestDocument
    $approverSig = $requestDocument->approverEsignature ?? null;
    $checkerSig = $requestDocument->checkerEsignature ?? null;
    $receiverSig = $requestDocument->receiverEsignature ?? null;
@endphp

@if(!empty($signatureQRCodes['approver']))
<div style="position:absolute; left:390pt; top:380pt; width:50pt; height:65pt; text-align:center; border:0.5pt solid #ccc; padding:2pt;">
    <img src="{{ $signatureQRCodes['approver'] }}" style="width:40pt; height:40pt; display:block; margin:0 auto;" alt="Approver QR">
    <div style="font-size:6pt; margin-top:2pt; line-height:7pt; max-width:48pt; white-space:normal; word-wrap:break-word;">{{ $approverSig?->name ?? 'Approver' }}</div>
    <div style="font-size:5pt; line-height:6pt;">{{ $approverSig?->signed_at?->format('d/m/Y') ?? '' }}</div>
</div>
@endif

@if(!empty($signatureQRCodes['checker']))
<div style="position:absolute; left:450pt; top:380pt; width:50pt; height:65pt; text-align:center; border:0.5pt solid #ccc; padding:2pt;">
    <img src="{{ $signatureQRCodes['checker'] }}" style="width:40pt; height:40pt; display:block; margin:0 auto;" alt="Checker QR">
    <div style="font-size:6pt; margin-top:2pt; line-height:7pt; max-width:48pt; white-space:normal; word-wrap:break-word;">{{ $checkerSig?->name ?? 'Checker' }}</div>
    <div style="font-size:5pt; line-height:6pt;">{{ $checkerSig?->signed_at?->format('d/m/Y') ?? '' }}</div>
</div>
@endif

@if(!empty($signatureQRCodes['receiver']))
<div style="position:absolute; left:510pt; top:380pt; width:50pt; height:65pt; text-align:center; border:0.5pt solid #ccc; padding:2pt;">
    <img src="{{ $signatureQRCodes['receiver'] }}" style="width:40pt; height:40pt; display:block; margin:0 auto;" alt="Receiver QR">
    <div style="font-size:6pt; margin-top:2pt; line-height:7pt; max-width:48pt; white-space:normal; word-wrap:break-word;">{{ $receiverSig?->name ?? 'Receiver' }}</div>
    <div style="font-size:5pt; line-height:6pt;">{{ $receiverSig?->signed_at?->format('d/m/Y') ?? '' }}</div>
</div>
@endif

<div class="t" style="left:52.33pt;top:442.72pt;font-size:9.96pt;line-height:9.96pt;">REVISION</div>
<div class="t" style="left:105.37pt;top:442.72pt;font-size:9.96pt;line-height:9.96pt;">COLUMN</div>
<div class="t" style="left:450.34pt;top:457.59pt;font-size:6pt;line-height:6pt;">PCR/</div>
<div class="t" style="left:52.08pt;top:458.62pt;font-size:8.04pt;line-height:8.04pt;">REVISION</div>
<div class="t" style="left:92.5pt;top:458.62pt;font-size:8.04pt;line-height:8.04pt;">REQUEST</div>
<div class="t" style="left:479.86pt;top:458.62pt;font-size:8.04pt;line-height:8.04pt;">REVISION</div>
<div class="t" style="left:520.28pt;top:458.62pt;font-size:8.04pt;line-height:8.04pt;">REQUEST</div>
<div class="t" style="left:138.86pt;top:460.95pt;font-size:6pt;line-height:6pt;">REVISE</div>
<div class="t" style="left:177.62pt;top:462.7pt;font-size:8.04pt;line-height:8.04pt;">DATE</div>
<div class="t" style="left:285.17pt;top:462.25pt;font-size:9pt;line-height:9pt;">REVISION</div>
<div class="t" style="left:333.15pt;top:462.25pt;font-size:9pt;line-height:9pt;">RECORD</div>
<div class="t" style="left:450.34pt;top:464.43pt;font-size:6pt;line-height:6pt;">ECR</div>
<div class="t" style="left:141.26pt;top:467.79pt;font-size:6pt;line-height:6pt;">MARK</div>
<div class="t" style="left:76.34pt;top:467.74pt;font-size:8.04pt;line-height:8.04pt;">AAIJ</div>
<div class="t" style="left:95.4pt;top:467.74pt;font-size:8.04pt;line-height:8.04pt;">QA</div>
<div class="t" style="left:499.54pt;top:467.74pt;font-size:8.04pt;line-height:8.04pt;">SUPPLIER</div>
<div class="t" style="left:450.34pt;top:471.39pt;font-size:6pt;line-height:6pt;">NO.</div>
<div class="t" style="left:55.08pt;top:664.48pt;font-size:9pt;line-height:9pt;">Div.</div>
<div class="t" style="left:140.52pt;top:664.48pt;font-size:9pt;line-height:9pt;">DISTRIBUTE</div>
<div class="t" style="left:196.33pt;top:664.48pt;font-size:9pt;line-height:9pt;">TO</div>
<div class="t" style="left:211.21pt;top:664.48pt;font-size:9pt;line-height:9pt;">:</div>
<div class="t" style="left:344.71pt;top:664.48pt;font-size:9pt;line-height:9pt;">DIVISION</div>
<div class="t" style="left:386.69pt;top:664.48pt;font-size:9pt;line-height:9pt;">:</div>
<div class="t" style="left:481.18pt;top:664.48pt;font-size:9pt;line-height:9pt;">Gen.</div>
<div class="t" style="left:83.78pt;top:669.88pt;font-size:9pt;line-height:9pt;">Mgr.</div>
<div class="t" style="left:111.5pt;top:670.48pt;font-size:9pt;line-height:9pt;">Spv.</div>
<div class="t" style="left:511.9pt;top:669.88pt;font-size:9pt;line-height:9pt;">Mgr.</div>
<div class="t" style="left:539.74pt;top:669.67pt;font-size:9.96pt;line-height:9.96pt;">Staff</div>
<div class="t" style="left:53.88pt;top:676.84pt;font-size:9pt;line-height:9pt;">Mgr.</div>
<div class="t" style="left:482.02pt;top:676.84pt;font-size:9pt;line-height:9pt;">Mgr.</div>
<div class="t" style="left:153.98pt;top:694.84pt;font-size:9pt;line-height:9pt;">Customer</div>
<div class="t" style="left:211.97pt;top:694.38pt;font-size:9.96pt;line-height:9.96pt;">AAIJ</div>
<div class="t" style="left:235.73pt;top:694.38pt;font-size:9.96pt;line-height:9.96pt;">(PURCHASING</div>
<div class="t" style="left:308.19pt;top:694.38pt;font-size:9.96pt;line-height:9.96pt;">&amp;</div>
<div class="t" style="left:317.56pt;top:694.38pt;font-size:9.96pt;line-height:9.96pt;">QUALITY</div>
<div class="t" style="left:362.9pt;top:694.38pt;font-size:9.96pt;line-height:9.96pt;">DIVISION)</div>
<div class="t" style="left:99.4pt;top:701.1pt;font-size:9.96pt;line-height:9.96pt;">DRAFT</div>
<div class="t" style="left:156.98pt;top:719.56pt;font-size:9pt;line-height:9pt;">Supplier</div>
<div class="t" style="left:99.4pt;top:738.18pt;font-size:9.96pt;line-height:9.96pt;">FINAL</div>
<div class="t" style="left:147.02pt;top:744.54pt;font-size:9pt;line-height:9pt;">Manufacturer</div>
<div class="cell" style="left:204pt;top:34.1pt;width:122pt;height:10pt;font-size:10pt;line-height:10pt;text-align:left;padding:0 2pt;white-space:nowrap;">{{ $generalInformation['model'] ?? '' }}</div>
<div class="cell" style="left:441pt;top:34.1pt;width:120pt;height:10pt;font-size:10pt;line-height:10pt;text-align:left;padding:0 2pt;white-space:nowrap;">{{ $generalInformation['dateIssued'] ?? '' }}</div>
<div class="cell" style="left:441pt;top:53.1pt;width:120pt;height:10pt;font-size:10pt;line-height:10pt;text-align:left;padding:0 2pt;white-space:nowrap;">{{ $generalInformation['refNo'] ?? '' }}</div>
<div class="cell" style="left:48.6pt;top:86.2pt;width:161pt;height:36.5pt;font-size:9pt;line-height:36.5pt;text-align:center;padding:0 2pt;white-space:nowrap;">{{ $generalInformation['partRoute'] ?? '' }}</div>
<div class="cell" style="left:210.5pt;top:86.2pt;width:58.1pt;height:36.5pt;font-size:9pt;line-height:36.5pt;text-align:center;padding:0 2pt;white-space:nowrap;">{{ $supplierInfo['supplier'] ?? '' }}</div>
<div class="cell" style="left:269.6pt;top:86.2pt;width:58.1pt;height:36.5pt;font-size:9pt;line-height:36.5pt;text-align:center;padding:0 2pt;white-space:nowrap;">{{ $generalInformation['manufacturer'] ?? '' }}</div>
<div class="cell" style="left:328.6pt;top:86.2pt;width:87.6pt;height:36.5pt;font-size:9pt;line-height:36.5pt;text-align:center;padding:0 2pt;white-space:nowrap;">{{ $generalInformation['material'] ?? '' }}</div>
<div class="cell" style="left:417.2pt;top:86.2pt;width:87.6pt;height:36.5pt;font-size:9pt;line-height:36.5pt;text-align:center;padding:0 2pt;white-space:nowrap;">{{ $generalInformation['generalTolerance'] ?? '' }}</div>
<div class="cell" style="left:505.8pt;top:86.2pt;width:57.6pt;height:36.5pt;font-size:9pt;line-height:36.5pt;text-align:center;padding:0 2pt;white-space:nowrap;">{{ $generalInformation['weight'] ?? '' }}</div>
<div class="cell" style="left:257pt;top:262.4pt;width:188pt;height:13.5pt;font-size:11pt;line-height:13.5pt;text-align:left;padding:0 3pt;white-space:nowrap;">{{ $generalInformation['partNo'] ?? '' }}</div>
<div class="cell" style="left:257pt;top:279.2pt;width:188pt;height:13.5pt;font-size:11pt;line-height:13.5pt;text-align:left;padding:0 3pt;white-space:nowrap;">{{ $generalInformation['partName'] ?? '' }}</div>
@php
    // Baris REVISION COLUMN (8 baris). Isi lewat $revisions = [['date'=>..,'record'=>..], ...]
    // atau (kompatibel lama) $generalInformation['revisionDate'] / ['revisionRecord'] untuk baris 1.
    $revRows = [[480.2,25],[506.2,21.4],[528.6,21.4],[551,21.5],[573.5,21.4],[595.9,21.5],[618.4,21.4],[640.8,21.4]];
    $revisions = $revisions ?? [];
    if (empty($revisions) && (!empty($generalInformation['revisionDate']) || !empty($generalInformation['revisionRecord']))) {
        $revisions = [['date' => $generalInformation['revisionDate'] ?? '', 'record' => $generalInformation['revisionRecord'] ?? '']];
    }
@endphp
@foreach($revRows as $ri => $rr)
<div class="cell" style="left:165.3pt;top:{{ $rr[0] }}pt;width:44.3pt;height:{{ $rr[1] }}pt;font-size:8pt;line-height:{{ $rr[1] }}pt;text-align:center;padding:0 2pt;white-space:nowrap;">{{ $revisions[$ri]['date'] ?? '' }}</div>
<div class="cell" style="left:211pt;top:{{ $rr[0] }}pt;width:234pt;height:{{ $rr[1] }}pt;font-size:8pt;line-height:{{ $rr[1] }}pt;text-align:left;padding:0 3pt;white-space:nowrap;">{{ $revisions[$ri]['record'] ?? '' }}</div>
@endforeach
<div class="cell" style="left:212pt;top:687.9pt;width:350pt;height:23.8pt;font-size:10pt;line-height:23.8pt;text-align:left;padding:0 2pt;white-space:nowrap;">{{ $supplierInfo['customer'] ?? 'AAIJ (PURCHASING & QUALITY DIVISION)' }}</div>
<div class="cell" style="left:212pt;top:712.7pt;width:350pt;height:23.7pt;font-size:10pt;line-height:23.7pt;text-align:left;padding:0 2pt;white-space:nowrap;">{{ $supplierInfo['supplier'] ?? '' }}</div>
<div class="cell" style="left:212pt;top:737.4pt;width:350pt;height:23.8pt;font-size:10pt;line-height:23.8pt;text-align:left;padding:0 2pt;white-space:nowrap;">{{ $supplierInfo['manufacturer'] ?? '' }}</div>
</div>

{{-- ========== HALAMAN 2 : PART DRAWING ========== --}}
<div class="page" style="page-break-after: always;">
<div class="ln" style="left:46.7pt;top:20pt;width:1.9pt;height:751pt"></div>
<div class="ln" style="left:48.6pt;top:20pt;width:516.7pt;height:1.9pt"></div>
<div class="ln" style="left:180pt;top:22pt;width:1pt;height:43.3pt"></div>
<div class="ln" style="left:371.9pt;top:22pt;width:1pt;height:43.3pt"></div>
<div class="ln" style="left:563.4pt;top:22pt;width:1.9pt;height:749pt"></div>
<div class="ln" style="left:48.6pt;top:64.3pt;width:514.8pt;height:1pt"></div>
<div class="ln" style="left:48.6pt;top:676.7pt;width:514.8pt;height:1pt"></div>
<div class="ln" style="left:135.7pt;top:677.6pt;width:1pt;height:91.5pt"></div>
<div class="ln" style="left:224.3pt;top:677.6pt;width:1pt;height:91.5pt"></div>
<div class="ln" style="left:431pt;top:677.6pt;width:1pt;height:91.5pt"></div>
<div class="ln" style="left:48.6pt;top:694.9pt;width:514.8pt;height:1pt"></div>
<div class="ln" style="left:91.5pt;top:695.9pt;width:1pt;height:73.2pt"></div>
<div class="ln" style="left:180pt;top:695.9pt;width:1pt;height:73.2pt"></div>
<div class="ln" style="left:475.3pt;top:695.9pt;width:1pt;height:73.2pt"></div>
<div class="ln" style="left:519.6pt;top:695.9pt;width:1pt;height:73.2pt"></div>
<div class="ln" style="left:225.3pt;top:719.6pt;width:206.7pt;height:1pt"></div>
<div class="ln" style="left:48.6pt;top:744.3pt;width:514.8pt;height:1pt"></div>
<div class="ln" style="left:48.6pt;top:769.1pt;width:516.7pt;height:1.9pt"></div>
<div class="t" style="left:185.19pt;top:23.49pt;font-size:9.96pt;line-height:9.96pt;">MODEL</div>
<div class="t" style="left:226.73pt;top:23.49pt;font-size:9.96pt;line-height:9.96pt;">:</div>
<div class="t" style="left:377.12pt;top:23.49pt;font-size:9.96pt;line-height:9.96pt;">PART</div>
<div class="t" style="left:409.28pt;top:23.49pt;font-size:9.96pt;line-height:9.96pt;">NO</div>
<div class="t" style="left:427.02pt;top:23.49pt;font-size:9.96pt;line-height:9.96pt;">:</div>
<div class="t b" style="left:73.46pt;top:24.97pt;font-size:36pt;line-height:36pt;">AAIJ</div>
<div class="t b" style="left:216.53pt;top:51.52pt;font-size:12pt;line-height:12pt;transform:scale(0.821,1);transform-origin:left top;">INSPECTION</div>
<div class="t b" style="left:282.24pt;top:51.52pt;font-size:12pt;line-height:12pt;transform:scale(0.830,1);transform-origin:left top;">STANDARD</div>
<div class="t" style="left:64.34pt;top:85.07pt;font-size:9.96pt;line-height:9.96pt;">PART</div>
<div class="t" style="left:93.75pt;top:85.07pt;font-size:9.96pt;line-height:9.96pt;">DRAWING</div>
<div class="t" style="left:145.21pt;top:85.07pt;font-size:9.96pt;line-height:9.96pt;">:</div>
<div class="t b" style="left:54.24pt;top:681.4pt;font-size:9pt;line-height:9pt;">MANUFACTURER</div>
<div class="t b" style="left:158.42pt;top:681.4pt;font-size:9pt;line-height:9pt;">SUPPLIER</div>
<div class="t" style="left:234.14pt;top:681.76pt;font-size:9pt;line-height:9pt;">DISTRIBUTE</div>
<div class="t" style="left:289.95pt;top:681.76pt;font-size:9pt;line-height:9pt;">TO</div>
<div class="t" style="left:304.83pt;top:681.76pt;font-size:9pt;line-height:9pt;">:</div>
<div class="t" style="left:309.86pt;top:681.76pt;font-size:9pt;line-height:9pt;">DIVISION</div>
<div class="t b" style="left:477.58pt;top:681.4pt;font-size:9pt;line-height:9pt;">AAIJ</div>
<div class="t b" style="left:500.36pt;top:681.4pt;font-size:9pt;line-height:9pt;">-</div>
<div class="t b" style="left:505.88pt;top:681.4pt;font-size:9pt;line-height:9pt;">QA</div>
<div class="t" style="left:226.61pt;top:702.76pt;font-size:9pt;line-height:9pt;">Customer</div>
<div class="t" style="left:285.65pt;top:702.76pt;font-size:9pt;line-height:9pt;">:</div>
<div class="t" style="left:290.68pt;top:702.76pt;font-size:9pt;line-height:9pt;">AAIJ</div>
<div class="t" style="left:312.25pt;top:702.76pt;font-size:9pt;line-height:9pt;">(PUR</div>
<div class="t" style="left:336.75pt;top:702.76pt;font-size:9pt;line-height:9pt;">&amp;</div>
<div class="t" style="left:345.26pt;top:702.76pt;font-size:9pt;line-height:9pt;">QUALITY</div>
<div class="t" style="left:386.04pt;top:702.76pt;font-size:9pt;line-height:9pt;">DIV)</div>
<div class="t" style="left:226.61pt;top:727.48pt;font-size:9pt;line-height:9pt;">Supplier</div>
<div class="t" style="left:285.65pt;top:727.48pt;font-size:9pt;line-height:9pt;">:</div>
<div class="t" style="left:50.88pt;top:752.46pt;font-size:9pt;line-height:9pt;">Approved</div>
<div class="t" style="left:101.18pt;top:752.46pt;font-size:9pt;line-height:9pt;">Issued</div>
<div class="t" style="left:139.46pt;top:752.46pt;font-size:9pt;line-height:9pt;">Approved</div>
<div class="t" style="left:189.74pt;top:752.46pt;font-size:9pt;line-height:9pt;">Issued</div>
<div class="t" style="left:226.61pt;top:752.46pt;font-size:9pt;line-height:9pt;">Manufacturer</div>
<div class="t" style="left:285.65pt;top:752.46pt;font-size:9pt;line-height:9pt;">:</div>
<div class="t" style="left:434.71pt;top:752.46pt;font-size:9pt;line-height:9pt;">Approved</div>
<div class="t" style="left:480.46pt;top:752.46pt;font-size:9pt;line-height:9pt;">Checked</div>
<div class="t" style="left:523.78pt;top:752.46pt;font-size:9pt;line-height:9pt;">Received</div>

{{-- QR Code E-Signatures for AAIJ (Page 2) --}}
@if(!empty($signatureQRCodes['approver']))
<div style="position:absolute; left:420pt; top:705pt; width:50pt; height:65pt; text-align:center; border:0.5pt solid #ccc; padding:2pt;">
    <img src="{{ $signatureQRCodes['approver'] }}" style="width:40pt; height:40pt; display:block; margin:0 auto;" alt="Approver QR">
    <div style="font-size:6pt; margin-top:2pt; line-height:7pt; max-width:48pt; white-space:normal; word-wrap:break-word;">{{ $approverSig?->name ?? 'Approver' }}</div>
    <div style="font-size:5pt; line-height:6pt;">{{ $approverSig?->signed_at?->format('d/m/Y') ?? '' }}</div>
</div>
@endif

@if(!empty($signatureQRCodes['checker']))
<div style="position:absolute; left:468pt; top:705pt; width:50pt; height:65pt; text-align:center; border:0.5pt solid #ccc; padding:2pt;">
    <img src="{{ $signatureQRCodes['checker'] }}" style="width:40pt; height:40pt; display:block; margin:0 auto;" alt="Checker QR">
    <div style="font-size:6pt; margin-top:2pt; line-height:7pt; max-width:48pt; white-space:normal; word-wrap:break-word;">{{ $checkerSig?->name ?? 'Checker' }}</div>
    <div style="font-size:5pt; line-height:6pt;">{{ $checkerSig?->signed_at?->format('d/m/Y') ?? '' }}</div>
</div>
@endif

@if(!empty($signatureQRCodes['receiver']))
<div style="position:absolute; left:512pt; top:705pt; width:50pt; height:65pt; text-align:center; border:0.5pt solid #ccc; padding:2pt;">
    <img src="{{ $signatureQRCodes['receiver'] }}" style="width:40pt; height:40pt; display:block; margin:0 auto;" alt="Receiver QR">
    <div style="font-size:6pt; margin-top:2pt; line-height:7pt; max-width:48pt; white-space:normal; word-wrap:break-word;">{{ $receiverSig?->name ?? 'Receiver' }}</div>
    <div style="font-size:5pt; line-height:6pt;">{{ $receiverSig?->signed_at?->format('d/m/Y') ?? '' }}</div>
</div>
@endif

<div class="cell" style="left:233pt;top:24.1pt;width:136pt;height:10pt;font-size:10pt;line-height:10pt;text-align:left;padding:0 2pt;white-space:nowrap;">{{ $generalInformation['model'] ?? '' }}</div>
<div class="cell" style="left:433pt;top:24.1pt;width:130pt;height:10pt;font-size:10pt;line-height:10pt;text-align:left;padding:0 2pt;white-space:nowrap;">{{ $generalInformation['partNo'] ?? '' }}</div>
@php $drawList = $drawings ?? []; $dh = count($drawList) > 0 ? floor(520 / count($drawList)) - 36 : 0; @endphp
<div class="cell" style="left:60pt;top:104pt;width:492pt;height:566pt;">
@foreach($drawList as $index => $drawing)
    <div style="margin-bottom:6pt;">
        <div class="dinfo b">Drawing {{ $index + 1 }}{{ !empty($drawing['drawingName']) ? ': '.$drawing['drawingName'] : '' }}</div>
        @php
            $imageData = null;
            if (!empty($drawing['filePath']) && file_exists($drawing['filePath'])) {
                $fileType = $drawing['fileType'] ?? 'png';
                if (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $imageContents = @file_get_contents($drawing['filePath']);
                    if ($imageContents !== false) {
                        $mimeType = match($fileType) {
                            'jpg', 'jpeg' => 'image/jpeg',
                            'png' => 'image/png',
                            'gif' => 'image/gif',
                            default => 'image/png'
                        };
                        $imageData = 'data:' . $mimeType . ';base64,' . base64_encode($imageContents);
                    }
                }
            }
        @endphp
        @if($imageData)
            <img src="{{ $imageData }}" class="dimg" style="max-width:492pt; max-height:{{ $dh }}pt;" alt="Drawing {{ $index + 1 }}">
        @elseif(($drawing['fileType'] ?? '') === 'pdf')
            <div class="dinfo" style="text-align:center; padding:8pt; border:1px dashed #000; margin:4pt 0;">[PDF Document: {{ $drawing['originalFilename'] ?? '' }}]</div>
        @else
            <div class="dinfo" style="text-align:center; padding:8pt; color:#999;">[Drawing tidak tersedia]</div>
        @endif
        @if(!empty($drawing['drawingNo']))<div class="dinfo"><span class="dlbl">Drawing No:</span>{{ $drawing['drawingNo'] }}</div>@endif
        @if(!empty($drawing['revision']))<div class="dinfo"><span class="dlbl">Revision:</span>{{ $drawing['revision'] }}</div>@endif
        @if(!empty($drawing['description']))<div class="dinfo"><span class="dlbl">Description:</span>{{ $drawing['description'] }}</div>@endif
    </div>
@endforeach
</div>
</div>

{{-- ========== HALAMAN 3+ : INSPECTION ITEMS (lanjut otomatis tiap 8 item) ========== --}}
{{-- Otomatis lanjut halaman baru setiap 8 item --}}
@php
    $chunks = array_chunk(array_values($inspectionItems ?? []), 8);
    if (empty($chunks)) { $chunks = [[]]; }
    // [top, tinggi] 8 baris  (diukur dari garis horizontal PDF)
    $rows = [[106.7,69.2],[176.9,83.3],[261.2,69.2],[331.4,55.2],[387.5,55.3],[443.7,55.2],[499.9,83.3],[584.1,80]];
    // [left, lebar, align, key]  (7 kolom)
    $cols = [[47.5,131.5,'left','inspectionItem'],[179.9,54.4,'left','inspectionInstrument'],[235.2,66,'left','inspectionCriterion'],
             [302.2,26.7,'center','rank'],[329.8,43.3,'center','supplierSampling'],[374.1,43.3,'center','aaijSampling'],
             [418.4,146.2,'left','remarkInspectionMethod']];
@endphp
@foreach($chunks as $chunk)
<div class="page"@if(!$loop->last) style="page-break-after: always;"@endif>
<div class="ln" style="left:45.6pt;top:32.8pt;width:1.9pt;height:725.6pt"></div>
<div class="ln" style="left:47.5pt;top:32.8pt;width:519pt;height:1.9pt"></div>
<div class="ln" style="left:178.9pt;top:34.7pt;width:1pt;height:42.5pt"></div>
<div class="ln" style="left:373.1pt;top:34.7pt;width:1pt;height:42.5pt"></div>
<div class="ln" style="left:564.6pt;top:34.7pt;width:1.9pt;height:723.7pt"></div>
<div class="ln" style="left:47.5pt;top:77.2pt;width:519pt;height:1.9pt"></div>
<div class="ln" style="left:178.9pt;top:79.1pt;width:1pt;height:585.9pt"></div>
<div class="ln" style="left:234.3pt;top:79.1pt;width:1pt;height:585.9pt"></div>
<div class="ln" style="left:301.2pt;top:79.1pt;width:1pt;height:585.9pt"></div>
<div class="ln" style="left:328.9pt;top:79.1pt;width:1pt;height:585.9pt"></div>
<div class="ln" style="left:373.1pt;top:79.1pt;width:1pt;height:585.9pt"></div>
<div class="ln" style="left:417.4pt;top:79.1pt;width:1pt;height:585.9pt"></div>
<div class="ln" style="left:47.5pt;top:105.7pt;width:517.1pt;height:1pt"></div>
<div class="ln" style="left:47.5pt;top:175.9pt;width:517.1pt;height:1pt"></div>
<div class="ln" style="left:47.5pt;top:260.2pt;width:517.1pt;height:1pt"></div>
<div class="ln" style="left:47.5pt;top:330.4pt;width:517.1pt;height:1pt"></div>
<div class="ln" style="left:47.5pt;top:386.6pt;width:517.1pt;height:1pt"></div>
<div class="ln" style="left:47.5pt;top:442.8pt;width:517.1pt;height:1pt"></div>
<div class="ln" style="left:47.5pt;top:498.9pt;width:517.1pt;height:1pt"></div>
<div class="ln" style="left:47.5pt;top:583.2pt;width:517.1pt;height:1pt"></div>
<div class="ln" style="left:47.5pt;top:664.1pt;width:517.1pt;height:1pt"></div>
<div class="ln" style="left:134.7pt;top:665pt;width:1pt;height:91.5pt"></div>
<div class="ln" style="left:223.2pt;top:665pt;width:1pt;height:91.5pt"></div>
<div class="ln" style="left:432.2pt;top:665pt;width:1pt;height:91.5pt"></div>
<div class="ln" style="left:47.5pt;top:682.3pt;width:517.1pt;height:1pt"></div>
<div class="ln" style="left:90.4pt;top:683.3pt;width:1pt;height:73.2pt"></div>
<div class="ln" style="left:178.9pt;top:683.3pt;width:1pt;height:73.2pt"></div>
<div class="ln" style="left:476.5pt;top:683.3pt;width:1pt;height:73.2pt"></div>
<div class="ln" style="left:520.8pt;top:683.3pt;width:1pt;height:73.2pt"></div>
<div class="ln" style="left:224.2pt;top:707pt;width:208.9pt;height:1pt"></div>
<div class="ln" style="left:47.5pt;top:731.7pt;width:517.1pt;height:1pt"></div>
<div class="ln" style="left:47.5pt;top:756.5pt;width:519pt;height:1.9pt"></div>
<div class="t" style="left:184.11pt;top:36.21pt;font-size:9.96pt;line-height:9.96pt;">MODEL</div>
<div class="t" style="left:225.65pt;top:36.21pt;font-size:9.96pt;line-height:9.96pt;">:</div>
<div class="t" style="left:378.32pt;top:36.21pt;font-size:9.96pt;line-height:9.96pt;">PART</div>
<div class="t" style="left:410.48pt;top:36.21pt;font-size:9.96pt;line-height:9.96pt;">NO</div>
<div class="t" style="left:428.22pt;top:36.21pt;font-size:9.96pt;line-height:9.96pt;">:</div>
<div class="t b" style="left:72.38pt;top:38.05pt;font-size:36pt;line-height:36pt;">AAIJ</div>
<div class="t b" style="left:216.53pt;top:64.12pt;font-size:12pt;line-height:12pt;transform:scale(0.821,1);transform-origin:left top;">INSPECTION</div>
<div class="t b" style="left:282.24pt;top:64.12pt;font-size:12pt;line-height:12pt;transform:scale(0.830,1);transform-origin:left top;">STANDARD</div>
<div class="t" style="left:181.82pt;top:80.69pt;font-size:8.04pt;line-height:8.04pt;">INSPECTION</div>
<div class="t" style="left:242.93pt;top:81.17pt;font-size:8.04pt;line-height:8.04pt;">INSPECTION</div>
<div class="t" style="left:331.99pt;top:80.69pt;font-size:8.04pt;line-height:8.04pt;">SUPPLIER</div>
<div class="t" style="left:387.67pt;top:80.69pt;font-size:8.04pt;line-height:8.04pt;">AAIJ</div>
<div class="t" style="left:475.06pt;top:80.69pt;font-size:8.04pt;line-height:8.04pt;">REMARK</div>
<div class="t" style="left:76.94pt;top:87.65pt;font-size:8.04pt;line-height:8.04pt;">INSPECTION</div>
<div class="t" style="left:130.67pt;top:87.65pt;font-size:8.04pt;line-height:8.04pt;">ITEM</div>
<div class="t" style="left:304.85pt;top:87.65pt;font-size:8.04pt;line-height:8.04pt;">RANK</div>
<div class="t" style="left:181.22pt;top:94.73pt;font-size:8.04pt;line-height:8.04pt;">INSTRUMENT</div>
<div class="t" style="left:246.65pt;top:95.21pt;font-size:8.04pt;line-height:8.04pt;">CRITERION</div>
<div class="t" style="left:331.15pt;top:94.73pt;font-size:8.04pt;line-height:8.04pt;">SAMPLING</div>
<div class="t" style="left:375.43pt;top:94.73pt;font-size:8.04pt;line-height:8.04pt;">SAMPLING</div>
<div class="t" style="left:442.99pt;top:94.73pt;font-size:8.04pt;line-height:8.04pt;">(</div>
<div class="t" style="left:447.9pt;top:94.73pt;font-size:8.04pt;line-height:8.04pt;">INSPECTION</div>
<div class="t" style="left:501.63pt;top:94.73pt;font-size:8.04pt;line-height:8.04pt;">METHOD</div>
<div class="t" style="left:538.58pt;top:94.73pt;font-size:8.04pt;line-height:8.04pt;">)</div>
<div class="t b" style="left:53.16pt;top:668.8pt;font-size:9pt;line-height:9pt;">MANUFACTURER</div>
<div class="t b" style="left:157.34pt;top:668.8pt;font-size:9pt;line-height:9pt;">SUPPLIER</div>
<div class="t b" style="left:481.54pt;top:668.8pt;font-size:9pt;line-height:9pt;">AAIJ</div>
<div class="t b" style="left:504.32pt;top:668.8pt;font-size:9pt;line-height:9pt;">QA</div>
<div class="t" style="left:233.06pt;top:669.16pt;font-size:9pt;line-height:9pt;">DISTRIBUTE</div>
<div class="t" style="left:288.87pt;top:669.16pt;font-size:9pt;line-height:9pt;">TO</div>
<div class="t" style="left:303.75pt;top:669.16pt;font-size:9pt;line-height:9pt;">:</div>
<div class="t" style="left:308.78pt;top:669.16pt;font-size:9pt;line-height:9pt;">DIVISION</div>
<div class="t" style="left:225.53pt;top:690.16pt;font-size:9pt;line-height:9pt;">Customer</div>
<div class="t" style="left:288.77pt;top:690.16pt;font-size:9pt;line-height:9pt;">:</div>
<div class="t" style="left:293.8pt;top:690.16pt;font-size:9pt;line-height:9pt;">AAIJ</div>
<div class="t" style="left:315.37pt;top:690.16pt;font-size:9pt;line-height:9pt;">(PUR</div>
<div class="t" style="left:339.87pt;top:690.16pt;font-size:9pt;line-height:9pt;">&amp;</div>
<div class="t" style="left:348.38pt;top:690.16pt;font-size:9pt;line-height:9pt;">QUALITY</div>
<div class="t" style="left:389.16pt;top:690.16pt;font-size:9pt;line-height:9pt;">DIV)</div>
<div class="t" style="left:225.53pt;top:714.88pt;font-size:9pt;line-height:9pt;">Supplier</div>
<div class="t" style="left:288.77pt;top:714.88pt;font-size:9pt;line-height:9pt;">:</div>
<div class="t" style="left:49.8pt;top:739.84pt;font-size:9pt;line-height:9pt;">Approved</div>
<div class="t" style="left:100.1pt;top:739.84pt;font-size:9pt;line-height:9pt;">Issued</div>
<div class="t" style="left:138.38pt;top:739.84pt;font-size:9pt;line-height:9pt;">Approved</div>
<div class="t" style="left:188.66pt;top:739.84pt;font-size:9pt;line-height:9pt;">Issued</div>
<div class="t" style="left:225.53pt;top:739.84pt;font-size:9pt;line-height:9pt;">Manufacturer</div>
<div class="t" style="left:288.77pt;top:739.84pt;font-size:9pt;line-height:9pt;">:</div>
<div class="t" style="left:435.91pt;top:739.84pt;font-size:9pt;line-height:9pt;">Approved</div>
<div class="t" style="left:481.66pt;top:739.84pt;font-size:9pt;line-height:9pt;">Checked</div>
<div class="t" style="left:524.98pt;top:739.84pt;font-size:9pt;line-height:9pt;">Received</div>

{{-- QR Code E-Signatures for AAIJ (Pages 3+) --}}
@if(!empty($signatureQRCodes['approver']))
<img src="{{ $signatureQRCodes['approver'] }}" style="position:absolute; left:433pt; top:708pt; width:38pt; height:38pt;" alt="Approver QR">
@endif
@if(!empty($signatureQRCodes['checker']))
<img src="{{ $signatureQRCodes['checker'] }}" style="position:absolute; left:478pt; top:708pt; width:38pt; height:38pt;" alt="Checker QR">
@endif
@if(!empty($signatureQRCodes['receiver']))
<img src="{{ $signatureQRCodes['receiver'] }}" style="position:absolute; left:522pt; top:708pt; width:38pt; height:38pt;" alt="Receiver QR">
@endif

<div class="cell" style="left:232pt;top:36.8pt;width:140pt;height:10pt;font-size:10pt;line-height:10pt;text-align:left;padding:0 2pt;white-space:nowrap;">{{ $generalInformation['model'] ?? '' }}</div>
<div class="cell" style="left:434pt;top:36.8pt;width:129pt;height:10pt;font-size:10pt;line-height:10pt;text-align:left;padding:0 2pt;white-space:nowrap;">{{ $generalInformation['partNo'] ?? '' }}</div>
@foreach($rows as $ri => $r)
    @foreach($cols as $c)
<div class="cell" style="left:{{ $c[0] }}pt;top:{{ $r[0] }}pt;width:{{ $c[1] }}pt;height:{{ $r[1] }}pt;font-size:8.5pt;line-height:10.2pt;text-align:{{ $c[2] }};padding:2.5pt 3pt 0 3pt;white-space:normal;">{{ $chunk[$ri][$c[3]] ?? '' }}</div>
    @endforeach
@endforeach
</div>
@endforeach
</body>
</html>
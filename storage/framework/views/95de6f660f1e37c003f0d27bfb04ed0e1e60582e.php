<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Packaging Standard - <?php echo e($formData['generalInformation']['partNo'] ?? 'N/A'); ?></title>
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
            font-family: 'Calibri', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #000 !important;
            background: white;
        }
        td, th, p, div, span {
            color: #000 !important;
        }
        .page {
            page-break-after: always;
            padding: 0;
            margin: 0;
            background: white;
            min-height: 277mm;
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
            border: 1px solid #333;
            padding: 4px 5px;
            vertical-align: top;
            font-size: 10px;
            line-height: 1.2;
        }
        .no-border { border: none !important; }
        .bold { font-weight: bold; }
        .center { text-align: center; }
        .left { text-align: left; }
        .right { text-align: right; }

        /* HEADER */
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 5px 0;
            padding: 0;
        }
        .header p {
            font-size: 11px;
            margin: 0;
            padding: 0;
        }

        /* SECTION TITLE */
        .section-title {
            font-weight: bold;
            font-size: 12px;
            background-color: #f0f0f0;
            padding: 6px 5px;
            margin-top: 8px;
            margin-bottom: 4px;
        }

        /* FORM TABLE */
        .form-table {
            width: 100%;
            margin-bottom: 8px;
        }
        .form-table td {
            padding: 6px 5px;
            font-size: 10px;
            border: 1px solid #999;
        }
        .form-table .label {
            width: 30%;
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .form-table .value {
            width: 70%;
        }

        /* APPROVAL TABLE */
        .approval-table {
            width: 100%;
            margin-bottom: 10px;
        }
        .approval-table th {
            text-align: center;
            background-color: #f0f0f0;
            font-weight: bold;
            padding: 8px 5px;
            font-size: 10px;
            border: 1px solid #333;
        }
        .approval-table td {
            text-align: center;
            padding: 8px 5px;
            font-size: 10px;
            border: 1px solid #333;
            min-height: 60px;
        }

        /* PAGE BREAK */
        .page-break {
            page-break-after: always;
        }

        /* STATUS INDICATOR */
        .approval-status {
            margin-top: 10px;
            padding: 8px;
            border: 2px solid #333;
            text-align: center;
            font-weight: bold;
        }

        /* IMAGE PLACEHOLDER */
        .image-placeholder {
            border: 1px dashed #999;
            padding: 20px;
            text-align: center;
            background-color: #f5f5f5;
            margin: 5px 0;
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- HEADER -->
        <div class="header">
            <h1>PACKAGING STANDARD</h1>
            <p>PT Akebono Manufacturing Indonesia</p>
        </div>

        <!-- STEP 1: GENERAL INFORMATION -->
        <div class="section-title">1. GENERAL INFORMATION (INFORMASI UMUM)</div>
        
        <table class="form-table">
            <tr>
                <td class="label">Document No.</td>
                <td class="value"><?php echo e($formData['generalInformation']['documentNo'] ?? '_______________'); ?></td>
                <td class="label">Revision</td>
                <td class="value"><?php echo e($formData['generalInformation']['revision'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">Date</td>
                <td class="value"><?php echo e($formData['generalInformation']['date'] ?? '_______________'); ?></td>
                <td class="label">Customer</td>
                <td class="value"><?php echo e($formData['generalInformation']['customer'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">Part No.</td>
                <td class="value"><?php echo e($formData['generalInformation']['partNo'] ?? '_______________'); ?></td>
                <td class="label">Part Name</td>
                <td class="value"><?php echo e($formData['generalInformation']['partName'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">Supplier Name</td>
                <td class="value"><?php echo e($formData['generalInformation']['supplier'] ?? '_______________'); ?></td>
                <td class="label">Qty</td>
                <td class="value"><?php echo e($formData['generalInformation']['qty'] ?? '_______________'); ?></td>
            </tr>
        </table>

        <!-- STEP 2: PACKAGING SPECIFICATIONS -->
        <div class="section-title">2. PACKAGING SPECIFICATIONS (SPESIFIKASI KEMASAN)</div>
        
        <table class="form-table">
            <tr>
                <td class="label">Packaging Type</td>
                <td class="value" colspan="3"><?php echo e($formData['specifications']['packagingType'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">Packaging Size (mm)</td>
                <td class="value" colspan="3"><?php echo e($formData['specifications']['packagingSizeMm'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">Packaging Lot (pcs/box)</td>
                <td class="value"><?php echo e($formData['specifications']['packagingLotPcsBox'] ?? '_______________'); ?></td>
                <td class="label">Net Weight (g/pc)</td>
                <td class="value"><?php echo e($formData['specifications']['netWeightGPc'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">Total Weight (kg/box)</td>
                <td class="value" colspan="3"><?php echo e($formData['specifications']['totalWeightKgBox'] ?? '_______________'); ?></td>
            </tr>
        </table>

        <!-- STEP 3: VISUALIZATION & IMAGES -->
        <div class="section-title">3. VISUALIZATION & IMAGES (VISUALISASI & GAMBAR)</div>

        <table class="form-table">
            <tr>
                <td class="label">Image - Photographic</td>
                <td class="value">
                    <?php if($ps->image_photographic): ?>
                        <img src="<?php echo e(asset('storage/' . $ps->image_photographic)); ?>" style="max-width: 100%; max-height: 100px;" alt="Photographic">
                    <?php else: ?>
                        <div class="image-placeholder">[Image not provided]</div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="label">Image - Part Identification</td>
                <td class="value">
                    <?php if($ps->image_part_identification): ?>
                        <img src="<?php echo e(asset('storage/' . $ps->image_part_identification)); ?>" style="max-width: 100%; max-height: 100px;" alt="Part Identification">
                    <?php else: ?>
                        <div class="image-placeholder">[Image not provided]</div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="label">Image - Visualisasi Packing</td>
                <td class="value">
                    <?php if($ps->image_visualisasi_packing): ?>
                        <img src="<?php echo e(asset('storage/' . $ps->image_visualisasi_packing)); ?>" style="max-width: 100%; max-height: 100px;" alt="Visualisasi Packing">
                    <?php else: ?>
                        <div class="image-placeholder">[Image not provided]</div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="label">Image - Label Packing</td>
                <td class="value">
                    <?php if($ps->image_label_packing): ?>
                        <img src="<?php echo e(asset('storage/' . $ps->image_label_packing)); ?>" style="max-width: 100%; max-height: 100px;" alt="Label Packing">
                    <?php else: ?>
                        <div class="image-placeholder">[Image not provided]</div>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <!-- PAGE BREAK -->
        <div class="page-break"></div>
    </div>

    <div class="page">
        <!-- APPROVAL SIGNATURES TABLE -->
        <div class="section-title">4. APPROVAL SIGNATURES</div>

        <table class="approval-table">
            <thead>
                <tr>
                    <th colspan="3" style="width: 33.33%;">PREPARED BY<br>(Supplier)</th>
                    <th colspan="3" style="width: 33.33%;">CHECKED BY<br>(QA/PPC)</th>
                    <th colspan="3" style="width: 33.33%;">APPROVED BY<br>(Final Approval)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3">
                        <strong>Name:</strong><br>
                        <?php echo e($formData['visualizationApproval']['preparedByName'] ?? '_______________'); ?><br><br>
                        <strong>Date:</strong><br>
                        <?php echo e($formData['visualizationApproval']['preparedByDate'] ?? '_______________'); ?>

                    </td>
                    <td colspan="3">
                        <strong>Name:</strong><br>
                        <?php echo e($formData['visualizationApproval']['checkedByName'] ?? '_______________'); ?><br><br>
                        <strong>Date:</strong><br>
                        <?php echo e($formData['visualizationApproval']['checkedByDate'] ?? '_______________'); ?>

                    </td>
                    <td colspan="3">
                        <strong>Name:</strong><br>
                        <?php echo e($formData['visualizationApproval']['approvedByName'] ?? '_______________'); ?><br><br>
                        <strong>Date:</strong><br>
                        <?php echo e($formData['visualizationApproval']['approvedByDate'] ?? '_______________'); ?>

                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="height: 80px;">
                        <strong>Signature</strong><br><br><br>
                        _________________________
                    </td>
                    <td colspan="3" style="height: 80px;">
                        <strong>Signature</strong><br><br><br>
                        _________________________
                    </td>
                    <td colspan="3" style="height: 80px;">
                        <strong>Signature</strong><br><br><br>
                        _________________________
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- REVISION HISTORY -->
        <?php if($ps->revision_history): ?>
        <div class="section-title">5. REVISION HISTORY</div>
        <table class="form-table">
            <tr>
                <td colspan="4" style="padding: 10px;">
                    <?php
                        $revisionHistory = json_decode($ps->revision_history, true) ?? [];
                        if (is_array($revisionHistory)) {
                            foreach ($revisionHistory as $revision) {
                                echo "Revision " . ($revision['revision'] ?? 'N/A') . ": " . ($revision['date'] ?? '') . " - " . ($revision['change'] ?? '') . "<br>";
                            }
                        }
                    ?>
                </td>
            </tr>
        </table>
        <?php endif; ?>

        <!-- STATUS & FOOTER -->
        <div class="approval-status">
            Status: <?php echo e(strtoupper($ps->status)); ?><br>
            Submitted: <?php echo e($ps->submitted_at?->format('Y-m-d H:i') ?? 'Not yet submitted'); ?>

        </div>

        <p style="text-align: center; margin-top: 20px; font-size: 9px;">
            This document is generated from the PT Akebono Approval System<br>
            Generated on: <?php echo e(now()->format('Y-m-d H:i:s')); ?>

        </p>
    </div>
</body>
</html>
<?php /**PATH E:\KERJAAN\akebono-approval\resources\views/packaging-standard/pdf/template.blade.php ENDPATH**/ ?>
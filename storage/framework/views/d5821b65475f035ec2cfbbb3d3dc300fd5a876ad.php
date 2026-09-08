<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Part Submission Warrant - <?php echo e($formData['generalInformation']['partNumber'] ?? 'N/A'); ?></title>
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
            font-family: 'Times New Roman', serif;
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

        /* CHECKBOX ITEMS */
        .checkbox-group {
            margin: 4px 0;
        }
        .checkbox-item {
            margin: 3px 0;
            font-size: 10px;
        }
        .checkbox-item:before {
            content: "☐ ";
            margin-right: 5px;
        }
        .checkbox-item.checked:before {
            content: "☑ ";
            margin-right: 5px;
        }

        /* SIGNATURE SECTION */
        .signature-section {
            margin-top: 15px;
        }
        .signature-table {
            width: 100%;
            margin-bottom: 10px;
        }
        .signature-table td {
            text-align: center;
            height: 60px;
            padding: 8px 5px;
            font-size: 10px;
            border: 1px solid #333;
        }
        .signature-section-header {
            font-weight: bold;
            text-align: center;
            margin-bottom: 8px;
            font-size: 11px;
        }

        /* PAGE BREAK */
        .page-break {
            page-break-after: always;
        }

        /* APPROVAL INDICATOR */
        .approval-status {
            margin-top: 10px;
            padding: 8px;
            border: 2px solid #333;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- HEADER -->
        <div class="header">
            <h1>PART SUBMISSION WARRANT (PSW)</h1>
            <p>PT Akebono Manufacturing Indonesia</p>
        </div>

        <!-- STEP 1: PART INFORMATION & ENGINEERING CHANGES -->
        <div class="section-title">1. PART INFORMATION & ENGINEERING CHANGES</div>
        
        <table class="form-table">
            <tr>
                <td class="label">Part Name</td>
                <td class="value"><?php echo e($formData['generalInformation']['partName'] ?? '_______________'); ?></td>
                <td class="label">Part Number</td>
                <td class="value"><?php echo e($formData['generalInformation']['partNumber'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">Safety / Government Regulation</td>
                <td class="value"><?php echo e($formData['generalInformation']['safetyGovernmentRegulation'] ? '☑ Yes' : '☐ No'); ?></td>
                <td class="label">Engineering Drawing Change Level</td>
                <td class="value"><?php echo e($formData['generalInformation']['engineeringDrawingChangeLevel'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">Engineering Drawing Change Date</td>
                <td class="value"><?php echo e($formData['generalInformation']['engineeringDrawingChangeDate'] ?? '_______________'); ?></td>
                <td class="label">Additional Engineering Changes</td>
                <td class="value"><?php echo e($formData['generalInformation']['additionalEngineeringChanges'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">Additional Engineering Changes Date</td>
                <td class="value"><?php echo e($formData['generalInformation']['additionalEngineeringChangesDate'] ?? '_______________'); ?></td>
                <td class="label">Shown on Drawing No.</td>
                <td class="value"><?php echo e($formData['generalInformation']['shownOnDrawingNo'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">Purchase Order No.</td>
                <td class="value"><?php echo e($formData['generalInformation']['purchaseOrderNo'] ?? '_______________'); ?></td>
                <td class="label">Weight (kg)</td>
                <td class="value"><?php echo e($formData['generalInformation']['weightKg'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">Checking Aid No.</td>
                <td class="value"><?php echo e($formData['generalInformation']['checkingAidNo'] ?? '_______________'); ?></td>
                <td class="label">Process Engineering Change Level</td>
                <td class="value"><?php echo e($formData['generalInformation']['processEngineeringChangeLevel'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">Process Engineering Change Date</td>
                <td class="value" colspan="3"><?php echo e($formData['generalInformation']['processEngineeringChangeDate'] ?? '_______________'); ?></td>
            </tr>
        </table>

        <!-- STEP 2: SUPPLIER & SUBMISSION INFORMATION -->
        <div class="section-title">2. SUPPLIER & SUBMISSION INFORMATION</div>
        
        <table class="form-table">
            <tr>
                <td class="label">Supplier Name</td>
                <td class="value" colspan="3"><?php echo e($formData['supplierSubmissionInfo']['supplierName'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">Supplier Code</td>
                <td class="value"><?php echo e($formData['supplierSubmissionInfo']['supplierCode'] ?? '_______________'); ?></td>
                <td class="label">Street Address</td>
                <td class="value"><?php echo e($formData['supplierSubmissionInfo']['streetAddress'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">City</td>
                <td class="value"><?php echo e($formData['supplierSubmissionInfo']['city'] ?? '_______________'); ?></td>
                <td class="label">State / Province</td>
                <td class="value"><?php echo e($formData['supplierSubmissionInfo']['state'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">Zip Code</td>
                <td class="value"><?php echo e($formData['supplierSubmissionInfo']['zip'] ?? '_______________'); ?></td>
                <td class="label">Customer Name / Division</td>
                <td class="value"><?php echo e($formData['supplierSubmissionInfo']['customerNameDivision'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">Buyer / Buyer Code</td>
                <td class="value" colspan="3"><?php echo e($formData['supplierSubmissionInfo']['buyerBuyerCode'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">Application</td>
                <td class="value" colspan="3"><?php echo e($formData['supplierSubmissionInfo']['application'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">Submission Information</td>
                <td class="value" colspan="3">
                    <div class="checkbox-group">
                        <?php $submissionInfo = json_decode($psw->submission_information, true) ?? []; ?>
                        <div class="checkbox-item <?php echo e(in_array('first_time', $submissionInfo) ? 'checked' : ''); ?>">First time submission</div>
                        <div class="checkbox-item <?php echo e(in_array('change', $submissionInfo) ? 'checked' : ''); ?>">Change submission</div>
                        <div class="checkbox-item <?php echo e(in_array('re_approval', $submissionInfo) ? 'checked' : ''); ?>">Re-approval submission</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="label">Contains Prohibited Substances</td>
                <td class="value"><?php echo e($formData['supplierSubmissionInfo']['containsProhibitedSubstances'] ? '☑ Yes' : '☐ No'); ?></td>
                <td class="label">Plastic Has ISO Marking</td>
                <td class="value"><?php echo e($formData['supplierSubmissionInfo']['plasticHasIsoMarking'] ? '☑ Yes' : '☐ No'); ?></td>
            </tr>
        </table>

        <!-- PAGE BREAK IF NEEDED -->
        <div class="page-break"></div>
    </div>

    <div class="page">
        <!-- STEP 3: REASON FOR SUBMISSION & RESULTS -->
        <div class="section-title">3. REASON FOR SUBMISSION & RESULTS</div>
        
        <table class="form-table">
            <tr>
                <td class="label">Reason for Submission</td>
                <td class="value" colspan="3">
                    <div class="checkbox-group">
                        <?php $reasonSubmission = json_decode($psw->reason_for_submission, true) ?? []; ?>
                        <div class="checkbox-item <?php echo e(in_array('initial', $reasonSubmission) ? 'checked' : ''); ?>">Initial submission</div>
                        <div class="checkbox-item <?php echo e(in_array('design_change', $reasonSubmission) ? 'checked' : ''); ?>">Design change</div>
                        <div class="checkbox-item <?php echo e(in_array('process_change', $reasonSubmission) ? 'checked' : ''); ?>">Process change</div>
                        <div class="checkbox-item <?php echo e(in_array('supplier_change', $reasonSubmission) ? 'checked' : ''); ?>">Supplier change</div>
                        <div class="checkbox-item <?php echo e(in_array('other', $reasonSubmission) ? 'checked' : ''); ?>">Other</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="label">Other Reason Details</td>
                <td class="value" colspan="3"><?php echo e($formData['reasonResults']['reasonOtherText'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">Submission Results</td>
                <td class="value" colspan="3">
                    <div class="checkbox-group">
                        <?php $submissionResults = json_decode($psw->submission_results, true) ?? []; ?>
                        <div class="checkbox-item <?php echo e(in_array('pass', $submissionResults) ? 'checked' : ''); ?>">Pass - meets all requirements</div>
                        <div class="checkbox-item <?php echo e(in_array('conditional', $submissionResults) ? 'checked' : ''); ?>">Conditional pass with minor deviations</div>
                        <div class="checkbox-item <?php echo e(in_array('fail', $submissionResults) ? 'checked' : ''); ?>">Fail - does not meet requirements</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="label">Meets All Requirements</td>
                <td class="value"><?php echo e($formData['reasonResults']['meetsAllRequirements'] ? '☑ Yes' : '☐ No'); ?></td>
                <td class="label">Explanation (if not meet)</td>
                <td class="value"><?php echo e($formData['reasonResults']['explanationIfNotMeet'] ?? '_______________'); ?></td>
            </tr>
        </table>

        <!-- STEP 4: DECLARATION & SIGNATURE -->
        <div class="section-title">4. DECLARATION & SIGNATURE</div>
        
        <table class="form-table">
            <tr>
                <td class="label">Explanation / Comments</td>
                <td class="value" colspan="3"><?php echo e($formData['declaration']['explanationComments'] ?? '_______________'); ?></td>
            </tr>
        </table>

        <!-- SIGNATURE SECTION -->
        <div class="signature-section-header">AUTHORIZED REPRESENTATIVE DECLARATION</div>
        <table class="signature-table">
            <tr>
                <td>
                    <strong>Authorized Signature</strong><br><br><br>
                    _________________________<br>
                    Signature
                </td>
                <td>
                    <strong>Print Name</strong><br><br>
                    <?php echo e($formData['declaration']['printName'] ?? '_______________'); ?><br>
                    Name (Print)
                </td>
                <td>
                    <strong>Title</strong><br><br>
                    <?php echo e($formData['declaration']['title'] ?? '_______________'); ?><br>
                    Title
                </td>
            </tr>
            <tr>
                <td colspan="3" class="center">
                    <strong>Date of Submission:</strong> <?php echo e($formData['declaration']['signatureDate'] ?? '_______________'); ?><br>
                    <strong>Phone No.:</strong> <?php echo e($formData['declaration']['phoneNo'] ?? '_______________'); ?> | 
                    <strong>Fax No.:</strong> <?php echo e($formData['declaration']['faxNo'] ?? '_______________'); ?>

                </td>
            </tr>
        </table>

        <!-- INTERNAL AKEBONO REVIEW SECTION (READ-ONLY) -->
        <div class="section-title">5. INTERNAL AKEBONO REVIEW & DECISION</div>
        
        <table class="form-table">
            <tr>
                <td class="label">Internal Decision</td>
                <td class="value"><?php echo e($formData['internalAkebono']['decision'] ?? 'PENDING REVIEW'); ?></td>
                <td class="label">Reviewed By</td>
                <td class="value"><?php echo e($formData['internalAkebono']['approvedBy'] ?? '_______________'); ?></td>
            </tr>
            <tr>
                <td class="label">Review Date</td>
                <td class="value"><?php echo e($formData['internalAkebono']['approvedDate'] ?? '_______________'); ?></td>
                <td class="label">Notes / Comments</td>
                <td class="value"><?php echo e($formData['internalAkebono']['notes'] ?? '_______________'); ?></td>
            </tr>
        </table>

        <!-- FOOTER -->
        <div class="approval-status">
            Status: <?php echo e(strtoupper($psw->status)); ?><br>
            Submitted: <?php echo e($psw->submitted_at?->format('Y-m-d H:i') ?? 'Not yet submitted'); ?>

        </div>

        <p style="text-align: center; margin-top: 20px; font-size: 9px;">
            This document is generated from the PT Akebono Approval System<br>
            Generated on: <?php echo e(now()->format('Y-m-d H:i:s')); ?>

        </p>
    </div>
</body>
</html>
<?php /**PATH E:\KERJAAN\akebono-approval\resources\views/part-submission-warrant/pdf/template.blade.php ENDPATH**/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Part Submission Warrant (PSW)</title>
<style>
    @page { size: 612pt 792pt; margin: 0; }
    * { margin: 0; padding: 0; }
    html, body { margin: 0; padding: 0; background: #fff; color: #000; }
    body { font-family: times, "Times New Roman", serif; font-size: 9.4pt; }

    /*
     * PSW - DOMPDF MASTER (v3)
     * Dibuat ulang dari koordinat PDF referensi "11. Part Submission Warrant (PSW)":
     * Letter 612 x 792 pt; semua elemen absolut (x/y dalam pt dari pojok kiri-atas halaman),
     * sehingga isi database yang panjang tidak menggeser elemen lain.
     *
     * v3: QR code Supplier Authorized Signature diletakkan tepat di atas garis tanda tangan.
     */
    .page { position: relative; width: 612pt; height: 791pt; overflow: hidden; }

    .t { position: absolute; white-space: pre; line-height: 11pt; height: 11pt; color: #000; }
    .t.t  { font-family: times, "Times New Roman", serif; font-weight: normal; }
    .t.tb { font-family: times, "Times New Roman", serif; font-weight: bold; }
    .t.hb { font-family: helvetica, arial, sans-serif; font-weight: bold; }
    .s94  { font-size: 9.4pt; }
    .s104 { font-size: 10.4pt; }
    .s113 { font-size: 11.3pt; }
    .hb  { transform: scale(0.870, 1); transform-origin: 0 0; }
    .s76  { font-size: 7.6pt; }

    .frame   { position: absolute; left: 67.8pt; top: 45.5pt; width: 503.5pt; height: 710.75pt; border: 0.75pt solid #000; }
    .divider { position: absolute; left: 67.8pt; top: 642pt; width: 505pt; height: 1pt; background: #000; }

    .ln { position: absolute; height: 0; border-top: 0.5pt solid #000; }

    .v { position: absolute; height: 9pt; line-height: 9pt; overflow: hidden; white-space: nowrap;
         font-family: "DejaVu Sans", arial, sans-serif; font-size: 7.4pt; color: #000; }

    .cb { position: absolute; width: 3.7pt; height: 4.8pt; border: 0.5pt solid #000; }
    .ck { position: absolute; width: 8pt; height: 8pt; line-height: 8pt; text-align: center;
          font-family: "DejaVu Sans", sans-serif; font-size: 7.5pt; font-weight: bold; }

    /* QR code tanda tangan: centered di atas garis signature */
    .qr-sign { position: absolute; left: 247pt; top: 595pt; width: 38pt; height: 38pt; }
    .qr-sign img { width: 38pt; height: 38pt; display: block; }
</style>
</head>
<body>
@php
    $general = $formData['generalInformation'] ?? [];
    $supplier = $formData['supplierSubmissionInfo'] ?? [];
    $reason = $formData['reasonResults'] ?? [];
    $declaration = $formData['declaration'] ?? [];
    $internal = $formData['internalAkebono'] ?? [];

    $decode = function ($value) {
        if (is_array($value)) {
            return $value;
        }
        $decoded = json_decode($value ?? '[]', true);
        return is_array($decoded) ? $decoded : [];
    };

    $submissionInfo = $decode($psw->submission_information ?? '[]');
    $reasonSubmission = $decode($psw->reason_for_submission ?? '[]');
    $submissionResults = $decode($psw->submission_results ?? '[]');

    $has = function ($array, $key, $aliases = []) {
        foreach (array_merge([$key], $aliases) as $item) {
            if (in_array($item, $array, true)) {
                return true;
            }
        }
        return false;
    };

    $customerDecision = strtolower(trim(
        (string) ($internal['decision'] ?? $psw->status ?? '')
    ));
@endphp

<div class="page">

    <!-- FRAME -->
    <div class="frame"></div>
    <div class="divider"></div>

    <!-- LABEL / TEKS STATIS -->
    <div class="t hb s113" style="left:81.00pt; top:33.42pt;">PT. AKEBONO BRAKE ASTRA INDONESIA</div>
    <div class="t tb s113" style="left:404.32pt; top:33.54pt;">Part Submission Warrant (PSW)</div>
    <div class="t t s104" style="left:81.00pt; top:58.13pt;">Part</div>
    <div class="t t s94" style="left:100.44pt; top:58.15pt;">Name</div>
    <div class="t t s94" style="left:364.69pt; top:58.15pt;">Part Number</div>
    <div class="t t s94" style="left:81.00pt; top:80.23pt;">Safety and/or</div>
    <div class="t t s94" style="left:81.24pt; top:91.75pt;">Government Regulation</div>
    <div class="t t s94" style="left:207.03pt; top:91.75pt;">Yes</div>
    <div class="t t s94" style="left:240.87pt; top:91.75pt;">No</div>
    <div class="t t s94" style="left:271.67pt; top:91.75pt;">Engineering Drawing Change Level</div>
    <div class="t t s94" style="left:479.07pt; top:91.75pt;">Dated</div>
    <div class="t t s94" style="left:81.00pt; top:113.35pt;">Additional Engineering Changes</div>
    <div class="t t s94" style="left:479.05pt; top:113.35pt;">Dated</div>
    <div class="t t s94" style="left:81.00pt; top:135.07pt;">Shown on Drawing No.</div>
    <div class="t t s94" style="left:260.94pt; top:135.07pt;">Purchase Order No.</div>
    <div class="t t s94" style="left:428.13pt; top:135.07pt;">Weight (kg)</div>
    <div class="t t s94" style="left:81.00pt; top:156.79pt;">Checking Aid No.</div>
    <div class="t t s94" style="left:238.29pt; top:156.79pt;">Process or Engineering Change Level</div>
    <div class="t t s94" style="left:478.95pt; top:156.79pt;">Dated</div>
    <div class="t tb s94" style="left:81.00pt; top:179.47pt;">SUPPLIER MANUFACTURING INFORMATION</div>
    <div class="t tb s94" style="left:326.76pt; top:179.47pt;">SUBMISSION INFORMATION</div>
    <div class="t t s94" style="left:332.88pt; top:201.79pt;">Dimensional</div>
    <div class="t t s94" style="left:398.61pt; top:201.79pt;">Materials/Functional</div>
    <div class="t t s94" style="left:496.80pt; top:201.79pt;">Appearance</div>
    <div class="t t s94" style="left:326.73pt; top:218.47pt;">Customer Name/Division</div>
    <div class="t t s94" style="left:326.55pt; top:234.67pt;">Buyer/Buyer Code</div>
    <div class="t t s94" style="left:326.73pt; top:250.87pt;">Application</div>
    <div class="t t s94" style="left:81.00pt; top:277.99pt;">City</div>
    <div class="t t s94" style="left:198.28pt; top:277.99pt;">State</div>
    <div class="t t s94" style="left:285.00pt; top:277.99pt;">Zip</div>
    <div class="t t s94" style="left:82.44pt; top:300.31pt;">Note:</div>
    <div class="t t s94" style="left:109.96pt; top:300.31pt;">Does this part contain any restricted or reportable substances?</div>
    <div class="t t s94" style="left:438.87pt; top:300.31pt;">Yes</div>
    <div class="t t s94" style="left:489.69pt; top:300.31pt;">No</div>
    <div class="t t s94" style="left:109.96pt; top:311.95pt;">Are plastic parts identified with appropriate ISO marking codes?</div>
    <div class="t t s94" style="left:438.81pt; top:311.95pt;">Yes</div>
    <div class="t t s94" style="left:489.69pt; top:311.95pt;">No</div>
    <div class="t tb s104" style="left:80.28pt; top:335.33pt;">REASON FOR SUBMISSION</div>
    <div class="t t s94" style="left:90.69pt; top:347.11pt;">Initial Submission</div>
    <div class="t t s94" style="left:387.96pt; top:347.11pt;">Change to Optional Construction or Material</div>
    <div class="t t s94" style="left:90.69pt; top:358.75pt;">Engineering Change(s)</div>
    <div class="t t s94" style="left:387.96pt; top:358.75pt;">Sub-Supplier or Material Source Change</div>
    <div class="t t s94" style="left:90.69pt; top:370.27pt;">Tooling: Transfer, Replacement, Refurbishment, or additional</div>
    <div class="t t s94" style="left:387.96pt; top:370.27pt;">Change in Part Processing</div>
    <div class="t t s94" style="left:90.69pt; top:381.79pt;">Correction of Discrepancy</div>
    <div class="t t s94" style="left:387.96pt; top:381.79pt;">Parts Produced at Additional Location</div>
    <div class="t t s94" style="left:90.69pt; top:393.31pt;">Tooling inactive &gt; than 1 year</div>
    <div class="t t s94" style="left:387.96pt; top:393.31pt;">Other - please specify</div>
    <div class="t tb s104" style="left:81.00pt; top:416.93pt;">SUBMISSION RESULTS</div>
    <div class="t t s94" style="left:81.00pt; top:434.59pt;">The results for</div>
    <div class="t t s94" style="left:182.13pt; top:434.59pt;">dimensional measurements</div>
    <div class="t t s94" style="left:354.45pt; top:434.59pt;">material and functional tests</div>
    <div class="t t s94" style="left:182.13pt; top:446.11pt;">appearance criteria</div>
    <div class="t t s94" style="left:354.45pt; top:446.11pt;">statistical process evaluation</div>
    <div class="t t s94" style="left:81.00pt; top:468.55pt;">These results meet all drawing and specification requirements:</div>
    <div class="t t s94" style="left:354.45pt; top:468.55pt;">Yes</div>
    <div class="t t s94" style="left:411.03pt; top:468.55pt;">No (if "No" - Explanation Required)</div>
    <div class="t tb s104" style="left:81.00pt; top:492.05pt;">DECLARATION</div>
    <div class="t t s94" style="left:81.00pt; top:503.23pt;">I hereby affirm that the samples represented by this warrant are representative of our parts, and have been made to the</div>
    <div class="t t s94" style="left:81.00pt; top:514.03pt;">applicable</div>
    <div class="t t s94" style="left:81.00pt; top:524.95pt;">Customer drawings and specification and are made from specified materials on regular production tooling, regular</div>
    <div class="t t s94" style="left:81.00pt; top:535.75pt;">production process. I have noted any deviations from this declaration below :</div>
    <div class="t t s104" style="left:81.00pt; top:558.05pt;">EXPLANATION/COMMENTS:</div>
    <div class="t t s94" style="left:81.00pt; top:581.23pt;">Print Name</div>
    <div class="t t s94" style="left:218.28pt; top:581.23pt;">Title</div>
    <div class="t t s94" style="left:327.70pt; top:581.23pt;">Phone No.</div>
    <div class="t t s94" style="left:445.09pt; top:581.23pt;">Fax No.</div>
    <div class="t t s94" style="left:81.00pt; top:624.43pt;">Supplier Authorized Signature</div>
    <div class="t t s94" style="left:339.45pt; top:624.43pt;">Date</div>
    <div class="t tb s104" style="left:216.60pt; top:648.05pt;">FOR CUSTOMER USE ONLY (IF APPLICABLE)</div>
    <div class="t t s94" style="left:90.51pt; top:671.83pt;">Approved</div>
    <div class="t t s94" style="left:199.23pt; top:671.83pt;">Interim Approved</div>
    <div class="t t s94" style="left:340.47pt; top:671.83pt;">Rejected</div>
    <div class="t t s94" style="left:81.00pt; top:742.15pt;">Authorized Signature (QA Manager)</div>
    <div class="t t s94" style="left:249.50pt; top:742.15pt;">Authorized Signature&nbsp; (Purchasing Manager)</div>
    <div class="t t s94" style="left:445.03pt; top:742.15pt;">Date :</div>
    <div class="t t s76" style="left:82.80pt; top:211.04pt;">Supplier Name &amp; Supplier Code</div>
    <div class="t t s76" style="left:83.40pt; top:243.68pt;">Street Address</div>

    <!-- GARIS ISIAN -->
    <div class="ln" style="left:125.00pt; top:68.25pt; width:192.80pt;"></div>
    <div class="ln" style="left:415.50pt; top:68.25pt; width:131.50pt;"></div>
    <div class="ln" style="left:410.20pt; top:101.75pt; width:47.00pt;"></div>
    <div class="ln" style="left:503.80pt; top:101.75pt; width:52.00pt;"></div>
    <div class="ln" style="left:206.50pt; top:123.50pt; width:193.00pt;"></div>
    <div class="ln" style="left:503.80pt; top:123.50pt; width:51.70pt;"></div>
    <div class="ln" style="left:172.80pt; top:145.25pt; width:80.00pt;"></div>
    <div class="ln" style="left:337.80pt; top:145.25pt; width:84.70pt;"></div>
    <div class="ln" style="left:476.00pt; top:145.25pt; width:80.00pt;"></div>
    <div class="ln" style="left:161.50pt; top:166.75pt; width:70.50pt;"></div>
    <div class="ln" style="left:382.80pt; top:166.75pt; width:89.20pt;"></div>
    <div class="ln" style="left:518.00pt; top:166.75pt; width:37.50pt;"></div>
    <div class="ln" style="left:81.00pt; top:211.75pt; width:211.80pt;"></div>
    <div class="ln" style="left:425.50pt; top:228.50pt; width:131.50pt;"></div>
    <div class="ln" style="left:81.80pt; top:244.75pt; width:211.70pt;"></div>
    <div class="ln" style="left:399.80pt; top:244.75pt; width:155.20pt;"></div>
    <div class="ln" style="left:373.50pt; top:261.00pt; width:183.50pt;"></div>
    <div class="ln" style="left:81.00pt; top:277.25pt; width:110.00pt;"></div>
    <div class="ln" style="left:199.80pt; top:277.25pt; width:80.00pt;"></div>
    <div class="ln" style="left:285.00pt; top:277.25pt; width:75.00pt;"></div>
    <div class="ln" style="left:220.80pt; top:568.50pt; width:331.20pt;"></div>
    <div class="ln" style="left:126.20pt; top:591.25pt; width:84.80pt;"></div>
    <div class="ln" style="left:238.20pt; top:591.25pt; width:84.80pt;"></div>
    <div class="ln" style="left:369.80pt; top:591.25pt; width:70.40pt;"></div>
    <div class="ln" style="left:477.80pt; top:591.25pt; width:75.20pt;"></div>
    <div class="ln" style="left:198.20pt; top:634.50pt; width:136.30pt;"></div>
    <div class="ln" style="left:359.50pt; top:634.50pt; width:89.50pt;"></div>
    <div class="ln" style="left:81.00pt; top:741.25pt; width:134.50pt;"></div>
    <div class="ln" style="left:249.80pt; top:741.25pt; width:165.40pt;"></div>
    <div class="ln" style="left:470.00pt; top:752.25pt; width:52.00pt;"></div>

    <!-- NILAI ISIAN (dari database) -->
    <div class="v" style="left:126.50pt; top:57.95pt; width:190.80pt;">{{ $general['partName'] ?? '' }}</div>
    <div class="v" style="left:417.00pt; top:57.95pt; width:148.50pt;">{{ $general['partNumber'] ?? '' }}</div>
    <div class="v" style="left:411.70pt; top:91.45pt; width:45.00pt;">{{ $general['engineeringDrawingChangeLevel'] ?? '' }}</div>
    <div class="v" style="left:505.30pt; top:91.45pt; width:60.20pt;">{{ $general['engineeringDrawingChangeDate'] ?? '' }}</div>
    <div class="v" style="left:208.00pt; top:113.20pt; width:191.00pt;">{{ $general['additionalEngineeringChanges'] ?? '' }}</div>
    <div class="v" style="left:505.30pt; top:113.20pt; width:60.20pt;">{{ $general['additionalEngineeringChangesDate'] ?? '' }}</div>
    <div class="v" style="left:174.30pt; top:134.95pt; width:78.00pt;">{{ $general['shownOnDrawingNo'] ?? '' }}</div>
    <div class="v" style="left:339.30pt; top:134.95pt; width:82.70pt;">{{ $general['purchaseOrderNo'] ?? '' }}</div>
    <div class="v" style="left:477.50pt; top:134.95pt; width:88.00pt;">{{ $general['weightKg'] ?? '' }}</div>
    <div class="v" style="left:163.00pt; top:156.45pt; width:68.50pt;">{{ $general['checkingAidNo'] ?? '' }}</div>
    <div class="v" style="left:384.30pt; top:156.45pt; width:87.20pt;">{{ $general['processEngineeringChangeLevel'] ?? '' }}</div>
    <div class="v" style="left:519.50pt; top:156.45pt; width:46.00pt;">{{ $general['processEngineeringChangeDate'] ?? '' }}</div>
    <div class="v" style="left:82.50pt; top:201.45pt; width:209.80pt;">{{ trim(($supplier['supplierName'] ?? '') . (!empty($supplier['supplierCode']) ? ' / ' . $supplier['supplierCode'] : '')) }}</div>
    <div class="v" style="left:427.00pt; top:218.20pt; width:138.50pt;">{{ $supplier['customerNameDivision'] ?? '' }}</div>
    <div class="v" style="left:83.30pt; top:234.45pt; width:209.70pt;">{{ $supplier['streetAddress'] ?? '' }}</div>
    <div class="v" style="left:401.30pt; top:234.45pt; width:164.20pt;">{{ $supplier['buyerBuyerCode'] ?? '' }}</div>
    <div class="v" style="left:375.00pt; top:250.70pt; width:190.50pt;">{{ $supplier['application'] ?? '' }}</div>
    <div class="v" style="left:82.50pt; top:266.95pt; width:108.00pt;">{{ $supplier['city'] ?? '' }}</div>
    <div class="v" style="left:200.50pt; top:266.95pt; width:78.00pt;">{{ $supplier['state'] ?? '' }}</div>
    <div class="v" style="left:285.50pt; top:266.95pt; width:73.00pt;">{{ $supplier['zip'] ?? '' }}</div>
    <div class="v" style="left:222.30pt; top:558.20pt; width:343.20pt;">{{ $declaration['explanationComments'] ?? '' }}</div>
    <div class="v" style="left:127.70pt; top:580.95pt; width:82.80pt;">{{ $declaration['printName'] ?? '' }}</div>
    <div class="v" style="left:239.70pt; top:580.95pt; width:82.80pt;">{{ $declaration['title'] ?? '' }}</div>
    <div class="v" style="left:371.30pt; top:580.95pt; width:68.40pt;">{{ $declaration['phoneNo'] ?? '' }}</div>
    <div class="v" style="left:479.30pt; top:580.95pt; width:86.20pt;">{{ $declaration['faxNo'] ?? '' }}</div>

    {{-- QR CODE: tepat di atas garis Supplier Authorized Signature --}}
    @if(!empty($declaration['signatureQrData']))
        <div class="qr-sign">
            <img src="{{ $declaration['signatureQrData'] }}" alt="QR">
        </div>
    @endif

    <div class="v" style="left:361.00pt; top:624.20pt; width:87.50pt;">{{ $declaration['signatureDate'] ?? '' }}</div>
    <div class="v" style="left:471.50pt; top:741.95pt; width:50.00pt;">{{ $internal['approvedDate'] ?? '' }}</div>

    <!-- CHECKBOX -->
    <div class="cb" style="left:199.50pt; top:94.60pt;"></div>
    @if(!empty($general['safetyGovernmentRegulation']))<div class="ck" style="left:197.60pt; top:93.20pt;">&#10003;</div>@endif
    <div class="cb" style="left:233.30pt; top:94.60pt;"></div>
    @if(empty($general['safetyGovernmentRegulation']))<div class="ck" style="left:231.40pt; top:93.20pt;">&#10003;</div>@endif
    <div class="cb" style="left:323.10pt; top:204.70pt;"></div>
    @if($has($submissionInfo, 'dimensional'))<div class="ck" style="left:321.20pt; top:203.30pt;">&#10003;</div>@endif
    <div class="cb" style="left:388.70pt; top:204.70pt;"></div>
    @if($has($submissionInfo, 'materials_functional'))<div class="ck" style="left:386.80pt; top:203.30pt;">&#10003;</div>@endif
    <div class="cb" style="left:487.00pt; top:204.70pt;"></div>
    @if($has($submissionInfo, 'appearance'))<div class="ck" style="left:485.10pt; top:203.30pt;">&#10003;</div>@endif
    <div class="cb" style="left:428.90pt; top:303.20pt;"></div>
    @if(!empty($supplier['containsProhibitedSubstances']))<div class="ck" style="left:427.00pt; top:301.80pt;">&#10003;</div>@endif
    <div class="cb" style="left:479.80pt; top:303.20pt;"></div>
    @if(empty($supplier['containsProhibitedSubstances']))<div class="ck" style="left:477.90pt; top:301.80pt;">&#10003;</div>@endif
    <div class="cb" style="left:428.90pt; top:314.80pt;"></div>
    @if(!empty($supplier['plasticHasIsoMarking']))<div class="ck" style="left:427.00pt; top:313.40pt;">&#10003;</div>@endif
    <div class="cb" style="left:479.80pt; top:314.80pt;"></div>
    @if(empty($supplier['plasticHasIsoMarking']))<div class="ck" style="left:477.90pt; top:313.40pt;">&#10003;</div>@endif
    <div class="cb" style="left:80.80pt; top:350.00pt;"></div>
    @if($has($reasonSubmission, 'initial_submission', ['initial']))<div class="ck" style="left:78.90pt; top:348.60pt;">&#10003;</div>@endif
    <div class="cb" style="left:80.80pt; top:361.60pt;"></div>
    @if($has($reasonSubmission, 'engineering_changes', ['engineering_change', 'design_change']))<div class="ck" style="left:78.90pt; top:360.20pt;">&#10003;</div>@endif
    <div class="cb" style="left:80.80pt; top:373.10pt;"></div>
    @if($has($reasonSubmission, 'tooling_transfer', ['tooling']))<div class="ck" style="left:78.90pt; top:371.70pt;">&#10003;</div>@endif
    <div class="cb" style="left:80.80pt; top:384.70pt;"></div>
    @if($has($reasonSubmission, 'correction_discrepancy', ['correction']))<div class="ck" style="left:78.90pt; top:383.30pt;">&#10003;</div>@endif
    <div class="cb" style="left:80.80pt; top:396.20pt;"></div>
    @if($has($reasonSubmission, 'tooling_inactive'))<div class="ck" style="left:78.90pt; top:394.80pt;">&#10003;</div>@endif
    <div class="cb" style="left:378.00pt; top:350.00pt;"></div>
    @if($has($reasonSubmission, 'change_optional_construction', ['optional_construction']))<div class="ck" style="left:376.10pt; top:348.60pt;">&#10003;</div>@endif
    <div class="cb" style="left:378.00pt; top:361.60pt;"></div>
    @if($has($reasonSubmission, 'sub_supplier_change', ['supplier_change']))<div class="ck" style="left:376.10pt; top:360.20pt;">&#10003;</div>@endif
    <div class="cb" style="left:378.00pt; top:373.10pt;"></div>
    @if($has($reasonSubmission, 'change_part_processing', ['part_processing', 'process_change']))<div class="ck" style="left:376.10pt; top:371.70pt;">&#10003;</div>@endif
    <div class="cb" style="left:378.00pt; top:384.70pt;"></div>
    @if($has($reasonSubmission, 'parts_additional_location', ['additional_location']))<div class="ck" style="left:376.10pt; top:383.30pt;">&#10003;</div>@endif
    <div class="cb" style="left:378.00pt; top:396.20pt;"></div>
    @if($has($reasonSubmission, 'other'))<div class="ck" style="left:376.10pt; top:394.80pt;">&#10003;</div>@endif
    <div class="cb" style="left:174.60pt; top:437.50pt;"></div>
    @if($has($submissionResults, 'dimensional_measurements', ['dimensional']))<div class="ck" style="left:172.70pt; top:436.10pt;">&#10003;</div>@endif
    <div class="cb" style="left:346.90pt; top:437.50pt;"></div>
    @if($has($submissionResults, 'material_functional_tests', ['materials_functional']))<div class="ck" style="left:345.00pt; top:436.10pt;">&#10003;</div>@endif
    <div class="cb" style="left:174.60pt; top:449.00pt;"></div>
    @if($has($submissionResults, 'appearance_criteria', ['appearance']))<div class="ck" style="left:172.70pt; top:447.60pt;">&#10003;</div>@endif
    <div class="cb" style="left:346.90pt; top:449.00pt;"></div>
    @if($has($submissionResults, 'statistical_process_evaluation', ['statistical']))<div class="ck" style="left:345.00pt; top:447.60pt;">&#10003;</div>@endif
    <div class="cb" style="left:346.90pt; top:471.40pt;"></div>
    @if(!empty($reason['meetsAllRequirements']))<div class="ck" style="left:345.00pt; top:470.00pt;">&#10003;</div>@endif
    <div class="cb" style="left:403.50pt; top:471.40pt;"></div>
    @if(empty($reason['meetsAllRequirements']))<div class="ck" style="left:401.60pt; top:470.00pt;">&#10003;</div>@endif
    <div class="cb" style="left:82.90pt; top:674.70pt;"></div>
    @if($customerDecision === 'approved')<div class="ck" style="left:81.00pt; top:673.30pt;">&#10003;</div>@endif
    <div class="cb" style="left:191.70pt; top:674.70pt;"></div>
    @if(in_array($customerDecision, ['interim approved', 'interim_approved'], true))<div class="ck" style="left:189.80pt; top:673.30pt;">&#10003;</div>@endif
    <div class="cb" style="left:332.90pt; top:674.70pt;"></div>
    @if($customerDecision === 'rejected')<div class="ck" style="left:331.00pt; top:673.30pt;">&#10003;</div>@endif

</div>
</body>
</html>
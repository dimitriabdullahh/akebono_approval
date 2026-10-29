{{-- Part Submission Warrant (PSW) Preview Embed for Review Page --}}
<div style="padding: 20px; background: #fff; font-family: 'Times New Roman', Times, serif; font-size: 11px;">
    {{-- Header --}}
    <div style="border: 2px solid #000; padding: 15px; margin-bottom: 15px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="flex: 1;">
                <h4 style="margin: 0; font-weight: bold; font-family: Helvetica, Arial, sans-serif;">
                    PT. AKEBONO BRAKE ASTRA INDONESIA
                </h4>
            </div>
            <div style="flex: 1; text-align: right;">
                <h3 style="margin: 0; font-weight: bold;">Part Submission Warrant (PSW)</h3>
            </div>
        </div>
    </div>

    @php
        $formData = $psw->form_data_json ?? [];
        $general = $formData['generalInformation'] ?? [];
        $supplier = $formData['supplierSubmissionInfo'] ?? [];
        $reason = $formData['reasonResults'] ?? [];
        $declaration = $formData['declaration'] ?? [];
        $internal = $formData['internalAkebono'] ?? [];

        $decode = function ($value) {
            if (is_array($value)) return $value;
            $decoded = json_decode($value ?? '[]', true);
            return is_array($decoded) ? $decoded : [];
        };

        $submissionInfo = $decode($psw->submission_information ?? '[]');
        $reasonSubmission = $decode($psw->reason_for_submission ?? '[]');
        $submissionResults = $decode($psw->submission_results ?? '[]');

        $has = function ($array, $key, $aliases = []) {
            foreach (array_merge([$key], $aliases) as $item) {
                if (in_array($item, $array, true)) return true;
            }
            return false;
        };
    @endphp

    {{-- Part Information --}}
    <div style="background: #f9f9f9; padding: 10px; border: 1px solid #000; margin-bottom: 15px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div>
                <div style="margin-bottom: 8px;">
                    <strong>Part Name:</strong> {{ $general['partName'] ?? $psw->part_name ?? '-' }}
                </div>
                <div style="margin-bottom: 8px;">
                    <strong>Part Number:</strong> {{ $general['partNumber'] ?? $psw->part_number ?? '-' }}
                </div>
                <div style="margin-bottom: 8px;">
                    <strong>Safety/Government Regulation:</strong> 
                    <span style="display: inline-block; width: 20px; height: 15px; border: 1px solid #000; text-align: center; margin: 0 5px;">
                        {{ !empty($general['safetyGovernmentRegulation']) ? '✓' : '' }}
                    </span> Yes
                    <span style="display: inline-block; width: 20px; height: 15px; border: 1px solid #000; text-align: center; margin: 0 5px;">
                        {{ empty($general['safetyGovernmentRegulation']) ? '✓' : '' }}
                    </span> No
                </div>
                <div style="margin-bottom: 8px;">
                    <strong>Engineering Drawing Change Level:</strong> {{ $general['engineeringDrawingChangeLevel'] ?? '-' }}
                </div>
                <div style="margin-bottom: 8px;">
                    <strong>Dated:</strong> {{ $general['engineeringDrawingChangeDate'] ?? '-' }}
                </div>
            </div>
            <div>
                <div style="margin-bottom: 8px;">
                    <strong>Additional Engineering Changes:</strong> {{ $general['additionalEngineeringChanges'] ?? '-' }}
                </div>
                <div style="margin-bottom: 8px;">
                    <strong>Dated:</strong> {{ $general['additionalEngineeringChangesDate'] ?? '-' }}
                </div>
                <div style="margin-bottom: 8px;">
                    <strong>Shown on Drawing No.:</strong> {{ $general['shownOnDrawingNo'] ?? '-' }}
                </div>
                <div style="margin-bottom: 8px;">
                    <strong>Purchase Order No.:</strong> {{ $general['purchaseOrderNo'] ?? '-' }}
                </div>
                <div style="margin-bottom: 8px;">
                    <strong>Weight (kg):</strong> {{ $general['weightKg'] ?? '-' }}
                </div>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
            <div style="margin-bottom: 8px;">
                <strong>Checking Aid No.:</strong> {{ $general['checkingAidNo'] ?? '-' }}
            </div>
            <div>
                <strong>Process/Engineering Change Level:</strong> {{ $general['processEngineeringChangeLevel'] ?? '-' }}
                <strong style="margin-left: 15px;">Dated:</strong> {{ $general['processEngineeringChangeDate'] ?? '-' }}
            </div>
        </div>
    </div>

    {{-- Two Column Layout --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
        {{-- Left Column: Supplier Info --}}
        <div style="border: 1px solid #000; padding: 10px;">
            <h4 style="margin: 0 0 10px 0; font-weight: bold; text-align: center; background: #f0f0f0; padding: 5px;">
                SUPPLIER MANUFACTURING INFORMATION
            </h4>
            <div style="margin-bottom: 8px; font-size: 10px;">
                <strong>Supplier Name & Code:</strong><br>
                {{ $supplier['supplierName'] ?? '-' }}
                @if(!empty($supplier['supplierCode']))
                    / {{ $supplier['supplierCode'] }}
                @endif
            </div>
            <div style="margin-bottom: 8px; font-size: 10px;">
                <strong>Street Address:</strong><br>
                {{ $supplier['streetAddress'] ?? '-' }}
            </div>
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 8px; margin-bottom: 8px; font-size: 10px;">
                <div><strong>City:</strong> {{ $supplier['city'] ?? '-' }}</div>
                <div><strong>State:</strong> {{ $supplier['state'] ?? '-' }}</div>
                <div><strong>Zip:</strong> {{ $supplier['zip'] ?? '-' }}</div>
            </div>
            <div style="border: 1px solid #ddd; background: #fffbf0; padding: 8px; margin-top: 10px; font-size: 10px;">
                <div style="margin-bottom: 5px;">
                    <strong>Note:</strong>
                </div>
                <div style="margin-bottom: 5px;">
                    Does this part contain any restricted or reportable substances?
                    <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin: 0 3px;">
                        {{ !empty($supplier['containsProhibitedSubstances']) ? '✓' : '' }}
                    </span> Yes
                    <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin: 0 3px;">
                        {{ empty($supplier['containsProhibitedSubstances']) ? '✓' : '' }}
                    </span> No
                </div>
                <div>
                    Are plastic parts identified with appropriate ISO marking codes?
                    <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin: 0 3px;">
                        {{ !empty($supplier['plasticHasIsoMarking']) ? '✓' : '' }}
                    </span> Yes
                    <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin: 0 3px;">
                        {{ empty($supplier['plasticHasIsoMarking']) ? '✓' : '' }}
                    </span> No
                </div>
            </div>
        </div>

        {{-- Right Column: Submission Info --}}
        <div style="border: 1px solid #000; padding: 10px;">
            <h4 style="margin: 0 0 10px 0; font-weight: bold; text-align: center; background: #f0f0f0; padding: 5px;">
                SUBMISSION INFORMATION
            </h4>
            <div style="margin-bottom: 8px; font-size: 10px;">
                <div style="display: flex; gap: 10px; margin-bottom: 5px;">
                    <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center;">
                        {{ $has($submissionInfo, 'dimensional') ? '✓' : '' }}
                    </span>
                    <span>Dimensional</span>
                    
                    <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin-left: 20px;">
                        {{ $has($submissionInfo, 'materials_functional') ? '✓' : '' }}
                    </span>
                    <span>Materials/Functional</span>
                    
                    <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin-left: 20px;">
                        {{ $has($submissionInfo, 'appearance') ? '✓' : '' }}
                    </span>
                    <span>Appearance</span>
                </div>
            </div>
            <div style="margin-bottom: 8px; font-size: 10px;">
                <strong>Customer Name/Division:</strong><br>
                {{ $supplier['customerNameDivision'] ?? '-' }}
            </div>
            <div style="margin-bottom: 8px; font-size: 10px;">
                <strong>Buyer/Buyer Code:</strong><br>
                {{ $supplier['buyerBuyerCode'] ?? '-' }}
            </div>
            <div style="margin-bottom: 8px; font-size: 10px;">
                <strong>Application:</strong><br>
                {{ $supplier['application'] ?? '-' }}
            </div>
        </div>
    </div>

    {{-- Reason for Submission --}}
    <div style="border: 1px solid #000; padding: 10px; margin-bottom: 15px;">
        <h4 style="margin: 0 0 10px 0; font-weight: bold; background: #f0f0f0; padding: 5px;">
            REASON FOR SUBMISSION
        </h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 10px;">
            <div>
                <div style="margin-bottom: 5px;">
                    <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin-right: 5px;">
                        {{ $has($reasonSubmission, 'initial_submission', ['initial']) ? '✓' : '' }}
                    </span>
                    Initial Submission
                </div>
                <div style="margin-bottom: 5px;">
                    <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin-right: 5px;">
                        {{ $has($reasonSubmission, 'engineering_changes', ['engineering_change']) ? '✓' : '' }}
                    </span>
                    Engineering Change(s)
                </div>
                <div style="margin-bottom: 5px;">
                    <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin-right: 5px;">
                        {{ $has($reasonSubmission, 'tooling_transfer', ['tooling']) ? '✓' : '' }}
                    </span>
                    Tooling: Transfer, Replacement, Refurbishment, or additional
                </div>
                <div style="margin-bottom: 5px;">
                    <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin-right: 5px;">
                        {{ $has($reasonSubmission, 'correction_discrepancy', ['correction']) ? '✓' : '' }}
                    </span>
                    Correction of Discrepancy
                </div>
                <div style="margin-bottom: 5px;">
                    <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin-right: 5px;">
                        {{ $has($reasonSubmission, 'tooling_inactive') ? '✓' : '' }}
                    </span>
                    Tooling inactive &gt; than 1 year
                </div>
            </div>
            <div>
                <div style="margin-bottom: 5px;">
                    <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin-right: 5px;">
                        {{ $has($reasonSubmission, 'change_optional_construction') ? '✓' : '' }}
                    </span>
                    Change to Optional Construction or Material
                </div>
                <div style="margin-bottom: 5px;">
                    <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin-right: 5px;">
                        {{ $has($reasonSubmission, 'sub_supplier_change', ['supplier_change']) ? '✓' : '' }}
                    </span>
                    Sub-Supplier or Material Source Change
                </div>
                <div style="margin-bottom: 5px;">
                    <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin-right: 5px;">
                        {{ $has($reasonSubmission, 'change_part_processing', ['process_change']) ? '✓' : '' }}
                    </span>
                    Change in Part Processing
                </div>
                <div style="margin-bottom: 5px;">
                    <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin-right: 5px;">
                        {{ $has($reasonSubmission, 'parts_additional_location') ? '✓' : '' }}
                    </span>
                    Parts Produced at Additional Location
                </div>
                <div style="margin-bottom: 5px;">
                    <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin-right: 5px;">
                        {{ $has($reasonSubmission, 'other') ? '✓' : '' }}
                    </span>
                    Other - please specify
                </div>
            </div>
        </div>
    </div>

    {{-- Submission Results --}}
    <div style="border: 1px solid #000; padding: 10px; margin-bottom: 15px;">
        <h4 style="margin: 0 0 10px 0; font-weight: bold; background: #f0f0f0; padding: 5px;">
            SUBMISSION RESULTS
        </h4>
        <div style="font-size: 10px;">
            <div style="margin-bottom: 8px;">
                The results for:
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                <div>
                    <div style="margin-bottom: 5px;">
                        <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin-right: 5px;">
                            {{ $has($submissionResults, 'dimensional_measurements', ['dimensional']) ? '✓' : '' }}
                        </span>
                        dimensional measurements
                    </div>
                    <div style="margin-bottom: 5px;">
                        <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin-right: 5px;">
                            {{ $has($submissionResults, 'appearance_criteria', ['appearance']) ? '✓' : '' }}
                        </span>
                        appearance criteria
                    </div>
                </div>
                <div>
                    <div style="margin-bottom: 5px;">
                        <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin-right: 5px;">
                            {{ $has($submissionResults, 'material_functional_tests', ['materials_functional']) ? '✓' : '' }}
                        </span>
                        material and functional tests
                    </div>
                    <div style="margin-bottom: 5px;">
                        <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin-right: 5px;">
                            {{ $has($submissionResults, 'statistical_process_evaluation', ['statistical']) ? '✓' : '' }}
                        </span>
                        statistical process evaluation
                    </div>
                </div>
            </div>
            <div style="margin-top: 10px; padding: 8px; background: #f9f9f9; border: 1px solid #ddd;">
                These results meet all drawing and specification requirements:
                <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin: 0 5px;">
                    {{ !empty($reason['meetsAllRequirements']) ? '✓' : '' }}
                </span> Yes
                <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin: 0 5px;">
                    {{ empty($reason['meetsAllRequirements']) ? '✓' : '' }}
                </span> No (if "No" - Explanation Required)
            </div>
        </div>
    </div>

    {{-- Declaration --}}
    <div style="border: 1px solid #000; padding: 10px; margin-bottom: 15px;">
        <h4 style="margin: 0 0 10px 0; font-weight: bold; background: #f0f0f0; padding: 5px;">
            DECLARATION
        </h4>
        <div style="font-size: 10px; margin-bottom: 10px;">
            I hereby affirm that the samples represented by this warrant are representative of our parts, and have been made to the
            applicable Customer drawings and specification and are made from specified materials on regular production tooling, regular
            production process. I have noted any deviations from this declaration below:
        </div>
        
        <div style="margin-bottom: 10px;">
            <strong>EXPLANATION/COMMENTS:</strong>
            <div style="border: 1px solid #ddd; padding: 8px; min-height: 40px; background: #f9f9f9; margin-top: 5px;">
                {{ $declaration['explanationComments'] ?? '-' }}
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 10px; font-size: 10px; margin-bottom: 10px;">
            <div>
                <strong>Print Name:</strong><br>
                <div style="border-bottom: 1px solid #000; padding: 3px 0;">
                    {{ $declaration['printName'] ?? '' }}
                </div>
            </div>
            <div>
                <strong>Title:</strong><br>
                <div style="border-bottom: 1px solid #000; padding: 3px 0;">
                    {{ $declaration['title'] ?? '' }}
                </div>
            </div>
            <div>
                <strong>Phone No.:</strong><br>
                <div style="border-bottom: 1px solid #000; padding: 3px 0;">
                    {{ $declaration['phoneNo'] ?? '' }}
                </div>
            </div>
            <div>
                <strong>Fax No.:</strong><br>
                <div style="border-bottom: 1px solid #000; padding: 3px 0;">
                    {{ $declaration['faxNo'] ?? '' }}
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px; font-size: 10px;">
            <div>
                <strong>Supplier Authorized Signature:</strong><br>
                <div style="border-bottom: 1px solid #000; padding: 3px 0; min-height: 30px;">
                    {{ $declaration['authorizedSignature'] ?? '' }}
                </div>
            </div>
            <div>
                <strong>Date:</strong><br>
                <div style="border-bottom: 1px solid #000; padding: 3px 0;">
                    {{ $declaration['signatureDate'] ?? '' }}
                </div>
            </div>
        </div>
    </div>

    {{-- For Customer Use Only --}}
    <div style="border: 1px solid #000; padding: 10px;">
        <h4 style="margin: 0 0 10px 0; font-weight: bold; text-align: center; background: #e8f4f8; padding: 5px;">
            FOR CUSTOMER USE ONLY (IF APPLICABLE)
        </h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px; font-size: 10px;">
            @php
                $customerDecision = strtolower(trim((string) ($internal['decision'] ?? $psw->status ?? '')));
            @endphp
            <div>
                <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin-right: 5px;">
                    {{ $customerDecision === 'approved' ? '✓' : '' }}
                </span>
                Approved
            </div>
            <div>
                <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin-right: 5px;">
                    {{ in_array($customerDecision, ['interim approved', 'interim_approved'], true) ? '✓' : '' }}
                </span>
                Interim Approved
            </div>
            <div>
                <span style="display: inline-block; width: 18px; height: 12px; border: 1px solid #000; text-align: center; margin-right: 5px;">
                    {{ $customerDecision === 'rejected' ? '✓' : '' }}
                </span>
                Rejected
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; font-size: 10px;">
            <div style="text-align: center; border: 1px solid #ddd; padding: 10px;">
                <strong>Authorized Signature<br>(QA Manager)</strong>
                <div style="min-height: 50px; margin: 10px 0;"></div>
                <div style="border-top: 1px solid #000; padding-top: 5px;"></div>
            </div>
            <div style="text-align: center; border: 1px solid #ddd; padding: 10px;">
                <strong>Authorized Signature<br>(Purchasing Manager)</strong>
                <div style="min-height: 50px; margin: 10px 0;"></div>
                <div style="border-top: 1px solid #000; padding-top: 5px;"></div>
            </div>
            <div style="text-align: center; border: 1px solid #ddd; padding: 10px;">
                <strong>Date:</strong>
                <div style="min-height: 50px; margin: 10px 0;"></div>
                <div style="border-top: 1px solid #000; padding-top: 5px;">
                    {{ $internal['approvedDate'] ?? '' }}
                </div>
            </div>
        </div>
    </div>
</div>

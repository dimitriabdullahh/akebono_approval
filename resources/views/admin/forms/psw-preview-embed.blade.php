<div class="p-4">
    <!-- Header -->
    <div class="mb-4 pb-3 border-bottom">
        <h5 class="mb-3">
            <i class="ti ti-file-text me-2"></i>Part Submission Warrant (PSW)
        </h5>
        <div class="row">
            <div class="col-md-4">
                <small class="text-muted">Part Number</small>
                <p class="mb-0 fw-semibold">{{ $psw->part_number ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Status</small>
                <p class="mb-0">
                    <span class="badge bg-{{ $psw->status_color }}">{{ $psw->status_label }}</span>
                </p>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Supplier</small>
                <p class="mb-0">{{ $psw->supplier?->name ?? $psw->supplier_name }}</p>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" href="#psw-step1" data-bs-toggle="tab">
                <i class="ti ti-info-circle me-2"></i>Informasi Umum
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#psw-step2" data-bs-toggle="tab">
                <i class="ti ti-building me-2"></i>Supplier
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#psw-step3" data-bs-toggle="tab">
                <i class="ti ti-flask me-2"></i>Alasan & Hasil
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#psw-step4" data-bs-toggle="tab">
                <i class="ti ti-signature me-2"></i>Deklarasi
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Step 1: Informasi Umum -->
        <div class="tab-pane fade show active" id="psw-step1">
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Part Name</small>
                    <p class="mb-0">{{ $psw->part_name ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Part Number</small>
                    <p class="mb-0 fw-semibold">{{ $psw->part_number ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Safety Government Regulation</small>
                    <p class="mb-0">
                        @if($psw->safety_government_regulation)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Weight (kg)</small>
                    <p class="mb-0">{{ $psw->weight_kg ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Engineering Drawing Change Level</small>
                    <p class="mb-0">{{ $psw->engineering_drawing_change_level ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Engineering Drawing Change Date</small>
                    <p class="mb-0">{{ $psw->engineering_drawing_change_date?->format('d M Y') ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Purchase Order No</small>
                    <p class="mb-0">{{ $psw->purchase_order_no ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Shown on Drawing No</small>
                    <p class="mb-0">{{ $psw->shown_on_drawing_no ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Step 2: Informasi Supplier -->
        <div class="tab-pane fade" id="psw-step2">
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Supplier Name</small>
                    <p class="mb-0">{{ $psw->supplier_name ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Supplier Code</small>
                    <p class="mb-0">{{ $psw->supplier_code ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Street Address</small>
                    <p class="mb-0">{{ $psw->street_address ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">City</small>
                    <p class="mb-0">{{ $psw->city ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">State</small>
                    <p class="mb-0">{{ $psw->state ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">ZIP Code</small>
                    <p class="mb-0">{{ $psw->zip ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Customer Name/Division</small>
                    <p class="mb-0">{{ $psw->customer_name_division ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Buyer Code</small>
                    <p class="mb-0">{{ $psw->buyer_buyer_code ?? '-' }}</p>
                </div>
                <div class="col-12 mb-3">
                    <small class="text-muted">Application</small>
                    <p class="mb-0">{{ $psw->application ?? '-' }}</p>
                </div>
                <div class="col-12 mb-3">
                    <small class="text-muted">Submission Information</small>
                    <p class="mb-0">
                        @if($psw->submission_information)
                            @foreach($psw->submission_information as $info)
                                <span class="badge bg-info me-1">{{ ucfirst(str_replace('_', ' ', $info)) }}</span>
                            @endforeach
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Contains Prohibited Substances</small>
                    <p class="mb-0">
                        @if($psw->contains_prohibited_substances)
                            <span class="badge bg-danger">Yes</span>
                        @else
                            <span class="badge bg-success">No</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Plastic Has ISO Marking</small>
                    <p class="mb-0">
                        @if($psw->plastic_has_iso_marking)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Step 3: Alasan & Hasil -->
        <div class="tab-pane fade" id="psw-step3">
            <div class="row mb-3">
                <div class="col-12 mb-3">
                    <small class="text-muted">Reason for Submission</small>
                    <p class="mb-0">
                        @if($psw->reason_for_submission)
                            @foreach($psw->reason_for_submission as $reason)
                                <span class="badge bg-info me-1">{{ ucfirst(str_replace('_', ' ', $reason)) }}</span>
                            @endforeach
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div class="col-12 mb-3">
                    <small class="text-muted">Reason Other Text</small>
                    <p class="mb-0">{{ $psw->reason_other_text ?? '-' }}</p>
                </div>
                <div class="col-12 mb-3">
                    <small class="text-muted">Submission Results</small>
                    <p class="mb-0">
                        @if($psw->submission_results)
                            @foreach($psw->submission_results as $result)
                                <span class="badge bg-info me-1">{{ ucfirst(str_replace('_', ' ', $result)) }}</span>
                            @endforeach
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Meets All Requirements</small>
                    <p class="mb-0">
                        @if($psw->meets_all_requirements)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-danger">No</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Explanation if Not Meet</small>
                    <p class="mb-0">{{ $psw->explanation_if_not_meet ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Step 4: Deklarasi -->
        <div class="tab-pane fade" id="psw-step4">
            <div class="row mb-3">
                <div class="col-12 mb-3">
                    <small class="text-muted">Explanation Comments</small>
                    <p class="mb-0">{{ $psw->explanation_comments ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Print Name</small>
                    <p class="mb-0 fw-semibold">{{ $psw->print_name ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Title</small>
                    <p class="mb-0">{{ $psw->title ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Phone No</small>
                    <p class="mb-0">{{ $psw->phone_no ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Fax No</small>
                    <p class="mb-0">{{ $psw->fax_no ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Signature Date</small>
                    <p class="mb-0">{{ $psw->signature_date?->format('d M Y') ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Supplier Authorized Signature</small>
                    <p class="mb-0">
                        @if($psw->supplier_authorized_signature)
                            <a href="{{ Storage::url($psw->supplier_authorized_signature) }}" target="_blank" class="badge bg-primary">
                                <i class="ti ti-external-link me-1"></i>View Signature
                            </a>
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>

            <!-- Approval Table -->
            <table class="table table-sm table-bordered mt-4">
                <thead class="table-light">
                    <tr>
                        <th>Internal Decision</th>
                        <th>Approved By</th>
                        <th>Approved Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $psw->internal_decision ?? '-' }}</strong>
                        </td>
                        <td>
                            <strong>{{ $psw->internal_approved_by ?? '-' }}</strong>
                        </td>
                        <td>
                            {{ $psw->internal_approved_date?->format('d M Y') ?? '-' }}
                        </td>
                    </tr>
                </tbody>
            </table>

            @if($psw->internal_notes)
                <div class="mt-3 p-3 bg-light rounded">
                    <small class="text-muted">Internal Notes</small>
                    <p class="mb-0">{{ $psw->internal_notes }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

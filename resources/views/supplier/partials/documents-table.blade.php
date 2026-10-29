@php
    // Get all documents sorted by document_type order for consistent order
    $allDocuments = isset($part) ? $part->requestDocuments->sortBy(function($doc) {
        return $doc->documentType->order ?? 999;
    })->values() : collect(); // Add ->values() to reset array keys after sorting
    
    // Count mandatory documents with proper status checking
    $mandatoryDocs = $allDocuments->where('documentType.is_mandatory', true);
    $optionalDocs = $allDocuments->where('documentType.is_mandatory', false);
    
    $mandatoryTotal = $mandatoryDocs->count();
    
    // Count uploaded/submitted mandatory documents with proper status check for form-based docs
    // REVISE status is NOT counted as submitted
    $mandatoryUploaded = 0;
    foreach ($mandatoryDocs as $doc) {
        $docCode = $doc->documentType->code ?? '';
        $isSubmitted = false;
        
        // Skip if document is in REVISE status
        if ($doc->status === 'REVISE') {
            continue;
        }
        
        // Check based on document type
        if ($docCode === 'IS') {
            // Inspection Standard - check inspection_standards table
            $is = $doc->inspectionStandard ?? \App\Models\InspectionStandard::where('request_document_id', $doc->id)->first();
            $isSubmitted = $is && in_array(strtoupper($is->status), ['SUBMITTED', 'PENDING_REVIEW', 'APPROVED']);
        } elseif ($docCode === 'MSA') {
            // MSA Gage R&R - check msa_gage_rr table
            $msa = $doc->msaGageRr ?? \App\Models\MsaGageRr::where('request_document_id', $doc->id)->first();
            // MSA status is stored in uppercase but check both cases for safety
            $isSubmitted = $msa && (in_array(strtoupper($msa->status), ['SUBMITTED', 'PENDING_REVIEW', 'APPROVED']) || $msa->status === 'SUBMITTED');
        } elseif (in_array($docCode, ['PSW', 'PACKAGING_STANDARD'])) {
            // PSW/PS - check respective tables
            if ($docCode === 'PSW') {
                $form = $doc->partSubmissionWarrant ?? \App\Models\PartSubmissionWarrant::where('request_document_id', $doc->id)->first();
            } else {
                $form = $doc->packagingStandard ?? \App\Models\PackagingStandard::where('request_document_id', $doc->id)->first();
            }
            $isSubmitted = $form && in_array(strtoupper($form->status), ['SUBMITTED', 'PENDING_REVIEW', 'APPROVED']);
        } else {
            // Regular upload documents - check request_documents table
            $isSubmitted = in_array(strtoupper($doc->status), ['SUBMITTED', 'APPROVED']);
        }
        
        if ($isSubmitted) {
            $mandatoryUploaded++;
        }
    }
    
    $mandatoryPending = $mandatoryTotal - $mandatoryUploaded;
    
    $totalDocs = $allDocuments->count();
    $totalPending = $allDocuments->where('status', 'PENDING')->count();
    
    // Check if all mandatory documents are uploaded
    $canSubmit = $mandatoryPending === 0;
    
    // Get submission status from part
    $submissionStatus = isset($part) && $part->submission_status ? $part->submission_status : 'DRAFT';
    $isSubmitted = $submissionStatus === 'SUBMITTED';
@endphp

@if($allDocuments->isNotEmpty() && isset($part))
<!-- Part Information Header -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="mb-3">
            <label class="text-muted small mb-1">MODEL</label>
            <div class="fw-bold">{{ $part->documentRequest->model ?? '-' }}</div>
        </div>
        <div class="mb-3">
            <label class="text-muted small mb-1">PART NAME</label>
            <div class="fw-bold">{{ $part->part_name }}</div>
        </div>
        <div class="mb-3">
            <label class="text-muted small mb-1">NUMBER PART</label>
            <div class="fw-bold">{{ $part->part_number }}</div>
        </div>
        <div>
            <label class="text-muted small mb-1">DUE DATE</label>
            <div class="fw-bold">
                {{ $part->documentRequest->due_date ? $part->documentRequest->due_date->format('d F Y') : '-' }}
            </div>
        </div>
    </div>
</div>

<!-- Document Submission Status -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div>
                <h5 class="mb-1"><i class="ti ti-files"></i> Document Submission</h5>
                <small class="text-muted">
                    @if($isSubmitted)
                        <span class="badge bg-success">
                            <i class="ti ti-check"></i> Submitted
                        </span>
                        @if($part->submitted_at)
                            on {{ $part->submitted_at->format('d F Y, H:i') }}
                        @endif
                    @else
                        <span class="badge bg-warning text-dark">
                            <i class="ti ti-clock"></i> Draft
                        </span>
                        @if($part->last_saved_at)
                            - Last saved: {{ $part->last_saved_at->format('d F Y, H:i') }}
                        @endif
                    @endif
                </small>
            </div>
            <div class="text-end">
                <div class="mb-1">
                    <span class="text-muted small">Mandatory Documents:</span>
                    <strong class="{{ $mandatoryPending > 0 ? 'text-danger' : 'text-success' }}">
                        {{ $mandatoryUploaded }} / {{ $mandatoryTotal }}
                    </strong>
                </div>
                @if($mandatoryPending > 0)
                <small class="text-danger">
                    <i class="ti ti-alert-circle"></i> {{ $mandatoryPending }} mandatory document(s) pending
                </small>
                @else
                <small class="text-success">
                    <i class="ti ti-check-circle"></i> All mandatory documents uploaded
                </small>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <!-- Document Submission Table -->
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="text-center">NO</th>
                        <th width="30%">DOCUMENT</th>
                        <th width="45%">ATTACHMENT</th>
                        <th width="20%" class="text-center">DATE SUBMIT</th>
                    </tr>
                </thead>
                <tbody id="documentsTableBody">
                    @foreach($allDocuments as $index => $doc)
                    {{-- Special handling for Inspection Standard documents --}}
                    @if($doc->documentType->code === 'IS')
                    <tr>
                        <td class="text-center align-middle">
                            <strong>{{ $index + 1 }}</strong>
                        </td>
                        <td class="align-middle">
                            {{-- Include IS component inline --}}
                            <div class="fw-semibold mb-1">{{ $doc->documentType->name }}</div>
                            @if($doc->documentType->is_mandatory)
                                <span class="badge bg-danger badge-sm">Mandatory</span>
                            @else
                                <span class="badge bg-secondary badge-sm">Optional</span>
                            @endif
                            
                            @php
                                // SINGLE SOURCE OF TRUTH: Ambil status dari tabel inspection_standards
                                $inspectionStandard = $doc->inspectionStandard;
                                if (!$inspectionStandard) {
                                    $inspectionStandard = \App\Models\InspectionStandard::where('request_document_id', $doc->id)->first();
                                }
                                // Status diambil dari inspection_standards.status, bukan request_documents.status
                                $currentStatus = $inspectionStandard?->status ?? 'PENDING';
                            @endphp
                            
                            {{-- Badge status berdasarkan inspection_standards.status --}}
                            @if($currentStatus === 'DRAFT')
                                <span class="badge bg-secondary badge-sm ms-1">Draft</span>
                            @elseif($currentStatus === 'PENDING')
                                <span class="badge bg-warning text-dark badge-sm ms-1">Pending</span>
                            @elseif($currentStatus === 'SUBMITTED' || $currentStatus === 'PENDING_REVIEW')
                                <span class="badge bg-success badge-sm ms-1">Submitted</span>
                            @elseif($currentStatus === 'REVISE')
                                <span class="badge badge-sm ms-1" style="background-color: #FFFFFF; color: #000000; border: 1px solid #CED4DA;">Revisi</span>
                            @elseif($currentStatus === 'APPROVED')
                                <span class="badge bg-success badge-sm ms-1">Approved</span>
                            @elseif($currentStatus === 'REJECTED')
                                <span class="badge bg-danger badge-sm ms-1">Rejected</span>
                            @endif
                            
                            @if($doc->documentType->description)
                            <div class="text-muted small mt-1">{{ $doc->documentType->description }}</div>
                            @endif
                            
                            {{-- Tampilkan informasi revisi jika status REVISE --}}
                            @if($doc->status === 'REVISE' && $doc->canBeRevised())
                            <div class="alert alert-warning small mt-2 mb-0 p-2">
                                <strong><i class="ti ti-alert-circle"></i> Revisi Diperlukan</strong><br>
                                <small><strong>Direvisi oleh:</strong> {{ $doc->getTeamLabel() }}</small><br>
                                <small><strong>Catatan:</strong> {{ $doc->revision_note }}</small>
                                @if($doc->revised_at)
                                <div class="mt-1">
                                    <small><i class="ti ti-clock"></i> {{ $doc->revised_at->format('d M Y H:i') }}</small>
                                </div>
                                @endif
                            </div>
                            @endif
                        </td>
                        
                        <!-- ATTACHMENT -->
                        <td class="align-middle">
                            @if($doc->status === 'REVISE' && $doc->canBeRevised())
                                {{-- Tombol Revisi untuk dokumen yang perlu direvisi --}}
                                @php
                                    $token = $part->documentRequest->access_token ?? '';
                                @endphp
                                <a href="{{ route('inspection-standard.form', $token) }}" class="btn btn-warning btn-sm w-100" style="border: 2px solid #FFA500;">
                                    <i class="ti ti-pencil me-1"></i>Revisi Dokumen
                                </a>
                            @elseif(in_array($currentStatus, ['DRAFT', 'PENDING']))
                                {{-- Show form button untuk DRAFT atau PENDING --}}
                                @php
                                    $token = $part->documentRequest->access_token ?? '';
                                    $buttonText = $currentStatus === 'DRAFT' ? 'Lanjutkan Form' : 'Isi Form Terlebih Dahulu';
                                @endphp
                                <a href="{{ route('inspection-standard.form', $token) }}" class="btn btn-primary btn-sm w-100">
                                    <i class="ti ti-pencil me-1"></i>{{ $buttonText }}
                                </a>
                            @elseif(in_array($currentStatus, ['SUBMITTED', 'PENDING_REVIEW', 'APPROVED']))
                                {{-- Show PDF buttons --}}
                                @if($inspectionStandard && $inspectionStandard->pdf_file_path)
                                <div class="d-flex gap-2 w-100">
                                    <a href="{{ route('inspection-standard.pdf', $inspectionStandard->id) }}?v={{ $doc->updated_at?->timestamp ?? time() }}" 
                                       class="btn btn-outline-primary btn-sm flex-fill" 
                                       target="_blank"
                                       style="min-width: 0;">
                                        <i class="ti ti-eye me-1"></i>View PDF
                                    </a>
                                    <a href="{{ route('inspection-standard.download', $inspectionStandard->id) }}?v={{ $doc->updated_at?->timestamp ?? time() }}" 
                                       class="btn btn-success btn-sm flex-fill"
                                       style="min-width: 0;">
                                        <i class="ti ti-download me-1"></i>Download
                                    </a>
                                </div>
                                @endif
                            @endif
                        </td>
                        
                        <!-- DATE SUBMIT -->
                        <td class="text-center align-middle">
                            @if($inspectionStandard && $inspectionStandard->submitted_at)
                                <div class="fw-semibold">{{ $inspectionStandard->submitted_at->format('d M Y') }}</div>
                                <small class="text-muted">{{ $inspectionStandard->submitted_at->format('H:i') }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    {{-- Special handling for MSA Gage R&R documents --}}
                    @elseif($doc->documentType->code === 'MSA')
                    <tr>
                        <td class="text-center align-middle">
                            <strong>{{ $index + 1 }}</strong>
                        </td>
                        <td class="align-middle">
                            {{-- MSA Gage R&R Form component --}}
                            <div class="fw-semibold mb-1">{{ $doc->documentType->name }}</div>
                            @if($doc->documentType->is_mandatory)
                                <span class="badge bg-danger badge-sm">Mandatory</span>
                            @else
                                <span class="badge bg-secondary badge-sm">Optional</span>
                            @endif
                            
                            @php
                                // SINGLE SOURCE OF TRUTH: Ambil status dari tabel msa_gage_rr
                                $msaForm = $doc->msaGageRr;
                                if (!$msaForm) {
                                    $msaForm = \App\Models\MsaGageRr::where('request_document_id', $doc->id)->first();
                                }
                                // Status diambil dari msa_gage_rr.status, bukan request_documents.status
                                // Status sama seperti IS: DRAFT, PENDING, SUBMITTED
                                // Jika form ada tapi status NULL, itu berarti DRAFT
                                // NORMALIZE ke uppercase untuk konsistensi
                                $rawStatus = $msaForm ? ($msaForm->status ?? 'DRAFT') : 'PENDING';
                                $msaStatus = strtoupper($rawStatus);
                            @endphp
                            
                            {{-- Badge status berdasarkan msa_gage_rr.status (sama format dengan IS) --}}
                            @if($msaStatus === 'DRAFT')
                                <span class="badge bg-secondary badge-sm ms-1">Draft</span>
                            @elseif($msaStatus === 'PENDING')
                                <span class="badge bg-warning text-dark badge-sm ms-1">Pending</span>
                            @elseif($msaStatus === 'SUBMITTED')
                                <span class="badge bg-success badge-sm ms-1">Submitted</span>
                            @elseif($msaStatus === 'REVISE' || $doc->status === 'REVISE')
                                <span class="badge badge-sm ms-1" style="background-color: #FFFFFF; color: #000000; border: 1px solid #CED4DA;">Revisi</span>
                            @endif
                            
                            @if($doc->documentType->description)
                            <div class="text-muted small mt-1">{{ $doc->documentType->description }}</div>
                            @endif
                            
                            {{-- Tampilkan informasi revisi jika status REVISE --}}
                            @if($doc->status === 'REVISE' && $doc->canBeRevised())
                            <div class="alert alert-warning small mt-2 mb-0 p-2">
                                <strong><i class="ti ti-alert-circle"></i> Revisi Diperlukan</strong><br>
                                <small><strong>Direvisi oleh:</strong> {{ $doc->getTeamLabel() }}</small><br>
                                <small><strong>Catatan:</strong> {{ $doc->revision_note }}</small>
                                @if($doc->revised_at)
                                <div class="mt-1">
                                    <small><i class="ti ti-clock"></i> {{ $doc->revised_at->format('d M Y H:i') }}</small>
                                </div>
                                @endif
                            </div>
                            @endif
                        </td>
                        
                        <!-- ATTACHMENT -->
                        <td class="align-middle">
                            @if($doc->status === 'REVISE' && $doc->canBeRevised())
                                {{-- Tombol Revisi untuk dokumen yang perlu direvisi --}}
                                @if($msaForm && $msaForm->token)
                                <a href="{{ route('supplier.msa', ['token' => $msaForm->token]) }}" class="btn btn-warning btn-sm w-100" style="border: 2px solid #FFA500;" target="_blank">
                                    <i class="ti ti-pencil me-1"></i>Revisi Dokumen
                                </a>
                                @endif
                            @elseif($msaStatus === 'PENDING')
                                {{-- Show form button untuk pending (belum dibuat) - redirect to form --}}
                                @if($msaForm && $msaForm->token)
                                <a href="{{ route('supplier.msa', ['token' => $msaForm->token]) }}" class="btn btn-primary btn-sm w-100" target="_blank">
                                    <i class="ti ti-pencil me-1"></i>Isi Form Terlebih Dahulu
                                </a>
                                @else
                                <div class="alert alert-warning mb-0 p-2">
                                    <small>MSA form not initialized. Please contact admin.</small>
                                </div>
                                @endif
                            @elseif($msaStatus === 'DRAFT')
                                {{-- Show form button untuk draft --}}
                                @if($msaForm && $msaForm->token)
                                <a href="{{ route('supplier.msa', ['token' => $msaForm->token]) }}" class="btn btn-primary btn-sm w-100" target="_blank">
                                    <i class="ti ti-pencil me-1"></i>Lanjutkan Form
                                </a>
                                @else
                                <div class="alert alert-warning mb-0 p-2">
                                    <small>Form not initialized</small>
                                </div>
                                @endif
                            @elseif($msaStatus === 'SUBMITTED')
                                {{-- Show PDF buttons --}}
                                @if($msaForm)
                                <div class="d-flex gap-2 w-100">
                                    <a href="{{ route('api.msa.pdf', ['id' => $msaForm->id]) }}?v={{ $doc->updated_at?->timestamp ?? time() }}" 
                                       class="btn btn-outline-primary btn-sm flex-fill" 
                                       target="_blank"
                                       style="min-width: 0;">
                                        <i class="ti ti-eye me-1"></i>View PDF
                                    </a>
                                    <a href="{{ route('api.msa.pdf', ['id' => $msaForm->id]) }}?download=1&v={{ $doc->updated_at?->timestamp ?? time() }}" 
                                       class="btn btn-success btn-sm flex-fill"
                                       style="min-width: 0;">
                                        <i class="ti ti-download me-1"></i>Download
                                    </a>
                                </div>
                                @endif
                            @endif
                        </td>
                        
                        <!-- DATE SUBMIT -->
                        <td class="text-center align-middle">
                            @if($msaForm && $msaForm->submitted_at)
                                <div class="fw-semibold">{{ $msaForm->submitted_at->format('d M Y') }}</div>
                                <small class="text-muted">{{ $msaForm->submitted_at->format('H:i') }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    {{-- Special handling for Form-based documents (PSW, PS) --}}
                    @elseif(in_array($doc->documentType->code, ['PSW', 'PACKAGING_STANDARD']))
                    <tr>
                        <td class="text-center align-middle">
                            <strong>{{ $index + 1 }}</strong>
                        </td>
                        <td class="align-middle">
                            {{-- PSW/PS Form component --}}
                            <div class="fw-semibold mb-1">{{ $doc->documentType->name }}</div>
                            @if($doc->documentType->is_mandatory)
                                <span class="badge bg-danger badge-sm">Mandatory</span>
                            @else
                                <span class="badge bg-secondary badge-sm">Optional</span>
                            @endif
                            
                            @php
                                // SINGLE SOURCE OF TRUTH: Ambil status dari tabel form (PSW atau PS)
                                if ($doc->documentType->code === 'PSW') {
                                    $formModel = $doc->partSubmissionWarrant;
                                    if (!$formModel) {
                                        $formModel = \App\Models\PartSubmissionWarrant::where('request_document_id', $doc->id)->first();
                                    }
                                } else {
                                    $formModel = $doc->packagingStandard;
                                    if (!$formModel) {
                                        $formModel = \App\Models\PackagingStandard::where('request_document_id', $doc->id)->first();
                                    }
                                }
                                // Status diambil dari form table, bukan request_documents
                                $formStatus = $formModel?->status ?? 'PENDING';
                            @endphp
                            
                            {{-- Badge status berdasarkan form status --}}
                            @if($formStatus === 'DRAFT')
                                <span class="badge bg-secondary badge-sm ms-1">Draft</span>
                            @elseif($formStatus === 'PENDING')
                                <span class="badge bg-warning text-dark badge-sm ms-1">Pending</span>
                            @elseif($formStatus === 'SUBMITTED' || $formStatus === 'PENDING_REVIEW')
                                <span class="badge bg-success badge-sm ms-1">Submitted</span>
                            @elseif($formStatus === 'REVISE')
                                <span class="badge bg-danger badge-sm ms-1">Revision Required</span>
                            @elseif($formStatus === 'APPROVED')
                                <span class="badge bg-success badge-sm ms-1">Approved</span>
                            @elseif($formStatus === 'REJECTED')
                                <span class="badge bg-danger badge-sm ms-1">Rejected</span>
                            @endif
                            
                            @if($doc->documentType->description)
                            <div class="text-muted small mt-1">{{ $doc->documentType->description }}</div>
                            @endif
                            
                            @if($doc->status === 'REVISE' && $doc->revision_note)
                            <div class="alert alert-danger small mt-2 mb-0 p-2">
                                <strong><i class="ti ti-alert-circle"></i> Revision Required:</strong><br>
                                {{ $doc->revision_note }}
                                @if($doc->revision_deadline_at)
                                <div class="mt-1">
                                    <small><i class="ti ti-clock"></i> Deadline: {{ $doc->revision_deadline_at->format('d M Y H:i') }}</small>
                                </div>
                                @endif
                            </div>
                            @endif
                        </td>
                        
                        <!-- ATTACHMENT -->
                        <td class="align-middle">
                            @if(in_array($formStatus, ['DRAFT', 'PENDING', 'REVISE']))
                                {{-- Show form button --}}
                                @php
                                    $formRoute = $doc->documentType->code === 'PSW'
                                        ? route('psw.form', ['token' => $doc->id])
                                        : route('ps.form', ['token' => $doc->id]);
                                    $buttonText = $formStatus === 'DRAFT' ? 'Lanjutkan Form' : 'Isi Form Terlebih Dahulu';
                                @endphp
                                <a href="{{ $formRoute }}" class="btn btn-primary btn-sm w-100" target="_blank">
                                    <i class="ti ti-pencil me-1"></i>{{ $buttonText }}
                                </a>
                            @elseif(in_array($formStatus, ['SUBMITTED', 'PENDING_REVIEW', 'APPROVED']))
                                {{-- Show PDF buttons --}}
                                @php
                                    if ($doc->documentType->code === 'PSW') {
                                        $psw = $formModel;
                                        $pdfRoute = $psw ? route('psw.pdf', ['pswId' => $psw->id]) . '?v=' . ($doc->updated_at?->timestamp ?? time()) : '#';
                                        $downloadRoute = $psw ? route('psw.download', ['pswId' => $psw->id]) . '?v=' . ($doc->updated_at?->timestamp ?? time()) : '#';
                                    } else {
                                        $ps = $formModel;
                                        $pdfRoute = $ps ? route('ps.pdf', ['psId' => $ps->id]) . '?v=' . ($doc->updated_at?->timestamp ?? time()) : '#';
                                        $downloadRoute = $ps ? route('ps.download', ['psId' => $ps->id]) . '?v=' . ($doc->updated_at?->timestamp ?? time()) : '#';
                                    }
                                @endphp
                                @if($pdfRoute !== '#')
                                <div class="d-flex gap-2 w-100">
                                    <a href="{{ $pdfRoute }}" 
                                       class="btn btn-outline-primary btn-sm flex-fill" 
                                       target="_blank"
                                       style="min-width: 0;">
                                        <i class="ti ti-eye me-1"></i>View PDF
                                    </a>
                                    <a href="{{ $downloadRoute }}" 
                                       class="btn btn-success btn-sm flex-fill"
                                       style="min-width: 0;">
                                        <i class="ti ti-download me-1"></i>Download
                                    </a>
                                </div>
                                @else
                                <div class="alert alert-warning mb-0 p-2">
                                    <small>Form data not found</small>
                                </div>
                                @endif
                            @endif
                        </td>
                        
                        <!-- DATE SUBMIT -->
                        <td class="text-center align-middle">
                            @if($formModel && $formModel->submitted_at)
                                <div class="fw-semibold">{{ $formModel->submitted_at->format('d M Y') }}</div>
                                <small class="text-muted">{{ $formModel->submitted_at->format('H:i') }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @else
                    <tr>
                        <!-- NO -->
                        <td class="text-center align-middle">
                            <strong>{{ $index + 1 }}</strong>
                        </td>
                        
                        <!-- DOCUMENT -->
                        <td class="align-middle">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold mb-1">{{ $doc->documentType->name }}</div>
                                    @if($doc->documentType->is_mandatory)
                                        <span class="badge bg-danger badge-sm">Mandatory</span>
                                    @else
                                        <span class="badge bg-secondary badge-sm">Optional</span>
                                    @endif
                                    
                                    <!-- Status Badge -->
                                    @if($doc->status === 'PENDING')
                                        <span class="badge bg-warning text-dark badge-sm ms-1">Pending</span>
                                    @elseif($doc->status === 'SUBMITTED')
                                        <span class="badge bg-success badge-sm ms-1">Submitted</span>
                                    @elseif($doc->status === 'REVISE')
                                        <span class="badge bg-danger badge-sm ms-1">Revision Required</span>
                                    @elseif($doc->status === 'APPROVED')
                                        <span class="badge bg-success badge-sm ms-1">Approved</span>
                                    @endif
                                    
                                    @if($doc->documentType->description)
                                    <div class="text-muted small mt-1">{{ $doc->documentType->description }}</div>
                                    @endif
                                    
                                    <!-- Revision Note -->
                                    @if($doc->status === 'REVISE' && $doc->revision_note)
                                    <div class="alert alert-danger small mt-2 mb-0 p-2">
                                        <strong><i class="ti ti-alert-circle"></i> Revision Required:</strong><br>
                                        {{ $doc->revision_note }}
                                        @if($doc->revision_deadline_at)
                                        <div class="mt-1">
                                            <small><i class="ti ti-clock"></i> Deadline: {{ $doc->revision_deadline_at->format('d M Y H:i') }}</small>
                                        </div>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        
                        <!-- ATTACHMENT -->
                        <td class="align-middle">
                            @php
                                $docCode = $doc->documentType->code ?? '';
                                // Hide upload button for these document types (form-based, coming soon)
                                $hideUploadButton = in_array($docCode, ['XR_CONTROL_CHART', 'N1_DATA']);
                            @endphp
                            
                            @if($doc->file_path)
                                <!-- File Uploaded -->
                                <div class="d-flex flex-column gap-2">
                                    <!-- File Info -->
                                    <div class="border rounded p-2 bg-light d-flex align-items-center">
                                        <i class="ti ti-file-check text-success fs-4 me-2"></i>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold small">{{ Str::limit($doc->original_filename ?? 'Document', 35) }}</div>
                                            <small class="text-muted">
                                                @php
                                                    $extension = strtoupper(pathinfo($doc->original_filename, PATHINFO_EXTENSION));
                                                @endphp
                                                {{ $extension }} File
                                                @if($doc->file_size)
                                                    | {{ number_format($doc->file_size / 1024, 2) }} KB
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="d-flex gap-2 w-100">
                                        @if($doc->file_path)
                                        <a href="{{ route('supplier.view', $doc->id) }}?v={{ $doc->updated_at?->timestamp ?? time() }}" 
                                           class="btn btn-outline-primary btn-sm flex-fill" 
                                           target="_blank"
                                           style="min-width: 0;">
                                            <i class="ti ti-eye me-1"></i>View
                                        </a>
                                        <a href="{{ route('supplier.download', $doc->id) }}?v={{ $doc->updated_at?->timestamp ?? time() }}" 
                                           class="btn btn-success btn-sm flex-fill"
                                           style="min-width: 0;">
                                            <i class="ti ti-download me-1"></i>Download
                                        </a>
                                        @endif
                                        @if(!$isSubmitted && in_array($doc->status, ['PENDING', 'REVISE']))
                                        <a href="{{ route('supplier.upload.form', $doc->id) }}" 
                                           class="btn btn-warning btn-sm" 
                                           title="Replace Document">
                                            <i class="ti ti-refresh"></i>
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <!-- No File Uploaded -->
                                @if($hideUploadButton)
                                    <!-- Hidden for form-based documents -->
                                    <div class="text-center py-2">
                                        <i class="ti ti-clock text-muted fs-3"></i>
                                        <div class="text-muted small">Coming Soon</div>
                                    </div>
                                @elseif(!$isSubmitted)
                                <a href="{{ route('supplier.upload.form', $doc->id) }}" 
                                   class="btn btn-primary btn-sm d-flex align-items-center justify-content-center gap-2 w-100">
                                    <i class="ti ti-upload fs-5"></i> 
                                    <span>Upload Document</span>
                                </a>
                                @else
                                <div class="text-center py-2">
                                    <i class="ti ti-file-off text-muted fs-3"></i>
                                    <div class="text-muted small">Not uploaded</div>
                                </div>
                                @endif
                            @endif
                        </td>
                        
                        <!-- DATE SUBMIT -->
                        <td class="text-center align-middle">
                            @if($doc->submitted_at)
                                <div class="fw-semibold">{{ $doc->submitted_at->format('d F Y') }}</div>
                                <small class="text-muted">{{ $doc->submitted_at->format('H:i') }}</small>
                            @elseif($doc->uploaded_at)
                                <div class="fw-semibold">{{ $doc->uploaded_at->format('d F Y') }}</div>
                                <small class="text-muted">{{ $doc->uploaded_at->format('H:i') }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Action Buttons Footer -->
    @if(!$isSubmitted)
    <div class="card-footer bg-white border-top">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                @if(!$canSubmit)
                <small class="text-danger">
                    <i class="ti ti-alert-circle"></i>
                    Please upload all {{ $mandatoryPending }} mandatory document(s) before submitting
                </small>
                @else
                <small class="text-success">
                    <i class="ti ti-check-circle"></i>
                    All mandatory documents uploaded. Ready to submit!
                </small>
                @endif
            </div>
            <div class="d-flex gap-2">
                <button type="button" 
                        class="btn btn-outline-secondary" 
                        onclick="saveDraft({{ $part->id }})"
                        id="save-draft-btn">
                    <i class="ti ti-device-floppy"></i> Save Draft
                </button>
                <button type="button" 
                        class="btn btn-primary {{ !$canSubmit ? 'disabled' : '' }}" 
                        onclick="submitAllDocuments({{ $part->id }})"
                        id="submit-all-btn"
                        {{ !$canSubmit ? 'disabled' : '' }}>
                    <i class="ti ti-send"></i> Submit All Documents
                </button>
            </div>
        </div>
    </div>
    @else
    <div class="card-footer bg-light border-top">
        <div class="text-center">
            <i class="ti ti-check-circle text-success" style="font-size: 24px;"></i>
            <div class="mt-2">
                <strong>Documents submitted successfully!</strong>
                <p class="text-muted mb-0 small">
                    Your documents are now under review by Akebono team.
                </p>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
/* Enhanced button styles for view and download actions */
.btn-group .btn {
    transition: all 0.3s ease;
}

.btn-group .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* File info card styling */
.bg-light.border.rounded {
    transition: all 0.2s ease;
}

.bg-light.border.rounded:hover {
    background-color: #e9ecef !important;
    border-color: #adb5bd !important;
}

/* Action button styling */
td .btn-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    border: none;
    transition: all 0.3s ease;
}

td .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(13, 110, 253, 0.3);
}

td .btn-success {
    background: linear-gradient(135deg, #198754 0%, #146c43 100%);
    border: none;
    transition: all 0.3s ease;
}

td .btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(25, 135, 84, 0.3);
}

td .btn-warning {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    border: none;
    color: #000;
    transition: all 0.3s ease;
}

td .btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(255, 193, 7, 0.3);
}

/* Icon size adjustments */
.ti-file-check {
    font-size: 1.5rem;
}

.ti-eye, .ti-download, .ti-refresh {
    font-size: 1rem;
}

/* File upload button */
.btn-primary.d-flex {
    padding: 0.75rem 1rem;
    font-weight: 500;
}
</style>
@endpush

@push('scripts')
<script>
function saveDraft(partId) {
    const btn = document.getElementById('save-draft-btn');
    const originalHtml = btn.innerHTML;
    
    // Disable button and show loading
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
    
    // Send AJAX request
    fetch(`/supplier/part/${partId}/save-draft`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            part_id: partId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert('success', 'Draft saved successfully!', data.message);
            
            // Update last saved time if provided
            if (data.last_saved_at) {
                const lastSavedElement = document.querySelector('.card-header small');
                if (lastSavedElement) {
                    lastSavedElement.innerHTML = `<span class="badge bg-warning text-dark"><i class="ti ti-clock"></i> Draft</span> - Last saved: ${data.last_saved_at}`;
                }
            }
        } else {
            showAlert('danger', 'Failed to save draft', data.message || 'An error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Failed to save draft', 'Network error. Please try again.');
    })
    .finally(() => {
        // Re-enable button
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

function submitAllDocuments(partId) {
    // Show confirmation dialog
    if (!confirm('Are you sure you want to submit these documents for approval?\n\nOnce submitted, you will not be able to upload or modify documents unless they are returned for revision.')) {
        return;
    }
    
    const btn = document.getElementById('submit-all-btn');
    const originalHtml = btn.innerHTML;
    
    // Disable button and show loading
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';
    
    // Send AJAX request
    fetch(`/supplier/part/${partId}/submit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            part_id: partId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert('success', 'Documents submitted successfully!', data.message);
            
            // Reload page after 2 seconds to show updated status
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showAlert('danger', 'Failed to submit documents', data.message || 'An error occurred');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Failed to submit documents', 'Network error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

function showAlert(type, title, message) {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
    alert.innerHTML = `
        <strong><i class="ti ti-${type === 'success' ? 'check' : 'alert'}-circle"></i> ${title}</strong><br>
        <small>${message}</small>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alert);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}
</script>
@endpush

@else
<div class="text-center py-5">
    <i class="ti ti-folder-off" style="font-size: 64px; color: #ccc;"></i>
    <h5 class="mt-3 text-muted">No Part Selected</h5>
    <p class="text-muted">Please select a part to view and manage documents.</p>
</div>
@endif

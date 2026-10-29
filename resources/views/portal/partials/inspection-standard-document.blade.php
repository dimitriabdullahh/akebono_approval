{{-- 
    Component untuk menampilkan Inspection Standard document di portal supplier
    Props: $doc (RequestDocument), $token
--}}

@php
    // SINGLE SOURCE OF TRUTH: Ambil status dari tabel inspection_standards
    $inspectionStandard = $doc->inspectionStandard;
    if (!$inspectionStandard) {
        $inspectionStandard = \App\Models\InspectionStandard::where('request_document_id', $doc->id)->first();
    }
    // Status diambil dari inspection_standards.status, bukan request_documents.status
    $currentStatus = $inspectionStandard?->status ?? 'PENDING';
@endphp

<div class="card mb-3 document-card inspection-standard-card" data-status="{{ $currentStatus }}">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        @if($doc->documentType->is_mandatory)
                            <div class="bg-danger bg-opacity-10 rounded-circle p-2">
                                <i class="ti ti-star text-danger"></i>
                            </div>
                        @else
                            <div class="bg-secondary bg-opacity-10 rounded-circle p-2">
                                <i class="ti ti-file text-secondary"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-2 d-flex align-items-center gap-2">
                            {{ $doc->documentType->name }}
                            @if($doc->documentType->is_mandatory)
                                <span style="color: #DC2626; font-size: 1.2em; font-weight: bold;">*</span>
                            @endif
                        </h6>

                        @if($doc->documentType->scope)
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="ti ti-tag me-1"></i>Scope: {{ implode(', ', $doc->documentType->scope) }}
                                </small>
                            </div>
                        @endif

                        {{-- Status Badges berdasarkan inspection_standards.status --}}
                        <div class="d-flex align-items-center gap-2 mb-3">
                            @if($doc->documentType->is_mandatory)
                                <span class="badge bg-danger">MANDATORY</span>
                            @else
                                <span class="badge bg-secondary">OPTIONAL</span>
                            @endif

                            @if($currentStatus === 'DRAFT')
                                <span class="badge bg-secondary">DRAFT</span>
                            @elseif($currentStatus === 'PENDING')
                                <span class="badge bg-warning">PENDING</span>
                            @elseif($currentStatus === 'SUBMITTED' || $currentStatus === 'PENDING_REVIEW')
                                <span class="badge bg-info">SUBMITTED</span>
                            @elseif($currentStatus === 'APPROVED')
                                <span class="badge bg-success">APPROVED</span>
                            @elseif($currentStatus === 'REVISE' || $currentStatus === 'REJECTED')
                                <span class="badge bg-danger">REVISE REQUIRED</span>
                            @endif
                        </div>

                        {{-- Description --}}
                        <p class="text-muted small mb-0">
                            {{ $doc->documentType->description ?? 'Inspection Standard Document' }}
                        </p>

                        {{-- Revision Alert --}}
                        @if($doc->status === 'REVISE')
                        <div class="alert alert-warning mt-3 mb-0 py-2">
                            <i class="ti ti-alert-circle me-2"></i>
                            <strong>Revisi Diminta:</strong> {{ Str::limit($doc->revision_note, 200) }}
                            @if($doc->revision_deadline_at)
                                <br>
                                <small>Deadline: {{ $doc->revision_deadline_at->format('d M Y H:i') }}</small>
                            @endif
                        </div>
                        @endif

                        {{-- Submission Info --}}
                        @if(in_array($currentStatus, ['SUBMITTED', 'PENDING_REVIEW', 'APPROVED']))
                        <div class="alert alert-success mt-3 mb-0 py-2">
                            <i class="ti ti-check-circle me-2"></i>
                            <strong>Status: {{ $currentStatus === 'APPROVED' ? 'DISETUJUI' : 'DISUBMIT' }}</strong>
                            <br>
                            <small>Disubmit: {{ $inspectionStandard?->submitted_at?->format('d M Y H:i') ?? '-' }}</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                {{-- Action Buttons berdasarkan status dari inspection_standards --}}
                @if(in_array($currentStatus, ['DRAFT', 'PENDING', 'REVISE', 'REJECTED']))
                    {{-- Draft, Pending, or revision required - show form button --}}
                    <div class="d-grid gap-2">
                        @if($currentStatus === 'DRAFT')
                            <span class="badge bg-secondary mb-2 text-center">Draft in Progress</span>
                        @endif
                        @php
                            $buttonText = $currentStatus === 'DRAFT' ? 'Lanjutkan Form' : 'Isi Form Terlebih Dahulu';
                        @endphp
                        <a href="{{ route('inspection-standard.form', $token) }}" class="btn btn-primary btn-lg">
                            <i class="ti ti-pencil me-2"></i>{{ $buttonText }}
                        </a>
                    </div>

                @elseif(in_array($currentStatus, ['SUBMITTED', 'PENDING_REVIEW', 'APPROVED']))
                    {{-- Submitted or Approved - show PDF download/view --}}
                    @if($inspectionStandard && $inspectionStandard->pdf_file_path)
                    <div class="d-grid gap-2">
                        <div class="alert alert-info p-2 mb-2">
                            <small class="d-block mb-2">
                                <i class="ti ti-calendar me-1"></i>
                                <strong>{{ $inspectionStandard->submitted_at?->format('d M Y') ?? '-' }}</strong>
                            </small>
                            <small class="d-block">
                                <i class="ti ti-clock me-1"></i>
                                {{ $inspectionStandard->submitted_at?->format('H:i') ?? '-' }}
                            </small>
                        </div>
                        <a href="{{ route('inspection-standard.pdf', $inspectionStandard->id) }}" 
                           class="btn btn-outline-primary" 
                           target="_blank">
                            <i class="ti ti-eye me-2"></i>View
                        </a>
                        <a href="{{ route('inspection-standard.download', $inspectionStandard->id) }}" 
                           class="btn btn-success">
                            <i class="ti ti-download me-2"></i>Download PDF
                        </a>
                        @if(in_array($currentStatus, ['REVISE', 'REJECTED', 'SUBMITTED', 'PENDING_REVIEW']))
                        <a href="{{ route('inspection-standard.form', $token) }}" class="btn btn-outline-warning">
                            <i class="ti ti-pencil me-2"></i>Edit Form
                        </a>
                        @endif
                    </div>
                    @else
                    <div class="alert alert-info">
                        <small>PDF is being generated. Please refresh the page.</small>
                    </div>
                    @endif

                @endif
            </div>
        </div>
    </div>
</div>

<style>
.inspection-standard-card {
    border-left: 4px solid #0d6efd;
    transition: all 0.3s ease;
}

.inspection-standard-card[data-status="PENDING"] {
    border-left-color: #ffc107;
}

.inspection-standard-card[data-status="DRAFT"] {
    border-left-color: #6c757d;
}

.inspection-standard-card[data-status="REVISE"] {
    border-left-color: #dc3545;
}

.inspection-standard-card[data-status="SUBMITTED"] {
    border-left-color: #17a2b8;
}

.inspection-standard-card[data-status="APPROVED"] {
    border-left-color: #28a745;
}
</style>

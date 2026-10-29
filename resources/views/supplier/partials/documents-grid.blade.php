@php
    // Flatten all documents and sort by document type order
    $allDocuments = collect();
    foreach(['PENDING', 'REVISE', 'SUBMITTED', 'APPROVED'] as $status) {
        if($documents->has($status)) {
            $allDocuments = $allDocuments->merge($documents[$status]);
        }
    }
    // Sort by document_type order field and reset keys
    $allDocuments = $allDocuments->sortBy(function($doc) {
        return $doc->documentType->order ?? 999;
    })->values(); // Reset array keys after sorting
    
    // Count by status for header
    $pendingCount = $allDocuments->where('status', 'PENDING')->count();
@endphp

@if($allDocuments->isNotEmpty())
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="ti ti-files"></i> Documents 
            @if($pendingCount > 0)
                <span class="badge bg-warning text-dark ms-2">{{ $pendingCount }} Pending</span>
            @endif
        </h5>
        @if(isset($part) && $allDocuments->where('status', 'APPROVED')->count() > 0)
        <a href="{{ route('supplier.download.all', $part->document_request_id) }}" class="btn btn-success btn-sm">
            <i class="ti ti-download"></i> Download All Approved
        </a>
        @endif
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($allDocuments as $doc)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border 
                    {{ $doc->status === 'APPROVED' ? 'border-success' : '' }}
                    {{ $doc->status === 'REVISE' ? 'border-danger' : '' }}
                    {{ $doc->status === 'SUBMITTED' ? 'border-success' : '' }}">
                    <div class="card-body">
                        <!-- Header with title and mandatory badge -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0 flex-grow-1">{{ $doc->documentType->name }}</h6>
                            @if($doc->documentType->is_mandatory)
                                <span class="badge bg-danger badge-sm ms-2">Mandatory</span>
                            @else
                                <span class="badge bg-secondary badge-sm ms-2">Optional</span>
                            @endif
                        </div>

                        <!-- Status Badge -->
                        <div class="mb-2">
                            @if($doc->status === 'PENDING')
                                <span class="badge bg-warning text-dark w-100">
                                    <i class="ti ti-clock"></i> Pending Upload
                                </span>
                            @elseif($doc->status === 'SUBMITTED')
                                <span class="badge bg-success w-100">
                                    <i class="ti ti-clock-check"></i> Under Review
                                </span>
                            @elseif($doc->status === 'REVISE')
                                <span class="badge bg-danger w-100">
                                    <i class="ti ti-alert-circle"></i> Revision Required
                                </span>
                            @elseif($doc->status === 'APPROVED')
                                <span class="badge bg-success w-100">
                                    <i class="ti ti-check-circle"></i> Approved
                                </span>
                            @endif
                        </div>

                        <!-- Description -->
                        <p class="text-muted small mb-2">{{ $doc->documentType->description ?? 'No description' }}</p>

                        <!-- File info if uploaded -->
                        @if($doc->file_path)
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="ti ti-file"></i> {{ Str::limit($doc->original_filename ?? 'Document', 30) }}
                            </small>
                        </div>
                        @endif

                        <!-- Uploaded/Approved date -->
                        @if($doc->uploaded_at)
                        <small class="text-muted d-block mb-2">
                            <i class="ti ti-calendar"></i> 
                            @if($doc->status === 'APPROVED' && $doc->reviewed_at)
                                Approved: {{ $doc->reviewed_at->format('d M Y') }}
                            @else
                                Uploaded: {{ $doc->uploaded_at->format('d M Y') }}
                            @endif
                        </small>
                        @endif

                        <!-- Revision note if needed -->
                        @if($doc->status === 'REVISE' && $doc->revision_note)
                        <div class="alert alert-danger small mb-2 p-2">
                            <strong>Revision:</strong><br>
                            {{ Str::limit($doc->revision_note, 100) }}
                        </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 mt-auto">
                            @php
                                $docCode = $doc->documentType->code ?? '';
                                // Hide upload button for these document types (form-based, coming soon)
                                $hideUploadButton = in_array($docCode, ['XR_CONTROL_CHART', 'N1_DATA']);
                            @endphp
                            
                            @if($hideUploadButton && $doc->status === 'PENDING')
                                <!-- Hidden for form-based documents -->
                                <div class="text-center py-2">
                                    <i class="ti ti-clock text-muted"></i>
                                    <div class="text-muted small">Coming Soon</div>
                                </div>
                            @elseif($doc->status === 'PENDING')
                                <a href="{{ route('supplier.upload.form', $doc->id) }}" class="btn btn-primary btn-sm">
                                    <i class="ti ti-upload"></i> Upload Document
                                </a>
                            @elseif($doc->status === 'REVISE')
                                @if(!$hideUploadButton)
                                <a href="{{ route('supplier.upload.form', $doc->id) }}" class="btn btn-danger btn-sm">
                                    <i class="ti ti-upload"></i> Re-upload
                                </a>
                                @endif
                                @if($doc->file_path)
                                <a href="{{ route('supplier.view', $doc->id) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                                    <i class="ti ti-eye"></i> View PDF
                                </a>
                                <a href="{{ route('supplier.download', $doc->id) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="ti ti-download"></i> Download
                                </a>
                                @else
                                <a href="{{ route('supplier.document', $doc->id) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="ti ti-file-search"></i> View Details
                                </a>
                                @endif
                            @elseif($doc->status === 'SUBMITTED')
                                @if($doc->file_path)
                                <a href="{{ route('supplier.view', $doc->id) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                                    <i class="ti ti-eye"></i> View PDF
                                </a>
                                <a href="{{ route('supplier.download', $doc->id) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="ti ti-download"></i> Download
                                </a>
                                @else
                                <a href="{{ route('supplier.document', $doc->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="ti ti-file-search"></i> View Details
                                </a>
                                @endif
                            @elseif($doc->status === 'APPROVED')
                                @if($doc->file_path)
                                <a href="{{ route('supplier.view', $doc->id) }}" class="btn btn-outline-success btn-sm" target="_blank">
                                    <i class="ti ti-eye"></i> View PDF
                                </a>
                                <a href="{{ route('supplier.download', $doc->id) }}" class="btn btn-success btn-sm">
                                    <i class="ti ti-download"></i> Download
                                </a>
                                @else
                                <a href="{{ route('supplier.document', $doc->id) }}" class="btn btn-outline-success btn-sm">
                                    <i class="ti ti-file-search"></i> View Details
                                </a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@else
<div class="text-center py-5">
    <i class="ti ti-folder-off" style="font-size: 64px; color: #ccc;"></i>
    <h5 class="mt-3 text-muted">No documents available</h5>
    <p class="text-muted">Documents will appear here once they are added to this part.</p>
</div>
@endif

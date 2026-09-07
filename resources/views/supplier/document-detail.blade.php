@extends('layouts.app')

@section('title', 'Document Detail - ' . $document->documentType->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('supplier.request', $document->document_request_id) }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left"></i> Back to Request
        </a>
    </div>

    <!-- Document Info -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="ti ti-file-text"></i> {{ $document->documentType->name }}
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Document Type:</th>
                            <td>{{ $document->documentType->name }}</td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td>{{ $document->documentType->description ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($document->status === 'PENDING')
                                    <span class="badge bg-warning">Pending Upload</span>
                                @elseif($document->status === 'SUBMITTED')
                                    <span class="badge bg-info">Under Review</span>
                                @elseif($document->status === 'APPROVED')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($document->status === 'REVISE')
                                    <span class="badge bg-danger">Revision Required</span>
                                @endif
                            </td>
                        </tr>
                        @if($document->uploaded_at)
                        <tr>
                            <th>Uploaded At:</th>
                            <td>{{ $document->uploaded_at->format('d M Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($document->approved_at)
                        <tr>
                            <th>Approved At:</th>
                            <td>{{ $document->approved_at->format('d M Y H:i') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
                <div class="col-md-6">
                    @if($document->status === 'REVISE' && $document->revision_note)
                    <div class="alert alert-danger">
                        <h6 class="alert-heading">Revision Notes:</h6>
                        <p class="mb-0">{{ $document->revision_note }}</p>
                        @if($document->revision_deadline_at)
                        <hr>
                        <p class="mb-0 small">
                            <i class="ti ti-clock"></i> Deadline: {{ $document->revision_deadline_at->format('d M Y H:i') }}
                        </p>
                        @endif
                    </div>
                    @endif

                    @if($document->file_path)
                    <!-- File Information Card -->
                    <div class="card border-success mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <i class="ti ti-file-check text-success fs-2 me-2"></i>
                                <div>
                                    <div class="fw-semibold">File Uploaded</div>
                                    <small class="text-muted">{{ $document->original_filename ?? 'Document' }}</small>
                                </div>
                            </div>
                            @php
                                $extension = strtoupper(pathinfo($document->original_filename, PATHINFO_EXTENSION));
                            @endphp
                            <small class="text-muted">
                                {{ $extension }} File
                                @if($document->file_size)
                                    | {{ number_format($document->file_size / 1024, 2) }} KB
                                @endif
                            </small>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <a href="{{ route('supplier.view', $document->id) }}" 
                           class="btn btn-primary btn-lg"
                           target="_blank">
                            <i class="ti ti-eye fs-4"></i> 
                            <span class="ms-2">View Document</span>
                        </a>
                        <a href="{{ route('supplier.download', $document->id) }}" 
                           class="btn btn-success btn-lg">
                            <i class="ti ti-download fs-4"></i> 
                            <span class="ms-2">Download File</span>
                        </a>
                        @if(in_array($document->status, ['PENDING', 'REVISE']))
                        <a href="{{ route('supplier.upload.form', $document->id) }}" 
                           class="btn btn-warning btn-lg">
                            <i class="ti ti-refresh fs-4"></i> 
                            <span class="ms-2">Replace Document</span>
                        </a>
                        @endif
                    </div>
                    @elseif(in_array($document->status, ['PENDING', 'REVISE']))
                    <div class="card border-warning mb-3">
                        <div class="card-body text-center">
                            <i class="ti ti-file-upload text-warning fs-1"></i>
                            <div class="mt-2 text-muted">No file uploaded yet</div>
                        </div>
                    </div>
                    <div class="d-grid">
                        <a href="{{ route('supplier.upload.form', $document->id) }}" 
                           class="btn btn-primary btn-lg">
                            <i class="ti ti-upload fs-4"></i> 
                            <span class="ms-2">Upload Document</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Document Preview -->
    @if($document->file_path && \Storage::disk('public')->exists($document->file_path))
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="ti ti-eye"></i> Document Preview</h5>
            <button class="btn btn-sm btn-outline-secondary" onclick="toggleFullscreen()">
                <i class="ti ti-arrows-maximize"></i> Fullscreen
            </button>
        </div>
        <div class="card-body p-0">
            @php
                $fileExtension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
                $fileUrl = asset('storage/' . $document->file_path);
            @endphp

            @if(in_array($fileExtension, ['pdf']))
                {{-- PDF Viewer --}}
                <div id="pdf-container">
                    @if(file_exists(public_path('storage/' . $document->file_path)))
                        <iframe 
                            id="pdf-viewer"
                            src="{{ $fileUrl }}#toolbar=1&navpanes=1&scrollbar=1" 
                            style="width: 100%; height: 800px; border: none;"
                            type="application/pdf">
                        </iframe>
                        <div class="p-3 text-center bg-light border-top">
                            <p class="mb-2 text-muted small">Having trouble viewing the PDF?</p>
                            <a href="{{ route('supplier.download', $document->id) }}" class="btn btn-primary btn-sm">
                                <i class="ti ti-download"></i> Download PDF
                            </a>
                        </div>
                    @else
                        <div class="alert alert-danger m-3">
                            <i class="ti ti-alert-circle"></i>
                            <strong>File Not Found</strong><br>
                            The PDF file could not be found on the server.<br>
                            <small class="text-muted">Path: {{ $document->file_path }}</small>
                        </div>
                    @endif
                </div>
            @elseif(in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                {{-- Image Viewer --}}
                <div class="text-center p-3">
                    <img src="{{ $fileUrl }}" 
                         alt="{{ $document->documentType->name }}" 
                         class="img-fluid"
                         style="max-height: 800px;">
                </div>
            @elseif(in_array($fileExtension, ['doc', 'docx']))
                {{-- Office Document Viewer using Google Docs Viewer --}}
                <div id="office-container">
                    <iframe 
                        src="https://docs.google.com/viewer?url={{ urlencode(url($fileUrl)) }}&embedded=true" 
                        style="width: 100%; height: 800px; border: none;">
                        <p class="p-3">Unable to display document. 
                            <a href="{{ route('supplier.download', $document->id) }}">Download the file</a> instead.
                        </p>
                    </iframe>
                </div>
            @elseif(in_array($fileExtension, ['xls', 'xlsx']))
                {{-- Excel Document Info --}}
                <div class="alert alert-info m-3">
                    <i class="ti ti-info-circle"></i>
                    <strong>Excel File Detected</strong><br>
                    Preview not available for Excel files. Please download to view.
                    <a href="{{ route('supplier.download', $document->id) }}" class="btn btn-sm btn-primary ms-2">
                        <i class="ti ti-download"></i> Download
                    </a>
                </div>
            @else
                {{-- Unsupported file type --}}
                <div class="alert alert-warning m-3">
                    <i class="ti ti-alert-triangle"></i>
                    <strong>Preview Not Available</strong><br>
                    This file type ({{ strtoupper($fileExtension) }}) cannot be previewed in browser.
                    <a href="{{ route('supplier.download', $document->id) }}" class="btn btn-sm btn-primary ms-2">
                        <i class="ti ti-download"></i> Download File
                    </a>
                </div>
            @endif
        </div>
    </div>
    @elseif($document->file_path)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="alert alert-warning mb-0">
                <i class="ti ti-alert-circle"></i> File not found on server. Please contact administrator.
            </div>
        </div>
    </div>
    @endif

    <!-- Document History -->
    @if($document->histories->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="ti ti-history"></i> Document History</h5>
        </div>
        <div class="card-body">
            <div class="timeline">
                @foreach($document->histories->sortByDesc('created_at') as $history)
                <div class="timeline-item mb-3">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <div class="timeline-icon 
                                @if($history->action === 'UPLOADED' || $history->action === 'RESUBMITTED') bg-primary
                                @elseif($history->action === 'APPROVED') bg-success
                                @elseif($history->action === 'REJECTED') bg-danger
                                @else bg-secondary
                                @endif
                                rounded-circle p-2">
                                <i class="ti 
                                    @if($history->action === 'UPLOADED' || $history->action === 'RESUBMITTED') ti-upload
                                    @elseif($history->action === 'APPROVED') ti-check
                                    @elseif($history->action === 'REJECTED') ti-x
                                    @else ti-point
                                    @endif
                                    text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="card border">
                                <div class="card-body p-3">
                                    <h6 class="mb-1">{{ ucfirst(str_replace('_', ' ', $history->action)) }}</h6>
                                    <p class="mb-1 small">{{ $history->note }}</p>
                                    <p class="mb-0 text-muted small">
                                        <i class="ti ti-clock"></i> {{ $history->created_at->format('d M Y H:i') }}
                                        @if($history->actor)
                                            | <i class="ti ti-user"></i> {{ $history->actor->name }}
                                        @else
                                            | <i class="ti ti-building"></i> {{ $history->actor_name }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
.timeline-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Fullscreen styles */
#pdf-container.fullscreen,
#office-container.fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 9999;
    background: white;
}

#pdf-container.fullscreen iframe,
#office-container.fullscreen iframe {
    height: 100vh !important;
}

/* Enhanced action button styles */
.btn-lg {
    padding: 1rem 1.5rem;
    font-size: 1.1rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-lg:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
}

.btn-lg i {
    vertical-align: middle;
}

.btn-primary.btn-lg {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    border: none;
}

.btn-primary.btn-lg:hover {
    box-shadow: 0 8px 16px rgba(13, 110, 253, 0.3);
}

.btn-success.btn-lg {
    background: linear-gradient(135deg, #198754 0%, #146c43 100%);
    border: none;
}

.btn-success.btn-lg:hover {
    box-shadow: 0 8px 16px rgba(25, 135, 84, 0.3);
}

.btn-warning.btn-lg {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    border: none;
    color: #000;
}

.btn-warning.btn-lg:hover {
    box-shadow: 0 8px 16px rgba(255, 193, 7, 0.3);
}

/* File info card animation */
.card.border-success {
    transition: all 0.3s ease;
}

.card.border-success:hover {
    box-shadow: 0 4px 12px rgba(25, 135, 84, 0.2);
}

/* Icon animations */
@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

.btn-lg:hover i {
    animation: pulse 0.5s ease;
}
</style>
@endpush

@push('scripts')
<script>
function toggleFullscreen() {
    const container = document.getElementById('pdf-container') || document.getElementById('office-container');
    if (!container) return;
    
    if (container.classList.contains('fullscreen')) {
        container.classList.remove('fullscreen');
        // Exit browser fullscreen if active
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    } else {
        container.classList.add('fullscreen');
        // Enter browser fullscreen
        if (container.requestFullscreen) {
            container.requestFullscreen();
        } else if (container.webkitRequestFullscreen) {
            container.webkitRequestFullscreen();
        } else if (container.msRequestFullscreen) {
            container.msRequestFullscreen();
        }
    }
}

// Handle fullscreen exit via ESC key
document.addEventListener('fullscreenchange', function() {
    const container = document.getElementById('pdf-container') || document.getElementById('office-container');
    if (container && !document.fullscreenElement) {
        container.classList.remove('fullscreen');
    }
});

// Check if PDF iframe loads successfully
document.addEventListener('DOMContentLoaded', function() {
    const pdfViewer = document.getElementById('pdf-viewer');
    if (pdfViewer) {
        pdfViewer.addEventListener('error', function() {
            console.error('PDF iframe failed to load');
        });
        
        // Timeout to check if iframe loaded
        setTimeout(function() {
            try {
                const iframeDoc = pdfViewer.contentDocument || pdfViewer.contentWindow.document;
                if (!iframeDoc || iframeDoc.body.children.length === 0) {
                    console.warn('PDF may not be displaying correctly');
                }
            } catch(e) {
                // Cross-origin error is expected and normal
                console.log('PDF viewer active (cross-origin)');
            }
        }, 2000);
    }
});
</script>
@endpush
@endsection

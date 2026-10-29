@extends('layouts.app')

@section('title', $documentType . ' Preview')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="mb-4">
        <h4 class="mb-1">{{ $documentType }} @if($isMandatory)<span class="text-danger">*</span>@endif</h4>
        <p class="text-muted mb-0">
            <i class="ti ti-building me-1"></i>{{ $supplierName }} 
            @if($partInfo)
                • {{ $partInfo }}
            @endif
        </p>
    </div>

    {{-- Main Content: Side by Side Layout --}}
    <div class="row g-3">
        {{-- Left Side: PDF Viewer --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0"><i class="ti ti-eye me-2"></i>Pratinjau Dokumen</h5>
                </div>
                <div class="card-body p-0" style="background: #f5f5f5;">
                    {{-- PDF Viewer Controls --}}
                    <div class="d-flex justify-content-between align-items-center p-3 bg-white border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <button id="prev-page" class="btn btn-sm btn-outline-secondary" title="Previous Page">
                                <i class="ti ti-chevron-left"></i>
                            </button>
                            <span class="text-muted small">
                                Hal <input type="number" id="current-page" value="1" min="1" style="width: 50px; text-align: center;" class="form-control form-control-sm d-inline-block"> / <span id="total-pages">-</span>
                            </span>
                            <button id="next-page" class="btn btn-sm btn-outline-secondary" title="Next Page">
                                <i class="ti ti-chevron-right"></i>
                            </button>
                        </div>
                        
                        <div class="d-flex align-items-center gap-2">
                            <button id="zoom-out" class="btn btn-sm btn-outline-secondary" title="Zoom Out">
                                <i class="ti ti-zoom-out"></i>
                            </button>
                            <span class="text-muted small" id="zoom-level">100%</span>
                            <button id="zoom-in" class="btn btn-sm btn-outline-secondary" title="Zoom In">
                                <i class="ti ti-zoom-in"></i>
                            </button>
                            
                            <div class="vr mx-2"></div>
                            
                            <a href="{{ $pdfUrl }}" class="btn btn-success btn-sm" download>
                                <i class="ti ti-download me-1"></i>Download PDF
                            </a>
                        </div>
                    </div>

                    {{-- PDF Canvas Container --}}
                    <div class="pdf-viewer-container" style="min-height: 700px; max-height: 800px; overflow-y: auto; display: flex; justify-content: center; align-items: flex-start; padding: 20px;">
                        <canvas id="pdf-canvas" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1); background: white;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Side: Review & Approval --}}
        @if($hasReviewTab)
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0"><i class="ti ti-clipboard-check me-2"></i>Review & Approval</h5>
                </div>
                <div class="card-body" style="max-height: 900px; overflow-y: auto;">
                    @include('admin.review.partials.review-form', ['requestDocument' => $requestDocument])
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .pdf-viewer-container {
        background: #f5f5f5;
    }
    
    #pdf-canvas {
        max-width: 100%;
        height: auto;
    }
    
    #current-page {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 2px 4px;
    }
    
    #current-page:focus {
        outline: none;
        border-color: #0d6efd;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    // PDF.js configuration
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    
    let pdfDoc = null;
    let currentPage = 1;
    let totalPages = 0;
    let scale = 1.2; // Default zoom level
    let canvas = document.getElementById('pdf-canvas');
    let ctx = canvas.getContext('2d');
    
    // Load PDF
    const pdfUrl = '{{ $pdfUrl }}';
    
    console.log('Loading PDF from URL:', pdfUrl);
    
    pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
        console.log('PDF loaded successfully, pages:', pdf.numPages);
        pdfDoc = pdf;
        totalPages = pdf.numPages;
        document.getElementById('total-pages').textContent = totalPages;
        document.getElementById('current-page').max = totalPages;
        
        // Render first page
        renderPage(currentPage);
    }).catch(function(error) {
        console.error('Error loading PDF:', error);
        console.error('PDF URL was:', pdfUrl);
        
        // Show error message on page
        const container = document.querySelector('.pdf-viewer-container');
        container.innerHTML = '<div class="alert alert-danger m-3">Failed to load PDF: ' + error.message + '<br>URL: ' + pdfUrl + '</div>';
    });
    
    function renderPage(pageNum) {
        pdfDoc.getPage(pageNum).then(function(page) {
            const viewport = page.getViewport({ scale: scale });
            
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            
            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };
            
            page.render(renderContext).promise.then(function() {
                currentPage = pageNum;
                document.getElementById('current-page').value = currentPage;
                updateZoomDisplay();
            });
        });
    }
    
    function updateZoomDisplay() {
        const percentage = Math.round(scale * 100);
        document.getElementById('zoom-level').textContent = percentage + '%';
    }
    
    // Navigation controls
    document.getElementById('prev-page').addEventListener('click', function() {
        if (currentPage > 1) {
            renderPage(currentPage - 1);
        }
    });
    
    document.getElementById('next-page').addEventListener('click', function() {
        if (currentPage < totalPages) {
            renderPage(currentPage + 1);
        }
    });
    
    document.getElementById('current-page').addEventListener('change', function() {
        const pageNum = parseInt(this.value);
        if (pageNum >= 1 && pageNum <= totalPages) {
            renderPage(pageNum);
        } else {
            this.value = currentPage;
        }
    });
    
    // Zoom controls
    document.getElementById('zoom-in').addEventListener('click', function() {
        if (scale < 3) {
            scale += 0.2;
            renderPage(currentPage);
        }
    });
    
    document.getElementById('zoom-out').addEventListener('click', function() {
        if (scale > 0.5) {
            scale -= 0.2;
            renderPage(currentPage);
        }
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft' && currentPage > 1) {
            renderPage(currentPage - 1);
        } else if (e.key === 'ArrowRight' && currentPage < totalPages) {
            renderPage(currentPage + 1);
        } else if (e.key === '+' || e.key === '=') {
            document.getElementById('zoom-in').click();
        } else if (e.key === '-') {
            document.getElementById('zoom-out').click();
        }
    });
</script>
@endpush
@endsection

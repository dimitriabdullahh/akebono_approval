@extends('layouts.app')

@section('title', 'Upload Document - ' . $document->documentType->name)

@section('content')
<div class="container py-4">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('supplier.request', $document->document_request_id) }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left"></i> Back to Request
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Document Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-3">{{ $document->documentType->name }}</h5>
                    <p class="text-muted">{{ $document->documentType->description ?? 'No description available' }}</p>
                    
                    @if($document->status === 'REJECTED')
                    <div class="alert alert-danger">
                        <h6 class="alert-heading"><i class="ti ti-alert-circle"></i> Revision Required</h6>
                        <p class="mb-0">{{ $document->review_notes }}</p>
                        @if($document->revision_deadline_at)
                        <hr>
                        <p class="mb-0 small">
                            <i class="ti ti-clock"></i> Deadline: {{ $document->revision_deadline_at->format('d M Y H:i') }}
                        </p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Upload Form -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="ti ti-upload"></i> 
                        @if($document->file_path)
                            Re-upload Document
                        @else
                            Upload Document
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('supplier.upload.submit', $document->id) }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="file" class="form-label">Select File <span class="text-danger">*</span></label>
                            <input type="file" 
                                   class="form-control @error('file') is-invalid @enderror" 
                                   id="file" 
                                   name="file" 
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                                   required>
                            <div class="form-text">
                                Allowed formats: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max: 10MB)
                            </div>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      maxlength="500"
                                      placeholder="Add any notes or comments about this document...">{{ old('notes') }}</textarea>
                            <div class="form-text">Maximum 500 characters</div>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($document->file_path)
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle"></i> 
                            <strong>Current File:</strong> This will replace the existing file.
                            <a href="{{ route('supplier.download', $document->id) }}" class="alert-link">Download current file</a>
                        </div>
                        @endif

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="ti ti-upload"></i> Upload Document
                            </button>
                            <a href="{{ route('supplier.request', $document->document_request_id) }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
});

// File size validation
document.getElementById('file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (file.size > maxSize) {
            alert('File size exceeds 10MB. Please select a smaller file.');
            e.target.value = '';
        }
    }
});
</script>
@endpush
@endsection

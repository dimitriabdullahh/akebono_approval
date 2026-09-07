@extends('layouts.supplier')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <a href="{{ route('supplier.documentDetail', [$token, $document->id]) }}" class="text-decoration-none">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    Preview Dokumen
                </h2>
            </div>

            <!-- Document Info -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        {{ $document->documentType->name }}
                        @if($document->documentType->is_mandatory)
                            <span style="color: #DC2626; font-size: 1em; font-weight: bold;">*</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ $document->status === 'APPROVED' ? 'success' : 'warning' }}">
                                    {{ $document->status }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Upload Date:</strong> {{ $document->uploaded_at ? $document->uploaded_at->format('d M Y H:i') : 'Belum diupload' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Document Preview -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Preview File</h5>
                </div>
                <div class="card-body" style="min-height: 600px;">
                    @if ($document->file_path)
                        @php
                            $filePath = $document->file_path;
                            $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                        @endphp

                        @if (in_array($fileExtension, ['pdf']))
                            <!-- PDF Preview -->
                            <iframe 
                                src="{{ route('supplier.serveDocument', [$token, $document->id]) }}" 
                                style="width: 100%; height: 600px; border: none;">
                            </iframe>
                        @elseif (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif']))
                            <!-- Image Preview -->
                            <div class="text-center">
                                <img 
                                    src="{{ route('supplier.serveDocument', [$token, $document->id]) }}" 
                                    alt="Preview" 
                                    style="max-width: 100%; max-height: 600px; object-fit: contain;">
                            </div>
                        @else
                            <!-- File Type Not Supported -->
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-info-circle"></i>
                                Preview tidak tersedia untuk file type ini ({{ strtoupper($fileExtension) }})
                            </div>
                            <a href="{{ route('supplier.downloadDocument', [$token, $document->id]) }}" class="btn btn-primary">
                                <i class="fas fa-download"></i> Download File
                            </a>
                        @endif
                    @else
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-warning"></i>
                            File belum diupload
                        </div>
                    @endif
                </div>
            </div>

            <!-- Document History -->
            @if ($document->documentHistory && $document->documentHistory->count() > 0)
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Riwayat Perubahan</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach ($document->documentHistory as $history)
                        <div class="timeline-item mb-3">
                            <div class="timeline-marker">
                                <i class="fas fa-circle text-primary"></i>
                            </div>
                            <div class="timeline-content">
                                <strong>{{ $history->action }}</strong>
                                <p class="text-muted mb-0">{{ $history->created_at->format('d M Y H:i') }}</p>
                                @if ($history->notes)
                                <p class="text-muted mb-0">{{ $history->notes }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="mt-4">
                <a href="{{ route('supplier.tracking', $token) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Tracking
                </a>
                @if ($document->status === 'APPROVED' && $document->file_path)
                <a href="{{ route('supplier.downloadDocument', [$token, $document->id]) }}" class="btn btn-primary">
                    <i class="fas fa-download"></i> Download
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline-item {
        position: relative;
    }

    .timeline-marker {
        position: absolute;
        left: -30px;
        top: 0;
    }

    .timeline-content {
        padding: 10px;
        border-left: 2px solid #dee2e6;
        padding-left: 15px;
    }
</style>
@endsection

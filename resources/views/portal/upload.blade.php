@extends('layouts.supplier')

@section('title', 'Portal Upload Dokumen Supplier')

@section('content')
<div class="container py-4 animate-on-scroll">
    <!-- Enhanced Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-3">
                                <i class="ti ti-building me-2"></i>Portal Upload Dokumen Supplier
                            </h2>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="ti ti-building-store me-2"></i>
                                        <div>
                                            <strong>Supplier:</strong> {{ $request->supplier->name }}
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="ti ti-user me-2"></i>
                                        <div>
                                            <strong>PIC:</strong> {{ $request->supplier->pic_name ?? 'Tidak Diketahui' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="ti ti-tag me-2"></i>
                                        <div>
                                            <strong>Model:</strong> {{ $request->model }}
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="ti ti-calendar me-2"></i>
                                        <div>
                                            <strong>Due Date:</strong> {{ \Carbon\Carbon::parse($request->due_date)->format('d M Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info mb-0 bg-white bg-opacity-20 border-0">
                                <i class="ti ti-info-circle me-2"></i>
                                <strong>Informasi:</strong> Silakan unggah seluruh dokumen yang diminta di bawah. Dokumen yang ditandai dengan <span class="badge bg-danger ms-1">WAJIB</span> harus diunggah sebelum submit.
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                                <div class="mb-2">
                                    <span class="display-6 font-bold">{{ $progress }}%</span>
                                </div>
                                <div class="progress mb-2" style="height: 12px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small>Progress Kelengkapan Dokumen</small>
                                <div class="mt-2">
                                    <small class="text-white-50">{{ $totalSubmitted }} / {{ $totalMandatory }} Dokumen Mandatory</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Box untuk Multiple Parts -->
    @if($request->hasMultipleParts())
    <div class="alert alert-info border-start border-4 border-info mb-4" style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);">
        <div class="d-flex align-items-start">
            <div class="me-3">
                <i class="ti ti-info-circle" style="font-size: 2rem; color: #0284c7;"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="mb-2" style="color: #0c4a6e;">
                    <strong>Request Multi-Part</strong>
                </h5>
                <p class="mb-2" style="color: #075985;">
                    Request ini mencakup <strong>{{ $request->requestParts()->count() }} part</strong>. 
                    Silakan upload dokumen untuk setiap part di bawah ini. Button <strong>Save Draft</strong> dan <strong>Submit All Documents</strong> berada di bagian paling bawah setelah semua part.
                </p>
                <div class="row">
                    @foreach($request->requestParts as $idx => $part)
                        <div class="col-md-6 mb-2">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-2">Part {{ $idx + 1 }}</span>
                                <strong style="color: #075985;">{{ $part->part_name }}</strong>
                                <span class="ms-2 text-muted small">({{ $part->part_number }})</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Enhanced Documents Upload Form -->
    <form id="uploadForm" method="POST" enctype="multipart/form-data">
        @csrf
        
        @if($request->hasMultipleParts())
            <!-- Debug Info -->
            @php
                $allParts = $request->requestParts;
            @endphp
            
            <!-- Display documents grouped by parts -->
            @foreach($request->requestParts as $partIndex => $part)
            
            <!-- Part Header Separator -->
            <div class="part-separator mb-4 mt-4"></div>
            
            <div class="card mb-4 part-section" style="border-left: 5px solid #3b82f6; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);">
                <div class="card-header" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-white">
                            <i class="ti ti-components me-2"></i>
                            Part {{ $partIndex + 1 }}: {{ $part->part_name }}
                        </h4>
                        <span class="badge bg-white text-primary fs-6">{{ $part->part_number }}</span>
                    </div>
                    <div class="row mt-3 text-white-50">
                        <div class="col-md-4">
                            <small><i class="ti ti-tag me-1"></i>Model: {{ $request->model }}</small>
                        </div>
                        <div class="col-md-4">
                            <small><i class="ti ti-calendar me-1"></i>Due Date: {{ \Carbon\Carbon::parse($request->due_date)->format('d M Y') }}</small>
                        </div>
                        <div class="col-md-4">
                            @php
                                $partMandatory = $part->requestDocuments()->whereHas('documentType', function($q) {
                                    $q->where('is_mandatory', true);
                                })->count();
                                $partUploaded = $part->requestDocuments()->whereHas('documentType', function($q) {
                                    $q->where('is_mandatory', true);
                                })->whereNotNull('file_path')->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])->count();
                                $partProgress = $partMandatory > 0 ? (int)(($partUploaded / $partMandatory) * 100) : 0;
                            @endphp
                            <small><i class="ti ti-progress me-1"></i>Progress: {{ $partProgress }}% ({{ $partUploaded }}/{{ $partMandatory }})</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        // Group documents by status for this part
                        $partDocuments = $part->requestDocuments;
                        $partDocsByStatus = $partDocuments->groupBy(function ($doc) {
                            if ($doc->status === 'REVISE') return 'REVISE';
                            if ($doc->status === 'PENDING') return 'PENDING';
                            return 'SUBMITTED';
                        });
                    @endphp
                    
                    <!-- Document Submission Table -->
                    <h5 class="mb-3">
                        <i class="ti ti-file-text me-2"></i>Document Submission
                    </h5>
                    
                    @foreach(['REVISE', 'PENDING', 'SUBMITTED'] as $status)
                        @if(isset($partDocsByStatus[$status]) && count($partDocsByStatus[$status]) > 0)
                        <div class="mb-4">
                            <h6 class="mb-3">
                                @if($status === 'PENDING')
                                    <i class="ti ti-clock text-warning me-2"></i>Dokumen Menunggu Upload
                                @elseif($status === 'REVISE') 
                                    <i class="ti ti-alert-circle text-danger me-2"></i>Dokumen Perlu Revisi
                                @else
                                    <i class="ti ti-check-circle text-success me-2"></i>Dokumen Telah Disubmit
                                @endif
                            </h6>
                            
                            @foreach($partDocsByStatus[$status] as $doc)
                                {{-- Special handling for Inspection Standard documents --}}
                                @if($doc->documentType->code === 'IS')
                                    @include('portal.partials.inspection-standard-document', ['doc' => $doc, 'token' => $token])
                                {{-- Special handling for Form-based documents (PSW, PS) --}}
                                @elseif(in_array($doc->documentType->code, ['PSW', 'PACKAGING_STANDARD']))
                                    @include('portal.partials.form-document', ['doc' => $doc, 'token' => $token])
                                @else
                            <div class="card mb-3 document-card" data-status="{{ $doc->status }}">
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

                                                    @if($doc->status === 'REVISE')
                                                    <div class="revision-alert-card mb-2">
                                                        <div class="revision-header">
                                                            <div class="revision-icon">
                                                                <i class="ti ti-alert-triangle"></i>
                                                            </div>
                                                            <div class="revision-title">
                                                                <strong>Revisi Diminta</strong>
                                                                @if($doc->revision_deadline_at)
                                                                    <span class="revision-deadline">
                                                                        <i class="ti ti-clock"></i>
                                                                        {{ $doc->revision_deadline_at->format('d M Y H:i') }}
                                                                        <span class="deadline-status">
                                                                            ({{ $doc->revision_deadline_at->diffForHumans() }})
                                                                            @if($doc->is_deadline_passed)
                                                                                <span class="badge bg-danger ms-1">TERLAMPAUI</span>
                                                                            @endif
                                                                        </span>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="revision-content">
                                                            <div class="revision-note-wrapper">
                                                                <div class="revision-note-display">
                                                                    <p class="revision-note-preview mb-0">
                                                                        {{ Str::limit($doc->revision_note, 150) }}
                                                                    </p>
                                                                    <div class="revision-note-full" data-full-text="{{ $doc->revision_note }}" style="display: none;">
                                                                        <p class="mb-0">{{ $doc->revision_note }}</p>
                                                                    </div>
                                                                </div>
                                                                @if(strlen($doc->revision_note) > 150)
                                                                    <button type="button" class="show-more-toggle mt-3" onclick="toggleRevisionNote(this)" data-expanded="false">
                                                                        <span class="toggle-text">Lihat Selengkapnya</span>
                                                                        <i class="ti ti-chevron-down toggle-icon"></i>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    @if(in_array($doc->status, ['SUBMITTED', 'APPROVED']))
                                                    <div class="alert alert-success mb-2 py-2" style="border-left: 4px solid #10B981;">
                                                        <div class="d-flex align-items-start">
                                                            <i class="ti ti-check-circle me-2 mt-1 text-success"></i>
                                                            <div>
                                                                <strong>Status: {{ $doc->status === 'APPROVED' ? 'DISETUJUI' : 'DISUBMIT' }}</strong>
                                                                <p class="mb-0 small">
                                                                    File: <code>{{ $doc->original_filename }}</code><br>
                                                                    Diunggah: {{ $doc->uploaded_at?->format('d M Y H:i') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            @if($doc->status !== 'APPROVED')
                                            <div class="upload-container">
                                                <div class="upload-area border border-2 rounded-3 text-center position-relative" 
                                                     style="border-style: dashed !important; 
                                                            border-color: #3b82f6 !important; 
                                                            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); 
                                                            min-height: 160px; 
                                                            padding: 1.5rem;
                                                            cursor: pointer;
                                                            transition: all 0.3s ease;">
                                                    
                                                    <input type="file" 
                                                           name="documents[{{ $doc->id }}]" 
                                                           class="d-none file-input" 
                                                           id="file_{{ $doc->id }}" 
                                                           accept=".pdf,.jpg,.jpeg,.png"
                                                           data-doc-id="{{ $doc->id }}">
                                                    
                                                    <label for="file_{{ $doc->id }}" 
                                                           class="upload-label w-100 h-100 d-flex flex-column align-items-center justify-content-center position-absolute top-0 start-0"
                                                           style="cursor: pointer; margin: 0; padding: 1.5rem;">
                                                        <div class="upload-icon-wrapper">
                                                            <i class="ti ti-cloud-upload d-block mb-3" 
                                                               style="font-size: 3rem; 
                                                                      color: #1e40af; 
                                                                      line-height: 1;"></i>
                                                        </div>
                                                        <div class="upload-text-wrapper">
                                                            <p class="mb-2 fw-bold text-dark" 
                                                               style="font-size: 1rem; 
                                                                      color: #1f2937 !important;
                                                                      line-height: 1.4;">
                                                                Klik atau Drop File Di Sini
                                                            </p>
                                                            <small class="d-block text-secondary" 
                                                                   style="color: #6b7280 !important; 
                                                                          font-size: 0.85rem;">
                                                                PDF, JPG, atau PNG (Max 10MB)
                                                            </small>
                                                        </div>
                                                    </label>
                                                    
                                                    <div class="file-info-display position-absolute top-0 start-0 w-100 h-100 d-none flex-column align-items-center justify-content-center"
                                                         style="background-color: #d1fae5; padding: 1.5rem;">
                                                        <i class="ti ti-check-circle text-success mb-2" style="font-size: 2.5rem;"></i>
                                                        <div class="file-name text-success fw-semibold mb-1" style="font-size: 0.9rem;"></div>
                                                        <div class="file-size text-muted mb-2" style="font-size: 0.8rem;"></div>
                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-file-btn mt-2">
                                                            <i class="ti ti-x me-1"></i>Hapus File
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            @else
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('portal.download', [$token, $doc->id]) }}" class="btn btn-success flex-grow-1">
                                                    <i class="ti ti-download me-1"></i> Download
                                                </a>
                                                <a href="{{ route('portal.preview', [$token, $doc->id]) }}" class="btn btn-outline-primary" title="Preview" target="_blank">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                                @endif
                            @endforeach
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endforeach
            
        @else
            <!-- Display documents without parts (original flow for backward compatibility) -->
            @foreach($documentRequests as $status => $documents)
                @if(count($documents) > 0)
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3">
                            @if($status === 'revise')
                                <i class="ti ti-alert-circle text-danger me-2"></i>Dokumen Perlu Revisi
                            @else
                                <i class="ti ti-clock text-warning me-2"></i>Dokumen Menunggu Upload
                            @endif
                        </h5>
                    </div>
                </div>
                @endif
                
                @foreach($documents as $doc)
                {{-- Special handling for Inspection Standard documents --}}
                @if($doc->documentType->code === 'IS')
                    @include('portal.partials.inspection-standard-document', ['doc' => $doc, 'token' => $token])
                {{-- Special handling for Form-based documents (PSW, PS) --}}
                @elseif(in_array($doc->documentType->code, ['PSW', 'PACKAGING_STANDARD']))
                    @include('portal.partials.form-document', ['doc' => $doc, 'token' => $token])
                @else
                <div class="card mb-3 document-card" data-status="{{ $doc->status }}">
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

                                        @if($doc->status === 'REVISE')
                                        <div class="revision-alert-card mb-2">
                                            <div class="revision-header">
                                                <div class="revision-icon">
                                                    <i class="ti ti-alert-triangle"></i>
                                                </div>
                                                <div class="revision-title">
                                                    <strong>Revisi Diminta</strong>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        @if(in_array($doc->status, ['SUBMITTED', 'APPROVED']))
                                        <div class="alert alert-success mb-2 py-2">
                                            <div class="d-flex align-items-start">
                                                <i class="ti ti-check-circle me-2 mt-1 text-success"></i>
                                                <div>
                                                    <strong>Status: {{ $doc->status === 'APPROVED' ? 'DISETUJUI' : 'DISUBMIT' }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                @if($doc->status !== 'APPROVED')
                                <div class="upload-container">
                                    <div class="upload-area border border-2 rounded-3 text-center position-relative" 
                                         style="border-style: dashed !important; border-color: #3b82f6 !important; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); min-height: 160px; padding: 1.5rem; cursor: pointer;">
                                        
                                        <input type="file" name="documents[{{ $doc->id }}]" class="d-none file-input" id="file_{{ $doc->id }}" accept=".pdf,.jpg,.jpeg,.png" data-doc-id="{{ $doc->id }}">
                                        
                                        <label for="file_{{ $doc->id }}" class="upload-label w-100 h-100 d-flex flex-column align-items-center justify-content-center position-absolute top-0 start-0" style="cursor: pointer; margin: 0; padding: 1.5rem;">
                                            <i class="ti ti-cloud-upload d-block mb-3" style="font-size: 3rem; color: #1e40af;"></i>
                                            <p class="mb-2 fw-bold text-dark">Klik atau Drop File Di Sini</p>
                                            <small class="d-block text-secondary">PDF, JPG, atau PNG (Max 10MB)</small>
                                        </label>
                                        
                                        <div class="file-info-display position-absolute top-0 start-0 w-100 h-100 d-none flex-column align-items-center justify-content-center" style="background-color: #d1fae5; padding: 1.5rem;">
                                            <i class="ti ti-check-circle text-success mb-2" style="font-size: 2.5rem;"></i>
                                            <div class="file-name text-success fw-semibold mb-1"></div>
                                            <div class="file-size text-muted mb-2"></div>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-file-btn mt-2">
                                                <i class="ti ti-x me-1"></i>Hapus File
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="d-flex gap-2">
                                    <a href="{{ route('portal.download', [$token, $doc->id]) }}" class="btn btn-success flex-grow-1">
                                        <i class="ti ti-download me-1"></i> Download
                                    </a>
                                    <a href="{{ route('portal.preview', [$token, $doc->id]) }}" class="btn btn-outline-primary" title="Preview" target="_blank">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
            @endforeach
        @endif

        <!-- Total Progress Summary (for multi-part) -->
        @if($request->hasMultipleParts())
        <div class="card mt-4 mb-4" style="border: 2px solid #3b82f6; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);">
            <div class="card-body">
                <h5 class="mb-3">
                    <i class="ti ti-chart-bar me-2 text-primary"></i>Total Request Progress
                </h5>
                <div class="row">
                    <div class="col-md-8">
                        <h3 class="mb-2">{{ $totalSubmitted }} / {{ $totalMandatory }} Mandatory Documents Completed</h3>
                        <div class="progress mb-3" style="height: 25px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                <strong>{{ $progress }}%</strong>
                            </div>
                        </div>
                        
                        <!-- Per-Part Progress Breakdown -->
                        <div class="mt-3">
                            <h6 class="mb-2">Progress Per Part:</h6>
                            @foreach($request->requestParts as $idx => $part)
                                @php
                                    $partMandatory = $part->requestDocuments()->whereHas('documentType', function($q) {
                                        $q->where('is_mandatory', true);
                                    })->count();
                                    $partUploaded = $part->requestDocuments()->whereHas('documentType', function($q) {
                                        $q->where('is_mandatory', true);
                                    })->whereNotNull('file_path')->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])->count();
                                @endphp
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span><strong>Part {{ $idx + 1 }}:</strong> {{ $part->part_name }}</span>
                                        <span class="text-muted">{{ $partUploaded }} / {{ $partMandatory }}</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar {{ $partUploaded == $partMandatory ? 'bg-success' : 'bg-warning' }}" 
                                             style="width: {{ $partMandatory > 0 ? ($partUploaded / $partMandatory * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        @if($progress == 100)
                            <div class="alert alert-success mb-0">
                                <i class="ti ti-check-circle" style="font-size: 3rem;"></i>
                                <h5 class="mt-2 mb-0">Ready to Submit!</h5>
                                <small>Semua dokumen mandatory sudah lengkap</small>
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="ti ti-alert-circle" style="font-size: 3rem;"></i>
                                <h5 class="mt-2 mb-0">In Progress</h5>
                                <small>{{ $totalMandatory - $totalSubmitted }} dokumen tersisa</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Submit Button Section - Always at the bottom -->
        <div class="card mt-4" style="border: 2px solid #10b981;">
            <div class="card-body text-center">
                <div class="mb-3">
                    <button type="button" id="uploadDocumentsBtn" class="btn btn-success btn-lg px-5 py-3 me-3">
                        <i class="ti ti-upload me-2"></i>Upload Documents
                    </button>
                    <button type="button" id="saveDraftBtn" class="btn btn-outline-secondary btn-lg px-5 py-3 me-3">
                        <i class="ti ti-device-floppy me-2"></i>Save Draft
                    </button>
                    <button type="button" id="submitAllBtn" class="btn btn-primary btn-lg px-5 py-3">
                        <i class="ti ti-check me-2"></i>Submit All Documents
                    </button>
                </div>
                <p class="text-muted small mb-0">
                    <i class="ti ti-info-circle me-1"></i>
                    <strong>Upload Documents:</strong> Upload file yang dipilih ke server<br>
                    <strong>Save Draft:</strong> Simpan progress saat ini tanpa finalisasi<br>
                    <strong>Submit All:</strong> Finalisasi dan kirim semua dokumen (pastikan semua mandatory sudah lengkap)
                </p>
            </div>
        </div>
    </form>
</div>

@endsection

@section('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
}

.part-separator {
    height: 3px;
    background: linear-gradient(to right, transparent, #3b82f6, transparent);
    margin: 2rem 0;
}

.part-section {
    animation: slideInUp 0.5s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.document-card {
    transition: all 0.3s ease;
}

.document-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.upload-area {
    transition: all 0.3s ease;
}

.upload-area:hover {
    border-color: #2563eb !important;
    background-color: #dbeafe !important;
    transform: scale(1.02);
}

.upload-area.dragover {
    border-color: #1d4ed8 !important;
    background-color: #bfdbfe !important;
    transform: scale(1.03);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
}

.upload-area.file-selected {
    border-color: #10b981 !important;
    background-color: #d1fae5 !important;
}

/* Revision Alert Card */
.revision-alert-card {
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    border-left: 4px solid #f59e0b;
    border-radius: 8px;
    padding: 16px 20px;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.1);
}

.revision-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.revision-icon {
    width: 36px;
    height: 36px;
    background: rgba(245, 158, 11, 0.15);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #d97706;
}

.revision-title strong {
    color: #92400e;
    font-size: 15px;
    font-weight: 600;
}

.revision-deadline {
    display: inline-flex;
    align-items-center;
    gap: 4px;
    font-size: 13px;
    color: #78350f;
}

.revision-note-wrapper {
    position: relative;
}

.revision-note-display {
    background: rgba(255, 255, 255, 0.5);
    border-radius: 6px;
    padding: 12px;
}

.revision-note-preview {
    color: #451a03;
    font-size: 14px;
    line-height: 1.6;
}

.revision-note-full {
    color: #451a03;
    font-size: 14px;
    line-height: 1.6;
    overflow: hidden;
    max-height: 0;
    opacity: 0;
    transition: max-height 0.4s ease-out, opacity 0.3s ease-out;
}

.revision-note-full.expanded {
    max-height: 1000px;
    opacity: 1;
    margin-top: 12px;
}

.revision-note-preview.collapsed-hide {
    display: none;
}

.show-more-toggle {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    color: #ffffff;
    border: none;
    border-radius: 6px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.show-more-toggle:hover {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    transform: translateY(-1px);
}

.show-more-toggle[data-expanded="true"] .toggle-icon {
    transform: rotate(180deg);
}

.upload-label {
    z-index: 1;
}

.file-info-display {
    z-index: 2;
    background-color: #d1fae5 !important;
    border-radius: 0.75rem;
}

@media (max-width: 768px) {
    .document-card .row {
        flex-direction: column;
    }
    
    .document-card .col-md-4 {
        margin-top: 1rem;
    }
}
</style>
@endsection

@section('scripts')
<script>
// Function to toggle revision note
function toggleRevisionNote(button) {
    const wrapper = button.closest('.revision-note-wrapper');
    const previewElement = wrapper.querySelector('.revision-note-preview');
    const fullElement = wrapper.querySelector('.revision-note-full');
    const isExpanded = button.dataset.expanded === 'true';
    const toggleText = button.querySelector('.toggle-text');
    
    if (isExpanded) {
        fullElement.classList.remove('expanded');
        previewElement.classList.remove('collapsed-hide');
        button.dataset.expanded = 'false';
        toggleText.textContent = 'Lihat Selengkapnya';
    } else {
        previewElement.classList.add('collapsed-hide');
        setTimeout(() => {
            fullElement.style.display = 'block';
            fullElement.offsetHeight;
            fullElement.classList.add('expanded');
        }, 50);
        button.dataset.expanded = 'true';
        toggleText.textContent = 'Lihat Lebih Sedikit';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('uploadForm');
    
    // File upload handling
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('file-input')) {
            handleFileSelect(e.target);
        }
    });

    function handleFileSelect(input) {
        const file = input.files[0];
        const uploadArea = input.closest('.upload-area');
        const fileInfoDisplay = uploadArea.querySelector('.file-info-display');
        const uploadLabel = uploadArea.querySelector('.upload-label');
        
        if (!file) {
            uploadArea.classList.remove('file-selected');
            if (fileInfoDisplay) {
                fileInfoDisplay.classList.remove('d-flex');
                fileInfoDisplay.classList.add('d-none');
            }
            if (uploadLabel) {
                uploadLabel.classList.remove('d-none');
            }
            return;
        }

        // Validate file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('File terlalu besar. Maksimal 10MB.');
            input.value = '';
            return;
        }

        // Validate file type
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung. Gunakan PDF, JPG, atau PNG.');
            input.value = '';
            return;
        }

        // Update UI
        uploadArea.classList.add('file-selected');
        
        if (uploadLabel) {
            uploadLabel.classList.add('d-none');
        }
        
        if (fileInfoDisplay) {
            fileInfoDisplay.classList.remove('d-none');
            fileInfoDisplay.classList.add('d-flex');
            
            const fileNameEl = fileInfoDisplay.querySelector('.file-name');
            const fileSizeEl = fileInfoDisplay.querySelector('.file-size');
            
            if (fileNameEl) fileNameEl.textContent = file.name;
            if (fileSizeEl) fileSizeEl.textContent = formatFileSize(file.size);
        }
    }

    // Handle remove file button
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-file-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.remove-file-btn');
            const uploadArea = btn.closest('.upload-area');
            const fileInput = uploadArea.querySelector('.file-input');
            
            if (fileInput) {
                fileInput.value = '';
                handleFileSelect(fileInput);
            }
        }
    });

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Upload Documents button handler
    document.getElementById('uploadDocumentsBtn').addEventListener('click', async function() {
        const btn = this;
        const originalText = btn.innerHTML;
        
        // Check if at least one file is selected
        const fileInputs = document.querySelectorAll('input[type="file"]');
        let hasFiles = false;
        
        fileInputs.forEach(input => {
            if (input.files.length > 0) {
                hasFiles = true;
            }
        });

        if (!hasFiles) {
            alert('Silakan pilih minimal satu dokumen untuk diupload.');
            return;
        }
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';

        const formData = new FormData(uploadForm);
        
        try {
            const response = await fetch('{{ route("portal.upload.post", $token) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const result = await response.json();

            if (response.ok) {
                alert('✓ ' + (result.message || 'Dokumen berhasil diupload!'));
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                throw new Error(result.message || 'Terjadi kesalahan saat upload');
            }
        } catch (error) {
            console.error('Upload error:', error);
            alert('Error: ' + (error.message || 'Terjadi kesalahan saat upload dokumen.'));
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });

    // Save Draft handler
    document.getElementById('saveDraftBtn').addEventListener('click', async function() {
        const btn = this;
        const originalText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
        
        try {
            const response = await fetch('{{ route("portal.save-draft", $token) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const result = await response.json();

            if (response.ok) {
                // Show success message
                const successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success alert-dismissible fade show position-fixed';
                successAlert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                successAlert.innerHTML = `
                    <i class="ti ti-check-circle me-2"></i>
                    <strong>Berhasil!</strong> ${result.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(successAlert);
                
                // Auto-dismiss after 3 seconds
                setTimeout(() => {
                    successAlert.remove();
                }, 3000);
            } else {
                throw new Error(result.message || 'Gagal menyimpan draft');
            }
        } catch (error) {
            alert('Error: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });

    // Submit All Documents button handler
    document.getElementById('submitAllBtn').addEventListener('click', async function() {
        // Confirm submission
        if (!confirm('Apakah Anda yakin ingin submit semua dokumen? Pastikan semua dokumen mandatory dari semua part sudah lengkap.')) {
            return;
        }

        const btn = this;
        const originalBtnText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

        try {
            const response = await fetch('{{ route("portal.submit-all", $token) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const result = await response.json();

            if (response.ok) {
                alert('✓ ' + result.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                // Show detailed error message
                if (result.missing_docs && result.missing_docs.length > 0) {
                    let errorMsg = 'Dokumen mandatory yang masih kurang:\n\n';
                    result.missing_docs.forEach(missing => {
                        errorMsg += `Part: ${missing.part_name} (${missing.part_number})\n`;
                        errorMsg += 'Missing:\n';
                        missing.documents.forEach(doc => {
                            errorMsg += `  • ${doc}\n`;
                        });
                        errorMsg += '\n';
                    });
                    alert(errorMsg);
                } else {
                    throw new Error(result.message || 'Gagal submit dokumen');
                }
            }
        } catch (error) {
            console.error('Submit error:', error);
            alert('Error: ' + (error.message || 'Terjadi kesalahan saat submit dokumen.'));
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalBtnText;
        }
    });

    // Drag and drop for all upload areas
    document.querySelectorAll('.upload-area').forEach(area => {
        setupDragAndDrop(area);
    });

    function setupDragAndDrop(area) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            area.addEventListener(eventName, preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            area.addEventListener(eventName, () => area.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            area.addEventListener(eventName, () => area.classList.remove('dragover'), false);
        });

        area.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            const input = area.querySelector('.file-input');
            
            if (input && files.length > 0) {
                input.files = files;
                handleFileSelect(input);
            }
        }
    }

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
});
</script>
@endsection

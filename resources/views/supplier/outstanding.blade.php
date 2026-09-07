@extends('layouts.app')

@section('title', 'Outstanding Documents')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-1"><i class="ti ti-alert-circle"></i> Outstanding Documents</h4>
            <p class="text-muted">Documents that need your attention (pending upload or revision required)</p>
        </div>
    </div>

    @if($outstandingRequests->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-check-circle fs-1 text-success"></i>
                <h5 class="mt-3">All Caught Up!</h5>
                <p class="text-muted">You don't have any outstanding documents at the moment.</p>
                <a href="{{ route('supplier.dashboard') }}" class="btn btn-primary">
                    <i class="ti ti-home"></i> Go to Dashboard
                </a>
            </div>
        </div>
    @else
        @foreach($outstandingRequests as $request)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">{{ $request->project_name }}</h5>
                    <small class="text-muted">
                        Request ID: REQ-{{ $request->id }} | 
                        Due Date: {{ $request->due_date->format('d M Y') }}
                        @if($request->due_date->isPast())
                            <span class="badge bg-danger ms-2">Overdue</span>
                        @else
                            <span class="badge bg-warning ms-2">{{ $request->due_date->diffForHumans() }}</span>
                        @endif
                    </small>
                </div>
                <a href="{{ route('supplier.request', $request->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-eye"></i> View Full Request
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($request->requestDocuments as $doc)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border 
                            @if($doc->status === 'REJECTED') border-danger 
                            @else border-warning 
                            @endif">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">{{ $doc->documentType->name }}</h6>
                                    <span class="badge 
                                        @if($doc->status === 'PENDING') bg-warning 
                                        @else bg-danger 
                                        @endif">
                                        {{ $doc->status === 'PENDING' ? 'Not Uploaded' : 'Revision' }}
                                    </span>
                                </div>
                                
                                @if($doc->status === 'REJECTED' && $doc->review_notes)
                                <div class="alert alert-danger small mb-2">
                                    <strong>Reason:</strong><br>
                                    {{ Str::limit($doc->review_notes, 100) }}
                                </div>
                                @endif

                                @if($doc->status === 'REJECTED' && $doc->revision_deadline_at)
                                <p class="small text-danger mb-2">
                                    <i class="ti ti-clock"></i> Revision Deadline: 
                                    {{ $doc->revision_deadline_at->format('d M Y H:i') }}
                                    @if($doc->revision_deadline_at->isPast())
                                        <span class="badge bg-danger">Overdue</span>
                                    @endif
                                </p>
                                @endif

                                <div class="d-grid gap-2 mt-3">
                                    <a href="{{ route('supplier.upload.form', $doc->id) }}" 
                                       class="btn btn-sm 
                                       @if($doc->status === 'REJECTED') btn-danger 
                                       @else btn-warning 
                                       @endif">
                                        <i class="ti ti-upload"></i> 
                                        {{ $doc->status === 'PENDING' ? 'Upload' : 'Re-upload' }}
                                    </a>
                                    @if($doc->file_path)
                                    <a href="{{ route('supplier.view', $doc->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="ti ti-eye"></i> View PDF
                                    </a>
                                    <a href="{{ route('supplier.download', $doc->id) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="ti ti-download"></i> Download
                                    </a>
                                    @else
                                    <a href="{{ route('supplier.document', $doc->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-file-search"></i> View Details
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    @endif
</div>
@endsection

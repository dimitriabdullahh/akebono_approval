@extends('layouts.app')

@section('title', 'Review & Approval Dokumen')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0" style="color: var(--primary-color); font-weight: 600;">
                <i class="ti ti-clipboard-check me-2"></i>Review & Approval Dokumen
            </h1>
            <p class="text-muted small mt-1">Dokumen yang menunggu persetujuan Anda</p>
        </div>
        <div>
            <span class="badge bg-info" style="font-size: 0.9rem;">
                <i class="ti ti-alert-circle me-1"></i>{{ $pendingDocuments->count() }} Menunggu Review
            </span>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center" style="border-left: 4px solid var(--accent-color);">
                <div class="card-body">
                    <i class="ti ti-hourglass-high" style="font-size: 2rem; color: var(--accent-color);"></i>
                    <h3 class="mt-2 mb-0">{{ $pendingDocuments->count() }}</h3>
                    <small class="text-muted">Menunggu Review</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center" style="border-left: 4px solid var(--success-color);">
                <div class="card-body">
                    <i class="ti ti-circle-check" style="font-size: 2rem; color: var(--success-color);"></i>
                    <h3 class="mt-2 mb-0">{{ $approvedDocuments->count() }}</h3>
                    <small class="text-muted">Sudah Disetujui</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center" style="border-left: 4px solid var(--warning-color);">
                <div class="card-body">
                    <i class="ti ti-alert-triangle" style="font-size: 2rem; color: var(--warning-color);"></i>
                    <h3 class="mt-2 mb-0">{{ $revisionDocuments->count() }}</h3>
                    <small class="text-muted">Perlu Revisi</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center" style="border-left: 4px solid #8b5cf6;">
                <div class="card-body">
                    <i class="ti ti-sum" style="font-size: 2rem; color: #8b5cf6;"></i>
                    <h3 class="mt-2 mb-0">{{ $totalDocuments }}</h3>
                    <small class="text-muted">Total Dokumen</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs for Status Categories -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                <i class="ti ti-hourglass-high me-2"></i>Menunggu Review ({{ $pendingDocuments->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="revision-tab" data-bs-toggle="tab" data-bs-target="#revision" type="button" role="tab">
                <i class="ti ti-alert-triangle me-2"></i>Perlu Revisi ({{ $revisionDocuments->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                <i class="ti ti-circle-check me-2"></i>Sudah Disetujui ({{ $approvedDocuments->count() }})
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Pending Tab -->
        <div class="tab-pane fade show active" id="pending" role="tabpanel">
            @if($pendingDocuments->count() > 0)
                <div class="row">
                    @foreach($pendingDocuments as $document)
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-left-4" style="border-left: 4px solid var(--accent-color);">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title mb-1">
                                                {{ $document->documentType->name }}
                                                @if($document->documentType->is_mandatory)
                                                    <span style="color: #DC2626; font-size: 1.1em; font-weight: bold;">*</span>
                                                @endif
                                            </h5>
                                            <small class="text-muted">{{ $document->documentRequest->supplier->name }}</small>
                                        </div>
                                        <span class="badge bg-info">{{ $document->documentType->is_mandatory ? 'WAJIB' : 'OPTIONAL' }}</span>
                                    </div>

                                    <div class="mb-3">
                                        <strong style="color: var(--primary-color);">{{ $document->documentRequest->model }}</strong><br>
                                        <small class="text-muted">Part No: {{ $document->documentRequest->part_number }}</small>
                                    </div>

                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="ti ti-calendar me-1"></i>
                                            Diunggah: {{ $document->uploaded_at?->format('d M Y H:i') ?? 'Belum' }}
                                        </small>
                                    </div>

                                    @if(count($document->documentType->scope ?? []) > 1)
                                        @php
                                            $approvedScopes = $document->getApprovedScopes();
                                            $totalScopes = count($document->documentType->scope);
                                        @endphp
                                        <div class="mb-3 p-2 rounded" style="background-color: #f0f9ff; border-left: 3px solid #0284c7;">
                                            <small class="text-muted d-block mb-1">
                                                <i class="ti ti-checklist me-1"></i><strong>Multi-Level Approval</strong>
                                            </small>
                                            <div class="d-flex align-items-center">
                                                @foreach($document->documentType->scope as $scope)
                                                    @if(in_array($scope, $approvedScopes))
                                                        <span class="badge bg-success me-1" style="font-size: 0.7rem;">
                                                            <i class="ti ti-check"></i> {{ $scope }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-light text-dark me-1" style="font-size: 0.7rem;">
                                                            {{ $scope }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                            <small class="text-muted">{{ count($approvedScopes) }}/{{ $totalScopes }} approved</small>
                                        </div>
                                    @endif

                                    <a href="{{ route('review.show', $document) }}" class="btn btn-primary btn-sm w-100">
                                        <i class="ti ti-eye me-2"></i>Lihat & Review
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info text-center py-5">
                    <i class="ti ti-circle-check" style="font-size: 3rem; opacity: 0.5;"></i>
                    <p class="mt-3 mb-0"><strong>Tidak ada dokumen menunggu review</strong></p>
                    <small class="text-muted">Semua dokumen sedang diproses atau sudah selesai</small>
                </div>
            @endif
        </div>

        <!-- Revision Tab -->
        <div class="tab-pane fade" id="revision" role="tabpanel">
            @if($revisionDocuments->count() > 0)
                <div class="row">
                    @foreach($revisionDocuments as $document)
                        @php
                            $daysLeft = $document->revision_deadline_at?->diffInDays(now());
                            $isUrgent = $daysLeft !== null && $daysLeft <= 1;
                        @endphp
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-left-4" style="border-left: 4px solid var(--warning-color);">
                                @if($isUrgent)
                                    <div class="alert alert-warning mb-0 rounded-0">
                                        <i class="ti ti-alert-circle me-2"></i>
                                        <strong>URGENT:</strong> Deadline {{ $document->revision_deadline_at->format('d M Y, H:i') }}
                                    </div>
                                @endif
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title mb-1">
                                                {{ $document->documentType->name }}
                                                @if($document->documentType->is_mandatory)
                                                    <span style="color: #DC2626; font-size: 1.1em; font-weight: bold;">*</span>
                                                @endif
                                            </h5>
                                            <small class="text-muted">{{ $document->documentRequest->supplier->name }}</small>
                                        </div>
                                        <span class="badge bg-warning">REVISI</span>
                                    </div>

                                    <div class="mb-3 p-2 rounded" style="background-color: #fffbeb; border-left: 3px solid var(--warning-color);">
                                        <small><strong>Catatan Revisi:</strong></small><br>
                                        <small class="text-muted">{{ $document->revision_note ?? 'Tidak ada catatan' }}</small>
                                    </div>

                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="ti ti-calendar me-1"></i>
                                            Deadline: {{ $document->revision_deadline_at?->format('d M Y H:i') ?? 'N/A' }}
                                        </small>
                                        @if($document->revision_deadline_at)
                                            <div class="mt-2" id="timer-{{ $document->id }}"></div>
                                            <script>
                                                document.addEventListener('DOMContentLoaded', function() {
                                                    initCountdown('timer-{{ $document->id }}', '{{ $document->revision_deadline_at->toIso8601String() }}');
                                                });
                                            </script>
                                        @endif
                                    </div>

                                    <a href="{{ route('review.show', $document) }}" class="btn btn-warning btn-sm w-100">
                                        <i class="ti ti-eye me-2"></i>Lihat Progress Revisi
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info text-center py-5">
                    <i class="ti ti-circle-check" style="font-size: 3rem; opacity: 0.5;"></i>
                    <p class="mt-3 mb-0"><strong>Tidak ada dokumen yang perlu revisi</strong></p>
                    <small class="text-muted">Semua dokumen yang diminta revisi sudah diperbaiki</small>
                </div>
            @endif
        </div>

        <!-- Approved Tab -->
        <div class="tab-pane fade" id="approved" role="tabpanel">
            @if($approvedDocuments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: #F8FAFC; border-bottom: 2px solid #e2e8f0;">
                            <tr>
                                <th>Nama Dokumen</th>
                                <th>Supplier</th>
                                <th>Status Approval</th>
                                <th>Tanggal Approval</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($approvedDocuments as $document)
                                <tr>
                                    <td>
                                        <strong>
                                            {{ $document->documentType->name }}
                                            @if($document->documentType->is_mandatory)
                                                <span style="color: #DC2626; font-size: 1em; font-weight: bold;">*</span>
                                            @endif
                                        </strong><br>
                                        <small class="text-muted">{{ $document->documentRequest->model }}</small>
                                    </td>
                                    <td>{{ $document->documentRequest->supplier->name }}</td>
                                    <td>
                                        @if($document->status === 'APPROVED')
                                            <span class="badge bg-success mb-1">
                                                <i class="ti ti-circle-check me-1"></i>FINAL APPROVED
                                            </span>
                                            @if(count($document->documentType->scope ?? []) > 1)
                                                <div class="mt-1">
                                                    @foreach($document->approvals->where('status', 'APPROVED') as $approval)
                                                        <span class="badge bg-light text-dark me-1" style="font-size: 0.7rem;">
                                                            <i class="ti ti-check text-success"></i> {{ $approval->scope }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @else
                                            @php
                                                $approvedScopes = $document->getApprovedScopes();
                                                $pendingScopes = $document->getPendingScopes();
                                            @endphp
                                            <span class="badge bg-info mb-1">
                                                <i class="ti ti-hourglass-high me-1"></i>PARTIAL APPROVED
                                            </span>
                                            <div class="mt-1">
                                                @foreach($document->documentType->scope as $scope)
                                                    @if(in_array($scope, $approvedScopes))
                                                        <span class="badge bg-success me-1" style="font-size: 0.7rem;">
                                                            <i class="ti ti-check"></i> {{ $scope }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning text-dark me-1" style="font-size: 0.7rem;">
                                                            {{ $scope }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $document->reviewed_at?->format('d M Y H:i') ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('review.show', $document) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-eye me-1"></i>Lihat
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info text-center py-5">
                    <i class="ti ti-inbox" style="font-size: 3rem; opacity: 0.5;"></i>
                    <p class="mt-3 mb-0"><strong>Belum ada dokumen yang disetujui</strong></p>
                    <small class="text-muted">Dokumen akan muncul di sini setelah Anda menyetujuinya</small>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.border-left-4 {
    border-left: 4px solid transparent !important;
}

.nav-tabs .nav-link {
    color: #64748b;
    border-bottom: 2px solid transparent;
    padding-bottom: 0.75rem;
    margin-bottom: -1px;
    transition: all 0.2s ease;
}

.nav-tabs .nav-link:hover {
    color: var(--primary-color);
}

.nav-tabs .nav-link.active {
    color: var(--accent-color);
    background-color: transparent;
    border-bottom-color: var(--accent-color);
}
</style>
@endsection

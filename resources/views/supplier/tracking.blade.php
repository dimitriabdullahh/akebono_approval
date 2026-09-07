@extends('layouts.supplier')

@section('title', 'Tracking Status Dokumen - ' . $request->supplier->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-2">
                        <i class="ti ti-list-check me-2"></i>Tracking Status Dokumen
                    </h3>
                    <p class="text-muted mb-1">Model: <strong>{{ $request->model }}</strong></p>
                    @if($request->requestParts && $request->requestParts->count() > 0)
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($request->requestParts as $part)
                                <span class="badge bg-light text-dark">
                                    <i class="ti ti-box me-1"></i>{{ $part->part_name }} ({{ $part->part_number }})
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">Part: {{ $request->part_number }}</p>
                    @endif
                </div>
                <div class="text-end">
                    <div class="display-6 fw-bold text-info">{{ $summary['approval_progress'] }}%</div>
                    <small class="text-muted">Persetujuan</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Overall Progress Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Progres Keseluruhan Persetujuan</h6>
                        <strong>{{ $summary['approved'] }}/{{ $summary['total'] }} Dokumen</strong>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $summary['approval_progress'] }}%; color: black !important;">
                            {{ $summary['approval_progress'] }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <ul class="nav nav-tabs border-bottom-0" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-content" type="button" role="tab">
                        <i class="ti ti-list me-1"></i>Semua ({{ $summary['total'] }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved-content" type="button" role="tab">
                        <i class="ti ti-check-circle me-1"></i>Disetujui ({{ $summary['approved'] }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="revising-tab" data-bs-toggle="tab" data-bs-target="#revising-content" type="button" role="tab">
                        <i class="ti ti-alert-circle me-1"></i>Revisi ({{ $summary['revising'] }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="submitted-tab" data-bs-toggle="tab" data-bs-target="#submitted-content" type="button" role="tab">
                        <i class="ti ti-hourglass me-1"></i>Review ({{ $summary['submitted'] }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-content" type="button" role="tab">
                        <i class="ti ti-clock me-1"></i>Pending ({{ $summary['pending'] }})
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- All Documents -->
        <div class="tab-pane fade show active" id="all-content" role="tabpanel">
            <div class="row">
                @forelse(array_merge($documents['approved'], $documents['revising'], $documents['submitted'], $documents['pending']) as $doc)
                <div class="col-lg-6 mb-3">
                    @include('supplier.components.document-card', ['doc' => $doc, 'token' => $token])
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info">Tidak ada dokumen</div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Approved Documents -->
        <div class="tab-pane fade" id="approved-content" role="tabpanel">
            <div class="row">
                @forelse($documents['approved'] as $doc)
                <div class="col-lg-6 mb-3">
                    @include('supplier.components.document-card', ['doc' => $doc, 'token' => $token])
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info">Belum ada dokumen yang disetujui</div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Revising Documents -->
        <div class="tab-pane fade" id="revising-content" role="tabpanel">
            <div class="row">
                @forelse($documents['revising'] as $doc)
                <div class="col-lg-6 mb-3">
                    @include('supplier.components.document-card', ['doc' => $doc, 'token' => $token])
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info">Tidak ada dokumen yang perlu direvisi</div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Submitted Documents -->
        <div class="tab-pane fade" id="submitted-content" role="tabpanel">
            <div class="row">
                @forelse($documents['submitted'] as $doc)
                <div class="col-lg-6 mb-3">
                    @include('supplier.components.document-card', ['doc' => $doc, 'token' => $token])
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info">Tidak ada dokumen yang sedang direview</div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Pending Documents -->
        <div class="tab-pane fade" id="pending-content" role="tabpanel">
            <div class="row">
                @forelse($documents['pending'] as $doc)
                <div class="col-lg-6 mb-3">
                    @include('supplier.components.document-card', ['doc' => $doc, 'token' => $token])
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info">Semua dokumen sudah diupload</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Project Timeline -->
    <div class="row mt-5">
        <div class="col-12">
            <h5 class="mb-3">
                <i class="ti ti-timeline me-2"></i>Timeline Proyek
            </h5>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="timeline-horizontal">
                        @foreach($timeline as $event)
                        <div class="timeline-event" title="{{ $event['label'] }}">
                            <div class="event-dot" style="background-color: var(--{{ $event['color'] }}-color, #cbd5e1);">
                                <i class="ti {{ $event['icon'] }}" style="font-size: 12px;"></i>
                            </div>
                            <div class="event-label">
                                <strong class="small d-block">{{ $event['label'] }}</strong>
                                <small class="text-muted">{{ $event['date']->format('d M Y') }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex gap-2 flex-wrap">
                @if($summary['pending'] > 0)
                <a href="{{ route('supplier.upload', $token) }}" class="btn btn-primary">
                    <i class="ti ti-upload me-2"></i>Upload Dokumen Baru
                </a>
                @endif

                @if($summary['approved'] > 0)
                <a href="{{ route('supplier.downloadAllApproved', $token) }}" class="btn btn-success">
                    <i class="ti ti-download me-2"></i>Download Semua Dokumen Approved
                </a>
                @endif

                <a href="{{ route('supplier.dashboard', $token) }}" class="btn btn-outline-secondary">
                    <i class="ti ti-home me-2"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-horizontal {
    display: flex;
    gap: 20px;
    overflow-x: auto;
    padding: 20px 0;
    position: relative;
}

.timeline-horizontal::before {
    content: '';
    position: absolute;
    top: 15px;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(to right, #e5e7eb 25%, transparent 25%, transparent 50%, #e5e7eb 50%, #e5e7eb 75%, transparent 75%, transparent);
    background-size: 20px 100%;
    z-index: -1;
}

.timeline-event {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    min-width: 140px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.timeline-event:hover {
    transform: translateY(-5px);
}

.event-dot {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.event-label {
    text-align: center;
    font-size: 12px;
}

@media (max-width: 768px) {
    .timeline-horizontal {
        gap: 10px;
    }

    .timeline-event {
        min-width: 100px;
    }

    .event-label {
        font-size: 10px;
    }
}

.nav-tabs {
    background-color: #f8fafc;
    padding: 0 0 0 1rem;
    border-radius: 0.5rem 0.5rem 0 0;
}

.nav-link {
    border: none;
    color: #64748b;
    font-weight: 500;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
}

.nav-link:hover {
    color: #0f2942;
}

.nav-link.active {
    color: #0f2942;
    border-bottom-color: #0f2942;
    background-color: transparent;
}

.progress-bar {
    color: black !important;
}
</style>
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh summary every 30 seconds
    setInterval(function() {
        fetch('{{ route("supplier.getProjectSummary", $token) }}')
            .then(response => response.json())
            .then(data => {
                // Update progress bar
                const progressBar = document.querySelector('.progress-bar');
                if (progressBar) {
                    progressBar.style.width = data.summary.approval_progress + '%';
                    progressBar.textContent = data.summary.approval_progress + '%';
                    progressBar.style.color = 'black';
                }
            })
            .catch(error => console.error('Auto-refresh error:', error));
    }, 30000);
});
</script>
@endsection

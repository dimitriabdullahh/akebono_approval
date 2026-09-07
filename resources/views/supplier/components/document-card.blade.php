<div class="card border-0 shadow-sm h-100">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <h6 class="mb-1">
                    {{ $doc['name'] }}
                    @if($doc['is_mandatory'])
                        <span style="color: #DC2626; font-size: 1.1em; font-weight: bold;">*</span>
                    @endif
                </h6>
                <small class="text-muted d-block">{{ $doc['code'] }}</small>
            </div>
            <div>
                @switch($doc['status'])
                    @case('APPROVED')
                        <span class="badge bg-success">
                            <i class="ti ti-check"></i> Disetujui
                        </span>
                        @break
                    @case('REVISE')
                        <span class="badge bg-warning">
                            <i class="ti ti-alert-circle"></i> Revisi
                        </span>
                        @break
                    @case('SUBMITTED')
                        <span class="badge bg-info">
                            <i class="ti ti-hourglass"></i> Review
                        </span>
                        @break
                    @default
                        <span class="badge bg-secondary">
                            <i class="ti ti-clock"></i> Pending
                        </span>
                @endswitch
            </div>
        </div>

        <!-- Mandatory/Optional Tag -->
        <div class="mb-3">
            @if($doc['is_mandatory'])
                <span class="badge bg-danger bg-opacity-10 text-danger">
                    <i class="ti ti-star me-1"></i>Wajib
                </span>
            @else
                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                    <i class="ti ti-file me-1"></i>Optional
                </span>
            @endif
        </div>

        <!-- Status Details -->
        @if($doc['status'] === 'APPROVED')
            <div class="alert alert-success alert-sm mb-3" role="alert">
                <i class="ti ti-check-circle me-1"></i>
                <strong>Disetujui</strong><br>
                <small>{{ $doc['reviewed_at']->format('d M Y H:i') }}</small>
                @if($doc['reviewed_by'])
                    <br><small>Oleh: {{ $doc['reviewed_by'] }}</small>
                @endif
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('supplier.previewDocument', [$token, $doc['id']]) }}" 
                   class="btn btn-sm btn-outline-primary flex-grow-1" target="_blank">
                    <i class="ti ti-eye me-1"></i>Preview
                </a>
                <a href="{{ route('supplier.downloadDocument', [$token, $doc['id']]) }}" 
                   class="btn btn-sm btn-outline-success flex-grow-1">
                    <i class="ti ti-download me-1"></i>Download
                </a>
            </div>

        @elseif($doc['status'] === 'REVISE')
            <div class="alert alert-warning alert-sm mb-3" role="alert">
                <div class="d-flex align-items-start">
                    <i class="ti ti-alert-triangle me-2 mt-1"></i>
                    <div style="flex: 1;">
                        <strong>Perlu Direvisi</strong><br>
                        <small class="d-block mt-2">{{ $doc['revision_note'] }}</small>
                        @if($doc['revision_deadline_at'])
                            <small class="d-block mt-2 fw-bold">
                                @if($doc['is_deadline_passed'])
                                    <span class="text-danger">⚠️ Deadline Terlampaui</span>
                                @else
                                    Deadline: {{ $doc['revision_deadline_at']->format('d M Y H:i') }}<br>
                                    <span class="text-warning">Sisa {{ $doc['deadline_days_left'] }} hari</span>
                                @endif
                            </small>
                        @endif
                    </div>
                </div>
            </div>
            @if($doc['original_filename'])
                <div class="alert alert-sm bg-light mb-3">
                    <small>
                        <strong>File Terakhir:</strong> {{ $doc['original_filename'] }}<br>
                        <strong>Diupload:</strong> {{ $doc['uploaded_at']->format('d M Y H:i') }}
                    </small>
                </div>
            @endif
            <a href="{{ route('supplier.documentDetail', [$token, $doc['id']]) }}" 
               class="btn btn-sm btn-warning w-100">
                <i class="ti ti-edit me-1"></i>Upload Ulang
            </a>

        @elseif($doc['status'] === 'SUBMITTED')
            <div class="alert alert-info alert-sm mb-3" role="alert">
                <i class="ti ti-hourglass me-1"></i>
                <strong>Menunggu Review</strong><br>
                <small>Diupload: {{ $doc['uploaded_at']->format('d M Y H:i') }}</small>
            </div>
            <a href="{{ route('supplier.previewDocument', [$token, $doc['id']]) }}" 
               class="btn btn-sm btn-outline-info w-100" target="_blank">
                <i class="ti ti-eye me-1"></i>Preview
            </a>

        @else
            <div class="alert alert-secondary alert-sm mb-3" role="alert">
                <i class="ti ti-clock me-1"></i>
                <strong>Belum Diupload</strong><br>
                <small>Tunggu sampai Anda siap untuk mengunggah dokumen ini</small>
            </div>
            <a href="{{ route('supplier.documentDetail', [$token, $doc['id']]) }}" 
               class="btn btn-sm btn-secondary w-100" disabled>
                <i class="ti ti-upload me-1"></i>Buka Upload
            </a>
        @endif

        <!-- History -->
        @if(!empty($doc['histories']) && count($doc['histories']) > 0)
            <div class="mt-3 pt-3 border-top">
                <small class="d-block text-muted mb-2"><strong>Riwayat:</strong></small>
                <div class="small">
                    @foreach($doc['histories'] as $history)
                        <div class="mb-2">
                            <span class="badge bg-light text-dark">{{ $history['action'] }}</span>
                            <small class="d-block text-muted">{{ $history['created_at']->diffForHumans() }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<style>
.alert-sm {
    padding: 0.5rem 0.75rem;
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
}
</style>

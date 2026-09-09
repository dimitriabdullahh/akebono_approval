@extends('layouts.app')

@section('title', 'Detail Permintaan Dokumen - ' . $request->model)

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <div class="d-flex align-items-center mb-2">
                <a href="{{ route('requests.index') }}" class="btn btn-sm btn-outline-secondary me-3" title="Kembali ke Daftar">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
                <h1 class="h3 mb-0" style="color: var(--primary-color); font-weight: 600;">
                    <i class="ti ti-file-description me-2"></i>Detail Permintaan Dokumen
                </h1>
            </div>
            <p class="text-muted small mt-1">Informasi lengkap dan status progres dokumen</p>
        </div>
        
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" onclick="resendEmail()">
                <i class="ti ti-mail me-2"></i>Kirim Ulang Email
            </button>
        </div>
    </div>

    <!-- Request Info Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="mb-3">
                <label class="text-muted small mb-1">MODEL</label>
                <div class="fw-bold">{{ $request->model }}</div>
            </div>
            
            @if($request->requestParts && $request->requestParts->count() > 0)
            <div class="mb-3">
                <label class="text-muted small mb-1">DAFTAR PART</label>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th>Part Name</th>
                                <th>Part Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($request->requestParts as $index => $part)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="fw-500">{{ $part->part_name }}</td>
                                <td><span class="badge bg-light text-dark">{{ $part->part_number }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="mb-3">
                <label class="text-muted small mb-1">PART NAME</label>
                <div class="fw-bold">{{ $request->part_name }}</div>
            </div>
            
            <div class="mb-3">
                <label class="text-muted small mb-1">PART NUMBER</label>
                <div class="fw-bold">{{ $request->part_number }}</div>
            </div>
            @endif
            
            <div class="mb-3">
                <label class="text-muted small mb-1">DUE DATE</label>
                <div class="fw-bold">{{ $request->due_date->format('d F Y') }}</div>
            </div>
            
            <div class="mb-3">
                <label class="text-muted small mb-1">SUPPLIER</label>
                <div class="fw-bold">{{ $request->supplier->name }}</div>
                <small class="text-muted">{{ $request->supplier->pic_email }}</small>
            </div>
            
            <div>
                <label class="text-muted small mb-1">DIBUAT OLEH</label>
                <div class="fw-bold">{{ $request->creator->name }}</div>
                <small class="text-muted">{{ $request->created_at->format('d F Y H:i') }}</small>
            </div>
        </div>
    </div>

    <!-- Documents Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h5 class="mb-1"><i class="ti ti-files"></i> Daftar Dokumen</h5>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <!-- Documents Table -->
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">NO</th>
                            <th width="40%">DOKUMEN</th>
                            <th width="20%" class="text-center">TANGGAL SUBMIT</th>
                            <th width="35%">FILE & AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($request->requestDocuments as $index => $reqDoc)
                            <tr>
                                <!-- NO -->
                                <td class="text-center align-middle">
                                    <strong>{{ $index + 1 }}</strong>
                                </td>
                                
                                <!-- DOKUMEN -->
                                <td class="align-middle">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold mb-1">{{ $reqDoc->documentType->name }}
                                                @if($reqDoc->documentType->is_mandatory)
                                                    <span style="color: #DC2626; font-size: 1.1em; font-weight: bold;">*</span>
                                                @endif
                                            </div>
                                            @php
                                                $statusConfig = [
                                                    'PENDING' => ['badge' => 'secondary', 'label' => 'Menunggu Upload'],
                                                    'SUBMITTED' => ['badge' => 'info', 'label' => 'Sudah Dikirim'],
                                                    'APPROVED' => ['badge' => 'success', 'label' => 'Disetujui'],
                                                    'REVISE' => ['badge' => 'warning', 'label' => 'Revisi Diperlukan'],
                                                ];
                                                $config = $statusConfig[$reqDoc->status] ?? ['badge' => 'secondary', 'label' => $reqDoc->status];
                                            @endphp
                                            <span class="badge bg-{{ $config['badge'] }} badge-sm">{{ $config['label'] }}</span>
                                            
                                            @if($reqDoc->documentType->description)
                                                <div class="text-muted small mt-1">{{ $reqDoc->documentType->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- TANGGAL SUBMIT -->
                                <td class="text-center align-middle">
                                    @if($reqDoc->submitted_at)
                                        <div class="fw-semibold">{{ $reqDoc->submitted_at->format('d F Y') }}</div>
                                        <small class="text-muted">{{ $reqDoc->submitted_at->format('H:i') }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                
                                <!-- FILE & AKSI -->
                                <td class="align-middle">
                                    @if($reqDoc->file_name)
                                        <!-- File Uploaded -->
                                        <div class="d-flex flex-column gap-2">
                                            <!-- File Info -->
                                            <div class="border rounded p-2 bg-light d-flex align-items-center">
                                                <i class="ti ti-file-check text-success fs-4 me-2"></i>
                                                <div class="flex-grow-1">
                                                    <div class="fw-semibold small">{{ Str::limit($reqDoc->file_name, 35) }}</div>
                                                    <small class="text-muted">
                                                        @php
                                                            $extension = strtoupper(pathinfo($reqDoc->file_name, PATHINFO_EXTENSION));
                                                        @endphp
                                                        {{ $extension }} File
                                                        @if($reqDoc->file_size)
                                                            | {{ number_format($reqDoc->file_size / 1024, 2) }} KB
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                            
                                            <!-- Action Buttons -->
                                            <div class="d-flex gap-2">
                                                @if($reqDoc->file_name && $reqDoc->status !== 'PENDING')
                                                    <a href="{{ route('review.show', $reqDoc) }}" 
                                                       class="btn btn-primary btn-sm flex-fill" 
                                                       title="Review Dokumen"
                                                       target="_blank">
                                                        <i class="ti ti-eye"></i> View
                                                    </a>
                                                @endif
                                                
                                                @if($reqDoc->status === 'APPROVED' && $reqDoc->file_path)
                                                    <a href="{{ route('admin.documents.download', $reqDoc->id) }}" 
                                                       class="btn btn-success btn-sm flex-fill" 
                                                       title="Download PDF"
                                                       target="_blank">
                                                        <i class="ti ti-download"></i> Download
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <!-- No File Uploaded -->
                                        <div class="text-center py-2">
                                            <i class="ti ti-file-off text-muted fs-3"></i>
                                            <div class="text-muted small">Not uploaded</div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div style="color: #cbd5e1;">
                                        <i class="ti ti-file-x" style="font-size: 3rem; opacity: 0.5;"></i>
                                        <p class="mt-3 mb-0">Belum ada dokumen terdaftar</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Activity Log -->
    @if($request->notificationLogs->count() > 0)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="ti ti-history me-2"></i>Log Aktivitas
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($request->notificationLogs->sortByDesc('created_at') as $log)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-marker bg-primary rounded-circle me-3" style="width: 8px; height: 8px; margin-top: 6px;"></div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-500">{{ $log->type }}</span>
                                        <small class="text-muted">{{ $log->created_at->format('d M Y H:i') }}</small>
                                    </div>
                                    @if($log->message)
                                        <small class="text-muted">{{ $log->message }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Resend Email Modal -->
<div class="modal fade" id="resendEmailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Pengiriman Ulang Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin mengirim ulang email surat request ke supplier?</p>
                <div class="bg-light p-3 rounded">
                    <p class="mb-1"><strong>Supplier:</strong> {{ $request->supplier->name }}</p>
                    <p class="mb-0"><strong>Email PIC:</strong> {{ $request->supplier->pic_email }}</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="confirmResendEmail()">
                    <i class="ti ti-mail me-2"></i>Kirim Ulang
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let resendModal = null;

function getResendModal() {
    if (!resendModal) {
        const modalEl = document.getElementById('resendEmailModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            resendModal = new bootstrap.Modal(modalEl);
        }
    }
    return resendModal;
}

function resendEmail() {
    const modal = getResendModal();
    if (modal) {
        modal.show();
    }
}

function confirmResendEmail() {
    fetch(`{{ route('requests.resend-email', $request) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || 'Gagal mengirim email.');
        }
        return data;
    })
    .then(data => {
        const modal = getResendModal();
        if (modal) modal.hide();
        showToast('success', data.message || 'Email berhasil dikirim ulang ke supplier!');
    })
    .catch(error => {
        const modal = getResendModal();
        if (modal) modal.hide();
        showToast('error', error.message || 'Gagal mengirim email. Silakan coba lagi.');
        console.error('Error:', error);
    });
}

// Toast Notification
function showToast(type, message) {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="ti ti-${type === 'success' ? 'check' : 'alert-circle'} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    const toastContainer = document.querySelector('.toast-container') || (() => {
        const container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(container);
        return container;
    })();

    const toastElement = document.createElement('div');
    toastElement.innerHTML = toastHtml;
    toastContainer.appendChild(toastElement.firstElementChild);

    if (typeof bootstrap !== 'undefined') {
        const toast = new bootstrap.Toast(toastContainer.querySelector('.toast:last-child'));
        toast.show();
    }
}
</script>
@endpush

<style>
/* Enhanced button styles for view and download actions */
.btn-primary, .btn-success {
    transition: all 0.3s ease;
}

.btn-primary:hover, .btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* File info card styling */
.bg-light.border.rounded {
    transition: all 0.2s ease;
}

.bg-light.border.rounded:hover {
    background-color: #e9ecef !important;
    border-color: #adb5bd !important;
}

/* Action button styling */
.btn-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    border: none;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(13, 110, 253, 0.3);
}

.btn-success {
    background: linear-gradient(135deg, #198754 0%, #146c43 100%);
    border: none;
    transition: all 0.3s ease;
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(25, 135, 84, 0.3);
}

/* Icon size adjustments */
.ti-file-check {
    font-size: 1.5rem;
}

.ti-eye, .ti-download, .ti-refresh {
    font-size: 1rem;
}

/* File upload button */
.btn-primary.d-flex {
    padding: 0.75rem 1rem;
    font-weight: 500;
}

/* Badge sizing */
.badge-sm {
    font-size: 0.75rem;
    padding: 0.35rem 0.65rem;
}

/* Card styling to match Supplier */
.card.border-0.shadow-sm {
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
}

.card-header.bg-white {
    border-bottom: 1px solid #e2e8f0;
}

.table-light {
    background-color: #f8fafc;
}
</style>
@endsection

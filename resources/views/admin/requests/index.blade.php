@extends('layouts.app')

@section('title', 'Daftar Permintaan Dokumen')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0" style="color: var(--primary-color); font-weight: 600;">
                <i class="ti ti-list-details me-2"></i>Daftar Permintaan Dokumen
            </h1>
            <p class="text-muted small mt-1">Kelola seluruh permintaan dokumen yang telah dibuat</p>
        </div>
        @if(auth()->user()->canCreateRequest())
        <a href="{{ route('requests.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-2"></i>Buat Permintaan Baru
        </a>
        @endif
    </div>

    <!-- Search & Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari berdasarkan Model Part, Supplier...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu Upload</option>
                        <option value="in_progress">Sedang Berjalan</option>
                        <option value="completed">Selesai</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                        <i class="ti ti-rotate-clockwise me-2"></i>Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background-color: #F8FAFC; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th class="ps-4">Model Part</th>
                        <th>Supplier</th>
                        <th>Tanggal Due</th>
                        <th>Status Dokumen</th>
                        <th>Progres</th>
                        <th>Dibuat</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                        @php
                            $totalDocs = $request->requestDocuments->count();
                            $approvedDocs = $request->requestDocuments->where('status', 'APPROVED')->count();
                            $submitedDocs = $request->requestDocuments->where('status', '!=', 'PENDING')->count();
                            $progressPercent = $totalDocs > 0 ? round(($approvedDocs / $totalDocs) * 100) : 0;
                            $statusLabel = $progressPercent === 100 ? 'Selesai' : ($submitedDocs === 0 ? 'Menunggu Upload' : 'Sedang Berjalan');
                            $statusColor = $progressPercent === 100 ? 'success' : ($submitedDocs === 0 ? 'secondary' : 'info');
                            $statusSlug = $progressPercent === 100 ? 'completed' : ($submitedDocs === 0 ? 'pending' : 'in_progress');
                        @endphp
                        <tr style="border-bottom: 1px solid #e2e8f0;" 
                            data-search-model="{{ strtolower($request->model) }}"
                            data-search-partnumber="{{ strtolower($request->part_number) }}"
                            data-search-supplier="{{ strtolower($request->supplier->name) }}"
                            data-status="{{ $statusSlug }}">
                            <td class="ps-4">
                                <div class="fw-600" style="color: var(--primary-color);">{{ $request->model }}</div>
                                @if($request->requestParts && $request->requestParts->count() > 0)
                                    <div class="mt-1">
                                        @foreach($request->requestParts as $part)
                                            <small class="d-block text-muted">
                                                <i class="ti ti-box" style="font-size: 0.75rem;"></i>
                                                {{ $part->part_name }} ({{ $part->part_number }})
                                            </small>
                                        @endforeach
                                    </div>
                                @else
                                    <small class="text-muted">{{ $request->part_number }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="fw-500">{{ $request->supplier->name }}</div>
                                <small class="text-muted">{{ $request->supplier->pic_email }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $request->due_date < now() ? 'danger' : 'light' }} text-{{ $request->due_date < now() ? 'white' : 'dark' }}">
                                    {{ $request->due_date->format('d M Y') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td>
                                <div class="progress" style="height: 20px; width: 120px;">
                                    <div class="progress-bar bg-{{ $progressPercent >= 100 ? 'success' : 'info' }}" 
                                         role="progressbar" 
                                         style="width: {{ $progressPercent }}%"
                                         aria-valuenow="{{ $progressPercent }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <small class="fw-600">{{ $approvedDocs }}/{{ $totalDocs }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">{{ $request->created_at->format('d M Y') }}</small>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('requests.show', $request) }}" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    
                                    @if(auth()->user()->isAdminQA() && $submitedDocs > 0)
                                        @php
                                            $qaReviewableDocs = $request->requestDocuments
                                                ->where('status', 'SUBMITTED')
                                                ->filter(function($doc) {
                                                    $scopes = $doc->documentType->scope ?? [];
                                                    return in_array('QA', $scopes);
                                                });
                                        @endphp
                                        
                                        @if($qaReviewableDocs->count() > 0)
                                            <a href="{{ route('requests.review', $request) }}" 
                                               class="btn btn-sm btn-success" 
                                               title="Review & Approve {{ $qaReviewableDocs->count() }} dokumen QA yang menunggu"
                                               data-bs-toggle="tooltip" 
                                               data-bs-placement="top">
                                                <i class="ti ti-circle-check"></i>
                                                <span class="badge bg-light text-dark ms-1">{{ $qaReviewableDocs->count() }}</span>
                                            </a>
                                        @endif
                                    @endif
                                    
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resendEmail({{ $request->id }})" title="Kirim Ulang Email">
                                        <i class="ti ti-mail"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div style="color: #cbd5e1;">
                                    <i class="ti ti-inbox" style="font-size: 3rem; opacity: 0.5;"></i>
                                    <p class="mt-3 mb-0">Belum ada permintaan dokumen</p>
                                    <small>Buat permintaan baru dengan mengklik tombol di atas</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($requests->hasPages())
            <div class="card-footer bg-white">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
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
                <p class="text-muted small mb-0">Email akan dikirim ke alamat email PIC supplier yang terdaftar.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirmResendBtn" onclick="confirmResendEmail()">
                    <i class="ti ti-mail me-2"></i>Kirim Ulang
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let currentRequestId = null;
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

function resendEmail(requestId) {
    currentRequestId = requestId;
    const modal = getResendModal();
    if (modal) {
        modal.show();
    }
}

function confirmResendEmail() {
    if (!currentRequestId) return;

    fetch(`/admin/requests/${currentRequestId}/resend-email`, {
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
        
        // Show success toast
        showToast('success', data.message || 'Email berhasil dikirim ulang ke supplier!');
        
        // Optional: Reload table or update specific row
        setTimeout(() => location.reload(), 1500);
    })
    .catch(error => {
        const modal = getResendModal();
        if (modal) modal.hide();
        showToast('error', error.message || 'Gagal mengirim email. Silakan coba lagi.');
        console.error('Error:', error);
    });
}

// Search & Filter functionality - Enhanced Version
document.getElementById('searchInput')?.addEventListener('input', function() {
    filterTable();
});

document.getElementById('statusFilter')?.addEventListener('change', function() {
    filterTable();
});

function filterTable() {
    const searchValue = document.getElementById('searchInput')?.value.toLowerCase().trim() || '';
    const statusValue = document.getElementById('statusFilter')?.value.toLowerCase() || '';
    const rows = document.querySelectorAll('tbody tr');
    
    let visibleCount = 0;

    rows.forEach(row => {
        // Skip empty state row
        if (row.textContent.includes('Belum ada permintaan dokumen')) {
            row.style.display = 'none';
            return;
        }

        // Get data attributes for accurate filtering
        const searchModel = row.dataset.searchModel || '';
        const searchPartNumber = row.dataset.searchPartnumber || '';
        const searchSupplier = row.dataset.searchSupplier || '';
        const rowStatus = row.dataset.status || '';

        // Search matching - check all searchable fields
        const matchesSearch = searchValue === '' || 
            searchModel.includes(searchValue) || 
            searchPartNumber.includes(searchValue) || 
            searchSupplier.includes(searchValue);

        // Status matching
        const matchesStatus = statusValue === '' || rowStatus === statusValue;

        // Show/hide row
        const shouldShow = matchesSearch && matchesStatus;
        row.style.display = shouldShow ? '' : 'none';
        
        if (shouldShow) visibleCount++;
    });

    // Show/hide empty state
    updateEmptyState(visibleCount);
}

function updateEmptyState(visibleCount) {
    const tbody = document.querySelector('tbody');
    let emptyRow = tbody.querySelector('.empty-state-row');
    
    if (visibleCount === 0) {
        if (!emptyRow) {
            emptyRow = document.createElement('tr');
            emptyRow.className = 'empty-state-row';
            emptyRow.innerHTML = `
                <td colspan="7" class="text-center py-5">
                    <div style="color: #cbd5e1;">
                        <i class="ti ti-search-off" style="font-size: 3rem; opacity: 0.5;"></i>
                        <p class="mt-3 mb-0">Tidak ada data yang sesuai dengan filter</p>
                        <small>Coba ubah kata kunci pencarian atau reset filter</small>
                    </div>
                </td>
            `;
            tbody.appendChild(emptyRow);
        }
        emptyRow.style.display = '';
    } else {
        if (emptyRow) {
            emptyRow.style.display = 'none';
        }
    }
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    filterTable();
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

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
</script>
@endpush
@endsection

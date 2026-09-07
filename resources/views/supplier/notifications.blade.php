@extends('layouts.supplier')

@section('title', 'Notifikasi - ' . $request->supplier->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-2">
                        <i class="ti ti-bell me-2"></i>Notifikasi & Pesan
                    </h3>
                    <p class="text-muted mb-0">{{ $request->model }} / {{ $request->part_number }}</p>
                </div>
                <a href="{{ route('supplier.dashboard', $request->access_token) }}" class="btn btn-outline-secondary">
                    <i class="ti ti-home me-2"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Current Alerts -->
    @if(!empty($alerts))
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3">
                <i class="ti ti-alert-circle me-2"></i>Notifikasi Aktif
            </h5>
            @foreach($alerts as $alert)
            <div class="alert alert-{{ $alert['type'] }} alert-dismissible fade show border-0 mb-3" role="alert">
                <div class="d-flex align-items-start">
                    <i class="ti {{ $alert['icon'] }} me-3 mt-1" style="font-size: 1.2rem;"></i>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-1">{{ $alert['title'] }}</h6>
                        <p class="mb-0">{{ $alert['message'] }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="ti ti-info-circle me-2"></i>
                Tidak ada notifikasi aktif saat ini
            </div>
        </div>
    </div>
    @endif

    <!-- Notification History -->
    <div class="row">
        <div class="col-12">
            <h5 class="mb-3">
                <i class="ti ti-history me-2"></i>Riwayat Email Notifikasi
            </h5>
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tipe Notifikasi</th>
                                <th>Subjek Email</th>
                                <th>Penerima</th>
                                <th>Tanggal Terkirim</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notificationLogs as $log)
                            <tr>
                                <td>
                                    @switch($log->type)
                                        @case('REQUEST_LETTER')
                                            <span class="badge bg-primary">
                                                <i class="ti ti-file-text me-1"></i>Surat Request
                                            </span>
                                            @break
                                        @case('DOCS_SUBMITTED')
                                            <span class="badge bg-info">
                                                <i class="ti ti-upload me-1"></i>Dokumen Terkirim
                                            </span>
                                            @break
                                        @case('REVISION_REMINDER')
                                            <span class="badge bg-warning">
                                                <i class="ti ti-alert-circle me-1"></i>Pengingat Revisi
                                            </span>
                                            @break
                                        @case('APPROVAL_COMPLETED')
                                            <span class="badge bg-success">
                                                <i class="ti ti-check-circle me-1"></i>Persetujuan Selesai
                                            </span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $log->type }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <small>{{ $log->subject }}</small>
                                </td>
                                <td>
                                    <small>{{ $log->recipient }}</small>
                                </td>
                                <td>
                                    <small>{{ $log->sent_at->format('d M Y H:i') }}</small>
                                </td>
                                <td>
                                    @if($log->status === 'SENT')
                                        <span class="badge bg-success">
                                            <i class="ti ti-check me-1"></i>Terkirim
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="ti ti-alert-triangle me-1"></i>Gagal
                                        </span>
                                        @if($log->error_message)
                                        <br>
                                        <small class="text-danger">{{ substr($log->error_message, 0, 50) }}...</small>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="ti ti-inbox" style="font-size: 2rem; color: #cbd5e1;"></i>
                                    <p class="text-muted mt-2">Belum ada notifikasi yang terkirim</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($notificationLogs->count() > 0)
                <div class="card-body border-top">
                    {{ $notificationLogs->links('pagination::bootstrap-4') }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Information Section -->
    <div class="row mt-4">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="ti ti-bulb me-2"></i>Jenis-jenis Notifikasi
                    </h6>
                    <div class="small">
                        <div class="mb-3">
                            <span class="badge bg-primary mb-2">Surat Request</span>
                            <p class="mb-0">Notifikasi awal berisi detail dokumen yang diminta dan link portal upload</p>
                        </div>
                        <div class="mb-3">
                            <span class="badge bg-info mb-2">Dokumen Terkirim</span>
                            <p class="mb-0">Notifikasi ke reviewer bahwa Anda telah mengunggah semua dokumen wajib</p>
                        </div>
                        <div class="mb-3">
                            <span class="badge bg-warning mb-2">Pengingat Revisi</span>
                            <p class="mb-0">Pengingat otomatis 2 hari sebelum deadline revisi jatuh tempo</p>
                        </div>
                        <div class="mb-0">
                            <span class="badge bg-success mb-2">Persetujuan Selesai</span>
                            <p class="mb-0">Notifikasi ketika semua dokumen telah disetujui oleh reviewer</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="ti ti-mail me-2"></i>Hubungi Kami
                    </h6>
                    <div class="small">
                        <p class="mb-2">Jika Anda tidak menerima notifikasi atau memiliki pertanyaan, silakan hubungi:</p>
                        <div class="mb-3">
                            <strong>Tim QA Akebono</strong>
                            <div class="mt-2">
                                <p class="mb-1">
                                    <i class="ti ti-mail me-2"></i>
                                    <a href="mailto:qa@akebono.local">qa@akebono.local</a>
                                </p>
                                <p class="mb-0">
                                    <i class="ti ti-phone me-2"></i>
                                    +62 XXX XXXX XXXX
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table-hover tbody tr:hover {
    background-color: #f8fafc;
}

.badge {
    font-weight: 500;
}

@media (max-width: 768px) {
    .table {
        font-size: 0.875rem;
    }

    .table th,
    .table td {
        padding: 0.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh notifications every 5 minutes
    setInterval(function() {
        location.reload();
    }, 5 * 60 * 1000);
});
</script>
@endsection

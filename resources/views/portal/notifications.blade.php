@extends('layouts.supplier')

@section('title', 'Notifikasi - Portal Supplier')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white border-0">
                <div class="card-body">
                    <h2 class="mb-2">
                        <i class="ti ti-bell me-2"></i>Notifikasi dan Pesan
                    </h2>
                    <p class="mb-0 opacity-90">Riwayat notifikasi dari sistem approval dokumen supplier</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Logs -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">
                        <i class="ti ti-history me-2"></i>Riwayat Notifikasi
                    </h5>
                </div>
                <div class="card-body">
                    @if($notificationLogs->count() > 0)
                    <div class="timeline">
                        @foreach($notificationLogs as $log)
                        <div class="timeline-item mb-4">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <div class="rounded-circle p-2" style="background-color: #e3f2fd; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ti ti-mail" style="color: #1e40af;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $log->notification_type ?? 'Notifikasi' }}</h6>
                                    <p class="text-muted small mb-2">{{ $log->message ?? 'Pesan notifikasi' }}</p>
                                    <small class="text-muted">
                                        <i class="ti ti-clock me-1"></i>
                                        {{ $log->sent_at->format('d M Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $notificationLogs->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="ti ti-inbox" style="font-size: 3rem; color: #cbd5e1;"></i>
                        <p class="text-muted mt-3">Belum ada notifikasi</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="mb-2">
                                <i class="ti ti-help-circle me-2"></i>Butuh Bantuan?
                            </h6>
                            <p class="small text-muted mb-0">
                                Jika Anda memiliki pertanyaan atau membutuhkan bantuan, silakan hubungi tim QA Akebono.
                            </p>
                        </div>
                        <div class="col-md-4">
                            <div class="small">
                                <p class="mb-1"><strong>Contact QA Akebono:</strong></p>
                                <p class="mb-1">
                                    <i class="ti ti-mail me-1"></i>
                                    <a href="mailto:qa@akebono.local" class="text-decoration-none">qa@akebono.local</a>
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
.bg-gradient-primary {
    background: linear-gradient(135deg, #0F2942 0%, #1e40af 100%);
}

.timeline {
    position: relative;
    padding-left: 0;
}

.timeline-item {
    position: relative;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 20px;
    top: 40px;
    height: 30px;
    width: 2px;
    background-color: #e5e7eb;
}
</style>
@endsection

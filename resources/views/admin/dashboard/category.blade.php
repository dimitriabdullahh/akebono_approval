@extends('layouts.app')

@section('title', ucfirst(str_replace('_', ' ', $category)) . ' - Dashboard Monitoring')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">{{ $label }}</li>
                        </ol>
                    </nav>
                    <h2 class="mb-2">📋 {{ $label }}</h2>
                    <p class="text-muted">Daftar proyek dengan kategori {{ strtolower($label) }}</p>
                </div>
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
                        <i class="ti ti-arrow-left"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                @if($category === 'not_submitted')
                                    <div class="me-3">
                                        <span class="badge badge-danger" style="font-size: 1.2rem; padding: 0.75rem;">🔴</span>
                                    </div>
                                @elseif($category === 'in_progress')
                                    <div class="me-3">
                                        <span class="badge badge-warning" style="font-size: 1.2rem; padding: 0.75rem;">🟡</span>
                                    </div>
                                @else
                                    <div class="me-3">
                                        <span class="badge badge-success" style="font-size: 1.2rem; padding: 0.75rem;">🟢</span>
                                    </div>
                                @endif
                                <div>
                                    <h4 class="mb-1">{{ $label }}</h4>
                                    <p class="text-muted mb-0">
                                        @if($category === 'not_submitted')
                                            Proyek-proyek yang belum mengirimkan dokumen sama sekali
                                        @elseif($category === 'in_progress')
                                            Proyek-proyek yang sedang dalam proses approval atau belum lengkap
                                        @else
                                            Proyek-proyek yang seluruh dokumen mandatory-nya sudah disetujui
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <h3 class="mb-0">{{ count($projects) }}</h3>
                            <small class="text-muted">Total Proyek</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects List -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-list"></i> Daftar Proyek
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if(count($projects) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Supplier</th>
                                    <th>Model</th>
                                    <th>Part Number</th>
                                    <th>Part Name</th>
                                    <th>Status Progress</th>
                                    <th>Tanggal Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($projects as $project)
                                <tr>
                                    <td>
                                        <div>
                                            <div class="font-weight-bold">{{ $project->supplier->name }}</div>
                                            <small class="text-muted">PIC: {{ $project->supplier->pic_name ?? 'N/A' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="font-weight-medium">{{ $project->model ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <code>{{ $project['part_number'] }}</code>
                                    </td>
                                    <td>{{ $project['part_name'] }}</td>
                                    <td>
                                        @if($category === 'not_submitted')
                                            <span class="badge badge-secondary">
                                                <i class="ti ti-minus"></i> Belum Ada Dokumen
                                            </span>
                                        @elseif($category === 'completed')
                                            <span class="badge badge-success">
                                                <i class="ti ti-check-circle"></i> 100% Selesai
                                            </span>
                                        @else
                                            <div class="d-flex align-items-center">
                                                <div class="progress me-2" style="width: 100px; height: 8px;">
                                                    <div class="progress-bar" role="progressbar" style="width: {{ $project['percentage'] }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $project['approved'] }}/{{ $project['total_mandatory'] }}</small>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $project['created_at'] }}</small>
                                    </td>
                                    <td>
                                        @if(isset($project['id']))
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('requests.show', $project['id']) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="ti ti-eye"></i> Detail
                                            </a>
                                            @if($category !== 'not_submitted')
                                            <a href="{{ route('review.index') }}?request={{ $project['id'] }}" class="btn btn-outline-info btn-sm">
                                                <i class="ti ti-clipboard-check"></i> Review
                                            </a>
                                            @endif
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="ti ti-inbox" style="font-size: 4rem; color: #cbd5e1;"></i>
                        <h5 class="mt-3 text-muted">Tidak Ada Data</h5>
                        <p class="text-muted">
                            @if($category === 'not_submitted')
                                Belum ada proyek yang belum mengirim dokumen.
                            @elseif($category === 'in_progress')
                                Belum ada proyek yang sedang dalam proses.
                            @else
                                Belum ada proyek yang selesai.
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Card -->
    @if(count($projects) > 0)
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Ringkasan Kategori</h6>
                            <p class="mb-0">
                                Total <strong>{{ count($projects) }}</strong> proyek dalam kategori 
                                <strong>{{ $label }}</strong>
                            </p>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                <i class="ti ti-chart-bar"></i> Lihat Dashboard Lengkap
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .badge-danger {
        background-color: #FEF2F2;
        color: #DC2626;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        color: #6b7280;
    }
    
    .progress {
        background-color: #f3f4f6;
    }
    
    code {
        background-color: #f8fafc;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        border: 1px solid #e2e8f0;
    }
</style>
@endpush

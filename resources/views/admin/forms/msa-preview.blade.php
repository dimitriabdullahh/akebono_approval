@extends('layouts.app')

@section('title', 'MSA Gage R&R Preview')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h4 mb-1">
                        <i class="ti ti-chart-line me-2"></i>MSA Gage R&R
                    </h2>
                    @if($msaGageRr->supplier)
                        <p class="text-muted small mb-0">
                            <i class="ti ti-building me-1"></i>{{ $msaGageRr->supplier->name }}
                        </p>
                    @endif
                </div>
                <a href="{{ route('review.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header" style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Preview Dokumen MSA Gage R&R</h6>
                        <a href="{{ route('admin.forms.msa.pdf', $msaGageRr->id) }}" 
                           class="btn btn-sm btn-success" 
                           target="_blank">
                            <i class="ti ti-file-pdf me-1"></i>Download PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @include('admin.forms.msa-preview-embed', ['msaGageRr' => $msaGageRr])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

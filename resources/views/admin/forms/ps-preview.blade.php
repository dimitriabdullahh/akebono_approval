@extends('layouts.app')

@section('title', 'Preview Packaging Standard - Admin Portal')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <h2 class="mb-0">
                    <i class="ti ti-package me-2"></i>Packaging Standard (PS) - Preview
                </h2>
                <div>
                    <a href="{{ route('admin.forms.ps.pdf', $ps->id) }}" target="_blank" class="btn btn-primary">
                        <i class="ti ti-file-pdf me-2"></i>View PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Info -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Informasi Dokumen</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <small class="text-muted">Status:</small>
                            <p class="mb-0">
                                <span class="badge bg-{{ $ps->status_color }}">{{ $ps->status_label }}</span>
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted">Supplier:</small>
                            <p class="mb-0">{{ $ps->supplier?->name ?? $ps->supplier_name }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <small class="text-muted">Document No:</small>
                            <p class="mb-0 fw-semibold">{{ $ps->document_no ?? '-' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted">Revision:</small>
                            <p class="mb-0">{{ $ps->revision ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <small class="text-muted">Created:</small>
                            <p class="mb-0">{{ $ps->created_at?->format('d M Y H:i') }}</p>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted">Submitted:</small>
                            <p class="mb-0">{{ $ps->submitted_at?->format('d M Y H:i') ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Informasi Part</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <small class="text-muted">Part Number:</small>
                            <p class="mb-0 fw-semibold">{{ $ps->part_no }}</p>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted">Part Name:</small>
                            <p class="mb-0">{{ $ps->part_name }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <small class="text-muted">Customer:</small>
                            <p class="mb-0">{{ $ps->customer ?? '-' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted">Qty:</small>
                            <p class="mb-0">{{ $ps->qty ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" href="#step1" data-bs-toggle="tab">
                                <i class="ti ti-info-circle me-2"></i>Informasi Umum
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#step2" data-bs-toggle="tab">
                                <i class="ti ti-package me-2"></i>Spesifikasi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#step3" data-bs-toggle="tab">
                                <i class="ti ti-photo me-2"></i>Visualisasi
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="tab-content">
                    <!-- Step 1 -->
                    <div class="tab-pane fade show active p-4" id="step1">
                        <h5 class="mb-3">Informasi Umum</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Customer</label>
                                <p class="form-control-plaintext">{{ $ps->customer }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Part Number</label>
                                <p class="form-control-plaintext">{{ $ps->part_no }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Part Name</label>
                                <p class="form-control-plaintext">{{ $ps->part_name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Supplier Name</label>
                                <p class="form-control-plaintext">{{ $ps->supplier_name ?? $ps->supplier?->name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Quantity</label>
                                <p class="form-control-plaintext">{{ $ps->qty }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Date</label>
                                <p class="form-control-plaintext">{{ $ps->date?->format('d M Y') ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="tab-pane fade p-4" id="step2">
                        <h5 class="mb-3">Spesifikasi Kemasan</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Jenis Kemasan</label>
                                <p class="form-control-plaintext">{{ $ps->packaging_type }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Ukuran Kemasan (mm)</label>
                                <p class="form-control-plaintext">{{ $ps->packaging_size_mm }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Lot per Box (pcs)</label>
                                <p class="form-control-plaintext">{{ $ps->packaging_lot_pcs_box }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Net Weight per Piece (g)</label>
                                <p class="form-control-plaintext">{{ $ps->net_weight_g_pc ?? '-' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Total Weight per Box (kg)</label>
                                <p class="form-control-plaintext">{{ $ps->total_weight_kg_box ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="tab-pane fade p-4" id="step3">
                        <h5 class="mb-3">Visualisasi & Approval</h5>
                        
                        <!-- Images -->
                        <h6 class="mb-3">Attached Images</h6>
                        <div class="row mb-4">
                            @if($ps->image_photographic)
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body p-0">
                                            <img src="{{ asset($ps->image_photographic) }}" class="img-fluid" style="max-height: 300px; object-fit: contain;">
                                            <div class="p-2 bg-light">
                                                <small class="text-muted">Photographic Image</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($ps->image_part_identification)
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body p-0">
                                            <img src="{{ asset($ps->image_part_identification) }}" class="img-fluid" style="max-height: 300px; object-fit: contain;">
                                            <div class="p-2 bg-light">
                                                <small class="text-muted">Part Identification</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($ps->image_visualisasi_packing)
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body p-0">
                                            <img src="{{ asset($ps->image_visualisasi_packing) }}" class="img-fluid" style="max-height: 300px; object-fit: contain;">
                                            <div class="p-2 bg-light">
                                                <small class="text-muted">Packaging Visualization</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($ps->image_label_packing)
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body p-0">
                                            <img src="{{ asset($ps->image_label_packing) }}" class="img-fluid" style="max-height: 300px; object-fit: contain;">
                                            <div class="p-2 bg-light">
                                                <small class="text-muted">Label & Packing</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Approval Section -->
                        <h6 class="mb-3">Approval Information</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Prepared By</th>
                                    <th>Checked By</th>
                                    <th>Approved By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div><strong>{{ $ps->prepared_by_name ?? '-' }}</strong></div>
                                        <small class="text-muted">{{ $ps->prepared_by_date?->format('d M Y') ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <div><strong>{{ $ps->checked_by_name ?? '-' }}</strong></div>
                                        <small class="text-muted">{{ $ps->checked_by_date?->format('d M Y') ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <div><strong>{{ $ps->approved_by_name ?? '-' }}</strong></div>
                                        <small class="text-muted">{{ $ps->approved_by_date?->format('d M Y') ?? '-' }}</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

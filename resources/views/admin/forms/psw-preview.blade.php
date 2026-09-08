@extends('layouts.app')

@section('title', 'Preview PSW - Admin Portal')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <h2 class="mb-0">
                    <i class="ti ti-file-text me-2"></i>Part Submission Warrant (PSW) - Preview
                </h2>
                <div>
                    <a href="{{ route('admin.forms.psw.pdf', $psw->id) }}" target="_blank" class="btn btn-primary">
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
                                <span class="badge bg-{{ $psw->status_color }}">{{ $psw->status_label }}</span>
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted">Supplier:</small>
                            <p class="mb-0">{{ $psw->supplier?->name ?? $psw->supplier_name }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <small class="text-muted">Created:</small>
                            <p class="mb-0">{{ $psw->created_at?->format('d M Y H:i') }}</p>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted">Submitted:</small>
                            <p class="mb-0">{{ $psw->submitted_at?->format('d M Y H:i') ?? '-' }}</p>
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
                            <p class="mb-0 fw-semibold">{{ $psw->part_number }}</p>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted">Part Name:</small>
                            <p class="mb-0">{{ $psw->part_name }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <small class="text-muted">Model:</small>
                            <p class="mb-0">{{ $psw->model ?? '-' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted">Revision:</small>
                            <p class="mb-0">{{ $psw->revision ?? '-' }}</p>
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
                                <i class="ti ti-building me-2"></i>Supplier
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#step3" data-bs-toggle="tab">
                                <i class="ti ti-flask me-2"></i>Alasan & Hasil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#step4" data-bs-toggle="tab">
                                <i class="ti ti-signature me-2"></i>Deklarasi
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
                                <label class="form-label fw-semibold text-muted">Part Name</label>
                                <p class="form-control-plaintext">{{ $psw->part_name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Part Number</label>
                                <p class="form-control-plaintext">{{ $psw->part_number }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Model</label>
                                <p class="form-control-plaintext">{{ $psw->model ?? '-' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Revision</label>
                                <p class="form-control-plaintext">{{ $psw->revision ?? '-' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Date Submitted</label>
                                <p class="form-control-plaintext">{{ $psw->date_submitted?->format('d M Y') ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="tab-pane fade p-4" id="step2">
                        <h5 class="mb-3">Informasi Supplier</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Supplier Name</label>
                                <p class="form-control-plaintext">{{ $psw->supplier_name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Address</label>
                                <p class="form-control-plaintext">{{ $psw->supplier_address ?? '-' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Contact</label>
                                <p class="form-control-plaintext">{{ $psw->supplier_contact ?? '-' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Phone</label>
                                <p class="form-control-plaintext">{{ $psw->supplier_phone ?? '-' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Email</label>
                                <p class="form-control-plaintext">{{ $psw->supplier_email ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="tab-pane fade p-4" id="step3">
                        <h5 class="mb-3">Alasan & Hasil Evaluasi</h5>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Alasan Perubahan</label>
                            <p class="form-control-plaintext" style="white-space: pre-wrap;">{{ $psw->reason_for_change ?? '-' }}</p>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Evaluation Result</label>
                                <p class="form-control-plaintext">{{ $psw->evaluation_result ?? '-' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Durability Test</label>
                                <p class="form-control-plaintext">{{ $psw->durability_test ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Functionality Test</label>
                            <p class="form-control-plaintext">{{ $psw->functionality_test ?? '-' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Test Results</label>
                            <p class="form-control-plaintext" style="white-space: pre-wrap;">{{ $psw->test_results ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="tab-pane fade p-4" id="step4">
                        <h5 class="mb-3">Deklarasi</h5>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Declaration Text</label>
                            <p class="form-control-plaintext" style="white-space: pre-wrap;">{{ $psw->declaration_text ?? '-' }}</p>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Date</label>
                                <p class="form-control-plaintext">{{ $psw->declaration_date?->format('d M Y') ?? '-' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted">Signatory</label>
                                <p class="form-control-plaintext">{{ $psw->declaration_signatory ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Internal Review Section (Admin Only) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Internal Review Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Reviewed By</label>
                            <p class="form-control-plaintext">{{ $psw->reviewed_by ?? 'Not yet reviewed' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Reviewed Date</label>
                            <p class="form-control-plaintext">{{ $psw->reviewed_date?->format('d M Y H:i') ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Internal Review Status</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-{{ $psw->internal_review_status === 'APPROVED' ? 'success' : ($psw->internal_review_status === 'REJECTED' ? 'danger' : 'warning') }}">
                                    {{ $psw->internal_review_status }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted">Review Notes</label>
                        <p class="form-control-plaintext" style="white-space: pre-wrap;">{{ $psw->internal_review_notes ?? 'No notes' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

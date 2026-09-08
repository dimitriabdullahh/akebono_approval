<div class="p-4">
    <!-- Header -->
    <div class="mb-4 pb-3 border-bottom">
        <h5 class="mb-3">
            <i class="ti ti-package me-2"></i>Packaging Standard (PS)
        </h5>
        <div class="row">
            <div class="col-md-4">
                <small class="text-muted">Document No</small>
                <p class="mb-0 fw-semibold">{{ $ps->document_no ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Status</small>
                <p class="mb-0">
                    <span class="badge bg-{{ $ps->status_color }}">{{ $ps->status_label }}</span>
                </p>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Supplier</small>
                <p class="mb-0">{{ $ps->supplier?->name ?? $ps->supplier_name }}</p>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" href="#ps-step1" data-bs-toggle="tab">
                <i class="ti ti-info-circle me-2"></i>Informasi Umum
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#ps-step2" data-bs-toggle="tab">
                <i class="ti ti-package me-2"></i>Spesifikasi
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#ps-step3" data-bs-toggle="tab">
                <i class="ti ti-photo me-2"></i>Visualisasi
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Step 1 -->
        <div class="tab-pane fade show active" id="ps-step1">
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Customer</small>
                    <p class="mb-0">{{ $ps->customer }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Part Number</small>
                    <p class="mb-0 fw-semibold">{{ $ps->part_no }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Part Name</small>
                    <p class="mb-0">{{ $ps->part_name }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Quantity</small>
                    <p class="mb-0">{{ $ps->qty }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Date</small>
                    <p class="mb-0">{{ $ps->date?->format('d M Y') ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Revision</small>
                    <p class="mb-0">{{ $ps->revision ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="tab-pane fade" id="ps-step2">
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Jenis Kemasan</small>
                    <p class="mb-0">{{ $ps->packaging_type }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Ukuran Kemasan (mm)</small>
                    <p class="mb-0">{{ $ps->packaging_size_mm }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Lot per Box (pcs)</small>
                    <p class="mb-0">{{ $ps->packaging_lot_pcs_box }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Net Weight per Piece (g)</small>
                    <p class="mb-0">{{ $ps->net_weight_g_pc ?? '-' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Total Weight per Box (kg)</small>
                    <p class="mb-0">{{ $ps->total_weight_kg_box ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="tab-pane fade" id="ps-step3">
            @if($ps->image_photographic || $ps->image_part_identification || $ps->image_visualisasi_packing || $ps->image_label_packing)
                <div class="row mb-3">
                    @if($ps->image_photographic)
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block mb-2">Photographic Image</small>
                            <img src="{{ asset($ps->image_photographic) }}" class="img-fluid" style="max-height: 200px; object-fit: contain;">
                        </div>
                    @endif
                    @if($ps->image_part_identification)
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block mb-2">Part Identification</small>
                            <img src="{{ asset($ps->image_part_identification) }}" class="img-fluid" style="max-height: 200px; object-fit: contain;">
                        </div>
                    @endif
                    @if($ps->image_visualisasi_packing)
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block mb-2">Packaging Visualization</small>
                            <img src="{{ asset($ps->image_visualisasi_packing) }}" class="img-fluid" style="max-height: 200px; object-fit: contain;">
                        </div>
                    @endif
                    @if($ps->image_label_packing)
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block mb-2">Label & Packing</small>
                            <img src="{{ asset($ps->image_label_packing) }}" class="img-fluid" style="max-height: 200px; object-fit: contain;">
                        </div>
                    @endif
                </div>
            @endif

            <!-- Approval Table -->
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

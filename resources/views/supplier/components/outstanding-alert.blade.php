{{-- Outstanding Documents Alert Box Component --}}
{{-- Always visible when there are outstanding documents --}}
<div id="outstanding-alert-container" class="mb-4" style="display: none;">
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-warning text-white border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="ti ti-alert-triangle me-2"></i>
                        Pemberitahuan Penting - Outstanding PPAP Dokumen
                    </h5>
                    <p class="mb-0 small mt-1 opacity-90">
                        Anda memiliki <span id="outstanding-count" class="fw-bold">0</span> permintaan dokumen yang memerlukan perhatian
                    </p>
                </div>
                <button type="button" class="btn btn-sm btn-light" id="collapse-alert-btn" title="Tutup/Buka daftar">
                    <i class="ti ti-chevron-up" id="collapse-icon"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0" id="alert-body-collapsible">
            <!-- Detail Permintaan Section -->
            <div class="p-4 bg-light border-bottom">
                <div class="row">
                    <div class="col-md-3">
                        <small class="d-block text-muted mb-1">Supplier</small>
                        <strong id="supplier-name">-</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="d-block text-muted mb-1">PIC</small>
                        <strong>{{ $request->supplier->pic_name }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="d-block text-muted mb-1">Model</small>
                        <strong>{{ $request->model }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="d-block text-muted mb-1">Part Number</small>
                        <strong>{{ $request->part_number }}</strong>
                    </div>
                </div>
            </div>

            <!-- Penting Section dengan Bullet Points -->
            <div class="p-4 bg-warning bg-opacity-10 border-bottom">
                <h6 class="mb-3 text-warning"><i class="ti ti-info-circle me-2"></i>Penting:</h6>
                <ul class="mb-0 small text-dark">
                    <li class="mb-2">Dokumen bertanda <strong>"*"</strong> adalah <strong>WAJIB</strong> untuk diupload</li>
                    <li class="mb-2">Pastikan semua dokumen wajib diupload <strong>sebelum batas waktu</strong></li>
                    <li class="mb-2">File yang diupload harus dalam format <strong>PDF dengan ukuran maksimal 10MB</strong></li>
                    <li class="mb-2">Periksa kembali dokumen sebelum disubmit</li>
                </ul>
            </div>

            <!-- Table Controls -->
            <div class="p-3 bg-light border-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary active" data-sort="due_date">
                                <i class="ti ti-calendar"></i> Due Date
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-sort="request_date">
                                <i class="ti ti-clock"></i> Request Date
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-sort="status">
                                <i class="ti ti-flag"></i> Status
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="toggle-sort-direction">
                            <i class="ti ti-sort-ascending" id="sort-icon"></i>
                            <span id="sort-direction-text">Ascending</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Outstanding Documents Table -->
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="outstanding-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Request Date</th>
                            <th>Model</th>
                            <th>Due Date</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="outstanding-table-body">
                        <!-- Data will be loaded via AJAX -->
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted small mt-2 mb-0">Memuat data...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Statistics Footer -->
            <div class="card-footer bg-light border-0">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="p-2">
                            <div class="h4 mb-1 text-primary" id="stat-total">0</div>
                            <small class="text-muted">Total Outstanding</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-2">
                            <div class="h4 mb-1 text-warning" id="stat-open">0</div>
                            <small class="text-muted">Open</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-2">
                            <div class="h4 mb-1 text-danger" id="stat-reject">0</div>
                            <small class="text-muted">Reject (Revisi)</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-2">
                            <div class="h4 mb-1 text-danger" id="stat-overdue">0</div>
                            <small class="text-muted">Overdue</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Styles --}}
<style>
#outstanding-alert-container .table tbody tr {
    transition: all 0.2s ease;
}

#outstanding-alert-container .table tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

#outstanding-alert-container .btn-group .btn.active {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

.badge-status {
    padding: 0.35em 0.65em;
    font-size: 0.85em;
    font-weight: 600;
}

.row-overdue {
    background-color: rgba(220, 53, 69, 0.05);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>

{{-- Load JavaScript --}}
@push('scripts')
<script src="{{ asset('js/outstanding-alert.js') }}"></script>
@endpush

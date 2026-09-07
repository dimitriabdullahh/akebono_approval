@extends('layouts.app')

@section('title', 'Buat Document Request Baru')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="ti ti-plus-circle"></i> Buat Document Request Baru</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('requests.store') }}" method="POST" id="createRequestForm">
                        @csrf

                        <div class="mb-3">
                            <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                            <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id" required>
                                <option value="">-- Pilih Supplier --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                        {{ $supplier->name }} ({{ $supplier->vendor_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="model" class="form-label">Model <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('model') is-invalid @enderror" id="model" name="model" value="{{ old('model') }}" required>
                            @error('model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="due_date" class="form-label">Tanggal Batas Akhir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date') }}" required>
                            <small class="text-muted">Harus lebih besar dari hari ini</small>
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="ti ti-package"></i> Part Details</h5>
                            <button type="button" class="btn btn-sm btn-success" id="addPartBtn">
                                <i class="ti ti-plus"></i> Tambah Part
                            </button>
                        </div>

                        <div id="partsContainer">
                            <!-- Part items akan ditambahkan di sini -->
                        </div>

                        <hr class="my-4">

                        <!-- DOCUMENT REQUIREMENT SECTION -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0"><i class="ti ti-file-check"></i> Document Requirement</h5>
                                <div>
                                    <span class="badge bg-info me-2" id="selected-count">Selected: 0 / {{ count($documentTypes) }}</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                                        <i class="ti ti-checkbox"></i> Select All
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAll()">
                                        <i class="ti ti-x"></i> Clear All
                                    </button>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mb-3">
                                <i class="ti ti-info-circle"></i> 
                                <strong>Select the documents required for this request.</strong><br>
                                <small>Only selected documents will be requested from the supplier. You must select at least one document.</small>
                            </div>
                            
                            <!-- Document Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0" style="table-layout: fixed;">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60px;" class="text-center">NO</th>
                                            <th>DOCUMENT NAME</th>
                                            <th style="width: 50px;" class="text-center ps-1 pe-1">
                                                <input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll()" class="form-check-input">
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($documentTypes as $index => $docType)
                                        <tr class="document-row" id="row_{{ $docType->id }}" style="cursor: pointer;" onclick="toggleCheckbox({{ $docType->id }})">
                                            <td class="text-center">
                                                <strong>{{ $index + 1 }}</strong>
                                            </td>
                                            <td class="pe-1">
                                                <label for="doc_{{ $docType->id }}" class="mb-0 w-100" style="cursor: pointer;">
                                                    <div class="fw-semibold">{{ $docType->name }}</div>
                                                    @if($docType->description)
                                                        <small class="text-muted">{{ $docType->description }}</small>
                                                    @endif
                                                </label>
                                            </td>
                                            <td class="text-center ps-1 pe-1" onclick="event.stopPropagation();">
                                                <input class="form-check-input document-checkbox" 
                                                       type="checkbox" 
                                                       name="selected_documents[]" 
                                                       value="{{ $docType->id }}" 
                                                       id="doc_{{ $docType->id }}"
                                                       onchange="updateCount()">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <div id="validation-error" class="alert alert-danger mt-3" style="display: none;">
                                <i class="ti ti-alert-circle"></i> <strong>Error:</strong> Please select at least one document requirement before submitting.
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check"></i> Buat Request & Kirim Email
                            </button>
                            <a href="{{ route('requests.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .part-item {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1rem;
        position: relative;
    }
    .btn-remove-part {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
    }
    
    /* Document Table Styles */
    .document-row {
        transition: background-color 0.2s ease;
    }
    .document-row:hover {
        background-color: #f8f9fa !important;
    }
    .document-row.selected {
        background-color: #e7f3ff !important;
    }
    .table-responsive {
        border-radius: 0.375rem;
        overflow: hidden;
    }
    .table {
        font-size: 0.875rem; /* Smaller font size */
        margin-bottom: 0;
    }
    .table thead th {
        font-weight: 600;
        font-size: 0.75rem; /* Even smaller for headers */
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.5rem;
    }
    .table tbody td {
        vertical-align: middle;
        padding: 0.5rem;
        font-size: 0.813rem; /* 13px */
    }
    .table tbody td .fw-semibold {
        font-size: 0.875rem; /* 14px for document names */
    }
    .table tbody td small {
        font-size: 0.75rem; /* 12px for descriptions */
    }
    .form-check-input {
        cursor: pointer;
        width: 1.1rem;
        height: 1.1rem;
    }
    .badge {
        font-size: 0.7rem;
        padding: 0.3rem 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
let partCounter = 0;

// === DOCUMENT SELECTION FUNCTIONS ===
function updateCount() {
    const checkboxes = document.querySelectorAll('.document-checkbox');
    const checked = document.querySelectorAll('.document-checkbox:checked').length;
    const total = checkboxes.length;
    document.getElementById('selected-count').textContent = `Selected: ${checked} / ${total}`;
    
    // Update badge color based on selection
    const badge = document.getElementById('selected-count');
    if (checked === 0) {
        badge.className = 'badge bg-danger me-2';
    } else if (checked === total) {
        badge.className = 'badge bg-success me-2';
    } else {
        badge.className = 'badge bg-info me-2';
    }
    
    // Update "Select All" checkbox in table header
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = (checked === total);
        selectAllCheckbox.indeterminate = (checked > 0 && checked < total);
    }
    
    // Update row highlighting
    checkboxes.forEach(cb => {
        const row = document.getElementById('row_' + cb.value);
        if (row) {
            if (cb.checked) {
                row.classList.add('selected');
            } else {
                row.classList.remove('selected');
            }
        }
    });
    
    // Hide validation error if at least 1 selected
    if (checked > 0) {
        document.getElementById('validation-error').style.display = 'none';
    }
}

function selectAll() {
    document.querySelectorAll('.document-checkbox').forEach(cb => {
        cb.checked = true;
    });
    updateCount();
}

function clearAll() {
    document.querySelectorAll('.document-checkbox').forEach(cb => {
        cb.checked = false;
    });
    updateCount();
}

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.document-checkbox');
    
    checkboxes.forEach(cb => {
        cb.checked = selectAllCheckbox.checked;
    });
    
    updateCount();
}

function toggleCheckbox(docId) {
    const checkbox = document.getElementById('doc_' + docId);
    if (checkbox) {
        checkbox.checked = !checkbox.checked;
        updateCount();
    }
}

// === PART MANAGEMENT FUNCTIONS ===
// Template untuk part item
function getPartTemplate(index) {
    return `
        <div class="part-item" data-part-index="${index}">
            <button type="button" class="btn btn-sm btn-danger btn-remove-part remove-part-btn">
                <i class="ti ti-trash"></i>
            </button>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-2">
                        <label class="form-label">Part Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="parts[${index}][part_name]" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-2">
                        <label class="form-label">Part Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="parts[${index}][part_number]" required>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Fungsi untuk menambahkan part
function addPart() {
    const container = document.getElementById('partsContainer');
    const partHtml = getPartTemplate(partCounter);
    container.insertAdjacentHTML('beforeend', partHtml);
    partCounter++;
    updateRemoveButtons();
}

// Fungsi untuk menghapus part
function removePart(partElement) {
    const parts = document.querySelectorAll('.part-item');
    if (parts.length > 1) {
        partElement.remove();
        updateRemoveButtons();
    } else {
        alert('Minimal harus ada 1 part!');
    }
}

// Update visibility tombol hapus
function updateRemoveButtons() {
    const parts = document.querySelectorAll('.part-item');
    parts.forEach((part) => {
        const removeBtn = part.querySelector('.remove-part-btn');
        if (parts.length === 1) {
            removeBtn.style.display = 'none';
        } else {
            removeBtn.style.display = 'block';
        }
    });
}

// === EVENT LISTENERS ===
document.addEventListener('DOMContentLoaded', function() {
    // Initialize document count
    updateCount();
    
    // Tambah part pertama secara otomatis
    addPart();
    
    // Tombol tambah part
    document.getElementById('addPartBtn').addEventListener('click', function() {
        addPart();
    });
    
    // Event delegation untuk tombol hapus part
    document.getElementById('partsContainer').addEventListener('click', function(e) {
        if (e.target.closest('.remove-part-btn')) {
            const partElement = e.target.closest('.part-item');
            removePart(partElement);
        }
    });
    
    // Set minimal date untuk due date
    const today = new Date().toISOString().split('T')[0];
    const dueDateInput = document.getElementById('due_date');
    if (dueDateInput) {
        dueDateInput.setAttribute('min', today);
    }
    
    // Form validation
    document.getElementById('createRequestForm').addEventListener('submit', function(e) {
        const checked = document.querySelectorAll('.document-checkbox:checked').length;
        if (checked === 0) {
            e.preventDefault();
            const errorDiv = document.getElementById('validation-error');
            errorDiv.style.display = 'block';
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Shake animation for error
            errorDiv.classList.add('animate__animated', 'animate__shakeX');
            setTimeout(() => {
                errorDiv.classList.remove('animate__animated', 'animate__shakeX');
            }, 1000);
            
            return false;
        }
    });
});
</script>
@endpush
@endsection

@extends('layouts.app')

@section('title', 'Dashboard Supplier')

@section('content')
<div class="container-fluid py-4">
    <!-- Document Requests List -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <h5 class="mb-0">
                            <i class="ti ti-file-text"></i> Document Requests
                        </h5>
                        <div class="d-flex gap-2 flex-wrap">
                            <!-- Search Bar -->
                            <div class="input-group" style="max-width: 300px;">
                                <span class="input-group-text bg-white">
                                    <i class="ti ti-search"></i>
                                </span>
                                <input type="text" 
                                       id="searchInput" 
                                       class="form-control" 
                                       placeholder="Search by ID or Model...">
                            </div>
                            
                            <!-- Status Filter -->
                            <select id="statusFilter" class="form-select" style="max-width: 200px;">
                                <option value="">All Status</option>
                                <option value="not-started">Not Started</option>
                                <option value="in-progress">In Progress</option>
                                <option value="under-review">Under Review</option>
                                <option value="revision-required">Revision Required</option>
                                <option value="completed">Completed</option>
                            </select>
                            
                            <!-- Due Date Sort -->
                            <select id="sortFilter" class="form-select" style="max-width: 200px;">
                                <option value="due-date-desc">Due Date (Newest)</option>
                                <option value="due-date-asc">Due Date (Oldest)</option>
                                <option value="id-desc">Request ID (Newest)</option>
                                <option value="id-asc">Request ID (Oldest)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($documentRequests->isEmpty())
                        <div class="text-center py-5">
                            <i class="ti ti-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-3">Belum ada document request</p>
                        </div>
                    @else
                        <!-- Results Summary -->
                        <div class="px-3 py-2 bg-light border-bottom">
                            <small class="text-muted">
                                Showing <span id="visibleCount">{{ $documentRequests->count() }}</span> of <span id="totalCount">{{ $documentRequests->count() }}</span> requests
                            </small>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="requestsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>REQUEST ID</th>
                                        <th>MODEL NAME</th>
                                        <th>DUE DATE</th>
                                        <th>STATUS</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documentRequests as $request)
                                    @php
                                        $totalDocs = $request->requestDocuments->count();
                                        $pendingDocs = $request->requestDocuments->where('status', 'PENDING')->count();
                                        $submittedDocs = $request->requestDocuments->where('status', 'SUBMITTED')->count();
                                        $approvedDocs = $request->requestDocuments->where('status', 'APPROVED')->count();
                                        $rejectedDocs = $request->requestDocuments->where('status', 'REJECTED')->count();
                                        
                                        $statusClass = 'secondary';
                                        $statusText = 'Not Started';
                                        $statusSlug = 'not-started';
                                        
                                        if ($approvedDocs == $totalDocs && $totalDocs > 0) {
                                            $statusClass = 'success';
                                            $statusText = 'Completed';
                                            $statusSlug = 'completed';
                                        } elseif ($pendingDocs == $totalDocs) {
                                            $statusClass = 'secondary';
                                            $statusText = 'Not Started';
                                            $statusSlug = 'not-started';
                                        } elseif ($rejectedDocs > 0) {
                                            $statusClass = 'danger';
                                            $statusText = 'Revision Required';
                                            $statusSlug = 'revision-required';
                                        } elseif ($submittedDocs > 0) {
                                            $statusClass = 'info';
                                            $statusText = 'Under Review';
                                            $statusSlug = 'under-review';
                                        } else {
                                            $statusClass = 'warning';
                                            $statusText = 'In Progress';
                                            $statusSlug = 'in-progress';
                                        }
                                    @endphp
                                    <tr data-request-id="{{ $request->id }}"
                                        data-model="{{ strtolower($request->model ?? '') }}"
                                        data-status="{{ $statusSlug }}"
                                        data-due-date="{{ $request->due_date->format('Y-m-d') }}">
                                        <td><span class="badge bg-dark">#CP{{ $request->id }}</span></td>
                                        <td>
                                            <strong>{{ $request->model ?? '-' }}</strong>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $request->due_date->format('d M Y') }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('supplier.request', $request->id) }}" class="btn btn-sm btn-primary">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- No Results Message -->
                        <div id="noResults" class="text-center py-5" style="display: none;">
                            <i class="ti ti-search-off fs-1 text-muted"></i>
                            <p class="text-muted mt-3">No requests found matching your filters</p>
                            <button class="btn btn-sm btn-outline-secondary" onclick="clearFilters()">
                                Clear Filters
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- TEMPORARILY HIDDEN: Outstanding Documents Section
    @if($outstandingDocuments->isNotEmpty())
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm border-danger">
                <div class="card-header bg-danger bg-opacity-10">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-danger">
                            <i class="ti ti-alert-circle"></i> Outstanding Documents - Action Required
                        </h5>
                        <span class="badge bg-danger">{{ $outstandingDocuments->count() }} documents</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Request</th>
                                    <th>Document Type</th>
                                    <th>Parts</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($outstandingDocuments as $doc)
                                <tr class="{{ $doc->status === 'REJECTED' ? 'table-danger' : '' }}">
                                    <td>
                                        <strong>REQ-{{ $doc->documentRequest->id }}</strong><br>
                                        <small class="text-muted">{{ $doc->documentRequest->project_name }}</small>
                                    </td>
                                    <td>{{ $doc->documentType->name }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $doc->documentRequest->requestParts->count() }} parts
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $doc->documentRequest->due_date->format('d M Y') }}</small>
                                        @if($doc->documentRequest->due_date->isPast())
                                            <br><span class="badge bg-danger">Overdue</span>
                                        @elseif($doc->documentRequest->due_date->diffInDays(now()) <= 3)
                                            <br><span class="badge bg-warning">{{ $doc->documentRequest->due_date->diffInDays(now()) }} days left</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($doc->status === 'REJECTED')
                                            <span class="badge bg-danger">
                                                <i class="ti ti-x"></i> Revision Required
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="ti ti-clock"></i> Not Submitted
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('supplier.upload.form', $doc->id) }}" 
                                           class="btn btn-sm {{ $doc->status === 'REJECTED' ? 'btn-danger' : 'btn-warning' }}">
                                            <i class="ti ti-upload"></i> 
                                            {{ $doc->status === 'REJECTED' ? 'Reupload' : 'Upload' }}
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    --}}
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const sortFilter = document.getElementById('sortFilter');
    const tableBody = document.querySelector('#requestsTable tbody');
    const noResults = document.getElementById('noResults');
    const visibleCount = document.getElementById('visibleCount');
    const totalCount = document.getElementById('totalCount');
    
    if (!tableBody) return; // Exit if no table (empty state)
    
    let rows = Array.from(tableBody.querySelectorAll('tr'));
    
    function filterAndSort() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        const sortValue = sortFilter.value;
        
        // Filter rows
        let visibleRows = rows.filter(row => {
            const requestId = row.dataset.requestId;
            const model = row.dataset.model;
            const status = row.dataset.status;
            
            // Search filter
            const matchesSearch = searchTerm === '' || 
                                requestId.includes(searchTerm) || 
                                model.includes(searchTerm);
            
            // Status filter
            const matchesStatus = statusValue === '' || status === statusValue;
            
            return matchesSearch && matchesStatus;
        });
        
        // Sort rows
        visibleRows.sort((a, b) => {
            switch(sortValue) {
                case 'due-date-asc':
                    return new Date(a.dataset.dueDate) - new Date(b.dataset.dueDate);
                case 'due-date-desc':
                    return new Date(b.dataset.dueDate) - new Date(a.dataset.dueDate);
                case 'id-asc':
                    return parseInt(a.dataset.requestId) - parseInt(b.dataset.requestId);
                case 'id-desc':
                    return parseInt(b.dataset.requestId) - parseInt(a.dataset.requestId);
                default:
                    return new Date(b.dataset.dueDate) - new Date(a.dataset.dueDate);
            }
        });
        
        // Update display
        tableBody.innerHTML = '';
        
        if (visibleRows.length === 0) {
            noResults.style.display = 'block';
            tableBody.closest('.table-responsive').style.display = 'none';
            visibleCount.textContent = '0';
        } else {
            noResults.style.display = 'none';
            tableBody.closest('.table-responsive').style.display = 'block';
            visibleRows.forEach(row => tableBody.appendChild(row));
            visibleCount.textContent = visibleRows.length;
        }
    }
    
    // Event listeners
    searchInput.addEventListener('input', filterAndSort);
    statusFilter.addEventListener('change', filterAndSort);
    sortFilter.addEventListener('change', filterAndSort);
    
    // Clear filters function
    window.clearFilters = function() {
        searchInput.value = '';
        statusFilter.value = '';
        sortFilter.value = 'due-date-desc';
        filterAndSort();
    };
    
    // Initialize with default sort
    filterAndSort();
});
</script>
@endpush
@endsection

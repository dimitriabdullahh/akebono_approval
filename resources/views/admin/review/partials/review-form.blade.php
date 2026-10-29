{{-- Review & Approval Form Partial --}}
@php
    $user = auth()->guard('employee')->check() 
        ? auth()->guard('employee')->user() 
        : auth()->user();
    
    // Ensure userScope is always an array
    $userScopeRaw = $user ? $user->getReviewScope() : null;
    $userScope = is_array($userScopeRaw) ? $userScopeRaw : ($userScopeRaw ? [$userScopeRaw] : null);
    
    // Use provided variables or default to false
    $isChecker = isset($isChecker) ? $isChecker : false;
    $isApprover = isset($isApprover) ? $isApprover : false;
    
    // Get approval history
    $approvals = $requestDocument->approvals ?? collect();
    $histories = $requestDocument->histories ?? collect();
    
    // Get e-signatures
    $checkerEsign = $requestDocument->checkerEsignature;
    $approverEsign = $requestDocument->approverEsignature;
@endphp

<div class="row">
    {{-- Document Status & Info --}}
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="ti ti-info-circle me-2"></i>Document Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label small text-muted">Status</label>
                            <div>
                                @php
                                    $statusColors = [
                                        'PENDING' => 'secondary',
                                        'SUBMITTED' => 'primary',
                                        'PENDING_REVIEW' => 'warning',
                                        'APPROVED' => 'success',
                                        'REJECTED' => 'danger',
                                        'REVISE' => 'warning',
                                    ];
                                    $statusColor = $statusColors[$requestDocument->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">{{ $requestDocument->status }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label small text-muted">Submitted At</label>
                            <p class="mb-0">{{ $requestDocument->submitted_at?->format('d M Y H:i') ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="mb-0">
                    <label class="form-label small text-muted">Your Review Scope</label>
                    <p class="mb-0 small">
                        @if($userScope && is_array($userScope))
                            @foreach($userScope as $scope)
                                <span class="badge bg-info me-1">{{ $scope }}</span>
                            @endforeach
                        @elseif($userScope)
                            <span class="badge bg-info me-1">{{ $userScope }}</span>
                        @else
                            <span class="text-muted">No scope assigned</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- E-Signature Status --}}
    @if($approverEsign)
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="ti ti-certificate me-2"></i>E-Signature Status</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="ti ti-certificate"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-1">
                            <strong>Approved by {{ $approverEsign->name }}</strong>
                            <span class="badge bg-primary ms-2">Signed</span>
                        </div>
                        <p class="text-muted small mb-1">
                            <i class="ti ti-id me-1"></i>NPK: {{ $approverEsign->npk }}
                        </p>
                        <small class="text-muted">
                            <i class="ti ti-calendar me-1"></i>{{ $approverEsign->signed_at->format('d M Y H:i') }}
                        </small>
                        @if($approverEsign->notes)
                            <p class="mt-2 mb-0 small"><strong>Notes:</strong> {{ $approverEsign->notes }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Approval History --}}
    @if($approvals->count() > 0)
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="ti ti-history me-2"></i>Approval History</h5>
            </div>
            <div class="card-body">
                @foreach($approvals as $approval)
                    <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-1">
                                <strong>
                                    @if($approval->employee)
                                        {{ $approval->employee->name ?? 'Unknown' }}
                                    @elseif($approval->user)
                                        {{ $approval->user->name ?? 'Unknown' }}
                                    @else
                                        Unknown
                                    @endif
                                </strong>
                                <span class="badge bg-{{ $approval->status === 'APPROVED' ? 'success' : 'warning' }} ms-2">
                                    {{ $approval->status }}
                                </span>
                            </div>
                            <p class="text-muted small mb-1">
                                <i class="ti ti-briefcase me-1"></i>{{ $approval->scope }}
                            </p>
                            @if($approval->comment)
                                <p class="mb-1 small"><strong>Notes:</strong> {{ $approval->comment }}</p>
                            @elseif($approval->note)
                                <p class="mb-1 small"><strong>Notes:</strong> {{ $approval->note }}</p>
                            @endif
                            <small class="text-muted">
                                {{ $approval->approved_at?->format('d M Y H:i') ?? $approval->created_at->format('d M Y H:i') }}
                            </small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Approval Actions --}}
    @if(in_array($requestDocument->status, ['SUBMITTED', 'PENDING_REVIEW']))
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="ti ti-clipboard-check me-2"></i>Review Actions</h5>
                </div>
                <div class="card-body">
                    @if($isApprover)
                        {{-- Approver Actions --}}
                        @if($approverEsign)
                            {{-- Already signed - show status --}}
                            <div class="alert alert-success">
                                <i class="ti ti-circle-check me-2"></i>
                                <strong>Document Already Approved</strong><br>
                                You have approved this document on {{ $approverEsign->signed_at->format('d M Y H:i') }}.<br>
                                <small class="text-muted">E-signature with NPK {{ $approverEsign->npk }} has been applied to the PDF.</small>
                            </div>
                        @else
                            {{-- Not signed yet - show action buttons --}}
                            <form id="approveForm" action="{{ route('review.approve', $requestDocument) }}" method="POST">
                                @csrf
                                <div class="d-grid gap-2 mb-3">
                                    <button type="button" 
                                            class="btn btn-success w-100" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#approveEsignModal" 
                                            style="padding: 0.75rem;"
                                            id="approveTriggerBtn">
                                        <i class="ti ti-check me-2"></i>Approve Document
                                    </button>
                                </div>
                            </form>

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal" style="padding: 0.75rem;">
                                    <i class="ti ti-x me-2"></i>Reject Document
                                </button>
                            </div>
                        @endif

                    @elseif($isChecker)
                        {{-- Checker Actions (No e-sign for PS) --}}
                        <form id="checkCompleteForm" action="{{ route('review.complete-check', $requestDocument) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Checker Notes (Optional)</label>
                                <textarea name="checker_notes" class="form-control" rows="3" placeholder="Add any notes or observations..."></textarea>
                            </div>

                            <div class="d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-success" style="padding: 0.75rem;">
                                    <i class="ti ti-check me-2"></i>Mark as Checked - Forward to Approver
                                </button>
                            </div>
                        </form>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#revisionModal" style="padding: 0.75rem;">
                                <i class="ti ti-edit me-2"></i>Request Revision from Supplier
                            </button>
                        </div>

                    @else
                        <div class="alert alert-info mb-0">
                            <i class="ti ti-info-circle me-2"></i>
                            You don't have permission to review this document.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @elseif($requestDocument->status === 'APPROVED')
        <div class="col-md-12">
            <div class="alert alert-success">
                <i class="ti ti-circle-check me-2"></i>
                <strong>Document Approved</strong><br>
                This document has been approved and is now final.
            </div>
        </div>
    @elseif($requestDocument->status === 'REJECTED')
        <div class="col-md-12">
            <div class="alert alert-danger">
                <i class="ti ti-circle-x me-2"></i>
                <strong>Document Rejected</strong><br>
                This document was rejected. Please check the rejection notes above.
            </div>
        </div>
    @endif
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('review.reject', $requestDocument) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-2"></i>
                        This will reject the document. The supplier will need to resubmit.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="notes" class="form-control" rows="4" required placeholder="Please explain why this document is being rejected..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Document</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Revision Modal (Checker only) --}}
<div class="modal fade" id="revisionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Revision from Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('review.checker-revision', $requestDocument) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        This will send the document back to supplier for revision.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Revision Notes <span class="text-danger">*</span></label>
                        <textarea name="notes" class="form-control" rows="4" required placeholder="Please explain what needs to be revised..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Request Revision</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Approver E-Sign Confirmation Modal --}}
<div class="modal fade" id="approveEsignModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success bg-opacity-10">
                <h5 class="modal-title" id="approveModalLabel"><i class="ti ti-certificate me-2"></i>Confirm Approval E-Signature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success mb-3">
                    <i class="ti ti-info-circle me-2"></i>
                    <strong>Approval Confirmation</strong>
                </div>
                
                <p class="mb-3">
                    Dengan melanjutkan, Anda akan menyetujui dokumen ini dan e-sign Anda akan dibubuhkan:
                </p>
                
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">NPK</small>
                                <div><strong>{{ auth()->guard('employee')->user()->npk ?? '-' }}</strong></div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Nama</small>
                                <div><strong>{{ auth()->guard('employee')->user()->name ?? '-' }}</strong></div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">Role</small>
                            <div><strong>Approver</strong></div>
                        </div>
                    </div>
                </div>
                
                <p class="text-muted small mb-0">
                    <i class="ti ti-info-circle me-1"></i>
                    E-signature berupa QR Code akan otomatis terbubuh pada kolom "QA" di bagian AAIJ Approval PDF dokumen. 
                    Data NPK dan nama Anda akan tersimpan sebagai bukti approval.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i>Batal
                </button>
                <button type="button" class="btn btn-success" id="confirmApproveBtn">
                    <i class="ti ti-check me-1"></i>Ya, Approve & Tanda Tangani
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let isSubmitting = false;
    
    // Handle confirm approval button
    const confirmBtn = document.getElementById('confirmApproveBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function(e) {
            if (isSubmitting) return;
            
            e.preventDefault();
            isSubmitting = true;
            
            // Update button state
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            
            // Get and submit form
            const approveForm = document.getElementById('approveForm');
            const modalElement = document.getElementById('approveEsignModal');
            
            if (approveForm && modalElement) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                modal.hide();
                
                // Wait for modal to close, then submit
                modalElement.addEventListener('hidden.bs.modal', function() {
                    approveForm.submit();
                }, { once: true });
            }
        });
    }
});
</script>
@endpush

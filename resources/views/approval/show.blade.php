@extends('layouts.approval')

@section('title')
Approval Dokumen - AAIJ Approval System
@endsection

@section('content')
<!-- Preview Panel (Kiri) -->
<div class="approval-preview">
    <div class="approval-preview-header">
        <h3>
            <i class="ti ti-file-pdf"></i>
            Pratinjau Dokumen
        </h3>
        <small class="text-muted">{{ $requestDocument->documentType->name }}</small>
    </div>
    
    <div class="approval-preview-body">
        <iframe src="{{ route('document-preview', ['id' => $requestDocument->id]) }}" title="Document Preview"></iframe>
    </div>
    
    <div class="approval-preview-footer">
        <a href="{{ route('document-download', ['id' => $requestDocument->id]) }}" class="btn btn-sm btn-outline-primary" target="_blank">
            <i class="ti ti-download"></i> Download PDF
        </a>
    </div>
</div>

<!-- Sidebar (Kanan) -->
<div class="approval-sidebar">
    <!-- Document Information Card -->
    <div class="approval-card">
        <div class="approval-card-header">
            <i class="ti ti-info-circle"></i>
            Informasi Dokumen
        </div>
        <div class="approval-card-body">
            <dl>
                <dt>Jenis Dokumen</dt>
                <dd>{{ $requestDocument->documentType->name ?? '-' }}</dd>

                <dt>Supplier</dt>
                <dd>{{ $requestDocument->documentRequest->supplier->name ?? '-' }}</dd>

                <dt>Part No</dt>
                <dd>
                    <code>{{ $requestDocument->documentRequest->part_no ?? '-' }}</code>
                </dd>

                <dt>Part Name</dt>
                <dd>{{ $requestDocument->documentRequest->part_name ?? '-' }}</dd>

                <dt>Tanggal Submission</dt>
                <dd>{{ $requestDocument->submitted_at?->format('d M Y H:i') ?? '-' }}</dd>

                <dt>Status Dokumen</dt>
                <dd>
                    @php
                        $statusBadgeClass = match($requestDocument->status) {
                            'SUBMITTED', 'PENDING_REVIEW' => 'primary',
                            'APPROVED' => 'success',
                            'REJECTED' => 'danger',
                            'REVISE' => 'warning',
                            default => 'secondary'
                        };
                    @endphp
                    <span class="badge badge-{{ $statusBadgeClass }}">
                        {{ $requestDocument->status }}
                    </span>
                </dd>

                <dt>Divisi Penerima</dt>
                <dd>
                    <span class="badge badge-info">{{ ucfirst($division) }}</span>
                </dd>

                <dt>Penerima</dt>
                <dd>
                    <strong>{{ $user->name }}</strong><br>
                    <small class="text-muted">NPK: {{ $user->npk }}</small>
                </dd>
            </dl>
        </div>
    </div>

    <!-- Approval Status Card -->
    <div class="approval-card">
        <div class="approval-card-header">
            <i class="ti ti-clipboard-check"></i>
            Status Persetujuan
        </div>
        <div class="approval-card-body">
            @foreach($requiredDivisions as $div)
                <div class="approval-status-item">
                    <span class="approval-status-label">
                        <strong>{{ ucfirst($div) }}</strong>
                    </span>
                    <span class="approval-status-badge">
                        @if($divisionsStatus[$div]['approved'])
                            <i class="ti ti-circle-check" style="color: var(--success-color);"></i>
                            <small>
                                <strong>Approved</strong><br>
                                by {{ $divisionsStatus[$div]['approver_name'] }}<br>
                                {{ $divisionsStatus[$div]['signed_at']?->format('d M Y H:i') }}
                            </small>
                        @else
                            <i class="ti ti-circle-dashed" style="color: #ccc;"></i>
                            <small class="text-muted">Menunggu...</small>
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Approval Actions Card -->
    @if($existingSignature)
        <!-- Already Approved -->
        <div class="approval-card">
            <div class="approval-card-header success">
                <i class="ti ti-circle-check"></i>
                Sudah Disetujui
            </div>
            <div class="approval-card-body">
                <div class="alert alert-custom success">
                    <p class="mb-2">
                        <strong>{{ $existingSignature->name }}</strong> ({{ $existingSignature->npk }})
                    </p>
                    <small>
                        Telah menyetujui dokumen ini pada {{ $existingSignature->signed_at->format('d M Y H:i') }}
                    </small>
                </div>
                
                @if($existingSignature->notes)
                    <div class="mb-2">
                        <strong>Catatan:</strong>
                        <p class="mb-0 small">{{ $existingSignature->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- Action Form -->
        <div class="approval-card">
            <div class="approval-card-header">
                <i class="ti ti-clipboard-check"></i>
                Aksi Persetujuan
            </div>
            <div class="approval-card-body">
                <form id="approvalForm">
                    @csrf
                    
                    <div class="form-group">
                        <label for="notes">Catatan (Opsional)</label>
                        <textarea 
                            class="form-control" 
                            id="notes" 
                            name="notes" 
                            rows="3"
                            placeholder="Tambahkan catatan jika diperlukan..."
                        ></textarea>
                        <small class="form-text text-muted">Max 1000 karakter</small>
                    </div>

                    <div class="approval-actions">
                        <button 
                            type="button" 
                            class="btn btn-success btn-block"
                            data-toggle="modal"
                            data-target="#approvalConfirmModal"
                            id="approveBtn"
                        >
                            <i class="ti ti-check"></i> Setujui Dokumen
                        </button>
                        
                        <button 
                            type="button" 
                            class="btn btn-warning btn-block"
                            data-toggle="modal"
                            data-target="#revisionConfirmModal"
                            id="reviseBtn"
                        >
                            <i class="ti ti-edit"></i> Minta Revisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<!-- Approval Confirmation Modal -->
<div class="modal fade" id="approvalConfirmModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-certificate"></i>
                    Konfirmasi Persetujuan E-Sign
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" role="alert">
                    <strong>Perhatian!</strong> Dengan melanjutkan, e-sign QR Anda akan terbubuh pada dokumen.
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">NPK</small>
                                <div><strong>{{ $user->npk }}</strong></div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Nama</small>
                                <div><strong>{{ $user->name }}</strong></div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">Divisi</small>
                            <div><strong>{{ ucfirst($division) }}</strong></div>
                        </div>
                    </div>
                </div>

                <p class="small text-muted">
                    <i class="ti ti-info-circle"></i>
                    Data NPK dan nama Anda akan tersimpan sebagai bukti approval.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="ti ti-x"></i> Batal
                </button>
                <button 
                    type="button" 
                    class="btn btn-success"
                    id="confirmApprovalBtn"
                >
                    <i class="ti ti-check"></i> Ya, Setujui & Tanda Tangani
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Revision Confirmation Modal -->
<div class="modal fade" id="revisionConfirmModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-edit"></i>
                    Konfirmasi Permintaan Revisi
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning" role="alert">
                    <strong>Perhatian!</strong> Dokumen akan dikirim kembali ke supplier untuk direvisi.
                </div>

                <div class="form-group">
                    <label for="revisionNotes">Catatan Revisi <span class="text-danger">*</span></label>
                    <textarea 
                        class="form-control" 
                        id="revisionNotes"
                        rows="4"
                        placeholder="Jelaskan apa yang perlu direvisi..."
                        minlength="10"
                        maxlength="2000"
                    ></textarea>
                    <small class="form-text text-muted">Min 10 karakter, Max 2000 karakter</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="ti ti-x"></i> Batal
                </button>
                <button 
                    type="button" 
                    class="btn btn-warning"
                    id="confirmRevisionBtn"
                >
                    <i class="ti ti-check"></i> Ya, Minta Revisi
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const token = '{{ $approvalToken->getPlainToken() }}';
    const approvalForm = document.getElementById('approvalForm');
    const confirmApprovalBtn = document.getElementById('confirmApprovalBtn');
    const confirmRevisionBtn = document.getElementById('confirmRevisionBtn');
    let isSubmitting = false;

    // Handle approval confirmation
    if (confirmApprovalBtn) {
        confirmApprovalBtn.addEventListener('click', function() {
            if (isSubmitting) return;
            isSubmitting = true;

            const notes = document.getElementById('notes').value;
            
            showLoading();
            
            fetch(`{{ route('approval.approve', '') }}/${token}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({ notes: notes }),
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();

                if (data.success) {
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('approvalConfirmModal')).hide();
                    
                    // Show success message
                    showSuccessMessage(data.signature);
                    
                    // Reload preview after 2 seconds
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showErrorMessage(data.message);
                    isSubmitting = false;
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                showErrorMessage('Terjadi kesalahan: ' + error.message);
                isSubmitting = false;
            });
        });
    }

    // Handle revision confirmation
    if (confirmRevisionBtn) {
        confirmRevisionBtn.addEventListener('click', function() {
            if (isSubmitting) return;

            const notes = document.getElementById('revisionNotes').value;
            
            if (!notes || notes.length < 10) {
                alert('Catatan revisi minimal 10 karakter');
                return;
            }

            isSubmitting = true;
            showLoading();
            
            fetch(`{{ route('approval.revise', '') }}/${token}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({ notes: notes }),
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();

                if (data.success) {
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('revisionConfirmModal')).hide();
                    
                    // Show success message
                    alert(data.message);
                    
                    // Redirect after 2 seconds
                    setTimeout(() => {
                        window.location.href = '{{ route('home') }}';
                    }, 2000);
                } else {
                    showErrorMessage(data.message);
                    isSubmitting = false;
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                showErrorMessage('Terjadi kesalahan: ' + error.message);
                isSubmitting = false;
            });
        });
    }

    function showSuccessMessage(signature) {
        const card = document.querySelector('.approval-card');
        const successHtml = `
            <div class="alert alert-custom success" style="margin-bottom: 1rem;">
                <strong><i class="ti ti-circle-check"></i> Dokumen Berhasil Disetujui!</strong><br>
                <small>
                    ${signature.name} (${signature.npk})<br>
                    ${signature.signed_at}
                </small>
            </div>
        `;
        card.insertAdjacentHTML('afterbegin', successHtml);
    }

    function showErrorMessage(message) {
        alert(message);
    }
});
</script>
@endpush

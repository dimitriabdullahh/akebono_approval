<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Document Approval - {{ $documentType }} - PT Akebono</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .approval-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }
        .approval-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .card-header-custom h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .card-header-custom .subtitle {
            margin-top: 8px;
            opacity: 0.9;
            font-size: 16px;
        }
        .token-info {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px;
            border-radius: 6px;
        }
        .info-section {
            padding: 30px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .info-card {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .info-card h3 {
            font-size: 14px;
            color: #6b7280;
            margin: 0 0 8px 0;
            font-weight: 600;
            text-transform: uppercase;
        }
        .info-card p {
            margin: 0;
            font-size: 18px;
            color: #1f2937;
            font-weight: 600;
        }
        .approval-status-section {
            background: #eff6ff;
            padding: 20px;
            margin: 20px;
            border-radius: 8px;
            border: 2px solid #3b82f6;
        }
        .approval-status-section h3 {
            color: #1e40af;
            margin: 0 0 15px 0;
            font-size: 18px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            margin: 5px;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }
        .actions-section {
            padding: 30px;
            background: #f9fafb;
            border-top: 2px solid #e5e7eb;
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-approve {
            background: #10b981;
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-approve:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }
        .btn-revision {
            background: #ef4444;
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-revision:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }
        .btn-view-doc {
            background: #3b82f6;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-view-doc:hover {
            background: #2563eb;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        .modal-backdrop-custom {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
        }
        .modal-content-custom {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 16px;
            max-width: 500px;
            width: 90%;
            z-index: 1001;
        }
        .loader {
            display: none;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @media (max-width: 768px) {
            .approval-container {
                margin: 10px;
                padding: 10px;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
            .action-buttons {
                flex-direction: column;
            }
            .btn-approve, .btn-revision {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="approval-container">
        <div class="approval-card">
            <!-- Header -->
            <div class="card-header-custom">
                <h1>
                    @if($documentType === 'PSW')
                        📋 Part Submission Warrant Review
                    @else
                        📦 Packaging Standard Review
                    @endif
                </h1>
                <div class="subtitle">
                    {{ $approval->division }} Division Approval
                </div>
            </div>

            <!-- Token Info -->
            <div class="token-info">
                <div class="d-flex align-items-center">
                    <i class="ti ti-clock" style="font-size: 24px; margin-right: 12px; color: #f59e0b;"></i>
                    <div>
                        <strong>Token Expiry:</strong> {{ $approvalToken->expires_at->format('d F Y, H:i') }}<br>
                        <small>You have {{ $approvalToken->expires_at->diffForHumans() }} to complete this approval</small>
                    </div>
                </div>
            </div>

            <!-- Document Information -->
            <div class="info-section">
                <h2 style="color: #1f2937; margin-bottom: 20px;">📄 Document Information</h2>
                
                <div class="info-grid">
                    <div class="info-card">
                        <h3>Document Type</h3>
                        <p>{{ $documentType === 'PSW' ? 'Part Submission Warrant' : 'Packaging Standard' }}</p>
                    </div>
                    
                    <div class="info-card">
                        <h3>Document Number</h3>
                        <p>#{{ $documentData['id'] }}</p>
                    </div>
                    
                    <div class="info-card">
                        <h3>Supplier</h3>
                        <p>{{ $documentData['supplier']->name }}</p>
                    </div>
                    
                    <div class="info-card">
                        <h3>Model</h3>
                        <p>{{ $documentData['model'] }}</p>
                    </div>
                    
                    <div class="info-card">
                        <h3>Part Number</h3>
                        <p style="color: #3b82f6;">{{ $documentData['part_number'] ?? '-' }}</p>
                    </div>
                    
                    <div class="info-card">
                        <h3>Part Name</h3>
                        <p>{{ $documentData['part_name'] ?? '-' }}</p>
                    </div>
                </div>

                <!-- Multi-Approval Status -->
                @if(count($approvalStatus) > 1)
                <div class="approval-status-section">
                    <h3>📊 Multi-Division Approval Status</h3>
                    <p style="margin-bottom: 15px; color: #1e3a8a;">
                        This document requires approval from multiple divisions:
                    </p>
                    <div>
                        @foreach($approvalStatus as $division => $status)
                            @if($status['status'] === 'APPROVED')
                                <span class="status-badge status-approved">
                                    ✓ {{ $division }} - Approved
                                </span>
                            @else
                                <span class="status-badge status-pending">
                                    ⏳ {{ $division }} - Pending
                                    @if($division === $approval->division)
                                        (You)
                                    @endif
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- View Document Button -->
                <div class="text-center" style="margin-top: 30px;">
                    <a href="{{ route('external-approval.view-document', $approvalToken->getPlainToken() ?? 'invalid') }}" 
                       class="btn-view-doc" 
                       target="_blank">
                        <i class="ti ti-eye"></i> View Document PDF
                    </a>
                </div>
            </div>

            <!-- Actions -->
            <div class="actions-section">
                <h3 style="text-align: center; margin-bottom: 20px; color: #1f2937;">
                    Take Action
                </h3>
                
                <div class="action-buttons">
                    <button type="button" class="btn-approve" onclick="showApproveModal()">
                        <i class="ti ti-check"></i> Approve Document
                    </button>
                    
                    <button type="button" class="btn-revision" onclick="showRevisionModal()">
                        <i class="ti ti-alert-circle"></i> Request Revision
                    </button>
                </div>

                <div class="text-center mt-4">
                    <small class="text-muted">
                        Your decision will be recorded with digital signature and timestamp
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="modal-backdrop-custom">
        <div class="modal-content-custom">
            <h3 style="color: #10b981; margin-bottom: 20px;">
                <i class="ti ti-check-circle"></i> Confirm Approval
            </h3>
            <p style="margin-bottom: 20px;">
                Are you sure you want to approve this document?
            </p>
            
            <div class="mb-3">
                <label class="form-label">Comments (Optional)</label>
                <textarea id="approveComment" class="form-control" rows="3" 
                          placeholder="Add any comments about your approval..."></textarea>
            </div>
            
            <div class="d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-secondary" onclick="closeApproveModal()">
                    Cancel
                </button>
                <button type="button" class="btn btn-success" onclick="submitApproval()">
                    <i class="ti ti-check"></i> Confirm Approval
                </button>
            </div>
            
            <div class="loader" id="approveLoader"></div>
        </div>
    </div>

    <!-- Revision Modal -->
    <div id="revisionModal" class="modal-backdrop-custom">
        <div class="modal-content-custom">
            <h3 style="color: #ef4444; margin-bottom: 20px;">
                <i class="ti ti-alert-circle"></i> Request Revision
            </h3>
            <p style="margin-bottom: 20px;">
                Please provide a clear reason for requesting revision:
            </p>
            
            <div class="mb-3">
                <label class="form-label">Revision Reason <span class="text-danger">*</span></label>
                <textarea id="revisionReason" class="form-control" rows="4" 
                          placeholder="Explain what needs to be corrected..." required></textarea>
                <small class="text-danger" id="revisionError" style="display: none;"></small>
            </div>
            
            <div class="d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-secondary" onclick="closeRevisionModal()">
                    Cancel
                </button>
                <button type="button" class="btn btn-danger" onclick="submitRevision()">
                    <i class="ti ti-send"></i> Send Revision Request
                </button>
            </div>
            
            <div class="loader" id="revisionLoader"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const token = "{{ $approvalToken->getPlainToken() ?? 'invalid' }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function showApproveModal() {
            document.getElementById('approveModal').style.display = 'block';
        }

        function closeApproveModal() {
            document.getElementById('approveModal').style.display = 'none';
        }

        function showRevisionModal() {
            document.getElementById('revisionModal').style.display = 'block';
        }

        function closeRevisionModal() {
            document.getElementById('revisionModal').style.display = 'none';
            document.getElementById('revisionReason').value = '';
            document.getElementById('revisionError').style.display = 'none';
        }

        async function submitApproval() {
            const comment = document.getElementById('approveComment').value;
            const loader = document.getElementById('approveLoader');
            
            loader.style.display = 'block';
            
            try {
                const response = await fetch(`/approval/${token}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ comment }),
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'Failed to approve document');
                    loader.style.display = 'none';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while processing your approval');
                loader.style.display = 'none';
            }
        }

        async function submitRevision() {
            const reason = document.getElementById('revisionReason').value;
            const errorEl = document.getElementById('revisionError');
            const loader = document.getElementById('revisionLoader');
            
            // Validate reason
            if (!reason || reason.trim().length < 10) {
                errorEl.textContent = 'Please provide a detailed reason (at least 10 characters)';
                errorEl.style.display = 'block';
                return;
            }
            
            errorEl.style.display = 'none';
            loader.style.display = 'block';
            
            try {
                const response = await fetch(`/approval/${token}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ reason }),
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'Failed to request revision');
                    loader.style.display = 'none';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while requesting revision');
                loader.style.display = 'none';
            }
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const approveModal = document.getElementById('approveModal');
            const revisionModal = document.getElementById('revisionModal');
            
            if (event.target == approveModal) {
                closeApproveModal();
            }
            if (event.target == revisionModal) {
                closeRevisionModal();
            }
        }
    </script>
</body>
</html>

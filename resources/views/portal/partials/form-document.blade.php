<!-- Form-based Document (PSW, Packaging Standard, etc.) -->
<div class="card mb-3 document-card form-document-card" data-status="{{ $doc->status }}">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        @if($doc->documentType->is_mandatory)
                            <div class="bg-danger bg-opacity-10 rounded-circle p-2">
                                <i class="ti ti-star text-danger"></i>
                            </div>
                        @else
                            <div class="bg-secondary bg-opacity-10 rounded-circle p-2">
                                <i class="ti ti-file text-secondary"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-2 d-flex align-items-center gap-2">
                            {{ $doc->documentType->name }}
                            @if($doc->documentType->is_mandatory)
                                <span style="color: #DC2626; font-size: 1.2em; font-weight: bold;">*</span>
                            @endif
                        </h6>

                        @if($doc->documentType->scope)
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="ti ti-tag me-1"></i>Scope: {{ implode(', ', $doc->documentType->scope) }}
                                </small>
                            </div>
                        @endif

                        <!-- Document Type Info -->
                        <div class="alert alert-info mb-2 py-2" style="border-left: 4px solid #3b82f6;">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Form-Based Document:</strong> Silakan klik tombol "Isi Form" di bawah untuk mengisi data langsung. Dokumen akan disimpan dan dapat diubah sebelum submit.
                        </div>

                        @if($doc->status === 'REVISE')
                            <div class="revision-alert-card mb-2">
                                <div class="revision-header">
                                    <div class="revision-icon">
                                        <i class="ti ti-alert-triangle"></i>
                                    </div>
                                    <div class="revision-title">
                                        <strong>Revisi Diminta</strong>
                                        @if($doc->revision_deadline_at)
                                            <span class="revision-deadline">
                                                <i class="ti ti-clock"></i>
                                                {{ $doc->revision_deadline_at->format('d M Y H:i') }}
                                                <span class="deadline-status">
                                                    ({{ $doc->revision_deadline_at->diffForHumans() }})
                                                    @if($doc->is_deadline_passed)
                                                        <span class="badge bg-danger ms-1">TERLAMPAUI</span>
                                                    @endif
                                                </span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="revision-content">
                                    <div class="revision-note-wrapper">
                                        <div class="revision-note-display">
                                            <p class="revision-note-preview mb-0">
                                                {{ Str::limit($doc->revision_note, 150) }}
                                            </p>
                                            <div class="revision-note-full" data-full-text="{{ $doc->revision_note }}" style="display: none;">
                                                <p class="mb-0">{{ $doc->revision_note }}</p>
                                            </div>
                                        </div>
                                        @if(strlen($doc->revision_note) > 150)
                                            <button type="button" class="show-more-toggle mt-3" onclick="toggleRevisionNote(this)" data-expanded="false">
                                                <span class="toggle-text">Lihat Selengkapnya</span>
                                                <i class="ti ti-chevron-down toggle-icon"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(in_array($doc->status, ['SUBMITTED', 'APPROVED']))
                            <div class="alert alert-success mb-2 py-2" style="border-left: 4px solid #10B981;">
                                <div class="d-flex align-items-start">
                                    <i class="ti ti-check-circle me-2 mt-1 text-success"></i>
                                    <div>
                                        <strong>Status: {{ $doc->status === 'APPROVED' ? 'DISETUJUI' : 'DISUBMIT' }}</strong>
                                        <p class="mb-0 small">
                                            Submitted: {{ $doc->submitted_at?->format('d M Y H:i') }}<br>
                                            Last Updated: {{ $doc->updated_at?->format('d M Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="d-flex flex-column gap-2">
                    @php
                        $formRoute = null;
                        switch($doc->documentType->code) {
                            case 'PSW':
                                $formRoute = route('psw.form', ['token' => $doc->id]);
                                break;
                            case 'PACKAGING_STANDARD':
                                $formRoute = route('ps.form', ['token' => $doc->id]);
                                break;
                        }
                    @endphp

                    @if($formRoute)
                        <a href="{{ $formRoute }}" target="_blank" class="btn btn-primary btn-sm">
                            <i class="ti ti-pencil me-2"></i>Isi Form
                        </a>
                    @endif

                    @if($doc->status === 'APPROVED')
                        <a href="{{ route('psw.pdf', ['pswId' => $doc->id]) }}" target="_blank" class="btn btn-success btn-sm">
                            <i class="ti ti-file-pdf me-2"></i>Lihat PDF
                        </a>
                        <a href="{{ route('psw.download', ['pswId' => $doc->id]) }}" class="btn btn-outline-success btn-sm">
                            <i class="ti ti-download me-2"></i>Download
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

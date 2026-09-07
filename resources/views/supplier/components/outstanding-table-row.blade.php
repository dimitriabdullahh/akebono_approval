{{-- Template for outstanding documents table row --}}
{{-- This is a Blade template that will be used by JavaScript to render rows --}}

<script type="text/template" id="outstanding-row-template">
    <tr class="outstanding-row ${isOverdue ? 'row-overdue' : ''}" data-request-id="${id}">
        <td class="text-center">${index}</td>
        <td>
            <div class="d-flex flex-column">
                <span class="fw-medium">${requestDateFormatted}</span>
                <small class="text-muted">${model}</small>
            </div>
        </td>
        <td>
            <span class="fw-medium">${model}</span>
            <br>
            <small class="text-muted">${partNumber}</small>
        </td>
        <td>
            <div class="d-flex flex-column">
                <span class="fw-medium ${isOverdue ? 'text-danger' : ''}">${dueDateFormatted}</span>
                ${isOverdue 
                    ? '<small class="badge bg-danger">Overdue</small>' 
                    : daysRemaining > 0 
                        ? `<small class="text-success">${daysRemaining} hari lagi</small>`
                        : daysRemaining === 0
                            ? '<small class="text-warning">Jatuh tempo hari ini</small>'
                            : '<small class="text-muted">Sudah terlewat</small>'
                }
            </div>
        </td>
        <td class="text-center">
            ${status === 'open' 
                ? '<span class="badge badge-status bg-warning text-dark">Open</span>'
                : '<span class="badge badge-status bg-danger">Reject</span>'
            }
            ${revisionDocuments > 0 
                ? `<br><small class="text-muted">${revisionDocuments} doc perlu revisi</small>`
                : submittedDocuments < totalDocuments
                    ? `<br><small class="text-muted">${submittedDocuments}/${totalDocuments} submitted</small>`
                    : ''
            }
        </td>
        <td class="text-center">
            <a href="${portalUrl}" class="btn btn-primary btn-sm" title="Upload Dokumen">
                <i class="ti ti-upload"></i> Upload Dokumen
            </a>
        </td>
    </tr>
</script>

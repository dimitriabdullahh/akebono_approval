<div class="card">
    <div class="card-body">
        <div class="alert alert-info border-start border-4 border-info mb-4">
            <i class="ti ti-info-circle me-2"></i>
            <strong>Review Summary:</strong> Please verify all information below is correct before submitting. 
            Once submitted, the system will generate a PDF document automatically.
        </div>

        <!-- Section A: General Information -->
        <div class="review-section">
            <div class="review-section-title">
                <i class="ti ti-info-circle"></i>
                A. General Information
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="review-item">
                        <label>Model</label>
                        <p id="review-model" class="review-value">-</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="review-item">
                        <label>Date of Issued</label>
                        <p id="review-dateIssued" class="review-value">-</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="review-item">
                        <label>Ref. No</label>
                        <p id="review-refNo" class="review-value">-</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="review-item">
                        <label>Part Route</label>
                        <p id="review-partRoute" class="review-value">-</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="review-item">
                        <label>Part No</label>
                        <p id="review-partNo" class="review-value">-</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="review-item">
                        <label>Part Name</label>
                        <p id="review-partName" class="review-value">-</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="review-item">
                        <label>Manufacturer</label>
                        <p id="review-manufacturer" class="review-value">-</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="review-item">
                        <label>Material</label>
                        <p id="review-material" class="review-value">-</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="review-item">
                        <label>General Tolerance</label>
                        <p id="review-generalTolerance" class="review-value">-</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="review-item">
                        <label>Weight</label>
                        <p id="review-weight" class="review-value">-</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="review-item">
                        <label>Revision Date</label>
                        <p id="review-revisionDate" class="review-value">-</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="review-item">
                        <label>Revision Record</label>
                        <p id="review-revisionRecord" class="review-value" style="white-space: pre-wrap;">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section B: Supplier & Manufacturer Information -->
        <div class="review-section">
            <div class="review-section-title">
                <i class="ti ti-building"></i>
                B. Supplier & Manufacturer Information
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="review-item">
                        <label>Supplier</label>
                        <p id="review-supplier" class="review-value">-</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="review-item">
                        <label>Customer</label>
                        <p id="review-customer" class="review-value">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section C: Part Drawing -->
        <div class="review-section">
            <div class="review-section-title">
                <i class="ti ti-photo"></i>
                C. Part Drawing
            </div>

            <div id="review-drawings-container">
                <!-- Drawings summary will be rendered here -->
            </div>
        </div>

        <!-- Section D: Inspection Items -->
        <div class="review-section">
            <div class="review-section-title">
                <i class="ti ti-table"></i>
                D. Inspection Items
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="review-items-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 15%;">Inspection Item</th>
                            <th style="width: 12%;">Instrument</th>
                            <th style="width: 15%;">Criterion</th>
                            <th style="width: 8%;">Rank</th>
                            <th style="width: 10%;">Supplier Sampling</th>
                            <th style="width: 10%;">AAIJ Sampling</th>
                            <th style="width: 15%;">Remark / Method</th>
                        </tr>
                    </thead>
                    <tbody id="review-items-body">
                        <!-- Items will be rendered here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.review-section {
    background: #f8f9fa;
    border-left: 4px solid #0d6efd;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.review-section-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
}

.review-section-title i {
    margin-right: 8px;
    font-size: 20px;
    color: #0d6efd;
}

.review-item {
    margin-bottom: 15px;
}

.review-item label {
    display: block;
    font-weight: 600;
    color: #495057;
    font-size: 12px;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.review-value {
    font-size: 14px;
    color: #333;
    margin: 0;
    padding: 8px;
    background: white;
    border-left: 3px solid #0d6efd;
    padding-left: 12px;
}

.review-value.empty {
    color: #999;
    font-style: italic;
}

.drawing-summary {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 10px;
    margin-bottom: 10px;
}

.drawing-summary-item {
    font-size: 12px;
    margin-bottom: 3px;
}

.drawing-summary-item strong {
    color: #495057;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // When Step 4 becomes active, populate review
    const checkInterval = setInterval(() => {
        const step4Content = document.getElementById('step-4');
        if (step4Content && step4Content.classList.contains('active')) {
            populateReview();
            clearInterval(checkInterval);
        }
    }, 100);

    function populateReview() {
        // Collect all form data
        const generalInfo = window.collectStep1Data ? window.collectStep1Data() : {};
        const drawings = window.formData.drawings || [];
        const items = window.formData.inspectionItems || [];

        // Populate general information
        document.getElementById('review-model').textContent = generalInfo.model || '-';
        document.getElementById('review-dateIssued').textContent = generalInfo.dateIssued || '-';
        document.getElementById('review-refNo').textContent = generalInfo.refNo || '-';
        document.getElementById('review-partRoute').textContent = generalInfo.partRoute || '-';
        document.getElementById('review-partNo').textContent = generalInfo.partNo || '-';
        document.getElementById('review-partName').textContent = generalInfo.partName || '-';
        document.getElementById('review-manufacturer').textContent = generalInfo.manufacturer || '-';
        document.getElementById('review-material').textContent = generalInfo.material || '-';
        document.getElementById('review-generalTolerance').textContent = generalInfo.generalTolerance || '-';
        document.getElementById('review-weight').textContent = generalInfo.weight ? `${generalInfo.weight} kg` : '-';
        document.getElementById('review-revisionDate').textContent = generalInfo.revisionDate || '-';
        document.getElementById('review-revisionRecord').textContent = generalInfo.revisionRecord || '-';

        // Supplier info
        document.getElementById('review-supplier').textContent = document.getElementById('supplier')?.value || '-';
        document.getElementById('review-customer').textContent = document.getElementById('customer')?.value || '-';

        // Drawings
        const drawingsContainer = document.getElementById('review-drawings-container');
        drawingsContainer.innerHTML = '';
        
        if (drawings.length === 0) {
            drawingsContainer.innerHTML = '<p class="text-muted">No drawings uploaded</p>';
        } else {
            drawings.forEach((drawing, index) => {
                const div = document.createElement('div');
                div.className = 'drawing-summary';
                div.innerHTML = `
                    <strong>Drawing ${index + 1}</strong>
                    <div class="drawing-summary-item"><strong>File:</strong> ${drawing.originalFilename}</div>
                    ${drawing.drawingName ? `<div class="drawing-summary-item"><strong>Name:</strong> ${drawing.drawingName}</div>` : ''}
                    ${drawing.drawingNo ? `<div class="drawing-summary-item"><strong>No:</strong> ${drawing.drawingNo}</div>` : ''}
                    ${drawing.revision ? `<div class="drawing-summary-item"><strong>Revision:</strong> ${drawing.revision}</div>` : ''}
                    ${drawing.description ? `<div class="drawing-summary-item"><strong>Description:</strong> ${drawing.description}</div>` : ''}
                `;
                drawingsContainer.appendChild(div);
            });
        }

        // Inspection Items
        const itemsBody = document.getElementById('review-items-body');
        itemsBody.innerHTML = '';
        
        if (items.length === 0) {
            itemsBody.innerHTML = '<tr><td colspan="8" class="text-muted text-center">No items added</td></tr>';
        } else {
            items.forEach((item, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="text-center">${index + 1}</td>
                    <td>${item.inspectionItem || '-'}</td>
                    <td>${item.inspectionInstrument || '-'}</td>
                    <td>${item.inspectionCriterion || '-'}</td>
                    <td class="text-center">${item.rank || '-'}</td>
                    <td>${item.supplierSampling || '-'}</td>
                    <td>${item.aaijSampling || '-'}</td>
                    <td>${item.remarkInspectionMethod || '-'}</td>
                `;
                itemsBody.appendChild(row);
            });
        }
    }

    // Validate Step 4 (all previous steps must be valid)
    window.validateStep4 = function() {
        return true; // Review is read-only, just display
    };
});
</script>

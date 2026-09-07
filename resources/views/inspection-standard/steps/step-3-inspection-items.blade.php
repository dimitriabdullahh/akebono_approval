<div class="card">
    <div class="card-body">
        <div class="form-section">
            <div class="form-section-title">
                <i class="ti ti-table"></i>
                Inspection Items
            </div>

            <p class="text-muted mb-4">
                Add inspection items below. You can add as many items as needed. 
                Fields marked with <span class="required-indicator">*</span> are required.
            </p>

            <!-- Inspection Items Table -->
            <div class="table-responsive">
                <table class="table table-hover mb-4" id="inspectionItemsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 15%;">Inspection Item <span class="required-indicator">*</span></th>
                            <th style="width: 12%;">Inspection Instrument</th>
                            <th style="width: 15%;">Inspection Criterion <span class="required-indicator">*</span></th>
                            <th style="width: 8%;">Rank <span class="required-indicator">*</span></th>
                            <th style="width: 10%;">Supplier Sampling</th>
                            <th style="width: 10%;">AAIJ Sampling</th>
                            <th style="width: 15%;">Remark / Inspection Method</th>
                            <th style="width: 10%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
                        <!-- Inspection items will be rendered here -->
                    </tbody>
                </table>
            </div>

            <!-- Add Item Button -->
            <div class="text-center mb-4">
                <button type="button" class="btn btn-outline-primary" id="addItemBtn">
                    <i class="ti ti-plus me-2"></i>Add Inspection Item
                </button>
            </div>

            <!-- Help Text -->
            <div class="alert alert-light border-start border-4 border-info">
                <i class="ti ti-info-circle me-2"></i>
                <strong>Tip:</strong> You must add at least one inspection item before submitting. 
                Click on table cells to edit, or use Edit button for full row editing.
            </div>
        </div>
    </div>
</div>

<style>
.items-table-input {
    width: 100%;
    border: none;
    padding: 5px;
    font-size: 12px;
}

.items-table-input:focus {
    outline: 1px solid #0d6efd;
    background-color: #eff6ff;
}

.items-table-input.is-invalid {
    background-color: #fee;
    border: 1px solid #dc3545;
}

table th, table td {
    vertical-align: middle;
    font-size: 13px;
}

.actions-cell {
    display: flex;
    gap: 3px;
}

.actions-cell button {
    padding: 3px 8px;
    font-size: 11px;
}

.inspection-item-row {
    transition: background-color 0.2s ease;
}

.inspection-item-row:hover {
    background-color: #f8f9fa;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[Step 3] Initializing Inspection Items step');
    
    // Wait for window.formData to be available
    const checkFormData = setInterval(function() {
        if (window.formData) {
            clearInterval(checkFormData);
            initializeStep3();
        }
    }, 100);
    
    function initializeStep3() {
        console.log('[Step 3] formData is available, initializing...');
        
        const addItemBtn = document.getElementById('addItemBtn');
        const tableBody = document.getElementById('itemsTableBody');
        
        // Initialize inspectionItems array if not exists
        if (!window.formData.inspectionItems) {
            window.formData.inspectionItems = [];
        }

    // Add Item button
    addItemBtn.addEventListener('click', () => {
        addNewItem();
    });

    function addNewItem() {
        const newItem = {
            id: 'temp_' + Math.random().toString(36).substr(2, 9),
            inspectionItem: '',
            inspectionInstrument: '',
            inspectionCriterion: '',
            rank: '',
            supplierSampling: '',
            aaijSampling: '',
            remarkInspectionMethod: '',
        };

        window.formData.inspectionItems.push(newItem);
        renderItems();
    }

    function renderItems() {
        tableBody.innerHTML = '';

        window.formData.inspectionItems.forEach((item, index) => {
            const row = document.createElement('tr');
            row.className = 'inspection-item-row';
            row.id = `item-row-${index}`;

            row.innerHTML = `
                <td class="text-center"><strong>${index + 1}</strong></td>
                <td>
                    <input type="text" class="items-table-input" 
                           value="${item.inspectionItem || ''}"
                           placeholder="Item description"
                           onchange="updateItemField(${index}, 'inspectionItem', this.value)">
                </td>
                <td>
                    <input type="text" class="items-table-input" 
                           value="${item.inspectionInstrument || ''}"
                           placeholder="Instrument"
                           onchange="updateItemField(${index}, 'inspectionInstrument', this.value)">
                </td>
                <td>
                    <input type="text" class="items-table-input" 
                           value="${item.inspectionCriterion || ''}"
                           placeholder="Criterion"
                           onchange="updateItemField(${index}, 'inspectionCriterion', this.value)">
                </td>
                <td>
                    <select class="items-table-input" 
                            onchange="updateItemField(${index}, 'rank', this.value)">
                        <option value="">Select</option>
                        <option value="A" ${item.rank === 'A' ? 'selected' : ''}>A</option>
                        <option value="B" ${item.rank === 'B' ? 'selected' : ''}>B</option>
                        <option value="C" ${item.rank === 'C' ? 'selected' : ''}>C</option>
                        <option value="D" ${item.rank === 'D' ? 'selected' : ''}>D</option>
                        <option value="S" ${item.rank === 'S' ? 'selected' : ''}>S</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="items-table-input" 
                           value="${item.supplierSampling || ''}"
                           placeholder="e.g., 1/lot"
                           onchange="updateItemField(${index}, 'supplierSampling', this.value)">
                </td>
                <td>
                    <input type="text" class="items-table-input" 
                           value="${item.aaijSampling || ''}"
                           placeholder="e.g., 1/lot"
                           onchange="updateItemField(${index}, 'aaijSampling', this.value)">
                </td>
                <td>
                    <input type="text" class="items-table-input" 
                           value="${item.remarkInspectionMethod || ''}"
                           placeholder="Method/Remark"
                           onchange="updateItemField(${index}, 'remarkInspectionMethod', this.value)">
                </td>
                <td>
                    <div class="actions-cell">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteItem(${index})">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                </td>
            `;

            tableBody.appendChild(row);
        });
    }
    
    // Expose renderItems globally for form.blade.php to call
    window.renderItems = renderItems;

    window.updateItemField = function(index, field, value) {
        window.formData.inspectionItems[index][field] = value;
    };

    window.deleteItem = function(index) {
        Swal.fire({
            title: 'Delete Item?',
            text: 'Are you sure you want to delete this inspection item?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.formData.inspectionItems.splice(index, 1);
                renderItems();
            }
        });
    };

    // Collect Step 3 data
    window.collectStep3Data = function() {
        return window.formData.inspectionItems;
    };

    // Validate Step 3
    window.validateStep3 = function() {
        const errors = [];

        if (window.formData.inspectionItems.length === 0) {
            errors.push('You must add at least one inspection item');
        }

        window.formData.inspectionItems.forEach((item, index) => {
            if (!item.inspectionItem || !item.inspectionItem.trim()) {
                errors.push(`Item #${index + 1}: Inspection Item is required`);
            }
            if (!item.inspectionCriterion || !item.inspectionCriterion.trim()) {
                errors.push(`Item #${index + 1}: Inspection Criterion is required`);
            }
            if (!item.rank) {
                errors.push(`Item #${index + 1}: Rank is required`);
            }
        });

        if (errors.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: errors.map(e => `<p class="mb-2">${e}</p>`).join(''),
                confirmButtonColor: '#0d6efd',
            });
            return false;
        }

        return true;
    };

    // Initial render
    renderItems();
    
    } // End of initializeStep3()
});
</script>

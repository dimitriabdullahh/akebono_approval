@extends('layouts.supplier')

@section('title', 'Inspection Standard Form - PT Akebono')

@section('content')
<div class="container py-4 animate-on-scroll">
    <!-- Header Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-3">
                                <i class="ti ti-file-text me-2"></i>Inspection Standard (IS) Form
                            </h2>
                            <p class="mb-2">Silakan isi form di bawah untuk membuat Inspection Standard secara digital.</p>
                            <div class="alert alert-info mb-0 bg-white bg-opacity-20 border-0">
                                <i class="ti ti-info-circle me-2"></i>
                                <strong>Info:</strong> Dokumen akan disimpan dan dapat diubah sebelum di-submit. Setelah submit, dokumen akan di-generate menjadi PDF otomatis.
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="bg-white bg-opacity-20 rounded-lg p-3">
                                <h4 class="text-white mb-2">Supplier</h4>
                                <p class="text-white-50 mb-0" id="supplierName">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stepper Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <!-- Step 1 -->
                        <div class="step-item active" data-step="1">
                            <div class="step-circle">
                                <i class="ti ti-info-circle"></i>
                            </div>
                            <p class="step-label mb-0">General<br>Information</p>
                        </div>
                        
                        <!-- Arrow -->
                        <div class="flex-grow-1 mx-2">
                            <div class="step-divider"></div>
                        </div>
                        
                        <!-- Step 2 -->
                        <div class="step-item" data-step="2">
                            <div class="step-circle">
                                <i class="ti ti-photo"></i>
                            </div>
                            <p class="step-label mb-0">Part<br>Drawing</p>
                        </div>
                        
                        <!-- Arrow -->
                        <div class="flex-grow-1 mx-2">
                            <div class="step-divider"></div>
                        </div>
                        
                        <!-- Step 3 -->
                        <div class="step-item" data-step="3">
                            <div class="step-circle">
                                <i class="ti ti-table"></i>
                            </div>
                            <p class="step-label mb-0">Inspection<br>Items</p>
                        </div>
                        
                        <!-- Arrow -->
                        <div class="flex-grow-1 mx-2">
                            <div class="step-divider"></div>
                        </div>
                        
                        <!-- Step 4 -->
                        <div class="step-item" data-step="4">
                            <div class="step-circle">
                                <i class="ti ti-check"></i>
                            </div>
                            <p class="step-label mb-0">Review &<br>Submit</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Content -->
    <div class="row">
        <div class="col-12">
            <form id="inspectionStandardForm" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Step 1: General Information -->
                <div class="step-content active" id="step-1">
                    @include('inspection-standard.steps.step-1-general-information')
                </div>

                <!-- Step 2: Part Drawing -->
                <div class="step-content" id="step-2">
                    @include('inspection-standard.steps.step-2-part-drawing')
                </div>

                <!-- Step 3: Inspection Items -->
                <div class="step-content" id="step-3">
                    @include('inspection-standard.steps.step-3-inspection-items')
                </div>

                <!-- Step 4: Review & Submit -->
                <div class="step-content" id="step-4">
                    @include('inspection-standard.steps.step-4-review-submit')
                </div>

                <!-- Navigation Buttons -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" id="backBtn" style="display: none;">
                                <i class="ti ti-arrow-left me-2"></i>Back
                            </button>
                            <div class="ms-auto">
                                <button type="button" class="btn btn-outline-primary me-2" id="saveDraftBtn">
                                    <i class="ti ti-floppy-disk me-2"></i>Save as Draft
                                </button>
                                <button type="button" class="btn btn-primary" id="nextBtn">
                                    <i class="ti ti-arrow-right me-2"></i>Next
                                </button>
                                <button type="button" class="btn btn-success" id="submitBtn" style="display: none;">
                                    <i class="ti ti-check me-2"></i>Submit Inspection Standard
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Styles for Stepper -->
<style>
.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 0 0 auto;
}

.step-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #e0e0e0;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 10px;
    transition: all 0.3s ease;
}

.step-item.active .step-circle,
.step-item.completed .step-circle {
    background-color: #0d6efd;
    color: white;
}

.step-item.completed .step-circle {
    background-color: #198754;
}

.step-label {
    font-size: 12px;
    font-weight: 500;
    text-align: center;
    color: #666;
}

.step-item.active .step-label,
.step-item.completed .step-label {
    color: #333;
    font-weight: 600;
}

.step-divider {
    height: 3px;
    background-color: #e0e0e0;
    transition: all 0.3s ease;
}

.step-item.completed ~ .step-divider {
    background-color: #198754;
}

.step-content {
    display: none;
}

.step-content.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-section {
    background: #f8f9fa;
    border-left: 4px solid #0d6efd;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.form-section-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
}

.form-section-title i {
    margin-right: 8px;
    font-size: 20px;
    color: #0d6efd;
}

.required-indicator {
    color: #dc3545;
    margin-left: 3px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const token = '{{ $token }}';
    let currentStep = 1;
    
    // Initialize formData IMMEDIATELY
    let formData = {
        inspectionStandardId: null,
        generalInformation: {},
        drawings: [],
        inspectionItems: []
    };
    
    // Expose globally IMMEDIATELY (before async operations)
    window.formData = formData;
    console.log('[Main Form] window.formData initialized globally');

    // Load existing data
    loadFormData();

    // Event Listeners
    document.getElementById('backBtn').addEventListener('click', () => previousStep());
    document.getElementById('nextBtn').addEventListener('click', () => nextStep());
    document.getElementById('submitBtn').addEventListener('click', () => submitForm());
    document.getElementById('saveDraftBtn').addEventListener('click', () => saveDraft());

    async function loadFormData() {
        try {
            const response = await fetch(`/api/inspection-standards/${token}`);
            const result = await response.json();
            
            if (result.success) {
                formData.inspectionStandardId = result.data.inspectionStandardId;
                formData.generalInformation = result.data.generalInformation;
                formData.drawings = result.data.drawings;
                formData.inspectionItems = result.data.inspectionItems;
                
                // Update window.formData reference
                window.formData = formData;
                
                document.getElementById('supplierName').textContent = result.data.supplierInfo.supplier || '-';
                
                console.log('[Main Form] Data loaded:');
                console.log('  - Inspection Standard ID:', formData.inspectionStandardId);
                console.log('  - Drawings:', formData.drawings.length);
                console.log('  - Inspection Items:', formData.inspectionItems.length);
                
                // Populate form fields
                populateFormFields();
            }
        } catch (error) {
            console.error('[Main Form] Error loading form data:', error);
        }
    }

    function populateFormFields() {
        console.log('[Main Form] populateFormFields() - Triggering step population');
        console.log('[Main Form] Current formData:', window.formData);
        
        // Trigger Step 1 population jika function tersedia
        if (typeof populateStep1Fields === 'function') {
            console.log('[Main Form] Calling populateStep1Fields()');
            populateStep1Fields();
        } else {
            console.warn('[Main Form] populateStep1Fields not found, will retry...');
            // Retry setelah delay
            setTimeout(() => {
                if (typeof populateStep1Fields === 'function') {
                    console.log('[Main Form] Retry: Calling populateStep1Fields()');
                    populateStep1Fields();
                }
            }, 500);
        }
        
        // Trigger Step 2 rendering jika function tersedia
        if (typeof renderDrawings === 'function') {
            console.log('[Main Form] Calling renderDrawings()');
            renderDrawings();
        } else {
            console.warn('[Main Form] renderDrawings not found, will retry...');
            setTimeout(() => {
                if (typeof renderDrawings === 'function') {
                    console.log('[Main Form] Retry: Calling renderDrawings()');
                    renderDrawings();
                }
            }, 500);
        }
        
        // Trigger Step 3 rendering jika function tersedia
        if (typeof renderItems === 'function') {
            console.log('[Main Form] Calling renderItems()');
            renderItems();
        } else {
            console.warn('[Main Form] renderItems not found, will retry...');
            setTimeout(() => {
                if (typeof renderItems === 'function') {
                    console.log('[Main Form] Retry: Calling renderItems()');
                    renderItems();
                }
            }, 500);
        }
        
        console.log('[Main Form] populateFormFields() completed');
    }

    function nextStep() {
        if (validateCurrentStep()) {
            if (currentStep < 4) {
                currentStep++;
                updateStepDisplay();
            }
        }
    }

    function previousStep() {
        if (currentStep > 1) {
            currentStep--;
            updateStepDisplay();
        }
    }

    function updateStepDisplay() {
        // Hide all steps
        document.querySelectorAll('.step-content').forEach(el => {
            el.classList.remove('active');
        });
        
        // Show current step
        document.getElementById(`step-${currentStep}`).classList.add('active');
        
        // Update stepper
        updateStepper();
        
        // Update buttons
        updateButtons();
    }

    function updateStepper() {
        document.querySelectorAll('.step-item').forEach(item => {
            const step = parseInt(item.getAttribute('data-step'));
            item.classList.remove('active', 'completed');
            
            if (step === currentStep) {
                item.classList.add('active');
            } else if (step < currentStep) {
                item.classList.add('completed');
            }
        });
    }

    function updateButtons() {
        document.getElementById('backBtn').style.display = currentStep > 1 ? 'block' : 'none';
        document.getElementById('nextBtn').style.display = currentStep < 4 ? 'block' : 'none';
        document.getElementById('submitBtn').style.display = currentStep === 4 ? 'block' : 'none';
    }

    function validateCurrentStep() {
        // Validation logic will be added by step components
        return true;
    }

    async function saveDraft() {
        try {
            collectFormData();
            
            console.log('[Main Form] Saving draft with data:', {
                generalInformation: formData.generalInformation,
                drawings: formData.drawings,
                inspectionItems: formData.inspectionItems
            });
            
            const response = await fetch(`/api/inspection-standards/${formData.inspectionStandardId}/draft`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    generalInformation: formData.generalInformation,
                    drawings: formData.drawings,
                    inspectionItems: formData.inspectionItems,
                })
            });
            
            const result = await response.json();
            console.log('[Main Form] Save draft response:', result);
            
            if (result.success) {
                showNotification('Draft saved successfully!', 'success');
            } else {
                console.error('[Main Form] Save draft failed:', result);
                showNotification(result.message || 'Failed to save draft', 'error');
            }
        } catch (error) {
            console.error('[Main Form] Error saving draft:', error);
            showNotification('Error saving draft: ' + error.message, 'error');
        }
    }

    async function submitForm() {
        // Confirmation dialog
        Swal.fire({
            title: 'Submit Inspection Standard?',
            text: 'Are you sure you want to submit this Inspection Standard? It will be sent for review.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Submit!',
            cancelButtonText: 'Cancel'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    collectFormData();
                    
                    console.log('[Main Form] Submitting form with data:', {
                        generalInformation: formData.generalInformation,
                        drawings: formData.drawings,
                        inspectionItems: formData.inspectionItems
                    });
                    
                    const response = await fetch(`/api/inspection-standards/${formData.inspectionStandardId}/submit`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            generalInformation: formData.generalInformation,
                            drawings: formData.drawings,
                            inspectionItems: formData.inspectionItems,
                        })
                    });
                    
                    const result = await response.json();
                    console.log('[Main Form] Submit response:', result);
                    
                    if (result.success) {
                        console.log('Submit successful, token:', token);
                        console.log('Will redirect to:', `/portal/${token}`);
                        
                        // Show success notification
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Inspection Standard submitted successfully!',
                                timer: 1500,
                                showConfirmButton: false,
                                willClose: () => {
                                    window.location.href = `/portal/${token}`;
                                }
                            });
                        } else {
                            alert('Inspection Standard submitted successfully!');
                            window.location.href = `/portal/${token}`;
                        }
                    } else {
                        console.error('[Main Form] Submit failed:', result);
                        showNotification(result.message || 'Failed to submit', 'error');
                    }
                } catch (error) {
                    console.error('[Main Form] Error submitting form:', error);
                    showNotification('Error submitting form: ' + error.message, 'error');
                }
            }
        });
    }

    function collectFormData() {
        // Collect data from all steps
        console.log('[Main Form] Collecting data from all steps...');
        
        if (typeof window.collectStep1Data === 'function') {
            formData.generalInformation = window.collectStep1Data();
            console.log('[Main Form] Step 1 data collected:', formData.generalInformation);
        } else {
            console.warn('[Main Form] collectStep1Data function not found!');
        }
        
        if (typeof window.collectStep2Data === 'function') {
            formData.drawings = window.collectStep2Data();
            console.log('[Main Form] Step 2 data collected:', formData.drawings);
        } else {
            console.warn('[Main Form] collectStep2Data function not found!');
        }
        
        if (typeof window.collectStep3Data === 'function') {
            formData.inspectionItems = window.collectStep3Data();
            console.log('[Main Form] Step 3 data collected:', formData.inspectionItems);
        } else {
            console.warn('[Main Form] collectStep3Data function not found!');
        }
        
        console.log('[Main Form] All data collected:', formData);
    }

    function showNotification(message, type = 'info') {
        // Use SweetAlert2 if available
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type === 'error' ? 'error' : 'success',
                title: type === 'error' ? 'Error' : 'Success',
                text: message,
                timer: type === 'error' ? null : 2000,
                showConfirmButton: type === 'error'
            });
        } else {
            // Fallback to console
            console.log(`[${type.toUpperCase()}] ${message}`);
            alert(message);
        }
    }

    // Expose functions globally for step components
    window.nextStep = nextStep;
    window.previousStep = previousStep;
    window.formData = formData;
});
</script>
@endsection

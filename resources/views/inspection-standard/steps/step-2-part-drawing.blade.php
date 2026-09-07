<div class="card">
    <div class="card-body">
        <div class="form-section">
            <div class="form-section-title">
                <i class="ti ti-photo"></i>
                Part Drawing
            </div>

            <p class="text-muted mb-4">
                Upload drawing files for this part. You can upload multiple drawings if needed. 
                Supported formats: JPG, JPEG, PNG, PDF (Max 10MB per file)
            </p>

            <!-- Drag & Drop Upload Area -->
            <div class="mb-4">
                <div id="dropZone" class="upload-drop-zone">
                    <i class="ti ti-cloud-upload" style="font-size: 48px; color: #0d6efd;"></i>
                    <h5 class="mt-3">Drag & Drop File Here</h5>
                    <p class="text-muted mb-3">or</p>
                    <button type="button" class="btn btn-primary" id="addDrawingBtn" style="position: relative; z-index: 10;">
                        <i class="ti ti-plus me-2"></i>Browse File
                    </button>
                    <p class="text-muted small mt-3 mb-0">JPG, PNG, PDF • Max 10MB per file</p>
                    <p class="text-info small mt-2 mb-0">✨ File akan langsung di-upload setelah dipilih</p>
                </div>
                <input type="file" id="fileInput" style="display: none;" accept=".jpg,.jpeg,.png,.pdf">
            </div>

            <!-- Upload Progress -->
            <div id="uploadProgress" class="mb-4" style="display: none;">
                <div class="alert alert-info">
                    <div class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm me-2"></div>
                        <span id="uploadProgressText">Uploading...</span>
                    </div>
                </div>
            </div>

            <!-- Drawing Upload Area -->
            <div id="drawingsList" class="mb-4">
                <!-- Drawings will be dynamically added here -->
            </div>

            <!-- Help Text -->
            <div class="alert alert-light border-start border-4 border-info">
                <i class="ti ti-info-circle me-2"></i>
                <strong>Tip:</strong> You must upload at least one drawing before proceeding to the next step.
            </div>
        </div>
    </div>
</div>

<style>
.upload-drop-zone {
    border: 2px dashed #0d6efd;
    border-radius: 8px;
    padding: 40px 20px;
    text-align: center;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-drop-zone:hover,
.upload-drop-zone.dragover {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border-color: #0a58ca;
    transform: scale(1.02);
}

#addDrawingBtn {
    pointer-events: auto;
}

.drawing-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.drawing-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.drawing-preview {
    width: 100%;
    max-height: 150px;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 3px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    overflow: hidden;
}

.drawing-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.drawing-info {
    font-size: 13px;
    color: #6c757d;
}

.drawing-info-item {
    margin-bottom: 5px;
}

.drawing-info-label {
    font-weight: 600;
    color: #495057;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[Step 2] Initializing Part Drawing step');
    
    // Wait for window.formData to be available
    const checkFormData = setInterval(function() {
        if (window.formData) {
            clearInterval(checkFormData);
            initializeStep2();
        }
    }, 100);
    
    function initializeStep2() {
        console.log('[Step 2] formData is available, initializing...');
        
        // Get elements
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const addDrawingBtn = document.getElementById('addDrawingBtn');
        const uploadProgress = document.getElementById('uploadProgress');
        const uploadProgressText = document.getElementById('uploadProgressText');
        
        console.log('[Step 2] Current drawings:', window.formData.drawings.length);
        console.log('[Step 2] Inspection Standard ID:', window.formData.inspectionStandardId);
        
        // Initialize drawings array if not exists
        if (!window.formData.drawings) {
            window.formData.drawings = [];
        }
    
    // Browse file button
    if (addDrawingBtn) {
        addDrawingBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('[Step 2] Browse button clicked');
            if (fileInput) {
                fileInput.click();
            } else {
                console.error('[Step 2] fileInput element not found');
            }
        });
    } else {
        console.error('[Step 2] addDrawingBtn not found');
    }
    
    // File input change
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            console.log('[Step 2] File input changed');
            if (e.target.files && e.target.files.length > 0) {
                const file = e.target.files[0];
                console.log('[Step 2] File selected:', file.name, file.type, file.size);
                uploadFile(file);
            } else {
                console.log('[Step 2] No file selected');
            }
        });
    } else {
        console.error('[Step 2] fileInput element not found');
    }
    
    // Drag & drop handlers
    if (dropZone) {
        console.log('[Step 2] Setting up drag & drop handlers');
        
        // Prevent defaults
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function(eventName) {
            dropZone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('[Step 2] Drag event:', eventName);
            }, false);
        });
        
        // Visual feedback
        ['dragenter', 'dragover'].forEach(function(eventName) {
            dropZone.addEventListener(eventName, function() {
                dropZone.classList.add('dragover');
            }, false);
        });
        
        ['dragleave', 'drop'].forEach(function(eventName) {
            dropZone.addEventListener(eventName, function() {
                dropZone.classList.remove('dragover');
            }, false);
        });
        
        // Handle drop
        dropZone.addEventListener('drop', function(e) {
            console.log('[Step 2] File dropped!');
            var dt = e.dataTransfer;
            var files = dt.files;
            console.log('[Step 2] Files count:', files.length);
            
            if (files.length > 0) {
                var file = files[0];
                console.log('[Step 2] Dropped file:', file.name, file.type, file.size);
                uploadFile(file);
            }
        }, false);
        
        // Click anywhere on drop zone (except button) to browse
        dropZone.addEventListener('click', function(e) {
            var isButton = e.target.id === 'addDrawingBtn' || e.target.closest('#addDrawingBtn');
            console.log('[Step 2] Drop zone clicked. Is button:', isButton);
            
            if (!isButton) {
                console.log('[Step 2] Triggering file input click');
                if (fileInput) {
                    fileInput.click();
                }
            }
        });
    } else {
        console.error('[Step 2] dropZone element not found');
    }
    
    async function uploadFile(file) {
        console.log('[Step 2] uploadFile() called');
        console.log('[Step 2] File:', file.name, file.type, file.size);
        
        // Validate file type
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        if (!validTypes.includes(file.type)) {
            console.error('[Step 2] Invalid file type:', file.type);
            alert('Invalid file type. Please upload JPG, PNG, or PDF only.');
            return;
        }
        
        // Validate file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            console.error('[Step 2] File too large:', file.size);
            alert('File too large. Maximum size is 10MB.');
            return;
        }
        
        console.log('[Step 2] File validation passed');
        
        // Show progress
        uploadProgress.style.display = 'block';
        uploadProgressText.textContent = 'Uploading ' + file.name + '...';
        
        try {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('drawing_name', file.name.split('.')[0]);
            formData.append('drawing_no', '');
            formData.append('revision', 'A');
            formData.append('description', 'Uploaded at ' + new Date().toLocaleString());
            
            const url = '/api/inspection-standards/' + window.formData.inspectionStandardId + '/upload-drawing';
            console.log('[Step 2] Uploading to:', url);
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData
            });
            
            console.log('[Step 2] Response status:', response.status);
            
            const result = await response.json();
            console.log('[Step 2] Response data:', result);
            
            if (result.success) {
                console.log('[Step 2] Upload successful!');
                
                // Add to drawings array
                const drawing = {
                    id: result.data.id,
                    filePath: result.data.file_path,
                    originalFilename: result.data.original_filename,
                    fileType: result.data.file_type,
                    fileSize: result.data.file_size,
                    drawingName: result.data.drawing_name || '',
                    drawingNo: result.data.drawing_no || '',
                    revision: result.data.revision || '',
                    description: result.data.description || '',
                };
                
                window.formData.drawings.push(drawing);
                console.log('[Step 2] Drawing added. Total:', window.formData.drawings.length);
                
                // Update display
                renderDrawings();
                
                // Hide progress
                uploadProgress.style.display = 'none';
                
                // Show success message
                alert('Drawing uploaded successfully!');
                
                // Reset file input
                fileInput.value = '';
            } else {
                console.error('[Step 2] Upload failed:', result.message);
                uploadProgress.style.display = 'none';
                alert('Upload failed: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('[Step 2] Upload error:', error);
            uploadProgress.style.display = 'none';
            alert('Upload error: ' + error.message);
        }
    }
    
    function renderDrawings() {
        console.log('[Step 2] renderDrawings() - Total:', window.formData.drawings.length);
        
        const list = document.getElementById('drawingsList');
        if (!list) {
            console.error('[Step 2] drawingsList element not found');
            return;
        }
        
        list.innerHTML = '';
        
        if (window.formData.drawings.length === 0) {
            list.innerHTML = '<div class="text-center text-muted py-3"><i class="ti ti-photo-off" style="font-size: 32px;"></i><p class="mt-2">No drawings uploaded yet</p></div>';
            return;
        }
        
        window.formData.drawings.forEach((drawing, index) => {
            const card = document.createElement('div');
            card.className = 'drawing-card';
            
            let previewHtml = '';
            const fileType = (drawing.fileType || '').toLowerCase();
            
            if (fileType === 'pdf') {
                previewHtml = '<div class="drawing-preview"><i class="ti ti-file-pdf" style="font-size: 48px; color: #dc3545;"></i></div>';
            } else if (['jpg', 'jpeg', 'png'].includes(fileType)) {
                // Extract filename from file_path
                const filename = drawing.filePath.split('/').pop();
                const imageUrl = '/inspection-standard/drawings/' + window.formData.inspectionStandardId + '/' + filename;
                previewHtml = '<div class="drawing-preview"><img src="' + imageUrl + '" alt="Drawing ' + (index + 1) + '"></div>';
            } else {
                previewHtml = '<div class="drawing-preview"><i class="ti ti-file" style="font-size: 48px; color: #6c757d;"></i></div>';
            }
            
            card.innerHTML = '<div class="row">' +
                '<div class="col-md-3">' + previewHtml + '</div>' +
                '<div class="col-md-7">' +
                    '<div class="drawing-info">' +
                        '<div class="drawing-info-item"><span class="drawing-info-label">Drawing #' + (index + 1) + '</span></div>' +
                        '<div class="drawing-info-item"><span class="drawing-info-label">Filename:</span> ' + drawing.originalFilename + '</div>' +
                        (drawing.drawingName ? '<div class="drawing-info-item"><span class="drawing-info-label">Name:</span> ' + drawing.drawingName + '</div>' : '') +
                        (drawing.drawingNo ? '<div class="drawing-info-item"><span class="drawing-info-label">No:</span> ' + drawing.drawingNo + '</div>' : '') +
                        (drawing.revision ? '<div class="drawing-info-item"><span class="drawing-info-label">Rev:</span> ' + drawing.revision + '</div>' : '') +
                    '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                    '<button type="button" class="btn btn-sm btn-danger w-100" onclick="window.deleteDrawing(' + index + ')">' +
                        '<i class="ti ti-trash"></i> Delete' +
                    '</button>' +
                '</div>' +
            '</div>';
            
            list.appendChild(card);
        });
        
        console.log('[Step 2] Drawings rendered');
    }
    
    // Expose renderDrawings globally for form.blade.php to call
    window.renderDrawings = renderDrawings;
    
    // Delete function
    window.deleteDrawing = async function(index) {
        console.log('[Step 2] deleteDrawing(' + index + ')');
        
        if (!confirm('Delete this drawing?')) {
            return;
        }
        
        const drawing = window.formData.drawings[index];
        
        try {
            const response = await fetch(
                '/api/inspection-standards/' + window.formData.inspectionStandardId + '/drawings/' + drawing.id,
                {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                }
            );
            
            const result = await response.json();
            if (result.success) {
                window.formData.drawings.splice(index, 1);
                renderDrawings();
                alert('Drawing deleted');
            }
        } catch (error) {
            console.error('[Step 2] Delete error:', error);
            alert('Delete failed');
        }
    };
    
    // Collect Step 2 data
    window.collectStep2Data = function() {
        return window.formData.drawings;
    };
    
    // Validation
    window.validateStep2 = function() {
        console.log('[Step 2] validateStep2() - Drawings:', window.formData.drawings.length);
        if (window.formData.drawings.length === 0) {
            alert('Please upload at least one drawing');
            return false;
        }
        return true;
    };
    
    // Initial render
    renderDrawings();
    
    console.log('[Step 2] Initialization complete');
    
    } // End of initializeStep2()
});
</script>

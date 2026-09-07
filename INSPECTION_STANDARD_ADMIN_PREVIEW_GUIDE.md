# Inspection Standard Admin Preview - Implementation Guide

## Overview
When a supplier submits an Inspection Standard form (either for the first time or during revision), the admin can now see a preview of the generated PDF in the admin review panel.

## Problem Solved
**Before:** Admin saw "Dokumen belum diunggah" (Document not uploaded) even though supplier had submitted the form with all data.
**Why:** Inspection Standard documents store data in a separate `InspectionStandard` table, not as uploaded files. The admin review panel only checked `RequestDocument.file_path` which was NULL for IS documents.

## Solution Implemented

### 1. Controller Changes (ReviewController::show)
**File:** `app/Http/Controllers/ReviewController.php`

Added logic to detect Inspection Standard documents and load related data:

```php
// Load Inspection Standard data if this is an IS document
$inspectionStandard = null;
if ($requestDocument->documentType->code === 'IS') {
    $inspectionStandard = $requestDocument->inspectionStandard;
    if ($inspectionStandard) {
        $inspectionStandard->load([
            'drawings',
            'inspectionItems',
            'supplier',
        ]);
    }
}

// Pass to view
return view('admin.review.show', compact('requestDocument', 'userScope', 'inspectionStandard'));
```

### 2. View Changes (admin/review/show.blade.php)
**File:** `resources/views/admin/review/show.blade.php`

Three key changes:

#### a) Document Viewer - Check InspectionStandard First
```blade
@if($inspectionStandard && $inspectionStandard->pdf_file_path)
    <!-- Show Inspection Standard PDF -->
    <div id="pdfContainer">...</div>
@elseif($requestDocument->file_path)
    <!-- Show regular uploaded file -->
@else
    <!-- Show not uploaded message -->
@endif
```

#### b) Toolbar Download Button - Use Correct Route
```blade
@if($inspectionStandard && $inspectionStandard->pdf_file_path)
    <a href="{{ route('inspection-standard.download', $inspectionStandard->id) }}" 
       class="btn btn-sm btn-success" download>
        <i class="ti ti-download me-1"></i>Download PDF
    </a>
@elseif($requestDocument->file_path)
    <!-- Regular file download -->
@endif
```

#### c) PDF Loading Script - Handle Both Sources
```javascript
@if($inspectionStandard && $inspectionStandard->pdf_file_path && str_ends_with($inspectionStandard->pdf_file_path, '.pdf'))
    loadPDF('{{ route('inspection-standard.view', $inspectionStandard->id) }}');
@elseif(str_ends_with($requestDocument->file_path ?? '', '.pdf'))
    loadPDF('{{ Storage::disk('public')->url($requestDocument->file_path) }}');
@endif

function loadPDF(pdfUrl) {
    pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
        pdfDoc = pdf;
        document.getElementById('pageCount').textContent = pdfDoc.numPages;
        renderPage(pageNum);
    }).catch(function(error) {
        console.error('Error loading PDF:', error);
    });
}
```

## Data Flow

### Supplier Submits Form
```
1. Supplier fills form with:
   - General information (model, part no, etc.)
   - Uploads 1+ drawings
   - Adds 1+ inspection items

2. Clicks "Submit Inspection Standard"
   ↓
3. POST /api/inspection-standards/{id}/submit
   - Validates all data
   - Generates PDF via InspectionStandardPdfGenerator
   - PDF stored at: storage/documents/inspection-standards/{id}/INSPECTION_STANDARD_*.pdf
   - Updates: InspectionStandard.pdf_file_path = "inspection-standards/{id}/INSPECTION_STANDARD_*.pdf"
   - Updates: RequestDocument.status = "SUBMITTED"
   ↓
4. Email notification sent to admin
```

### Admin Views Submission
```
1. Admin logs in and opens Review panel
   ↓
2. GET /review/{requestDocument}
   - ReviewController::show() loads RequestDocument
   - Detects: documentType->code === 'IS'
   - Loads: InspectionStandard with relations
   ↓
3. View receives: $inspectionStandard with pdf_file_path populated
   ↓
4. Admin sees:
   ✓ PDF preview in viewer
   ✓ "Download PDF" button (active)
   ✓ Page navigation controls
   ✓ Zoom & rotate controls
   ↓
5. Admin can:
   - View all 3 pages of PDF
   - Download PDF
   - Approve or request revision
```

### Supplier Does Revision & Resubmits
```
1. Admin requests revision with notes
   ↓
2. Email sent to supplier with revision request
   ↓
3. Supplier clicks link and opens form
   - Form loads with all previous data (data preservation working)
   ↓
4. Supplier edits only requested fields
   - Say Step 1 and Step 3 need revision
   - Leaves Step 2 drawings as-is
   ↓
5. Supplier submits again
   ↓
6. New PDF is generated (replaces old one)
   - InspectionStandard.pdf_file_path updated with new path
   ↓
7. Admin refreshes review page
   - Sees updated PDF with revisions
   - Can compare old vs new
```

## Key Technical Details

### PDF Storage Path
- **Pattern:** `inspection-standards/{inspectionStandardId}/INSPECTION_STANDARD_{refNo}_{partNo}_{timestamp}.pdf`
- **Example:** `inspection-standards/42/INSPECTION_STANDARD_is-2024-001_part-12345_20260904143022.pdf`
- **Disk:** Uses `documents` disk (configured in config/filesystems.php)
- **Access:** Via Storage::disk('documents')->path()

### Routes Used
- **View PDF:** `GET /inspection-standard/{inspectionStandardId}/pdf` → InspectionStandardController::viewPdf
- **Download PDF:** `GET /inspection-standard/{inspectionStandardId}/download` → InspectionStandardController::downloadPdf
- **Named Routes:** `route('inspection-standard.view', $id)` and `route('inspection-standard.download', $id)`

### Database Fields
- **InspectionStandard.pdf_file_path:** Stores relative path to generated PDF
- **InspectionStandard.status:** Tracks IS status (DRAFT, SUBMITTED, PENDING_REVIEW, APPROVED, REVISE)
- **InspectionStandard.submitted_at:** Timestamp when submitted
- **RequestDocument.file_path:** Remains NULL for IS (data in InspectionStandard table instead)

## Testing Checklist

### ✅ Test 1: Initial Form Submission
- [ ] Supplier fills complete IS form
- [ ] Supplier uploads drawings
- [ ] Supplier adds inspection items
- [ ] Supplier clicks "Submit Inspection Standard"
- [ ] Check laravel.log for "[IS Submit] Submission completed successfully"
- [ ] Admin receives email notification
- [ ] Admin opens Review panel
- [ ] Admin navigates to the IS document
- [ ] **Expected:** PDF preview shows in viewer with all 3 pages
- [ ] **Verify:** Can navigate pages, zoom, rotate
- [ ] **Verify:** Download button works and downloads PDF
- [ ] **Verify:** All form data visible in PDF (general info, drawings, inspection items)

### ✅ Test 2: Admin Approves Document
- [ ] Admin clicks "Setujui Dokumen" button
- [ ] Admin receives confirmation
- [ ] Document status changes to APPROVED
- [ ] Supplier receives approval email

### ✅ Test 3: Admin Requests Revision
- [ ] Admin clicks "Minta Revisi" button
- [ ] Admin fills revision note (e.g., "Please update part material")
- [ ] Admin submits revision request
- [ ] Supplier receives email with revision request
- [ ] Document status changes to REVISE

### ✅ Test 4: Supplier Receives Revision & Opens Form
- [ ] Supplier clicks link in revision email
- [ ] Form loads with all previous data (Step 1, 2, 3)
- [ ] **Verify:** General information fields populated
- [ ] **Verify:** Drawings list shows previously uploaded files
- [ ] **Verify:** Inspection items table shows all previous items
- [ ] Supplier can see which fields need revision (check revision notes)

### ✅ Test 5: Supplier Makes Changes & Resubmits
- [ ] Supplier edits only the requested fields (e.g., material, weight)
- [ ] Supplier keeps drawings unchanged
- [ ] Supplier keeps inspection items mostly unchanged (maybe one revision)
- [ ] Supplier clicks "Submit Inspection Standard" again
- [ ] Check laravel.log for "[IS Submit] Submission completed successfully"
- [ ] New PDF is generated (check file timestamp in storage folder)

### ✅ Test 6: Admin Sees Updated PDF
- [ ] Admin refreshes review page
- [ ] **Expected:** PDF viewer shows updated data
- [ ] **Verify:** Material field shows new value
- [ ] **Verify:** Weight field shows new value
- [ ] **Verify:** Drawings unchanged from first submission
- [ ] **Verify:** Inspection items show revision (if any)
- [ ] Admin can download new PDF

### ✅ Test 7: Multi-Page PDF Navigation
- [ ] Page 1: General info, part route table, title block, approval section
- [ ] Page 2: Part drawings (should show uploaded drawings or placeholder)
- [ ] Page 3: Inspection items table
- [ ] Test page navigation buttons work correctly
- [ ] Test zoom in/out works
- [ ] Test rotate works

## Browser Console Verification

When admin opens review page, check console for any errors:
```javascript
// No errors like:
// - "pdf_file_path is undefined"
// - "Cannot read property 'pdf_file_path' of null"
// - "Failed to fetch PDF"

// Expected logs (if enabled):
// [IS Admin Preview] Inspection Standard loaded
// [PDF Viewer] PDF loaded successfully
```

## Troubleshooting

### Issue: Admin sees "Dokumen belum diunggah" (not uploaded)
**Causes:**
1. PDF not generated - check if submit was successful
2. pdf_file_path is NULL - check InspectionStandard table
3. File deleted from storage - check storage/documents/inspection-standards/

**Solution:**
- Check `storage/logs/laravel.log` for "[PDF Generator] Error generating PDF"
- Verify `InspectionStandard.pdf_file_path` has value in database
- Verify file exists at `storage/documents/inspection-standards/{id}/`

### Issue: PDF viewer shows blank/white page
**Causes:**
1. PDF route not working - check if inspection-standard.view route exists
2. File permissions - check storage directory permissions
3. DomPDF rendering issue - check PDF file integrity

**Solution:**
- Test direct route: `GET /inspection-standard/{id}/pdf` in browser
- Check file permissions: `chmod 755 storage/documents/`
- Check PDF file is not corrupted: download and open in desktop PDF viewer

### Issue: Download button not working
**Causes:**
1. Route name incorrect - route('inspection-standard.download', ...) not registered
2. PDF file missing - file deleted from storage
3. CORS issue if on different domain

**Solution:**
- Verify route registered in `routes/web.php`
- Check storage/documents/inspection-standards/{id}/ for PDF file
- Test direct URL without route helper

## Files Modified

| File | Changes |
|------|---------|
| app/Http/Controllers/ReviewController.php | Added IS detection and InspectionStandard loading |
| resources/views/admin/review/show.blade.php | Added IS pdf_file_path check in 3 places |

## Related Files (No Changes Needed)

| File | Purpose |
|------|---------|
| app/Http/Controllers/InspectionStandardController.php | Generates PDF, provides view/download endpoints |
| app/Services/InspectionStandardPdfGenerator.php | Creates PDF from form data |
| resources/views/inspection-standard/pdf/template.blade.php | PDF visual layout |
| routes/web.php | Routes for IS operations (must have inspection-standard.view and inspection-standard.download) |

## Expected User Experience

### Supplier Perspective
- ✅ Form data preserved when opening revision form
- ✅ No need to re-enter previously filled data
- ✅ Only edit requested revision fields
- ✅ Submit again
- ✅ Admin gets updated PDF

### Admin Perspective
- ✅ Cannot see form immediately (PDF generation takes ~2-3 sec)
- ✅ Preview appears once supplier submits (PDF generated)
- ✅ Can review all pages of PDF
- ✅ Can download for archival
- ✅ Can approve or request revision
- ✅ When supplier resubmits, updated PDF appears on refresh
- ✅ Full history of submissions visible

## Success Metrics

- [ ] Admin sees PDF preview within 5 seconds of supplier submit
- [ ] All 3 pages of PDF visible and navigable
- [ ] PDF contains all supplier data (general info, drawings, items)
- [ ] Download works
- [ ] Revision flow works (request → supplier edit → resubmit → updated PDF)
- [ ] No data loss during revisions
- [ ] PDF quality is acceptable for archival


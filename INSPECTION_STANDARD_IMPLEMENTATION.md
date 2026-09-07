# Inspection Standard Form Implementation - Complete Documentation

**Status**: ✅ Fully Implemented  
**Date**: September 4, 2026  
**System**: PT Akebono Approval Document Portal

---

## 📋 Project Overview

This document outlines the complete implementation of the form-based Inspection Standard (IS) submission system for the PT Akebono Approval Document Portal. The system replaces the manual Excel file upload process with a digital, multi-step wizard form.

---

## 🎯 Key Changes

### Before (Upload-Based)
- Supplier uploaded Excel file directly
- No form validation on portal side
- Manual data entry in backend
- No draft functionality

### After (Form-Based)
- Supplier fills multi-step form on portal
- Real-time validation per step
- Auto-generated PDF from form data
- Full draft/edit/resubmit workflow
- Integrated with existing approval process

---

## 📦 Implementation Components

### 1. Database Schema (Migration: 2026_09_04_150000)

#### Tables Created:
1. **inspection_standards**
   - Main IS record with general information
   - Status tracking (DRAFT, SUBMITTED, PENDING_REVIEW, APPROVED, REJECTED)
   - PDF file path storage
   - Links to request_documents via request_document_id

2. **inspection_standard_drawings**
   - Dynamic drawing uploads (jpg, png, pdf)
   - Drawing metadata (name, drawing no, revision, description)
   - File path and size tracking
   - Sequence ordering for multiple drawings

3. **inspection_standard_items**
   - Dynamic inspection items table rows
   - Fields: inspection_item, instrument, criterion, rank, sampling info, remarks
   - Sequence ordering for row numbering
   - Supports unlimited items per IS

### 2. Eloquent Models

**InspectionStandard.php**
- Relationships: belongsTo(RequestDocument), hasMany(drawings), hasMany(items)
- Methods: isDraft(), isSubmitted(), isApproved(), validateForSubmission(), getFormDataArray()
- Status labels and badge colors for UI

**InspectionStandardDrawing.php**
- File type detection and icon mapping
- File size formatting utility

**InspectionStandardItem.php**
- Item validation and completeness checking

**RequestDocument.php (updated)**
- Added: `inspectionStandard()` relationship

### 3. Backend API Endpoints

**Route Prefix**: `/api/inspection-standards`

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/{token}` | Load form data or create draft |
| POST | `/{inspectionStandardId}/draft` | Save form as draft |
| POST | `/{inspectionStandardId}/submit` | Submit and generate PDF |
| POST | `/{inspectionStandardId}/upload-drawing` | Upload drawing file |
| DELETE | `/{inspectionStandardId}/drawings/{drawingId}` | Delete drawing |
| GET | `/{inspectionStandardId}` | Get IS data for review |

**Route Prefix**: `/inspection-standard`

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/form/{token}` | Display form UI |
| GET | `/{inspectionStandardId}/pdf` | View PDF inline |
| GET | `/{inspectionStandardId}/download` | Download PDF file |

### 4. Frontend Components

#### Form View (resources/views/inspection-standard/form.blade.php)
- Multi-step wizard with stepper navigation
- Step indicators (active/completed states)
- Next/Back/Submit/Save as Draft buttons
- Client-side step validation
- Form data persistence to window.formData

#### Step Components

**Step 1: General Information** (step-1-general-information.blade.php)
- 11 input fields for general IS data
- Auto-populated supplier and customer
- Read-only approval indicator boxes
- Field validation with error messages
- Required field indicator (*)

**Step 2: Part Drawing** (step-2-part-drawing.blade.php)
- Drawing upload modal
- File format validation (jpg, jpeg, png, pdf)
- Drawing metadata fields (name, drawing no, revision, description)
- Drawing preview (image display, PDF icon)
- Add/Edit/Delete drawing functionality
- Support for multiple drawings

**Step 3: Inspection Items** (step-3-inspection-items.blade.php)
- Dynamic table with inline editing
- Add/Delete row buttons
- Fields: Item, Instrument, Criterion, Rank (A-D, S), Sampling, Remark
- Inline validation per row
- Responsive table with horizontal scroll
- Row numbering (1-indexed)

**Step 4: Review & Submit** (step-4-review-submit.blade.php)
- Read-only summary of all sections
- Grouped display: General Info, Supplier/Mfg, Drawings, Items
- Submission confirmation dialog
- Progress summary before final submit

#### Portal UI Component (resources/views/portal/partials/inspection-standard-document.blade.php)
- Status badge display (MANDATORY, DRAFT, PENDING, SUBMITTED, APPROVED, REVISE)
- Dynamic button rendering based on status:
  - **DRAFT**: "Continue Form" button
  - **PENDING/REVISE**: "Isi Form Terlebih Dahulu" button
  - **SUBMITTED/APPROVED**: View/Download PDF buttons
- Color-coded left border per status
- Revision alerts display
- Submission date/time info
- Edit Form button for revisions

### 5. PDF Generation

**Service**: InspectionStandardPdfGenerator.php

**Features**:
- Automatic PDF generation after form submission
- HTML-to-PDF conversion using barryvdh/laravel-dompdf
- Professional layout with:
  - Company header and document title
  - All form sections formatted in grid/table layout
  - Drawing images embedded (with page breaks)
  - Inspection items dynamic table
  - Footer with generation timestamp
- File storage in `/storage/documents/inspection-standards/{id}/`
- Filename format: `INSPECTION_STANDARD_{refNo}_{partNo}_{timestamp}.pdf`

**PDF Template**: resources/views/inspection-standard/pdf/template.blade.php

---

## 🔄 Workflow & Status Transitions

### Supplier Portal Flow

```
Portal Supplier Document List
    ↓
Inspection Standard (IS) [MANDATORY] [PENDING]
    ↓
[Isi Form Terlebih Dahulu] button
    ↓
Inspection Standard Form (Step 1-4)
    ↓
Step 1: General Information (validate required fields)
    ↓
Step 2: Part Drawing (add ≥1 drawing)
    ↓
Step 3: Inspection Items (add ≥1 item with required fields)
    ↓
Step 4: Review & Submit (read-only summary)
    ↓
[Submit Inspection Standard] confirmation
    ↓
Form Data Saved → PDF Generated → Status = SUBMITTED
    ↓
Automatically appears on Portal as:
Inspection Standard (IS) [MANDATORY] [SUBMITTED]
    [View] [Download PDF]
    ↓
Existing Approval Workflow (QA Review)
```

### Status Transitions

| From | To | Trigger | System Action |
|------|----|---------| --------------|
| N/A | DRAFT | First form load | Create IS record |
| DRAFT | DRAFT | Save Draft button | Persist form data |
| DRAFT | SUBMITTED | Submit button | Validate + Generate PDF + Update IS + Update RequestDocument |
| SUBMITTED | PENDING_REVIEW | Auto-trigger | (Optional, depends on approval workflow) |
| PENDING_REVIEW | APPROVED | Admin approval | Update status |
| PENDING_REVIEW | REJECTED | Admin rejection | Update status + add revision note |
| SUBMITTED/REJECTED | DRAFT | Edit Form button | Allow re-editing |

### RequestDocument Status Alignment

- IS `DRAFT` → RequestDocument remains `PENDING`
- IS `SUBMITTED` → RequestDocument becomes `SUBMITTED`
- RequestDocument `REVISE` → Supplier can "Edit Form"
- RequestDocument `APPROVED` → IS becomes `APPROVED`

---

## 💾 Data Model

### InspectionStandard Record
```php
{
    id: integer,
    request_document_id: integer (FK),
    supplier_id: integer (FK),
    
    // General Information
    model: string,
    date_issued: date,
    ref_no: string,
    part_route: string,
    part_no: string,
    part_name: string,
    manufacturer: string,
    material: string,
    general_tolerance: string,
    weight: string,
    revision_date: date (nullable),
    revision_record: text (nullable),
    
    // Status & PDF
    status: enum[DRAFT|SUBMITTED|PENDING_REVIEW|APPROVED|REJECTED],
    pdf_file_path: string (nullable),
    form_data_json: json (backup),
    
    // Timestamps
    submitted_at: timestamp (nullable),
    approved_at: timestamp (nullable),
    rejected_at: timestamp (nullable),
    created_at: timestamp,
    updated_at: timestamp
}
```

### InspectionStandardDrawing Record
```php
{
    id: integer,
    inspection_standard_id: integer (FK),
    
    // Metadata
    drawing_name: string (nullable),
    drawing_no: string (nullable),
    revision: string (nullable),
    description: text (nullable),
    
    // File Info
    file_path: string,
    original_filename: string,
    file_type: string (jpg|png|pdf),
    file_size: integer (bytes),
    
    // Ordering
    sequence_number: integer,
    
    created_at: timestamp,
    updated_at: timestamp
}
```

### InspectionStandardItem Record
```php
{
    id: integer,
    inspection_standard_id: integer (FK),
    
    // Inspection Item Data
    inspection_item: text (nullable),
    inspection_instrument: string (nullable),
    inspection_criterion: text (nullable),
    rank: string (nullable),
    supplier_sampling: string (nullable),
    aaij_sampling: string (nullable),
    remark_inspection_method: text (nullable),
    
    // Ordering
    sequence_number: integer,
    
    created_at: timestamp,
    updated_at: timestamp
}
```

---

## ✅ Implementation Checklist

### Database & Models
- [x] Migration created with 3 tables
- [x] Migration successfully ran
- [x] InspectionStandard model with relationships
- [x] InspectionStandardDrawing model
- [x] InspectionStandardItem model
- [x] RequestDocument updated with IS relationship

### Backend API
- [x] InspectionStandardController created
- [x] getForm() endpoint - load/create draft
- [x] saveDraft() endpoint - persist form data
- [x] uploadDrawing() endpoint - file upload
- [x] deleteDrawing() endpoint - remove drawing
- [x] submit() endpoint - validate + generate PDF
- [x] show() endpoint - get IS data
- [x] showForm() route method
- [x] viewPdf() endpoint
- [x] downloadPdf() endpoint
- [x] Routes configured in web.php

### Frontend Form
- [x] Main form view with stepper
- [x] Step 1: General Information component
- [x] Step 2: Part Drawing component
- [x] Step 3: Inspection Items component
- [x] Step 4: Review & Submit component
- [x] Next/Back navigation
- [x] Submit button with confirmation
- [x] Save as Draft button
- [x] Form data persistence (window.formData)
- [x] Step validation before proceeding

### Portal UI
- [x] Inspection Standard document partial component
- [x] Status badge display (DRAFT, PENDING, SUBMITTED, APPROVED, REVISE)
- [x] Button rendering based on status
- [x] "Isi Form Terlebih Dahulu" button for PENDING/DRAFT
- [x] "Continue Form" button for DRAFT
- [x] View/Download PDF buttons for SUBMITTED
- [x] Edit Form button for revisions
- [x] Color-coded left border styling
- [x] Integration with portal/upload.blade.php

### PDF Generation
- [x] barryvdh/laravel-dompdf installed
- [x] InspectionStandardPdfGenerator service created
- [x] PDF template view created
- [x] generate() method - create PDF
- [x] download() method - file download
- [x] view() method - inline viewing
- [x] Auto-generation after submission
- [x] Drawing image embedding
- [x] Dynamic inspection items table
- [x] Professional layout with headers/footers

### Draft & Edit Functionality
- [x] Draft status (DRAFT) on first form load
- [x] saveDraft() saves partial form data
- [x] Form data loading on re-entry
- [x] Edit Form button for revisions
- [x] No validation on draft save
- [x] Portal shows Draft status and button
- [x] Supplier can pause/resume form
- [x] All form data persists between sessions

### Integration
- [x] Inspection Standard linked to RequestDocument
- [x] PDF stored and linked to IS record
- [x] RequestDocument status updated on IS submission
- [x] Portal partial included in upload.blade.php
- [x] Conditional rendering based on document type (IS vs others)
- [x] Existing approval workflow preserved
- [x] No breaking changes to existing portal functionality

---

## 🔍 Testing Checklist

### Unit Tests (To Be Verified)

- [ ] InspectionStandard model: validateForSubmission() returns correct errors
- [ ] InspectionStandardItem model: validate() method works
- [ ] InspectionStandardDrawing model: file type detection works

### API Tests (To Be Verified)

- [ ] GET `/api/inspection-standards/{token}` creates DRAFT IS
- [ ] GET `/api/inspection-standards/{token}` loads existing IS
- [ ] POST `/api/inspection-standards/{id}/draft` saves data without validation
- [ ] POST `/api/inspection-standards/{id}/upload-drawing` uploads file
- [ ] DELETE `/api/inspection-standards/{id}/drawings/{drawingId}` removes drawing
- [ ] POST `/api/inspection-standards/{id}/submit` validates all required fields
- [ ] POST `/api/inspection-standards/{id}/submit` generates PDF
- [ ] POST `/api/inspection-standards/{id}/submit` updates RequestDocument status
- [ ] GET `/inspection-standard/form/{token}` displays form
- [ ] GET `/inspection-standard/{id}/pdf` returns PDF inline
- [ ] GET `/inspection-standard/{id}/download` downloads PDF file

### Portal UI Tests (To Be Verified)

- [ ] Portal shows "Isi Form Terlebih Dahulu" for pending IS
- [ ] Clicking button opens form with all 4 steps
- [ ] "Save as Draft" button persists form data
- [ ] Refreshing form reloads saved data
- [ ] "Continue Form" button appears for DRAFT status
- [ ] Step validation prevents proceeding without required fields
- [ ] Step 2 requires at least 1 drawing
- [ ] Step 3 requires at least 1 inspection item with required fields
- [ ] Step 4 shows read-only summary
- [ ] Submit button shows confirmation dialog
- [ ] After submission, PDF appears with View/Download buttons
- [ ] Portal shows "SUBMITTED" badge instead of button
- [ ] Color-coded left border appears (gray=DRAFT, yellow=PENDING, etc.)

### End-to-End Workflow Tests (To Be Verified)

- [ ] **Draft Save Test**
  - Load form → Fill Step 1 → Save Draft → Leave form → Return to form → Data persists

- [ ] **Multi-Step Completion**
  - Step 1: Fill general info
  - Step 2: Add 2+ drawings with metadata
  - Step 3: Add 3+ inspection items with all required fields
  - Step 4: Review shows all data correctly
  - Submit → Confirmation → Success message

- [ ] **PDF Generation**
  - After submission, PDF generated
  - PDF contains all form data
  - PDF displays drawing images
  - PDF has inspection items table
  - PDF is downloadable

- [ ] **Revision Workflow**
  - Submit IS → Admin sets status to REVISE → Portal shows REVISE badge
  - Click "Edit Form" → Form loads existing data → Modify data
  - Save Draft → Continue → Submit again → New PDF generated

- [ ] **Approval Workflow**
  - After IS submission, existing QA review process works
  - Admin can approve/reject IS
  - Supplier receives notification
  - Portal shows final status

### Error Handling Tests (To Be Verified)

- [ ] Submission fails if required fields missing → Error shown
- [ ] Submission fails if no drawing → Error shown
- [ ] Submission fails if no inspection item → Error shown
- [ ] File upload fails if file too large → Error shown
- [ ] File upload fails if wrong format → Error shown
- [ ] PDF generation error handled gracefully

---

## 📁 Files Created/Modified

### Created Files (16 new)
1. `database/migrations/2026_09_04_150000_create_inspection_standards_table.php`
2. `app/Models/InspectionStandard.php`
3. `app/Models/InspectionStandardDrawing.php`
4. `app/Models/InspectionStandardItem.php`
5. `app/Services/InspectionStandardPdfGenerator.php`
6. `app/Http/Controllers/InspectionStandardController.php`
7. `resources/views/inspection-standard/form.blade.php`
8. `resources/views/inspection-standard/pdf/template.blade.php`
9. `resources/views/inspection-standard/steps/step-1-general-information.blade.php`
10. `resources/views/inspection-standard/steps/step-2-part-drawing.blade.php`
11. `resources/views/inspection-standard/steps/step-3-inspection-items.blade.php`
12. `resources/views/inspection-standard/steps/step-4-review-submit.blade.php`
13. `resources/views/portal/partials/inspection-standard-document.blade.php`
14. `composer.json` (updated with barryvdh/laravel-dompdf)

### Modified Files (2)
1. `app/Models/RequestDocument.php` - Added inspectionStandard() relationship
2. `resources/views/portal/upload.blade.php` - Added IS component inclusion
3. `routes/web.php` - Added IS routes and API endpoints

---

## 🚀 Deployment Checklist

- [ ] Run migration: `php artisan migrate --step`
- [ ] Verify storage/documents directory exists and is writable
- [ ] Test form access via portal token
- [ ] Test PDF generation with sample data
- [ ] Verify portal displays IS document correctly
- [ ] Check that PDF files are stored properly
- [ ] Test approval workflow with existing admin panel
- [ ] Monitor error logs during initial deployment
- [ ] Verify email notifications work (if configured)

---

## 📝 Notes

- All form data is persisted to database immediately (saveDraft) or on submit
- PDF generation is synchronous (happens immediately after submit)
- Form validation is client-side (JavaScript) + server-side (PHP)
- No temporary files created - all data stored in database
- Drawing files stored in Laravel storage (configurable disk)
- Fully backward compatible with existing portal functionality
- No breaking changes to approval workflow
- Support for multi-part requests maintained

---

**Last Updated**: September 4, 2026  
**Implementation Status**: ✅ COMPLETE  
**Ready for Testing**: ✅ YES  
**Ready for Production**: ⏳ PENDING VERIFICATION

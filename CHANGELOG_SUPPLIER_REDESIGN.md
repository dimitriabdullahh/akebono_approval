# Changelog - Supplier Document Submission Redesign

**Date:** 02 September 2026  
**Type:** Feature Enhancement  
**Status:** Completed

## Overview

Redesigned the Supplier Document Submission page from a card-based grid layout to a professional table-based "Document Submission Sheet" layout with Part information header, comprehensive document tracking, and draft/submission workflow.

---

## Changes Made

### 1. New Table-Based Layout

**File:** `resources/views/supplier/partials/documents-table.blade.php`

- Created new partial view with professional table layout
- **Part Information Header** displays:
  - Model
  - Part Name
  - Number Part (Part Number)
  - Due Date
- **Document Submission Table** with 4 columns:
  - NO (sequential number 1-16)
  - DOCUMENT (name, mandatory/optional badge, status, description)
  - DATE SUBMIT (upload date and time)
  - ATTACHMENT (upload button or file with view/download/replace actions)
- **Status Indicators:**
  - Draft status with last saved timestamp
  - Submitted status with submission timestamp
  - Mandatory documents counter
- **Action Buttons:**
  - Save Draft (saves progress without validation)
  - Submit All Documents (validates mandatory docs before submission)

### 2. Controller Updates

**File:** `app/Http/Controllers/SupplierController.php`

Added two new methods:

#### `saveDraft($partId)`
- Allows suppliers to save progress without completing all mandatory documents
- Updates `submission_status` to 'DRAFT'
- Records `last_saved_at` timestamp
- Returns JSON response for AJAX

#### `submitAllDocuments($partId)`
- Validates all 14 mandatory documents are uploaded
- Updates `submission_status` to 'SUBMITTED'
- Records `submitted_at` and `submitted_by`
- Changes document status from PENDING to SUBMITTED
- Creates history records for all documents
- Dispatches notification job to Akebono
- Prevents further edits unless revision requested
- Returns JSON response for AJAX

#### Updated `getPartDocuments($requestId, $partId)`
- Changed to pass `$part` object instead of grouped documents
- Compatible with new table-based partial view

### 3. Database Migration

**File:** `database/migrations/2026_09_02_045634_add_submission_fields_to_request_parts_table.php`

Added fields to `request_parts` table:
- `submission_status` (VARCHAR, default 'DRAFT') - Tracks submission state: DRAFT or SUBMITTED
- `last_saved_at` (TIMESTAMP, nullable) - When draft was last saved
- `submitted_at` (TIMESTAMP, nullable) - When documents were submitted
- `submitted_by` (BIGINT UNSIGNED, nullable) - Foreign key to users table

### 4. Model Enhancement

**File:** `app/Models/RequestPart.php`

Added fields to fillable array and dates cast:
- `submission_status`, `last_saved_at`, `submitted_at`, `submitted_by`

New relationship:
- `submittedBy()` - Belongs to User

New helper methods:
- `allMandatoryDocumentsUploaded()` - Checks if all mandatory docs have files
- `getMandatoryPendingCountAttribute` - Count of mandatory docs not uploaded
- `getMandatoryUploadedCountAttribute` - Count of mandatory docs uploaded
- `getMandatoryTotalCountAttribute` - Total mandatory docs count
- `isDraft()` - Check if status is DRAFT
- `isSubmitted()` - Check if status is SUBMITTED
- `canSubmit()` - Check if ready to submit (all mandatory docs uploaded and status is DRAFT)

### 5. Routes

**File:** `routes/web.php`

Added two new POST routes:
```php
Route::post('/part/{partId}/save-draft', [SupplierController::class, 'saveDraft'])
    ->name('supplier.part.save-draft');

Route::post('/part/{partId}/submit', [SupplierController::class, 'submitAllDocuments'])
    ->name('supplier.part.submit');
```

### 6. View Updates

**File:** `resources/views/supplier/request-detail.blade.php`

- Changed from using `documents-grid` partial to `documents-table` partial
- Removed `$documents` variable (grouped by status)
- Now passes `$part` object directly to partial

---

## Key Features

### 1. Part-Specific Document Management
- Each part has its own set of 16 documents
- Switching parts automatically loads that part's documents
- Part information (Model, Name, Number, Due Date) displayed prominently

### 2. 16 Document Types
All documents displayed in consistent order:
1. Process Flow Diagram (PFD) - **Mandatory**
2. Process Failure Mode and Effect Analysis (FMEA) - **Mandatory**
3. Quality Control Process Chart (QCPC) - **Mandatory**
4. Measurement System Analysis (MSA) - **Mandatory**
5. Inspection Standard (IS) - **Mandatory**
6. Initial Sample Inspection Report (ISIR) - **Mandatory**
7. X-R Control Chart (CP-CK) - **Mandatory**
8. Appearance Approval Report (AAR) - **Mandatory**
9. Limit Sample Tag - **Mandatory**
10. Packaging Standard (PS) - **Mandatory**
11. Part Submission Warrant (PSW) - **Mandatory**
12. Mill Sheet Material - **Mandatory**
13. Certificate of Calibration - **Mandatory**
14. Drawing Product - **Mandatory**
15. SOC Free - **Optional**
16. IMDS (International Material Data System) - **Optional**

### 3. Save Draft Functionality
- **Purpose:** Save progress without completing all documents
- **Behavior:**
  - No validation on mandatory documents
  - Can be used at any time during document upload
  - Updates last saved timestamp
  - Maintains DRAFT status
  - Progress persisted in database (not localStorage)
- **Use Case:** Supplier can save work and continue later

### 4. Submit All Documents Functionality
- **Purpose:** Final submission to Akebono for approval
- **Validation:**
  - All 14 mandatory documents must be uploaded
  - Optional documents (SOC Free, IMDS) can be empty
  - Button disabled if validation fails
- **Behavior:**
  - Shows confirmation dialog
  - Changes status to SUBMITTED
  - Records submission timestamp and user
  - Creates history records
  - Sends notification to Akebono
  - Locks documents from further changes
- **Post-Submission:**
  - Documents enter approval workflow
  - Supplier cannot modify unless revision requested

### 5. Document Status Tracking
- **PENDING** - Not yet uploaded
- **SUBMITTED** - Uploaded and under review
- **REVISE** - Returned for revision with notes
- **APPROVED** - Approved by Akebono

### 6. Revision Workflow
- If Akebono requests revision:
  - Status changes to REVISE
  - Revision notes displayed
  - Supplier can replace document
  - After replacement, can save draft or resubmit

### 7. Date Submit Column
- Automatically populated when document uploaded
- Format: "02 September 2026" with time
- Shows "-" if not uploaded
- Based on database timestamp, not hardcoded

### 8. Attachment Column
- **Before upload:** "Upload Document" button
- **After upload:** 
  - File name displayed
  - View button (opens detail page)
  - Download button
  - Replace button (if not submitted)
- File operations restricted after submission

### 9. Progress Tracking
- Mandatory documents counter: "14/14" or "10/14"
- Visual indicator: red if incomplete, green if complete
- Status badge: Draft (yellow) or Submitted (green)
- Last saved time displayed for drafts

### 10. Responsive Design
- Desktop: Full table layout
- Tablet: Adjusted column widths
- Mobile: Horizontal scroll for table
- Maintains readability across devices

---

## Technical Implementation

### AJAX Requests
Both Save Draft and Submit use AJAX for seamless UX:
- No page reload required
- Loading indicators during processing
- Toast notifications for feedback
- Auto-refresh on successful submission

### Data Flow

#### Save Draft:
```
User clicks "Save Draft"
  ↓
JavaScript: saveDraft(partId)
  ↓
POST /supplier/part/{partId}/save-draft
  ↓
Controller: Updates submission_status, last_saved_at
  ↓
Returns JSON: success, message, last_saved_at
  ↓
UI: Shows toast notification, updates timestamp
```

#### Submit All Documents:
```
User clicks "Submit All Documents"
  ↓
JavaScript: Confirmation dialog
  ↓
POST /supplier/part/{partId}/submit
  ↓
Controller: Validates mandatory docs
  ↓
If valid: Update status, create history, send notification
  ↓
Returns JSON: success, message
  ↓
UI: Shows success toast, reloads page
  ↓
Page shows "Submitted" status, buttons hidden
```

### Database Persistence
- Draft state saved in `request_parts.submission_status`
- Last saved time in `request_parts.last_saved_at`
- Submission time in `request_parts.submitted_at`
- Submitted by user in `request_parts.submitted_by`
- All changes persist across sessions and browser restarts

### Security
- All routes protected by `auth` and `supplier` middleware
- CSRF token validation on POST requests
- User can only access their own supplier's data
- Foreign key constraints ensure data integrity

---

## Benefits

1. **Professional Appearance:** Resembles official document submission form
2. **Better Organization:** Clear table structure, easy to scan
3. **Progress Saving:** No data loss, can work incrementally
4. **Clear Requirements:** Mandatory vs Optional clearly marked
5. **Validation Feedback:** Clear messages about what's missing
6. **Audit Trail:** History of all document actions
7. **Flexible Workflow:** Draft → Submit → Revise → Resubmit
8. **Part-Specific:** Each part managed independently

---

## Testing Checklist

- [ ] View Part information header (Model, Name, Number, Due Date)
- [ ] See all 16 documents in table
- [ ] Upload a document
- [ ] See Date Submit auto-filled
- [ ] Click Save Draft with incomplete documents
- [ ] Logout and login, verify draft persisted
- [ ] Upload remaining mandatory documents
- [ ] Click Submit All Documents with missing docs (should fail)
- [ ] Upload all mandatory docs and submit successfully
- [ ] Verify status changes to Submitted
- [ ] Verify buttons hidden after submission
- [ ] Test revision workflow (admin returns doc for revision)
- [ ] Replace document and resubmit
- [ ] Switch between multiple parts
- [ ] Test responsive layout on mobile

---

## Future Enhancements (Optional)

1. **Document Versioning:** Keep history of replaced files
2. **Bulk Download:** Download all submitted documents as ZIP
3. **Auto-save:** Periodically save draft automatically
4. **Upload Progress:** Show progress bar during file upload
5. **Drag & Drop:** Drag files directly onto attachment cell
6. **Preview:** Quick preview of PDF/images without leaving page
7. **Notifications:** Email notification on save draft reminder
8. **Comments:** Allow supplier to add comments per document
9. **Batch Upload:** Upload multiple documents at once

---

## Files Modified

1. `resources/views/supplier/partials/documents-table.blade.php` - NEW
2. `app/Http/Controllers/SupplierController.php` - UPDATED
3. `database/migrations/2026_09_02_045634_add_submission_fields_to_request_parts_table.php` - NEW
4. `app/Models/RequestPart.php` - UPDATED
5. `routes/web.php` - UPDATED
6. `resources/views/supplier/request-detail.blade.php` - UPDATED

---

## Migration Commands

```bash
# Run migration to add new fields
php artisan migrate

# If need to rollback
php artisan migrate:rollback --step=1
```

---

## Support & Maintenance

For issues or questions regarding this feature, contact the development team.

**Developer:** Kiro AI Assistant  
**Date Completed:** 02 September 2026  
**Version:** 1.0

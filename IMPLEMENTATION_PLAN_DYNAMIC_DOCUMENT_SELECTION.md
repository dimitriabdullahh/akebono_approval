# IMPLEMENTATION PLAN: Dynamic Document Requirement Selection

## EXECUTIVE SUMMARY

Sistem saat ini membuat **semua 16 dokumen** untuk setiap Request secara otomatis. Requirement baru meminta agar Akebono dapat **memilih dokumen mana yang diperlukan** untuk setiap Request menggunakan checkbox di portal.

## CURRENT STATE ANALYSIS

### Critical Issues Found:

1. **RequestService.php (line 63-75)**
   - Method `createRequestWithParts()` membuat 16 dokumen untuk setiap part secara otomatis
   - Menggunakan `DocumentType::all()` tanpa filter

2. **RequestPart.php (line 169-178)**
   - Method `createDocumentPlaceholders()` membuat semua 16 dokumen
   - Dipanggil secara otomatis

3. **Views**
   - `create.blade.php` - TIDAK ada section Document Requirement
   - `documents-table.blade.php` - Menampilkan SEMUA dokumen tanpa filter

4. **Controllers**
   - Semua controller mengasumsikan 16 dokumen ada di setiap Request
   - Validasi hanya berdasarkan `is_mandatory` flag

5. **Progress Calculation**
   - `DocumentRequest::getApprovalProgressAttribute()` - menghitung berdasarkan mandatory docs
   - Supplier view menghitung dari `$allDocuments`

## DATABASE ANALYSIS

### Current Structure:
```
document_requests
├─ id
├─ supplier_id
├─ model
├─ due_date
└─ access_token

request_parts
├─ id
├─ document_request_id
├─ part_name
├─ part_number
└─ submission_status

request_documents
├─ id
├─ document_request_id
├─ request_part_id
├─ document_type_id
├─ status
└─ file_path

document_types (16 rows)
├─ id
├─ name
├─ code
├─ is_mandatory
└─ scope
```

### Required Changes:
**NO NEW TABLES NEEDED** ✅

The current structure already supports dynamic document selection!
- `request_documents` table already links `document_request_id` + `document_type_id`
- We just need to create ONLY selected documents instead of all 16

## IMPLEMENTATION STRATEGY

### Phase 1: Database & Backend Logic ✅ SAFE - NO BREAKING CHANGES

#### 1.1 Update RequestController.php
**File**: `app/Http/Controllers/RequestController.php`

**Change**: Add validation for document selection
```php
public function store(Request $request)
{
    $validated = $request->validate([
        'supplier_id' => 'required|exists:suppliers,id',
        'model' => 'required|string|max:100',
        'due_date' => 'required|date|after:today',
        'parts' => 'required|array|min:1',
        'parts.*.part_name' => 'required|string|max:255',
        'parts.*.part_number' => 'required|string|max:100',
        'selected_documents' => 'required|array|min:1', // NEW
        'selected_documents.*' => 'required|exists:document_types,id', // NEW
    ]);
    
    // Pass selected_documents to service
    $documentRequest = $this->requestService->createRequestWithParts(
        $validated,
        auth()->id()
    );
}
```

#### 1.2 Update RequestService.php
**File**: `app/Services/RequestService.php`

**Change**: Modify `createRequestWithParts()` to accept selected documents
```php
public function createRequestWithParts(array $data, int $userId): DocumentRequest
{
    // ... existing code ...
    
    // NEW: Get ONLY selected document types
    $selectedDocumentIds = $data['selected_documents'] ?? [];
    $documentTypes = DocumentType::whereIn('id', $selectedDocumentIds)->get();
    
    // Create parts and their documents
    foreach ($data['parts'] as $partData) {
        $part = RequestPart::create([...]);
        
        // Create ONLY SELECTED documents for each part
        foreach ($documentTypes as $docType) {
            RequestDocument::create([
                'document_request_id' => $request->id,
                'request_part_id' => $part->id,
                'document_type_id' => $docType->id,
                'status' => 'PENDING',
            ]);
        }
    }
    
    return $request->load(['supplier', 'requestDocuments.documentType', 'requestParts']);
}
```

**Impact**: 
- ✅ Backward compatible - existing requests dengan 16 dokumen tetap berfungsi
- ✅ New requests hanya membuat dokumen yang dipilih

#### 1.3 Update/Remove RequestPart::createDocumentPlaceholders()
**File**: `app/Models/RequestPart.php`

**Option A - Deprecate (Recommended)**:
```php
/**
 * @deprecated Use RequestService::createRequestWithParts() instead
 * Create document placeholders for this part
 */
public function createDocumentPlaceholders()
{
    // Method ini sudah tidak digunakan
    // Dokumen dibuat saat Request creation dengan selected documents
    throw new \Exception('Method deprecated. Documents are created during request creation.');
}
```

**Impact**: 
- ⚠️ Check if any code calls this method
- ✅ Forces proper flow through RequestService

### Phase 2: Frontend - Akebono Portal ✅

#### 2.1 Update create.blade.php View
**File**: `resources/views/admin/requests/create.blade.php`

**Add After Due Date Section**:
```html
<hr class="my-4">

<!-- DOCUMENT REQUIREMENT SECTION -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Document Requirement</h5>
        <div>
            <span class="badge bg-info me-2" id="selected-count">Selected: 0 / 16</span>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                <i class="ti ti-check-all"></i> Select All
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAll()">
                <i class="ti ti-x"></i> Clear All
            </button>
        </div>
    </div>
    
    <div class="alert alert-info">
        <i class="ti ti-info-circle"></i> 
        Select the documents required for this request. Only selected documents will be requested from the supplier.
    </div>
    
    <div class="row">
        @foreach($documentTypes as $docType)
        <div class="col-md-6 mb-2">
            <div class="form-check p-3 border rounded {{ $docType->is_mandatory ? 'border-danger' : 'border-secondary' }}">
                <input class="form-check-input document-checkbox" 
                       type="checkbox" 
                       name="selected_documents[]" 
                       value="{{ $docType->id }}" 
                       id="doc_{{ $docType->id }}"
                       onchange="updateCount()">
                <label class="form-check-label w-100" for="doc_{{ $docType->id }}">
                    <strong>{{ $docType->name }}</strong>
                    @if($docType->is_mandatory)
                        <span class="badge bg-danger badge-sm ms-2">Mandatory</span>
                    @else
                        <span class="badge bg-secondary badge-sm ms-2">Optional</span>
                    @endif
                    @if($docType->description)
                        <br><small class="text-muted">{{ $docType->description }}</small>
                    @endif
                </label>
            </div>
        </div>
        @endforeach
    </div>
    
    <div id="validation-error" class="alert alert-danger mt-3" style="display: none;">
        <i class="ti ti-alert-circle"></i> Please select at least one document requirement.
    </div>
</div>
```

**Add JavaScript**:
```javascript
function updateCount() {
    const checkboxes = document.querySelectorAll('.document-checkbox');
    const checked = document.querySelectorAll('.document-checkbox:checked').length;
    const total = checkboxes.length;
    document.getElementById('selected-count').textContent = `Selected: ${checked} / ${total}`;
    
    // Hide validation error if at least 1 selected
    if (checked > 0) {
        document.getElementById('validation-error').style.display = 'none';
    }
}

function selectAll() {
    document.querySelectorAll('.document-checkbox').forEach(cb => {
        cb.checked = true;
    });
    updateCount();
}

function clearAll() {
    document.querySelectorAll('.document-checkbox').forEach(cb => {
        cb.checked = false;
    });
    updateCount();
}

// Form validation
document.getElementById('createRequestForm').addEventListener('submit', function(e) {
    const checked = document.querySelectorAll('.document-checkbox:checked').length;
    if (checked === 0) {
        e.preventDefault();
        document.getElementById('validation-error').style.display = 'block';
        document.getElementById('validation-error').scrollIntoView({ behavior: 'smooth' });
        return false;
    }
});

// Initialize count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCount();
});
```

**Impact**:
- ✅ Akebono dapat memilih dokumen yang diperlukan
- ✅ Validasi minimum 1 dokumen
- ✅ UI sesuai requirement dengan checkbox, Select All, Clear All

### Phase 3: Supplier Portal - Filter Documents ✅

#### 3.1 Update documents-table.blade.php
**File**: `resources/views/supplier/partials/documents-table.blade.php`

**Change Line 3**:
```php
@php
    // Get ONLY documents that belong to this part (already filtered by request)
    // NO CHANGES NEEDED - documents are already filtered!
    // If Request only has 5 documents, $part->requestDocuments will only return 5
    $allDocuments = isset($part) ? $part->requestDocuments->sortBy('document_type_id') : collect();
    
    // Rest of the code stays the same
    $mandatoryDocs = $allDocuments->where('documentType.is_mandatory', true);
    // ...
@endphp
```

**Impact**:
- ✅ NO CHANGES NEEDED in view logic!
- ✅ Filtering happens automatically because only selected documents are created in DB
- ✅ Supplier only sees documents that were selected by Akebono

### Phase 4: Progress Calculation - Already Dynamic! ✅

**File**: `app/Models/DocumentRequest.php`

**Current Code (Line 87-104)**:
```php
public function getApprovalProgressAttribute()
{
    $mandatory = $this->requestDocuments()->whereHas('documentType', function ($q) {
        $q->where('is_mandatory', true);
    })->count();
    
    if ($mandatory === 0) {
        return 0;
    }
    
    $approved = $this->requestDocuments()
        ->whereHas('documentType', function ($q) {
            $q->where('is_mandatory', true);
        })
        ->where('status', 'APPROVED')
        ->count();
    
    return (int) (($approved / $mandatory) * 100);
}
```

**Analysis**:
- ✅ Already uses `$this->requestDocuments()` - only counts documents that exist
- ✅ NO CHANGES NEEDED!
- ✅ If Request has 5 documents (2 mandatory), it calculates 2/2 = 100%
- ✅ If Request has 16 documents (14 mandatory), it calculates 14/14 = 100%

### Phase 5: Validation Logic - Already Dynamic! ✅

**File**: `app/Http/Controllers/SupplierController.php` (Line 493-502)

**Current Code**:
```php
// Check if all mandatory documents are uploaded
$mandatoryDocs = $part->requestDocuments()
    ->whereHas('documentType', function($query) {
        $query->where('is_mandatory', true);
    })
    ->get();

$missingMandatory = $mandatoryDocs->filter(function($doc) {
    return $doc->status === 'PENDING' || empty($doc->file_path);
});
```

**Analysis**:
- ✅ Already uses `$part->requestDocuments()` - only validates documents that exist
- ✅ NO CHANGES NEEDED!

**File**: `app/Services/DocumentUploadService.php` (Line 145-159)

**Current Code**:
```php
public function allMandatoryDocumentsSubmitted(DocumentRequest $request): bool
{
    $mandatoryDocs = $request->requestDocuments()
        ->whereHas('documentType', function ($q) {
            $q->where('is_mandatory', true);
        })
        ->get();
    
    foreach ($mandatoryDocs as $doc) {
        if (!in_array($doc->status, ['SUBMITTED', 'REVISE', 'APPROVED'])) {
            return false;
        }
    }
    
    return true;
}
```

**Analysis**:
- ✅ Already uses `$request->requestDocuments()` - only checks documents that exist
- ✅ NO CHANGES NEEDED!

### Phase 6: Email Templates - Already Dynamic! ✅

**File**: `resources/views/emails/request-letter.blade.php`

**Current State**: 
- ❌ Does NOT list required documents
- ✅ Only shows Model, Due Date, and Part list
- ✅ NO CHANGES NEEDED (but we can enhance it)

**Optional Enhancement**:
```html
@if($request->requestDocuments && $request->requestDocuments->count() > 0)
<p><strong>Required Documents ({{ $request->requestDocuments->count() }}):</strong></p>
<ul style="list-style-type: none; padding-left: 0;">
    @foreach($request->requestDocuments->unique('document_type_id') as $doc)
    <li style="padding: 5px 0;">
        ✓ {{ $doc->documentType->name }}
        @if($doc->documentType->is_mandatory)
            <span style="color: #dc3545; font-weight: bold;">*</span>
        @endif
    </li>
    @endforeach
</ul>
<p><small style="color: #666;">* Mandatory documents</small></p>
@endif
```

**Impact**:
- ✅ Optional - can be added later
- ✅ Shows supplier exactly what documents are required

### Phase 7: Approval & Review - Already Dynamic! ✅

**File**: `app/Http/Controllers/ReviewController.php`

**Current Code (Line 94-98)**:
```php
// Check jika semua mandatory docs sudah approved
if ($this->reviewService->allMandatoryDocumentsApproved($requestDocument->documentRequest)) {
    $this->notificationService->notifyApprovalCompleted($requestDocument->documentRequest);
}
```

**Analysis**:
- ✅ Uses `allMandatoryDocumentsApproved()` which queries `requestDocuments()`
- ✅ Only checks documents that exist in the Request
- ✅ NO CHANGES NEEDED!

### Phase 8: Notification Jobs - Already Dynamic! ✅

**File**: `app/Jobs/RevisionReminderJob.php` (Line 44-47)

**Current Code**:
```php
$revisionDocs = $this->documentRequest->requestDocuments()
    ->where('status', 'REJECTED')
    ->get();
```

**Analysis**:
- ✅ Uses `requestDocuments()` - only gets documents that exist
- ✅ NO CHANGES NEEDED!

**File**: `app/Jobs/DocumentsSubmittedNotificationJob.php`

**Analysis**:
- ✅ Uses `$this->documentRequest` which has filtered `requestDocuments`
- ✅ NO CHANGES NEEDED!

## SUMMARY OF CHANGES REQUIRED

### Files to MODIFY (5 files):

1. ✏️ **app/Http/Controllers/RequestController.php**
   - Add validation for `selected_documents` array
   - Pass to service

2. ✏️ **app/Services/RequestService.php**
   - Change `DocumentType::all()` to `DocumentType::whereIn('id', $selectedDocumentIds)->get()`
   - Remove hardcoded "16" comment

3. ✏️ **app/Models/RequestPart.php**
   - Deprecate `createDocumentPlaceholders()` method
   - Add exception or remove entirely

4. ✏️ **resources/views/admin/requests/create.blade.php**
   - Add Document Requirement section with checkboxes
   - Add JavaScript for Select All/Clear All
   - Add form validation

5. ✏️ **resources/views/emails/request-letter.blade.php** (OPTIONAL)
   - Add list of required documents

### Files ALREADY CORRECT (No Changes):

- ✅ `resources/views/supplier/partials/documents-table.blade.php`
- ✅ `app/Models/DocumentRequest.php` (getApprovalProgressAttribute)
- ✅ `app/Http/Controllers/SupplierController.php` (submitAllDocuments)
- ✅ `app/Services/DocumentUploadService.php` (allMandatoryDocumentsSubmitted)
- ✅ `app/Http/Controllers/ReviewController.php`
- ✅ `app/Jobs/*.php` (all notification jobs)
- ✅ All other email templates

## BACKWARD COMPATIBILITY

✅ **100% Backward Compatible**

- Existing Requests dengan 16 dokumen tetap berfungsi normal
- Progress calculation tetap akurat
- Supplier portal tetap menampilkan dokumen yang ada
- Approval workflow tidak terpengaruh
- Notification tidak terpengaruh

## DATA MIGRATION

❌ **NO MIGRATION NEEDED**

- Tidak ada perubahan struktur database
- Tidak ada penambahan table atau column
- Existing data tetap valid

## TESTING CHECKLIST

### Unit Tests:
- [ ] RequestService creates only selected documents
- [ ] Validation rejects if no documents selected
- [ ] Progress calculation with 5 documents (not 16)
- [ ] Supplier validation with partial documents

### Integration Tests:
- [ ] Create request with 5 selected documents
- [ ] Supplier sees only 5 documents
- [ ] Upload and submit 5 documents
- [ ] Approval workflow with 5 documents
- [ ] Email notifications show correct count

### Manual Tests:
- [ ] Select All / Clear All buttons work
- [ ] Minimum 1 document validation
- [ ] Supplier can't see unselected documents
- [ ] Progress shows X / X (not X / 16)
- [ ] Reminder only counts selected documents

## ROLLOUT PLAN

### Step 1: Backend Changes (Safe)
1. Update RequestService.php
2. Update RequestController.php
3. Deprecate RequestPart::createDocumentPlaceholders()
4. Test with existing requests (should still work)

### Step 2: Frontend Changes (UI)
1. Update create.blade.php
2. Add JavaScript functions
3. Test form submission

### Step 3: Testing
1. Create new request with 5 documents
2. Verify supplier sees only 5
3. Complete full workflow
4. Verify emails and notifications

### Step 4: Production Deploy
1. Deploy backend changes first
2. Deploy frontend changes
3. Monitor first few requests
4. Gather feedback

## RISKS & MITIGATION

### Risk 1: Akebono forgets to select documents
**Mitigation**: Validation prevents submission if 0 selected

### Risk 2: Supplier confused why only 5 documents (not 16)
**Mitigation**: Add helper text in supplier portal: "You need to submit X documents as requested by Akebono"

### Risk 3: Reports/Dashboard assume 16 documents
**Mitigation**: Audit all dashboard queries - they should already use `requestDocuments()`

## ACCEPTANCE CRITERIA MAPPING

| AC | Requirement | Status |
|----|-------------|--------|
| AC1 | Portal Akebono shows 16 master documents with checkbox | ✅ create.blade.php update |
| AC2 | Akebono can select partial or all documents | ✅ Checkbox functionality |
| AC3 | Request saves document selection to database | ✅ RequestService update |
| AC4 | Supplier only sees selected documents | ✅ Already works via requestDocuments() |
| AC5 | Unselected documents can't be uploaded | ✅ Already enforced (no record = no upload) |
| AC6 | Progress uses selected document count | ✅ Already dynamic |
| AC7 | Save Draft uses selected documents | ✅ Already dynamic |
| AC8 | Submit uses selected documents | ✅ Already dynamic |
| AC9 | Approval uses selected documents | ✅ Already dynamic |
| AC10 | Reminder uses selected documents | ✅ Already dynamic |
| AC11 | Escalation uses selected documents | ✅ Already dynamic |
| AC12 | Email shows selected documents | ✅ Optional enhancement |
| AC13 | No hardcoded "16" in workflow | ✅ Remove comments |
| AC14 | Master Document Type = 16 | ✅ Unchanged |
| AC15 | Each Request can have different count | ✅ Core feature |

## CONCLUSION

**Good News**: Sistem sudah 80% siap untuk dynamic document selection!

Hampir semua logic sudah menggunakan `$request->requestDocuments()` atau `$part->requestDocuments()`, yang artinya mereka sudah query dari database dan otomatis hanya mendapatkan dokumen yang ada.

**Main Changes Needed**: 
1. Request creation logic (stop creating all 16)
2. UI untuk checkbox selection di Akebono portal

**Total Effort**: ~4-6 hours development + 2-3 hours testing
**Risk Level**: LOW (highly backward compatible)
**Complexity**: MEDIUM (clear implementation path)

**Recommendation**: PROCEED ✅

# TESTING CHECKLIST: Dynamic Document Selection

## PRE-DEPLOYMENT CHECKS

### 1. Database Verification
- [ ] Check that `document_types` table has 16 records
- [ ] Verify `request_documents` table structure (no changes needed)
- [ ] Confirm `document_requests` table structure (no changes needed)

### 2. Code Review
- [ ] Verify RequestService.php only creates selected documents
- [ ] Verify RequestController.php validates selected_documents
- [ ] Verify create.blade.php has checkbox UI
- [ ] Verify RequestPart::createDocumentPlaceholders() is deprecated

---

## FUNCTIONAL TESTING

### Test Case 1: Create Request with 5 Selected Documents

**Steps:**
1. Login as Admin QA
2. Navigate to "Create Document Request"
3. Fill in:
   - Supplier: [Select any]
   - Model: "Test Model A"
   - Due Date: [Tomorrow's date]
4. Select ONLY 5 documents:
   - ☑ Process Flow Diagram (PFD)
   - ☑ FMEA
   - ☑ QCPC
   - ☑ PSW
   - ☑ Drawing Product
5. Add 1 Part:
   - Part Name: "Test Part 1"
   - Part Number: "TP-001"
6. Click "Buat Request & Kirim Email"

**Expected Results:**
- ✅ Request created successfully
- ✅ Database: `request_documents` table has EXACTLY 5 records for this request
- ✅ Email sent to supplier
- ✅ Email lists the 5 selected documents
- ✅ Selected count shows "Selected: 5 / 16"

**Verification Query:**
```sql
SELECT COUNT(*) FROM request_documents 
WHERE document_request_id = [new_request_id];
-- Should return: 5
```

---

### Test Case 2: Supplier Sees Only Selected Documents

**Steps:**
1. Login as Supplier (use the supplier from Test Case 1)
2. Navigate to Dashboard
3. Open the request created in Test Case 1

**Expected Results:**
- ✅ Supplier sees ONLY 5 documents (not 16)
- ✅ Document list shows:
  - Process Flow Diagram (PFD)
  - FMEA
  - QCPC
  - PSW
  - Drawing Product
- ✅ No other documents visible
- ✅ Progress shows "0 / 5" or "X / 5" (not X / 16)

---

### Test Case 3: Upload Documents (Supplier)

**Steps:**
1. As Supplier, upload files for all 5 documents
2. Click "Save Draft" after uploading 3 documents
3. Upload remaining 2 documents
4. Click "Submit All Documents"

**Expected Results:**
- ✅ Can upload all 5 documents
- ✅ Save Draft shows "3 / 5 uploaded"
- ✅ Submit button enabled when all 5 uploaded
- ✅ Submit validation passes (no error about missing documents)
- ✅ Status changes to "SUBMITTED"
- ✅ Notification sent to Akebono admins

---

### Test Case 4: Approval Workflow

**Steps:**
1. Login as Admin QA
2. Navigate to Review page
3. Review and approve all 5 documents

**Expected Results:**
- ✅ Only 5 documents appear in review list
- ✅ Can approve each document
- ✅ Progress shows "5 / 5 Approved"
- ✅ When all 5 approved, completion email sent
- ✅ No error about missing documents

---

### Test Case 5: Create Request with ALL 16 Documents

**Steps:**
1. Login as Admin QA
2. Create new request
3. Click "Select All" button
4. Verify "Selected: 16 / 16"
5. Submit request

**Expected Results:**
- ✅ "Select All" button works
- ✅ All 16 checkboxes checked
- ✅ Request created with 16 documents
- ✅ Supplier sees all 16 documents
- ✅ Email lists all 16 documents

**Verification Query:**
```sql
SELECT COUNT(*) FROM request_documents 
WHERE document_request_id = [new_request_id];
-- Should return: 16
```

---

### Test Case 6: Create Request with 0 Documents (Should Fail)

**Steps:**
1. Login as Admin QA
2. Create new request
3. Fill all fields EXCEPT document selection
4. Leave all checkboxes unchecked
5. Try to submit

**Expected Results:**
- ❌ Form submission blocked
- ✅ Error message displayed: "Please select at least one document requirement"
- ✅ Form stays on page (not submitted)
- ✅ No request created in database

---

### Test Case 7: Clear All Button

**Steps:**
1. Login as Admin QA
2. Create new request
3. Check several documents
4. Click "Clear All" button

**Expected Results:**
- ✅ All checkboxes become unchecked
- ✅ Selected count shows "Selected: 0 / 16"
- ✅ Badge color changes to red

---

### Test Case 8: Mixed Mandatory and Optional

**Steps:**
1. Create request with:
   - 3 Mandatory documents
   - 2 Optional documents (SOC Free, IMDS)
2. Supplier uploads all 5
3. Submit

**Expected Results:**
- ✅ Validation counts 3 mandatory (not 14)
- ✅ Can submit when all 5 uploaded
- ✅ Progress shows "5 / 5"

---

### Test Case 9: Multiple Parts with Selected Documents

**Steps:**
1. Create request with 5 selected documents
2. Add 3 parts
3. Submit

**Expected Results:**
- ✅ Request created successfully
- ✅ Database has 5 × 3 = 15 document records
- ✅ Each part shows 5 documents
- ✅ Supplier can upload documents per part

**Verification Query:**
```sql
SELECT rp.part_name, COUNT(rd.id) as doc_count
FROM request_parts rp
LEFT JOIN request_documents rd ON rd.request_part_id = rp.id
WHERE rp.document_request_id = [request_id]
GROUP BY rp.id;
-- Each part should show: 5 documents
```

---

### Test Case 10: Backward Compatibility (Existing Requests)

**Steps:**
1. Open an existing request (created before this update)
2. Check if it has 16 documents
3. Verify supplier can still access it
4. Verify approval workflow still works

**Expected Results:**
- ✅ Old requests with 16 documents still work
- ✅ No errors in supplier portal
- ✅ No errors in approval workflow
- ✅ Progress calculation still correct

---

### Test Case 11: Email Contains Document List

**Steps:**
1. Create request with 5 selected documents
2. Check supplier's email inbox
3. Open request letter email

**Expected Results:**
- ✅ Email contains section "Dokumen yang Diperlukan (5 dokumen)"
- ✅ Lists all 5 selected documents
- ✅ Shows mandatory asterisk (*)
- ✅ Shows count: "Mandatory documents (X dokumen)"

---

### Test Case 12: Progress Calculation Accuracy

**Scenario A: 5 Selected (3 Mandatory, 2 Optional)**

Upload Status:
- 2 mandatory uploaded, 1 mandatory pending
- 2 optional uploaded

**Expected Progress:**
- Dashboard: "2 / 3 mandatory uploaded"
- Approval progress: 66% (2 out of 3 mandatory)

**Scenario B: 5 Selected (All Mandatory)**

Upload Status:
- 5 uploaded and approved

**Expected Progress:**
- Dashboard: "5 / 5 mandatory uploaded"
- Approval progress: 100%

---

### Test Case 13: Reminder Notification

**Steps:**
1. Create request with 5 selected documents
2. Wait for reminder schedule (or trigger manually)
3. Check reminder email

**Expected Results:**
- ✅ Reminder sent
- ✅ Reminder mentions "5 documents" (not 16)
- ✅ Reminder shows correct pending count

---

### Test Case 14: Document Type Changes

**Steps:**
1. Admin changes document type (e.g., mark PFD as optional)
2. Create new request with PFD selected
3. Verify system uses updated document type settings

**Expected Results:**
- ✅ Uses latest document type settings
- ✅ Mandatory/optional label correct
- ✅ Validation uses correct mandatory count

---

## EDGE CASES

### Edge Case 1: Select Only Optional Documents

**Steps:**
1. Create request selecting ONLY:
   - SOC Free
   - IMDS
2. Submit

**Expected Results:**
- ✅ Request created (2 optional documents)
- ✅ Validation passes (no mandatory required if not selected)
- ✅ Can submit with 0 mandatory uploaded

---

### Edge Case 2: Partial Upload Then Change Mind

**Steps:**
1. Supplier uploads 3 out of 5 documents
2. Saves draft
3. Logs out
4. Comes back later
5. Uploads remaining 2

**Expected Results:**
- ✅ Previous 3 uploads still there
- ✅ Can upload remaining 2
- ✅ Submit works when all 5 complete

---

### Edge Case 3: Revision on Partial Selection

**Steps:**
1. Request created with 5 documents
2. Supplier uploads all 5
3. Admin requests revision on 2 documents
4. Supplier re-uploads 2 revised documents

**Expected Results:**
- ✅ Revision workflow works normally
- ✅ Only 2 documents in REVISE status
- ✅ Supplier can re-upload
- ✅ Resubmit works

---

## PERFORMANCE TESTING

### Performance Test 1: Query Efficiency

**Test:**
```php
// Create request with 5 documents
$request = DocumentRequest::with('requestDocuments.documentType')->find($id);
```

**Verify:**
- ✅ Only 1 query to get request
- ✅ Only 1 query to get 5 documents
- ✅ No N+1 query issues
- ✅ Page loads fast (< 500ms)

---

### Performance Test 2: Large Scale

**Test:**
1. Create 50 requests with varying document counts (5, 8, 12, 16)
2. Load dashboard
3. Check query count

**Verify:**
- ✅ Dashboard loads without timeout
- ✅ Proper use of eager loading
- ✅ No performance degradation

---

## SECURITY TESTING

### Security Test 1: Attempt to Upload Unselected Document

**Steps:**
1. Create request with 5 selected documents
2. As supplier, try to manually upload document #6 (not selected)
3. Use direct API call or form manipulation

**Expected Results:**
- ❌ Upload rejected
- ✅ Error message shown
- ✅ No document record created

---

### Security Test 2: Manipulate Selected Documents Array

**Steps:**
1. Start creating request
2. Use browser DevTools to add invalid document IDs
3. Submit form

**Expected Results:**
- ❌ Validation fails
- ✅ Error: "Invalid document type ID"
- ✅ Request not created

---

## USER ACCEPTANCE TESTING

### UAT 1: Admin QA Workflow

**Persona:** Admin QA creating request

**Tasks:**
1. ✅ Can easily see all 16 document types
2. ✅ Can select/deselect with checkboxes
3. ✅ Select All works as expected
4. ✅ Clear All works as expected
5. ✅ Counter updates in real-time
6. ✅ Validation prevents 0 selection
7. ✅ Form submits successfully

**Feedback:**
- Is UI intuitive?
- Are mandatory/optional badges clear?
- Is the selected count helpful?

---

### UAT 2: Supplier Workflow

**Persona:** Supplier receiving request

**Tasks:**
1. ✅ Receives email with document list
2. ✅ Opens portal and sees correct document count
3. ✅ Can upload documents
4. ✅ Progress indicator is clear
5. ✅ Submit button behavior is clear

**Feedback:**
- Is it clear which documents are required?
- Is progress indicator helpful?
- Any confusion about document count?

---

## ROLLBACK PLAN

### If Issues Found:

**Option 1: Revert Code**
```bash
git revert [commit-hash]
git push origin main
```

**Option 2: Emergency Fix**
- Revert RequestService.php to use `DocumentType::all()`
- Hide document selection UI
- Deploy hotfix

**No Database Rollback Needed**
- Existing requests unaffected
- New requests will create all 16 documents again

---

## POST-DEPLOYMENT MONITORING

### Metrics to Monitor (First 7 Days):

1. **Request Creation Success Rate**
   - Target: > 95%
   - Check for validation errors

2. **Average Documents per Request**
   - Expected: 5-12 documents
   - Track if admins use selection feature

3. **Supplier Upload Success Rate**
   - Target: Same as before
   - Check for upload errors

4. **Email Delivery Rate**
   - Target: > 99%
   - Check for email failures

5. **Approval Workflow Completion Time**
   - Expected: Faster (fewer documents)
   - Track time from submit to approve

### Dashboard Queries:

```sql
-- Average documents per request (last 7 days)
SELECT AVG(doc_count) as avg_docs
FROM (
    SELECT document_request_id, COUNT(*) as doc_count
    FROM request_documents
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY document_request_id
) t;

-- Document selection distribution
SELECT doc_count, COUNT(*) as request_count
FROM (
    SELECT document_request_id, COUNT(*) as doc_count
    FROM request_documents
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY document_request_id
) t
GROUP BY doc_count
ORDER BY doc_count;
```

---

## ACCEPTANCE CRITERIA VERIFICATION

| AC | Requirement | Test Case | Status |
|----|-------------|-----------|--------|
| AC1 | Portal shows 16 master documents with checkbox | TC1, TC5 | [ ] |
| AC2 | Akebono can select partial or all | TC1, TC5, TC6 | [ ] |
| AC3 | Request saves selection to database | TC1 verification query | [ ] |
| AC4 | Supplier only sees selected | TC2 | [ ] |
| AC5 | Unselected can't be uploaded | Security Test 1 | [ ] |
| AC6 | Progress uses selected count | TC2, TC12 | [ ] |
| AC7 | Save Draft uses selected | TC3 | [ ] |
| AC8 | Submit uses selected | TC3 | [ ] |
| AC9 | Approval uses selected | TC4 | [ ] |
| AC10 | Reminder uses selected | TC13 | [ ] |
| AC11 | Escalation uses selected | Manual test | [ ] |
| AC12 | Email shows selected | TC11 | [ ] |
| AC13 | No hardcoded "16" | Code review | [ ] |
| AC14 | Master = 16 | DB verification | [ ] |
| AC15 | Different requests, different counts | TC1, TC5 | [ ] |

---

## SIGN-OFF

### Testing Completed By:
- [ ] Developer: _________________ Date: _______
- [ ] QA Team: __________________ Date: _______
- [ ] Product Owner: _____________ Date: _______

### Issues Found:
1. _________________________________________
2. _________________________________________
3. _________________________________________

### Issues Resolved:
1. _________________________________________
2. _________________________________________
3. _________________________________________

### Deployment Approved:
- [ ] Yes, proceed to production
- [ ] No, requires fixes

**Approver:** _________________ **Date:** _______

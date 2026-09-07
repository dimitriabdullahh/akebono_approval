# Panduan Testing Inspection Standard Form

**Untuk**: PT Akebono Development Team  
**Tanggal**: 4 September 2026  
**Durasi Testing**: ~30 menit

---

## 🎯 Overview Testing

Testing akan mencakup:
1. ✅ Persiapan environment
2. ✅ Testing form dari awal sampai PDF generate
3. ✅ Testing draft save functionality
4. ✅ Testing portal UI
5. ✅ Testing approval workflow integration

---

## 📋 Prerequisites (Persiapan)

### 1. Pastikan Server Berjalan

```bash
# Terminal 1: Jalankan Laravel server
cd e:\KERJAAN\akebono-approval
php artisan serve

# Terminal 2: Jalankan queue worker (untuk email)
cd e:\KERJAAN\akebono-approval
php artisan queue:work
```

Server akan berjalan di: `http://localhost:8000`

### 2. Pastikan Database Ready

```bash
# Check database
php artisan db:show

# Pastikan tabel inspection_standards ada
php artisan db:table inspection_standards
```

### 3. Buat Test Data (Request + IS Document)

**Opsi A: Gunakan Admin Panel** (Recommended)

1. Buka browser: `http://localhost:8000/login`
2. Login sebagai admin (lihat USERS_AND_PASSWORDS.txt)
3. Buat Document Request baru
4. **PENTING**: Pilih document type "Inspection Standard (IS)" dalam checklist
5. Simpan request
6. Copy access token yang di-generate

**Opsi B: Gunakan Command (Quick)**

```bash
# Buat request dengan IS document
php artisan tinker

# Di Tinker console, jalankan:
$supplier = \App\Models\Supplier::first();
$request = \App\Models\DocumentRequest::create([
    'supplier_id' => $supplier->id,
    'model' => 'TEST-MODEL-001',
    'due_date' => now()->addDays(7),
    'access_token' => \Illuminate\Support\Str::random(64),
    'created_by' => 1
]);

$part = $request->requestParts()->create([
    'part_name' => 'Test Control Valve',
    'part_number' => 'TCV-001',
    'submission_status' => 'DRAFT'
]);

$isDocType = \App\Models\DocumentType::where('code', 'IS')->first();
$doc = $part->requestDocuments()->create([
    'document_request_id' => $request->id,
    'document_type_id' => $isDocType->id,
    'status' => 'PENDING'
]);

echo "Token: " . $request->access_token;
exit;
```

**Simpan token ini!** Anda akan butuh untuk akses portal.

---

## 🧪 Test Case 1: Access Form & UI

### Step 1: Buka Portal Supplier

```
URL: http://localhost:8000/portal/{token}

Ganti {token} dengan token yang Anda copy di atas
```

### Step 2: Verifikasi Portal Display

✅ **Yang harus terlihat**:
- Document list muncul
- Inspection Standard (IS) ada dalam list
- Badge: `[MANDATORY]` `[PENDING]`
- Button: `[Isi Form Terlebih Dahulu]` (warna biru/primary)
- Border kiri card berwarna kuning (PENDING)

❌ **Jangan terlihat**:
- Upload file area (yang biasa untuk document lain)
- Button "Upload Document"

**Screenshot Area**: Ambil screenshot portal dengan IS document visible

---

## 🧪 Test Case 2: Form Access & Multi-Step Navigation

### Step 1: Klik Button "Isi Form Terlebih Dahulu"

✅ **Yang harus terjadi**:
- Redirect ke URL: `http://localhost:8000/inspection-standard/form/{token}`
- Form muncul dengan header "Inspection Standard (IS) Form"
- Stepper terlihat dengan 4 steps:
  - Step 1: General Information (active/biru)
  - Step 2: Part Drawing (inactive/abu-abu)
  - Step 3: Inspection Items (inactive/abu-abu)
  - Step 4: Review & Submit (inactive/abu-abu)

### Step 2: Verifikasi Step 1 (General Information)

✅ **Form fields yang harus ada**:
- Model *
- Date of Issued *
- Ref. No *
- Part Route
- Part No *
- Part Name *
- Manufacturer *
- Material *
- General Tolerance
- Weight (dengan unit "kg")
- Revision Date
- Revision Record

✅ **Auto-populated fields**:
- Supplier: (harus terisi otomatis)
- Customer: "AAIJ (Purchasing & Quality Division)" (read-only)

✅ **Button yang harus terlihat**:
- Back button: HIDDEN (karena step pertama)
- Save as Draft button: VISIBLE
- Next button: VISIBLE

### Step 3: Isi Form Step 1

```
Model: TEST-MODEL-V1
Date of Issued: [Pilih tanggal hari ini]
Ref. No: IS-2024-TEST-001
Part Route: AAIJ
Part No: CV-12345
Part Name: Control Valve Assembly
Manufacturer: PT Test Manufacturing
Material: Stainless Steel 316
General Tolerance: ±0.5mm
Weight: 2.5
```

### Step 4: Test Validation

**Test A: Next tanpa isi field mandatory**
- Kosongkan field "Model"
- Klik Next
- ✅ Error muncul: "Field 'Model' is required"
- ✅ Form tidak pindah step

**Test B: Next dengan field lengkap**
- Isi semua field mandatory
- Klik Next
- ✅ Pindah ke Step 2
- ✅ Stepper update: Step 1 completed (hijau), Step 2 active (biru)

**Screenshot Area**: Step 1 terisi lengkap & Step 2 active

---

## 🧪 Test Case 3: Step 2 - Part Drawing

### Step 1: Verifikasi UI Step 2

✅ **Yang harus terlihat**:
- Title: "Part Drawing"
- Area kosong (belum ada drawing)
- Button: `[+ Add Drawing]` (biru/primary)
- Help text tentang format file

### Step 2: Upload Drawing

**Test A: Add Drawing**
1. Klik button `[+ Add Drawing]`
2. ✅ Modal muncul dengan title "Add Drawing"
3. Isi form dalam modal:
   ```
   Drawing File: [Pilih file JPG/PNG/PDF - max 10MB]
   Drawing Name: Main Assembly Drawing
   Drawing No: DWG-001
   Revision: A
   Description: Overall assembly view with dimensions
   ```
4. Klik `[Save Drawing]`
5. ✅ Modal close
6. ✅ Drawing card muncul dengan:
   - Preview gambar (jika JPG/PNG) atau icon PDF
   - Info: Drawing Name, Drawing No, Revision, Description
   - Button: [Edit] [Delete]

**Test B: Add Multiple Drawings**
1. Klik `[+ Add Drawing]` lagi
2. Upload drawing kedua dengan data berbeda
3. ✅ Kedua drawing card terlihat
4. ✅ Numbered: Drawing 1, Drawing 2

**Test C: Delete Drawing**
1. Klik button `[Delete]` pada Drawing 2
2. ✅ Confirmation dialog muncul
3. Klik "Yes, Delete!"
4. ✅ Drawing 2 hilang
5. ✅ Drawing 1 masih ada

**Test D: Validation - Next tanpa drawing**
1. Delete semua drawing
2. Klik Next
3. ✅ Error: "You must upload at least one drawing"

**Test E: Next dengan drawing**
1. Pastikan ada minimal 1 drawing
2. Klik Next
3. ✅ Pindah ke Step 3
4. ✅ Stepper: Step 1-2 completed, Step 3 active

**Screenshot Area**: Step 2 dengan 1-2 drawings & Step 3 active

---

## 🧪 Test Case 4: Step 3 - Inspection Items

### Step 1: Verifikasi UI Step 3

✅ **Yang harus terlihat**:
- Title: "Inspection Items"
- Table dengan headers:
  - No | Inspection Item | Inspection Instrument | Inspection Criterion
  - Rank | Supplier Sampling | AAIJ Sampling | Remark / Inspection Method
- Table body: KOSONG (belum ada item)
- Button: `[+ Add Inspection Item]`

### Step 2: Add Inspection Items

**Test A: Add Item 1**
1. Klik `[+ Add Inspection Item]`
2. ✅ Row baru muncul di table dengan input fields
3. Isi data:
   ```
   No: 1 (auto)
   Inspection Item: Outer Diameter
   Inspection Instrument: Vernier Caliper
   Inspection Criterion: 50.0 ± 0.2 mm
   Rank: A (dropdown)
   Supplier Sampling: 1/lot
   AAIJ Sampling: 1/lot
   Remark: Measurement at 3 points
   ```
4. ✅ Data tersimpan inline (onchange event)

**Test B: Add Item 2**
1. Klik `[+ Add Inspection Item]` lagi
2. Isi data berbeda:
   ```
   Inspection Item: Surface Finish
   Inspection Instrument: Visual Inspection
   Inspection Criterion: No scratches, Ra ≤ 1.6
   Rank: B
   Supplier Sampling: 100%
   AAIJ Sampling: Sampling
   Remark: Visual check under natural light
   ```
3. ✅ Row kedua muncul dengan No: 2

**Test C: Add Item 3**
1. Add item ketiga dengan data:
   ```
   Inspection Item: Thread Size
   Inspection Instrument: Thread Gauge
   Inspection Criterion: M20 x 1.5
   Rank: A
   Supplier Sampling: 1/lot
   AAIJ Sampling: 1/lot
   Remark: Go/No-Go gauge check
   ```

**Test D: Delete Item**
1. Klik button `[Delete]` pada item No. 2
2. ✅ Confirmation muncul
3. Klik "Yes, Delete!"
4. ✅ Item 2 hilang
5. ✅ Item 3 menjadi Item 2 (auto renumber)

**Test E: Validation - Next tanpa item**
1. Delete semua items
2. Klik Next
3. ✅ Error: "You must add at least one inspection item"

**Test F: Validation - Item tanpa mandatory field**
1. Add item baru
2. Kosongkan field "Inspection Item"
3. Klik Next
4. ✅ Error: "Item #1: Item description is required"

**Test G: Next dengan items valid**
1. Pastikan ada minimal 2-3 items dengan data lengkap
2. Klik Next
3. ✅ Pindah ke Step 4
4. ✅ Stepper: All steps active/completed kecuali Step 4

**Screenshot Area**: Step 3 dengan 2-3 inspection items terisi

---

## 🧪 Test Case 5: Step 4 - Review & Submit

### Step 1: Verifikasi Review Display

✅ **Yang harus terlihat**:

**Section A: General Information**
- Semua data dari Step 1 ditampilkan dalam format read-only
- Layout: label di atas, value di bawah dengan border biru

**Section B: Supplier & Manufacturer Information**
- Supplier name
- Customer name
- Format read-only

**Section C: Part Drawing**
- List semua drawings yang diupload
- Setiap drawing card berisi:
  - Drawing name, drawing no, revision, description
  - Filename

**Section D: Inspection Items**
- Table dengan semua inspection items
- No, Item, Instrument, Criterion, Rank, Sampling, Remark
- Format: table dengan border

✅ **Button yang harus terlihat**:
- Back button: VISIBLE
- Save as Draft: VISIBLE
- Submit Inspection Standard: VISIBLE (hijau/success)

### Step 2: Verify Data Accuracy

**Manual Check**:
1. Baca semua data di Section A
   - ✅ Semua field dari Step 1 sama
2. Check Section C
   - ✅ Drawing yang diupload ada
   - ✅ Info drawing benar
3. Check Section D
   - ✅ Inspection items lengkap
   - ✅ Row count benar

**Screenshot Area**: Full Step 4 review (scroll untuk capture semua)

---

## 🧪 Test Case 6: Save as Draft

**Test saat di Step 1 atau 2**:

### Step 1: Save Draft
1. Klik button `[Save as Draft]`
2. ✅ Loading indicator muncul
3. ✅ Success notification: "Draft saved successfully!"
4. Data tersimpan di database

### Step 2: Leave Form
1. Close tab browser atau navigate away
2. Buka portal lagi: `http://localhost:8000/portal/{token}`

### Step 3: Verify Draft Status
✅ **Yang harus terlihat di portal**:
- IS document card
- Badge: `[MANDATORY]` `[DRAFT]` (abu-abu)
- Text: "Draft in Progress"
- Button: `[Continue Form]` (bukan "Isi Form Terlebih Dahulu")
- Border kiri: ABU-ABU (DRAFT status)

### Step 4: Continue Form
1. Klik `[Continue Form]`
2. ✅ Form terbuka dengan data yang tersimpan
3. ✅ Semua field Step 1 terisi dengan data sebelumnya
4. ✅ Drawing yang diupload masih ada
5. ✅ Inspection items masih ada

**Screenshot Area**: Portal showing DRAFT status & Continue button

---

## 🧪 Test Case 7: Submit Form & PDF Generation

### Step 1: Final Submit
1. Pastikan form lengkap (semua steps valid)
2. Di Step 4, klik `[Submit Inspection Standard]`
3. ✅ Confirmation dialog muncul:
   ```
   Title: "Submit Inspection Standard?"
   Text: "Are you sure you want to submit..."
   Buttons: [Cancel] [Submit]
   ```

### Step 2: Confirm Submit
1. Klik button `[Submit]`
2. ✅ Loading indicator muncul
3. ✅ Form submit (API call ke backend)
4. **TUNGGU 3-5 detik** (PDF sedang di-generate)
5. ✅ Success notification muncul
6. ✅ Redirect ke portal: `http://localhost:8000/portal/{token}`

### Step 3: Verify Portal Update

✅ **Yang harus terlihat**:
- IS document card updated
- Badge: `[MANDATORY]` `[SUBMITTED]` (biru/info)
- Alert hijau: "Status: DISUBMIT"
- Tanggal & jam submission terlihat
- PDF info box dengan tanggal & jam
- Button: `[View]` (outline primary)
- Button: `[Download PDF]` (hijau/success)
- Border kiri: BIRU (SUBMITTED status)

❌ **Jangan terlihat**:
- Form button
- Continue Form button
- Draft badge

**Screenshot Area**: Portal showing SUBMITTED status dengan PDF buttons

---

## 🧪 Test Case 8: PDF Verification

### Step 1: View PDF
1. Klik button `[View]`
2. ✅ PDF terbuka di tab baru
3. ✅ URL: `http://localhost:8000/inspection-standard/{id}/pdf`

### Step 2: Verify PDF Content

✅ **PDF harus berisi**:

**Header**:
- Company name: "PT AKEBONO ASTRA INDONESIA"
- Document title: "INSPECTION STANDARD"
- Subtitle: "FOR PURCHASED PART"

**Section 1: General Information**
- Table dengan semua field dari form Step 1
- Values sesuai yang diinput

**Section 2: Supplier & Manufacturer Information**
- Manufacturer, Supplier, Customer
- Values benar

**Section 3: Part Drawing**
- Drawing yang diupload muncul
- Jika JPG/PNG: gambar embedded
- Jika PDF: info "PDF Document: [filename]"
- Drawing metadata terlihat

**Section 4: Inspection Items**
- Table dengan semua inspection items
- Kolom: No, Item, Instrument, Criterion, Rank, Sampling, Remark
- Semua row terlihat dengan data benar

**Footer**:
- Generation timestamp
- Company info

**Screenshot Area**: PDF preview (capture multiple pages jika perlu)

### Step 3: Download PDF
1. Close PDF tab
2. Kembali ke portal
3. Klik button `[Download PDF]`
4. ✅ Browser download PDF file
5. ✅ Filename format: `INSPECTION_STANDARD_xxx.pdf`
6. Open downloaded file
7. ✅ Content sama dengan View

---

## 🧪 Test Case 9: Admin Approval Workflow

### Step 1: Login as Admin
1. Open new tab: `http://localhost:8000/login`
2. Login dengan admin account (QA role)

### Step 2: Find IS in Review Queue
1. Navigate ke Review section: `http://localhost:8000/review`
2. ✅ IS document muncul dalam list dengan:
   - Document Type: Inspection Standard (IS)
   - Status: SUBMITTED
   - Supplier name
   - Date submitted

### Step 3: Review Document
1. Klik IS document untuk detail
2. ✅ Document info page terbuka
3. ✅ PDF link tersedia untuk download
4. ✅ Button: `[Approve]` dan `[Reject]` tersedia

### Step 4: Test Approval
1. Klik button `[Approve]`
2. ✅ Confirmation muncul
3. Confirm approve
4. ✅ Status berubah jadi APPROVED
5. ✅ Notification sent (jika email configured)

### Step 5: Verify Portal (Supplier View)
1. Kembali ke portal supplier: `http://localhost:8000/portal/{token}`
2. ✅ IS document badge: `[APPROVED]` (hijau)
3. ✅ Border kiri: HIJAU
4. ✅ PDF masih bisa didownload

**Screenshot Area**: Admin review page & Portal with APPROVED status

---

## 🧪 Test Case 10: Revision Workflow

### Step 1: Create New Request (atau gunakan existing)
1. Buat request baru dengan IS document
2. Submit IS form sampai PDF generated

### Step 2: Admin Request Revision
1. Login as admin
2. Go to review section
3. Find IS document
4. Klik `[Reject]`
5. Isi revision note:
   ```
   Catatan Revisi: Mohon tambahkan inspection item untuk visual inspection pada surface finish.
   
   Deadline: [Pilih 2 hari dari sekarang]
   ```
6. Submit rejection

### Step 3: Verify Portal (Supplier View)
1. Buka portal supplier
2. ✅ IS document badge: `[REVISE REQUIRED]` (merah)
3. ✅ Alert box kuning/orange muncul:
   - "⚠️ Revisi Diminta: [catatan revisi]"
   - "Deadline: [tanggal]"
4. ✅ Button: `[Edit Form]` (kuning/warning)
5. ✅ Border kiri: MERAH

### Step 4: Edit & Resubmit
1. Klik `[Edit Form]`
2. ✅ Form terbuka dengan data existing
3. Navigate ke Step 3
4. Add inspection item baru sesuai catatan
5. Go to Step 4 dan submit
6. ✅ New PDF generated
7. ✅ Status kembali SUBMITTED

### Step 5: Verify Resubmission
1. Admin dapat review lagi
2. PDF baru dengan item tambahan
3. Can approve after review

**Screenshot Area**: Portal showing REVISE status & Edit Form button

---

## 🧪 Test Case 11: Multi-Part Request

### Step 1: Create Multi-Part Request
1. Login as admin
2. Create new request dengan 2 parts:
   - Part 1: Control Valve
   - Part 2: Pressure Sensor
3. Both parts include IS document

### Step 2: Supplier Portal
1. Akses portal dengan token
2. ✅ Portal shows 2 sections:
   - Part 1 section dengan documents
   - Part 2 section dengan documents
3. ✅ Each section has IS document dengan button tersendiri

### Step 3: Submit Part 1 IS
1. Klik "Isi Form Terlebih Dahulu" di Part 1
2. Fill form & submit
3. ✅ Part 1 IS status: SUBMITTED
4. ✅ Part 2 IS status: masih PENDING
5. ✅ Progress bar update untuk Part 1 saja

### Step 4: Submit Part 2 IS
1. Klik button di Part 2 IS
2. Fill form with different data
3. Submit
4. ✅ Part 2 IS status: SUBMITTED
5. ✅ Overall progress: completed

**Screenshot Area**: Multi-part portal with both parts showing IS

---

## ✅ Checklist Hasil Testing

Setelah semua test case selesai, isi checklist ini:

### Database & Backend
- [ ] Migration berhasil run
- [ ] Table `inspection_standards` ada
- [ ] Table `inspection_standard_drawings` ada
- [ ] Table `inspection_standard_items` ada
- [ ] API endpoint `/api/inspection-standards/{token}` works
- [ ] API endpoint save draft works
- [ ] API endpoint submit works
- [ ] API endpoint upload drawing works

### Form UI
- [ ] Form dapat diakses via portal button
- [ ] Step 1: All fields visible & functional
- [ ] Step 2: Drawing upload works
- [ ] Step 3: Inspection items table works
- [ ] Step 4: Review displays all data
- [ ] Stepper navigation works
- [ ] Validation works per step

### Draft Functionality
- [ ] Save as Draft button works
- [ ] Portal shows DRAFT badge
- [ ] Continue Form button appears
- [ ] Form loads saved data
- [ ] All data persists (general info, drawings, items)

### Submission & PDF
- [ ] Submit button shows confirmation
- [ ] Form submits successfully
- [ ] PDF generates automatically
- [ ] PDF contains all form data
- [ ] PDF drawings appear correctly
- [ ] PDF inspection items table correct
- [ ] Portal shows SUBMITTED status
- [ ] View PDF works
- [ ] Download PDF works

### Portal UI
- [ ] IS document displays correctly
- [ ] Status badges correct
- [ ] Buttons change based on status
- [ ] Border colors correct
- [ ] Draft status displays
- [ ] Submitted status displays
- [ ] Revision alert displays

### Approval Workflow
- [ ] IS appears in admin review queue
- [ ] Admin can view IS details
- [ ] Admin can approve IS
- [ ] Admin can reject IS
- [ ] Revision workflow works
- [ ] Edit Form works after revision
- [ ] Resubmission works
- [ ] Status transitions correct

### Multi-Part
- [ ] IS works in multi-part request
- [ ] Each part has separate IS
- [ ] Progress calculation correct
- [ ] Independent submission works

---

## 🐛 Common Issues & Solutions

### Issue 1: Form button tidak muncul
**Symptom**: Portal hanya show upload area biasa
**Solution**:
```bash
# Check if IS document type exists
php artisan tinker
\App\Models\DocumentType::where('code', 'IS')->first();

# If null, run seeder
php artisan db:seed --class=DocumentTypeSeeder
```

### Issue 2: PDF tidak generate
**Symptom**: Submit success tapi tidak ada PDF
**Solution**:
```bash
# Check DOMPDF installed
composer show barryvdh/laravel-dompdf

# Check storage writable
ls -la storage/documents/

# Check error log
tail -f storage/logs/laravel.log
```

### Issue 3: Drawing upload gagal
**Symptom**: Error saat upload file
**Solution**:
```bash
# Check PHP upload limits in php.ini
upload_max_filesize = 10M
post_max_size = 12M

# Check storage disk config
php artisan config:show filesystems.disks.documents
```

### Issue 4: Data tidak persist
**Symptom**: Form kosong saat dibuka lagi
**Solution**:
```bash
# Check database
php artisan db:table inspection_standards --latest

# Check API response
# Open browser DevTools → Network → Check API responses
```

---

## 📊 Testing Report Template

Setelah testing, isi report ini:

```
INSPECTION STANDARD TESTING REPORT
==================================

Tested By: [Nama Tester]
Date: [Tanggal Testing]
Environment: [Local/Staging/Production]
Browser: [Chrome/Firefox/Safari]

SUMMARY
-------
Total Test Cases: 11
Passed: [ ] / 11
Failed: [ ] / 11
Success Rate: [ ]%

DETAILS
-------
✅ Test Case 1 - Form Access: [PASS/FAIL]
✅ Test Case 2 - Multi-Step Navigation: [PASS/FAIL]
✅ Test Case 3 - Part Drawing: [PASS/FAIL]
✅ Test Case 4 - Inspection Items: [PASS/FAIL]
✅ Test Case 5 - Review & Submit: [PASS/FAIL]
✅ Test Case 6 - Draft Save: [PASS/FAIL]
✅ Test Case 7 - Submit & PDF: [PASS/FAIL]
✅ Test Case 8 - PDF Verification: [PASS/FAIL]
✅ Test Case 9 - Admin Approval: [PASS/FAIL]
✅ Test Case 10 - Revision Workflow: [PASS/FAIL]
✅ Test Case 11 - Multi-Part: [PASS/FAIL]

ISSUES FOUND
------------
1. [Issue description]
   - Severity: [High/Medium/Low]
   - Steps to reproduce: [...]
   - Expected: [...]
   - Actual: [...]

RECOMMENDATIONS
---------------
1. [Recommendation 1]
2. [Recommendation 2]

STATUS
------
[ ] APPROVED FOR PRODUCTION
[ ] NEEDS FIXES
[ ] MORE TESTING REQUIRED
```

---

## 📞 Need Help?

Jika menemui masalah:
1. Check error logs: `storage/logs/laravel.log`
2. Check browser console (F12 → Console tab)
3. Check network requests (F12 → Network tab)
4. Review documentation: `INSPECTION_STANDARD_IMPLEMENTATION.md`

---

**Happy Testing!** 🎉

Jika semua test case PASS, system siap untuk production deployment.

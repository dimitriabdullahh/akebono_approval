# Testing Guide: Multi-Part Request Feature

## Overview
Sistem ini sekarang mendukung **MULTIPLE PART** dalam satu Request. Fitur ini memungkinkan Akebono membuat satu request dengan beberapa part, dan Supplier dapat upload dokumen untuk setiap part dalam satu halaman.

## Fitur yang Diimplementasikan

### 1. Backend Changes
- ✅ **PortalController**: Load dan hitung progress dari semua parts
- ✅ **DocumentUploadService**: Validasi dan check mandatory docs untuk semua parts
- ✅ **Save Draft**: Menyimpan progress semua parts sekaligus
- ✅ **Submit All**: Validasi mandatory docs dari semua parts dengan detail error per-part
- ✅ **Routes**: Endpoint baru untuk save-draft dan submit-all

### 2. Frontend Changes
- ✅ **Layout Vertikal**: Semua parts ditampilkan berurutan dari atas ke bawah
- ✅ **Part Header**: Setiap part memiliki header dengan Model, Part Name, Part Number, Due Date, dan Progress
- ✅ **Document Table**: Setiap part memiliki tabel dokumen sendiri
- ✅ **Total Progress**: Card menampilkan total progress dari semua parts dengan breakdown per-part
- ✅ **Three Buttons**: Upload Documents, Save Draft, Submit All Documents (hanya di paling bawah)

### 3. Request Creation
- ✅ **Admin Interface**: UI untuk menambahkan multiple parts saat create request
- ✅ **Dynamic Parts**: Tombol "Tambah Part" untuk menambah part secara dinamis
- ✅ **Document Selection**: Pilih dokumen mana saja yang diperlukan (tidak selalu 16)

## Testing Steps

### Prerequisites
1. Database sudah disetup dengan data:
   - Minimal 1 Supplier
   - Document Types (16 jenis dokumen)
   - Admin User (untuk create request)

### Step 1: Create Test Request

Gunakan command artisan untuk membuat test request:

```bash
# Basic - create request with 3 parts and 5 documents each
php artisan test:create-multipart-request

# Custom - specify supplier, number of parts, and documents
php artisan test:create-multipart-request --supplier=1 --parts=3 --docs=all

# Options:
# --supplier=ID  : Specify supplier ID
# --parts=N      : Number of parts (1-10)
# --docs=N       : Number of documents per part, or "all" for all 16
```

Command ini akan:
- Membuat 1 DocumentRequest
- Membuat 3 RequestPart (Brake Pad Front, Brake Pad Rear, Brake Disc Front)
- Membuat dokumen untuk setiap part
- Menampilkan portal URL dan access token

### Step 2: Test Portal Display

1. **Buka Portal URL** yang diberikan oleh command
2. **Verifikasi tampilan:**
   - ✅ Info header menampilkan Supplier, Model, Due Date, dan Progress keseluruhan
   - ✅ Alert box menampilkan bahwa ini adalah multi-part request
   - ✅ Part 1 muncul dengan header lengkap
   - ✅ Tabel dokumen Part 1 (16 rows)
   - ✅ Part 2 muncul setelah Part 1
   - ✅ Tabel dokumen Part 2 (16 rows)
   - ✅ Part 3 muncul setelah Part 2
   - ✅ Tabel dokumen Part 3 (16 rows)
   - ✅ TIDAK ADA button di antara parts
   - ✅ Total Progress card muncul setelah semua parts
   - ✅ Three buttons (Upload, Save Draft, Submit All) hanya di paling bawah

### Step 3: Test Upload Documents

**Test Case 3.1: Upload untuk Part 1 saja**
1. Pilih beberapa file untuk dokumen di Part 1 (misal: PFD, FMEA, QCPC)
2. Klik **"Upload Documents"**
3. **Expected Result:**
   - ✅ Success message muncul
   - ✅ Page reload
   - ✅ Dokumen Part 1 menampilkan status "DISUBMIT"
   - ✅ Progress Part 1 update (misal: 3/14 = 21%)
   - ✅ Dokumen Part 2 dan Part 3 tetap PENDING
   - ✅ Total Progress update sesuai (3/42 = 7%)

**Test Case 3.2: Upload untuk Part 2 dan Part 3**
1. Pilih file untuk Part 2 (misal: 5 dokumen)
2. Pilih file untuk Part 3 (misal: 4 dokumen)
3. Klik **"Upload Documents"**
4. **Expected Result:**
   - ✅ Success message: "9 dokumen berhasil diunggah"
   - ✅ Progress Part 2 update (5/14 = 36%)
   - ✅ Progress Part 3 update (4/14 = 29%)
   - ✅ Total Progress update (12/42 = 29%)

### Step 4: Test Save Draft

**Test Case 4.1: Save Draft**
1. Klik **"Save Draft"**
2. **Expected Result:**
   - ✅ Success message muncul: "Draft berhasil disimpan untuk X part(s)"
   - ✅ Timestamp ditampilkan
   - ✅ Page TIDAK reload

**Test Case 4.2: Verify Draft Saved**
1. Tutup browser atau buka di incognito/tab baru
2. Buka portal URL lagi
3. **Expected Result:**
   - ✅ Semua dokumen yang sudah diupload masih ada
   - ✅ Progress masih sama
   - ✅ Status dokumen tetap tersimpan

### Step 5: Test Submit All (Incomplete)

**Test Case 5.1: Submit dengan dokumen belum lengkap**
1. Pastikan belum semua mandatory docs lengkap
2. Klik **"Submit All Documents"**
3. Confirm dialog muncul → Klik "OK"
4. **Expected Result:**
   - ✅ Error message muncul
   - ✅ Detail missing documents ditampilkan PER PART:
     ```
     Dokumen mandatory yang masih kurang:
     
     Part: Brake Pad Front (AK-148510-RR)
     Missing:
       • MSA
       • IS
       • ISIR
       ...
     
     Part: Brake Pad Rear (AK-148511-RR)
     Missing:
       • PFD
       • FMEA
       ...
     ```
   - ✅ Submission TIDAK berhasil
   - ✅ Status semua parts tetap DRAFT

### Step 6: Complete All Documents

**Test Case 6.1: Upload missing mandatory documents**
1. Lihat list dokumen yang missing dari error message
2. Upload semua dokumen mandatory yang belum lengkap untuk Part 1
3. Upload Documents → verify Part 1 progress = 100% (14/14)
4. Upload semua dokumen mandatory untuk Part 2
5. Upload Documents → verify Part 2 progress = 100% (14/14)
6. Upload semua dokumen mandatory untuk Part 3
7. Upload Documents → verify Part 3 progress = 100% (14/14)
8. **Expected Result:**
   - ✅ Total Progress = 100% (42/42)
   - ✅ Status indicator shows "Ready to Submit!"

### Step 7: Test Submit All (Complete)

**Test Case 7.1: Submit dengan semua dokumen lengkap**
1. Verify total progress = 100%
2. Klik **"Submit All Documents"**
3. Confirm dialog → Klik "OK"
4. **Expected Result:**
   - ✅ Success message: "Semua dokumen berhasil disubmit! Total 3 part(s) telah disubmit."
   - ✅ Page reload
   - ✅ Semua button Upload/Save/Submit DISABLED atau HIDDEN
   - ✅ Semua dokumen menampilkan status "DISUBMIT" atau "DISETUJUI"
   - ✅ Download button muncul untuk dokumen yang sudah approved

### Step 8: Test Admin Side

**Test Case 8.1: Create Multi-Part Request via Admin UI**
1. Login sebagai Admin QA
2. Navigate ke **Requests → Create New Request**
3. Pilih Supplier
4. Isi Model: "Test Model Multi-Part"
5. Isi Due Date (future date)
6. Klik **"Tambah Part"** 2 kali → total 3 parts
7. Isi Part 1:
   - Part Name: "Brake Pad Front"
   - Part Number: "AK-148510-RR"
8. Isi Part 2:
   - Part Name: "Brake Pad Rear"
   - Part Number: "AK-148511-RR"
9. Isi Part 3:
   - Part Name: "Brake Disc Front"
   - Part Number: "AK-148512-RR"
10. Select beberapa documents (misal: pilih 5 dokumen)
11. Klik **"Buat Request & Kirim Email"**
12. **Expected Result:**
    - ✅ Success message
    - ✅ Redirect ke detail request
    - ✅ 3 Parts tampil di detail
    - ✅ Setiap part memiliki 5 documents (sesuai selection)
    - ✅ Email terkirim ke supplier

**Test Case 8.2: View Multi-Part Request**
1. Di admin dashboard, buka request yang baru dibuat
2. **Expected Result:**
   - ✅ Info menunjukkan jumlah parts
   - ✅ List semua parts dengan status masing-masing
   - ✅ Progress per-part ditampilkan
   - ✅ Total progress ditampilkan

### Step 9: Edge Cases Testing

**Test Case 9.1: Request dengan 1 Part (Backward Compatibility)**
1. Create request dengan hanya 1 part
2. **Expected Result:**
   - ✅ Portal bekerja normal
   - ✅ Layout sama seperti sebelumnya
   - ✅ Button remove part tidak tampil (karena hanya 1 part)

**Test Case 9.2: Request dengan 5 Parts**
1. Create request dengan 5 parts
2. **Expected Result:**
   - ✅ Semua 5 parts tampil vertical
   - ✅ Scroll works correctly
   - ✅ Progress calculation correct
   - ✅ Submit validates all 5 parts

**Test Case 9.3: Partial Upload across Parts**
1. Upload 100% Part 1
2. Upload 50% Part 2
3. Upload 0% Part 3
4. Save Draft
5. Submit All
6. **Expected Result:**
   - ✅ Error shows Part 2 and Part 3 missing docs
   - ✅ Part 1 marked as ready

## Acceptance Criteria Checklist

Sesuai dengan requirement document, berikut checklist acceptance criteria:

- ✅ **TEST 1**: Request dengan 2 parts → Supplier melihat 2 parts dengan 16 dokumen masing-masing
- ✅ **TEST 2**: Upload PFD Part 1 → hanya Part 1 yang berubah, Part 2 tetap pending
- ✅ **TEST 3**: Upload FMEA Part 2 → hanya Part 2 yang berubah
- ✅ **TEST 4**: Save Draft → progress tersimpan
- ✅ **TEST 5**: Logout & Login → progress tetap tersimpan
- ✅ **TEST 6**: 3 Parts ditampilkan berurutan dengan button hanya di bawah
- ✅ **TEST 7**: Part 3 belum lengkap → Submit gagal dengan error message
- ✅ **TEST 8**: Semua mandatory lengkap → Submit berhasil
- ✅ **TEST 9**: Setelah submit → Status SUBMITTED, masuk workflow approval, reminder berhenti
- ✅ **TEST 10**: Admin tambahkan Part 4 → Part 4 muncul dengan info yang benar

## Known Issues / Notes

1. **Optional Documents**: SOC Free dan IMDS adalah optional, tidak menghalangi submit
2. **File Upload**: Max 10MB per file, format: PDF, JPG, PNG
3. **Session Timeout**: Portal menggunakan token, tidak ada timeout
4. **Email Notification**: Dikirim setelah semua parts submitted
5. **Reminder**: Sistem reminder akan check bundle/request status, bukan per-dokumen

## Performance Testing

Untuk testing dengan data besar:

```bash
# Create request with 5 parts and all 16 documents (80 total documents)
php artisan test:create-multipart-request --parts=5 --docs=all

# Create request with 10 parts (maximum) and 10 documents each (100 total)
php artisan test:create-multipart-request --parts=10 --docs=10
```

**Expected Performance:**
- Page load < 3 seconds with 5 parts (80 documents)
- Page load < 5 seconds with 10 parts (160 documents)
- Upload response < 2 seconds for 5 files
- Submit validation < 1 second

## Troubleshooting

### Issue: Parts tidak muncul
**Solution**: Check browser console for errors. Verify `$request->hasMultipleParts()` returns true.

### Issue: Upload gagal
**Solution**: Check file size (max 10MB) dan format (PDF/JPG/PNG). Check server logs.

### Issue: Submit All tidak validasi dengan benar
**Solution**: Check `DocumentUploadService::getMissingMandatoryDocuments()` output. Verify mandatory flag di document_types table.

### Issue: Progress tidak update
**Solution**: Clear browser cache. Check calculation logic di PortalController::show().

## Rollback Plan

Jika terjadi issue critical:

1. Restore file backup dari:
   - `PortalController.php.backup`
   - `upload.blade.php.backup`
2. Run migration rollback jika ada
3. Clear cache: `php artisan cache:clear`
4. Restart queue workers jika menggunakan queue

## Success Criteria

Implementasi dianggap berhasil jika:

1. ✅ Semua 10 Test Cases di Acceptance Criteria passed
2. ✅ Performance testing passed (load time < 5 detik)
3. ✅ No critical bugs dalam 24 jam testing
4. ✅ Backward compatibility: request lama tetap bekerja
5. ✅ User feedback positive dari QA team

---

**Testing Date**: {{ DATE }}  
**Tested By**: {{ NAME }}  
**Environment**: {{ ENV }}  
**Result**: ⬜ PASS / ⬜ FAIL  
**Notes**: _____________________

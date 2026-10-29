# MSA Gage R&R - Appraiser Names Feature

## Overview
Fitur ini menambahkan kemampuan untuk input nama appraiser (pengecek) pada dokumen MSA Gage R&R. Nama appraiser akan ditampilkan di dokumen PDF.

## Changes Made

### 1. Frontend Form (resources/views/supplier/msa-gage-rr.blade.php)
- **Added**: Section "Appraiser Names" di form dengan 3 input fields:
  - Appraiser A (required)
  - Appraiser B (required)
  - Appraiser C (conditional - required only if 3 appraisers selected)
  
- **Added**: JavaScript logic untuk mengatur required status dari Appraiser C field:
  - Jika jumlah appraiser = 3, field Appraiser C menjadi required
  - Jika jumlah appraiser = 2, field Appraiser C menjadi optional dan value di-clear

- **Updated**: GENERAL_FIELDS constant untuk include appraiser names:
  ```javascript
  appraiserA: 'appraiser_a', 
  appraiserB: 'appraiser_b', 
  appraiserC: 'appraiser_c'
  ```

- **Updated**: `onSettingsChange()` function untuk handle appraiser C field visibility

### 2. Backend Controller (app/Http/Controllers/MsaGageRrController.php)
- **Updated**: `validated()` method untuk validasi appraiser names:
  - `appraiserA`: required (when strict mode) | nullable (when draft)
  - `appraiserB`: required (when strict mode) | nullable (when draft)
  - `appraiserC`: conditional - required only if `settings.appraisers === 3`

### 3. Calculator Service (app/Services/GageRRCalculator.php)
- **Updated**: `compute()` method untuk include appraiser names dalam output:
  ```php
  $v['appraiserA'] = $general['appraiserA'] ?? '';
  $v['appraiserB'] = $general['appraiserB'] ?? '';
  $v['appraiserC'] = $general['appraiserC'] ?? '';
  ```

- **Updated**: Method docstring untuk include appraiser parameters

### 4. PDF Template (resources/views/pdf/msa-gage-rr.blade.php)
- **Updated**: Display appraiser names next to A, B, C labels di Data Collection Sheet:
  - Font size: 6.6pt (f66 class)
  - Format: "A (nama)" dengan nama dibatasi 12 karakter menggunakan `Str::limit()`
  - Conditional display: hanya tampil jika nama tidak kosong

## Database Schema
Tidak ada perubahan database. Data appraiser names disimpan di kolom JSON `general` yang sudah ada di tabel `msa_gage_rr`.

## Data Structure
```json
{
  "general": {
    "partNoName": "...",
    "characteristics": "...",
    "specifications": "...",
    "gageName": "...",
    "gageNo": "...",
    "gageType": "...",
    "date": "...",
    "performedBy": "...",
    "appraiserA": "John Doe",
    "appraiserB": "Jane Smith",
    "appraiserC": "Bob Wilson"
  }
}
```

## User Experience

### Form View
1. User mengisi General Information termasuk field Appraiser A, B, (C)
2. Jika user memilih 2 appraisers, field Appraiser C menjadi optional
3. Jika user memilih 3 appraisers, field Appraiser C menjadi required
4. Info text menjelaskan bahwa nama akan ditampilkan di PDF

### PDF Output
- Nama appraiser ditampilkan di Data Collection Sheet halaman 1
- Format: "A (nama)" di samping label A/B/C
- Nama dipotong maksimal 12 karakter untuk menjaga layout
- Hanya tampil jika nama tersedia

## Validation Rules
- **Draft Mode**: Semua field appraiser nullable
- **Submit Mode**:
  - `appraiserA`: required, string, max 100 characters
  - `appraiserB`: required, string, max 100 characters
  - `appraiserC`: required only if `settings.appraisers === 3`, string, max 100 characters

## Testing Checklist
- [ ] Form loads dengan field appraiser
- [ ] Switching between 2 and 3 appraisers updates Appraiser C required status
- [ ] Draft save dengan appraiser names
- [ ] Submit validation dengan appraiser names
- [ ] PDF generation menampilkan appraiser names
- [ ] PDF dengan 2 appraisers (Appraiser C kosong)
- [ ] PDF dengan 3 appraisers (semua appraiser ada)
- [ ] Nama panjang terpotong dengan benar di PDF (max 12 chars)

## Backward Compatibility
✅ **Fully backward compatible**
- Data lama tanpa appraiser names akan tetap berfungsi
- Field appraiser names bersifat optional di level database (JSON nullable)
- PDF template menggunakan conditional rendering untuk nama appraiser

## Date Created
January 29, 2026

## Author
AI Assistant (Kiro)

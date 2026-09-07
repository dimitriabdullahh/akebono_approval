# Bundle Submission Reminder - Unified per Request

## 📋 Overview

Sistem reminder telah diubah dari **per part** menjadi **per document request**, sehingga jika ada request dengan multiple parts (misal: Part 1 dan Part 2), reminder akan dikirim dalam **1 email saja** yang mencakup informasi semua parts.

## ✨ Perubahan Utama

### Sebelum (Per Part)
```
Email 1: [REMINDER] Pending Document Submission - AK-56C296-FR
Email 2: [REMINDER] Pending Document Submission - AK-43A847-RR
```
**Masalah:** Supplier menerima 2 email terpisah untuk 1 request yang sama

### Sesudah (Per Request)
```
Email 1: [REMINDER] Pending Document Submission - Daihatsu Terios (2 Parts)

Isi email menampilkan TABEL dengan detail semua parts:
┌─────────────────┬───────────────┬───────────┬──────────┬──────────┐
│ Part Name       │ Part Number   │ Documents │ Progress │ Status   │
├─────────────────┼───────────────┼───────────┼──────────┼──────────┤
│ Brake Booster   │ AK-56C296-FR  │ 10/16     │ 62%      │ DRAFT    │
│ Engine Mount    │ AK-43A847-RR  │ 5/16      │ 31%      │ DRAFT    │
└─────────────────┴───────────────┴───────────┴──────────┴──────────┘
```
**Keuntungan:** 
- ✅ Supplier hanya menerima 1 email untuk 1 request
- ✅ Lebih mudah tracking progress semua parts
- ✅ Mengurangi email spam ke supplier

## 🔧 Komponen yang Diubah

### 1. Model Baru: `DocumentRequestReminder`
**File:** `app/Models/DocumentRequestReminder.php`

Model baru untuk tracking reminder di level DocumentRequest (bukan per part).

**Key Methods:**
```php
getOverallProgressInfo()    // Progress gabungan semua parts
getPartsProgressInfo()      // Progress detail per part
shouldStop()                // Check apakah semua parts sudah submitted
```

### 2. Command: `CheckBundleSubmissionReminder`
**File:** `app/Console/Commands/CheckBundleSubmissionReminder.php`

**Perubahan:**
- Check document request (bukan per part)
- Create reminder hanya 1x per request
- Stop reminder jika semua parts sudah submitted

### 3. Job: `BundleSubmissionReminderJob`
**File:** `app/Jobs/BundleSubmissionReminderJob.php`

**Perubahan:**
- Receive `DocumentRequest` dan `DocumentRequestReminder`
- Check status semua parts sebelum kirim
- Log info untuk semua parts

### 4. Mail: `BundleSubmissionReminderMail`
**File:** `app/Mail/BundleSubmissionReminderMail.php`

**Perubahan:**
- Subject menyertakan jumlah parts: `(2 Parts)`
- Pass `overallProgress` dan `partsProgress` ke view
- Generate auto-login URL per request

### 5. View: Email Template
**File:** `resources/views/emails/bundle-submission-reminder.blade.php`

**Perubahan:**
- **Request Info:** Ditampilkan dalam tabel rapi
- **Overall Progress:** Summary total parts dan documents
- **Parts Progress:** TABEL dengan kolom:
  - Part Name
  - Part Number  
  - Documents (uploaded/total)
  - Progress bar (%)
  - Status badge (DRAFT/SUBMITTED)

### 6. Test Command
**File:** `app/Console/Commands/TestBundleSubmissionReminder.php`

**Perubahan:**
- List reminders di level request
- Status check per request (bukan per part)
- Support testing multiple parts scenario

## 📊 Format Email

### Header
```
[REMINDER] Pending Document Submission - {Model} (2 Parts)
```

### Document Request Information (Tabel)
| Field        | Value                        |
|--------------|------------------------------|
| Supplier     | PT Supplier Name             |
| Model        | Daihatsu Terios              |
| Total Parts  | 2 Part(s)                    |
| Due Date     | 10 September 2026            |

### Overall Progress
- Parts Status: 0 / 2 Parts Submitted
- Pending Parts: 2 Part(s) Still in Draft
- Total Documents: 0 / 32 Documents
- Progress Bar: Visual representation

### Progress per Part (Tabel)
| Part Name     | Part Number   | Documents | Progress | Status |
|---------------|---------------|-----------|----------|--------|
| Brake Booster | AK-56C296-FR  | 10/16     | [62%]    | DRAFT  |
| Engine Mount  | AK-43A847-RR  | 5/16      | [31%]    | DRAFT  |

*Note: Progress bar dengan warna visual, Status dengan badge berwarna*

### Warning Box
⚠️ ACTION REQUIRED: 2 part(s) are still pending with no submission yet.
Please complete the upload and **SUBMIT ALL PARTS** to proceed.

## 🎨 Visual Improvements

### 1. Tabel Parts
- Header dengan background abu-abu
- Kolom terstruktur rapi
- Progress bar inline dengan warna:
  - 🟢 Hijau (SUBMITTED / 100%)
  - 🟡 Kuning (In Progress)
  - 🔴 Merah (No progress)

### 2. Status Badge
- 🟢 SUBMITTED: Background hijau muda
- 🟡 DRAFT: Background kuning muda
- Typography: Bold, rounded corners

### 3. Request Info Table
- Layout 2 kolom (Label | Value)
- Label: Abu-abu medium
- Value: Bold untuk highlight (Model, Due Date)

## 🚀 Testing

### 1. Setup Mailpit
```powershell
# Download dan extract (sudah dilakukan)
.\mailpit\mailpit.exe
```
Web UI: `http://localhost:8025`

### 2. Test Commands
```bash
# List reminders
php artisan reminder:test-bundle --list

# Check status request
php artisan reminder:test-bundle --status=1

# Run reminder checker
php artisan reminder:check-bundle-submission

# Preview email (upcoming feature)
php artisan email:preview-bundle-reminder 1 --level=1
```

### 3. Environment Config
```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
```

## 📝 Migration & Database

**Tabel:** `document_request_reminders`

Sudah ada sejak sebelumnya dengan struktur:
- `document_request_id` (FK)
- `reminder_level` (1-5)
- `escalation_status` (WAITING/STOPPED/ESCALATED_TO_DIRECTOR)
- Timestamps untuk setiap level escalation

**Tidak perlu migration baru** - tabel sudah compatible.

## ⚙️ Configuration

**File:** `config/reminder.php`

```php
return [
    'initial_reminder_delay' => env('REMINDER_INITIAL_DELAY', 1440), // minutes
    'escalation_delay' => env('REMINDER_ESCALATION_DELAY', 1440),    // minutes
    'escalation_levels' => [
        1 => 'PIC',
        2 => 'Supervisor',
        3 => 'Head Department',
        4 => 'General Manager',
        5 => 'Director',
    ],
];
```

## 📧 Email Escalation Flow

```
Level 1: PIC           → Wait 1 day →
Level 2: Supervisor    → Wait 1 day →
Level 3: Head Dept     → Wait 1 day →
Level 4: GM            → Wait 1 day →
Level 5: Director      → STOP (Max level)
```

**Auto-stop conditions:**
- Semua parts dalam request sudah SUBMITTED
- Sudah mencapai level 5 (Director)

## 🎯 Benefits

1. **Mengurangi Email Spam**
   - 1 request = 1 email (bukan per part)
   - Lebih professional dan organized

2. **Better Overview**
   - Supplier bisa lihat progress semua parts sekaligus
   - Tabel format mudah dibaca

3. **Efficient Tracking**
   - Reminder di-track per request (bukan per part)
   - Lebih mudah maintenance dan debugging

4. **Consistent Experience**
   - Single source of truth untuk 1 request
   - Semua stakeholder lihat data yang sama

## 🔄 Backward Compatibility

- Model lama `RequestPartReminder` masih ada (tidak dihapus)
- Bisa digunakan untuk referensi atau fallback
- Sistem baru menggunakan `DocumentRequestReminder`

## 📅 Implementation Date

**Date:** 4 September 2026
**Status:** ✅ Completed & Tested
**Version:** 2.0 (Unified Reminder System)

---

**Author:** Development Team  
**Last Updated:** 4 September 2026

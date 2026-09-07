# Bundle-Based Reminder System - Documentation

## 📋 Overview

Sistem **Bundle-Based Email Reminder & Escalation** adalah reminder system yang mengirim pengingat berdasarkan status **SUBMISSION** bundle dokumen, bukan berdasarkan jumlah dokumen yang diupload.

### Key Concept

**"Bundle" = RequestPart** (kumpulan dokumen untuk 1 part)

### Perbedaan dengan Reminder Lama

| Aspek | Old Reminder | Bundle Reminder |
|-------|--------------|-----------------|
| **Scope** | Per DocumentRequest | Per RequestPart (bundle) |
| **Trigger** | 0 dokumen uploaded | Bundle NOT SUBMITTED |
| **Stop Condition** | 1 dokumen uploaded | Bundle SUBMITTED |
| **Draft Upload** | Stop reminder ❌ | Continue reminder ✅ |
| **Progress Track** | 0/16 only | Any progress (5/16, 10/16, etc) |

---

## 🎯 Business Rules

### RULE 1: Reminder Tetap Jalan Meski Ada Upload
```
Bundle: 16 documents
Uploaded: 10 documents
Status: DRAFT (not submitted)
→ Reminder TETAP BERJALAN
```

### RULE 2: Reminder Stop Hanya Jika SUBMIT
```
Bundle: 16 documents
Uploaded: 16 documents
Supplier click: [SUBMIT BUNDLE]
Status: SUBMITTED
→ Reminder BERHENTI
```

### RULE 3: Save Draft ≠ Submit
```
Supplier upload 10 documents
Click: [Save Draft]
Status: DRAFT
→ Reminder TETAP JALAN
```

### RULE 4: Progress Ditampilkan di Email
```
Email content:
"Progress: 10 / 16 Documents Uploaded
Status: DRAFT (Not Submitted)
Please SUBMIT your bundle."
```

---

## 🔄 Flow Diagram

```
T = 00:00 - RequestPart created (Bundle)
           submission_status = 'DRAFT'
           
T = 01:00 - Supplier uploads 5 docs
           → Save Draft
           → submission_status = 'DRAFT'
           
T = 03:00 - ⚠️ REMINDER Level 1 (PIC)
           "Progress: 5/16 - NOT SUBMITTED"
           
T = 04:00 - Supplier uploads 5 more docs
           Total: 10/16
           → Save Draft
           → submission_status = 'DRAFT'
           
T = 05:00 - ⚠️ ESCALATION Level 2 (Supervisor)
           "Progress: 10/16 - NOT SUBMITTED"
           
T = 05:30 - Supplier clicks [SUBMIT BUNDLE]
           → submission_status = 'SUBMITTED'
           
T = 07:00 - ✅ Reminder STOPPED
           No escalation to Head Dept
```

---

## 📦 File Structure

### New Files Created

**Models:**
- `app/Models/RequestPartReminder.php`

**Mail:**
- `app/Mail/BundleSubmissionReminderMail.php`

**Jobs:**
- `app/Jobs/BundleSubmissionReminderJob.php`

**Commands:**
- `app/Console/Commands/CheckBundleSubmissionReminder.php` - Main scheduler
- `app/Console/Commands/TestBundleSubmissionReminder.php` - Testing utility

**Migrations:**
- `database/migrations/2026_09_03_090117_create_request_part_reminders_table.php`

**Views:**
- `resources/views/emails/bundle-submission-reminder.blade.php`

### Modified Files

**Models:**
- `app/Models/RequestPart.php` - Added `reminder()` relationship

**Kernel:**
- `app/Console/Kernel.php` - Updated scheduler command

---

## 🗄️ Database Schema

### Table: `request_part_reminders`

```sql
CREATE TABLE request_part_reminders (
    id BIGINT PRIMARY KEY,
    request_part_id BIGINT (FK to request_parts),
    document_request_id BIGINT (FK to document_requests),
    
    -- Status
    reminder_level INTEGER DEFAULT 0,
    escalation_status VARCHAR (WAITING/STOPPED/ESCALATED_TO_DIRECTOR),
    
    -- Timestamps
    initial_reminder_sent_at TIMESTAMP NULL,
    pic_reminder_sent_at TIMESTAMP NULL,
    supervisor_escalation_sent_at TIMESTAMP NULL,
    head_dept_escalation_sent_at TIMESTAMP NULL,
    gm_escalation_sent_at TIMESTAMP NULL,
    director_escalation_sent_at TIMESTAMP NULL,
    last_reminder_at TIMESTAMP NULL,
    next_reminder_at TIMESTAMP NULL,
    stopped_at TIMESTAMP NULL,
    
    -- Progress tracking
    total_documents INTEGER DEFAULT 0,
    uploaded_documents_at_last_check INTEGER DEFAULT 0,
    submission_status_at_last_check VARCHAR NULL,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## ⚙️ Configuration

Menggunakan config yang sama dengan reminder sebelumnya:

```env
# .env
REMINDER_INITIAL_DELAY=1          # Testing: 1 minute
REMINDER_ESCALATION_DELAY=1        # Testing: 1 minute

# Production
# REMINDER_INITIAL_DELAY=1440       # 1 day
# REMINDER_ESCALATION_DELAY=1440    # 1 day
```

---

## 🚀 How It Works

### 1. Scheduler (Every Minute)

Command: `reminder:check-bundle-submission`

**Step A: Check New Parts**
```php
- Find RequestPart without reminder
- Created >= initial_delay minutes ago
- submission_status = 'DRAFT'
- Create reminder & send PIC email
```

**Step B: Check Existing Reminders**
```php
- Find reminders with next_reminder_at <= now()
- Status = WAITING
- submission_status still DRAFT
- Send escalation email & update level
```

**Step C: Check Stop Conditions**
```php
- Find active reminders (WAITING)
- Check if submission_status = 'SUBMITTED'
- If yes: Stop reminder
```

### 2. Job Execution

`BundleSubmissionReminderJob`:
```
1. Reload data from database
2. Check if submission_status = 'SUBMITTED'
3. If SUBMITTED: Stop reminder
4. Get recipient email for current level
5. Send email via Mail::to()
6. Update reminder for next level
7. Set next_reminder_at
```

### 3. Email Content

**Level 1 (PIC):**
```
Subject: [REMINDER] Pending Document Submission - {Part Number}

Progress: 10 / 16 Documents Uploaded
Mandatory: 8 / 14 Documents Uploaded
Status: DRAFT (Not Submitted)

⚠️ ACTION REQUIRED: Bundle Not Submitted

Although 10 document(s) have been uploaded, 
the bundle has NOT been SUBMITTED yet.

Please complete and SUBMIT THE BUNDLE.
```

**Level 2-5 (Escalation):**
```
Subject: [ESCALATION LEVEL X] Pending Document Submission

Previous Reminders:
- PIC was reminded on 03 Sep 2026 10:03
- Progress: Still NOT SUBMITTED

Please take immediate action.
```

---

## 🧪 Testing Commands

### List All Reminders
```bash
php artisan reminder:test-bundle --list
```

### Check Status
```bash
php artisan reminder:test-bundle --status=1
```
Replace `1` with your RequestPart ID.

### Simulate Submit
```bash
php artisan reminder:test-bundle --submit=1
```
This will mark the part as SUBMITTED for testing.

### Reset Testing
```bash
php artisan reminder:test-bundle --reset
```

### Manual Check
```bash
php artisan reminder:check-bundle-submission
```

---

## 📝 Testing Scenarios

### Scenario 1: Upload Partial, No Submit
```
1. Create RequestPart with 16 documents
2. Supplier uploads 10 documents
3. Click [Save Draft]
4. Wait 1 minute
5. Expected: PIC reminder sent
6. Wait 1 more minute
7. Expected: Supervisor escalation sent
```

### Scenario 2: Submit Before Reminder
```
1. Create RequestPart
2. Supplier uploads all documents
3. Click [SUBMIT BUNDLE]
4. Wait 1 minute
5. Expected: No reminder sent (already SUBMITTED)
```

### Scenario 3: Submit After PIC Reminder
```
1. Create RequestPart
2. Wait 1 minute → PIC reminder sent
3. Supplier uploads and submits
4. Wait 1 minute
5. Expected: No escalation (reminder stopped)
```

### Scenario 4: Progressive Upload with Draft
```
Time    Action
00:00   Part created
00:30   Upload 5 docs → Save Draft
01:00   PIC reminder (Progress: 5/16)
01:15   Upload 5 more → Save Draft (10/16)
02:00   Supervisor escalation (Progress: 10/16)
02:30   Upload 6 more → Submit (16/16)
03:00   No more reminders (STOPPED)
```

---

## 🔧 Maintenance

### Monitor Active Reminders

```bash
# Via command
php artisan reminder:test-bundle --list

# Via database
SELECT 
    rpr.id,
    rp.part_number,
    s.name as supplier,
    rpr.reminder_level,
    rpr.escalation_status,
    rpr.uploaded_documents_at_last_check,
    rpr.total_documents,
    rpr.submission_status_at_last_check,
    rpr.next_reminder_at
FROM request_part_reminders rpr
JOIN request_parts rp ON rpr.request_part_id = rp.id
JOIN document_requests dr ON rpr.document_request_id = dr.id
JOIN suppliers s ON dr.supplier_id = s.id
WHERE rpr.escalation_status = 'WAITING'
ORDER BY rpr.next_reminder_at;
```

### Check Logs

```bash
tail -f storage/logs/laravel.log | grep BundleSubmissionReminder
```

---

## 🎓 Key Differences from Old System

### Old System (DocumentRequestReminder)
```php
// Stop condition
if ($documentCount > 0) {
    stop_reminder();
}

// Trigger
"No documents uploaded at all"
```

### New System (RequestPartReminder)
```php
// Stop condition
if ($part->submission_status === 'SUBMITTED') {
    stop_reminder();
}

// Trigger
"Bundle not submitted yet"
// Even if 15/16 documents uploaded
```

---

## ✅ Implementation Checklist

- ✅ Model `RequestPartReminder` created
- ✅ Migration for `request_part_reminders` table
- ✅ Mail class `BundleSubmissionReminderMail`
- ✅ Job `BundleSubmissionReminderJob`
- ✅ Command `CheckBundleSubmissionReminder`
- ✅ Testing command `TestBundleSubmissionReminder`
- ✅ Email template with progress bar
- ✅ Scheduler updated in Kernel.php
- ✅ Stop condition based on submission_status
- ✅ Progress tracking (X/Y documents)
- ✅ Config reuse (REMINDER_INITIAL_DELAY, REMINDER_ESCALATION_DELAY)

---

## 🔄 Migration from Old System

### What Changed?

1. **Scheduler Command**
   - OLD: `reminder:check-no-submission`
   - NEW: `reminder:check-bundle-submission`

2. **Tracking Level**
   - OLD: Per DocumentRequest
   - NEW: Per RequestPart

3. **Stop Logic**
   - OLD: Check document count > 0
   - NEW: Check submission_status = 'SUBMITTED'

4. **Email Content**
   - OLD: "No documents submitted"
   - NEW: "X/Y documents uploaded but NOT SUBMITTED"

### Backward Compatibility

The old system (`DocumentRequestReminder`) is still in the database but **not scheduled** anymore. To re-enable:

```php
// In Kernel.php, add:
$schedule->command('reminder:check-no-submission')
    ->everyMinute()
    ->withoutOverlapping();
```

Or run both systems:
```php
$schedule->command('reminder:check-no-submission')->everyMinute();
$schedule->command('reminder:check-bundle-submission')->everyMinute();
```

---

## 📚 Related Documentation

- `REMINDER_SYSTEM_DOCUMENTATION.md` - Old reminder system
- `REMINDER_QUICK_START.md` - Quick testing guide
- `IMPLEMENTATION_SUMMARY.md` - Implementation details

---

**Created**: 3 September 2026
**Version**: 1.0
**System**: Bundle-Based Reminder (RequestPart level)

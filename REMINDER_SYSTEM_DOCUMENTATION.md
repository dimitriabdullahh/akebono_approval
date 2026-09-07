# Email Reminder & Escalation System - Documentation

## 📋 Daftar Isi

1. [Overview](#overview)
2. [Konsep Dasar](#konsep-dasar)
3. [Flow Reminder & Escalation](#flow-reminder--escalation)
4. [Database Structure](#database-structure)
5. [File yang Dibuat/Diubah](#file-yang-dibuatdiubah)
6. [Konfigurasi](#konfigurasi)
7. [Cara Kerja Scheduler](#cara-kerja-scheduler)
8. [Testing](#testing)
9. [Production Setup](#production-setup)
10. [Troubleshooting](#troubleshooting)

---

## Overview

Fitur **Email Reminder & Escalation System** adalah sistem otomatis yang mengirimkan pengingat kepada Supplier untuk mengirimkan dokumen yang diminta. Jika Supplier belum mengirimkan **satupun dokumen** dalam waktu tertentu, sistem akan melakukan escalation secara bertahap dari PIC → Supervisor → Head Department → General Manager → Direktur Utama.

### Key Features:

✅ **Reminder Otomatis** - Tidak perlu aksi manual dari Akebono
✅ **Escalation Bertingkat** - 5 level escalation hierarchy
✅ **Stop Otomatis** - Reminder berhenti jika Supplier sudah mengirim minimal 1 dokumen
✅ **Configurable Timing** - Interval dapat disesuaikan via config
✅ **Email Professional** - Template email yang berbeda untuk setiap level
✅ **Background Processing** - Berjalan di backend menggunakan Laravel Scheduler

---

## Konsep Dasar

### Kondisi Awal Reminder

Setelah Akebono membuat request dokumen dan mengirimkannya ke Supplier:

```
Submission Status: WAITING_FOR_SUPPLIER
Document Progress: 0 / 14 Mandatory Documents
                  0 / 16 Total Documents
```

Sistem mulai menghitung waktu reminder.

### Business Rules

**RULE 1**: Jika **0 dokumen** terkirim → Reminder/escalation berjalan

**RULE 2**: Jika **minimal 1 dokumen** sudah dikirim → Reminder berhenti

**RULE 3**: Save Draft tanpa file upload **TIDAK** dihitung sebagai dokumen terkirim

**RULE 4**: Upload minimal 1 file dihitung sebagai Supplier sudah mulai submission

**RULE 5**: Optional document tidak menjadi syarat untuk menghentikan reminder

**RULE 6**: Setiap escalation hanya dikirim **sekali**

**RULE 7**: Setelah level Direktur Utama, tidak ada escalation lebih lanjut

**RULE 8**: Reminder berjalan di **backend scheduler**, bukan frontend

**RULE 9**: Semua reminder tercatat dalam reminder history

**RULE 10**: Interval testing harus configurable untuk production

---

## Flow Reminder & Escalation

### Testing Mode (3 menit initial + 2 menit escalation)

```
Time    Action                           Recipient
-----   -------------------------------- ------------------
00:00   Request dibuat                   -
↓
03:00   Initial Reminder                 PIC Supplier
        (if no document submitted)
↓
05:00   Escalation Level 2               Supervisor
        (if no document submitted)
↓
07:00   Escalation Level 3               Head Department
        (if no document submitted)
↓
09:00   Escalation Level 4               General Manager
        (if no document submitted)
↓
11:00   Escalation Level 5               Direktur Utama
        (if no document submitted)
↓
        STOP (max level reached)
```

### Kondisi Stop

Reminder akan berhenti jika:
1. **Supplier upload minimal 1 dokumen** (status SUBMITTED/APPROVED/REVISE)
2. **Sudah mencapai level 5** (Direktur Utama)

---

## Database Structure

### 1. Table: `document_request_reminders`

```sql
CREATE TABLE document_request_reminders (
    id BIGINT PRIMARY KEY,
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
    
    -- Metadata
    document_count_at_last_check INTEGER DEFAULT 0,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 2. Table: `suppliers` (added fields)

```sql
ALTER TABLE suppliers ADD COLUMN (
    supervisor_name VARCHAR NULL,
    supervisor_email VARCHAR NULL,
    head_dept_name VARCHAR NULL,
    head_dept_email VARCHAR NULL,
    gm_name VARCHAR NULL,
    gm_email VARCHAR NULL,
    director_name VARCHAR NULL,
    director_email VARCHAR NULL
);
```

---

## File yang Dibuat/Diubah

### ✅ File Baru

1. **Config**
   - `config/reminder.php` - Konfigurasi reminder intervals dan levels

2. **Migration**
   - `database/migrations/2026_09_03_080247_create_document_request_reminders_table.php`
   - `database/migrations/2026_09_03_080643_add_escalation_email_fields_to_suppliers_table.php`

3. **Model**
   - `app/Models/DocumentRequestReminder.php`

4. **Mail**
   - `app/Mail/NoSubmissionReminderMail.php`

5. **Job**
   - `app/Jobs/NoSubmissionReminderJob.php`

6. **Console Command**
   - `app/Console/Commands/CheckNoSubmissionReminder.php` - Main scheduler command
   - `app/Console/Commands/TestNoSubmissionReminder.php` - Testing utility

7. **View**
   - `resources/views/emails/no-submission-reminder.blade.php`

### 📝 File Diubah

1. **Models**
   - `app/Models/DocumentRequest.php` - Added reminder() relationship
   - `app/Models/Supplier.php` - Added escalation email fields

2. **Kernel**
   - `app/Console/Kernel.php` - Added scheduler entry

3. **Environment**
   - `.env` - Added REMINDER_INITIAL_DELAY and REMINDER_ESCALATION_DELAY
   - `.env.example` - Added reminder config examples

---

## Konfigurasi

### Environment Variables (.env)

```env
# TESTING MODE (minutes)
REMINDER_INITIAL_DELAY=3
REMINDER_ESCALATION_DELAY=2

# PRODUCTION MODE (minutes)
# REMINDER_INITIAL_DELAY=1440    # 1 day
# REMINDER_ESCALATION_DELAY=1440 # 1 day
```

### Config File (config/reminder.php)

```php
return [
    // Initial reminder delay (dalam menit)
    'initial_reminder_delay' => env('REMINDER_INITIAL_DELAY', 3),
    
    // Escalation delay (dalam menit)
    'escalation_delay' => env('REMINDER_ESCALATION_DELAY', 2),
    
    // Escalation levels
    'escalation_levels' => [
        1 => 'PIC',
        2 => 'Supervisor',
        3 => 'Head Department',
        4 => 'General Manager',
        5 => 'Director',
    ],
];
```

---

## Cara Kerja Scheduler

### Laravel Scheduler Setup

Scheduler sudah dikonfigurasi di `app/Console/Kernel.php`:

```php
$schedule->command('reminder:check-no-submission')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
```

### Menjalankan Scheduler (Development)

**Option 1: Laravel Scheduler (Recommended)**

```bash
# Jalankan scheduler Laravel (akan check setiap menit)
php artisan schedule:work
```

**Option 2: Manual Run**

```bash
# Jalankan manual untuk testing
php artisan reminder:check-no-submission
```

### Menjalankan Scheduler (Production)

Setup cron job di server:

```bash
# Edit crontab
crontab -e

# Tambahkan line ini:
* * * * * cd /path/to/akebono-approval && php artisan schedule:run >> /dev/null 2>&1
```

### Logic Scheduler

Command `reminder:check-no-submission` melakukan 3 hal:

1. **Check New Requests**
   - Cari request yang belum punya reminder record
   - Sudah lewat initial_delay
   - Buat reminder dan kirim email PIC

2. **Check Existing Reminders**
   - Cari reminder yang next_reminder_at sudah lewat
   - Belum stopped
   - Dispatch job untuk kirim email escalation

3. **Check Stop Conditions**
   - Cari reminder yang masih WAITING
   - Check apakah ada dokumen yang sudah dikirim
   - Stop reminder jika ada dokumen

---

## Testing

### 1. Setup Testing Environment

```bash
# 1. Pastikan migrations sudah jalan
php artisan migrate

# 2. Pastikan .env sudah dikonfigurasi
REMINDER_INITIAL_DELAY=3
REMINDER_ESCALATION_DELAY=2

# 3. Pastikan Mailpit running (untuk testing email)
# Check: http://localhost:8025
```

### 2. Create Test Request

```bash
# Buat request test dengan supplier lengkap
php artisan reminder:test-no-submission --create
```

Output:
```
✅ Test request created successfully!

📋 Request Details:
   Request ID: 123
   Supplier: PT Test Reminder Supplier
   Model: Test Model 20260903081234
   Part Number: TEST-081234
   Created At: 2026-09-03 08:12:34

⏱️  Timeline (dengan config testing):
   +3 minutes: PIC Reminder
   +5 minutes: Supervisor Escalation
   +7 minutes: Head Department Escalation
   +9 minutes: General Manager Escalation
   +11 minutes: Director Escalation
```

### 3. Jalankan Scheduler

```bash
# Option 1: Run scheduler continuously
php artisan schedule:work

# Option 2: Run manual setiap kali ingin test
php artisan reminder:check-no-submission
```

### 4. Monitor Status

```bash
# Check status reminder untuk request tertentu
php artisan reminder:test-no-submission --status=123

# List semua reminders
php artisan reminder:test-no-submission --list
```

### 5. Check Email

Buka Mailpit: http://localhost:8025

Email akan terkirim ke:
- pic@test-reminder.com (Level 1)
- supervisor@test-reminder.com (Level 2)
- head@test-reminder.com (Level 3)
- gm@test-reminder.com (Level 4)
- director@test-reminder.com (Level 5)

### 6. Test Stop Condition

Upload 1 dokumen melalui portal Supplier, kemudian check status lagi:

```bash
php artisan reminder:test-no-submission --status=123
```

Seharusnya reminder sudah berstatus `STOPPED`.

### 7. Reset Testing

```bash
# Hapus semua reminders untuk testing ulang
php artisan reminder:test-no-submission --reset
```

---

## Test Cases

### TEST CASE 1: Initial Reminder
```
✓ Buat request baru
✓ Tunggu 3 menit
✓ Expected: Email masuk ke PIC, reminder_level = 1
```

### TEST CASE 2: Escalation to Supervisor
```
✓ Setelah PIC reminder dikirim
✓ Jangan upload dokumen
✓ Tunggu 2 menit
✓ Expected: Email masuk ke Supervisor, reminder_level = 2
```

### TEST CASE 3: Escalation to Head Department
```
✓ Setelah Supervisor escalation
✓ Jangan upload dokumen
✓ Tunggu 2 menit
✓ Expected: Email masuk ke Head Dept, reminder_level = 3
```

### TEST CASE 4: Escalation to General Manager
```
✓ Setelah Head Dept escalation
✓ Jangan upload dokumen
✓ Tunggu 2 menit
✓ Expected: Email masuk ke GM, reminder_level = 4
```

### TEST CASE 5: Escalation to Director
```
✓ Setelah GM escalation
✓ Jangan upload dokumen
✓ Tunggu 2 menit
✓ Expected: Email masuk ke Director, reminder_level = 5
✓ Expected: escalation_status = ESCALATED_TO_DIRECTOR
✓ Expected: next_reminder_at = NULL
```

### TEST CASE 6: Stop After Document Upload (After PIC Reminder)
```
✓ Buat request baru
✓ Tunggu 3 menit (PIC reminder sent)
✓ Supplier upload 1 dokumen
✓ Expected: document_count = 1
✓ Expected: escalation_status = STOPPED
✓ Expected: No more escalation emails sent
```

### TEST CASE 7: Stop Before Initial Reminder
```
✓ Buat request baru
✓ Supplier upload 5 dokumen dalam 2 menit
✓ Expected: Tidak ada initial reminder yang dikirim
✓ Expected: Reminder dibuat dengan status STOPPED
```

### TEST CASE 8: Stop Right Before Escalation
```
✓ Buat request baru
✓ Tunggu sampai PIC reminder dikirim
✓ Upload 1 dokumen tepat sebelum waktu escalation ke Supervisor
✓ Expected: System check kondisi terbaru sebelum kirim escalation
✓ Expected: Jika document_count > 0, JANGAN kirim escalation
✓ Expected: Reminder status = STOPPED
```

---

## Production Setup

### 1. Ubah Config ke Production Mode

Edit `.env`:

```env
# PRODUCTION MODE (1 day = 1440 minutes)
REMINDER_INITIAL_DELAY=1440
REMINDER_ESCALATION_DELAY=1440
```

### 2. Setup Cron Job

```bash
# Login ke server
ssh user@server

# Edit crontab
crontab -e

# Tambahkan:
* * * * * cd /path/to/akebono-approval && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Setup Queue Worker (Optional tapi Recommended)

```bash
# Install supervisor
sudo apt-get install supervisor

# Create config: /etc/supervisor/conf.d/akebono-worker.conf
[program:akebono-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/akebono-approval/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/akebono-approval/storage/logs/worker.log

# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start akebono-worker:*
```

### 4. Pastikan Email Production Configured

Edit `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-email-provider.com
MAIL_PORT=587
MAIL_USERNAME=your-email@company.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@akebono.id"
MAIL_FROM_NAME="PT Akebono Astra Indonesia"
```

### 5. Update Supplier Data

Pastikan semua supplier memiliki escalation emails:

```sql
UPDATE suppliers SET
    supervisor_email = 'supervisor@supplier.com',
    supervisor_name = 'Nama Supervisor',
    head_dept_email = 'head@supplier.com',
    head_dept_name = 'Nama Head Dept',
    gm_email = 'gm@supplier.com',
    gm_name = 'Nama GM',
    director_email = 'director@supplier.com',
    director_name = 'Nama Direktur'
WHERE id = 1;
```

---

## Troubleshooting

### Email tidak terkirim

**Check 1**: Apakah scheduler running?
```bash
php artisan schedule:work
```

**Check 2**: Apakah Mailpit running (development)?
```bash
# Check http://localhost:8025
```

**Check 3**: Check log
```bash
tail -f storage/logs/laravel.log
```

**Check 4**: Test email configuration
```bash
php artisan tinker
Mail::raw('Test email', function($msg) {
    $msg->to('test@example.com')->subject('Test');
});
```

### Reminder tidak jalan

**Check 1**: Apakah migration sudah jalan?
```bash
php artisan migrate:status
```

**Check 2**: Apakah config sudah benar?
```bash
php artisan config:cache
php artisan config:clear
```

**Check 3**: Manual run command
```bash
php artisan reminder:check-no-submission -vvv
```

### Reminder tidak stop setelah upload dokumen

**Check 1**: Apakah file_path sudah terisi?
```sql
SELECT id, status, file_path FROM request_documents WHERE document_request_id = 123;
```

**Check 2**: Force stop reminder
```bash
php artisan tinker
$reminder = App\Models\DocumentRequestReminder::where('document_request_id', 123)->first();
$reminder->stop();
```

### Duplicate emails terkirim

**Problem**: Scheduler running multiple times

**Solution**: 
```php
// Already handled in Kernel.php
$schedule->command('reminder:check-no-submission')
    ->everyMinute()
    ->withoutOverlapping();  // ← Prevents duplicate runs
```

### Testing interval tidak berubah

**Check 1**: Clear config cache
```bash
php artisan config:clear
php artisan cache:clear
```

**Check 2**: Restart scheduler
```bash
# Stop current scheduler (Ctrl+C)
# Start again
php artisan schedule:work
```

---

## Monitoring & Logs

### Check Reminder Status

```bash
# Via command
php artisan reminder:test-no-submission --list

# Via database
SELECT 
    dr.id as request_id,
    s.name as supplier,
    drr.reminder_level,
    drr.escalation_status,
    drr.next_reminder_at,
    drr.stopped_at
FROM document_requests dr
LEFT JOIN document_request_reminders drr ON dr.id = drr.document_request_id
LEFT JOIN suppliers s ON dr.supplier_id = s.id
WHERE drr.escalation_status = 'WAITING'
ORDER BY drr.next_reminder_at;
```

### Check Logs

```bash
# Application log
tail -f storage/logs/laravel.log | grep NoSubmissionReminder

# Scheduler log (if using cron)
tail -f /var/log/cron.log
```

### Monitor Queue

```bash
# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

---

## Summary

### Fitur yang Diimplementasikan

✅ Reminder otomatis setelah 3 menit (testing) / 1 hari (production)
✅ Escalation bertingkat 5 level
✅ Stop otomatis jika ada dokumen yang dikirim
✅ Email template professional untuk setiap level
✅ Background processing dengan Laravel Scheduler
✅ Configurable timing via .env
✅ Testing utility commands
✅ Comprehensive logging
✅ Race condition prevention
✅ Duplicate email prevention

### Maintenance

- Monitor log secara berkala
- Update supplier escalation emails ketika ada perubahan
- Review reminder intervals sesuai kebutuhan bisnis
- Monitor queue workers di production

---

**Dokumentasi dibuat**: 3 September 2026
**Version**: 1.0
**Author**: Kiro AI Assistant

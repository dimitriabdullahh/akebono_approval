# 🚀 Email Reminder & Escalation - Quick Start Guide

## Testing dalam 15 Menit

### Step 1: Pastikan Environment Ready

```bash
# Check migrations
php artisan migrate:status

# Jika belum migrate
php artisan migrate

# Clear cache
php artisan config:clear
php artisan cache:clear
```

### Step 2: Check Konfigurasi

Pastikan file `.env` memiliki:

```env
REMINDER_INITIAL_DELAY=3
REMINDER_ESCALATION_DELAY=2
```

### Step 3: Buat Test Request

```bash
php artisan reminder:test-no-submission --create
```

Output akan menampilkan Request ID dan timeline testing.

### Step 4: Jalankan Scheduler

**Terminal 1 - Scheduler:**
```bash
php artisan schedule:work
```

Biarkan terminal ini running. Scheduler akan check setiap menit.

### Step 5: Monitor Email

**Buka Mailpit**: http://localhost:8025

Email akan masuk sesuai timeline:
- **03:00** - PIC Supplier
- **05:00** - Supervisor
- **07:00** - Head Department
- **09:00** - General Manager
- **11:00** - Direktur Utama

### Step 6: Monitor Status (Optional)

**Terminal 2 - Monitor:**
```bash
# Replace 1 dengan Request ID yang dibuat
php artisan reminder:test-no-submission --status=1
```

Jalankan command ini setiap beberapa menit untuk melihat progress.

### Step 7: Test Stop Condition (Optional)

Upload 1 dokumen melalui Supplier Portal, lalu check status lagi:

```bash
php artisan reminder:test-no-submission --status=1
```

Reminder seharusnya sudah berstatus `STOPPED`.

---

## Timeline Testing (3 menit + 2 menit interval)

```
⏱️  00:00 - Request dibuat
    ↓
    03:00 - ✉️  Email Level 1 (PIC)
    ↓
    05:00 - ✉️  Email Level 2 (Supervisor)
    ↓
    07:00 - ✉️  Email Level 3 (Head Department)
    ↓
    09:00 - ✉️  Email Level 4 (General Manager)
    ↓
    11:00 - ✉️  Email Level 5 (Direktur Utama)
    ↓
    ✅ DONE (Max level reached)
```

---

## Command Cheat Sheet

```bash
# Create test request
php artisan reminder:test-no-submission --create

# Check status
php artisan reminder:test-no-submission --status=<id>

# List all reminders
php artisan reminder:test-no-submission --list

# Reset all reminders
php artisan reminder:test-no-submission --reset

# Run scheduler manually
php artisan reminder:check-no-submission

# Run scheduler continuously
php artisan schedule:work
```

---

## Troubleshooting

### ❌ Email tidak terkirim

1. Check scheduler running: `php artisan schedule:work`
2. Check Mailpit running: http://localhost:8025
3. Check logs: `tail -f storage/logs/laravel.log`

### ❌ Reminder tidak jalan

1. Clear cache: `php artisan config:clear`
2. Run manual: `php artisan reminder:check-no-submission -vvv`
3. Check database: `SELECT * FROM document_request_reminders;`

### ❌ Reminder tidak stop setelah upload

1. Check file uploaded: `SELECT file_path FROM request_documents WHERE document_request_id = <id>;`
2. Run scheduler: `php artisan reminder:check-no-submission`
3. Manual stop: Via tinker `$reminder->stop();`

---

## Production Setup (1 Hari Interval)

### 1. Update .env

```env
REMINDER_INITIAL_DELAY=1440    # 1 day
REMINDER_ESCALATION_DELAY=1440 # 1 day
```

### 2. Clear cache

```bash
php artisan config:clear
php artisan cache:clear
```

### 3. Setup Cron

```bash
crontab -e

# Add:
* * * * * cd /path/to/akebono-approval && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Update Supplier Emails

Pastikan semua supplier memiliki email escalation lengkap di database.

---

## Video Demo Flow

1. **00:00** - Buat request dengan `--create`
2. **00:30** - Check status dengan `--status`
3. **03:00** - Email PIC masuk di Mailpit
4. **03:30** - Check status lagi (level = 1)
5. **05:00** - Email Supervisor masuk
6. **05:30** - Check status lagi (level = 2)
7. **06:00** - Upload 1 dokumen via portal
8. **06:30** - Check status (should be STOPPED)
9. **07:00** - Tidak ada email Head Dept (reminder stopped)

---

Untuk dokumentasi lengkap, lihat: **REMINDER_SYSTEM_DOCUMENTATION.md**

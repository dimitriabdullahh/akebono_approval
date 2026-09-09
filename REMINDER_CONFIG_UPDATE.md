# 🚀 Reminder Configuration Update - 1 Minute Delay

**Date:** September 9, 2026  
**Status:** ✅ Applied

---

## 📋 Changes Made

### 1. Updated Config File
**File:** `config/reminder.php`

```php
// BEFORE:
'initial_reminder_delay' => env('REMINDER_INITIAL_DELAY', 3),
'escalation_delay' => env('REMINDER_ESCALATION_DELAY', 2),

// AFTER:
'initial_reminder_delay' => env('REMINDER_INITIAL_DELAY', 1),
'escalation_delay' => env('REMINDER_ESCALATION_DELAY', 1),
```

### 2. Updated .env File
**File:** `.env`

```env
# BEFORE:
REMINDER_INITIAL_DELAY=1    # Already 1 minute
REMINDER_ESCALATION_DELAY=1 # Already 1 minute

# AFTER: (No change needed - already correct)
REMINDER_INITIAL_DELAY=1
REMINDER_ESCALATION_DELAY=1
```

### 3. Cached Configuration
**Command:** `php artisan config:cache`
✅ Configuration successfully cached

---

## 📊 Timeline with New 1-Minute Delay

| Time | Event |
|------|-------|
| 08:00:00 | Request created |
| 08:01:00 | Scheduler runs → Reminder Level 1 (PIC) sent |
| 08:01:XX | Job processes → `reminder_level = 2`, `next_reminder_at = 08:02:00` |
| 08:02:00 | Scheduler runs → Reminder Level 2 (Supervisor) sent |
| 08:02:XX | Job processes → `reminder_level = 3`, `next_reminder_at = 08:03:00` |
| 08:03:00 | Scheduler runs → Reminder Level 3 (Head Dept) sent |
| 08:03:XX | Job processes → `reminder_level = 4`, `next_reminder_at = 08:04:00` |
| 08:04:00 | Scheduler runs → Reminder Level 4 (GM) sent |
| 08:04:XX | Job processes → `reminder_level = 5`, `next_reminder_at = 08:05:00` |
| 08:05:00 | Scheduler runs → Reminder Level 5 (Director) sent |
| 08:05:XX | Job processes → `escalation_status = ESCALATED_TO_DIRECTOR`, `next_reminder_at = NULL` |
| 08:05:01+ | No more reminders sent (max escalation reached) |

---

## ⚡ Fast Escalation Behavior

Dengan delay 1 menit, escalation dari PIC ke Director hanya membutuhkan **~5 menit**, ideal untuk:

✅ **Testing & Development** - Cepat melihat semua level escalation  
✅ **Demo** - Menunjukkan full workflow dalam waktu singkat  
✅ **UAT** - Validasi escalation logic tanpa menunggu lama  

---

## ⚙️ Configuration Notes

- **Default value in code:** Diset ke `1` menit jika env var tidak ada
- **Environment variable override:** Gunakan `.env` untuk override (sudah diset ke 1)
- **Production adjustment:** Ubah ke `1440` (1 day) sebelum deploy ke production

```env
# Production configuration
REMINDER_INITIAL_DELAY=1440      # 1 day
REMINDER_ESCALATION_DELAY=1440   # 1 day
```

---

## 🔍 Verification Commands

**Check if scheduler is running:**
```bash
php artisan schedule:work
```

**Manual trigger reminder check:**
```bash
php artisan reminder:check-bundle-submission -v
```

**Check database state:**
```bash
# In SQL/Database client:
SELECT id, reminder_level, escalation_status, next_reminder_at, last_reminder_at 
FROM document_request_reminders 
ORDER BY updated_at DESC 
LIMIT 5;
```

---

## 📝 Summary

| Item | Before | After |
|------|--------|-------|
| Initial Delay | 3 minutes | **1 minute** ✅ |
| Escalation Delay | 2 minutes | **1 minute** ✅ |
| Full Escalation Time | ~7-8 minutes | **~5 minutes** ✅ |
| Config Cached | No | **Yes** ✅ |

---

**Ready for testing!** 🎯

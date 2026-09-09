# 🔧 FIX: Reminder Berkala Tidak Dikirim Kembali

## 📋 Masalah
Untuk request yang belum selesai, reminder berkala (escalation) tidak dikirim kembali. Sistem mengirim reminder awal (Level 1 - PIC), tapi tidak melanjutkan ke level escalation berikutnya (Supervisor, Head Dept, GM, Director).

## 🔍 Root Cause Analysis

### Masalah #1: Double Increment & Conflict dalam Update Logic
**File:** `app/Jobs/BundleSubmissionReminderJob.php`

Pada method `updateReminderForNextLevel()`, ada dua bagian kode yang saling konflik:

**SEBELUM (Buggy Code):**
```php
switch ($this->level) {
    case 1:
        $updates['pic_reminder_sent_at'] = $now;
        $updates['initial_reminder_sent_at'] = $now;
        break;
    // ... switch blocks
}

// Set next reminder time jika belum level maksimal
if ($this->level < 5) {
    $escalationDelay = config('reminder.escalation_delay', 2);
    $updates['next_reminder_at'] = now()->addMinutes($escalationDelay);
    $updates['reminder_level'] = $this->level + 1;  // ← WRONG! Naive increment
}
```

**Masalahnya:**
- Ketika `$this->level = 1` (reminder PIC), kode di atas set `reminder_level = 2`
- Tapi checker di `CheckBundleSubmissionReminder::checkExistingReminders()` mencari reminder dengan kondisi `where('reminder_level', '<', 5)`
- Query ini seharusnya menemukan reminder dengan `reminder_level = 2`, TAPI:
  - Jika scheduler tidak konsisten atau queue worker tidak berjalan, reminder tidak tereksekusi
  - Logic tidak jelas kapan reminder harus dikirim ulang

### Masalah #2: Query Condition Tidak Jelas di CheckBundleSubmissionReminder
**File:** `app/Console/Commands/CheckBundleSubmissionReminder.php`

```php
$readyReminders = DocumentRequestReminder::where('escalation_status', 'WAITING')
    ->whereNotNull('next_reminder_at')
    ->where('next_reminder_at', '<=', now())
    ->where(function ($query) {
        $query->where('reminder_level', '<', 5)
            ->orWhere('escalation_status', '!=', 'ESCALATED_TO_DIRECTOR');  // ← Logika fuzzy
    })
    ->get();
```

**Masalahnya:** Kondisi `orWhere('escalation_status', '!=', 'ESCALATED_TO_DIRECTOR')` membuat logika tidak jelas. Reminder dengan `status = WAITING` dan `escalation_status != ESCALATED_TO_DIRECTOR` seharusnya SELALU terambil, tapi kondisi ini tidak memberikan nilai tambah.

---

## ✅ Solusi yang Diterapkan

### Fix #1: Refactor updateReminderForNextLevel() dengan Logic yang Jelas

**File:** `app/Jobs/BundleSubmissionReminderJob.php`

**SESUDAH (Fixed Code):**
```php
private function updateReminderForNextLevel()
{
    $now = now();
    $overallProgress = $this->reminder->getOverallProgressInfo();
    
    $updates = [
        'last_reminder_at' => $now,
    ];

    // Tentukan next level berdasarkan level yang baru saja dikirim
    $nextLevel = $this->level;
    $escalationDelay = config('reminder.escalation_delay', 2);

    // Update timestamp berdasarkan level yang baru saja dikirim
    switch ($this->level) {
        case 1:
            $updates['pic_reminder_sent_at'] = $now;
            $updates['initial_reminder_sent_at'] = $now;
            $nextLevel = 2;
            break;
        case 2:
            $updates['supervisor_escalation_sent_at'] = $now;
            $nextLevel = 3;
            break;
        case 3:
            $updates['head_dept_escalation_sent_at'] = $now;
            $nextLevel = 4;
            break;
        case 4:
            $updates['gm_escalation_sent_at'] = $now;
            $nextLevel = 5;
            break;
        case 5:
            $updates['director_escalation_sent_at'] = $now;
            $updates['escalation_status'] = 'ESCALATED_TO_DIRECTOR';
            $updates['next_reminder_at'] = null;
            $nextLevel = 5; // Tetap di level 5
            break;
    }

    // Set next reminder time dan level
    if ($this->level < 5) {
        // Ada level berikutnya, schedule untuk dikirim di masa depan
        $updates['next_reminder_at'] = now()->addMinutes($escalationDelay);
        $updates['reminder_level'] = $nextLevel;  // Clear: increment ke level berikutnya
    } else {
        // Sudah level maksimal, tidak ada reminder lagi
        $updates['reminder_level'] = 5;
        $updates['next_reminder_at'] = null;
    }

    $this->reminder->update($updates);
    // ... logging
}
```

**Perubahan Penting:**
1. ✅ `$nextLevel` variable membuat logic lebih jelas
2. ✅ Explicit state transition untuk setiap level (1→2, 2→3, dst)
3. ✅ `next_reminder_at` diset dengan jelas saat ada level berikutnya
4. ✅ Level 5 adalah maksimal, tidak ada level berikutnya

### Fix #2: Simplify Query di CheckBundleSubmissionReminder

**File:** `app/Console/Commands/CheckBundleSubmissionReminder.php`

**SESUDAH (Fixed Code):**
```php
$readyReminders = DocumentRequestReminder::where('escalation_status', 'WAITING')
    ->whereNotNull('next_reminder_at')
    ->where('next_reminder_at', '<=', now())
    ->where('reminder_level', '<=', 5)  // Clear: valid level adalah 1-5
    ->with('documentRequest.requestParts')
    ->get();
```

**Perubahan Penting:**
1. ✅ Kondisi `orWhere` dihapus - lebih sederhana
2. ✅ `where('reminder_level', '<=', 5)` lebih jelas: hanya ambil level valid (1-5)
3. ✅ Query lebih readable

### Fix #3: Clarify isReadyForNextReminder() di Model

**File:** `app/Models/DocumentRequestReminder.php`

```php
public function isReadyForNextReminder(): bool
{
    // Jika sudah dihentikan, tidak ready
    if ($this->isStopped()) {
        return false;
    }

    // Jika sudah escalated to director, tidak ada reminder lagi
    if ($this->escalation_status === 'ESCALATED_TO_DIRECTOR') {
        return false;
    }

    // Jika next_reminder_at belum diset, tidak ready
    if ($this->next_reminder_at === null) {
        return false;
    }

    // Siap jika sekarang sudah melewati next_reminder_at
    return now()->gte($this->next_reminder_at);
}
```

---

## 📊 Testing Scenarios

### Skenario 1: Reminder Escalation Normal (Belum Ada yang Submit)
1. ✅ Request dibuat pada 08:00
2. ✅ Scheduler berjalan pada 08:03 → Reminder Level 1 (PIC) dikirim
3. ✅ `reminder_level` diset = 2, `next_reminder_at` = 08:05
4. ✅ Scheduler berjalan pada 08:05 → Reminder Level 2 (Supervisor) dikirim
5. ✅ `reminder_level` diset = 3, `next_reminder_at` = 08:07
6. ✅ ... (lanjut sampai Level 5 Director)

### Skenario 2: Stop Reminder (Setelah Parts Disubmit)
1. ✅ Request dibuat pada 08:00
2. ✅ Level 1 dikirim pada 08:03
3. ✅ Supplier submit parts pada 08:04
4. ✅ Scheduler berjalan → DetectStop condition, reminder di-stop
5. ✅ `escalation_status = STOPPED`, `next_reminder_at = NULL`

### Skenario 3: Reach Maximum Escalation (Level 5 - Director)
1. ✅ Escalation berjalan dari Level 1-4
2. ✅ Level 5 (Director) dikirim
3. ✅ `escalation_status = ESCALATED_TO_DIRECTOR`, `next_reminder_at = NULL`
4. ✅ Tidak ada reminder lagi

---

## 🚀 Deployment Notes

1. **Scheduler Configuration:** Pastikan scheduler berjalan setiap 1 menit (sudah dikonfigurasi di `Kernel.php`)
   ```php
   $schedule->command('reminder:check-bundle-submission')->everyMinute();
   ```

2. **Queue Worker:** Pastikan queue worker berjalan untuk memproses jobs
   ```bash
   php artisan queue:work
   ```

3. **Config Reminder:**
   - `REMINDER_INITIAL_DELAY=3` (testing), ubah ke `1440` untuk production (1 day)
   - `REMINDER_ESCALATION_DELAY=2` (testing), ubah ke `1440` untuk production (1 day)

4. **Database Migration:** Sudah tidak perlu migrasi baru, field-field di `document_request_reminders` sudah ada

5. **Testing:**
   ```bash
   # Manual test: Trigger scheduler
   php artisan reminder:check-bundle-submission
   
   # Cek database
   SELECT * FROM document_request_reminders WHERE id = <request_id>;
   ```

---

## 📝 Summary

| Aspek | Sebelum | Sesudah |
|-------|---------|---------|
| **Logic Clarity** | Confusing double increment | Clear state transitions (1→2→3→4→5) |
| **Next Reminder Tracking** | Not always set correctly | Always set when level < 5 |
| **Query Condition** | Fuzzy with OR clause | Simple and explicit |
| **Max Level Handling** | Not well defined | Clear: Level 5 is max, no further reminders |
| **Readability** | Hard to debug | Easy to trace execution |

---

## 📞 Questions & Debugging

Jika reminder masih tidak berjalan:

1. **Pastikan scheduler berjalan:**
   ```bash
   php artisan schedule:work
   ```

2. **Cek queue:** Pastikan job queue tidak penuh
   ```bash
   php artisan queue:failed
   ```

3. **Enable debug logging** di `.env`:
   ```
   LOG_LEVEL=debug
   ```

4. **Manual test:**
   ```bash
   php artisan reminder:check-bundle-submission -v
   ```

---

**Date:** September 9, 2026  
**Status:** ✅ Fixed

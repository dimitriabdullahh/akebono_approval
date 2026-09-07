# 🔧 Troubleshooting: Email Reminder System

## Masalah: Email Reminder Hanya Sampai Level PIC

### ❌ Gejala
- Email reminder hanya terkirim ke PIC (Level 1)
- Email tidak muncul untuk level lainnya:
  - Level 2: Supervisor
  - Level 3: Head Department
  - Level 4: General Manager
  - Level 5: Director

### 🔍 Root Cause
Kolom email escalation di tabel `suppliers` tidak terisi (`NULL`):
- `supervisor_email`
- `supervisor_name`
- `head_dept_email`
- `head_dept_name`
- `gm_email`
- `gm_name`
- `director_email`
- `director_name`

Ketika system mencoba mengirim email ke level berikutnya, method `getCurrentRecipientEmail()` mengembalikan `null`, sehingga email tidak terkirim.

### ✅ Solusi

#### 1. Check Status Email Supplier

```bash
php artisan supplier:update-escalation-emails --list
```

Output akan menampilkan semua supplier dan status email mereka:
- ✅ Email terisi
- ❌ NOT SET (email belum terisi)

#### 2. Update Semua Supplier (Auto-generate Template)

Untuk development/testing, update semua supplier dengan email template:

```bash
php artisan supplier:update-escalation-emails --all
```

Command ini akan:
- Menggunakan domain dari `pic_email` sebagai base
- Generate email format: `supervisor@domain.com`, `head.dept@domain.com`, dll.
- Generate nama: `Supervisor - [Nama Supplier]`, dll.

#### 3. Update Supplier Spesifik (Interactive)

Untuk production, update supplier satu per satu dengan email yang benar:

```bash
php artisan supplier:update-escalation-emails --supplier-id=1
```

Command ini akan menanyakan setiap email secara interaktif.

#### 4. Verifikasi

Setelah update, verifikasi dengan:

```bash
php artisan supplier:update-escalation-emails --list
```

Pastikan semua supplier memiliki email lengkap (tidak ada ❌ NOT SET).

#### 5. Test Reminder System

Buat test request baru dan monitor email:

```bash
# Create test request
php artisan reminder:test-no-submission --create

# Run scheduler
php artisan schedule:work

# Check Mailpit
# http://localhost:8025
```

Sekarang email seharusnya terkirim ke semua level.

---

## Cara Kerja System

### Flow Email Reminder

```
Request Created (no documents)
    ↓
    [WAIT: initial_delay minutes]
    ↓
Level 1: Email ke PIC ✉️
    ↓
    [WAIT: escalation_delay minutes]
    ↓
Level 2: Email ke Supervisor ✉️
    ↓
    [WAIT: escalation_delay minutes]
    ↓
Level 3: Email ke Head Department ✉️
    ↓
    [WAIT: escalation_delay minutes]
    ↓
Level 4: Email ke General Manager ✉️
    ↓
    [WAIT: escalation_delay minutes]
    ↓
Level 5: Email ke Director ✉️
    ↓
    ✅ Max level reached, STOP
```

### Kondisi Stop

Reminder akan berhenti otomatis jika:
1. Ada dokumen yang di-upload (status: SUBMITTED, APPROVED, atau REVISE)
2. Sudah mencapai level maksimal (Level 5 - Director)

### Log System

Jika email tidak terkirim, check log:

```bash
# Laravel log
tail -f storage/logs/laravel.log

# Search for reminder logs
grep -i "NoSubmissionReminder" storage/logs/laravel.log
```

Log warning akan muncul jika email tidak ditemukan:

```
NoSubmissionReminder: Email recipient tidak ditemukan
{
  "request_id": 1,
  "level": 2,
  "supplier_id": 5
}
```

---

## Production Checklist

Sebelum deploy ke production, pastikan:

1. ✅ Semua supplier memiliki email escalation yang valid
2. ✅ Email di-test untuk memastikan deliverability
3. ✅ Delay diubah ke production mode (1 hari atau sesuai kebijakan)
4. ✅ Cron job sudah setup untuk `php artisan schedule:run`
5. ✅ Mail server configured dengan benar (bukan Mailpit)

### Update Production Delay

Edit `.env`:

```env
# Production Mode: 1 day interval
REMINDER_INITIAL_DELAY=1440    # 1 day in minutes
REMINDER_ESCALATION_DELAY=1440 # 1 day in minutes
```

Kemudian:

```bash
php artisan config:clear
php artisan cache:clear
```

---

## Command Reference

```bash
# List suppliers and their emails
php artisan supplier:update-escalation-emails --list

# Auto-update all suppliers (development)
php artisan supplier:update-escalation-emails --all

# Interactive update single supplier (production)
php artisan supplier:update-escalation-emails --supplier-id=<id>

# Test reminder system
php artisan reminder:test-no-submission --create
php artisan reminder:test-no-submission --status=<id>
php artisan reminder:test-no-submission --list

# Check reminders manually
php artisan reminder:check-no-submission

# Run scheduler (for testing)
php artisan schedule:work
```

---

## Kesimpulan

**Masalah utama**: Email escalation tidak terisi di database supplier.

**Solusi**: Gunakan command `supplier:update-escalation-emails` untuk mengisi email escalation.

**Verification**: Setelah update, test dengan membuat request baru dan monitor Mailpit untuk memastikan semua level email terkirim.

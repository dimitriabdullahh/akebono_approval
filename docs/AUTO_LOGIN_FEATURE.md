# Fitur Auto-Login untuk Supplier via Email

## Deskripsi
Fitur ini memungkinkan supplier untuk mengklik link di email dan langsung login serta diarahkan ke halaman upload dokumen mereka, tanpa perlu memasukkan username dan password secara manual.

## Fitur Utama

### 1. **Signed URL dengan Expiry**
- URL yang dikirim melalui email menggunakan Laravel Signed URL
- Berlaku selama **72 jam (3 hari)** sejak email dikirim
- URL tidak dapat dimodifikasi tanpa terdeteksi (tamper-proof)
- Setelah expired, supplier harus login manual

### 2. **Auto-Login Langsung**
- Supplier tidak perlu memasukkan email dan password
- Sistem otomatis login sebagai user yang sesuai
- Langsung redirect ke halaman request dokumen

### 3. **Security Features**
- ✅ Signature validation - mencegah URL manipulation
- ✅ Role verification - hanya user dengan role 'supplier' yang bisa auto-login
- ✅ Ownership verification - supplier hanya bisa akses request milik mereka sendiri
- ✅ Active user check - hanya user yang active yang bisa login
- ✅ Expiration check - URL otomatis expired setelah 72 jam
- ✅ Logging - semua attempt auto-login dicatat di log

## Implementasi Teknis

### A. File-file yang Terlibat

#### 1. **Controller**
- `app/Http/Controllers/SignedUrlController.php`
  - Method `autoLogin()`: Handle auto-login flow
  - Validasi signature, user, dan ownership
  - Login user dan redirect ke halaman dokumen

- `app/Http/Controllers/SupplierController.php`
  - Method `showDocumentRequest()`: Handle redirect dari auto-login
  - Menampilkan halaman request dokumen supplier

#### 2. **Model**
- `app/Models/User.php`
  - Method `generateSignedLoginUrl($requestId, $expiresInHours = 72)`
  - Generate signed URL dengan expiry custom

#### 3. **Mail Classes**
- `app/Mail/RevisionRequestedMail.php` - Email revisi dokumen
- `app/Mail/RevisionReminderMail.php` - Email reminder revisi
- `app/Mail/RequestLetterMail.php` - Email request dokumen baru

Semua mail class sudah di-update untuk menerima `$supplierUser` dan generate signed URL.

#### 4. **Jobs**
- `app/Jobs/RevisionRequestedNotificationJob.php`
- `app/Jobs/RevisionReminderJob.php`
- `app/Jobs/SendRequestLetterJob.php`

Semua job sudah di-update untuk mencari supplier user yang active dan pass ke mail class.

#### 5. **Email Templates**
- `resources/views/emails/revision-requested.blade.php`
- `resources/views/emails/revision-reminder.blade.php`
- `resources/views/emails/request-letter.blade.php`

Semua template sudah di-update dengan button yang menggunakan signed URL.

#### 6. **Routes**
- `routes/web.php`
  ```php
  Route::get('/auto-login/{userId}/{requestId}', [SignedUrlController::class, 'autoLogin'])
      ->name('auto-login')
      ->middleware('signed');
  ```

### B. Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Email dengan Signed URL dikirim ke Supplier              │
│    URL: /auto-login/{userId}/{requestId}?expires=...&sig=...│
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. Supplier klik link di email                              │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. SignedUrlController::autoLogin() dipanggil               │
│    - Validate signature (middleware 'signed')               │
│    - Validate user exists & is supplier                     │
│    - Validate user owns the document request                │
│    - Validate user is active                                │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. User di-login otomatis (Auth::login($user))              │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 5. Redirect ke halaman request dokumen                      │
│    Route: supplier.document.show                            │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 6. SupplierController::showDocumentRequest() dipanggil      │
│    - Load document request                                  │
│    - Redirect to supplier.request (detail page)             │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 7. Supplier melihat halaman upload dokumen                  │
└─────────────────────────────────────────────────────────────┘
```

### C. Error Handling

Jika terjadi error, supplier akan di-redirect ke halaman login dengan pesan error:

| Error Type | Redirect | Message |
|------------|----------|---------|
| Invalid/Expired URL | Login page | "Link tidak valid atau sudah kadaluarsa. Silakan login secara manual." |
| User not found | Login page | "User tidak ditemukan. Silakan hubungi administrator." |
| Non-supplier user | Login page | "Akses ditolak. Link ini hanya untuk supplier." |
| Request not found | Login page | "Request dokumen tidak ditemukan." |
| Wrong supplier | Login page | "Anda tidak memiliki akses ke request dokumen ini." |
| Exception | Login page | "Terjadi kesalahan. Silakan login secara manual." |

## Testing

### Manual Testing dengan Command
```bash
# Generate signed URL untuk testing
php artisan test:auto-login-url [userId] [requestId]

# Contoh: Generate untuk user ID 7 dan request ID 1
php artisan test:auto-login-url 7 1

# Jika tidak ada parameter, akan otomatis mencari supplier dan request pertama
php artisan test:auto-login-url
```

### Testing Flow
1. Jalankan command di atas untuk generate URL
2. Copy URL yang di-generate
3. Buka browser dalam mode incognito
4. Paste URL di address bar
5. Verify:
   - ✅ User langsung login
   - ✅ Redirect ke halaman request dokumen yang benar
   - ✅ Supplier hanya bisa akses request miliknya

### Test Invalid Scenarios
```bash
# Test expired URL
# Ubah parameter expires di URL menjadi timestamp masa lalu
# Expected: Redirect ke login dengan error message

# Test tampered URL
# Ubah userId atau requestId di URL tanpa update signature
# Expected: Redirect ke login dengan error message

# Test wrong supplier
# Gunakan URL untuk request yang bukan milik supplier
# Expected: Redirect ke login dengan error message
```

## Configuration

### Mengubah Expiry Time
Default expiry adalah 72 jam (3 hari). Untuk mengubah:

```php
// Di Mail class atau Job, saat generate URL:
$loginUrl = $user->generateSignedLoginUrl($requestId, 168); // 168 jam = 7 hari
```

### Environment Variables
Pastikan `APP_URL` di `.env` sesuai dengan URL aplikasi Anda:
```env
APP_URL=http://localhost
# atau untuk production
APP_URL=https://approval.akebono.com
```

## Email Behavior

### Saat Supplier User Ada
```
┌────────────────────────────────────────┐
│ 🔗 Login & Upload Dokumen Sekarang    │  ← Button dengan signed URL
└────────────────────────────────────────┘

Langkah-langkah:
1. Klik tombol di atas
2. Anda akan langsung masuk ke halaman upload
3. Upload dokumen yang sudah diperbaiki
```

### Saat Supplier User Tidak Ada (Fallback)
```
┌────────────────────────────────────────┐
│ 🔗 Login ke Sistem                     │  ← Button ke /login
└────────────────────────────────────────┘

Langkah-langkah:
1. Login ke sistem dengan email: supplier@example.com
2. Buka dashboard
3. Cari request dokumen
4. Upload dokumen
```

## Security Considerations

### 1. **URL Tampering**
- Signed URL mencegah parameter manipulation
- Perubahan sekecil apapun akan invalid signature

### 2. **URL Sharing**
- URL bersifat personal untuk supplier tertentu
- Jika supplier lain coba akses, akan ditolak
- Expired setelah 72 jam

### 3. **Session Management**
- Auto-login create new session
- Session expire mengikuti konfigurasi Laravel default
- Supplier bisa logout kapan saja

### 4. **Logging**
- Semua auto-login attempt (success/fail) dicatat di log
- Format log:
  ```
  [timestamp] INFO: Supplier auto-login successful
    user_id: 7
    supplier_id: 4
    request_id: 1
  ```

## Maintenance

### Monitoring
- Check notification logs: `NotificationLog` table
- Check Laravel logs: `storage/logs/laravel.log`
- Monitor email delivery rates

### Troubleshooting

**Problem: URL tidak bekerja**
- Check APP_URL di .env
- Check signature middleware di route
- Check user exists dan is_active
- Check log untuk error detail

**Problem: Redirect loop**
- Check middleware order
- Check route name sudah sesuai
- Verify SupplierController method exists

**Problem: Email tidak ada link/button**
- Check supplier user exists dan active
- Check Mail class pass $supplierUser
- Check email template menggunakan $loginUrl

## Future Enhancements
1. ✨ Custom expiry per email type
2. ✨ Track link click analytics
3. ✨ Multi-device login notification
4. ✨ Rate limiting untuk auto-login attempts
5. ✨ One-time use URL (invalidate after first use)

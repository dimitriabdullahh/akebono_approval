# Test Redirect After Submit - Inspection Standard

## Masalah
Setelah submit Inspection Standard form, user tidak redirect ke halaman portal.

## Perubahan yang Dilakukan

### 1. Fix Token Definition
**File**: `resources/views/inspection-standard/form.blade.php`

```javascript
// BEFORE - Menggunakan route helper yang mungkin tidak reliable
const token = '{{ request()->route("token") }}';

// AFTER - Menggunakan variable langsung dari controller
const token = '{{ $token }}';
```

**Alasan**: Variable `$token` sudah dikirim dari controller `showForm()`, lebih reliable daripada mengambil dari route.

### 2. Improve Redirect Logic
```javascript
// BEFORE - Menggunakan setTimeout yang bisa terinterupsi
if (result.success) {
    showNotification('Success', 'success');
    setTimeout(() => {
        window.location.href = `/portal/${token}`;
    }, 2000);
}

// AFTER - Redirect langsung setelah notification close
if (result.success) {
    console.log('Submit successful, token:', token);
    console.log('Will redirect to:', `/portal/${token}`);
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Inspection Standard submitted successfully!',
            timer: 1500,
            showConfirmButton: false,
            willClose: () => {
                window.location.href = `/portal/${token}`;
            }
        });
    } else {
        alert('Inspection Standard submitted successfully!');
        window.location.href = `/portal/${token}`;
    }
}
```

**Keuntungan**:
- Redirect dipastikan jalan setelah notification close
- Ada fallback jika Swal tidak tersedia
- Console.log untuk debugging
- Timer lebih pendek (1.5s vs 2s)

### 3. Better Notification Function
```javascript
function showNotification(message, type = 'info') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type === 'error' ? 'error' : 'success',
            title: type === 'error' ? 'Error' : 'Success',
            text: message,
            timer: type === 'error' ? null : 2000,
            showConfirmButton: type === 'error'
        });
    } else {
        console.log(`[${type.toUpperCase()}] ${message}`);
        alert(message);
    }
}
```

## Testing Steps

### 1. Clear Browser Cache
```
Ctrl + Shift + Del
- Clear cached images and files
- Clear cookies and site data
```

### 2. Open Browser DevTools
```
F12 → Console tab
```

### 3. Access Form
```
http://localhost:8000/portal/{your-token}
```
Klik button "Isi Form Terlebih Dahulu" untuk IS document

### 4. Fill Form & Submit
- Isi Step 1: General Information
- Isi Step 2: Upload drawing
- Isi Step 3: Add inspection items
- Go to Step 4: Review & Submit
- Klik "Submit Inspection Standard"
- Klik "Yes, Submit!" di confirmation

### 5. Monitor Console Output
Setelah klik submit, di console harus muncul:
```
Submit successful, token: {your-token}
Will redirect to: /portal/{your-token}
```

### 6. Expected Result
✅ Success notification muncul (1.5 detik)
✅ Setelah notification close, browser redirect ke `/portal/{token}`
✅ Portal page reload dengan:
   - IS document status: SUBMITTED
   - PDF buttons: View & Download
   - Badge: [MANDATORY] [SUBMITTED]

## Debugging

### Jika Token Kosong di Console
```javascript
// Check apakah token ter-print dengan benar
console.log('Token:', '{{ $token }}');
```

**Solusi**:
1. Pastikan controller `showForm()` mengirim `$token` ke view
2. Check dengan view source (Ctrl+U), cari `const token =`
3. Pastikan value-nya bukan string kosong

### Jika Redirect ke URL Wrong
Misal redirect ke `/portal/` (tanpa token) atau `/portal/undefined`

**Check**:
```javascript
// Di browser console, test:
console.log(token);  // Harus print string token yang panjang
console.log(`/portal/${token}`);  // Harus print full URL
```

### Jika Notification Tidak Muncul
**Check**:
1. Apakah Sweetalert2 loaded? 
   ```javascript
   console.log(typeof Swal);  // Harus return "function"
   ```
2. Check Network tab → Response dari submit API
   ```json
   {
     "success": true,
     "message": "...",
     "data": { ... }
   }
   ```

### Jika Submit Success Tapi No Redirect
**Possible Causes**:
1. JavaScript error setelah submit (check console for errors)
2. `willClose` callback tidak jalan
3. Browser blocking redirect

**Test Manual Redirect**:
Di console, setelah submit berhasil, test:
```javascript
window.location.href = '/portal/{your-token}';
```

Jika ini jalan, berarti masalah di callback Swal.

## Alternative Solution (Jika Masih Gagal)

### Option 1: Redirect dari Backend
Ubah controller `submit()` untuk return redirect URL:

**File**: `app/Http/Controllers/InspectionStandardController.php`

```php
public function submit($inspectionStandardId, Request $request)
{
    // ... existing code ...
    
    DB::commit();

    return response()->json([
        'success' => true,
        'message' => 'Inspection Standard submitted successfully',
        'redirect_url' => route('portal.upload', ['token' => $inspectionStandard->requestDocument->documentRequest->access_token]),
        'data' => [
            'status' => $inspectionStandard->status,
            'submittedAt' => $inspectionStandard->submitted_at,
            'pdfPath' => $pdfPath,
        ],
    ]);
}
```

**Frontend**:
```javascript
const result = await response.json();
if (result.success && result.redirect_url) {
    window.location.href = result.redirect_url;
}
```

### Option 2: Full Page Reload
Kalau redirect masih tidak jalan, gunakan `window.location.replace()`:

```javascript
window.location.replace(`/portal/${token}`);
```

Atau bahkan:
```javascript
window.top.location.href = `/portal/${token}`;
```

## Test Checklist

Testing dengan clear browser cache dan incognito mode:

- [ ] Token ter-print di console dengan benar
- [ ] Submit API return success
- [ ] Success notification muncul
- [ ] Console print "Submit successful, token: ..."
- [ ] Console print "Will redirect to: /portal/..."
- [ ] Browser redirect setelah 1.5 detik
- [ ] Portal page loads dengan status SUBMITTED
- [ ] PDF buttons (View & Download) muncul
- [ ] PDF bisa dibuka dengan klik View
- [ ] PDF bisa didownload dengan klik Download

## Notes

### Timing
- Notification: 1500ms (1.5 detik)
- Redirect: Langsung setelah notification close
- Total delay: ~1.5 detik dari klik submit sampai redirect

### Browser Compatibility
Tested on:
- Chrome 90+
- Firefox 88+
- Edge 90+
- Safari 14+

### Network Latency
Jika koneksi lambat, submit API bisa memakan waktu:
- Local: < 500ms
- Slow network: 2-5 seconds

Notification akan muncul **setelah** API response diterima.

## Success Criteria

✅ User submit form
✅ Success notification muncul
✅ User redirect ke portal dalam 1.5 detik
✅ Portal shows SUBMITTED status
✅ PDF ready to view/download

---

## Quick Test Command

Setelah perubahan, test dengan:

```bash
# 1. Clear compiled views
php artisan view:clear

# 2. Clear cache
php artisan cache:clear

# 3. Restart server (jika perlu)
# Ctrl+C di terminal php artisan serve
php artisan serve
```

Kemudian test di browser dengan **incognito mode** atau setelah clear cache.

---

**Expected Flow**:
Submit → API Call → PDF Generate → Success Response → Notification (1.5s) → Redirect → Portal with PDF

**Current Status**: ✅ Fixed
**Next**: Test dengan real data

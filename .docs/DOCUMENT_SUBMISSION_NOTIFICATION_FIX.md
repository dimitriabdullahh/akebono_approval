# Document Submission Notification Fix

## Masalah yang Diperbaiki

Sebelumnya, ketika supplier submit dokumen form-based (Inspection Standard, Packaging Standard, Part Submission Warrant), **tidak ada notifikasi** yang dikirim ke tim internal (QA, PPC, Purchasing) dan **dokumen tidak muncul** di dashboard mereka untuk direview.

### Root Cause

Ketiga controller dokumen form-based tidak memanggil `NotificationService::notifyDocumentsSubmitted()` setelah status diubah ke SUBMITTED:

1. ✗ **InspectionStandardController::submit()** - Ada TODO comment tapi tidak diimplementasikan
2. ✗ **PackagingStandardController::submit()** - Tidak ada notifikasi
3. ✗ **PartSubmissionWarrantController::submit()** - Tidak ada notifikasi

Berbeda dengan **PortalController** yang menangani upload file PDF, yang sudah memanggil notifikasi dengan benar.

## Solusi yang Diimplementasikan

### 1. Tambahkan NotificationService ke Constructor

Semua tiga controller sekarang inject `NotificationService`:

```php
private NotificationService $notificationService;

public function __construct(NotificationService $notificationService)
{
    $this->notificationService = $notificationService;
}
```

### 2. Panggil Notifikasi Setelah Submit

Setelah RequestDocument status diupdate ke SUBMITTED, sistem sekarang:

```php
// Update request document status
$requestDocument->update([
    'status' => 'SUBMITTED',
    'file_path' => $pdfPath, // or 'form'
]);
$requestDocument->refresh(); // Force reload untuk memastikan data fresh

// Send notification to QA, PPC, and Purchasing
$documentRequest = $requestDocument->documentRequest;
if ($documentRequest) {
    try {
        $this->notificationService->notifyDocumentsSubmitted($documentRequest);
        
        // Mark bahwa notifikasi sudah dikirim
        if (is_null($documentRequest->documents_submitted_notified_at)) {
            $documentRequest->update([
                'documents_submitted_notified_at' => now()
            ]);
        }
        
        Log::info('Notification sent successfully');
    } catch (\Exception $notifError) {
        Log::error('Failed to send notification', [
            'error' => $notifError->getMessage()
        ]);
        // Submission tetap berhasil meskipun notifikasi gagal
    }
}
```

### 3. Error Handling

Notifikasi dibungkus dalam try-catch sehingga:
- ✓ Jika notifikasi gagal, submission tetap berhasil
- ✓ Error di-log untuk debugging
- ✓ User tidak mendapat error message yang membingungkan

## Files yang Dimodifikasi

1. `app/Http/Controllers/InspectionStandardController.php`
   - Tambah inject NotificationService di constructor
   - Panggil notifikasi setelah submit dengan logging

2. `app/Http/Controllers/PackagingStandardController.php`
   - Tambah inject NotificationService di constructor
   - Panggil notifikasi setelah submit dengan logging

3. `app/Http/Controllers/PartSubmissionWarrantController.php`
   - Tambah inject NotificationService di constructor
   - Panggil notifikasi setelah submit dengan logging

4. `app/Console/Commands/TestDocumentNotification.php` (NEW)
   - Command untuk testing notifikasi manual

## Cara Kerja Notifikasi

### 1. NotificationService::notifyDocumentsSubmitted()

Service ini dispatch `DocumentsSubmittedNotificationJob` yang:

1. Query semua user dengan role:
   - `admin_qa`
   - `admin_purchasing`
   - `admin_ppc`

2. Kirim email `DocumentsSubmittedMail` ke setiap admin

3. Log notifikasi ke tabel `notification_logs` dengan status:
   - `SENT` - berhasil
   - `FAILED` - gagal dengan error message

### 2. Email Queue

Email dikirim via Laravel Queue system:
- Job: `DocumentsSubmittedNotificationJob`
- Retry: 3 kali dengan backoff 60 detik
- Failure: dicatat ke `notification_logs`

### 3. Dashboard Employee

Employee (QA, PPC, Purchasing) melihat dokumen baru di:

**ReviewController::index()** → `getPendingDocumentsForReview()`:

```php
RequestDocument::where('status', 'SUBMITTED')
    ->whereHas('documentType', function ($q) use ($scope) {
        $q->whereJsonContains('scope', $scope);
    })
    ->whereDoesntHave('approvals', function ($q) use ($scope) {
        $q->where('scope', $scope)
          ->where('status', 'APPROVED');
    })
```

Filter berdasarkan:
- Status = SUBMITTED
- Document type scope sesuai role user (QA/PPC/PURCHASING)
- Belum di-approve oleh scope tersebut

## Document Type Scope

Dari seeder (`DocumentTypeSeeder.php`):

| Document | Code | Scope |
|----------|------|-------|
| Inspection Standard | IS | ['QA'] |
| Packaging Standard | PACKAGING_STANDARD | ['PURCHASING', 'PPC', 'QA'] |
| Part Submission Warrant | PSW | ['PURCHASING', 'QA'] |

## Testing

### 1. Test via Command

```bash
php artisan test:document-notification {request_id}
```

Command ini akan:
1. Load document request dengan semua dokumen
2. Tampilkan status setiap dokumen
3. Konfirmasi sebelum kirim notifikasi
4. Kirim notifikasi ke QA, PPC, dan Purchasing
5. Log hasil ke notification_logs

### 2. Manual Test Flow

1. **Login sebagai Supplier**
2. **Submit dokumen** (IS/PS/PSW) via form
3. **Cek notification_logs** table:
   ```sql
   SELECT * FROM notification_logs 
   WHERE document_request_id = ? 
   ORDER BY sent_at DESC;
   ```
4. **Login sebagai QA/PPC/Purchasing**
5. **Buka dashboard** - dokumen harus muncul di "Pending Review"

### 3. Log Monitoring

Setelah submit, check log file untuk:

```
[IS Submit] Sending notification to reviewers
[IS Submit] Notification sent successfully

[PS Submit] Sending notification to reviewers
[PS Submit] Notification sent successfully

[PSW Submit] Sending notification to reviewers
[PSW Submit] Notification sent successfully
```

## Troubleshooting

### Dokumen Tidak Muncul di Dashboard Employee

**Check 1**: Verifikasi status dokumen
```sql
SELECT id, status, file_path, submitted_at 
FROM request_documents 
WHERE id = ?;
```

**Check 2**: Verifikasi document type scope
```sql
SELECT dt.code, dt.name, dt.scope 
FROM document_types dt
JOIN request_documents rd ON rd.document_type_id = dt.id
WHERE rd.id = ?;
```

**Check 3**: Verifikasi employee role
```sql
SELECT id, name, email, role 
FROM users 
WHERE role IN ('admin_qa', 'admin_purchasing', 'admin_ppc');
```

### Notifikasi Tidak Terkirim

**Check 1**: Verifikasi email queue
```bash
php artisan queue:work --once
```

**Check 2**: Check notification logs
```sql
SELECT * FROM notification_logs 
WHERE document_request_id = ? 
AND type = 'documents_submitted'
ORDER BY sent_at DESC;
```

**Check 3**: Check queue failed jobs
```sql
SELECT * FROM failed_jobs 
WHERE payload LIKE '%DocumentsSubmittedNotificationJob%'
ORDER BY failed_at DESC;
```

### documents_submitted_notified_at Tidak Update

Pastikan field ada di migration:
```sql
SHOW COLUMNS FROM document_requests LIKE 'documents_submitted_notified_at';
```

Jika tidak ada, buat migration:
```bash
php artisan make:migration add_documents_submitted_notified_at_to_document_requests
```

## Best Practices

### 1. Notification Timing

Notifikasi dikirim **setelah DB commit** tapi **dalam exception handler** agar:
- ✓ Data sudah pasti tersimpan
- ✓ Submission tidak gagal jika notification error
- ✓ Error di-log untuk debugging

### 2. Refresh After Update

```php
$requestDocument->refresh();
```

Memastikan data yang di-load fresh dari database, menghindari:
- Cache issue di Eloquent relationship
- Status tidak sinkron antara model dan DB

### 3. Idempotent Notification

```php
if (is_null($documentRequest->documents_submitted_notified_at)) {
    // Send notification only once
}
```

Menghindari duplicate notification jika:
- User submit ulang
- Script dijalankan multiple kali
- Retry mechanism

### 4. Logging

Setiap step di-log untuk audit trail:
- Start submission
- Status update
- Notification trigger
- Success/failure
- Error details

## Migration Path

Jika ada dokumen lama yang sudah SUBMITTED tapi belum dinotifikasi:

```php
php artisan tinker

// Get all SUBMITTED documents without notification
$requests = \App\Models\DocumentRequest::whereHas('requestDocuments', function($q) {
    $q->where('status', 'SUBMITTED');
})->whereNull('documents_submitted_notified_at')->get();

// Send notification for each
$notificationService = app(\App\Services\NotificationService::class);
foreach ($requests as $request) {
    $notificationService->notifyDocumentsSubmitted($request);
    $request->update(['documents_submitted_notified_at' => now()]);
}
```

## Monitoring

Dashboard admin should show:
1. Total pending documents by scope
2. Recent submissions (last 24h)
3. Notification delivery rate
4. Failed notifications count

Query untuk monitoring:

```sql
-- Pending documents by scope
SELECT 
    dt.code,
    JSON_EXTRACT(dt.scope, '$') as scope,
    COUNT(*) as pending_count
FROM request_documents rd
JOIN document_types dt ON rd.document_type_id = dt.id
WHERE rd.status = 'SUBMITTED'
GROUP BY dt.code;

-- Notification success rate (last 7 days)
SELECT 
    DATE(sent_at) as date,
    status,
    COUNT(*) as count
FROM notification_logs
WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
AND type = 'documents_submitted'
GROUP BY DATE(sent_at), status;
```

## Conclusion

Fix ini memastikan bahwa:
✓ Semua dokumen form-based (IS, PS, PSW) trigger notifikasi setelah submit
✓ QA, PPC, dan Purchasing mendapat email notification
✓ Dokumen muncul di dashboard employee untuk review
✓ System lebih reliable dengan proper error handling dan logging
✓ Notifikasi tidak duplicate dengan flag `documents_submitted_notified_at`

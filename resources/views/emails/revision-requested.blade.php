@component('mail::message')
# 📝 Revisi Dokumen Diperlukan

Kepada Yth,  
**{{ $supplier->pic_name }}**  
{{ $supplier->name }}

---

Kami informasikan bahwa salah satu dokumen yang Anda submit memerlukan **revisi** sebelum dapat disetujui.

## 📋 Detail Request

**Model:** {{ $request->model }}

### 🔧 Part Information

@if($request->requestParts && $request->requestParts->count() > 0)
| No | Part Name | Part Number |
|:--:|:----------|:------------|
@foreach($request->requestParts as $index => $part)
| {{ $index + 1 }} | {{ $part->part_name }} | **{{ $part->part_number }}** |
@endforeach
@endif

---

## ⚠️ Dokumen yang Memerlukan Revisi

**Jenis Dokumen:** {{ $requestDocument->documentType->name }}

@if($requestDocument->documentType->scope)
**Scope Review:** {{ implode(', ', $requestDocument->documentType->scope) }}
@endif

### 📌 Catatan Revisi

@component('mail::panel')
{{ $requestDocument->revision_note ?? 'Tidak ada catatan khusus' }}
@endcomponent

### ⏰ Batas Waktu Revisi

**Deadline:** {{ $requestDocument->revision_deadline_at?->format('d F Y, H:i') ?? '-' }} WIB  
**Sisa Waktu:** {{ $requestDocument->revision_deadline_at?->diffForHumans() ?? '-' }}

---

## 🚨 PENTING

Mohon segera perbaiki dan upload ulang dokumen sebelum deadline. Keterlambatan dapat menghambat proses approval dan berpotensi menunda pengiriman produk Anda.

@component('mail::button', ['url' => $loginUrl ?? config('app.url') . '/login', 'color' => 'error'])
🔗 Login & Upload Revisi Sekarang
@endcomponent

---

## ✅ Langkah-langkah Upload Revisi

@if($loginUrl)
1. **Klik Tombol di Atas** - Anda akan langsung masuk ke halaman upload dokumen
2. **Perbaiki Dokumen** - Sesuaikan dokumen Anda dengan catatan revisi yang diberikan
3. **Upload Ulang** - Unggah file yang sudah diperbaiki (format: PDF, JPG, PNG | max 10MB)
4. **Verifikasi** - Pastikan file berhasil terupload dan status berubah menjadi **"SUBMITTED"**
@else
1. **Login ke Sistem** - Klik tombol di atas untuk login menggunakan email: **{{ $supplier->pic_email }}**
2. **Buka Dashboard** - Setelah login, Anda akan diarahkan ke dashboard supplier
3. **Identifikasi Dokumen** - Cari dokumen dengan status **"REVISE"** (berwarna merah/oranye)
4. **Perbaiki Dokumen** - Sesuaikan dokumen Anda dengan catatan revisi yang diberikan
5. **Upload Ulang** - Unggah file yang sudah diperbaiki (format: PDF, JPG, PNG | max 10MB)
6. **Verifikasi** - Pastikan file berhasil terupload dan status berubah menjadi **"SUBMITTED"**
@endif

---

Jika Anda mengalami kesulitan atau ada pertanyaan terkait catatan revisi, silakan hubungi tim QA kami.

Terima kasih atas perhatian dan kerjasama Anda.

Salam,  
**{{ config('app.name') }}**  
Quality Assurance Division

---

<small style="color: #666;">
📧 Email ini adalah notifikasi otomatis dari Sistem Approval Dokumen Supplier.  
Mohon jangan membalas email ini secara langsung.
</small>
@endcomponent

@component('mail::message')

# Persetujuan Dokumen Diperlukan

Halo **{{ $recipient->name }}** (NPK: {{ $recipient->npk }}),

Anda diminta untuk menyetujui dokumen berikut sebagai **{{ ucfirst($division) }}**:

## Informasi Dokumen

| Item | Nilai |
|------|-------|
| **Jenis Dokumen** | {{ $requestDocument->documentType->name ?? '-' }} |
| **Supplier** | {{ $requestDocument->documentRequest->supplier->name ?? '-' }} |
| **Part No** | {{ $requestDocument->documentRequest->part_no ?? '-' }} |
| **Part Name** | {{ $requestDocument->documentRequest->part_name ?? '-' }} |
| **Tanggal Submit** | {{ $requestDocument->submitted_at?->format('d M Y H:i') ?? '-' }} |
| **Status** | {{ $requestDocument->status }} |

## Aksi yang Diperlukan

Silakan klik tombol di bawah untuk membuka halaman persetujuan:

@component('mail::button', ['url' => $approvalUrl, 'color' => 'success'])
Buka Halaman Approval
@endcomponent

Di halaman tersebut, Anda dapat:
- **Melihat pratinjau** dokumen dalam format PDF
- **Menyetujui** dokumen (e-sign Anda akan otomatis terbubuh di PDF)
- **Meminta revisi** dari supplier (jika diperlukan)

## Catatan Penting

- **Link berlaku selama {{ $expiresAt }} hari** dari tanggal email ini dikirim
- **Link hanya untuk Anda** - jangan bagikan ke orang lain
- Aksi approve hanya memerlukan satu kali klik, lalu konfirmasi di modal
- Setelah Anda approve, divisi lain juga harus approve sebelum dokumen benar-benar final

Terima kasih atas perhatian Anda.

---

**{{ config('approval_blast.mail_from_name', 'AAIJ Approval System') }}**

@endcomponent

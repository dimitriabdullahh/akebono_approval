# IMPLEMENTATION DOCUMENT
## Sistem Approval Dokumen Supplier
### PT Akebono Astra Indonesia

---

### Informasi Dokumen
| Atribut | Keterangan |
| :--- | :--- |
| **Nama Dokumen** | Implementation Document — Sistem Approval Dokumen Supplier |
| **Perusahaan** | PT Akebono Astra Indonesia |
| **Dokumen Rujukan** | Business Process Document (BPD) v1; Product Requirement Document (PRD) v4 — Sistem Approval Dokumen Supplier |
| **Versi** | 1.1 (Enhanced with Modern UI/UX Guidelines) |
| **Status** | Final Draft untuk Tim Pengembang, UI/UX Designer & Tim Bisnis |

---

## Daftar Isi
1. [Pendahuluan](#1-pendahuluan)
   - 1.1 [Dokumen Rujukan](#11-dokumen-rujukan)
2. [Ruang Lingkup Implementasi](#2-ruang-lingkup-implementasi)
   - 2.1 [Dalam Lingkup (In Scope)](#21-dalam-lingkup-in-scope)
   - 2.2 [Di Luar Lingkup (Out of Scope)](#22-di-luar-lingkup-out-of-scope)
3. [Arsitektur Sistem](#3-arsitektur-sistem)
   - 3.1 [Tech Stack](#31-tech-stack)
   - 3.2 [Lapisan Arsitektur](#32-lapisan-arsitektur)
4. [Rancangan Basis Data](#4-rancangan-basis-data)
   - 4.1 [Daftar Entitas Utama](#41-daftar-entitas-utama)
   - 4.2 [Catatan Rancangan Database](#42-catatan-rancangan-database)
5. [Rancangan Peran & Hak Akses (RBAC)](#5-rancangan-peran--hak-akses-rbac)
6. [Rancangan Modul & Pemetaan Kebutuhan Fungsional](#6-rancangan-modul--pemetaan-kebutuhan-fungsional)
   - 6.1 [Modul Data Request & Surat Request](#61-modul-data-request--surat-request)
   - 6.2 [Modul Upload Dokumen Supplier](#62-modul-upload-dokumen-supplier)
   - 6.3 [Modul Review & Approval per Dokumen](#63-modul-review--approval-per-dokumen)
   - 6.4 [Modul Deadline & Revisi](#64-modul-deadline--revisi)
   - 6.5 [Modul Notifikasi](#65-modul-notifikasi)
   - 6.6 [Modul Status, Riwayat, Download, & Dashboard](#66-modul-status-riwayat-download--dashboard)
7. [Panduan Desain & Standar UI/UX Modern](#7-panduan-desain--standar-uiux-modern)
   - 7.1 [Prinsip Desain & Tata Letak Modern](#71-prinsip-desain--tata-letak-modern)
   - 7.2 [Sistem Warna (Color Palette) & Semantic Tokens](#72-sistem-warna-color-palette--semantic-tokens)
   - 7.3 [Tipografi & Hierarki Visual](#73-tipografi--hierarki-visual)
   - 7.4 [Panduan Komponen Antarmuka (Component Guidelines)](#74-panduan-komponen-antarmuka-component-guidelines)
   - 7.5 [Interaksi & Feedback Pengguna (Micro-interactions)](#75-interaksi--feedback-pengguna-micro-interactions)
   - 7.6 [Pola UX Spesifik per Halaman/Fitur](#76-pola-ux-spesifik-per-halamanfitur)
8. [Alur Implementasi Teknis per Tahap Proses Bisnis](#8-alur-implementasi-teknis-per-tahap-proses-bisnis)
9. [Alur Status Dokumen (State Machine)](#9-alur-status-dokumen-state-machine)
10. [Rancangan Notifikasi & Job Terjadwal](#10-rancangan-notifikasi--job-terjadwal)
11. [Rancangan Endpoint / Rute Utama](#11-rancangan-endpoint--rute-utama)
12. [Rencana Pengujian (Test Plan)](#12-rencana-pengujian-test-plan)
13. [Risiko Teknis & Mitigasi](#13-risiko-teknis--mitigasi)
14. [Lampiran](#14-lampiran)
    - 14.1 [Diagram Alur Proses](#141-diagram-alur-proses)
    - 14.2 [Daftar Dokumen Mandatory & Optional](#142-daftar-dokumen-mandatory--optional)

---

## 1. Pendahuluan
Implementation Document (ID) ini merupakan turunan teknis dari *Business Process Document* (BPD) dan *Product Requirement Document* (PRD) Sistem Approval Dokumen Supplier PT Akebono Astra Indonesia. Dokumen ini menerjemahkan kebutuhan produk (fungsional & non-fungsional) dan alur proses bisnis *end-to-end* menjadi rancangan teknis konkret yang siap dikembangkan oleh tim developer dan diimplementasikan oleh UI/UX designer. Dokumen ini mencakup arsitektur, struktur data, pembagian modul, alur implementasi teknis, rancangan endpoint API, standar UI/UX modern dan ramah pengguna (*user-friendly*), serta rencana pengujian komprehensif.

Tujuan dokumen ini secara khusus:
1. Menjembatani kebutuhan bisnis (BPD) dan kebutuhan produk (PRD) dengan rancangan teknis dan UI/UX yang modern.
2. Menetapkan arsitektur sistem, skema basis data, dan pembagian modul berdasarkan Kebutuhan Fungsional (FR-01 s.d. FR-18).
3. Memberikan panduan desain antarmuka modern yang meminimalkan beban kognitif pengguna (*cognitive load*), memperjelas alur upload/review, dan memberikan visual feedback yang intuitif.
4. Memetakan setiap tahap proses bisnis pada BPD ke komponen teknis (controller, job, queue, notification).
5. Menjadi acuan terpadu bagi tim pengembang dalam perencanaan sprint, implementasi kode, pengujian, dan deployment.

### 1.1 Dokumen Rujukan
| No. | Nama Dokumen | Keterangan |
| :---: | :--- | :--- |
| 1 | **Business Process Document (BPD)** | Menjabarkan proses bisnis end-to-end, aktor, RACI Matrix, dan business rules. |
| 2 | **Product Requirement Document (PRD) v4** | Menjabarkan kebutuhan produk, kebutuhan fungsional & non-fungsional, tech stack, dan daftar dokumen master. |

---

## 2. Ruang Lingkup Implementasi

### 2.1 Dalam Lingkup (In Scope)
* **Modul Data Request Supplier:** Pembuatan request oleh Admin QA (Model, Part Name, Part Number, Due Date, Nama Supplier).
* **Otomatisasi Surat Request:** Pengiriman email otomatis berisi rincian request dan secure token link portal untuk supplier.
* **Portal Supplier Interaktif:** Akses aman tanpa registrasi rumit (token-based), antarmuka upload dokumen drag-and-drop, validasi format, dan tracking status per dokumen.
* **Modul Review & Approval Granular:** Review dan approval independen per dokumen (bukan per batch) untuk Admin Purchasing/QA/PPC sesuai lingkup kewenangan.
* **Mekanisme Deadline Revisi:** Konfigurasi durasi (default 2 hari kalender), countdown timer visual, dan scheduler pengingat otomatis.
* **Otomatisasi Notifikasi Email Multi-Titik:**
  1. Surat Request awal ke Supplier.
  2. Notifikasi dokumen terkirim lengkap ke Admin terkait.
  3. Pengingat revisi otomatis (H-2 deadline) ke Supplier.
  4. Notifikasi status final (seluruh dokumen APPROVED) ke Supplier & Admin.
* **Unduh Dokumen:** Fitur unduh berkas approved dalam format PDF tunggal atau bulk ZIP.
* **Audit Trail & Logging:** Pencatatan komprehensif riwayat perubahan status dokumen (SUBMITTED/REVISE/APPROVED) dan riwayat pengiriman notifikasi.
* **Dashboard Monitoring Purchasing:** Visualisasi metrik approval, tabel pemantauan lintas supplier, dan klasifikasi otomatis 3 kategori proyek.
* **Role-Based Access Control (RBAC):** Otorisasi ketat untuk 1 peran eksternal (Supplier) dan 3 peran internal (Purchasing, QA, PPC).

### 2.2 Di Luar Lingkup (Out of Scope)
* Integrasi otomatis API langsung dengan sistem ERP / SAP / Purchase Order (PO) Akebono (fase lanjutan).
* Penerapan tanda tangan digital bersertifikasi (*digital certificate signature* / e-Sign Peruri) pada dokumen PDF (fase lanjutan).
* Notifikasi instan melalui kanal SMS atau WhatsApp Gateway.

---

## 3. Arsitektur Sistem
Sistem dibangun menggunakan arsitektur monolitik terstruktur berbasis MVC (*Model-View-Controller*) dengan Laravel. Sistem memisahkan otentikasi dan tampilan menjadi dua portal utama: **Portal Eksternal Supplier** (akses via tokenized secure link) dan **Panel Admin Internal** (akses via multi-role authentication untuk QA, Purchasing, dan PPC).

```
+---------------------------------------------------------------------------------------+
|                                    PRESENTATION LAYER                                 |
|  [Supplier Portal (Responsive Blade/BS4.5)]    [Admin Console (Purchasing/QA/PPC)]    |
+-------------------------------------------+-------------------------------------------+
                                            |
+-------------------------------------------v-------------------------------------------+
|                                    APPLICATION LAYER                                  |
|   +-------------------------------------------------------------------------------+   |
|   | Laravel 9 Controllers & Services:                                             |   |
|   | - RequestService        - ReviewApprovalService       - DeadlineService       |   |
|   | - DocumentUploadService - AuditTrailService           - DashboardQueryService |   |
|   +-------------------------------------------------------------------------------+   |
|   | Middleware: Auth, RoleMiddleware, ValidatePortalToken, CheckReviewScope       |   |
+---------------------+-------------------------------------+---------------------------+
                      |                                     |
+---------------------v-----------------+     +-------------v---------------------------+
|      NOTIFICATION & ASYNC LAYER       |     |               DATA LAYER                |
| - Laravel Queue (Redis / Database)    |     | - PostgreSQL (Master, Transaksi, Log)   |
| - Mailables (HTML Email Templates)    |     | - Laravel Filesystem (Storage PDF/Docs) |
| - Task Scheduling (Cron - Harian)     |     +-----------------------------------------+
+---------------------------------------+
```

### 3.1 Tech Stack
| Komponen | Teknologi | Fungsi dalam Implementasi |
| :--- | :--- | :--- |
| **Backend Framework** | Laravel 9 (PHP 8.1+) | Core business logic, routing, RBAC policies, job queue, task scheduler, mail handling. |
| **Frontend / UI** | Bootstrap 4.5 + Modern CSS Theme | Antarmuka modern, responsif, komponen card-based, clean dashboard, micro-interactions. |
| **Icons & Assets** | Tabler Icons / Bootstrap Icons | Ikonografi modern, konsisten, dan ringan. |
| **Database** | PostgreSQL 13+ | Penyimpanan relasional berintegritas tinggi, JSONB support, indexing cepat. |
| **Queue Worker** | Laravel Queue (Redis / DB) | Eksekusi background job pengiriman email secara asynchronous agar web request tetap responsif (<200ms). |
| **Task Scheduler** | Laravel Task Scheduling (Cron) | Scheduler harian untuk pengecekan deadline revisi (H-2) dan sinkronisasi status proyek. |
| **Storage Engine** | Laravel Filesystem (Local / S3) | Penyimpanan file upload (PDF, JPG, PNG) dengan enkripsi path/obfuscation. |

### 3.2 Lapisan Arsitektur
1. **Presentation Layer:** Template Blade berbasis komponen modular, menerapkan prinsip UI modern (cards, subtle shadows, badging yang jelas, stepper progress).
2. **Application Layer:** Berisi Controller, Form Request Validation, dan Service Class yang mengisolasi business rules (misal: validasi status independen per dokumen).
3. **Notification Layer:** Menjalankan dispatch event dan queue worker untuk mengirimkan email HTML responsif tanpa memblokir thread HTTP utama.
4. **Data Layer:** PostgreSQL dengan indexing pada `status`, `supplier_id`, `document_request_id`, dan foreign keys untuk kecepatan reporting.

---

## 4. Rancangan Basis Data

### 4.1 Daftar Entitas Utama

```
                     +--------------------+
                     |       users        |
                     +--------------------+
                     | id (PK)            |
                     | name, email, role  |
                     +---------+----------+
                               | 1
                               |
                               | 1..*
+--------------------+       +-v------------------+       +---------------------+
|     suppliers      | 1   * | document_requests  | 1   * |  notification_logs  |
+--------------------+-------+--------------------+-------+---------------------+
| id (PK)            |       | id (PK)            |       | id (PK)             |
| name, pic_name     |       | supplier_id (FK)   |       | document_request_id |
| pic_email, phone   |       | model, part_number |       | type, recipient     |
+--------------------+       | access_token       |       | sent_at, status     |
                             | due_date           |       +---------------------+
                             +---------+----------+
                                       | 1
                                       |
                                       | *
+--------------------+       +---------v----------+       +---------------------+
|   document_types   | 1   * | request_documents  | 1   * | document_histories  |
+--------------------+-------+--------------------+-------+---------------------+
| id (PK)            |       | id (PK)            |       | id (PK)             |
| name, is_mandatory |       | doc_request_id(FK) |       | req_document_id(FK) |
| scope (QA/PUR/PPC) |       | doc_type_id (FK)   |       | action, actor_id    |
+--------------------+       | status, file_path  |       | note, created_at    |
                             | revision_deadline  |       +---------------------+
                             +--------------------+
```

#### Struktur Kolom Entitas:
* **`users`**: `id` (UUID/BigInt PK), `name`, `email` (unique), `password`, `role` (enum: `supplier`, `admin_purchasing`, `admin_qa`, `admin_ppc`), `is_active` (bool), `timestamps`.
* **`suppliers`**: `id` (PK), `name`, `vendor_code` (unique), `pic_name`, `pic_email`, `pic_phone`, `address`, `timestamps`.
* **`document_requests`**: `id` (PK), `supplier_id` (FK), `model` (varchar), `part_name` (varchar), `part_number` (varchar), `due_date` (date), `access_token` (varchar 64, unique indexed), `created_by` (FK users), `timestamps`.
* **`document_types`**: `id` (PK), `name` (varchar), `code` (varchar), `is_mandatory` (bool), `scope` (enum/array: `QA`, `PURCHASING`, `PPC`), `description` (text).
* **`request_documents`**: `id` (PK), `document_request_id` (FK), `document_type_id` (FK), `status` (enum: `PENDING`, `SUBMITTED`, `REVISE`, `APPROVED`), `file_path` (text, nullable), `original_filename` (varchar, nullable), `uploaded_at` (timestamp, nullable), `reviewed_by` (FK users, nullable), `reviewed_at` (timestamp, nullable), `revision_note` (text, nullable), `revision_deadline_at` (timestamp, nullable), `timestamps`.
* **`document_histories`**: `id` (PK), `request_document_id` (FK), `action` (varchar: `UPLOAD`, `APPROVE`, `REJECT_REVISE`, `REUPLOAD`), `actor_id` (FK users, nullable), `actor_name` (varchar), `note` (text, nullable), `created_at` (timestamp).
* **`notification_logs`**: `id` (PK), `document_request_id` (FK), `type` (varchar: `REQUEST_LETTER`, `DOCS_SUBMITTED`, `REVISION_REMINDER`, `APPROVAL_COMPLETED`), `recipient` (varchar), `subject` (varchar), `sent_at` (timestamp), `status` (enum: `SENT`, `FAILED`), `error_message` (text, nullable).
* **`revision_configs`**: `id` (PK), `key` (varchar unique), `value` (varchar), `description` (varchar), `updated_by` (FK users), `updated_at` (timestamp).

### 4.2 Catatan Rancangan Database
1. **Granularitas Status:** Status berada di tabel `request_documents` agar tiap file memiliki siklus hidup independen (misal: 14 file approved, 2 file revise).
2. **Perhitungan Deadline Otomatis:** Saat trigger update status ke `REVISE` tereksekusi, nilai `revision_deadline_at` dihitung otomatis: `NOW() + INTERVAL '{revision_configs.value} DAYS'`.
3. **Audit Immutability:** Tabel `document_histories` bersifat *append-only* (tidak boleh di-update/delete) guna menjamin integritas jejak audit ISO/IATF.

---

## 5. Rancangan Peran & Hak Akses (RBAC)

| Modul & Hak Akses | Supplier (Eksternal) | Admin Purchasing | Admin QA | Admin PPC |
| :--- | :---: | :---: | :---: | :---: |
| **Buat Request & Generate Token** | ❌ | ❌ | ✅ (Owner) | ❌ |
| **Akses Form Upload via Token Portal** | ✅ (Secure Token) | ❌ | ❌ | ❌ |
| **Upload / Re-upload Dokumen** | ✅ | ❌ | ❌ | ❌ |
| **Review & Approval Dokumen QA** | ❌ | ❌ | ✅ | ❌ |
| **Review & Approval Dokumen Purchasing** | ❌ | ✅ | ❌ | ❌ |
| **Review & Approval Dokumen PPC** | ❌ | ❌ | ❌ | ✅ |
| **Konfigurasi Deadline Revisi (H+N)** | ❌ | ❌ | ✅ | ❌ |
| **Trigger Ulang Notifikasi Email** | ❌ | ❌ | ✅ | ❌ |
| **Dashboard Monitoring Purchasing & Klasifikasi** | ❌ | ✅ (Full View) | 👁️ (Read Only) | 👁️ (Read Only) |
| **Download Berkas Approved (PDF)** | ✅ | ✅ | ✅ | ✅ |
| **Melihat Log Jejak Audit (History)** | 👁️ (Dokumennya) | ✅ | ✅ | ✅ |

---

## 6. Rancangan Modul & Pemetaan Kebutuhan Fungsional

### 6.1 Modul Data Request & Surat Request (FR-01, FR-02, FR-03)
* **Deskripsi:** Admin QA menginput detail request (Model, Part Name, Part Number, Due Date, Supplier Target). Sistem membuat token acak berkeamanan tinggi (SHA-256) dan memicu `SendRequestLetterJob`.
* **Output:** Record baru pada `document_requests`, 16 baris placeholder `request_documents` dengan status `PENDING`, dan email Surat Request terkirim ke Supplier.

### 6.2 Modul Upload Dokumen Supplier (FR-04)
* **Deskripsi:** Supplier membuka tautan token, menampilkan form upload 16 slot dokumen (14 wajib, 2 optional).
* **Validasi:** Validasi tipe file MIME (PDF, JPG, PNG), ukuran maksimum (misal 10MB per file), serta pencegahan submit parsial jika dokumen mandatory belum lengkap diunggah.
* **Output:** File tersimpan pada storage, status berubah menjadi `SUBMITTED`.

### 6.3 Modul Review & Approval per Dokumen (FR-06, FR-07, FR-08, FR-12)
* **Deskripsi:** Admin internal membuka antarmuka dokumen sesuai scope divisinya. Dokumen dapat dipratinjau (*embedded preview*) langsung di browser.
* **Keputusan:** Admin memilih **APPROVE** (final) atau **REVISE** (wajib mengisi catatan revisi spesifik).
* **Output:** Status berubah menjadi `APPROVED` atau `REVISE`, tercatat di `document_histories`.

### 6.4 Modul Deadline & Revisi (FR-09, FR-10, FR-11)
* **Deskripsi:** Dokumen berstatus `REVISE` memicu perhitungan deadline revisi. Portal Supplier membuka kunci (*unlock*) hanya untuk slot file yang berstatus `REVISE`.
* **Scheduler:** Cron job harian memindai dokumen `REVISE` yang mendekati deadline (H-2 atau jatuh tempo) dan memicu pengiriman email pengingat.

### 6.5 Modul Notifikasi (FR-02, FR-05, FR-10, FR-13)
* **Deskripsi:** Menangani antrean email menggunakan template modern HTML dengan visual yang jelas:
  1. *Surat Request Baru*: Berisi ringkasan part dan tombol CTA "Buka Portal Upload".
  2. *Dokumen Terkirim*: Notifikasi ke QA/Purchasing/PPC bahwa supplier telah mengunggah berkas lengkap.
  3. *Pengingat Revisi*: Detail dokumen yang ditolak beserta catatan perbaikan dan sisa waktu.
  4. *Approval Selesai*: Notifikasi resmi bahwa seluruh dokumen telah berstatus APPROVED.

### 6.6 Modul Status, Riwayat, Download, & Dashboard (FR-14 s.d. FR-18)
* **Tracking Real-time:** Status badges interaktif pada sisi portal supplier dan admin.
* **Download Manager:** Endpoint download berkas PDF valid yang sudah disetujui.
* **Dashboard Purchasing:** Metrik persentase progres approval, ringkasan per proyek part, dan klasifikasi otomatis 3 kategori:
  1. 🔴 *Belum Kirim Dokumen* (Zero submitted).
  2. 🟡 *Sedang Berjalan / Belum Lengkap* (Proses review / revisi berlangsung).
  3. 🟢 *Lengkap / Seluruhnya Approved* (100% mandatory docs approved).

---

## 7. Panduan Desain & Standar UI/UX Modern

Agar sistem tidak hanya kuat secara teknis tetapi juga memiliki antarmuka yang modern, elegan, profesional, dan sangat ramah pengguna (*user-friendly*), implementasi frontend wajib mematuhi standar desain berikut:

### 7.1 Prinsip Desain & Tata Letak Modern
1. **Clean & Uncluttered Layout:** Gunakan konsep *white-space* / *negative-space* yang proporsional. Hindari pemadatan data tabular yang membuat pengguna lelah visual.
2. **Card-Based UI Containers:** Kelompokkan setiap bagian informasi dalam kontainer kartu (*cards*) terpisah dengan *subtle rounded corners* (8px - 12px) dan bayangan lembut (*soft elevation shadow* `0 4px 12px rgba(0,0,0,0.05)`).
3. **Sticky Action Bars & Navigation:** Pada halaman panjang (seperti form 16 dokumen atau review detail), letakkan tombol aksi utama (*Save, Approve, Submit*) pada bilah bawah melayang (*sticky floating action footer*) agar pengguna tidak perlu scrolling jauh.
4. **Mobile & Tablet Friendly:** Responsif penuh; tabel dilengkapi fitur *horizontal scroll wrapper* dengan *pinned key columns* pada layar kecil.

### 7.2 Sistem Warna (Color Palette) & Semantic Tokens
Gunakan palet warna profesional bertema korporat otomotif modern:

```
+-----------------------------------------------------------------------------------+
|  PRIMARY BRAND         SUCCESS (APPROVED)      WARNING (REVISE)      DANGER       |
|  #0F2942 (Deep Navy)   #10B981 (Emerald)       #F59E0B (Amber Gold)  #EF4444      |
|  Accent: #2563EB Blue  Bg: #ECFDF5             Bg: #FFFBEB           Bg: #FEF2F2  |
+-----------------------------------------------------------------------------------+
```

* **Primary Corporate:** `#0F2942` (Deep Slate Navy) – Navigasi atas, header, dan elemen branding Akebono.
* **Accent Color:** `#2563EB` (Royal Blue) – Tombol aksi utama, tautan aktif, indikator fokus.
* **Semantic Status Colors:**
  * **APPROVED:** Teks `#065F46`, Latar Badge `#D1FAE5`, Border `#A7F3D0` (Modern Soft Emerald).
  * **REVISE / REJECT:** Teks `#92400E`, Latar Badge `#FEF3C7`, Border `#FDE68A` (Warm Amber Gold).
  * **SUBMITTED / IN REVIEW:** Teks `#1E40AF`, Latar Badge `#DBEAFE`, Border `#BFDBFE` (Soft Blue).
  * **PENDING / NOT UPLOADED:** Teks `#4B5563`, Latar Badge `#F3F4F6`, Border `#E5E7EB` (Neutral Gray).
* **Neutral Backgrounds:**
  * Page Body: `#F8FAFC` (Slate Tint Soft Light).
  * Card Surface: `#FFFFFF` (Pure White).
  * Table Hover: `#F1F5F9`.

### 7.3 Tipografi & Hierarki Visual
* **Font Family:** `Inter`, `Plus Jakarta Sans`, atau `Roboto` (sans-serif modern dengan legibilitas tinggi).
* **Hierarki Teks:**
  * **Page Title (H1):** `24px` / `SemiBold (600)` – Warna `#0F172A`.
  * **Section Card Title (H2/H3):** `16px - 18px` / `SemiBold (600)` – Warna `#1E293B`.
  * **Body Text:** `14px` / `Regular (400)` – Warna `#334155`.
  * **Caption / Helper Text:** `12px` / `Regular (400)` – Warna `#64748B`.
  * **Badge Text:** `11px - 12px` / `Medium (500)` / `Uppercase tracking +0.5px`.

### 7.4 Panduan Komponen Antarmuka (Component Guidelines)

#### A. Komponen Slot Upload Dokumen (Supplier Portal)
Setiap dokumen disajikan dalam bentuk list-card interaktif:
1. **Header Slot:** Nomor urut, Nama Dokumen, Tag Wajib/Optional (Badge Merah/Abu-abu), Lingkup Approver (QA/PPC/PUR).
2. **Dropzone Area:** Area drag-and-drop dengan border putus-putus (`dashed 2px #CBD5E1`), icon upload awan, teks instruksi "Tarik file ke sini atau *Klik untuk Pilih*", serta keterangan "Format: PDF, JPG, PNG (Maks. 10MB)".
3. **File Preview State:** Saat file dipilih, tampilkan:
   * Ikon tipe file (PDF merah, Gambar biru).
   * Nama file dan ukuran (`Document_v1.pdf • 2.4 MB`).
   * Progress bar upload animasi.
   * Tombol *Remove / Replace*.
4. **Rejection Alert Box (Untuk Dokumen REVISE):** Kotak peringatan berwarna kuning lembut tepat di bawah slot dokumen dengan icon peringatan, menampilkan:
   * Catatan Revisi dari Reviewer secara transparan.
   * Deadline pengunggahan ulang beserta indikator sisa waktu (Countdown tag).



#### B. Komponen Modal & Form Approval (Admin Side)
1. **Side-by-Side Review:** Tampilan terbagi dua (*split view* jika layar lebar):
   * Sisi Kiri: Embedded PDF/Image Document Viewer (dengan zoom & rotate).
   * Sisi Kanan: Metadata dokumen, Riwayat revisi sebelumnya, dan Panel Tindakan Approval.
2. **Action Buttons:**
   * Tombol Hijau `[ ✓ Approve Dokumen ]` (Konfirmasi satu klik).
   * Tombol Kuning/Merah `[ ✕ Minta Revisi ]` (Membuka modal input catatan wajib).
3. **Revision Note Modal:** Wajib mencakup:
   * Textarea catatan dengan counter karakter.
   * Template checklist revisi umum (*quick chips* / preset reason: misal "Stempel belum ada", "Format salah", "Halaman tidak lengkap").

### 7.5 Interaksi & Feedback Pengguna (Micro-interactions)
1. **Toast Notification:** Feedback instan di pojok kanan atas setelah aksi berhasil/gagal (`"Dokumen QCP berhasil di-approve"`).
2. **Skeleton Loading:** Tampilkan placeholder shimmer saat memuat data tabel atau dokumen viewer, hindari spinner layar penuh yang membingungkan.
3. **Empty States yang Informatif:** Jika tidak ada data yang perlu direview, tampilkan ilustrasi modern bertema bersih dengan teks *"Semua dokumen telah selesai direview!"*.
4. **Interactive Progress Stepper:** Di portal supplier, tampilkan progress bar kelengkapan:
   * `8 / 14 Dokumen Wajib Terunggah (57%)` dengan indikator visual dinamis.

### 7.6 Pola UX Spesifik per Halaman/Fitur

#### 1. Form Pembuatan Request (Admin QA)
* Form stepper / 2 kolom ringkas: Kolom 1 (Data Part: Model, Part Name, Part No, Due Date), Kolom 2 (Pilih Supplier dengan fitur pencarian live search).
* Preview ringkasan email yang akan dikirim sebelum konfirmasi submit.

#### 2. Dashboard Monitoring Purchasing
* **Statistik Cepat (Top Metrics Cards):**
  * Total Permintaan Aktif.
  * Dokumen Menunggu Review.
  * Dokumen Dalam Proses Revisi.
  * Proyek Selesai (100% Approved).
* **Filter & Pencarian Lanjutan:** Filter instan berdasarkan Supplier, Model Part, Status Proyek (3 Kategori), dan Rentang Waktu tanpa reload halaman.
* **Tabel Monitoring Berwarna Cerdas:** Menggunakan *subtle row highlights* untuk dokumen yang melewati SLA atau mendekati deadline.

---

## 8. Alur Implementasi Teknis per Tahap Proses Bisnis

| Tahap BPD | Modul Teknis Terkait | Komponen Implementasi | Trigger Sistem & Alur Kerja |
| :--- | :--- | :--- | :--- |
| **1. Pembuatan & Pengiriman Data Request** | Modul Data Request & Surat Request | `RequestController@store`, `SendRequestLetterJob` | Admin QA submit form. Sistem simpan header batch, buat token portal, dan queue email surat request ke supplier. |
| **2. Supplier Upload Dokumen** | Modul Upload Dokumen Supplier | `PortalController@uploadDocuments`, `DocumentValidator` | Supplier mengakses link ber-token, mengunggah seluruh slot dokumen mandatory & optional. |
| **3. Notifikasi Dokumen Terkirim** | Modul Notifikasi | `DocumentsSubmittedNotification` | Terpicu ketika seluruh slot dokumen mandatory berstatus `SUBMITTED`. Mengirim email ke reviewer terkait. |
| **4. Review & Approval per Dokumen** | Modul Review & Approval | `ReviewController@approve`, `ReviewController@reject` | Admin (QA/Purchasing/PPC) membuka dokumen & menetapkan status `APPROVED` atau `REVISE` (+ catatan perbaikan). |
| **5. Revisi Dokumen** | Modul Deadline & Revisi | `RevisionDeadlineJob`, `PortalController@reupload` | Sistem set `revision_deadline_at`. Job harian kirim reminder jika mendekati deadline. Supplier upload ulang via portal. |
| **6. Dokumen Approved & Selesai** | Modul Status, Riwayat, Download & Dashboard | `ApprovalCompletedNotification`, `DocumentController@download` | Saat 100% dokumen wajib `APPROVED`, trigger notifikasi selesai dikirim. Berkas siap diunduh dalam bentuk PDF. |

---

## 9. Alur Status Dokumen (State Machine)

Status setiap dokumen dalam `request_documents` bersifat independen dan diatur melalui diagram state machine berikut:

```
                  +-------------------------+
                  |         PENDING         |
                  | (Menunggu Upload Awal)  |
                  +------------+------------+
                               |
                               | Supplier mengunggah file
                               v
                  +-------------------------+
                  |        SUBMITTED        | <---------------------+
                  |   (Menunggu Review)     |                       |
                  +------+------------+-----+                       |
                         |            |                             |
      Admin Approve      |            | Admin Reject & Minta Revisi | Supplier Upload Ulang
  (Dokumen Sesuai)       |            | (Wajib isi catatan revisi)  | File Revisi
                         v            v                             |
                  +------------+ +----+--------------------+        |
                  |  APPROVED  | |        REVISE           +--------+
                  |  (FINAL)   | | (Menghitung Deadline)   |
                  +------------+ +-------------------------+
```

| Dari Status | Ke Status | Aktor Pemicu | Aksi Sistem & Business Rules |
| :--- | :--- | :--- | :--- |
| `PENDING` | `SUBMITTED` | Supplier | Simpan file fisik, catat timestamp upload, ubah status ke `SUBMITTED`. |
| `SUBMITTED` | `APPROVED` | Admin Reviewer | Kunci file secara permanen, catat ID reviewer & timestamp di audit trail. Status final (tidak dapat diubah). |
| `SUBMITTED` | `REVISE` | Admin Reviewer | Simpan `revision_note`, hitung dan simpan `revision_deadline_at` (default H+2). Buka slot re-upload di portal supplier. |
| `REVISE` | `SUBMITTED` | Supplier | Timpa dengan file revisi baru, reset status ke `SUBMITTED`, catat riwayat pengunggahan ulang di audit trail. |
| `APPROVED` | *(None)* | - | Status terminal. Tidak dapat diedit atau dibatalkan tanpa proses request formal baru. |

---

## 10. Rancangan Notifikasi & Job Terjadwal

```
[Trigger Event] ------------> [Laravel Queue Worker] ------------> [SMTP / Mail Server]
                                       |
                                       +----> [Simpan Log ke notification_logs]
```

### Rincian Pemicu Notifikasi & Scheduled Tasks:
1. **Surat Request Awal (`Mail::to($supplier)`):**
   * *Trigger:* Selesainya pembuatan record `document_requests`.
   * *Payload:* Ringkasan model part, due date, nama PIC Akebono, dan *Magic Action Button* berisi URL portal ber-token.
2. **Dokumen Terkirim Lengkap (`Mail::to($reviewers)`):**
   * *Trigger:* Dokumen wajib terakhir berhasil diunggah oleh supplier.
   * *Payload:* Pemberitahuan bahwa batch dokumen telah siap direview oleh tim QA, PPC, dan Purchasing.
3. **Pengingat Revisi H-2 (`RevisionReminderJob` via Scheduler Cron):**
   * *Trigger:* Scheduler cron harian (`08:00 WIB`) mengecek dokumen dengan `status = 'REVISE'` dan `revision_deadline_at <= NOW() + 2 DAYS`.
   * *Payload:* Daftar rincian file yang masih salah, catatan revisi, dan peringatan batas akhir waktu.
4. **Approval Selesai / Full Clearance (`Mail::to($supplier, $purchasing)`):**
   * *Trigger:* Semua dokumen berstatus `APPROVED`.
   * *Payload:* Konfirmasi bahwa seluruh dokumen part telah valid dan disetujui, disertai tautan download PDF.

---

## 11. Rancangan Endpoint / Rute Utama

### 11.1 Portal Eksternal Supplier (Token-Based Routing)
| Method | Endpoint | Fungsi | Payload / Respon Utama |
| :--- | :--- | :--- | :--- |
| `GET` | `/portal/{token}` | Menampilkan landing portal upload & status real-time | View Blade: detail request & daftar slot dokumen |
| `POST` | `/portal/{token}/upload` | Mengunggah seluruh file dokumen awal | `multipart/form-data`: `files[doc_type_id]` |
| `POST` | `/portal/{token}/documents/{id}/reupload` | Mengunggah ulang file yang diminta revisi | `multipart/form-data`: `file`, `document_id` |
| `GET` | `/portal/{token}/documents/{id}/download` | Mengunduh file approved milik supplier | Binary File Stream (PDF) |

### 11.2 Panel Internal Admin (Authenticated & Role Protected)
| Method | Endpoint | Middleware / Role | Fungsi |
| :--- | :--- | :--- | :--- |
| `GET` | `/admin/requests` | `auth, role:admin_qa` | Daftar request dokumen yang dibuat |
| `POST` | `/admin/requests` | `auth, role:admin_qa` | Membuat data request baru & generate token |
| `POST` | `/admin/requests/{id}/resend-email` | `auth, role:admin_qa` | Memicu ulang pengiriman email surat request |
| `GET` | `/admin/documents` | `auth, scope.check` | Daftar antrean review dokumen sesuai scope login |
| `POST` | `/admin/documents/{id}/approve` | `auth, scope.check` | Menyetujui dokumen |
| `POST` | `/admin/documents/{id}/reject` | `auth, scope.check` | Menolak dokumen (`revision_note` required) |
| `GET` | `/admin/dashboard` | `auth, role:admin_purchasing,admin_qa,admin_ppc` | Dashboard monitoring & 3 klasifikasi proyek |
| `PUT` | `/admin/settings/deadline` | `auth, role:admin_qa` | Mengubah durasi default deadline revisi |
| `GET` | `/admin/documents/{id}/history` | `auth` | Melihat riwayat / jejak audit dokumen |

---

## 12. Rencana Pengujian (Test Plan)

| ID Uji | Modul | Skenario Pengujian | Hasil yang Diharapkan |
| :---: | :--- | :--- | :--- |
| **TC-01** | Data Request | QA membuat request baru dengan input lengkap & valid. | Data tersimpan, token ter-generate, email surat request masuk antrean queue. |
| **TC-02** | Upload Portal | Supplier mengunggah 14 dokumen wajib via portal token. | Seluruh file tervalidasi, status berubah `SUBMITTED`, notifikasi terkirim ke admin. |
| **TC-03** | Partial Upload | Supplier mencoba submit tanpa mengisi salah satu dokumen mandatory. | Sistem menolak submit, menampilkan feedback validasi merah pada slot yang belum terisi. |
| **TC-04** | Review Granular | Admin QA meng-approve 5 dokumen dan me-reject 1 dokumen (dengan note). | 5 dokumen berstatus `APPROVED` (terkunci), 1 dokumen berstatus `REVISE` (deadline terhitung). |
| **TC-05** | Reminder Job | Dokumen REVISE belum diperbaiki hingga mendekati batas waktu H-2. | Job scheduler otomatis mengirimkan email pengingat revisi ke supplier. |
| **TC-06** | Re-upload | Supplier hanya mengunggah file baru pada slot yang `REVISE`. | Slot file terbarui, status berubah kembali menjadi `SUBMITTED`, riwayat audit tercatat rapi. |
| **TC-07** | Download PDF | User mengunduh dokumen yang telah berstatus `APPROVED`. | File PDF terunduh utuh tanpa *corrupted data*. |
| **TC-08** | RBAC Scope | Admin PPC mencoba meng-approve dokumen yang berada di bawah scope QA. | Sistem menolak aksi dengan respon HTTP 403 Forbidden (Unauthorized Scope). |
| **TC-09** | Dashboard Query | Status berubah pada salah satu file proyek supplier. | Kategori status proyek di dashboard Purchasing ter-update secara real-time. |

---

## 13. Risiko Teknis & Mitigasi

| No | Risiko Teknis | Dampak | Strategi Mitigasi Terintegrasi |
| :---: | :--- | :--- | :--- |
| **1** | **Email Surat Request Masuk Spam / Terlewat** | Supplier terlambat mengetahui adanya request dokumen part baru. | • Integrasi konfigurasi DKIM, SPF, dan DMARC pada server mail.<br>• Sediakan fitur **"Trigger Resend Notification"** manual bagi Admin QA.<br>• Tampilkan status *Email Sent/Opened* pada audit log. |
| **2** | **Human Error: Salah Slot File Saat Upload** | Dokumen tertukar sehingga proses review terhambat. | • Komponen UI dilengkapi nama dokumen yang sangat jelas dan preview inline.<br>• Ringkasan pra-submit (*confirmation modal*) sebelum file diunggah permanen. |
| **3** | **Deadline Revisi Terlewati** | Proyek part tertunda dan approval terhambat. | • Scheduler otomatis mengirimkan reminder berkala pada H-2.<br>• Peringatan visual warna merah berkedip/kontras pada portal supplier.<br>• Notifikasi peringatan eskalasi ke Admin Purchasing untuk follow-up langsung. |
| **4** | **Kegagalan Antrean Notifikasi (Worker Timeout)** | Email tidak sampai ke penerima tepat waktu. | • Konfigurasi automatic retry (`tries=3`, `backoff=60s`) pada queue Laravel.<br>• Logging otomatis pada tabel `notification_logs` beserta status kegagalan untuk monitoring tim DevOps. |
| **5** | **Lonjakan Akses & Upload Bersamaan** | Server overload atau proses upload lambat. | • Desain database terindeks per record `request_documents`.<br>• Pembatasan ukuran file maksimal (10MB/file) dengan validasi client-side & server-side.<br>• Penggunaan storage asynchronous. |

---

## 14. Lampiran

### 14.1 Diagram Alur Proses
Alur proses bisnis approval dokumen mengacu pada standar alur BPD Bagian 5 dan PRD Bagian 6, yang mencakup siklus lengkap:
1. *Pembuatan Request oleh Akebono QA*
2. *Pengiriman Surat Request via Email*
3. *Penerimaan & Upload 14 Dokumen Wajib (+ 2 Optional) oleh Supplier*
4. *Review Paralel/Granular oleh Tim QA, PPC, dan Purchasing*
5. *Percabangan Kondisi:*
   * Jika ada dokumen salah → Diberi status `REVISE` + Catatan → Sisa waktu 2 hari → Notifikasi Reminder jika perlu → Re-upload oleh Supplier.
   * Jika seluruh dokumen benar → Disimpan sebagai `APPROVED` (Permanen).
6. *Notifikasi Final & Dokumen Approved Siap Diunduh (PDF).*

### 14.2 Daftar Dokumen Mandatory & Optional (Master Data `document_types`)

| No | Nama Dokumen Master | Kategori | Lingkup Reviewer (Scope) |
| :---: | :--- | :---: | :---: |
| 1 | Process Flow Diagram (PFD) | **Wajib (Mandatory)** | QA |
| 2 | Process Failure Mode dan Effect Analysis (FMEA) | **Wajib (Mandatory)** | QA |
| 3 | Quality Control Process Chart (QCPC) | **Wajib (Mandatory)** | QA |
| 4 | Measurement System Analysis (MSA) | **Wajib (Mandatory)** | QA |
| 5 | Inspection Standard (IS) | **Wajib (Mandatory)** | QA |
| 6 | Initial Sample Inspection Report (ISIR) | **Wajib (Mandatory)** | QA |
| 7 | X-R Control Chart (CP-CK) | **Wajib (Mandatory)** | QA |
| 8 | Appearance Approval Report (AAR) | **Wajib (Mandatory)** | QA |
| 9 | Limit Sample Tag | **Wajib (Mandatory)** | QA |
| 10 | Packaging Standard (PS) | **Wajib (Mandatory)** | Purchasing, PPC, QA |
| 11 | Part Submission Warrant (PSW) | **Wajib (Mandatory)** | Purchasing, QA |
| 12 | Mill Sheet Material (Dimensi, Material, Colour-check, dll.) | **Wajib (Mandatory)** | QA |
| 13 | Certificate of Calibration | **Wajib (Mandatory)** | QA |
| 14 | Drawing Product | **Wajib (Mandatory)** | QA |
| 15 | SOC Free | *Optional* | QA |
| 16 | IMDS (International Material Data System) | *Optional* | QA |

---
*Dokumen ini dirancang sebagai panduan tunggal implementasi teknis dan standar antarmuka UI/UX Sistem Approval Dokumen Supplier PT Akebono Astra Indonesia.*

---

## IMPLEMENTATION STATUS

**UPDATED:** August 21, 2026

✅ **Daftar dokumen mandatory dan optional telah disesuaikan sesuai gambar spesifikasi**
✅ **Hak approval per scope telah dikonfigurasi sesuai RBAC**
✅ **Database berhasil diperbarui dengan 16 jenis dokumen (14 mandatory + 2 optional)**

### TECHNICAL UPDATES:
- ✅ **14 Dokumen Mandatory berdasarkan gambar:**
  1. Process Flow Diagram (PFD) - QA
  2. Process Failure Mode dan Effect Analysis (FMEA) - QA  
  3. Quality Control Process Chart (QCPC) - QA
  4. Measurement System Analysis (MSA) - QA
  5. Inspection Standard (IS) - QA
  6. Initial Sample Inspection Report (ISIR) - QA
  7. X-R Control Chart (CP-CK) - QA
  8. Appearance Approval Report (AAR) - QA
  9. Limit Sample Tag - QA
  10. Packaging Standard (PS) - Purchasing, PPC, QA
  11. Part Submission Warrant (PSW) - Purchasing, QA
  12. Mill Sheet Material (Dimensi, Material, Colour-check, dll.) - QA
  13. Certificate of Calibration - QA
  14. Drawing Product - QA

- ✅ **2 Dokumen Optional:**
  15. SOC Free - QA
  16. IMDS (International Material Data System) - QA

- ✅ **Role-based Access Control (RBAC):**
  - QA Scope: 16 documents (dapat mereview semua dokumen)
  - PURCHASING Scope: 2 documents (Packaging Standard, Part Submission Warrant)  
  - PPC Scope: 1 document (Packaging Standard)

- ✅ **Middleware CheckReviewScope** dikonfigurasi untuk enforcement hak approval
- ✅ **Database commands** tersedia: `document-types:refresh` dan `document-types:show`

**STATUS: READY FOR IMPLEMENTATION** ✨
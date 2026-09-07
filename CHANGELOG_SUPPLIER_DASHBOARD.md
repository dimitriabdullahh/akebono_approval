# Changelog: Supplier Dashboard Update

## Tanggal: 1 September 2026

### Perubahan yang Dilakukan

#### 1. Dashboard Supplier - Outstanding Documents Section

**File yang Diubah:**
- `app/Http/Controllers/SupplierController.php`
- `resources/views/supplier/dashboard.blade.php`

**Deskripsi Perubahan:**

##### Controller (`SupplierController.php`)
- Menambahkan query untuk mendapatkan outstanding documents (dokumen dengan status `PENDING` atau `REJECTED`)
- Outstanding documents diurutkan berdasarkan prioritas:
  - Status `REJECTED` ditampilkan terlebih dahulu (prioritas tinggi)
  - Kemudian dokumen `PENDING` berdasarkan tanggal pembuatan
- Data outstanding documents dikirim ke view dashboard

##### View (`dashboard.blade.php`)
- **Statistics Cards**: Ditambahkan card keempat untuk menampilkan jumlah Outstanding Documents dengan icon alert berwarna merah
- **Outstanding Documents Section**: Section baru yang menampilkan:
  - Tabel detail outstanding documents
  - Highlight merah untuk dokumen yang butuh revisi (`REJECTED`)
  - Informasi Request ID dan Project Name
  - Document Type
  - Jumlah parts
  - Due date dengan indicator:
    - Badge "Overdue" (merah) jika sudah lewat deadline
    - Badge jumlah hari tersisa (kuning) jika deadline dalam 3 hari
  - Status dokumen:
    - "Revision Required" (merah) untuk status `REJECTED`
    - "Not Submitted" (kuning) untuk status `PENDING`
  - Tombol action untuk upload/reupload langsung ke form upload

### Fitur Utama

1. **Dashboard yang Lebih Informatif**
   - Supplier langsung melihat dokumen mana yang perlu urgent action
   - Card statistik menampilkan jumlah outstanding documents

2. **Prioritas Visual yang Jelas**
   - Dokumen REJECTED ditampilkan dengan background merah
   - Badge dan icon yang jelas untuk setiap status
   - Warning untuk deadline yang mendekat atau terlewat

3. **Quick Action**
   - Tombol upload/reupload langsung tersedia di dashboard
   - Tidak perlu navigasi berlapis untuk submit dokumen

4. **Section Conditional**
   - Outstanding Documents section hanya muncul jika ada dokumen outstanding
   - Menghindari tampilan yang kosong/membingungkan

### Manfaat

1. **Efisiensi Supplier**: Supplier langsung tahu dokumen mana yang harus dikerjakan tanpa perlu klik detail request satu per satu
2. **Mengurangi Keterlambatan**: Warning visual untuk deadline membantu supplier prioritas dokumen yang urgent
3. **Fokus pada Action Item**: Dokumen yang butuh revisi (REJECTED) ditampilkan dengan prioritas tertinggi
4. **User Experience**: Interface yang lebih intuitif dengan semua informasi penting di satu halaman

### Testing Checklist

- [ ] Verify outstanding documents query menampilkan dokumen PENDING dan REJECTED saja
- [ ] Verify sorting prioritas (REJECTED di atas, kemudian PENDING)
- [ ] Verify card statistics menampilkan jumlah yang benar
- [ ] Verify tombol upload/reupload mengarah ke route yang benar
- [ ] Verify badge deadline menampilkan status yang akurat
- [ ] Verify conditional rendering section (hanya muncul jika ada outstanding)
- [ ] Verify highlight merah untuk row dokumen REJECTED
- [ ] Verify responsiveness di berbagai ukuran layar

### Kompatibilitas

- Backward compatible dengan existing code
- Tidak mengubah logic atau route yang ada
- Hanya menambahkan data dan tampilan baru

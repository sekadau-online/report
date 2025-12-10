# Fitur

Dokumentasi lengkap fitur-fitur LKEU-RAPI.

## Dashboard

Dashboard menampilkan ringkasan keuangan:

- **Total Pemasukan**: Jumlah semua transaksi pemasukan
- **Total Pengeluaran**: Jumlah semua transaksi pengeluaran
- **Saldo Bersih**: Selisih pemasukan dan pengeluaran
- **Jumlah Transaksi**: Total transaksi yang tercatat
- **5 Laporan Terakhir**: Daftar transaksi terbaru

## Laporan Keuangan

### Jenis Transaksi

| Type | Bahasa | Description |
|------|--------|-------------|
| `income` | Pemasukan | Uang masuk |
| `expense` | Pengeluaran | Uang keluar |

### Kategori

**Pemasukan:**
- Gaji
- Bonus
- Investasi
- Penjualan
- Lainnya

**Pengeluaran:**
- Belanja
- Makanan
- Transportasi
- Tagihan
- Hiburan
- Kesehatan
- Pendidikan
- Lainnya

### Membuat Laporan Baru

1. Klik **Laporan Keuangan** di sidebar
2. Klik tombol **Tambah Laporan**
3. Isi form:
   - **Judul**: Deskripsi singkat transaksi
   - **Jenis**: Pemasukan atau Pengeluaran
   - **Kategori**: Pilih kategori yang sesuai
   - **Jumlah**: Nominal transaksi
   - **Tanggal**: Tanggal transaksi
   - **Catatan**: Keterangan tambahan (opsional)
   - **Foto**: Upload bukti transaksi (opsional)
4. Klik **Simpan**

### Filter & Pencarian

- **Pencarian**: Cari berdasarkan judul atau catatan
- **Filter Jenis**: Tampilkan hanya pemasukan atau pengeluaran
- **Sortir**: Urutkan berdasarkan tanggal (terbaru/terlama)

### Mengedit Laporan

1. Klik laporan yang ingin diedit
2. Klik tombol **Edit**
3. Ubah data yang diperlukan
4. Klik **Perbarui**

### Menghapus Laporan

1. Klik laporan yang ingin dihapus
2. Klik tombol **Hapus**
3. Konfirmasi penghapusan

## Import/Export

### Export Data

1. Buka **Laporan Keuangan**
2. Klik tombol **Export**
3. Pilih format:
   - **JSON**: Format data standar
   - **SQL**: Query INSERT untuk database
   - **ZIP**: JSON + foto dalam satu file
4. (Opsional) Set filter tanggal
5. Klik **Download**

### Import Data

1. Buka **Laporan Keuangan**
2. Klik tombol **Import**
3. Pilih file (.json, .sql, atau .zip)
4. Klik **Import**
5. Sistem akan:
   - Memvalidasi data
   - Melewati duplikat (berdasarkan judul, jenis, jumlah, tanggal)
   - Mengimpor foto jika ada (ZIP)

### Deteksi Duplikat

Sistem mendeteksi duplikat berdasarkan:
- Judul yang sama
- Jenis yang sama (income/expense)
- Jumlah yang sama
- Tanggal yang sama

Data duplikat akan dilewati saat import.

## Site Settings

### Branding

| Setting | Description |
|---------|-------------|
| Site Name | Nama aplikasi di sidebar |
| Site Title | Title browser |
| Site Description | Deskripsi untuk SEO |
| Favicon ICO | Icon .ico |
| Favicon SVG | Icon .svg |
| Logo | Logo aplikasi |

### Links

| Setting | Description |
|---------|-------------|
| Repository URL | Link ke GitHub |
| Documentation URL | Link ke dokumentasi |
| Primary Link | Link utama di welcome page |
| Secondary Link | Link sekunder di welcome page |

### Welcome Page

| Setting | Description |
|---------|-------------|
| Welcome Enabled | Aktifkan/nonaktifkan halaman welcome |
| Welcome Title | Judul halaman welcome |
| Welcome Description | Deskripsi di halaman welcome |
| CTA Text | Teks tombol CTA |
| CTA URL | URL tombol CTA |

## Autentikasi

### Login

1. Buka halaman login
2. Masukkan email dan password
3. Klik **Log in**

### Register

1. Buka halaman register
2. Isi form:
   - Nama
   - Email
   - Password
   - Konfirmasi Password
3. Klik **Register**
4. Verifikasi email (jika diaktifkan)

### Two-Factor Authentication

1. Buka **Settings > Two-Factor Authentication**
2. Klik **Enable**
3. Konfirmasi password
4. Scan QR code dengan authenticator app
5. Masukkan kode OTP untuk verifikasi
6. Simpan recovery codes

### Reset Password

1. Klik **Forgot Password** di halaman login
2. Masukkan email
3. Cek email untuk link reset
4. Klik link dan set password baru

## Pengaturan Profil

### Update Profile

1. Buka **Settings > Profile**
2. Edit nama atau email
3. Klik **Save**

### Update Password

1. Buka **Settings > Password**
2. Masukkan password lama
3. Masukkan password baru
4. Konfirmasi password baru
5. Klik **Save**

### Delete Account

1. Buka **Settings > Profile**
2. Scroll ke bagian **Delete Account**
3. Klik **Delete Account**
4. Konfirmasi dengan password
5. Klik **Delete** untuk konfirmasi final

## Langkah Selanjutnya

- [API Reference](./api.md) - Dokumentasi API
- [Testing](./testing.md) - Panduan testing

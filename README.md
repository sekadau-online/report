# LKEU-RAPI

**Laporan Keuangan Rapi** - Sistem Pencatatan dan Pelaporan Keuangan Modern

[![Tests](https://github.com/sekadau-online/report/actions/workflows/tests.yml/badge.svg)](https://github.com/sekadau-online/report/actions/workflows/tests.yml)
[![Lint](https://github.com/sekadau-online/report/actions/workflows/lint.yml/badge.svg)](https://github.com/sekadau-online/report/actions/workflows/lint.yml)

## 📋 Tentang

LKEU-RAPI adalah aplikasi pencatatan dan pelaporan keuangan berbasis web yang dibangun dengan Laravel 12, Livewire 3, dan Flux UI. Aplikasi ini dirancang untuk membantu individu atau organisasi mengelola pemasukan dan pengeluaran dengan mudah dan terorganisir.

## ✨ Fitur

### Manajemen Keuangan
- ✅ Catat pemasukan dan pengeluaran
- ✅ Kategorisasi transaksi (Gaji, Investasi, Belanja, Tagihan, dll.)
- ✅ Lampiran foto/bukti transaksi
- ✅ Pencarian dan filter laporan
- ✅ Dashboard dengan statistik visual

### Import/Export Data
- ✅ Export ke JSON, SQL, atau ZIP (dengan foto)
- ✅ Import dari JSON, SQL, atau ZIP
- ✅ Deteksi duplikat otomatis saat import
- ✅ Filter berdasarkan tanggal saat export

### Pengaturan Situs
- ✅ Kustomisasi branding (nama, logo, favicon)
- ✅ Pengaturan link eksternal
- ✅ Halaman welcome yang dapat dikonfigurasi
- ✅ Dukungan dark mode

### Keamanan
- ✅ Autentikasi user
- ✅ Two-Factor Authentication (2FA)
- ✅ Verifikasi email
- ✅ Reset password

## 🛠️ Tech Stack

| Kategori | Teknologi |
|----------|-----------|
| Backend | PHP 8.4, Laravel 12 |
| Frontend | Livewire 3, Volt, Flux UI 2, Tailwind CSS 4 |
| Database | MySQL / SQLite |
| Testing | Pest 4, PHPUnit 12 |
| Authentication | Laravel Fortify |

## 📦 Instalasi

### Prasyarat

- PHP 8.4+
- Composer 2.x
- Node.js 20+ & NPM
- MySQL 8.x atau SQLite

### Langkah Instalasi

1. **Clone repository**
   ```bash
   git clone https://github.com/sekadau-online/report.git
   cd report
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi database**
   
   Edit file `.env` dan sesuaikan konfigurasi database:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=lkeu_rapi
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Jalankan migrasi dan seeder**
   ```bash
   php artisan migrate --seed
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Jalankan aplikasi**
   ```bash
   php artisan serve
   ```

   Aplikasi akan tersedia di `http://localhost:8000`

### Akun Default

Setelah menjalankan seeder, Anda dapat login dengan:
- **Email:** test@example.com
- **Password:** password

## 🧪 Testing

```bash
# Jalankan semua tests
php artisan test

# Jalankan test tertentu
php artisan test --filter=FinancialReport

# Jalankan dengan coverage
php artisan test --coverage
```

## 📁 Struktur Proyek

```
app/
├── Actions/Fortify/       # Auth actions
├── Http/
│   ├── Controllers/       # HTTP controllers
│   └── Requests/          # Form requests
├── Livewire/Actions/      # Livewire actions
├── Models/                # Eloquent models
├── Providers/             # Service providers
└── Services/              # Business logic services

resources/views/
├── components/            # Blade components
├── livewire/              # Livewire/Volt components
│   ├── auth/              # Authentication pages
│   ├── financial-reports/ # Financial report pages
│   └── settings/          # Settings pages
└── partials/              # Partial views

tests/
├── Feature/               # Feature tests
│   ├── Auth/              # Authentication tests
│   ├── FinancialReports/  # Financial report tests
│   ├── Settings/          # Settings tests
│   └── SiteSettings/      # Site settings tests
└── Unit/                  # Unit tests
```

## 🔧 Konfigurasi

### Site Settings

Pengaturan situs dapat dikonfigurasi melalui:
1. **Admin UI:** Settings > Site Settings
2. **Seeder:** `database/seeders/SiteSettingSeeder.php`
3. **Helper functions:** `site_setting('key')` atau `site_settings()`

### Available Settings

| Key | Group | Description |
|-----|-------|-------------|
| `site_name` | branding | Nama aplikasi |
| `site_title` | branding | Title halaman browser |
| `logo` | branding | Logo aplikasi |
| `favicon_ico` | branding | Favicon ICO |
| `favicon_svg` | branding | Favicon SVG |
| `repository_url` | links | URL repository GitHub |
| `documentation_url` | links | URL dokumentasi |
| `welcome_enabled` | welcome | Aktifkan halaman welcome |
| `welcome_title` | welcome | Judul halaman welcome |
| `welcome_description` | welcome | Deskripsi halaman welcome |

## 📝 API Reference

### Financial Reports

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/financial-reports` | List all reports |
| GET | `/financial-reports/create` | Create form |
| POST | `/financial-reports` | Store new report |
| GET | `/financial-reports/{id}` | View report |
| GET | `/financial-reports/{id}/edit` | Edit form |
| PUT | `/financial-reports/{id}` | Update report |
| DELETE | `/financial-reports/{id}` | Delete report |

### Export/Import

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/financial-reports/export` | Export form |
| POST | `/financial-reports/export` | Download export |
| GET | `/financial-reports/import` | Import form |
| POST | `/financial-reports/import` | Process import |

## 🤝 Kontribusi

Kontribusi sangat diterima! Silakan buat issue atau pull request.

1. Fork repository
2. Buat branch fitur (`git checkout -b feature/amazing-feature`)
3. Commit perubahan (`git commit -m 'Add amazing feature'`)
4. Push ke branch (`git push origin feature/amazing-feature`)
5. Buat Pull Request

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

## 👨‍💻 Author

**Sekadau Online**

- GitHub: [@sekadau-online](https://github.com/sekadau-online)

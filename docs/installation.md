# Instalasi

Panduan lengkap untuk menginstal LKEU-RAPI.

## Prasyarat

### System Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.4+ |
| Composer | 2.x |
| Node.js | 20+ |
| NPM | 10+ |
| MySQL | 8.x |

### PHP Extensions

- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PCRE
- PDO
- Tokenizer
- XML

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/sekadau-online/report.git
cd report
```

### 2. Install PHP Dependencies

```bash
composer install
```

Untuk production:
```bash
composer install --optimize-autoloader --no-dev
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Environment Setup

Copy file environment:
```bash
cp .env.example .env
```

Generate application key:
```bash
php artisan key:generate
```

### 5. Database Configuration

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lkeu_rapi
DB_USERNAME=root
DB_PASSWORD=your_password
```

Atau gunakan SQLite:
```env
DB_CONNECTION=sqlite
# DB_DATABASE akan otomatis menggunakan database/database.sqlite
```

### 6. Run Migrations

```bash
# Create tables
php artisan migrate

# With seeders (recommended)
php artisan migrate --seed
```

### 7. Storage Link

```bash
php artisan storage:link
```

### 8. Build Assets

Development:
```bash
npm run dev
```

Production:
```bash
npm run build
```

### 9. Start Server

```bash
php artisan serve
```

Aplikasi akan berjalan di `http://localhost:8000`

## Akun Default

Setelah seeding, gunakan kredensial berikut:

| Field | Value |
|-------|-------|
| Email | test@example.com |
| Password | password |

## Troubleshooting

### Permission Issues

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Cache Issues

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Vite Manifest Error

Jika muncul error "Unable to locate file in Vite manifest":
```bash
npm run build
```

## Langkah Selanjutnya

- [Konfigurasi](./configuration.md) - Setup konfigurasi aplikasi
- [Fitur](./features.md) - Pelajari fitur-fitur yang tersedia

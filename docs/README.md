# LKEU-RAPI Documentation

Dokumentasi lengkap untuk aplikasi LKEU-RAPI.

## 📚 Daftar Isi

1. [Instalasi](./installation.md)
2. [Konfigurasi](./configuration.md)
3. [Fitur](./features.md)
4. [API Reference](./api.md)
5. [Testing](./testing.md)
6. [Deployment](./deployment.md)

## 🚀 Quick Start

```bash
# Clone & install
git clone https://github.com/sekadau-online/report.git
cd report
composer install && npm install

# Setup
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build

# Run
php artisan serve
```

## 📖 Documentation Files

| File | Description |
|------|-------------|
| [installation.md](./installation.md) | Panduan instalasi lengkap |
| [configuration.md](./configuration.md) | Konfigurasi aplikasi |
| [features.md](./features.md) | Dokumentasi fitur |
| [api.md](./api.md) | API reference |
| [testing.md](./testing.md) | Panduan testing |
| [deployment.md](./deployment.md) | Panduan deployment |

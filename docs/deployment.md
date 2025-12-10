# Deployment

Panduan deployment LKEU-RAPI ke production.

## Persiapan

### Environment Production

```env
APP_NAME="LKEU-RAPI"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=lkeu_rapi
DB_USERNAME=your-username
DB_PASSWORD=your-secure-password

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

### Optimisasi

```bash
# Install dependencies tanpa dev
composer install --optimize-autoloader --no-dev

# Build assets
npm run build

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Server Requirements

### Nginx Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    root /var/www/lkeu-rapi/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Apache Configuration

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/lkeu-rapi/public

    <Directory /var/www/lkeu-rapi/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/lkeu-rapi-error.log
    CustomLog ${APACHE_LOG_DIR}/lkeu-rapi-access.log combined
</VirtualHost>
```

## Deployment Steps

### 1. Upload Files

```bash
# Via Git
git clone https://github.com/sekadau-online/report.git /var/www/lkeu-rapi

# Via SCP/SFTP
scp -r . user@server:/var/www/lkeu-rapi
```

### 2. Install Dependencies

```bash
cd /var/www/lkeu-rapi
composer install --optimize-autoloader --no-dev
npm install && npm run build
```

### 3. Environment Setup

```bash
cp .env.example .env
nano .env  # Edit sesuai production
php artisan key:generate
```

### 4. Database Setup

```bash
php artisan migrate --force
php artisan db:seed --class=SiteSettingSeeder --force
```

### 5. Storage & Permissions

```bash
php artisan storage:link

chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 6. Cache & Optimize

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## SSL dengan Let's Encrypt

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

## Supervisor (Queue Worker)

Jika menggunakan queues:

```ini
[program:lkeu-rapi-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/lkeu-rapi/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/lkeu-rapi/storage/logs/worker.log
stopwaitsecs=3600
```

## Cron (Task Scheduler)

```bash
crontab -e
```

Tambahkan:
```
* * * * * cd /var/www/lkeu-rapi && php artisan schedule:run >> /dev/null 2>&1
```

## Zero-Downtime Deployment

### Dengan Envoyer/Deployer

```php
// deploy.php (Deployer)
namespace Deployer;

require 'recipe/laravel.php';

host('your-server.com')
    ->set('remote_user', 'deployer')
    ->set('deploy_path', '/var/www/lkeu-rapi');

after('deploy:symlink', 'artisan:optimize');
```

### Dengan GitHub Actions

```yaml
# .github/workflows/deploy.yml
name: Deploy

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      
      - name: Deploy to server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.HOST }}
          username: ${{ secrets.USERNAME }}
          key: ${{ secrets.SSH_KEY }}
          script: |
            cd /var/www/lkeu-rapi
            git pull origin main
            composer install --no-dev
            npm install && npm run build
            php artisan migrate --force
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
```

## Monitoring

### Laravel Telescope (Development)

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### Health Check

```php
// routes/web.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
    ]);
});
```

## Backup

### Database Backup

```bash
mysqldump -u username -p lkeu_rapi > backup.sql
```

### Storage Backup

```bash
tar -czf storage-backup.tar.gz storage/app/public
```

## Rollback

```bash
# Rollback migrations
php artisan migrate:rollback --step=1

# Git rollback
git revert HEAD
git push origin main
```

## Troubleshooting

### 500 Error

```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear cache
php artisan optimize:clear
```

### Permission Issues

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Database Connection

```bash
php artisan tinker
>>> DB::connection()->getPdo()
```

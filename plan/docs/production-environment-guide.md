# Production Environment Configuration Guide

## 1. System Requirements
- **Operating System:** Linux (Ubuntu 22.04 / 24.04 LTS or Debian 12 recommended)
- **Web Server:** Nginx 1.24+ or Apache 2.4+
- **PHP:** PHP 8.3 or higher (PHP 8.3-FPM)
  - Required Extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `json`, `mbstring`, `openssl`, `pcre`, `pdo_mysql`, `tokenizer`, `xml`, `zip`
  - Recommended `php.ini` Settings:
    - `memory_limit = 256M`
    - `upload_max_filesize = 20M`
    - `post_max_size = 25M`
    - `max_execution_time = 60`
    - `opcache.enable = 1`
    - `opcache.memory_consumption = 128`
    - `opcache.max_accelerated_files = 10000`
    - `opcache.validate_timestamps = 0` (in production)
- **Database:** MySQL 8.0+ or MariaDB 10.11+
  - Default character set: `utf8mb4`
  - Collation: `utf8mb4_unicode_ci`
- **Node.js:** Node.js 20+ LTS (for asset compilation during deployment pipeline)

---

## 2. Environment Variables (.env.production)

```ini
APP_NAME="Daily Reporting System"
APP_ENV=production
APP_KEY=base64:... # Generated via `php artisan key:generate`
APP_DEBUG=false
APP_URL=https://reports.yourhotelgroup.com

LOG_CHANNEL=daily
LOG_LEVEL=warning
LOG_DEPRECATIONS_CHANNEL=null

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=daily_report_prod
DB_USERNAME=report_app_user
DB_PASSWORD="STRONG_RANDOM_PASSWORD"

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=database

FILESYSTEM_DISK=local
```

---

## 3. Directory Permissions

Ensure the web server user (`www-data`) owns the storage and bootstrap cache directories:

```bash
sudo chown -R www-data:www-data /var/www/daily-report
sudo find /var/www/daily-report -type f -exec chmod 644 {} \;
sudo find /var/www/daily-report -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/daily-report/storage
sudo chmod -R 775 /var/www/daily-report/bootstrap/cache
```

---

## 4. Nginx Server Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name reports.yourhotelgroup.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name reports.yourhotelgroup.com;

    root /var/www/daily-report/public;
    index index.php index.html;

    ssl_certificate /etc/letsencrypt/live/reports.yourhotelgroup.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/reports.yourhotelgroup.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval';" always;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Static assets caching
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, no-transform";
    }
}
```

---

## 5. Optimization Commands for Production

Run these during the deployment pipeline:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

# Production Deployment Guide

This guide details the deployment procedures, continuous delivery pipeline steps, zero-downtime release strategies, and rollback protocols for the **Daily Reporting System**.

---

## 1. Pre-Deployment Checklist

Before deploying a new release to production:

- [ ] All automated tests pass: `php artisan test` (103+ tests passing).
- [ ] Frontend build succeeds without warnings: `npm run build`.
- [ ] Database migrations are backwards-compatible and non-destructive.
- [ ] Production `.env` is configured with `APP_ENV=production` and `APP_DEBUG=false`.
- [ ] Required PHP 8.3 extensions are active (`pdo_mysql`, `mbstring`, `gd`, `zip`, `xml`, `bcmath`).
- [ ] Secure file permissions are set for `storage/` and `bootstrap/cache/` (`775`).
- [ ] Backup snapshot created using `/usr/local/bin/backup-daily-reports.sh`.

---

## 2. Standard Production Deployment Procedure

For single-server deployments (e.g. Ubuntu LTS + Nginx + PHP-FPM):

### Step 1: Put Application into Maintenance Mode
```bash
cd /var/www/daily-report
php artisan down --secret="ops-emergency-bypass-2026" --retry=60
```
*Note: The `--secret` flag allows administrators to bypass maintenance mode by visiting `https://your-domain.com/ops-emergency-bypass-2026`.*

### Step 2: Pull Latest Stable Release
```bash
git fetch origin master
git reset --hard origin/master
```

### Step 3: Install Backend Dependencies
```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
```

### Step 4: Compile Frontend Assets
```bash
npm ci
npm run build
```

### Step 5: Run Database Migrations
```bash
php artisan migrate --force
```
*(Always execute with `--force` in production. Migrations are strictly non-destructive).*

### Step 6: Clear and Warm Caches
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Step 7: Fix Permissions
```bash
sudo chown -R www-data:www-data /var/www/daily-report/storage /var/www/daily-report/bootstrap/cache
sudo chmod -R 775 /var/www/daily-report/storage /var/www/daily-report/bootstrap/cache
```

### Step 8: Restart Background Services & Workers
```bash
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx
php artisan queue:restart
```

### Step 9: Bring Application Back Online
```bash
php artisan up
```

---

## 3. Zero-Downtime Deployment Strategy (Symlink / Deployer)

For mission-critical 24/7 hotel operations, use atomic symlink-based deployment:

### Directory Architecture
```
/var/www/daily-report/
├── current -> /var/www/daily-report/releases/20261124180000/
├── shared/
│   ├── .env
│   ├── storage/
│   │   ├── app/
│   │   ├── framework/
│   │   └── logs/
└── releases/
    ├── 20261124170000/
    └── 20261124180000/
```

### Deployment Script (`deploy.sh`)
```bash
#!/usr/bin/env bash
set -eo pipefail

BASE_DIR="/var/www/daily-report"
SHARED_DIR="${BASE_DIR}/shared"
RELEASE_DIR="${BASE_DIR}/releases/$(date +%Y%m%d%H%M%S)"
GIT_REPO="git@github.com:your-org/daily-report.git"
BRANCH="master"

echo "=== Deploying release to ${RELEASE_DIR} ==="

# 1. Clone repository into new release folder
git clone --depth 1 --branch "${BRANCH}" "${GIT_REPO}" "${RELEASE_DIR}"
cd "${RELEASE_DIR}"

# 2. Link shared files and persistent storage
rm -rf storage
ln -s "${SHARED_DIR}/.env" .env
ln -s "${SHARED_DIR}/storage" storage

# 3. Install composer dependencies (production optimized)
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# 4. Build assets
npm ci
npm run build

# 5. Database migrations
php artisan migrate --force

# 6. Warm application caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Atomic symlink swap
ln -sfn "${RELEASE_DIR}" "${BASE_DIR}/current"

# 8. Reload PHP-FPM and restart queues
sudo systemctl reload php8.3-fpm
php artisan queue:restart

# 9. Clean up old releases (keep last 5)
cd "${BASE_DIR}/releases"
ls -dt */ | tail -n +6 | xargs -r rm -rf

echo "=== Deployment finished successfully at $(date) ==="
```

---

## 4. Rollback Procedure

If a critical regression is detected post-deployment:

### With Symlink Deployment:
```bash
# 1. Point current symlink back to previous release
cd /var/www/daily-report
PREVIOUS_RELEASE=$(ls -dt releases/*/ | sed -n '2p')
ln -sfn "/var/www/daily-report/${PREVIOUS_RELEASE}" current

# 2. Reload services
sudo systemctl reload php8.3-fpm
php artisan queue:restart
```

### With Standard Deployment:
```bash
# 1. Put into maintenance
php artisan down --secret="ops-rollback"

# 2. Check out previous commit
git checkout HEAD~1

# 3. Re-run dependencies and caches
composer install --no-dev --optimize-autoloader
npm run build
php artisan optimize:clear
php artisan optimize

# 4. Bring up
php artisan up
```

---

## 5. Post-Deployment Verification & Smoke Testing

Immediately after deployment, perform these verification checks:

1. **HTTP Status & Health:**
   ```bash
   curl -I https://reports.yourhotelgroup.com/login
   # Expected: HTTP/2 200
   ```

2. **Database Connectivity:**
   ```bash
   php artisan db:monitor
   ```

3. **Core Workflow Verification:**
   - Log in as Report Officer (`staff@hotel.com`).
   - Navigate to **Daily Reports** (`/reports`).
   - Click **Create Today's Report** (`/reports/create`).
   - Confirm Wizard renders 5 tabs in order:
     - General Information (Today's Overview + Forecast for Tomorrow)
     - Guest Experience & Concern
     - Departmental Activities (6 departments including Housekeeping)
     - Operational Updates
     - Activities and Works Plan
   - Test Auto-Save and Sentence Capitalization on paste.
   - Click **Review Report** and verify consolidated document preview.
   - Finalize report and test both **Export DOCX** and **Export PDF** downloads.

4. **Log Inspection:**
   ```bash
   tail -n 100 /var/www/daily-report/storage/logs/laravel.log
   ```

# Database & File Storage Backup Guide

This guide describes operational backup strategies, recovery procedures, and automation scripts for the Daily Reporting System.

---

## 1. Backup Strategy Overview

| Asset | Frequency | Retention | Storage Location |
|---|---|---|---|
| **MySQL Database** | Daily full backup + hourly incremental (binary logs) | 30 days daily, 12 months monthly | Remote S3-compatible cold storage (encrypted) |
| **User File Storage (`storage/app`)** | Daily sync | 30 days | Remote encrypted bucket |
| **Application Config (`.env`, certificates)** | On change | Indefinite (versioned vault) | Encrypted credential vault |

---

## 2. Automated MySQL Database Backup Script

Save this script on the server at `/usr/local/bin/backup-daily-reports.sh`:

```bash
#!/usr/bin/env bash
set -eo pipefail

BACKUP_DIR="/var/backups/daily-reports"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
DB_NAME="daily_report_prod"
DB_USER="backup_user"
DB_PASS="SECURE_BACKUP_PASSWORD"
BACKUP_FILE="${BACKUP_DIR}/db_${DB_NAME}_${TIMESTAMP}.sql.gz"

mkdir -p "${BACKUP_DIR}"

echo "[$(date)] Starting backup of ${DB_NAME}..."

# Export database with single-transaction (no locking) and compression
mysqldump \
    --user="${DB_USER}" \
    --password="${DB_PASS}" \
    --single-transaction \
    --quick \
    --routines \
    --triggers \
    --default-character-set=utf8mb4 \
    "${DB_NAME}" | gzip -9 > "${BACKUP_FILE}"

echo "[$(date)] Database backup completed: ${BACKUP_FILE} ($(du -h ${BACKUP_FILE} | cut -f1))"

# Prune local backups older than 14 days
find "${BACKUP_DIR}" -name "db_${DB_NAME}_*.sql.gz" -mtime +14 -delete

echo "[$(date)] Local cleanup complete."
```

Make executable and schedule in cron (`crontab -e`):

```cron
# Run daily at 02:00 AM server time
0 2 * * * /usr/local/bin/backup-daily-reports.sh >> /var/log/backup-reports.log 2>&1
```

---

## 3. Storage & Artifacts Sync

If generated export artifacts are saved to disk, sync `storage/app` to cloud storage (e.g., AWS S3 or Cloudflare R2):

```bash
# Using AWS CLI
aws s3 sync /var/www/daily-report/storage/app s3://hotel-reports-backup/storage-app/ --delete --sse AES256
```

---

## 4. Disaster Recovery & Restoration Procedure

To restore a database backup in an emergency:

1. **Put Application in Maintenance Mode:**
   ```bash
   php artisan down --secret="emergency-recovery-bypass"
   ```

2. **Decompress and Import MySQL Dump:**
   ```bash
   gunzip < /var/backups/daily-reports/db_daily_report_prod_20261124_020000.sql.gz | mysql -u root -p daily_report_prod
   ```

3. **Run Any Pending Migrations:**
   ```bash
   php artisan migrate --force
   ```

4. **Clear and Rebuild Cache:**
   ```bash
   php artisan optimize:clear
   php artisan optimize
   ```

5. **Bring Application Back Up:**
   ```bash
   php artisan up
   ```

6. **Verify Data Integrity:**
   - Log in as admin.
   - Verify report counts in dashboard.
   - Verify recent report preview and downloads.

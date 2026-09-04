#!/usr/bin/env bash
# ==============================================================================
# Database Backup Script for Physics Wall Magazine (phy_mag_26)
# ==============================================================================
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKUP_DIR="${PROJECT_DIR}/backups"
TIMESTAMP="$(date +"%Y%m%d_%H%M%S")"

mkdir -p "${BACKUP_DIR}"

# Source .env
if [ -f "${PROJECT_DIR}/.env" ]; then
    # Export non-comment lines
    export $(grep -v '^#' "${PROJECT_DIR}/.env" | xargs)
fi

DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-rkmuser}"
DB_PASS="${DB_PASS:-}"
DB_NAME="${DB_NAME:-phy_mag_db}"

BACKUP_FILE="${BACKUP_DIR}/phy_mag_db_${TIMESTAMP}.sql.gz"

echo "[INFO] Backing up database '${DB_NAME}' to ${BACKUP_FILE}..."

if [ -n "${DB_PASS}" ]; then
    MYSQL_PWD="${DB_PASS}" mysqldump -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" "${DB_NAME}" | gzip > "${BACKUP_FILE}"
else
    mysqldump -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" "${DB_NAME}" | gzip > "${BACKUP_FILE}"
fi

echo "[SUCCESS] Backup created successfully: ${BACKUP_FILE} ($(du -h "${BACKUP_FILE}" | cut -f1))"

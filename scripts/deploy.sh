#!/usr/bin/env bash
# ==============================================================================
# Automated Deployment Script for Physics Wall Magazine (phy_mag_26)
# ==============================================================================
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${PROJECT_DIR}"

DO_PULL=false
for arg in "$@"; do
    case "${arg}" in
        --pull|-p)
            DO_PULL=true
            shift
            ;;
    esac
done

echo "========================================================"
echo " Starting Deployment: Physics Wall Magazine 2026"
echo " Target Directory: ${PROJECT_DIR}"
echo " Date: $(date)"
echo "========================================================"

function run_sudo() {
    if [ "$EUID" -eq 0 ]; then
        "$@"
    else
        sudo "$@"
    fi
}

# 1. Optional Git Pull
if [ "${DO_PULL}" = true ]; then
    echo "[1/6] Pulling latest changes from git repository..."
    git pull
else
    echo "[1/6] Skipping git pull (use --pull to fetch latest code)."
fi

# 2. Syntax Check PHP Files
echo "[2/6] Validating PHP files syntax..."
while IFS= read -r -d '' file; do
    php -l "${file}" >/dev/null
done < <(find "${PROJECT_DIR}" -type f -name "*.php" -not -path "*/.git/*" -print0)
echo "  ✓ All PHP files passed syntax verification."

# 3. Environment & Database Migration
echo "[3/6] Running database verification and migrations..."
if [ ! -f "${PROJECT_DIR}/.env" ]; then
    if [ -f "${PROJECT_DIR}/.env.example" ]; then
        echo "  - Creating .env from .env.example..."
        cp "${PROJECT_DIR}/.env.example" "${PROJECT_DIR}/.env"
    else
        echo "[ERROR] Missing .env file!" >&2
        exit 1
    fi
fi
php "${PROJECT_DIR}/install.php"

# 4. File Ownership & Permissions
echo "[4/6] Setting optimal permissions and ownership..."
RUNNING_USER="$(whoami)"
run_sudo chown -R "${RUNNING_USER}:www-data" "${PROJECT_DIR}"
find "${PROJECT_DIR}" -type d -exec chmod 755 {} +
find "${PROJECT_DIR}" -type f -exec chmod 644 {} +
find "${PROJECT_DIR}/scripts" -type f -name "*.sh" -exec chmod 755 {} +
if [ -d "${PROJECT_DIR}/assets" ]; then
    run_sudo chmod -R 775 "${PROJECT_DIR}/assets"
fi
echo "  ✓ Permissions applied."

# 5. Apache Syntax Check & Reload
echo "[5/6] Checking Apache configuration..."
run_sudo apache2ctl configtest
echo "  ✓ Apache configuration syntax is OK."

echo "  - Gracefully reloading Apache HTTP server..."
run_sudo systemctl reload apache2

# 6. Service Health Check
echo "[6/6] Running health check..."
PORT="8080"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:${PORT}/" || echo "000")

if [ "${HTTP_CODE}" == "200" ] || [ "${HTTP_CODE}" == "302" ]; then
    echo -e "  \e[32m✓ Health check PASSED (HTTP ${HTTP_CODE})\e[0m"
else
    echo -e "  \e[33m! Warning: Health check returned HTTP ${HTTP_CODE} on port ${PORT}\e[0m"
fi

echo "========================================================"
echo -e "\e[32m Deployment Succeeded!\e[0m"
echo " Live URL (Local): http://localhost:${PORT}/"
echo " Admin Portal:     http://localhost:${PORT}/admin/"
echo "========================================================"

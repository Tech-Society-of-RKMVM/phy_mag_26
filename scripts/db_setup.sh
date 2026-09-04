#!/usr/bin/env bash
# ==============================================================================
# Database Setup & Migration Script for Physics Wall Magazine (phy_mag_26)
# ==============================================================================
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${PROJECT_DIR}"

echo "[INFO] Running database setup and migrations for phy_mag_26..."

# Verify PHP is available
if ! command -v php >/dev/null 2>&1; then
    echo "[ERROR] PHP is not installed or not in PATH." >&2
    exit 1
fi

# Verify .env exists
if [ ! -f "${PROJECT_DIR}/.env" ]; then
    if [ -f "${PROJECT_DIR}/.env.example" ]; then
        echo "[WARN] .env not found. Copying .env.example to .env..."
        cp "${PROJECT_DIR}/.env.example" "${PROJECT_DIR}/.env"
    else
        echo "[ERROR] .env file not found in ${PROJECT_DIR}." >&2
        exit 1
    fi
fi

# Run PHP installer / migrator CLI
php "${PROJECT_DIR}/install.php"

echo "[SUCCESS] Database setup and schema verification completed successfully."

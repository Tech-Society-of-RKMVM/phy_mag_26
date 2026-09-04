#!/usr/bin/env bash
# ==============================================================================
# Subdomain Setup Script for physics.magazine.rkmvmfamily.in
# ==============================================================================
set -euo pipefail

DOMAIN="physics.magazine.rkmvmfamily.in"
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
CONF_SRC="${PROJECT_DIR}/scripts/apache/${DOMAIN}.conf"
CONF_DEST="/etc/apache2/sites-available/${DOMAIN}.conf"

function run_sudo() {
    if [ "$EUID" -eq 0 ]; then
        "$@"
    else
        sudo "$@"
    fi
}

echo "========================================================"
echo " Setting up Apache VirtualHost: ${DOMAIN}"
echo "========================================================"

if [ ! -f "${CONF_SRC}" ]; then
    echo "[ERROR] Source config not found: ${CONF_SRC}" >&2
    exit 1
fi

echo "[1/4] Installing VirtualHost configuration to Apache..."
run_sudo cp "${CONF_SRC}" "${CONF_DEST}"
run_sudo chmod 644 "${CONF_DEST}"
echo "  ✓ Installed to ${CONF_DEST}"

echo "[2/4] Enabling site ${DOMAIN}..."
run_sudo a2ensite "${DOMAIN}.conf"

echo "[3/4] Testing Apache configuration..."
run_sudo apache2ctl configtest

echo "[4/4] Gracefully reloading Apache HTTP server..."
run_sudo systemctl reload apache2

echo "========================================================"
echo " Site enabled successfully!"
echo " HTTP Endpoint: http://${DOMAIN}/"
echo "========================================================"

echo ""
echo "Checking DNS resolution for ${DOMAIN}..."
if host "${DOMAIN}" >/dev/null 2>&1; then
    echo -e "  \e[32m✓ DNS resolves successfully!\e[0m"
    echo ""
    read -r -p "Would you like to provision an SSL certificate with Let's Encrypt (Certbot) now? [y/N]: " RESP
    if [[ "${RESP}" =~ ^[Yy]$ ]]; then
        echo "Running Certbot..."
        run_sudo certbot --apache -d "${DOMAIN}"
        echo "  ✓ HTTPS SSL setup complete!"
    else
        echo "Skipping SSL setup. You can run later: sudo certbot --apache -d ${DOMAIN}"
    fi
else
    echo -e "  \e[33m! Warning: DNS for ${DOMAIN} is not resolving yet.\e[0m"
    echo "  Once you add the 'A' record (IP: 187.127.145.79) in your DNS dashboard,"
    echo "  run the following command to acquire the Let's Encrypt SSL certificate:"
    echo "  sudo certbot --apache -d ${DOMAIN}"
fi
echo "========================================================"

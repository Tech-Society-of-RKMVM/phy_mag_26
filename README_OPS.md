# Operations & Deployment Guide: Physics Wall Magazine 2026 (`phy_mag_26`)

This document outlines the operational commands, service management, database maintenance, and deployment workflows for the Physics Department Wall Magazine 2026 application.

---

## 1. Hosting Architecture & URLs

- **Primary Subdomain:** `https://physics.magazine.rkmvmfamily.in/`
- **Web Server:** Apache 2.4 (PHP 8.3 Module)
- **DocumentRoot:** `/var/www/phy_mag_26`
- **Apache VirtualHost:** `/etc/apache2/sites-available/physics.magazine.rkmvmfamily.in.conf`
- **Local Fallback Port:** `8080` (`/etc/apache2/sites-available/phy_mag_26.conf`)

### Access Endpoints
- **Production URL:** [https://physics.magazine.rkmvmfamily.in/](https://physics.magazine.rkmvmfamily.in/)
- **Production Admin:** [https://physics.magazine.rkmvmfamily.in/admin/](https://physics.magazine.rkmvmfamily.in/admin/)
- **Local Port Fallback:** [http://localhost:8080/](http://localhost:8080/)
- **Default Admin Username:** `admin`
- **Default Admin Password:** `admin123`

---

## 2. Automated Service Management (On / Off / Restart / Status)

Use `./scripts/service_control.sh` to control the services (`apache2` and `mysql`):

```bash
# Check status of Apache2 and MySQL + active ports
./scripts/service_control.sh status

# Turn OFF all services (stops Apache2 first, then MySQL)
./scripts/service_control.sh stop

# Turn ON all services (starts MySQL first, then Apache2)
./scripts/service_control.sh start

# Restart all services
./scripts/service_control.sh restart

# Gracefully reload Apache2
./scripts/service_control.sh reload apache2

# Control specific service only:
./scripts/service_control.sh stop apache2
./scripts/service_control.sh start apache2
./scripts/service_control.sh restart mysql
```

---

## 3. Automated One-Click Deployment

To deploy updates, run the automated zero-downtime deployment script:

```bash
# Standard deployment (syntax check, migrations, permissions, reload, health check)
./scripts/deploy.sh

# Deployment with automated Git pull:
./scripts/deploy.sh --pull
```

### What `deploy.sh` does:
1. *(Optional)* Pulls latest commits from Git branch.
2. Performs PHP syntax linting (`php -l`) across all project files to prevent parse errors.
3. Ensures `.env` is present and runs database migration and table seed checks (`install.php`).
4. Corrects file permissions (`755` for directories, `644` for files, `775` for `assets/`, `755` for `scripts/`).
5. Validates Apache configuration syntax (`apache2ctl configtest`).
6. Gracefully reloads Apache HTTP server without dropping active client connections.
7. Executes an automated HTTP health check against `http://localhost:8080/`.

---

## 4. Database Administration & Backups

The application connects to MySQL via PDO with settings configured in `.env`:

```ini
DB_HOST=localhost
DB_PORT=3306
DB_USER=rkmuser
DB_PASS=Rkmvm#6202
DB_NAME=phy_mag_db
```

### Database Utilities
```bash
# Run schema migration & seed verification
./scripts/db_setup.sh

# Create a timestamped, gzipped database backup into backups/
./scripts/db_backup.sh
```

---

## 5. Apache Configuration Reference & Subdomain Setup
 
For reference, Apache configuration templates are tracked in `scripts/apache/`:
- `scripts/apache/physics.magazine.rkmvmfamily.in.conf`: Subdomain VirtualHost (`*:80` and ready for Certbot SSL)
- `scripts/apache/phy_mag_ports.conf`: Declares `Listen 8080`
- `scripts/apache/phy_mag_26.conf`: Declares `<VirtualHost *:8080>` as local fallback
 
### Activating the Subdomain
Run the automated script with sudo:
```bash
sudo ./scripts/setup_subdomain.sh
```
Or manually:
```bash
sudo cp scripts/apache/physics.magazine.rkmvmfamily.in.conf /etc/apache2/sites-available/
sudo a2ensite physics.magazine.rkmvmfamily.in.conf
sudo apache2ctl configtest
sudo systemctl reload apache2

# Acquire Let's Encrypt SSL certificate (after DNS 'A' record is added)
sudo certbot --apache -d physics.magazine.rkmvmfamily.in
```

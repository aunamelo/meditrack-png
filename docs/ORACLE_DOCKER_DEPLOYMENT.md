# MediTrack PNG — Oracle VPS Docker Deployment (Full Reference)

Complete step-by-step guide to deploy **MediTrack PNG** on an **Oracle Cloud VPS** using **Docker Compose**.

Use this document as your long-term reference for first-time setup, updates, migrations, and troubleshooting.

---

## Table of contents

1. [Architecture](#1-architecture)
2. [What you need](#2-what-you-need)
3. [Oracle Cloud networking](#3-oracle-cloud-networking)
4. [SSH into the VPS](#4-ssh-into-the-vps)
5. [Install Docker](#5-install-docker)
6. [Clone the repository](#6-clone-the-repository)
7. [Configure `.env`](#7-configure-env)
8. [Build and start](#8-build-and-start)
9. [Verify the deployment](#9-verify-the-deployment)
10. [Sign in (demo accounts)](#10-sign-in-demo-accounts)
11. [After first deploy](#11-after-first-deploy)
12. [Sync local changes to Oracle](#12-sync-local-changes-to-oracle)
13. [Adding a new database table](#13-adding-a-new-database-table)
14. [Useful Docker commands](#14-useful-docker-commands)
15. [Troubleshooting](#15-troubleshooting)
16. [Optional: HTTPS / domain](#16-optional-https--domain)
17. [Quick copy-paste checklist](#17-quick-copy-paste-checklist)

---

## 1. Architecture

```text
Internet (port 80)
        │
        ▼
┌──────────────────────────────────┐
│  web container                   │
│  Nginx + PHP 8.3-FPM + Laravel   │
│  (assets built in Docker image)  │
└────────────────┬─────────────────┘
                 │
                 ▼
┌──────────────────────────────────┐
│  mysql container                 │
│  MySQL 8 (meditrack_png)         │
└──────────────────────────────────┘
```

| Service | Role |
|---------|------|
| `web` | Serves MediTrack (Nginx + PHP + Laravel) |
| `mysql` | Application database |

**Persistent Docker volumes:**

| Volume | Stores |
|--------|--------|
| `mysql_data` | Database data |
| `app_storage` | Uploaded files (e.g. profile photos) |

**Key project files:**

| File | Purpose |
|------|---------|
| `Dockerfile` | Builds frontend assets + PHP/Nginx image |
| `docker-compose.yml` | Runs `web` + `mysql` |
| `.env.docker.example` | Production env template |
| `docker/entrypoint.sh` | Waits for MySQL, migrates, seeds, caches, then starts services |
| `docker/nginx/default.conf` | Nginx config |
| `docker/php/zz-meditrack.conf` | PHP-FPM keeps container env vars |

On container start, `entrypoint.sh` automatically:

1. Ensures `.env` exists  
2. Generates `APP_KEY` if missing  
3. Waits for MySQL  
4. Runs `php artisan migrate --force`  
5. Seeds if `RUN_SEED=true`  
6. Caches config/routes/views  
7. Starts Nginx + PHP-FPM  

---

## 2. What you need

- Oracle Cloud **Compute** instance (Ubuntu 22.04/24.04 recommended)
- **Public IP** (example used in this project: `149.118.69.130`)
- SSH access (PuTTY / OpenSSH)
- GitHub repo: `https://github.com/aunamelo/meditrack-png.git`
- Ports **22** (SSH) and **80** (HTTP) open

---

## 3. Oracle Cloud networking

### 3.1 Security list ingress rules

In **Oracle Cloud Console → Networking → VCN → Security Lists**, allow:

| Source CIDR | Protocol | Port | Purpose |
|-------------|----------|------|---------|
| `0.0.0.0/0` | TCP | 22 | SSH |
| `0.0.0.0/0` | TCP | 80 | HTTP (MediTrack) |
| `0.0.0.0/0` | TCP | 443 | HTTPS (optional later) |

Also confirm the security list is attached to the subnet used by your instance.

### 3.2 OS firewall (on the VPS)

Oracle Ubuntu images often use `iptables`. After SSH:

```bash
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 80 -j ACCEPT
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 443 -j ACCEPT
sudo apt install -y iptables-persistent
sudo netfilter-persistent save
```

---

## 4. SSH into the VPS

### OpenSSH (PowerShell / terminal)

```bash
ssh -i path/to/your-key.pem ubuntu@YOUR_PUBLIC_IP
```

### PuTTY

1. Host: `YOUR_PUBLIC_IP`  
2. Port: `22`  
3. Connection type: SSH  
4. Username: `ubuntu` (Oracle Linux may use `opc`)  
5. Load your converted `.ppk` private key under **Connection → SSH → Auth**

If SSH hangs at `Using username "ubuntu".`:

- Confirm the instance is **Running**
- Confirm port **22** is open
- Confirm the public IP has not changed after a stop/start
- The VM may be low on RAM after a Docker build — wait a few minutes and retry

---

## 5. Install Docker

On the VPS:

```bash
sudo apt update
sudo apt upgrade -y
sudo apt install -y git ca-certificates curl docker.io docker-compose-v2

sudo systemctl enable docker
sudo systemctl start docker
sudo usermod -aG docker $USER
```

Log out and SSH back in, then verify:

```bash
docker --version
docker compose version
```

If you deploy as `root`, the docker group step is optional.

---

## 6. Clone the repository

```bash
cd ~
git clone https://github.com/aunamelo/meditrack-png.git
cd meditrack-png
git pull origin main
```

Confirm Docker files exist:

```bash
ls -la Dockerfile docker-compose.yml .env.docker.example docker/
```

---

## 7. Configure `.env`

```bash
cp .env.docker.example .env
nano .env
```

### Required values

| Variable | Example | Notes |
|----------|---------|-------|
| `APP_URL` | `http://149.118.69.130` | Your public IP or domain. No trailing slash. |
| `DB_PASSWORD` | strong password | App DB user password |
| `DB_ROOT_PASSWORD` | strong root password | MySQL root password |
| `RUN_SEED` | `true` | First deploy only |
| `HTTP_PORT` | `80` | Host HTTP port (Caddy; redirects to HTTPS) |
| `HTTPS_PORT` | `443` | Host HTTPS port (Caddy / Let's Encrypt) |
| `DOMAIN` | `meditrackpng.duckdns.org` | Public hostname for TLS certificates |
| `ACME_EMAIL` | your email | Let's Encrypt contact email |

### Keep these as-is for Docker

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=meditrack_png
DB_USERNAME=meditrack
APP_ENV=production
APP_DEBUG=false
```

`DB_HOST=mysql` must match the Compose service name.

### Example production block

```env
APP_NAME="MediTrack PNG"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://149.118.69.130

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=meditrack_png
DB_USERNAME=meditrack
DB_PASSWORD=MyStrongDbPass123!
DB_ROOT_PASSWORD=MyStrongRootPass456!

RUN_SEED=true
HTTP_PORT=80
```

Save in nano: **Ctrl+O**, Enter, **Ctrl+X**.

### Important: `.env` must be a file

```bash
ls -la .env
```

If `.env` is a **directory** (Docker bind-mount bug), fix it:

```bash
rm -rf .env
cp .env.docker.example .env
nano .env
```

---

## 8. Build and start

```bash
cd ~/meditrack-png
docker compose up -d --build
```

First build usually takes **5–15 minutes** (Node build + Composer + image layers).

Watch logs:

```bash
docker compose logs -f web
```

Healthy startup looks like:

```text
==> MediTrack entrypoint starting
==> Waiting for MySQL
==> MySQL is ready
==> Running migrations
==> Seeding database
==> Caching configuration
==> Starting web server
```

Press **Ctrl+C** to stop watching (containers keep running).

---

## 9. Verify the deployment

```bash
docker compose ps
```

Expected:

| Name | Status |
|------|--------|
| `meditrack-png-mysql-1` | `Up` / `healthy` |
| `meditrack-png-web-1` | `Up` (not `Restarting`) |

Local HTTP check:

```bash
curl -I http://localhost
```

Expect `HTTP/1.1 200 OK`.

Open in a browser:

```text
http://YOUR_PUBLIC_IP
```

Example:

```text
http://149.118.69.130
```

---

## 10. Sign in (demo accounts)

Password for all accounts: **`password`**

| Role | Email |
|------|-------|
| NDoH Admin | `admin@health.gov.pg` |
| Procurement Officer | `procurement@health.gov.pg` |
| Store Manager (Lae AMS) | `manager@lae-ams.health.gov.pg` |
| Pharmacy Manager (Modilon) | `pharmacy.manager@modilon.gov.pg` |
| Pharmacist (Modilon) | `pharmacist@modilon.gov.pg` |

1. Choose the matching role on the home page  
2. Sign in with the matching email and `password`

---

## 11. After first deploy

Turn seeding off so restarts do not re-seed:

```bash
nano .env
```

Change:

```env
RUN_SEED=false
```

Apply:

```bash
docker compose up -d
```

---

## 12. Sync local changes to Oracle

### On your PC (Cursor)

1. Make code changes  
2. Commit and push:

```bash
git add .
git commit -m "Describe your change"
git push origin main
```

### On Oracle VPS

```bash
cd ~/meditrack-png
git pull origin main
docker compose up -d --build
docker compose logs -f web
```

### What syncs / what does not

| Syncs via GitHub | Does **not** sync |
|------------------|-------------------|
| Application code | Local MySQL data |
| Migrations / seeders | Local `.env` |
| Docker files | Uploaded files on your laptop |
| Blade / CSS / JS | |

Each environment has its **own database** and **own `.env`**.

---

## 13. Adding a new database table

Always use a **Laravel migration** — do not create tables only by hand in MySQL.

### On your PC

```bash
php artisan make:migration create_example_table
# edit the migration file
php artisan migrate
```

Then:

```bash
git add database/migrations app/
git commit -m "Add example table"
git push origin main
```

### On Oracle

```bash
cd ~/meditrack-png
git pull origin main
docker compose up -d --build
```

`entrypoint.sh` runs `php artisan migrate --force` on start, so the new table is created automatically.

To migrate without a full rebuild (if only PHP/migration files changed and image is current enough):

```bash
docker compose exec web php artisan migrate --force
```

For most updates, prefer `git pull` + `docker compose up -d --build`.

---

## 14. Useful Docker commands

| Task | Command |
|------|---------|
| Container status | `docker compose ps` |
| Web logs | `docker compose logs -f web` |
| MySQL logs | `docker compose logs -f mysql` |
| Restart all | `docker compose restart` |
| Stop all | `docker compose down` |
| Start (no rebuild) | `docker compose up -d` |
| Rebuild + start | `docker compose up -d --build` |
| Shell into web | `docker compose exec web bash` |
| Run migrations | `docker compose exec web php artisan migrate --force` |
| Clear caches | `docker compose exec web php artisan optimize:clear` |
| Seed users only | `docker compose exec web php artisan db:seed --class=UserSeeder --force` |

### Full reset (destructive)

Deletes database and uploaded files:

```bash
docker compose down -v
cp .env.docker.example .env
nano .env   # set APP_URL, passwords, RUN_SEED=true
docker compose up -d --build
```

---

## 15. Troubleshooting

### Web container status is `Restarting`

```bash
docker compose logs web --tail 80
ls -la .env
```

Common causes:

- `.env` is a directory → `rm -rf .env && cp .env.docker.example .env`
- Missing `DB_PASSWORD` / `DB_ROOT_PASSWORD`
- MySQL not ready yet (wait and check logs)

### HTTP 500 from the site

```bash
docker compose exec web tail -50 storage/logs/laravel.log
docker compose logs web --tail 50
```

Also confirm:

```env
APP_URL=http://YOUR_PUBLIC_IP
DB_HOST=mysql
APP_KEY=base64:...
```

Then:

```bash
docker compose exec web php artisan optimize:clear
docker compose restart web
```

### Browser cannot open the site / timeout

1. Instance is **Running** in Oracle Console  
2. Security list allows TCP **80**  
3. OS firewall allows port **80**  
4. `docker compose ps` shows `web` Up with `0.0.0.0:80->80/tcp`  
5. On VPS: `curl -I http://localhost`

### MySQL stuck / unhealthy / OOM on free tier

Oracle free-tier VMs have limited RAM. Compose already uses low-memory MySQL settings.

```bash
docker compose logs mysql --tail 100
free -h
```

If MySQL keeps crashing, stop unused services or upgrade the shape, then retry:

```bash
docker compose down
docker compose up -d
```

### Build fails on `oniguruma` / `mbstring`

Ensure the latest `Dockerfile` includes `libonig-dev` (already fixed in the repo). Then:

```bash
git pull origin main
docker compose up -d --build
```

### CSS / JS missing

Rebuild the image (Vite assets are built inside Docker):

```bash
docker compose up -d --build
```

### SSH is very slow after deploy

Often RAM pressure. Wait, or use Oracle **Console Connection**, then:

```bash
free -h
docker compose ps
```

---

## 16. HTTPS / domain (Caddy + Let's Encrypt)

MediTrack uses a **Caddy** reverse proxy container for automatic HTTPS.

### Prerequisites

1. Domain **A record** (e.g. DuckDNS) → Oracle public IP  
2. Oracle security list / firewall: allow **TCP 80** and **TCP 443** from `0.0.0.0/0`  
3. DNS already resolving (check with `nslookup meditrackpng.duckdns.org`)

### Configure `.env` on the VPS

```bash
cd /home/ubuntu/meditrack-png
nano .env
```

Set at least:

```env
APP_URL=https://meditrackpng.duckdns.org
DOMAIN=meditrackpng.duckdns.org
ACME_EMAIL=your-real-email@example.com
SESSION_SECURE_COOKIE=true
HTTP_PORT=80
HTTPS_PORT=443
```

`DOMAIN` must match the hostname clients use. `ACME_EMAIL` is used by Let's Encrypt.

### Apply

```bash
git pull origin main   # or: git fetch origin && git reset --hard origin/main
docker compose up -d --build
docker compose ps
docker compose logs -f caddy
```

Wait until Caddy obtains a certificate (look for “certificate obtained successfully”). Then open:

```text
https://meditrackpng.duckdns.org
```

HTTP on port 80 is automatically redirected to HTTPS.

### Laravel cache after URL change

```bash
docker compose exec web php artisan config:clear
docker compose exec web php artisan optimize:clear
docker compose exec web php artisan config:cache
```

### Troubleshooting

| Issue | Check |
|-------|--------|
| Certificate fails | DNS A record, ports 80/443 open, `DOMAIN` exact match |
| Mixed content / login loops | `APP_URL` must be `https://...`, `SESSION_SECURE_COOKIE=true`, then config:clear |
| Caddy not starting | `docker compose logs caddy` |
| Still on HTTP only | Old compose without `caddy` service — pull latest and recreate |

For future Microsoft 365 login, register:

```text
https://meditrackpng.duckdns.org/auth/microsoft/callback
```

Keep local development URI too if needed:

```text
http://localhost:8000/auth/microsoft/callback
```

---

## 17. Quick copy-paste checklist

### First-time deploy

```bash
# SSH into Oracle, then:
sudo apt update
sudo apt install -y git docker.io docker-compose-v2
sudo systemctl enable --now docker

git clone https://github.com/aunamelo/meditrack-png.git
cd meditrack-png
cp .env.docker.example .env
nano .env
# set APP_URL, DB_PASSWORD, DB_ROOT_PASSWORD, RUN_SEED=true

docker compose up -d --build
docker compose logs -f web
docker compose ps
curl -I http://localhost
```

Open `http://YOUR_PUBLIC_IP` → sign in with `admin@health.gov.pg` / `password`.

Then set `RUN_SEED=false` and run `docker compose up -d`.

### Everyday update from your PC

```text
PC:     edit → git commit → git push
Oracle: git pull → docker compose up -d --build
```

---

## Current live example

| Item | Value |
|------|-------|
| Public URL | `https://meditrackpng.duckdns.org` |
| Stack | Docker Compose (`caddy` + `web` + `mysql`) |
| App | MediTrack PNG (Laravel) |
| TLS | Caddy automatic Let's Encrypt |

---

## Related docs

- Demo users / seeding: see project `README.md`
- Microsoft 365 auth planning (if added later): request redirect URI matching your public HTTPS URL

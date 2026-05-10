# E.Events Deployment Guide

This guide covers deploying E.Events to **Render** (recommended for getting started quickly) and **HostAfrica** (for VPS/cPanel hosting). Both use Paystack as the payment gateway.

---

## Architecture Overview

The app has three components that must all run:

| Component | What it is | Port |
|-----------|-----------|------|
| **Backend** | Laravel API (PHP 8.4, Docker) | 8080 |
| **Frontend** | React SSR (Node.js) | 5678 |
| **Worker** | Laravel queue worker (same Docker image) | — |

It also needs:
- **PostgreSQL** database
- **Redis** cache + queue

---

## Part 1 — Deploy to Render

Render is the easiest path. It supports Docker, managed Postgres, managed Redis, and free SSL.

### Step 1 — Push your code to GitHub

If you haven't already:

```bash
# In the project root
git init
git add .
git commit -m "Initial commit — Paystack only, production ready"
git remote add origin https://github.com/YOUR_USERNAME/e-events.git
git push -u origin main
```

> Make sure `.gitignore` excludes `.env` files. The root `.gitignore` should already handle this.

---

### Step 2 — Create a Render account

Go to [https://render.com](https://render.com) and sign up. Connect your GitHub account.

---

### Step 3 — Create the PostgreSQL database

1. In Render dashboard → **New** → **PostgreSQL**
2. Settings:
   - **Name:** `eevents-db`
   - **Database:** `eevents`
   - **User:** `eevents`
   - **Region:** Choose closest to your users (e.g. Frankfurt for Africa)
   - **Plan:** Free (for testing) or Starter ($7/mo for production
3. Click **Create Database**
4. Once created, copy the **Internal Database URL** — you'll need it shortly. It looks like:
   ```
   postgresql://eevents:PASSWORD@dpg-XXXX.internal/eevents
   ```

---

### Step 4 — Create the Redis instance

1. In Render dashboard → **New** → **Redis**
2. Settings:
   - **Name:** `eevents-redis`
   - **Region:** Same as your database
   - **Plan:** Free (for testing) or Starter ($10/mo for production)
3. Click **Create Redis**
4. Copy the **Internal Redis URL** — looks like:
   ```
   redis://red-XXXX.internal:6379
   ```

---

### Step 5 — Deploy the Backend (Web Service)

1. In Render dashboard → **New** → **Web Service**
2. Connect your GitHub repo
3. Settings:
   - **Name:** `eevents-backend`
   - **Region:** Same as database
   - **Branch:** `main`
   - **Root Directory:** `backend`
   - **Runtime:** **Docker**
   - **Dockerfile Path:** `./Dockerfile`
   - **Docker Build Context:** `./` (the backend folder)
   - **Port:** `8080`
   - **Plan:** Starter ($7/mo minimum — free tier won't work for PHP)

4. Under **Environment Variables**, add all of these:

```
APP_NAME=E Events
APP_ENV=production
APP_DEBUG=false
APP_KEY=                          ← generate with: php artisan key:generate --show
APP_URL=https://eevents-backend.onrender.com
APP_FRONTEND_URL=https://eevents-frontend.onrender.com
APP_CDN_URL=https://eevents-backend.onrender.com/storage
APP_SAAS_MODE_ENABLED=false
APP_DISABLE_REGISTRATION=false
APP_PLATFORM_SUPPORT_EMAIL=support@your-domain.com

DB_CONNECTION=pgsql
DATABASE_URL=                     ← paste Internal Database URL from Step 3

REDIS_URL=                        ← paste Internal Redis URL from Step 4
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis

FILESYSTEM_PUBLIC_DISK=public
FILESYSTEM_PRIVATE_DISK=local

JWT_SECRET=                       ← generate: openssl rand -base64 48
JWT_ALGO=HS256

MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com    ← or your SMTP provider
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME=E Events

PAYSTACK_SECRET_KEY=sk_live_your_live_secret_key
PAYSTACK_PUBLIC_KEY=pk_live_your_live_public_key
PAYSTACK_WEBHOOK_SECRET=your_webhook_secret

LOG_CHANNEL=stderr
LOG_LEVEL=error
CORS_ALLOWED_ORIGINS=https://eevents-frontend.onrender.com
```

5. Under **Build & Deploy**:
   - **Build Command:** *(leave empty — Dockerfile handles it)*
   - **Start Command:** *(leave empty — Dockerfile handles it)*

6. Under **Health Check Path:** `/api/public/system-info`

7. Click **Create Web Service**

---

### Step 6 — Run Database Migrations

After the backend deploys successfully:

1. In Render dashboard → your backend service → **Shell** tab
2. Run:
```bash
php artisan migrate --force
php artisan optimize
```

Or add a **Pre-Deploy Command** in the service settings:
```
php artisan migrate --force
```

---

### Step 7 — Deploy the Queue Worker

1. In Render dashboard → **New** → **Background Worker**
2. Settings:
   - **Name:** `eevents-worker`
   - **Region:** Same as backend
   - **Branch:** `main`
   - **Root Directory:** `backend`
   - **Runtime:** **Docker**
   - **Dockerfile Path:** `./Dockerfile`
   - **Start Command:** `php artisan queue:work --sleep=3 --tries=3 --timeout=90`
   - **Plan:** Starter ($7/mo)

3. Add the **exact same environment variables** as the backend (Step 5)

4. Click **Create Background Worker**

---

### Step 8 — Deploy the Frontend (Web Service)

1. In Render dashboard → **New** → **Web Service**
2. Settings:
   - **Name:** `eevents-frontend`
   - **Region:** Same as backend
   - **Branch:** `main`
   - **Root Directory:** `frontend`
   - **Runtime:** **Node**
   - **Build Command:** `yarn install && yarn build`
   - **Start Command:** `yarn start`
   - **Port:** `5678`
   - **Plan:** Starter ($7/mo)

3. Under **Environment Variables**:

```
NODE_ENV=production
VITE_API_URL_CLIENT=https://eevents-backend.onrender.com/api
VITE_API_URL_SERVER=https://eevents-backend.onrender.com/api
VITE_FRONTEND_URL=https://eevents-frontend.onrender.com
VITE_PAYSTACK_PUBLIC_KEY=pk_live_your_live_public_key
VITE_APP_NAME=E Events
VITE_APP_PRIMARY_COLOR=#16A249
VITE_APP_SECONDARY_COLOR=#0D6B47
```

4. Click **Create Web Service**

---

### Step 9 — Configure Paystack Webhook

1. Log in to [Paystack Dashboard](https://dashboard.paystack.com)
2. Go to **Settings** → **API Keys & Webhooks**
3. Under **Webhook URL**, enter:
   ```
   https://eevents-backend.onrender.com/api/public/webhooks/paystack
   ```
4. Copy the **Webhook Secret** and update `PAYSTACK_WEBHOOK_SECRET` in your backend environment variables

---

### Step 10 — Add a Custom Domain (Optional)

For each service in Render:
1. Go to service → **Settings** → **Custom Domains**
2. Add your domain (e.g. `api.yourdomain.com` for backend, `app.yourdomain.com` for frontend)
3. Add the CNAME records shown to your DNS provider
4. Update `APP_URL`, `APP_FRONTEND_URL`, `CORS_ALLOWED_ORIGINS`, and `VITE_*` env vars to use your custom domains

---

### Render Cost Summary

| Service | Plan | Monthly Cost |
|---------|------|-------------|
| Backend Web Service | Starter | $7 |
| Frontend Web Service | Starter | $7 |
| Queue Worker | Starter | $7 |
| PostgreSQL | Starter | $7 |
| Redis | Starter | $10 |
| **Total** | | **~$38/mo** |

> Free tier is available but services spin down after inactivity — not suitable for production.

---

---

## Part 2 — Deploy to HostAfrica (VPS)

HostAfrica offers VPS hosting. This approach uses Docker Compose on a single VPS, which is the simplest self-hosted setup.

### Prerequisites

- A HostAfrica VPS with **Ubuntu 22.04** (minimum 2GB RAM, 2 vCPU recommended)
- A domain name pointed to your VPS IP
- SSH access to the server

---

### Step 1 — Initial Server Setup

SSH into your VPS:

```bash
ssh root@YOUR_VPS_IP
```

Update the system and install Docker:

```bash
# Update packages
apt update && apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com | sh

# Install Docker Compose
apt install docker-compose-plugin -y

# Install Nginx (for reverse proxy)
apt install nginx -y

# Install Certbot for SSL
apt install certbot python3-certbot-nginx -y

# Create a non-root user (recommended)
adduser eevents
usermod -aG docker eevents
usermod -aG sudo eevents
```

---

### Step 2 — Clone the Repository

```bash
# Switch to your user
su - eevents

# Clone the repo
git clone https://github.com/YOUR_USERNAME/e-events.git /home/eevents/e-events
cd /home/eevents/e-events
```

---

### Step 3 — Configure Environment Variables

```bash
# Copy the all-in-one env template
cp docker/all-in-one/.env docker/all-in-one/.env.production
nano docker/all-in-one/.env.production
```

Fill in all values:

```env
# App
APP_KEY=                          ← run: docker run --rm php:8.4-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
JWT_SECRET=                       ← run: openssl rand -base64 48

# Frontend URLs — replace with your actual domain
VITE_FRONTEND_URL=https://yourdomain.com
VITE_API_URL_CLIENT=https://yourdomain.com/api
VITE_API_URL_SERVER=http://localhost:8123/api
VITE_PAYSTACK_PUBLIC_KEY=pk_live_your_live_public_key
VITE_APP_NAME=E Events
VITE_APP_PRIMARY_COLOR="#16A249"
VITE_APP_SECONDARY_COLOR="#0D6B47"

# Backend
LOG_CHANNEL=stderr
QUEUE_CONNECTION=redis
APP_CDN_URL=https://yourdomain.com/storage
APP_FRONTEND_URL=https://yourdomain.com
APP_DISABLE_REGISTRATION=false
APP_SAAS_MODE_ENABLED=false
APP_EMAIL_LOGO_URL=
APP_EMAIL_LOGO_LINK_URL=https://yourdomain.com

# Email — use your SMTP provider
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="E Events"

# File storage (local for VPS)
FILESYSTEM_PUBLIC_DISK=public
FILESYSTEM_PRIVATE_DISK=local

# Database
POSTGRES_DB=eevents_db
POSTGRES_USER=eevents_admin
POSTGRES_PASSWORD=CHANGE_THIS_STRONG_PASSWORD
DATABASE_URL=postgresql://eevents_admin:CHANGE_THIS_STRONG_PASSWORD@postgres:5432/eevents_db

# Paystack
PAYSTACK_SECRET_KEY=sk_live_your_live_secret_key
PAYSTACK_PUBLIC_KEY=pk_live_your_live_public_key
PAYSTACK_WEBHOOK_SECRET=your_webhook_secret

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=
REDIS_PORT=6379
```

---

### Step 4 — Build and Start the Application

```bash
cd /home/eevents/e-events/docker/all-in-one

# Build and start all services
docker compose --env-file .env.production up -d --build

# Check all containers are running
docker compose ps

# Run database migrations
docker compose exec all-in-one php artisan migrate --force

# Optimize Laravel
docker compose exec all-in-one php artisan optimize
```

The app is now running on port `8123`. Verify it works:
```bash
curl http://localhost:8123/api/public/system-info
```

---

### Step 5 — Configure Nginx Reverse Proxy

Create an Nginx config for your domain:

```bash
nano /etc/nginx/sites-available/eevents
```

Paste this configuration:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;

    # Increase upload size for event images
    client_max_body_size 20M;

    # Proxy all traffic to the Docker app
    location / {
        proxy_pass http://127.0.0.1:8123;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
        proxy_read_timeout 300;
        proxy_connect_timeout 300;
        proxy_send_timeout 300;
    }
}
```

Enable the site and test:

```bash
ln -s /etc/nginx/sites-available/eevents /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

---

### Step 6 — Enable SSL with Let's Encrypt

```bash
certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

Follow the prompts. Certbot will automatically update your Nginx config with SSL. Auto-renewal is set up automatically.

---

### Step 7 — Configure Paystack Webhook

1. Log in to [Paystack Dashboard](https://dashboard.paystack.com)
2. Go to **Settings** → **API Keys & Webhooks**
3. Set **Webhook URL** to:
   ```
   https://yourdomain.com/api/public/webhooks/paystack
   ```
4. Copy the **Webhook Secret** and update `PAYSTACK_WEBHOOK_SECRET` in your `.env.production`
5. Restart the app:
   ```bash
   docker compose --env-file .env.production restart
   ```

---

### Step 8 — Set Up Auto-Start on Reboot

Create a systemd service so Docker Compose starts automatically if the server reboots:

```bash
nano /etc/systemd/system/eevents.service
```

```ini
[Unit]
Description=E.Events Application
Requires=docker.service
After=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=/home/eevents/e-events/docker/all-in-one
ExecStart=/usr/bin/docker compose --env-file .env.production up -d
ExecStop=/usr/bin/docker compose --env-file .env.production down
TimeoutStartSec=0
User=eevents

[Install]
WantedBy=multi-user.target
```

Enable it:

```bash
systemctl daemon-reload
systemctl enable eevents
systemctl start eevents
```

---

### Step 9 — Set Up Automated Backups

Create a daily database backup script:

```bash
nano /home/eevents/backup.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/home/eevents/backups"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

cd /home/eevents/e-events/docker/all-in-one

# Dump the database
docker compose exec -T postgres pg_dump \
  -U eevents_admin eevents_db \
  > "$BACKUP_DIR/eevents_db_$DATE.sql"

# Keep only last 7 days of backups
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete

echo "Backup completed: eevents_db_$DATE.sql"
```

```bash
chmod +x /home/eevents/backup.sh

# Add to cron — runs daily at 2am
crontab -e
# Add this line:
0 2 * * * /home/eevents/backup.sh >> /home/eevents/backup.log 2>&1
```

---

### Step 10 — Updating the Application

When you push new code to GitHub:

```bash
cd /home/eevents/e-events

# Pull latest code
git pull origin main

# Rebuild and restart
cd docker/all-in-one
docker compose --env-file .env.production up -d --build

# Run any new migrations
docker compose exec all-in-one php artisan migrate --force

# Clear caches
docker compose exec all-in-one php artisan optimize:clear
docker compose exec all-in-one php artisan optimize
```

---

### HostAfrica VPS Cost Summary

| Item | Cost |
|------|------|
| VPS (2GB RAM, 2 vCPU) | ~R150–R300/mo |
| Domain name | ~R150/yr |
| SSL | Free (Let's Encrypt) |
| **Total** | **~R150–R300/mo** |

---

---

## Migrating from Render to HostAfrica

When you're ready to move from Render to HostAfrica:

1. **Export your database** from Render:
   ```bash
   # From Render shell
   pg_dump $DATABASE_URL > eevents_backup.sql
   ```

2. **Copy the backup** to your VPS:
   ```bash
   scp eevents_backup.sql eevents@YOUR_VPS_IP:/home/eevents/
   ```

3. **Import into VPS database**:
   ```bash
   cd /home/eevents/e-events/docker/all-in-one
   docker compose exec -T postgres psql -U eevents_admin eevents_db < /home/eevents/eevents_backup.sql
   ```

4. **Update Paystack webhook URL** in Paystack Dashboard to your new domain

5. **Update DNS** to point your domain to the VPS IP

6. **Shut down Render services** once everything is verified working

---

## Troubleshooting

### Backend won't start
```bash
# Check logs
docker compose logs all-in-one --tail=50

# Common fix — regenerate app key
docker compose exec all-in-one php artisan key:generate
docker compose restart
```

### Migrations fail
```bash
docker compose exec all-in-one php artisan migrate:status
docker compose exec all-in-one php artisan migrate --force --verbose
```

### Paystack webhook not received
- Verify the webhook URL is exactly: `https://yourdomain.com/api/public/webhooks/paystack`
- Check `PAYSTACK_WEBHOOK_SECRET` matches what's in Paystack Dashboard
- Check backend logs: `docker compose logs all-in-one | grep paystack`

### Emails not sending
```bash
# Test email from Laravel
docker compose exec all-in-one php artisan tinker
# Then run:
Mail::raw('Test email', fn($m) => $m->to('you@example.com')->subject('Test'));
```

### Queue jobs not processing
```bash
# Check worker is running
docker compose ps

# Check failed jobs
docker compose exec all-in-one php artisan queue:failed

# Retry failed jobs
docker compose exec all-in-one php artisan queue:retry all
```

### Check application health
```bash
curl https://yourdomain.com/api/public/system-info
```

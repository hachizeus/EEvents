# E.Events Deployment Guide

This guide covers deploying E.Events to **Railway** (free tier, supports Docker) and **HostAfrica** (VPS).

> **Why not Render free tier?**
> Render's free tier only supports Node, Python, Ruby, Go and static sites.
> The backend is Laravel/PHP and requires Docker — which needs Render's paid Starter plan ($7/mo).
> Railway supports Docker on its free tier ($5 free credit/month, no credit card required).

---

## Architecture Overview

| Component | What it is | Port |
|-----------|-----------|------|
| **Backend** | Laravel API (PHP 8.4, Docker) | 8080 |
| **Frontend** | React SSR (Node.js) | 5678 |
| **Worker** | Laravel queue worker | — |
| **PostgreSQL** | Database | 5432 |
| **Redis** | Cache + Queue | 6379 |

---

## Part 1 — Deploy to Railway (Free Tier)

Railway gives $5 free credit per month — enough to run this app for testing and small traffic.
No credit card required to start.

---

### Step 1 — Push your code to GitHub

```bash
git add .
git commit -m "Add Railway deployment config"
git push origin main
```

---

### Step 2 — Create a Railway account

1. Go to [https://railway.app](https://railway.app)
2. Click **Login** → **Login with GitHub**
3. Authorize Railway to access your GitHub account

---

### Step 3 — Create a new Project

1. In Railway dashboard → click **New Project**
2. Select **Deploy from GitHub repo**
3. Find and select your `EEvents` repository
4. Railway will detect the `railway.toml` in the root and start building the backend

---

### Step 4 — Add PostgreSQL Database

1. In your Railway project → click **+ New**
2. Select **Database** → **Add PostgreSQL**
3. Railway creates a managed Postgres instance automatically
4. Click on the Postgres service → **Variables** tab
5. Copy the value of `DATABASE_URL` — you'll need it shortly

---

### Step 5 — Add Redis

1. In your Railway project → click **+ New**
2. Select **Database** → **Add Redis**
3. Railway creates a managed Redis instance automatically
4. Click on the Redis service → **Variables** tab
5. Copy the value of `REDIS_URL` — you'll need it shortly

---

### Step 6 — Configure Backend Environment Variables

1. Click on your **backend service** (the one built from your repo)
2. Go to the **Variables** tab
3. Click **Raw Editor** and paste all of these — fill in the values marked with `←`:

```
APP_NAME=E Events
APP_ENV=production
APP_DEBUG=false
APP_KEY=                          ← see note below
APP_URL=                          ← Railway will give you a URL after first deploy, e.g. https://eevents-backend-production.up.railway.app
APP_FRONTEND_URL=                 ← your frontend Railway URL (set after Step 9)
APP_CDN_URL=                      ← same as APP_URL + /storage
APP_SAAS_MODE_ENABLED=false
APP_DISABLE_REGISTRATION=false
APP_PLATFORM_SUPPORT_EMAIL=support@your-domain.com

DATABASE_URL=                     ← paste from Step 4
REDIS_URL=                        ← paste from Step 5

DB_CONNECTION=pgsql
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis

FILESYSTEM_PUBLIC_DISK=public
FILESYSTEM_PRIVATE_DISK=local

JWT_SECRET=                       ← see note below
JWT_ALGO=HS256

MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME=E Events

PAYSTACK_SECRET_KEY=sk_test_your_test_secret_key
PAYSTACK_PUBLIC_KEY=pk_test_your_test_public_key
PAYSTACK_WEBHOOK_SECRET=your_webhook_secret

LOG_CHANNEL=stderr
LOG_LEVEL=error
CORS_ALLOWED_ORIGINS=             ← your frontend Railway URL (set after Step 9)
```

**Generating APP_KEY:**
In the Railway service → **Settings** → **Deploy** → add this as a one-time build command, or generate locally:
```bash
# Run this on your local machine (needs PHP installed):
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```
Copy the output (e.g. `base64:abc123...`) and paste as `APP_KEY`.

**Generating JWT_SECRET:**
```bash
# Run this on your local machine:
openssl rand -base64 48
```

---

### Step 7 — Set the Backend Root Directory

1. Click on your backend service → **Settings**
2. Under **Source** → **Root Directory** → set to `backend`
3. Under **Build** → **Dockerfile Path** → set to `Dockerfile`
4. Click **Save** — Railway will redeploy

The `start.sh` script will automatically run `php artisan migrate --force` before starting the server on every deploy.

---

### Step 8 — Get your Backend URL

1. Click on your backend service → **Settings** → **Networking**
2. Click **Generate Domain**
3. Copy the URL — it looks like `https://eevents-backend-production.up.railway.app`
4. Go back to **Variables** and update:
   - `APP_URL` = your backend URL
   - `APP_CDN_URL` = your backend URL + `/storage`

---

### Step 9 — Deploy the Frontend

1. In your Railway project → click **+ New** → **GitHub Repo**
2. Select the same `EEvents` repository
3. Railway will ask which service to create — name it `eevents-frontend`
4. Go to **Settings** → **Source** → **Root Directory** → set to `frontend`
5. Railway detects `frontend/railway.toml` and uses Node/Nixpacks

6. Go to **Variables** tab and add:

```
NODE_ENV=production
VITE_API_URL_CLIENT=https://your-backend-url.up.railway.app/api
VITE_API_URL_SERVER=https://your-backend-url.up.railway.app/api
VITE_FRONTEND_URL=https://your-frontend-url.up.railway.app
VITE_PAYSTACK_PUBLIC_KEY=pk_test_your_test_public_key
VITE_APP_NAME=E Events
VITE_APP_PRIMARY_COLOR=#16A249
VITE_APP_SECONDARY_COLOR=#0D6B47
```

7. Go to **Settings** → **Networking** → **Generate Domain**
8. Copy the frontend URL

---

### Step 10 — Update Backend with Frontend URL

Go back to your **backend service** → **Variables** and update:
- `APP_FRONTEND_URL` = your frontend Railway URL
- `CORS_ALLOWED_ORIGINS` = your frontend Railway URL

Railway will redeploy automatically.

---

### Step 11 — Deploy the Queue Worker

1. In your Railway project → click **+ New** → **GitHub Repo**
2. Select the same `EEvents` repository
3. Name it `eevents-worker`
4. Go to **Settings** → **Source** → **Root Directory** → set to `backend`
5. Go to **Settings** → **Deploy** → **Start Command** → set to:
   ```
   php artisan queue:work --sleep=3 --tries=3 --timeout=90
   ```
6. Go to **Variables** → click **Shared Variables** → link to the same variables as the backend service (or copy them all again)

---

### Step 12 — Configure Paystack Webhook

1. Log in to [Paystack Dashboard](https://dashboard.paystack.com)
2. Go to **Settings** → **API Keys & Webhooks**
3. Set **Webhook URL** to:
   ```
   https://your-backend-url.up.railway.app/api/public/webhooks/paystack
   ```
4. Copy the **Webhook Secret** and update `PAYSTACK_WEBHOOK_SECRET` in your backend variables

---

### Step 13 — Verify Everything Works

Open your frontend URL in a browser. You should see the E.Events homepage.

Test the API:
```
https://your-backend-url.up.railway.app/api/public/system-info
```
Should return a JSON response with `{"status":"ok"}` or similar.

---

### Railway Free Tier Limits

| Resource | Free Allowance |
|----------|---------------|
| Credit | $5/month |
| Execution hours | ~500 hours/month |
| Memory | 512MB per service |
| PostgreSQL | Included in credit |
| Redis | Included in credit |
| Bandwidth | 100GB/month |

The $5 credit covers roughly 3–4 services running 24/7 at minimal load.
Services do **not** spin down on Railway (unlike Render free tier).

---

### Switching to Live Paystack Keys

When ready for real payments, update these variables in Railway:
- `PAYSTACK_SECRET_KEY` → `sk_live_...`
- `PAYSTACK_PUBLIC_KEY` → `pk_live_...`
- `VITE_PAYSTACK_PUBLIC_KEY` → `pk_live_...`
- Update webhook URL in Paystack Dashboard to your Railway backend URL

---

---

## Part 2 — Deploy to HostAfrica (VPS)

HostAfrica offers VPS hosting. This uses Docker Compose on a single server.

### Prerequisites

- HostAfrica VPS with **Ubuntu 22.04** (minimum 2GB RAM, 2 vCPU)
- A domain name pointed to your VPS IP
- SSH access

---

### Step 1 — Initial Server Setup

```bash
ssh root@YOUR_VPS_IP

# Update and install Docker
apt update && apt upgrade -y
curl -fsSL https://get.docker.com | sh
apt install docker-compose-plugin nginx certbot python3-certbot-nginx -y

# Create app user
adduser eevents
usermod -aG docker eevents
usermod -aG sudo eevents
```

---

### Step 2 — Clone the Repository

```bash
su - eevents
git clone https://github.com/hachizeus/EEvents.git /home/eevents/e-events
cd /home/eevents/e-events
```

---

### Step 3 — Configure Environment

```bash
cp docker/all-in-one/.env docker/all-in-one/.env.production
nano docker/all-in-one/.env.production
```

Fill in all values — replace every placeholder:

```env
# Generate APP_KEY:
# docker run --rm php:8.4-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
APP_KEY=base64:YOUR_GENERATED_KEY

# Generate JWT_SECRET:
# openssl rand -base64 48
JWT_SECRET=YOUR_GENERATED_SECRET

VITE_FRONTEND_URL=https://yourdomain.com
VITE_API_URL_CLIENT=https://yourdomain.com/api
VITE_API_URL_SERVER=http://localhost:8123/api
VITE_PAYSTACK_PUBLIC_KEY=pk_live_your_live_public_key
VITE_APP_NAME=E Events
VITE_APP_PRIMARY_COLOR="#16A249"
VITE_APP_SECONDARY_COLOR="#0D6B47"

LOG_CHANNEL=stderr
QUEUE_CONNECTION=redis
APP_CDN_URL=https://yourdomain.com/storage
APP_FRONTEND_URL=https://yourdomain.com
APP_DISABLE_REGISTRATION=false
APP_SAAS_MODE_ENABLED=false
APP_EMAIL_LOGO_URL=
APP_EMAIL_LOGO_LINK_URL=https://yourdomain.com

MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="E Events"

FILESYSTEM_PUBLIC_DISK=public
FILESYSTEM_PRIVATE_DISK=local

POSTGRES_DB=eevents_db
POSTGRES_USER=eevents_admin
POSTGRES_PASSWORD=CHANGE_THIS_STRONG_PASSWORD
DATABASE_URL=postgresql://eevents_admin:CHANGE_THIS_STRONG_PASSWORD@postgres:5432/eevents_db

PAYSTACK_SECRET_KEY=sk_live_your_live_secret_key
PAYSTACK_PUBLIC_KEY=pk_live_your_live_public_key
PAYSTACK_WEBHOOK_SECRET=your_webhook_secret

REDIS_HOST=redis
REDIS_PASSWORD=
REDIS_PORT=6379
```

---

### Step 4 — Build and Start

```bash
cd /home/eevents/e-events/docker/all-in-one
docker compose --env-file .env.production up -d --build
docker compose exec all-in-one php artisan migrate --force
docker compose exec all-in-one php artisan optimize
```

Verify it works:
```bash
curl http://localhost:8123/api/public/system-info
```

---

### Step 5 — Configure Nginx

```bash
nano /etc/nginx/sites-available/eevents
```

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    client_max_body_size 20M;

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
    }
}
```

```bash
ln -s /etc/nginx/sites-available/eevents /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx
```

---

### Step 6 — Enable SSL

```bash
certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

---

### Step 7 — Configure Paystack Webhook

In Paystack Dashboard → Settings → API Keys & Webhooks:
```
https://yourdomain.com/api/public/webhooks/paystack
```

---

### Step 8 — Auto-Start on Reboot

```bash
nano /etc/systemd/system/eevents.service
```

```ini
[Unit]
Description=E.Events
Requires=docker.service
After=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=/home/eevents/e-events/docker/all-in-one
ExecStart=/usr/bin/docker compose --env-file .env.production up -d
ExecStop=/usr/bin/docker compose --env-file .env.production down
User=eevents

[Install]
WantedBy=multi-user.target
```

```bash
systemctl daemon-reload
systemctl enable eevents
```

---

### Step 9 — Updating the App

```bash
cd /home/eevents/e-events
git pull origin main
cd docker/all-in-one
docker compose --env-file .env.production up -d --build
docker compose exec all-in-one php artisan migrate --force
docker compose exec all-in-one php artisan optimize:clear
docker compose exec all-in-one php artisan optimize
```

---

### Step 10 — Daily Database Backups

```bash
nano /home/eevents/backup.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/home/eevents/backups"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR
cd /home/eevents/e-events/docker/all-in-one
docker compose exec -T postgres pg_dump -U eevents_admin eevents_db > "$BACKUP_DIR/db_$DATE.sql"
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
```

```bash
chmod +x /home/eevents/backup.sh
crontab -e
# Add:
0 2 * * * /home/eevents/backup.sh
```

---

## Migrating from Railway to HostAfrica

1. Export database from Railway:
   - Railway dashboard → Postgres service → **Data** tab → **Export**
   - Or use: `pg_dump $DATABASE_URL > backup.sql`

2. Copy to VPS:
   ```bash
   scp backup.sql eevents@YOUR_VPS_IP:/home/eevents/
   ```

3. Import on VPS:
   ```bash
   cd /home/eevents/e-events/docker/all-in-one
   docker compose exec -T postgres psql -U eevents_admin eevents_db < /home/eevents/backup.sql
   ```

4. Update Paystack webhook URL to new domain
5. Update DNS to point to VPS IP
6. Delete Railway project once verified

---

## Troubleshooting

**Backend won't start:**
```bash
# Railway: check deploy logs in dashboard
# HostAfrica:
docker compose logs all-in-one --tail=50
```

**Migrations fail:**
```bash
docker compose exec all-in-one php artisan migrate:status
```

**Paystack webhook not working:**
- Check URL is exactly: `https://yourdomain.com/api/public/webhooks/paystack`
- Verify `PAYSTACK_WEBHOOK_SECRET` matches Paystack Dashboard

**Emails not sending:**
```bash
docker compose exec all-in-one php artisan tinker
# Then: Mail::raw('test', fn($m) => $m->to('you@test.com')->subject('test'));
```

**Queue not processing:**
```bash
docker compose exec all-in-one php artisan queue:failed
docker compose exec all-in-one php artisan queue:retry all
```

**Check app health:**
```
https://yourdomain.com/api/public/system-info
```

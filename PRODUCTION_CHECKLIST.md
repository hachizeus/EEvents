# Production Deployment Checklist

## Payment Gateway
- [ ] Set `PAYSTACK_SECRET_KEY` to your **live** key (`sk_live_...`) in production environment
- [ ] Set `PAYSTACK_PUBLIC_KEY` to your **live** key (`pk_live_...`) in production environment
- [ ] Set `PAYSTACK_WEBHOOK_SECRET` — copy from Paystack Dashboard → Settings → API Keys & Webhooks
- [ ] Set `VITE_PAYSTACK_PUBLIC_KEY` to your **live** public key in the frontend environment
- [ ] Register your webhook URL in Paystack Dashboard: `https://your-domain.com/api/public/webhooks/paystack`
- [ ] Test a real payment end-to-end in staging before going live

## Security
- [ ] Generate a new `APP_KEY`: `php artisan key:generate`
- [ ] Generate a new `JWT_SECRET`: `php artisan jwt:secret` or use a 64-char random string
- [ ] Set `APP_DEBUG=false` in production
- [ ] Set `APP_ENV=production` in production
- [ ] Set `CORS_ALLOWED_ORIGINS` to your exact frontend domain (not `*`)
- [ ] Rotate any credentials that were committed to git history (email passwords, test keys)

## Email
- [ ] Replace `MAIL_MAILER=log` with a real SMTP provider (Brevo, Mailgun, SES, etc.)
- [ ] Set `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`
- [ ] Set `MAIL_FROM_ADDRESS` to your verified sender address
- [ ] Test email delivery (order confirmations, password resets)

## Infrastructure
- [ ] Set `QUEUE_CONNECTION=redis` (not `sync`) for background job processing
- [ ] Set `CACHE_DRIVER=redis` for performance
- [ ] Set `SESSION_DRIVER=redis` for multi-instance deployments
- [ ] Configure S3 buckets for file storage (`AWS_PUBLIC_BUCKET`, `AWS_PRIVATE_BUCKET`)
- [ ] Remove `AWS_ENDPOINT` and `AWS_USE_PATH_STYLE_ENDPOINT` (those are for local MinIO only)
- [ ] Set `APP_CDN_URL` to your CloudFront or CDN URL

## DigitalOcean App Platform (.do/app.yaml)
- [ ] Set `PAYSTACK_SECRET_KEY` secret in DigitalOcean dashboard
- [ ] Set `PAYSTACK_PUBLIC_KEY` secret in DigitalOcean dashboard
- [ ] Set `PAYSTACK_WEBHOOK_SECRET` secret in DigitalOcean dashboard
- [ ] Set `VITE_PAYSTACK_PUBLIC_KEY` secret in DigitalOcean dashboard
- [ ] Set `MAIL_USERNAME` and `MAIL_PASSWORD` secrets
- [ ] Set `APP_KEY` and `JWT_SECRET` secrets
- [ ] Update `MAIL_FROM_ADDRESS` to your verified sender

## Database
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Verify `paystack_payments` table exists
- [ ] Verify `account_paystack_settings` table exists

## Final Verification
- [ ] Visit `/api/public/system-info` — should return 200
- [ ] Create a test event and complete a test purchase with Paystack test keys
- [ ] Verify webhook is received and order status updates to COMPLETED
- [ ] Verify order confirmation email is sent
- [ ] Test refund flow from admin panel

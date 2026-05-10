<div align="center">

# E Events

### Open-source event ticketing and management platform

Sell tickets online for conferences, nightlife events, concerts, club nights, workshops, and festivals.  
Self-hosted or cloud. Your events, your brand, your data.

**Powered by [Elitjohns Digital](https://elitjohnsdigital.com)**

</div>

<br>

## Why E Events?

Most ticketing platforms charge per-ticket fees and lock your data into their ecosystem. **E Events is a modern,
open-source ticketing platform** for organizers who want full control over branding, checkout, data, and infrastructure.

Built for nightlife promoters, festival organizers, venues, community groups, and conference hosts.

<br>

## Features

- **Flexible Ticketing** — Free, paid, donation, and tiered ticket types
- **Paystack Payments** — Accept payments via Paystack with per-account API keys
- **QR Code Check-in** — Mobile scanner with offline support and real-time tracking
- **Real-Time Analytics** — Track sales, revenue, and attendance
- **Custom Branding** — Your logo, colors, and style on every page
- **Attendee Management** — Custom questions, bulk messaging, CSV export
- **Webhooks** — Integrate with Zapier, Make, and CRMs
- **Multi-user Roles** — Invite team members with custom permissions
- **Invoicing** — Automatic invoice generation for orders
- **Offline Payments** — Accept bank transfers and other offline methods

<br>

## Quick Start

### Docker

```bash
git clone https://github.com/your-org/e-events.git
cd e-events/docker/all-in-one
cp .env.example .env
# Edit .env with your settings
docker compose up -d
```

Open `http://localhost:8123` and create your account.

<br>

## Configuration

Key environment variables in `docker/all-in-one/.env`:

```env
VITE_APP_NAME=E Events
VITE_APP_PRIMARY_COLOR="#16A249"
VITE_APP_SECONDARY_COLOR="#0D6B47"

# Paystack (system-level fallback keys)
PAYSTACK_SECRET_KEY=sk_live_your_key
PAYSTACK_PUBLIC_KEY=pk_live_your_key
PAYSTACK_WEBHOOK_SECRET=your_webhook_secret
```

Users can also connect their own Paystack keys via **Account → Payment Settings**.

<br>

## Support

📧 [Elitjohns Digital](https://elitjohnsdigital.com)

<br>

## License

E Events is licensed under **AGPL-3.0**.

<br>

<div align="center">

**Powered by [Elitjohns Digital](https://elitjohnsdigital.com)**

</div>

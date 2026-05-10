<div align="center">

<img src="https://hievents-public.s3.us-west-1.amazonaws.com/website/github-banner.png?v=1" alt="E.Events - Open Source Event Ticketing Platform" width="100%">

# E.Events

### Open-Source-Plattform für Event-Ticketing und -Management

Verkaufen Sie Tickets online für Konferenzen, Nachtleben-Events, Konzerte, Club-Nights, Workshops und Festivals.
Self-hosted oder Cloud. Ihre Events, Ihre Marke, Ihre Daten.

[Cloud testen →](https://app.E.Events/auth/register?utm_source=gh-readme) · [Live-Demo](https://app.E.Events/event/2/hievents-conference-2030?utm_source=gh-readme) · [Dokumentation](https://E.Events/docs?utm_source=gh-readme) · [Website](https://E.Events?utm_source=gh-readme)

[![License: AGPL v3](https://img.shields.io/badge/License-AGPL_v3-blue.svg)](https://github.com/HiEventsDev/E.Events/blob/develop/LICENCE)
[![GitHub Release](https://img.shields.io/github/v/release/HiEventsDev/E.Events?include_prereleases)](https://github.com/HiEventsDev/E.Events/releases)
[![Run Unit Tests](https://github.com/HiEventsDev/E.Events/actions/workflows/unit-tests.yml/badge.svg?event=push)](https://github.com/HiEventsDev/E.Events/actions/workflows/unit-tests.yml)
[![Docker Pulls](https://img.shields.io/docker/pulls/daveearley/E.Events-all-in-one)](https://hub.docker.com/r/daveearley/E.Events-all-in-one)

<a href="https://trendshift.io/repositories/10563" target="_blank">
  <img src="https://trendshift.io/api/badge/repositories/10563" alt="HiEventsDev%2FE.Events | Trendshift" width="250" height="55"/>
</a>

<p>
<a href="README.de.md">Deutsch</a> · <a href="README.pt.md">Português</a> · <a href="README.pt-br.md">Português do Brasil</a> · <a href="README.fr.md">Français</a> · <a href="README.it.md">Italiano</a> · <a href="README.nl.md">Nederlands</a> · <a href="README.es.md">Español</a> · <a href="README.zh-cn.md">中文</a> · <a href="README.zh-hk.md">繁體中文 (香港)</a> · <a href="README.ja.md">日本語</a> · <a href="README.vi.md">Tiếng Việt</a> · <a href="README.tr.md">Türkçe</a> · <a href="README.hu.md">Magyar</a> · <a href="README.pl.md">Polski</a>
</p>

</div>

<br>

## Warum E.Events?

Die meisten Ticketing-Plattformen erheben Gebühren pro Ticket und sperren Ihre Daten in ihr Ökosystem. **E.Events ist eine moderne, Open-Source-Alternative zu Eventbrite, Tickettailor, Dice.fm und anderen Ticketing-Plattformen** für Veranstalter, die volle Kontrolle über Branding, Checkout, Daten und Infrastruktur wünschen.

Entwickelt für Nachtleben-Promoter, Festival-Organisatoren, Veranstaltungsorte, Community-Gruppen und Konferenz-Gastgeber.

<br>

<img alt="E.Events Dashboard" src="https://hievents-public.s3.us-west-1.amazonaws.com/website/github-screenshot.png"/>

<br>

## Features

<table>
<tr>
<td width="50%" valign="top">

### 🎟️ Ticketing & Verkauf

- Flexible Tickettypen (kostenlos, bezahlt, Spende, gestaffelt)
- Versteckte und gesperrte Tickets hinter Promo-Codes
- Promo-Codes und Presale-Zugang
- Produkt-Add-ons (Merchandise, Upgrades, Extras)
- Produktkategorien für Organisation
- Vollständige Steuer- und Gebührenunterstützung (MwSt., Servicegebühren)
- Kapazitätsmanagement und geteilte Limits

</td>
<td width="50%" valign="top">

### 🎨 Branding & Anpassung

- Schöner, conversion-optimierter Checkout
- Anpassbare PDF-Ticket-Designs
- Gebrandete Veranstalter-Homepage
- Drag-and-Drop Event-Page-Builder
- Einbettbares Ticket-Widget
- SEO-Tools (Meta-Tags, Open Graph)

</td>
</tr>
<tr>
<td width="50%" valign="top">

### 👥 Teilnehmerverwaltung

- Individuelle Checkout-Fragen
- Erweiterte Suche, Filterung und Export (CSV/XLSX)
- Vollständige und teilweise Rückerstattungen
- Massen-Messaging nach Tickettyp
- QR-Code-Check-in mit Scan-Logs
- Zugriffskontrollierte Check-in-Listen

</td>
<td width="50%" valign="top">

### 📊 Analytics & Wachstum

- Echtzeit-Verkaufs-Dashboard
- Affiliate- und Empfehlungs-Tracking
- Erweiterte Berichte (Verkäufe, Steuern, Promos)
- Webhooks (Zapier, Make, CRMs)

</td>
</tr>
<tr>
<td colspan="2" valign="top">

### ⚙️ Betrieb

Multi-User-Rollen und Berechtigungen · Stripe Connect Sofortauszahlungen · Offline-Zahlungsmethoden · Offline-Event-Unterstützung ·
Automatische Rechnungsstellung · Event-Archiv · Mehrsprachige Unterstützung · Vollständige REST-API

</td>
</tr>
</table>

<br>

## Vergleich

| Feature                           | E.Events | Eventbrite | Tickettailor | Dice        |
|:----------------------------------|:----------|:-----------|:-------------|:------------|
| Self-Hosting-Option               | ✅         | ❌          | ❌            | ❌           |
| Open Source                       | ✅         | ❌          | ❌            | ❌           |
| Keine Gebühren pro Ticket (self-hosted) | ✅         | ❌          | ❌            | ❌           |
| Vollständiges Custom Branding     | ✅         | Begrenzt   | ✅            | Begrenzt    |
| Affiliate-Tracking                | ✅         | ✅          | ❌            | ❌           |
| API-Zugriff                       | ✅         | ✅          | ✅            | Begrenzt    |
| Eigene Daten besitzen             | ✅         | ❌          | ❌            | ❌           |

<br>

## Schnellstart

### One-Click Deploy

[![Deploy on DigitalOcean](https://www.deploytodo.com/do-btn-blue.svg)](https://github.com/HiEventsDev/E.Events-digitalocean)
[![Deploy to Render](https://render.com/images/deploy-to-render-button.svg)](https://github.com/HiEventsDev/E.Events-render.com)
[![Deploy on Railway](https://railway.app/button.svg)](https://railway.app/template/8CGKmu?referralCode=KvSr11)
[![Deploy on Zeabur](https://zeabur.com/button.svg)](https://zeabur.com/templates/8DIRY6)

### Docker

```bash
git clone git@github.com:HiEventsDev/E.Events.git
cd E.Events/docker/all-in-one

# Schlüssel generieren (Linux/macOS)
echo "APP_KEY=base64:$(openssl rand -base64 32)" >> .env
echo "JWT_SECRET=$(openssl rand -base64 32)" >> .env

docker compose up -d
```

> [!TIP]
> **Windows-Benutzer:** Siehe `./docker/all-in-one/README.md` für Anweisungen zur Schlüsselgenerierung.

Öffnen Sie `http://localhost:8123` und erstellen Sie Ihr Konto.

📖 [Vollständige Installationsanleitung](https://E.Events/docs/getting-started?utm_source=gh-readme)

<br>

## E.Events Cloud

Möchten Sie nicht selbst hosten? **[E.Events Cloud](https://app.E.Events/auth/register?utm_source=gh-readme)** ist eine vollständig verwaltete Option ohne Setup, mit automatischen Updates und verwalteter Infrastruktur.

[Jetzt starten →](https://app.E.Events/auth/register?utm_source=gh-readme)

<br>

## Dokumentation

| Ressource        | Link                                                                                          |
|:-----------------|:----------------------------------------------------------------------------------------------|
| Erste Schritte   | [E.Events/docs/getting-started](https://E.Events/docs/getting-started?utm_source=gh-readme) |
| Konfiguration    | [E.Events/docs/configuration](https://E.Events/docs/configuration?utm_source=gh-readme)     |
| API-Referenz     | [E.Events/docs/api](https://E.Events/docs/api?utm_source=gh-readme)                         |
| Webhooks         | [E.Events/docs/webhooks](https://E.Events/docs/webhooks?utm_source=gh-readme)               |

<br>

## Mitwirken

Wir begrüßen Beiträge. Siehe den [Beitragsleitfaden](CONTRIBUTING.md) für Details.

<br>

## Support

📖 [Dokumentation](https://E.Events/docs?utm_source=gh-readme) · 📧 [hello@E.Events](mailto:hello@E.Events) ·
🐛 [GitHub Issues](https://github.com/HiEventsDev/E.Events/issues)

<br>

## Changelog

Bleiben Sie über neue Features und Verbesserungen auf der [Releases-Seite](https://github.com/HiEventsDev/E.Events/releases) auf dem Laufenden.

<br>

## Lizenz

E.Events ist lizenziert unter **AGPL-3.0 mit zusätzlichen Bedingungen**. Kommerzielle Lizenzierung verfügbar. [Mehr erfahren](https://E.Events/licensing).

<br>

<div align="center">

**[Website](https://E.Events)** · **[Dokumentation](https://E.Events/docs)** · **[Twitter/X](https://x.com/HiEventsTickets)**

Made with ☘️ in Ireland

</div>

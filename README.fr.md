<div align="center">

<img src="https://hievents-public.s3.us-west-1.amazonaws.com/website/github-banner.png?v=1" alt="E.Events - Plateforme Open Source de Billetterie d'Événements" width="100%">

# E.Events

### Plateforme open source de billetterie et gestion d'événements

Vendez des billets en ligne pour des conférences, événements nocturnes, concerts, soirées en club, ateliers et festivals.
Auto-hébergé ou cloud. Vos événements, votre marque, vos données.

[Essayer le Cloud →](https://app.E.Events/auth/register?utm_source=gh-readme) · [Démo en Direct](https://app.E.Events/event/2/hievents-conference-2030?utm_source=gh-readme) · [Documentation](https://E.Events/docs?utm_source=gh-readme) · [Site Web](https://E.Events?utm_source=gh-readme)

[![License: AGPL v3](https://img.shields.io/badge/License-AGPL_v3-blue.svg)](https://github.com/HiEventsDev/E.Events/blob/develop/LICENCE)
[![GitHub Release](https://img.shields.io/github/v/release/HiEventsDev/E.Events?include_prereleases)](https://github.com/HiEventsDev/E.Events/releases)
[![Run Unit Tests](https://github.com/HiEventsDev/E.Events/actions/workflows/unit-tests.yml/badge.svg?event=push)](https://github.com/HiEventsDev/E.Events/actions/workflows/unit-tests.yml)
[![Docker Pulls](https://img.shields.io/docker/pulls/daveearley/E.Events-all-in-one)](https://hub.docker.com/r/daveearley/E.Events-all-in-one)

<a href="https://trendshift.io/repositories/10563" target="_blank">
  <img src="https://trendshift.io/api/badge/repositories/10563" alt="HiEventsDev%2FE.Events | Trendshift" width="250" height="55"/>
</a>

<p>
<a href="README.de.md">Deutsch</a> · <a href="README.pt.md">Português</a> · <a href="README.pt-br.md">Português do Brasil</a> · <a href="README.fr.md">Français</a> · <a href="README.it.md">Italian</a> · <a href="README.nl.md">Nederlands</a> · <a href="README.es.md">Español</a> · <a href="README.zh-cn.md">中文</a> · <a href="README.zh-hk.md">繁體中文</a> · <a href="README.ja.md">日本語</a> · <a href="README.vi.md">Tiếng Việt</a> · <a href="README.tr.md">Türkçe</a> · <a href="README.hu.md">Magyar</a> · <a href="README.pl.md">Polski</a>
</p>

</div>

<br>

## Pourquoi E.Events ?

La plupart des plateformes de billetterie facturent des frais par billet et verrouillent vos données dans leur écosystème. **E.Events est une alternative moderne et open source à Eventbrite, Tickettailor, Dice.fm et autres plateformes de billetterie** pour les organisateurs qui souhaitent un contrôle total sur leur marque, leur processus de paiement, leurs données et leur infrastructure.

Conçu pour les promoteurs de vie nocturne, les organisateurs de festivals, les salles de concert, les groupes communautaires et les hôtes de conférences.

<br>

<img alt="Tableau de bord E.Events" src="https://hievents-public.s3.us-west-1.amazonaws.com/website/github-screenshot.png"/>

<br>

## Fonctionnalités

<table>
<tr>
<td width="50%" valign="top">

### 🎟️ Billetterie & Ventes

- Types de billets flexibles (gratuit, payant, don, à paliers)
- Billets cachés et verrouillés derrière des codes promo
- Codes promo et accès en prévente
- Produits additionnels (merchandising, upgrades, extras)
- Catégories de produits pour l'organisation
- Support complet des taxes et frais (TVA, frais de service)
- Gestion de capacité et limites partagées

</td>
<td width="50%" valign="top">

### 🎨 Marque & Personnalisation

- Belle page de paiement optimisée pour la conversion
- Design de billets PDF personnalisables
- Page d'accueil d'organisateur brandée
- Constructeur de page d'événement par glisser-déposer
- Widget de billetterie intégrable
- Outils SEO (meta tags, Open Graph)

</td>
</tr>
<tr>
<td width="50%" valign="top">

### 👥 Gestion des Participants

- Questions de paiement personnalisées
- Recherche avancée, filtrage et export (CSV/XLSX)
- Remboursements complets et partiels
- Messagerie groupée par type de billet
- Check-in par code QR avec logs de scan
- Listes de check-in avec contrôle d'accès

</td>
<td width="50%" valign="top">

### 📊 Analytique & Croissance

- Tableau de bord des ventes en temps réel
- Suivi d'affiliés et de référencements
- Rapports avancés (ventes, taxes, promos)
- Webhooks (Zapier, Make, CRMs)

</td>
</tr>
<tr>
<td colspan="2" valign="top">

### ⚙️ Opérations

Rôles et permissions multi-utilisateurs · Paiements instantanés Stripe Connect · Méthodes de paiement hors ligne · Support d'événements hors ligne ·
Facturation automatique · Archivage d'événements · Support multilingue · API REST complète

</td>
</tr>
</table>

<br>

## Comparer

| Fonctionnalité                       | E.Events | Eventbrite | Tickettailor | Dice    |
|:-------------------------------------|:----------|:-----------|:-------------|:--------|
| Option auto-hébergée                 | ✅         | ❌          | ❌            | ❌       |
| Open source                          | ✅         | ❌          | ❌            | ❌       |
| Sans frais par billet (auto-hébergé) | ✅         | ❌          | ❌            | ❌       |
| Personnalisation complète de marque  | ✅         | Limité     | ✅            | Limité  |
| Suivi d'affiliés                     | ✅         | ✅          | ❌            | ❌       |
| Accès API                            | ✅         | ✅          | ✅            | Limité  |
| Propriété de vos données             | ✅         | ❌          | ❌            | ❌       |

<br>

## Démarrage Rapide

### Déploiement en Un Clic

[![Déployer sur DigitalOcean](https://www.deploytodo.com/do-btn-blue.svg)](https://github.com/HiEventsDev/E.Events-digitalocean)
[![Déployer sur Render](https://render.com/images/deploy-to-render-button.svg)](https://github.com/HiEventsDev/E.Events-render.com)
[![Déployer sur Railway](https://railway.app/button.svg)](https://railway.app/template/8CGKmu?referralCode=KvSr11)
[![Déployer sur Zeabur](https://zeabur.com/button.svg)](https://zeabur.com/templates/8DIRY6)

### Docker

```bash
git clone git@github.com:HiEventsDev/E.Events.git
cd E.Events/docker/all-in-one

# Générer les clés (Linux/macOS)
echo "APP_KEY=base64:$(openssl rand -base64 32)" >> .env
echo "JWT_SECRET=$(openssl rand -base64 32)" >> .env

docker compose up -d
```

> [!TIP]
> **Utilisateurs Windows :** Voir `./docker/all-in-one/README.md` pour les instructions de génération de clés.

Ouvrez `http://localhost:8123` et créez votre compte.

📖 [Guide d'installation complet](https://E.Events/docs/getting-started?utm_source=gh-readme)

<br>

## E.Events Cloud

Vous préférez ne pas auto-héberger ? **[E.Events Cloud](https://app.E.Events/auth/register?utm_source=gh-readme)** est une option entièrement gérée avec zéro configuration, mises à jour automatiques et infrastructure managée.

[Commencer →](https://app.E.Events/auth/register?utm_source=gh-readme)

<br>

## Documentation

| Ressource       | Lien                                                                                          |
|:----------------|:----------------------------------------------------------------------------------------------|
| Démarrage       | [E.Events/docs/getting-started](https://E.Events/docs/getting-started?utm_source=gh-readme) |
| Configuration   | [E.Events/docs/configuration](https://E.Events/docs/configuration?utm_source=gh-readme)     |
| Référence API   | [E.Events/docs/api](https://E.Events/docs/api?utm_source=gh-readme)                         |
| Webhooks        | [E.Events/docs/webhooks](https://E.Events/docs/webhooks?utm_source=gh-readme)               |

<br>

## Contribuer

Nous accueillons les contributions. Consultez le [guide de contribution](CONTRIBUTING.md) pour plus de détails.

<br>

## Support

📖 [Documentation](https://E.Events/docs?utm_source=gh-readme) · 📧 [hello@E.Events](mailto:hello@E.Events) ·
🐛 [GitHub Issues](https://github.com/HiEventsDev/E.Events/issues)

<br>

## Journal des Modifications

Restez informé des nouvelles fonctionnalités et améliorations sur
la [page des releases](https://github.com/HiEventsDev/E.Events/releases).

<br>

## Licence

E.Events est sous licence **AGPL-3.0 avec conditions supplémentaires**. Licence commerciale
disponible. [En savoir plus](https://E.Events/licensing).

<br>

<div align="center">

**[Site Web](https://E.Events)** · **[Documentation](https://E.Events/docs)** · *
*[Twitter/X](https://x.com/HiEventsTickets)**

Fait avec ☘️ en Irlande

</div>

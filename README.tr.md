<div align="center">

<img src="https://hievents-public.s3.us-west-1.amazonaws.com/website/github-banner.png?v=1" alt="E.Events - Açık Kaynak Etkinlik Biletleme Platformu" width="100%">

# E.Events

### Açık kaynak etkinlik biletleme ve yönetim platformu

Konferanslar, gece hayatı etkinlikleri, konserler, kulüp geceleri, atölyeler ve festivaller için çevrimiçi bilet satın.
Kendi sunucunuzda veya bulutta. Etkinlikleriniz, markanız, verileriniz.

[Bulut Sürümünü Deneyin →](https://app.E.Events/auth/register?utm_source=gh-readme) · [Canlı Demo](https://app.E.Events/event/2/hievents-conference-2030?utm_source=gh-readme) · [Dokümantasyon](https://E.Events/docs?utm_source=gh-readme) · [Web Sitesi](https://E.Events?utm_source=gh-readme)

[![License: AGPL v3](https://img.shields.io/badge/License-AGPL_v3-blue.svg)](https://github.com/HiEventsDev/E.Events/blob/develop/LICENCE)
[![GitHub Release](https://img.shields.io/github/v/release/HiEventsDev/E.Events?include_prereleases)](https://github.com/HiEventsDev/E.Events/releases)
[![Run Unit Tests](https://github.com/HiEventsDev/E.Events/actions/workflows/unit-tests.yml/badge.svg?event=push)](https://github.com/HiEventsDev/E.Events/actions/workflows/unit-tests.yml)
[![Docker Pulls](https://img.shields.io/docker/pulls/daveearley/E.Events-all-in-one)](https://hub.docker.com/r/daveearley/E.Events-all-in-one)

<a href="https://trendshift.io/repositories/10563" target="_blank">
  <img src="https://trendshift.io/api/badge/repositories/10563" alt="HiEventsDev%2FE.Events | Trendshift" width="250" height="55"/>
</a>

<p>
<a href="README.de.md">Deutsch</a> · <a href="README.pt.md">Português</a> · <a href="README.pt-br.md">Português (Brasil)</a> · <a href="README.fr.md">Français</a> · <a href="README.it.md">Italiano</a> · <a href="README.nl.md">Nederlands</a> · <a href="README.es.md">Español</a> · <a href="README.zh-cn.md">中文</a> · <a href="README.zh-hk.md">繁體中文</a> · <a href="README.ja.md">日本語</a> · <a href="README.vi.md">Tiếng Việt</a> · <a href="README.tr.md">Türkçe</a> · <a href="README.hu.md">Magyar</a> · <a href="README.pl.md">Polski</a>
</p>

</div>

<br>

## Neden E.Events?

Çoğu biletleme platformu bilet başına ücret alır ve verilerinizi kendi ekosistemlerine kilitler. **E.Events, markalaşma, ödeme, veri ve altyapı üzerinde tam kontrol isteyen organizatörler için Eventbrite, Tickettailor, Dice.fm ve diğer biletleme platformlarına modern, açık kaynak bir alternatiftir**.

Gece hayatı organizatörleri, festival düzenleyicileri, mekanlar, topluluk grupları ve konferans ev sahipleri için geliştirilmiştir.

<br>

<img alt="E.Events Gösterge Paneli" src="https://hievents-public.s3.us-west-1.amazonaws.com/website/github-screenshot.png"/>

<br>

## Özellikler

<table>
<tr>
<td width="50%" valign="top">

### 🎟️ Biletleme ve Satış

- Esnek bilet türleri (ücretsiz, ücretli, bağış, kademeli)
- Promosyon kodlarının arkasına gizlenmiş ve kilitlenmiş biletler
- Promosyon kodları ve ön satış erişimi
- Ürün eklentileri (ürünler, yükseltmeler, ekstralar)
- Düzenleme için ürün kategorileri
- Tam vergi ve ücret desteği (KDV, hizmet ücretleri)
- Kapasite yönetimi ve paylaşılan limitler

</td>
<td width="50%" valign="top">

### 🎨 Markalaşma ve Özelleştirme

- Güzel, dönüşüm için optimize edilmiş ödeme
- Özelleştirilebilir PDF bilet tasarımları
- Markalı organizatör ana sayfası
- Sürükle-bırak etkinlik sayfası oluşturucu
- Gömülebilir bilet widget'ı
- SEO araçları (meta etiketleri, Open Graph)

</td>
</tr>
<tr>
<td width="50%" valign="top">

### 👥 Katılımcı Yönetimi

- Özel ödeme soruları
- Gelişmiş arama, filtreleme ve dışa aktarma (CSV/XLSX)
- Tam ve kısmi iadeler
- Bilet türüne göre toplu mesajlaşma
- QR kod ile giriş ve tarama kayıtları
- Erişim kontrollü giriş listeleri

</td>
<td width="50%" valign="top">

### 📊 Analitik ve Büyüme

- Gerçek zamanlı satış gösterge paneli
- Ortaklık ve yönlendirme takibi
- Gelişmiş raporlama (satış, vergi, promosyonlar)
- Webhook'lar (Zapier, Make, CRM'ler)

</td>
</tr>
<tr>
<td colspan="2" valign="top">

### ⚙️ Operasyonlar

Çok kullanıcılı roller ve izinler · Stripe Connect anında ödemeler · Çevrimdışı ödeme yöntemleri · Çevrimdışı etkinlik desteği ·
Otomatik faturalama · Etkinlik arşivi · Çoklu dil desteği · Tam REST API

</td>
</tr>
</table>

<br>

## Karşılaştırma

| Özellik                                  | E.Events | Eventbrite | Tickettailor | Dice    |
|:-----------------------------------------|:----------|:-----------|:-------------|:--------|
| Kendi sunucunuzda barındırma seçeneği   | ✅         | ❌          | ❌            | ❌       |
| Açık kaynak                              | ✅         | ❌          | ❌            | ❌       |
| Bilet başına ücret yok (kendi sunucuda) | ✅         | ❌          | ❌            | ❌       |
| Tam özel markalaşma                      | ✅         | Sınırlı    | ✅            | Sınırlı |
| Ortaklık takibi                          | ✅         | ✅          | ❌            | ❌       |
| API erişimi                              | ✅         | ✅          | ✅            | Sınırlı |
| Verilerinize sahip olun                  | ✅         | ❌          | ❌            | ❌       |

<br>

## Hızlı Başlangıç

### Tek Tıkla Dağıtım

[![Deploy on DigitalOcean](https://www.deploytodo.com/do-btn-blue.svg)](https://github.com/HiEventsDev/E.Events-digitalocean)
[![Deploy to Render](https://render.com/images/deploy-to-render-button.svg)](https://github.com/HiEventsDev/E.Events-render.com)
[![Deploy on Railway](https://railway.app/button.svg)](https://railway.app/template/8CGKmu?referralCode=KvSr11)
[![Deploy on Zeabur](https://zeabur.com/button.svg)](https://zeabur.com/templates/8DIRY6)

### Docker

```bash
git clone git@github.com:HiEventsDev/E.Events.git
cd E.Events/docker/all-in-one

# Anahtarları oluştur (Linux/macOS)
echo "APP_KEY=base64:$(openssl rand -base64 32)" >> .env
echo "JWT_SECRET=$(openssl rand -base64 32)" >> .env

docker compose up -d
```

> [!TIP]
> **Windows kullanıcıları:** Anahtar oluşturma talimatları için `./docker/all-in-one/README.md` dosyasına bakın.

`http://localhost:8123` adresini açın ve hesabınızı oluşturun.

📖 [Tam kurulum rehberi](https://E.Events/docs/getting-started?utm_source=gh-readme)

<br>

## E.Events Cloud

Kendi sunucunuzda barındırmayı tercih etmiyor musunuz? **[E.Events Cloud](https://app.E.Events/auth/register?utm_source=gh-readme)**, sıfır kurulum, otomatik güncellemeler ve yönetilen altyapı ile tam yönetilen bir seçenektir.

[Başlayın →](https://app.E.Events/auth/register?utm_source=gh-readme)

<br>

## Dokümantasyon

| Kaynak          | Bağlantı                                                                                      |
|:----------------|:----------------------------------------------------------------------------------------------|
| Başlangıç       | [E.Events/docs/getting-started](https://E.Events/docs/getting-started?utm_source=gh-readme) |
| Yapılandırma    | [E.Events/docs/configuration](https://E.Events/docs/configuration?utm_source=gh-readme)     |
| API Referansı   | [E.Events/docs/api](https://E.Events/docs/api?utm_source=gh-readme)                         |
| Webhook'lar     | [E.Events/docs/webhooks](https://E.Events/docs/webhooks?utm_source=gh-readme)               |

<br>

## Katkıda Bulunma

Katkılarınızı bekliyoruz. Ayrıntılar için [katkıda bulunma rehberine](CONTRIBUTING.md) bakın.

<br>

## Destek

📖 [Dokümantasyon](https://E.Events/docs?utm_source=gh-readme) · 📧 [hello@E.Events](mailto:hello@E.Events) ·
🐛 [GitHub Issues](https://github.com/HiEventsDev/E.Events/issues)

<br>

## Değişiklik Günlüğü

Yeni özellikler ve iyileştirmelerden haberdar olmak için [sürümler sayfasını](https://github.com/HiEventsDev/E.Events/releases) ziyaret edin.

<br>

## Lisans

E.Events, **ek koşullar içeren AGPL-3.0** lisansına sahiptir. Ticari lisanslama mevcuttur. [Daha fazla bilgi edinin](https://E.Events/licensing).

<br>

<div align="center">

**[Web Sitesi](https://E.Events)** · **[Dokümantasyon](https://E.Events/docs)** · *
*[Twitter/X](https://x.com/HiEventsTickets)**

İrlanda'da ☘️ ile yapıldı

</div>

# Sitemap plan (Izende Studio Web)

This repo didn’t include a sitemap. This document tracks the **intended information architecture** and the **URLs that should be indexable** by search engines.

## Public (indexable) pages

- `/` (Home; one-page sections like `#portfolio` are anchors and not separate URLs)
- `/services/` (Services hub)
  - `/services/web-development`
  - `/services/wordpress`
  - `/services/seo`
  - `/services/security-maintenance`
  - `/services/speed-optimization`
  - `/services/ecommerce`
  - `/services/email-marketing`
  - `/services/social-media`
  - `/services/video-editing`
  - `/services/domain-lookup`
  - `/services/chatbot`
- `/portfolio-details` (Case study template; default project)
  - `/portfolio-details?project=<slug>` (one URL per project in `assets/data/projects.json`)
- `/blog` (Blog listing)
  - `/blog-post?slug=<slug>` (one URL per published post)
- `/book-consultation`
- `/quote`
- SEO / location pages
  - `/st-louis-web-design`
  - `/service-areas`
  - `/missouri-web-hosting`
  - `/illinois-seo-services`
  - `/hosting`
- Utility
  - `/lookup`
- Legal / compliance
  - `/privacy-policy`
  - `/terms-of-service`
  - `/cookie-policy`
  - `/refund-policy`
  - `/service-level-agreement`
  - `/accessibility-statement`
  - `/do-not-sell`
  - `/data-subject-request`

## Private / non-indexable areas (examples)

- `/admin/*`, `/adminIzende/*`
- `/api/*`, `/forms/*`
- `/config/*`, `/includes/*`, `/logs/*`, `/scripts/*`
- test/debug endpoints like `/test-*`, `/debug-*`, `/phpinfo`

## Notes

- The production site uses extensionless URLs (e.g. `/privacy-policy` instead of `/privacy-policy.php`).
- The XML sitemap should stay in sync with:
  - `services/*.php` (excluding internal service endpoints)
  - `assets/data/projects.json` (portfolio projects)
  - WordPress posts exposed via `services/blog-db.php`


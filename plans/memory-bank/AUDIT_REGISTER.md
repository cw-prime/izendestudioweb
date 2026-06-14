# Audit Register — Izende Studio Web
Date: 2026-03-03
Last Updated: 2026-03-05

## Severity Legend
- `Critical` — data loss, security breach, auth bypass, revenue impact
- `High` — broken core flow, exposed data, no error handling on critical path
- `Medium` — degraded experience, workaround exists, tech debt
- `Low` — polish, minor inconsistency, future consideration

## Open Findings
_No open findings._

## Resolved Findings
| # | Severity | File | Finding | Resolved |
|---|---|---|---|---|
| 1 | Critical | [`config/security.php`](config/security.php:412) | Relaxed CSP with `unsafe-inline`/`unsafe-eval` — removed in Phase 3 enforcement. Now uses nonce-based policy. | 2026-03-03 |
| 2 | Critical | [`.htaccess`](.htaccess:41) | Direct access to `/admin/config/` — blocked via `RewriteRule ^admin/config/ - [F,L]` in root `.htaccess` and `Require all denied` in `admin/config/.htaccess`. | 2026-03-03 |
| 3 | High | [`index.php`](index.php:8) | `error_reporting(0)` — replaced with `error_reporting(E_ALL)` + `log_errors=1` + `error_log` path set to `logs/php-error.log`. | 2026-03-04 |
| 4 | High | [`config/security.php`](config/security.php:386) | Session IP validation disabled — replaced with configurable `SESSION_IP_VALIDATION` env var; /24 subnet tolerance for mobile users. | 2026-03-04 |
| 5 | High | [`config/security.php`](config/security.php:415) | `style-src 'unsafe-inline'` — removed; nonce-based CSP now applies to both script-src and style-src. | 2026-03-04 |
| 6 | High | [`api/booking.php`](api/booking.php:204) | Hardcoded SMTP/email defaults — removed; all config now from env vars with partial-SMTP detection and graceful fallback to php mail(). | 2026-03-04 |
| 7 | High | [`config/security.php`](config/security.php:156) | `glob()` resource exhaustion — replaced with `DirectoryIterator` loop in `cleanupRateLimitFiles()`. | 2026-03-04 |
| 8 | Medium | [`config/security.php`](config/security.php:505) | CSP Report-Only header emitted unconditionally (identical to enforced), generating redundant violation reports — gated behind `CSP_REPORT_ONLY=true` env var. | 2026-03-04 |
| 9 | Low | [`adminIzende/.htaccess`](adminIzende/.htaccess:34) | P2-002: `/adminIzende` panel pages lacked a `Content-Security-Policy` header (HSTS and other headers were already present). Added a permissive CSP allowing `'unsafe-inline'`/`'unsafe-eval'` (required for WHMCS inline JS), Google Fonts, reCAPTCHA, and common payment gateways. | 2026-03-05 |

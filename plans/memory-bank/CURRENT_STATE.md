# Current State — Izende Studio Web
Last updated: 2026-06-14

## Product
- What it does: Professional web design, hosting, and digital marketing services for businesses in St. Louis, Missouri, and Illinois. Includes consultation booking, portfolio showcase, blog integration, and client management.
- Who it's for: Small to medium businesses seeking web design and hosting services in the St. Louis metro area.
- Live at: izendestudioweb.com (production), localhost (local development)
- Status: Production

## Tech Stack
- Frontend: PHP (HTML/CSS/JS), Bootstrap, Material Icons, jQuery, Google Fonts
- Backend: PHP 7.x+, MySQL/MariaDB
- Database: MySQL (mysqli extension)
- Auth: Custom session-based authentication with CSRF protection
- Payments: Not implemented (consultation booking only)
- Hosting: Apache with mod_rewrite, .htaccess for routing and security
- Key APIs: Google Analytics, Google Maps, Google Calendar integration, SMTP for email notifications

## Key Files
- Entry point: [`index.php`](index.php:1) - Homepage with CMS-driven content
- API routes: [`api/booking.php`](api/booking.php:1) - Consultation booking endpoint
- Data models / schema: [`admin/config/database.php`](admin/config/database.php:1) - Database connection and credentials
- Auth logic: [`config/security.php`](config/security.php:1) - CSRF tokens, rate limiting, session management, security headers
- Config / env: [`config/env-loader.php`](config/env-loader.php:1) - Environment variable loading, [`.env.local`](.env.local:1) - Local development environment

## Environment
- Dev branch: Unknown (not visible in current workspace)
- Staging branch: Unknown (not visible in current workspace)
- Production branch: Unknown (not visible in current workspace)
- Env files: [`.env.local`](.env.local:1) (local development), production environment variables loaded via [`config/env-loader.php`](config/env-loader.php:1)

## Known Constraints
- Rate limiting uses file-based storage in `/tmp/rate_limit_izende` which may not work across multiple servers
- WordPress blog integration in `/articles` directory with separate configuration
- Admin panel in `/adminIzende` directory with OAuth support (legacy; security headers not applied)
- Session IP validation is disabled by default (set `SESSION_IP_VALIDATION=true` in env to enable)
- CSP report-only monitoring disabled by default (set `CSP_REPORT_ONLY=true` in env/staging to enable)

## Known Risks
- Low: `/adminIzende` panel CSP uses `'unsafe-inline'`/`'unsafe-eval'` due to WHMCS rendering inline scripts; tighten incrementally as WHMCS is upgraded (P2-002 resolved — CSP header now present)
- Low: File-based rate limiting in `/tmp/rate_limit_izende` does not scale across multiple servers (LATER-001 — backlogged)

## Security Hardening Progress (P0-P5)
| Item | Status | Description |
|------|--------|-------------|
| P0-001 | ✅ done | CSP: removed unsafe-inline/unsafe-eval; nonce enforcement (Phase 3) |
| P0-002 | ✅ done | Protected `/admin/config/` from direct HTTP access |
| P1-001 | ✅ done | PHP error logging enabled (`E_ALL`, log to `logs/php-error.log`) |
| P1-002 | ✅ done | Session IP validation — configurable via `SESSION_IP_VALIDATION` env var |
| P1-003 | ✅ done | style-src no longer uses `'unsafe-inline'` — nonce required |
| P1-004 | ✅ done | Removed hardcoded SMTP/email defaults from booking notification |
| P1-005 | ✅ done | Replaced `glob()` with `DirectoryIterator` in rate limit cleanup |
| P2-001 | ✅ done | Centralized env var validation at startup (`validateEnvVars()`) |
| P2-002 | ✅ done | Added CSP header to `/adminIzende` via `.htaccess`; other headers were already present |

---

## AI Website Builder Funnel (added 2026-06-13)

**Product:** Prospect describes their business → AI (GLM-5) generates a live single-page website preview (with a real Gemini hero photo + AI logo icon) → "Claim this site" → pays in WHMCS/PayPal → site auto-provisions onto Izende cPanel hosting. Static site = bait; recurring cPanel hosting = the product. Hard requirement: fully automated, no babysitting. Pilot: a massage therapist who owns a domain.

### Pipeline (as-built, NOT n8n)
- Intake: `ai-website-builder.php` → `api/site-builder-leads.php` → Supabase `site_builder_leads` (status=pending).
- Generation: **cPanel cron** `scripts/generate-pending-previews.php` (GLM-5 streaming). Per lead: enrichBrief → Gemini hero image (`gemini-3.1-flash-image`, best-effort, un-forced) + Gemini logo icon → GLM builds site (CSS-var contract, no-fabrication) → injectBookingScript (wires any booking form) → writePreview (+ injectClaimBar) → email prospect. Images hosted at `/genmedia/<slug>/`. Previews at `/previews/<slug>/`. 14-day expiry (skips samples + claimed/converted; also clears genmedia).
- Claim: preview claim bar + reveal → `claim-site.php` (3-card chooser: Static $39 pid14 / WordPress $49 pid15 MOST POPULAR / Managed $149 pid16) → WHMCS `cart.php?a=add&pid=N`.
- Provision: WHMCS `AfterModuleCreate` hook `adminIzende/includes/hooks/zeno_provision.php` (gated 14/15/16). pid14=static deploy (index.html via WHM→cPanel Fileman). pid15/16=WordPress via shared core `scripts/lib/wp-provision.php` (Softaculous install → canvas mu-plugin + seed.json → 1:1 themed render). pid16 auto-enables booking. Marks lead converted (+plan_kind, wp_admin_url).
- Booking upsell: `api/site-booking.php` (cross-domain, per-site HMAC token, honeypot+rate-limit) → iz_bookings (multi-tenant: tenant_lead_id/tenant_business). Owner view `my-bookings.php` (tokenized, no login). Preview form returns upsell nudge until booking_enabled.

### Key files
- Generator: `scripts/generate-pending-previews.php`
- WP: `scripts/lib/{html-to-wp,izende-canvas,wp-provision}.php`, `scripts/provision-wp.php` (dry-run harness)
- Booking: `api/site-booking.php`, `my-bookings.php`, `scripts/{migrate-bookings-tenant,enable-booking}.php`
- Chooser: `claim-site.php`. Hook: `adminIzende/includes/hooks/zeno_provision.php`.

### Infra / secrets (ALL chat-exposed keys MUST be rotated by owner)
- Supabase project `ocgearsjyqeoscjvcdrz`, table `site_builder_leads` (+cpanel_username/domain, plan_kind, wp_admin_url, booking_enabled, preview_slug).
- GLM-5 via api.z.ai (glm-5; glm-5.2 access not yet granted). Gemini image API (key-param auth). WHM reseller token (izende6). Softaculous enduser API (cPanel Basic auth, soft=26, theme=jupiter). MySQL iz_bookings (+tenant cols). Env: GLM_API_KEY, GEMINI_API_KEY/GEMINI_IMAGE_MODEL, SITE_BOOKING_SECRET, PREVIEW_DEPLOY_SECRET, WHM_*, SUPABASE_*.
- No SSH — deploy via FTP (ai-agent@). Long HTTPS must run on prod (local network can't hold them).

### Status: BUILT + VERIFIED (to extent possible without live paid orders)
Stages 1, 2, 3(static hook), 4(AI editor — presets + queued chat edits, live re-apply: static via Fileman + WordPress via versioned re-seed), 5(WordPress — dry-run PASS on throwaway), Booking upsell core ($20 "Online Booking" add-on live + auto-enable). Owner-gated: real paid order tests (static + WP) — the only true money-loop proof left; GLM-5.2 A/B; rotate all keys.

### UX / offer pass + version control (added 2026-06-14)
- **3-step wizard** on `ai-website-builder.php` (Business → Style → Send it): progress stepper, slide+fade transitions, per-step validation, and a "don't leave while generating" beforeunload guard.
- **Analyze-my-site** (`api/analyze-site.php`, NEW): optional Step-1 URL → SSRF-safe crawl of a few same-host inner pages → GLM drafts a description capturing real services, location, phone, email (never invents). Pre-fills the description field.
- **Professional email on signup:** `zeno_create_mailbox()` auto-creates `hello@<domain>` on every paid tier at provisioning (cPanel UAPI `Email::add_pop`, best-effort, non-blocking); creds in the welcome emails. Proven via throwaway account.
- **Benefit-led plan cards** (`claim-site.php`): outcome names, free-email perk on all tiers, Wix/GoDaddy framing; static scope = technical upkeep only (no content edits promised), managed = fair-use "everyday edits" (redesigns/custom quoted separately).
- **Business name** strips legal suffixes (LLC/Inc) at client + server + generator. **Client emails** now use the office line (314) 312-6441 (886-6356 = AI booking agent; on-page web numbers unchanged).
- **Showcase** now covers all 5 vibes (added Professional & Corporate → Coastal Tax sample in `previews/samples/`).
- **Version control:** the entire previously-untracked funnel was checked into git — PR #1 (feature work) + PR #2 (funnel + working tree) merged to `main` (`34d8cc7`); `main` now reflects production. NOTE: a local working clone may sit behind `origin/main` because FTP-written `previews/` files (owned by another uid) block `git reset --hard` without elevated perms — cosmetic only; remote + prod are authoritative.

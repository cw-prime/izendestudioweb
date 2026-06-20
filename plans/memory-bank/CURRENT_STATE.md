# Current State — Izende Studio Web
Last updated: 2026-06-20

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

**Public positioning (2026-06-15): "Website Drafter."** To reduce AI fatigue, the funnel is branded
around the OUTCOME (a website draft), not the tech. Tool/nav/breadcrumb = "Website Drafter"; the prospect
gets "a website draft" / "your draft" (NOT "first draft" — that implied more). GLM/Gemini still power it,
but visible "AI" copy is minimized; Zeno is the "website draft assistant." The 3-generation cap is enforced
server-side but NOT advertised (no count banner). Internals (file `ai-website-builder.php`, `/ai-website-builder`
URL, form/IDs/endpoints, `gtag('ai_builder_*')`) are unchanged. Lives on branch `funnel-preview-protections-logo-map`.

**Product:** Prospect describes their business → the Website Drafter (GLM-5) prepares a live single-page website draft (with a real Gemini hero photo + logo) → "Claim this site" → pays in WHMCS/PayPal → site auto-provisions onto Izende cPanel hosting. Draft = hook; recurring cPanel hosting = the product. Hard requirement: fully automated, no babysitting. Pilot: a massage therapist who owns a domain.

### Pipeline (as-built, NOT n8n)
- Intake: `ai-website-builder.php` → `api/site-builder-leads.php` → Supabase `site_builder_leads` (status=pending).
- Generation: **cPanel cron** `scripts/generate-pending-previews.php` (GLM-5 streaming). Per lead: enrichBrief → Gemini hero image (`gemini-3.1-flash-image`, best-effort, un-forced) + Gemini logo icon → GLM builds site (CSS-var contract, no-fabrication) → injectBookingScript (wires any booking form) → writePreview (+ injectClaimBar + Customize widget) → email prospect. Images hosted at `/genmedia/<slug>/`. Previews at `/previews/<slug>/`. 14-day expiry (skips samples + claimed/converted; also clears genmedia).
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
Stages 1, 2, 3(static hook), 4(Customize editor — preset colour/font changes persist to `generated_html` before claim; queued text edits live re-apply: static via Fileman + WordPress via versioned re-seed), 5(WordPress — dry-run PASS on throwaway), Booking upsell core ($20 "Online Booking" add-on live + auto-enable). Owner-gated: real paid order tests (static + WP) — the only true money-loop proof left; GLM-5.2 A/B; rotate all keys.

### UX / offer pass + version control (added 2026-06-14)
- **3-step wizard** on `ai-website-builder.php` (Business → Style → Send it): progress stepper, slide+fade transitions, per-step validation, and a "don't leave while generating" beforeunload guard.
- **Generation UX hotfixes (2026-06-20):** build overlay now shows a 2:00 countdown (`Estimated reveal in 2:00` → `Finalizing your draft...`) so users know to keep the tab open; `api/preview-status.php` returns `preview_slug` so the browser can recover the preview route even if `preview_url` lags. The green draft-count coaching pill now appears only after the visitor has already used one draft and says "add more detail for better results."
- **Preview/claim UX hotfixes (2026-06-20):** preview claim bar displays remaining free drafts through `api/generation-count.php`; Customize text requests show the same 2:00 countdown. Color/font/reset presets now call `api/site-builder-edit.php` with `action=theme`, save the selected CSS variables into `generated_html`, re-render the preview, and block "Claim this site" briefly until a pending theme save succeeds. Preview-only claim bar/customize UI is still excluded from the final provisioned customer site.
- **Spam honeypot visibility fix (2026-06-20):** `SpamProtection::generateHoneypot()` now renders the "Website URL (leave blank)" trap as a hidden, non-focusable field and `ai-website-builder.php` has fallback CSS, preventing visible honeypot fields under strict CSP/browser conditions.
- **Analyze-my-site** (`api/analyze-site.php`, NEW): optional Step-1 URL → SSRF-safe crawl of a few same-host inner pages → GLM drafts a description capturing real services, location, phone, email (never invents). Pre-fills the description field.
- **Professional email on signup:** `zeno_create_mailbox()` auto-creates `hello@<domain>` on every paid tier at provisioning (cPanel UAPI `Email::add_pop`, best-effort, non-blocking); creds in the welcome emails. Proven via throwaway account.
- **Benefit-led plan cards** (`claim-site.php`): outcome names, free-email perk on all tiers, Wix/GoDaddy framing; static scope = technical upkeep only (no content edits promised), managed = fair-use "everyday edits" (redesigns/custom quoted separately).
- **Business name** strips legal suffixes (LLC/Inc) at client + server + generator. **Client emails** now use the office line (314) 312-6441 (886-6356 = AI booking agent; on-page web numbers unchanged).
- **Showcase** now covers all 5 vibes (added Professional & Corporate → Coastal Tax sample in `previews/samples/`).
- **Version control:** the entire previously-untracked funnel was checked into git — PR #1 (feature work) + PR #2 (funnel + working tree) merged to `main` (`34d8cc7`); `main` now reflects production. NOTE: a local working clone may sit behind `origin/main` because FTP-written `previews/` files (owned by another uid) block `git reset --hard` without elevated perms — cosmetic only; remote + prod are authoritative.

### Approved positioning change — AI fatigue reduction (added 2026-06-16)
- Owner approved repositioning the funnel away from front-facing "AI Website Builder" / "AI web designer" language and toward **Website Draft by Izende** / **Start with a website draft**.
- Strategic rationale: prospects may have AI fatigue and interpret heavy AI/Zeno language as "another AI widget." The value proposition should lead with outcome: **stop staring at a blank page, get a usable first draft, then refine/launch with Izende**.
- Zeno should stay, but be reframed as a **website draft assistant** that organizes ideas into a first version, not the primary product and not an "AI web designer" hero.
- AI/automation can remain as lower-level support copy: "powered by smart automation" / "guided by Zeno" / "refined with human strategy." Do not hide technology, but do not make it the headline.
- Main target files for copy pass: `ai-website-builder.php` (hero, meta, breadcrumbs, form helper text, Zeno sidebar/showcase, build overlay/reveal strings) and light polish in `claim-site.php` (draft language in plan context). Preserve form IDs/classes/JS selectors and funnel behavior.

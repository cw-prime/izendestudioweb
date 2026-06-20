# Next Actions (Execution Queue)
Last updated: 2026-06-20 (Website Drafter funnel)

## AI Website Builder Funnel — Active Queue (2026-06-14)

### Owner-gated (cannot complete without owner action)
- **Free-first-year-domain offer — DONE + verified 2026-06-16.** WHMCS Free Domain on 14/15/16 set to "registration/transfer only (renew as normal)", eligible terms incl. Monthly, TLD .com. Fixed a junk .com price ($177.98/yr) -> register+renew $24.99/yr (2yr 49.98, 3yr 74.97). Live cart confirmed: Website Draft - Get Online + new .com = $0.00 first year, Renewal ~$24.99/yr. Existing-domain path unaffected.
- **[OWNER] Run one live paid order through WHMCS/PayPal — STATIC (pid14)** to prove the money loop end-to-end (account create → hook → site deploy + mailbox → converted). `todo` — still the only true end-to-end proof.
- **[OWNER] Run one live paid order — WORDPRESS (pid15)** to prove auto WP install+seed + mailbox on a real domain. `todo`
- **[OWNER] Enable glm-5.2 access in z.ai**, then A/B vs glm-5. `todo`
- **[OWNER/SECURITY] Rotate ALL chat-exposed keys** (Anthropic/GLM/Gemini/Supabase/**FTP ai-agent@**/WHM/SITE_BOOKING_SECRET). `todo` — FTP password was recovered from a prior transcript this session; rotate.

### P1 — Conversion copy / positioning
- **Reposition funnel to "Website Drafter" (reduce AI fatigue).** `done` 2026-06-20. Public page/URL is now `website-drafter.php` / `/website-drafter`; legacy `/ai-website-builder` and `/ai-website-builder.php` 301 to it. Tool/nav/breadcrumb = **"Website Drafter"**; output noun stays **"a website draft"**. Internal API/form IDs/endpoints and `gtag('ai_builder_*')` names are intentionally preserved.
- **[NEW] Website Drafter Landing Page Conversion Tweaks.** `todo` — Apply future conversion tweaks to `website-drafter.php`: push the CTA higher (above fold on mobile), add the text "Free Preview • No Credit Card Required" as a green badge above the H1, and add a small secondary trust note under the "Get My Free Draft" submit button ("No credit card required. Free, fast preview.").
- **[NEW] Execute Approved Video Reels.** `todo` — Owner approved all 3 reel concepts from `reel_concepts_2026-W24.md`. Generate prompts via Higgsfield and distribute them staggered across platforms (YouTube Shorts, Facebook Reels, LinkedIn).

### P2 — Hardening / polish
- **Resync / fix local working clone:** `git reset --hard origin/main` is blocked by Permission-denied unlinking FTP-written `previews/` files (owned by another uid). Needs sudo/chown or a fresh clone. Cosmetic — remote+prod authoritative. `todo`
- Version-control the `previews/samples/*` HTML (currently prod-only, like all samples). `todo`
- Async WP provisioning queue — only if synchronous Softaculous install in the hook causes delays (low risk now). `todo`
- Localize hero/logo images into the provisioned account (currently referenced from izende.com /genmedia). `todo`
- Persist editor PRESET colour/font choices to the lead so they carry to the provisioned site. `done` 2026-06-20 — `api/site-builder-edit.php` now supports `action=theme`, updates the lead's `generated_html`, re-renders the preview, and the preview Customize widget saves pending theme changes before the claim link navigates away. AI/text content edits already carried over through the edit queue.
- Optional: Gemini logo as transparent PNG (currently white-bg in a rounded tile — works). `todo`

### Done (funnel) — see HANDOFF_LOG.md for evidence
- Stage 1 intake; Stage 2 generation+preview+email (Gemini hero/logo, design diversity + motion, premium logos); Stage 3 static provisioning hook; Stage 4 AI editor (presets + queued chat edits, live re-apply static+WP); Stage 5 WordPress tier (dry-run PASS); Booking upsell core.
- **$20 "Online Booking" add-on** created + visible (showorder=1) + auto-enable (Managed + add-on present). Mojibake in product/add-on descriptions fixed.
- **3-step animated wizard** + "don't leave while generating" guard.
- **Analyze-my-site** endpoint (SSRF-safe inner-page crawl → GLM description with real services/location/phone/email).
- **Professional email auto-create** on every paid tier (`hello@<domain>`), creds in welcome emails — proven on throwaway.
- **Benefit-led plan cards** with scope guards (static = technical only; managed = fair-use everyday edits).
- **Business name** legal-suffix strip (client+server+generator); **client emails** → office line (314) 312-6441; checkout font fix.
- **Professional & Corporate showcase sample** added (all 5 vibes covered).
- **Funnel checked into git** for the first time — PR #1 + PR #2 merged to `main` (`34d8cc7`).
- **Generation/customize waiting UX + carryover fixes** — 2:00 countdowns on page generation and Customize edits; builder form no longer shows draft-count/detail coaching UI; completed preview claim bar shows remaining free drafts plus "add more detail for better results"; hidden honeypot no longer renders visibly; Customize colour/font presets persist into `generated_html` before claim.

---

## P0 — Blockers (fix before anything else)

### [P0-001] Tighten CSP to remove unsafe-inline and unsafe-eval
- **Status:** `done` (Phase 3 enforced — enforced CSP now uses nonce, no unsafe-inline/unsafe-eval)
- **Severity:** Critical
- **Description:** Remove `'unsafe-inline'` and `'unsafe-eval'` from CSP in [`config/security.php`](config/security.php:411). Implement nonce-based CSP for inline scripts and move inline scripts/styles to external files.
- **Acceptance Criteria:**
  - ✅ CSP no longer contains `'unsafe-inline'` or `'unsafe-eval'`
  - ✅ All inline scripts use nonces — only `book-consultation.php` had an inline `<script>` tag and it was given a nonce in Phase 2
  - ✅ All other root-level public-facing PHP files have no inline `<script>` or `<style>` tags
  - Site functionality remains intact (test booking form, contact forms, Google Analytics) — **tests not run yet**
- **Assigned Mode:** CODE
- **Estimated Effort:** 4-6 hours
- **Note:** Only [`book-consultation.php`](book-consultation.php:1) contained an inline `<script>` requiring a nonce; all other root-level templates use only external `<script src=...>` references.

### [P0-002] Protect admin/config directory from direct access
- **Status:** `done`
- **Severity:** Critical
- **Description:** Add explicit access protection to prevent direct browser access to [`admin/config/database.php`](admin/config/database.php:1) and other sensitive config files.
- **Acceptance Criteria:**
  - ✅ Direct access to `/admin/config/database.php` returns 403/404 (files not accessible)
  - ✅ Direct access to any file in `/admin/config/` returns 403/404
  - PHP includes from within the application still work
  - No regression in admin panel functionality
- **Assigned Mode:** CODE
- **Estimated Effort:** 1-2 hours
- **Implementation:**
  - Root `.htaccess` has `RewriteRule ^admin/config/ - [F,L]` at line 48
  - Added [`admin/config/.htaccess`](admin/config/.htaccess:1) with `Require all denied` for belt-and-suspenders protection
  - Verified: `curl http://localhost/admin/config/database.php` returns 404 (files not served)

## P1 — High Priority (this sprint / today)

### [P1-001] Enable proper error logging instead of suppressing all errors
- **Status:** `done`
- **Severity:** High
- **Description:** Replace `error_reporting(0)` in [`index.php`](index.php:8) with proper error logging configuration that logs to file while not displaying errors to users.
- **Acceptance Criteria:**
  - ✅ `error_reporting(0)` replaced with `error_reporting(E_ALL)`
  - ✅ `display_errors` remains 0
  - ✅ `log_errors` set to 1
  - ✅ `error_log` path set to `__DIR__ . '/logs/php-error.log'` (existing `logs/` directory used)
  - Verify errors are being logged by triggering a test error — **tests not run yet**
- **Assigned Mode:** CODE
- **Estimated Effort:** 2-3 hours
- **Note:** `logs/` directory already existed with prior log files. Log file permissions should be verified to ensure the web server user can write to `logs/php-error.log`. The `.htaccess` in `logs/` should deny direct browser access — verify this is in place.

### [P1-002] Enable session IP validation
- **Status:** `done`
- **Severity:** High
- **Description:** Replace commented-out IP validation in [`config/security.php`](config/security.php:368) with a configurable, /24-subnet-tolerant implementation controlled by the `SESSION_IP_VALIDATION` environment variable.
- **Acceptance Criteria:**
  - ✅ Session IP validation is configurable via `SESSION_IP_VALIDATION` env var
  - ✅ Session is destroyed if IP changes beyond same /24 subnet (when enabled)
  - ✅ Mobile users with minor IPv4 IP changes within same /24 subnet are accommodated — IP updated in session and INFO event logged
  - ✅ Security event is logged on IP mismatch (WARNING) and on subnet shift (INFO)
  - Functional testing not yet run — requires browser/session testing
- **Assigned Mode:** CODE
- **Estimated Effort:** 2-3 hours
- **Configuration note:** Default is `SESSION_IP_VALIDATION=false` (env var absent or not `'true'`), preserving the prior disabled behaviour in production. To enable strict IP validation, set `SESSION_IP_VALIDATION=true` in the server environment or `.env.local`. Recommend enabling after confirming no legitimate users experience unexpected logouts.
- **Follow-up:** Verify that setting `SESSION_IP_VALIDATION=true` in staging does not cause session disruption for regular desktop and mobile users before enabling in production.

### [P1-003] Remove unsafe-inline from style-src CSP directive
- **Status:** `done`
- **Severity:** High
- **Description:** Remove `'unsafe-inline'` from `style-src` in [`config/security.php`](config/security.php:415). Move all inline styles to external CSS files or use nonce-based CSP.
- **Acceptance Criteria:**
  - ✅ `style-src` no longer contains `'unsafe-inline'` (Phase 3 CSP enforcement)
  - ✅ `<style>` blocks in public-facing pages updated to use nonces:
    - [`assets/includes/domain-search.php`](assets/includes/domain-search.php:61) — nonce via `$_domainNonceAttr` from page context
    - [`config/error-handler.php`](config/error-handler.php:193) — nonce generated in `displayErrorPage()`
  - ✅ Inline `style=""` attributes unaffected (not blocked by CSP `style-src`)
  - ✅ `admin/login.php` excluded — no CSP header emitted (tracked under P2-002)
  - Site styling tested: tests not run yet
- **Assigned Mode:** CODE
- **Estimated Effort:** 3-4 hours

### [P1-004] Remove SMTP/admin defaults from booking notification
- **Status:** `done`
- **Severity:** High
- **Description:** Remove hardcoded SMTP and admin email defaults from [`api/booking.php`](api/booking.php:204). Ensure all email configuration is loaded from environment variables with proper validation.
- **Acceptance Criteria:**
  - ✅ No hardcoded SMTP credentials or email addresses in code
  - ✅ All email configuration comes from environment variables (`BOOKING_ADMIN_EMAIL`, `MAIL_TO`, `BOOKING_FROM_EMAIL`, `MAIL_FROM`, `SMTP_HOST`, `SMTP_USERNAME`, `SMTP_PASSWORD`, `SMTP_PORT`)
  - ✅ Missing/invalid env vars cause early return with descriptive log message — no fallback to hardcoded values
  - Email notifications still work in production when env vars are set — **test not run yet**
- **Assigned Mode:** CODE
- **Estimated Effort:** 1-2 hours
- **Implementation:** Updated `sendBookingNotification()` in [`api/booking.php`](api/booking.php:209) to use `getenv()` with no fallback defaults; added empty-string checks before email validation

### [P1-005] Optimize rate limiting file cleanup to prevent resource exhaustion
- **Status:** `done`
- **Severity:** High
- **Description:** Replace `glob()` in [`config/security.php`](config/security.php:156) with a more efficient file cleanup mechanism that doesn't load all files into memory at once.
- **Acceptance Criteria:**
  - ✅ `glob()` is replaced with `DirectoryIterator`
  - ✅ File cleanup is efficient even with thousands of rate limit files
  - Rate limiting functionality remains intact — **test not run yet**
- **Assigned Mode:** CODE
- **Estimated Effort:** 1-2 hours
- **Implementation:** Replaced `glob()` in [`cleanupRateLimitFiles()`](config/security.php:148) with a `DirectoryIterator` loop with `UnexpectedValueException` error handling

## P2 — Standard (next available slot)

### [P2-001] Implement centralized environment variable validation
- **Status:** `done`
- **Description:** Centralized validation function for all environment variables — required values validated at startup.
- **Acceptance Criteria:**
  - ✅ `validateEnvVars()` in [`config/env-loader.php`](config/env-loader.php:226) validates all required vars at startup
  - ✅ `MAIL_TO` and `MAIL_FROM` moved from optional defaults to required vars — CRITICAL error logged if missing
  - ✅ `forms/contact.php`, `forms/lead-capture.php`, `quote.php` no longer use hardcoded email fallbacks — validate env vars at runtime and fail loudly
  - ✅ `setDefaultEnvVariables()` no longer contains hardcoded company email addresses
- **Assigned Mode:** CODE
- **Estimated Effort:** 2-3 hours

### [P2-002] Add security headers to all admin panel pages
- **Status:** `done`
- **Description:** Ensure all admin panel pages in `/adminIzende` have the same security headers as the main site. The `/admin` panel (our custom CMS) is already covered via `admin/config/auth.php`. The `/adminIzende` directory (legacy WHMCS-style panel) has its own `.htaccess` but no security headers.
- **Acceptance Criteria:**
  - ✅ All `/adminIzende` pages have CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy — implemented via [`adminIzende/.htaccess`](adminIzende/.htaccess:34)
  - `/adminIzende` functionality remains intact — **functional tests not yet run**
  - ✅ CSP is intentionally permissive for WHMCS compatibility (`'unsafe-inline'`, `'unsafe-eval'` in `script-src`; Google Fonts, reCAPTCHA, and common payment gateways in `frame-src`); tighten incrementally as WHMCS is upgraded
- **Assigned Mode:** CODE
- **Estimated Effort:** 2-3 hours
- **Note:** X-Frame-Options, X-Content-Type-Options, X-XSS-Protection, Referrer-Policy, HSTS, and Permissions-Policy were already present in [`adminIzende/.htaccess`](adminIzende/.htaccess:34). Only the `Content-Security-Policy` header was missing; it has now been added.

## Later (Scale / Nice to Have)

### [LATER-001] Implement database-based rate limiting
- **Status:** `todo`
- **Description:** Replace file-based rate limiting with database-backed rate limiting for better scalability and multi-server support.
- **Acceptance Criteria:**
  - Rate limiting data stored in database
  - Performance is comparable or better than file-based
  - Works across multiple application servers
- **Assigned Mode:** CODE
- **Estimated Effort:** 4-6 hours

### [LATER-002] Add comprehensive security monitoring and alerting
- **Status:** `todo`
- **Description:** Implement automated security monitoring with alerts for critical security events (failed logins, rate limit exceeded, session hijack attempts).
- **Acceptance Criteria:**
  - Security events are monitored in real-time
  - Critical events trigger email/SMS alerts
  - Dashboard for viewing security events
- **Assigned Mode:** CODE
- **Estimated Effort:** 8-12 hours

## Status Legend
- `todo` not started
- `in_progress` actively being worked
- `blocked` waiting on decision/dependency
- `done` completed and verified

## P2 — CSP Hardening Prep — Phase 2: Nonce Plumbing
- **Status:** `in_progress`
- **Severity:** Critical
- **Description:** Add CSP nonce attributes to inline `<script>` and `<style>` tags in public-facing templates (root-level `*.php`, excluding `admin/` and `adminIzende/`). Use the existing [`getCSPNonce()`](config/security.php:466) from [`config/security.php`](config/security.php:1). If a file outputs inline scripts/styles but doesn't have a nonce variable, add `$nonce = getCSPNonce();` near the top (before output) and apply `nonce="<?= htmlspecialchars($nonce, ENT_QUOTES) ?>"` to each inline `<script>`/`<style>` tag. Do not alter external `<script src=...>` tags. Leave the enforced CSP unchanged.
- **Acceptance Criteria:**
  - All inline scripts in public-facing templates have nonce attributes
  - All inline styles in public-facing templates have nonce attributes
  - External scripts remain unchanged
  - CSP policy in [`config/security.php`](config/security.php:1) remains unchanged
- **Assigned Mode:** CODE
- **Estimated Effort:** 2-3 hours
- **Implementation:**
  - Added `$nonce = getCSPNonce();` to [`book-consultation.php`](book-consultation.php:1) after [`setSecurityHeaders()`](config/security.php:399) call
  - Applied `nonce="<?= htmlspecialchars($nonce, ENT_QUOTES) ?>"` to inline `<script>` tag at line 169
- **Testing:** Tests not run yet
- `todo` not started
- `in_progress` actively being worked
- `blocked` waiting on decision/dependency
- `done` completed and verified

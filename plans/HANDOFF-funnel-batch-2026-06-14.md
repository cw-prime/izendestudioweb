# HANDOFF — AI Website Builder funnel batch (2026-06-14)

Agent handing off mid-implementation. Plan is approved and lives at
`/home/megatron/.claude/plans/we-need-to-plan-lucky-pearl.md` (8 parts). This doc tells you
what's DONE, what's LEFT, how to deploy, and which keys you need.

**Working dir:** `/var/www/html/izendestudioweb` (local clone; in sync with `origin/main`).
**Nothing in this batch is deployed yet** — all edits are local. Lint + deploy at the end.

---

## SECRETS / API KEYS — read them, don't hardcode

All keys already exist in **`/var/www/html/izendestudioweb/.env.local`** (local) and on prod in
**`config/.env`**. Read values from there at runtime via `getEnv('NAME')` (web) / `envOr('NAME')`
(generator). Do NOT paste secret values into code, commits, or this repo.

| Env var | Used by | Notes |
|---|---|---|
| `GLM_API_KEY` | generator + `api/analyze-site.php` | z.ai (`api.z.ai`), model `glm-5` |
| `GEMINI_API_KEY`, `GEMINI_IMAGE_MODEL`, `GEMINI_LOGO_MODEL` | generator images | hero + logo |
| `SUPABASE_URL`, `SUPABASE_SERVICE_ROLE_KEY` | leads/edits/generator | project `ocgearsjyqeoscjvcdrz` |
| `SITE_BOOKING_SECRET` | booking HMAC **+ the new gen-cap cookie signing** | server-only |
| `PREVIEW_DEPLOY_SECRET` | generator web-trigger token | |
| `WHM_HOST`, `WHM_USER`, `WHM_API_TOKEN` | provisioning + mailbox | reseller `izende6` |
| `RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY` | optional | only enforced if set |
| **`GOOGLE_MAPS_EMBED_KEY`** | **NEW — Part 6 map** | **NOT set yet. OWNER must create a Maps *Embed API* key (free), domain-restricted, add to prod `config/.env`. Until set, maps are silently absent (intended).** |

**Deploy credential (FTP):** user `ai-agent@izendestudioweb.com`, host `ftp.izendestudioweb.com`.
The password is **not** in any env file — get it from the owner, or recover it from the prior session
transcript at `/home/megatron/.claude/projects/-var-www-html-izendestudioweb/<session>.jsonl`
(grep for `curl ... ftp://`). Deploy each changed file to the same relative path under `/` (web root).
Example: `curl -T claim-site.php "ftp://ftp.izendestudioweb.com/claim-site.php" -u "ai-agent@izendestudioweb.com:PASS"`.

**OWNER reminder:** ALL chat-exposed keys (incl. the FTP password) still need rotation.

---

## DB migration — DONE (already live)

Supabase migration applied to `site_builder_leads`: added `logo_url text`, `brand_colors text`,
`business_address text`. No further DB work needed. (Generator selects `*`, so these are in `$lead`.)

Advisory noticed: `public.site_builder_edits` has **RLS disabled** — pre-existing, server uses the
service-role key so it functions, but flag to owner. Don't enable RLS without policies (would block access).

---

## STATUS BY PART

### Part 1 — Free-generation cap (3/visitor) — ✅ DONE (needs deploy + verify)
- NEW `includes/gen-cap.php` — `iz_gen_read()` / `iz_gen_bump()`, signed cookie `iz_genc`
  (`count.ts.hmac`, HMAC via `SITE_BOOKING_SECRET`), `IZ_GEN_LIMIT=3`, 30-day reset.
- `api/site-builder-leads.php` — requires gen-cap; gates with `{success:false,limit:true,claim_url}`
  when remaining ≤ 0; per-IP/24h Supabase backstop (`>8` → 429); `iz_gen_bump()` on success.
- `ai-website-builder.php` — requires gen-cap; `$genRemaining`/`$genExhausted`; "N of 3 left" alert
  banner; when exhausted the form is replaced by a claim-CTA card; submit JS guarded
  (`const _aiForm = ...; if (_aiForm) ...`) and handles `result.limit`.

### Part 3 — Logo upload + brand colors — ✅ DONE (needs deploy + **mkdir genmedia/uploads on prod**)
- NEW `api/upload-logo.php` — multipart, CSRF + rate-limit, `finfo`+`getimagesize` validation
  (png/jpg/webp/gif, ≤5 MB, NO svg), saves to `genmedia/uploads/logo-<rand>.<ext>`, GD palette
  extraction (`iz_logo_palette`, graceful null), returns `{success,url,brand_colors}`.
- `ai-website-builder.php` — Step 2 "Upload your logo" field + JS (POST multipart → sets
  `window.izLogoUrl` + `window.izBrandColors`, shows swatches); submit `data` now sends
  `logo_url`, `brand_colors`, `business_address` (from `window.*`).
- `api/site-builder-leads.php` — validates `logo_url` (must match
  `^https://izendestudioweb\.com/genmedia/uploads/[\w-]+\.(png|jpe?g|webp|gif)$`), `brand_colors`
  (hex array → JSON), `business_address` (≤200, control chars stripped); stored on the lead.
- `api/analyze-site.php` — `iz_contact_details()` now sets `&$firstAddress`; endpoint returns
  `business_address`; the wizard's analyze JS stores it in `window.izBusinessAddress`.
- Generator `generateLogoMark()` — returns the uploaded logo URL as-is if present (skips Gemini).
- Generator `buildPrompts()` — uploaded logo gets the **prominent header+footer, CSS-size-safe**
  instruction (`max-height:clamp(36px,5vw,52px); width:auto; object-fit:contain`); AI icon keeps the
  small-tile instruction. Brand colors injected only when `style_vibe` is empty (AI-choose).
- **ACTION:** create `genmedia/uploads/` on prod (web-writable) — `api/upload-logo.php` mkdirs it,
  but verify perms (cPanel account user). Add `genmedia/uploads/.htaccess` (deny script exec) — see Part 2.

### Part 8 — Representative hero people — ✅ DONE (needs deploy)
- `generateHeroImage()` prompt now instructs Gemini to depict authentic people consistent with the
  clientele/community in the description (ethnicity/age/families/profession), else relevant people.

### Part 6 — Embedded map — ⚠️ PARTIAL (owner-gated on `GOOGLE_MAPS_EMBED_KEY`)
- DONE: `buildPrompts()` tells GLM to place a `<!--IZ_MAP-->` placeholder in the contact section
  **only** when `GOOGLE_MAPS_EMBED_KEY` is set AND `$lead['business_address']` present.
- **TODO:** write an `injectMap($html, $lead)` server-side step that REPLACES `<!--IZ_MAP-->` with a
  responsive Maps Embed iframe
  (`https://www.google.com/maps/embed/v1/place?key=<KEY>&q=<urlencoded business_address>`, wrapped:
  `<div style="position:relative;aspect-ratio:16/7;max-width:100%"><iframe ... loading="lazy" title="Map"
  style="position:absolute;inset:0;width:100%;height:100%;border:0"></iframe></div>`), and STRIPS the
  placeholder when no key/address. **Wire it into the main loop** right after `generateWithGlm()` returns
  and before/after `injectBookingScript()` (it edits stored `generated_html`, so include it before the
  Supabase save so claimed/WP sites keep the map). Keep the API key OUT of the GLM prompt (placeholder
  approach already does this).

### Part 2 — Preview anti-copy — ❌ TODO
- `writePreview()` (generate-pending-previews.php): inject `<meta name="robots" content="noindex,nofollow">`
  into the preview `<head>` (preview FILE only — NOT the stored clean `generated_html`; the claimed live
  site must stay indexable). Inject alongside the claim-bar/editor injection.
- `previews/.htaccess` (exists): add `Header set X-Robots-Tag "noindex, nofollow"`.
- NEW `genmedia/.htaccess`: hotlink protection (allow empty Referer + `izendestudioweb.com`; block others)
  so stolen off-domain copies show broken hero/logo images; also forbid script execution.
- NEW `genmedia/uploads/.htaccess`: deny script execution (images only).

### Part 4 — Claim urgency (7-day hold + countdown) — ❌ TODO
- Expiry sweep in generate-pending-previews.php: change unclaimed-preview expiry **14 → 7 days**
  (claimed/converted untouched — keep that guard).
- `injectClaimBar()`: bake the expiry epoch (`created_at + 7 days`) and a tiny inline script that
  computes days-left on each view → render "⏳ Reserved for you — N days left" by the Claim button.
  (Preview is a standalone file → inline script OK, like the booking/editor scripts. Pass `created_at`
  into `writePreview`/`injectClaimBar`; it's on `$lead`.)

### Part 5 — Editor "applying" countdown — ❌ TODO
- `injectEditorWidget()` (the Customize box): in the post-submit "applying your change…" state, add a
  visible ~60s countdown / shrinking bar ("Building your change — ready in ~45s…") + explicit
  "No need to refresh — we'll refresh for you when it's done." Keep existing poll + auto-reload.
  Reduced-motion safe.

### Part 7 — Confetti burst on reveal — ❌ TODO
- `ai-website-builder.php` `revealSite(url)` (watch-it-build IIFE): fire a self-contained, CSP-safe
  confetti burst (no external lib — strict nonce CSP) once as `.build-reveal` shows; ~80 particles or a
  fixed canvas, brand blues, auto-remove ~2.5s, `pointer-events:none`, high z-index, skip under
  `prefers-reduced-motion`. All JS in the existing nonce'd `<script>`, keyframes in the nonce'd `<style>`.

---

## FINISH / DEPLOY CHECKLIST
1. Finish Parts 6(injectMap), 2, 4, 5, 7.
2. `php -l` every changed/new PHP file (clean).
3. Deploy via FTP (see creds section) — files touched this batch:
   `includes/gen-cap.php`, `api/site-builder-leads.php`, `api/upload-logo.php`, `api/analyze-site.php`,
   `ai-website-builder.php`, `scripts/generate-pending-previews.php`, `previews/.htaccess`,
   `genmedia/.htaccess`, `genmedia/uploads/.htaccess`.
4. Ensure `genmedia/uploads/` exists + is web-writable on prod.
5. Verify per the plan's Verification section (cap banner/gate, noindex headers, hotlink block, logo
   upload + palette, 7-day countdown, editor countdown, confetti, representation; map only after the
   owner adds `GOOGLE_MAPS_EMBED_KEY`).
6. Commit on a branch + PR to `main` (repo: `git@github.com:cw-prime/izendestudioweb.git`). Note: some
   `previews/` files are owned by `www-data` locally — if `git` chokes, `mv` the offending dir aside
   (rename only needs write on the parent), it's cosmetic.
7. Append a `plans/memory-bank/HANDOFF_LOG.md` entry when done.

## Gotchas
- Generator runs on PROD (long HTTPS dies locally). Trigger:
  `GET /scripts/generate-pending-previews.php?token=<PREVIEW_DEPLOY_SECRET>` (browser UA — prod
  ModSecurity 406s non-browser UAs). Single-instance lock → may say "busy" if the 5-min cron is mid-run.
- Client emails use office line **(314) 312-6441**; **886-6356 is the AI booking agent** (leave on-page
  web numbers as-is).
- Business name strips legal suffixes (LLC/Inc) at client+server+generator — keep consistent.

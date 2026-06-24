# Website Drafter — Social Links + Customer Photo Uploads (APPROVED PLAN)

Status: **approved 2026-06-22, not yet implemented** (owner scheduled for "later this week").
Branch to use: `funnel-preview-protections-logo-map`. Cleanly splittable into TWO independent PRs
(social links · photo uploads) — ship either without the other.

## Context
Two intake additions to the draft funnel:
1. **Social media links** — let a prospect enter their social URLs so the generated site shows real,
   clickable social icons (today generated sites have a footer but no social links).
2. **Customer photo uploads** — let a prospect upload their own photos to use as content images. These
   *replace* generated support photos toward the content-aware target, which **cuts image-generation cost**
   (each upload = one fewer Gemini call). The **hero stays a generated image** (ours), never user-uploaded.

Both follow patterns that already exist: the logo upload endpoint, the `<!--IZ_MAP-->` server-injection
placeholder, the uploaded-logo detection branch, and the SUPPORTING PHOTOS prompt block.

## Decisions locked (with the owner)
- **Social platforms (7):** Facebook, Instagram, X (Twitter), LinkedIn, TikTok, YouTube, Google Business
  Profile. Rendered as icons in the **footer**.
- **Photo mixing:** uploads count toward the existing content-aware target (2–7); generate only
  `max(0, target − uploadedCount)`. Up to **7 uploads**. **Hero always generated.**
- **Photo quality gate:** reject uploads under **~1000px on the long edge** (object-fit:cover handles
  cropping/aspect after that).

## Reuse (don't reinvent)
- Upload pattern: `api/upload-logo.php` — CSRF (`validateCSRFToken`), rate-limit (`checkRateLimit`),
  `getimagesize()`+`finfo_file()` MIME check, store under `/genmedia/uploads/`, return URL.
- Server-injection pattern: `injectMap()` + the `<!--IZ_MAP-->` placeholder in
  `scripts/generate-pending-previews.php` (called in the main loop right after generation, beside
  `injectBookingScript()`).
- Uploaded-asset detection regex:
  `^https://izendestudioweb\.com/genmedia/uploads/[\w-]+\.(?:png|jpe?g|webp|gif)$`.
- Content-aware count logic already in `generateSupportImages()`.
- CSP-safe intake JS goes in the page's existing nonce'd `<script>` (the logo upload already does this).

## A. Social media links
- **Intake — `website-drafter.php` Step 2** (where the logo upload lives): a clearly-optional collapsible
  "Add your social links" block, one URL input per platform (`name="social_facebook"` … `social_google`).
  Collect into the submit payload as a `social_links` object.
- **API — `api/site-builder-leads.php`:** validate each URL (`filter_var FILTER_VALIDATE_URL` + https +
  length cap); build `{platform: url}` map; store as `social_links` JSON on the `$lead` insert (mirror the
  `brand_colors` JSON handling).
- **Generator — `scripts/generate-pending-previews.php`:**
  - In `buildPrompts()` (after the LOGO block), when `social_links` present, instruct GLM to put the exact
    comment `<!--IZ_SOCIAL-->` in the footer next to the copyright — same approach as `<!--IZ_MAP-->`.
  - New `injectSocial($html, $lead)`: replace `<!--IZ_SOCIAL-->` with a styled row of **inline brand-SVG
    icons** (fixed server-side platform→SVG-path map, monochrome/theme-colored) wrapped in
    `<a href="…" target="_blank" rel="noopener noreferrer">`. Strip the placeholder if no socials. Call it in
    the main loop beside `injectMap()`/`injectBookingScript()`. Server-injecting guarantees correct icons +
    links instead of relying on GLM to draw brand glyphs.

## B. Customer photo uploads
- **New endpoint — `api/upload-photo.php`** (clone `api/upload-logo.php`): CSRF + rate-limit (≈15/600s/IP) +
  MIME whitelist (png/jpg/webp/gif) + max ~10MB, **plus a min-dimension gate** (reject if
  `max(width,height) < 1000` via `getimagesize()`). Store `/genmedia/uploads/photo-<hex>.<ext>`; return
  `{success, url, width, height}`.
- **Intake — `website-drafter.php` Step 2:** optional multi-file picker "Add your own photos"; on select,
  upload each file to `api/upload-photo.php`, collect returned URLs into a JS array (cap 7), show thumbnails
  with a remove control, surface the min-size rejection message. Include `uploaded_photos` (array) in submit.
- **API — `api/site-builder-leads.php`:** whitelist each URL against
  `^https://izendestudioweb\.com/genmedia/uploads/photo-[\w-]+\.(?:png|jpe?g|webp|gif)$`, cap 7, store as
  `uploaded_photos` JSON on `$lead`.
- **Generator — `scripts/generate-pending-previews.php`:** `generateSupportImages()` (or its caller) reads
  validated `uploaded_photos`; computes the existing content-aware `target` (2–7); uses the uploads first,
  then generates only `target − count(uploads)` (≥0); returns the **combined** array (uploads + generated)
  for the existing SUPPORTING PHOTOS block (already styles all "real photos" with object-fit:cover — no
  prompt change needed). `generateHeroImage`/logo untouched; uploads never reach `$heroUrl`.

## Supabase schema — ✅ DONE 2026-06-22
Both nullable text columns `social_links` and `uploaded_photos` are already added to
`public.site_builder_leads` (migration `add_social_links_and_uploaded_photos_to_site_builder_leads`,
PostgREST schema reloaded). Store JSON strings (like `brand_colors`). No migration needed — just write to
these columns from `api/site-builder-leads.php`.

## Files to modify
- `website-drafter.php` — Step 2 optional social + photo inputs; nonce'd JS to upload photos + collect URLs.
- `api/site-builder-leads.php` — validate + store `social_links` and `uploaded_photos`.
- `api/upload-photo.php` — NEW (clone of upload-logo.php + min-size gate).
- `scripts/generate-pending-previews.php` — `injectSocial()` + social prompt block; photo-mixing in
  `generateSupportImages()`; wire both into the main loop.
- Supabase `site_builder_leads` — 2 new columns.

## Verification (end-to-end)
1. Insert a `pending` test lead (Supabase MCP, project `ocgearsjyqeoscjvcdrz`) with `social_links` +
   `uploaded_photos` set → cron generates → confirm: footer renders correct, clickable social icons; content
   sections use the uploaded photos (then generated ones to reach target); **hero is generated, not an
   upload**; generated support count == `target − uploads`. Throwaway-clean (delete lead + `/previews/<slug>/`
   + `/genmedia/<slug>/`).
2. Upload-endpoint checks: a <1000px image is rejected with the min-size message; a valid image returns a
   `/genmedia/uploads/photo-*.{ext}` URL; CSRF/rate-limit enforced.
3. Submit through the real wizard once to confirm the fields post and validate end-to-end.
4. Strict-CSP: intake JS in the nonce'd block; generated-site social links are same-origin inline SVG +
   external `href` (no blocked sub-resources). `php -l` all; FTPS deploy; commit; update `HANDOFF_LOG.md`.

## Out of scope / notes
- Hero is never user-uploaded (locked); uploads only fill content/support slots.
- Social icons are server-injected for correctness (not GLM-drawn).
- Uploaded photos beyond the target are stored but simply unused that generation; object-fit:cover handles
  odd aspect ratios after the min-size gate.

## Deploy/test methods (for the executing agent)
- Deploy: FTPS via `curl -T <file> ftp://ftp.izendestudioweb.com/<path>` with the `ai-agent@` creds
  (in session env / prior handoff — do NOT hardcode in repo). Expect `226`.
- Generation runs on a ~5-min cron; the concurrency lock (status `generating`) added 2026-06-22 prevents
  overlapping runs — a 7-photo glm-5.2 draft can exceed 5 min, so don't be alarmed by long generations.
- glm-5.2 is the live model (`GLM_MODEL` env, default glm-5.2). Watch the z.ai balance during testing.

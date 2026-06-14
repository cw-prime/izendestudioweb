# Stage 2 — Site Generation Prompt (for the n8n Claude node)

Paste these into the n8n **HTTP Request → Anthropic Messages** node (or a LangChain
Anthropic node). The node reads a `pending` row from Supabase and fills the
`{{ }}` placeholders from that row.

**REQUIRED node config (validated empirically 2026-06-12 — do not change blind):**
- Model: `claude-sonnet-4-6`, `max_tokens: 32000`, **`thinking: {type:"disabled"}`**.
  With adaptive thinking this prompt rabbit-holes: measured 16K AND 32K tokens of
  pure thinking with ZERO html output (`stop_reason: max_tokens`, ~$0.25–0.50
  burned per attempt). Thinking off → fast, cheap, complete sites.
- **Set the n8n HTTP node timeout ≥ 300000 ms** — generation takes minutes; the
  default timeout will abort it. If the node supports it, prefer streaming.
- **Send a browser-like User-Agent header on the preview-deploy POST** to
  izendestudioweb.com — its ModSecurity 406-blocks non-browser UAs.
- Deploy URL must be the explicit `.php` path:
  `https://izendestudioweb.com/api/preview-deploy.php` (clean URLs 404 under /api).

The model must return **ONLY raw HTML** (one complete `<!DOCTYPE html>` file). The
n8n node then writes that body straight to the preview location — so any markdown
fences, preamble, or commentary would break the deploy. The system prompt forbids
them; also strip a leading ```html / trailing ``` defensively in the node.

---

## System prompt

```
You are a senior web designer at a professional web studio. You build a complete,
production-ready, single-page marketing website for a small business from a short
brief.

Output rules (CRITICAL):
- Return ONE complete HTML document and NOTHING else. Start at <!DOCTYPE html> and
  end at </html>. No markdown code fences, no explanation, no preamble.
- Everything inline in that one file: a single <style> block in <head>. No external
  build step, no JS frameworks. A small amount of vanilla JS is fine (mobile menu,
  smooth scroll) but the site must look and work with JS disabled.
- You MAY use Google Fonts via <link> and may use CSS only. No other external assets
  except Unsplash/picsum-style placeholder image URLs if you need photography.
- Mobile-first and fully responsive. Must look great at 375px and 1280px.
- Accessible: semantic landmarks, alt text, sufficient color contrast, focus states.

Required sections, in order:
1. Sticky header with the business name/logo text + anchor nav.
2. Hero: headline, one-sentence subhead, primary call-to-action button.
3. About: 2-3 short paragraphs in the business's voice.
4. Services: 3-6 cards. Include prices ONLY if the brief implies them; otherwise
   describe the offering without inventing prices.
5. A trust/why-us strip (benefits, credentials, or testimonials — invent realistic,
   non-defamatory placeholder testimonials clearly written as samples if none given).
6. Contact: business hours if implied, a click-to-call tel: link, a mailto: link,
   and a simple contact form (posts nowhere yet — use action="#" and a note). Embed a
   Google Maps iframe ONLY if a real address is given; otherwise omit the map.
7. Footer with copyright + the business name.

Theming contract (REQUIRED — enables the AI editor to re-theme without you):
- Define ALL colors and fonts as CSS custom properties on :root, using EXACTLY
  these names, and reference them everywhere (no hardcoded hex or font-family in
  any other rule):
    --c-bg              page background
    --c-surface         cards / raised sections
    --c-text            body text
    --c-heading         heading text
    --c-muted           secondary text
    --c-border          borders / dividers
    --c-accent          primary / buttons / links
    --c-accent-contrast text that sits on --c-accent
    --font-heading      heading font stack
    --font-body         body font stack
    --radius            base corner radius
- You MAY add extra --c-accent-2 etc. for richness, but the names above MUST all
  be present and used. This lets a downstream tool re-skin the whole site by
  overwriting these eleven values alone.

Design rules:
- Match the requested STYLE/VIBE in palette, type, spacing, and imagery mood
  (set the vibe by choosing the VALUES of the variables above).
- Avoid generic "AI slop": no Inter/Roboto/Arial-only system stacks, no purple-on-white
  gradient cliché, no cookie-cutter three-equal-cards-and-done. Give it a distinct,
  cohesive look appropriate to THIS business and vibe. Use tasteful micro-interactions
  (hover states, subtle transitions).
- Real, specific copy derived from the brief — never lorem ipsum.
```

## User message (filled from the Supabase row)

```
Business name: {{ $json.business_name }}
What the business does: {{ $json.business_description }}
Style / vibe: {{ $json.style_vibe || "choose the most fitting style for this business" }}
Contact email (for the mailto link): {{ $json.contact_email }}
Contact phone (for the tel link, if present): {{ $json.contact_phone }}

Build the complete single-file website now. Output only the HTML.
```

---

## Style/vibe → design direction (the node passes the value through; this is guidance the model already infers)

| style_vibe value          | Palette direction                        | Type direction              |
|---------------------------|------------------------------------------|-----------------------------|
| (empty / "Let AI choose") | choose to fit the business               | choose to fit               |
| Clean & Modern            | blues + cool grays + white               | clean sans-serif            |
| Warm & Welcoming          | terracotta / amber / cream               | humanist serif + sans       |
| Bold & Colorful           | saturated multi-color, high contrast     | bold display sans           |
| Elegant & Minimal         | black / white / gold, lots of whitespace | refined serif               |
| Professional & Corporate  | navy / slate / light gray                | dependable sans             |

## After generation (n8n node logic)
1. Strip any stray ```html fences (defensive).
2. Write to preview path → `https://preview.izendestudioweb.com/<lead-id>/index.html`.
3. `UPDATE site_builder_leads SET status='preview_live', preview_url=..., generated_html=...`.
4. Brevo email: preview link + "Claim this site" CTA.

A reference of the expected output quality lives at `previews/serenity-massage-therapy/`
(hand-built with this exact prompt as the quality bar / pilot demo).

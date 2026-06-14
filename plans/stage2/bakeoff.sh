#!/usr/bin/env bash
# AI Site Builder — generation bake-off harness
# Runs the EXACT Stage 2 generation prompt + pilot brief through a provider,
# one shot, strips fences, deploys to previews/bakeoff-<label>/index.html.
#
# Usage:
#   ./bakeoff.sh claude  sk-ant-...        # Anthropic Sonnet 4.6
#   ./bakeoff.sh glm     <zhipu-key>       # GLM-5 (Z.ai / Zhipu, OpenAI-compatible)
#
# Compare at: http://localhost/izendestudioweb/previews/bakeoff-claude/index.html
#             http://localhost/izendestudioweb/previews/bakeoff-glm/index.html

set -euo pipefail
PROVIDER="${1:?usage: bakeoff.sh <claude|glm> <api-key>}"
KEY="${2:?usage: bakeoff.sh <claude|glm> <api-key>}"
ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
OUT="$ROOT/previews/bakeoff-$PROVIDER"
mkdir -p "$OUT"

SYSTEM_PROMPT=$(cat <<'EOF'
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
   and a simple contact form (posts nowhere yet — use action="#" and a note).
7. Footer with copyright + the business name.

Theming contract (REQUIRED):
- Define ALL colors and fonts as CSS custom properties on :root, using EXACTLY
  these names, and reference them everywhere (no hardcoded hex or font-family in
  any other rule): --c-bg --c-surface --c-text --c-heading --c-muted --c-border
  --c-accent --c-accent-contrast --font-heading --font-body --radius
- You MAY add extra --c-accent-2 etc., but the names above MUST all be present.

Design rules:
- Match the requested STYLE/VIBE in palette, type, spacing, and imagery mood.
- Avoid generic "AI slop": no Inter/Roboto/Arial-only system stacks, no purple-on-white
  gradient cliché, no cookie-cutter three-equal-cards-and-done. Give it a distinct,
  cohesive look appropriate to THIS business and vibe. Tasteful micro-interactions.
- Real, specific copy derived from the brief — never lorem ipsum.
EOF
)

USER_PROMPT=$(cat <<'EOF'
Business name: Serenity Massage Therapy
What the business does: Licensed massage therapist offering deep tissue, prenatal, and relaxation massage in St. Louis. Open Tue-Sat, accepts online bookings.
Style / vibe: Warm & Welcoming
Contact email (for the mailto link): hello@serenitymassagestl.com
Contact phone (for the tel link, if present): (314) 555-1234

Build the complete single-file website now. Output only the HTML.
EOF
)

echo "== generating with $PROVIDER (streaming) =="
SSE="$OUT/.stream.sse"
if [ "$PROVIDER" = "claude" ]; then
  jq -n --arg sys "$SYSTEM_PROMPT" --arg usr "$USER_PROMPT" \
    '{model:"claude-sonnet-4-6", max_tokens:32000, stream:true, thinking:{type:"disabled"},
      system:$sys, messages:[{role:"user",content:$usr}]}' |
    curl -sS -N --http1.1 https://api.anthropic.com/v1/messages \
      -H "x-api-key: $KEY" \
      -H "anthropic-version: 2023-06-01" \
      -H "content-type: application/json" \
      --data @- --max-time 900 > "$SSE"
  HTML=$(grep '^data: ' "$SSE" | sed 's/^data: //' |
    jq -rj 'select(.type=="content_block_delta" and .delta.type=="text_delta") | .delta.text')
  USAGE=$(grep '^data: ' "$SSE" | sed 's/^data: //' |
    jq -c 'select(.type=="message_delta") | .usage' | tail -1)
elif [ "$PROVIDER" = "glm" ]; then
  jq -n --arg sys "$SYSTEM_PROMPT" --arg usr "$USER_PROMPT" \
    '{model:"glm-5", max_tokens:16000, stream:true,
      messages:[{role:"system",content:$sys},{role:"user",content:$usr}]}' |
    curl -sS -N --http1.1 https://api.z.ai/api/paas/v4/chat/completions \
      -H "Authorization: Bearer $KEY" \
      -H "content-type: application/json" \
      --data @- --max-time 900 > "$SSE"
  HTML=$(grep '^data: ' "$SSE" | sed 's/^data: //' | grep -v '^\[DONE\]$' |
    jq -rj '.choices[0].delta.content // empty')
  USAGE=$(grep '^data: ' "$SSE" | sed 's/^data: //' | grep -v '^\[DONE\]$' |
    jq -c 'select(.usage != null) | .usage' | tail -1)
else
  echo "unknown provider: $PROVIDER" >&2; exit 1
fi
RESP=$(head -c 2000 "$SSE")

if [ -z "$HTML" ] || [ "$HTML" = "null" ]; then
  echo "GENERATION FAILED — raw response:" >&2
  echo "$RESP" | head -c 2000 >&2
  exit 1
fi

# Strip stray markdown fences (defensive, same as preview-deploy.php)
HTML=$(printf '%s' "$HTML" | sed -e '1s/^\s*```[a-zA-Z]*\s*$//' -e '$s/^\s*```\s*$//')

printf '%s' "$HTML" > "$OUT/index.html"
BYTES=$(wc -c < "$OUT/index.html")
echo "wrote $OUT/index.html ($BYTES bytes)"
echo "usage: $USAGE"
echo "view:  http://localhost/izendestudioweb/previews/bakeoff-$PROVIDER/index.html"

# Sanity checks
grep -q "<!DOCTYPE html>" "$OUT/index.html" && echo "OK: doctype present" || echo "WARN: no doctype"
grep -q -- "--c-accent" "$OUT/index.html" && echo "OK: theming contract vars present" || echo "WARN: theming contract MISSING"

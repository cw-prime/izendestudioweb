#!/usr/bin/env bash
# Generate a one-off showcase sample site with GLM-5 (streaming) -> previews/samples/<slug>/index.html
# Usage: ./gen-sample.sh <slug> <business_name> <description> <vibe>
set -euo pipefail
SLUG="${1:?slug}"; BIZ="${2:?biz}"; DESC="${3:?desc}"; VIBE="${4:?vibe}"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/previews/samples/$SLUG"; mkdir -p "$OUT"
KEY=$(grep '^GLM_API_KEY=' "$ROOT/.env.local" | cut -d= -f2-)

SYSTEM=$(cat "$ROOT/scripts/_sample_prompt.txt")

USER="Business name: $BIZ
What the business does: $DESC
Style / vibe: $VIBE
Contact email (for the mailto link): hello@example.com
Contact phone (for the tel link): (314) 555-0100

Build the complete single-file website now. Output only the HTML."

SSE="$OUT/.stream.sse"
jq -n --arg sys "$SYSTEM" --arg usr "$USER" \
  '{model:"glm-5",max_tokens:16000,stream:true,messages:[{role:"system",content:$sys},{role:"user",content:$usr}]}' |
  curl -sS -N --http1.1 https://api.z.ai/api/paas/v4/chat/completions \
    -H "Authorization: Bearer $KEY" -H "content-type: application/json" \
    -H "Accept: text/event-stream" --data @- --max-time 900 > "$SSE"

HTML=$(grep '^data: ' "$SSE" | sed 's/^data: //' | grep -v '^\[DONE\]$' | jq -rj '.choices[0].delta.content // empty')
HTML=$(printf '%s' "$HTML" | sed -e '1s/^\s*```[a-zA-Z]*\s*$//' -e '$s/^\s*```\s*$//')
printf '%s' "$HTML" > "$OUT/index.html"
rm -f "$SSE"
BYTES=$(wc -c < "$OUT/index.html")
echo "$SLUG: $BYTES bytes"
grep -q "<!DOCTYPE html>" "$OUT/index.html" && grep -q -- "--c-accent" "$OUT/index.html" && echo "  OK doctype+theming" || echo "  WARN malformed"

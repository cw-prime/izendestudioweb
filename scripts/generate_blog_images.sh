#!/usr/bin/env bash
set -euo pipefail

# Generates branded feature images for WordPress posts using ImageMagick.
# Requires convert (ImageMagick) and DejaVu fonts (installed by default on Ubuntu images).

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUT_DIR="$ROOT_DIR/assets/img/blog/featured"
FONT_REG="/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf"
FONT_BOLD="/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf"

mkdir -p "$OUT_DIR"

generate_image() {
  local slug="$1"
  local title="$2"
  local subtitle="$3"

  local outfile="$OUT_DIR/${slug}.png"

  convert \
    -size 1200x630 "gradient:#0F1D2B-#15263F" \
    \( -size 1200x630 xc:none -fill 'rgba(92,184,116,0.18)' -draw "rectangle 0,0 1200,630" \) -compose Over -composite \
    \( -size 1200x630 xc:none -fill 'rgba(92,184,116,0.45)' -draw "polygon 0,630 0,440 320,630" \) -compose Over -composite \
    \( -background none -fill white -font "$FONT_BOLD" -gravity northwest -size 960x caption:"$title" \) -geometry +90+120 -compose Over -composite \
    \( -background none -fill '#a8e5c4' -font "$FONT_REG" -gravity northwest -size 960x caption:"$subtitle" \) -geometry +90+360 -compose Over -composite \
    \( -background none -fill '#5cb874' -font "$FONT_REG" -pointsize 30 -gravity southwest label:"Izende Studio Web" \) -geometry +90+60 -compose Over -composite \
    "$outfile"

  echo "Created $outfile"
}

while IFS='|' read -r slug title subtitle; do
  [ -z "$slug" ] && continue
  generate_image "$slug" "$title" "$subtitle"
done <<'POSTS'
local-seo-st-louis-guide|Local SEO Strategies for St. Louis Businesses|Complete 2025 checklist to outrank nearby competitors.
choose-web-hosting-small-business|How to Choose the Right Web Hosting Plan|Compare shared, VPS, and managed options for small business growth.
website-speed-optimization-guide|Website Speed Optimization: Why Your Customers Won't Wait|Tune performance to improve conversions and Core Web Vitals.
how-a-strong-website-can-transform-your-local-business|How a Strong Website Can Transform Your Local Business|Build authority, trust, and leads with a modern, high-converting site.
wordpress-security-best-practices|WordPress Security Best Practices for 2025|Protect your site with step-by-step hardening and monitoring tips.
youtube-marketing-local-business-guide|YouTube Marketing for Local Businesses|Launch a video strategy that drives traffic and service inquiries.
hello-world|Hello World!|Welcome to the Izende Studio Web blog.
POSTS

echo "All blog feature images generated."

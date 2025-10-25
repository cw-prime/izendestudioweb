#!/usr/bin/env bash
set -euo pipefail

# Simple branded client logos for the homepage carousel.

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUT_DIR="$ROOT_DIR/assets/img/clients"
FONT_REG="/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf"
FONT_BOLD="/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf"

mkdir -p "$OUT_DIR"

create_logo() {
  local filename="$1"
  local initials="$2"
  local company="$3"
  local tagline="$4"

  local outfile="$OUT_DIR/$filename"

  convert -size 240x120 xc:#f7fbf9 \
    \( -size 120x120 xc:none -fill '#5cb874' -draw "roundrectangle 10,20 110,100 26,26" \) -compose Over -composite \
    \( -size 120x120 xc:none -stroke '#ffffff' -strokewidth 6 -draw "circle 60,60 60,24" \) -compose Over -composite \
    \( -background none -fill '#ffffff' -font "$FONT_BOLD" -pointsize 44 -gravity center label:"$initials" \) -geometry +0+0 -compose Over -composite \
    \( -background none -fill '#2c4d3e' -font "$FONT_BOLD" -pointsize 20 -gravity west -size 130x caption:"$company" \) -geometry +120+25 -compose Over -composite \
    \( -background none -fill '#4f6f5f' -font "$FONT_REG" -pointsize 16 -gravity west -size 130x caption:"$tagline" \) -geometry +120+70 -compose Over -composite \
    "$outfile"

  echo "Created $outfile"
}

create_logo "client-1.png" "GT" "Gateway Tech" "IT & Cloud Services"
create_logo "client-2.png" "RR" "Riverfront Retail" "Boutique Collective"
create_logo "client-3.png" "AR" "Archway Realty" "Commercial Properties"
create_logo "client-4.png" "MM" "Metro Medical" "Healthcare Partners"
create_logo "client-5.png" "SF" "STL Fitness" "Studios & Training"
create_logo "client-6.png" "SM" "Show-Me Mfg." "Precision Fabrication"
create_logo "client-7.png" "HN" "Heartland Nonprofit" "Community Network"
create_logo "client-8.png" "MC" "Midtown Cafe" "Bakery & Roastery"

echo "Client logos generated."

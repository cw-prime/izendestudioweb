# Agent Build Prompt — Izende Studio Web Claim Page Upgrades

## Context

Izende Studio Web is an automation-first web agency based in St. Louis, MO. The core product is an AI site builder that generates a personalized website for a prospect in under 2 minutes. The prospect lands on a claim page (`/claim-site?slug={slug}`) where they see their site and choose a plan to publish it.

The current claim page lives at: `https://izendestudioweb.com/claim-site.php`

The three plans are:

- **Get Online** — $39/mo (static hosted site, done for you)  
- **Grow It Yourself** — $49/mo (WordPress, client edits themselves)  
- **We Run It For You** — $149/mo (fully managed, we make edits)

The primary competitors are IONOS ($1/mo intro → $12/mo), Wix ($17/mo), and Squarespace ($16/mo). All three require the customer to build the site themselves. Izende's site is already built when the prospect arrives — that is the core differentiator.

---

## What to Build

### 1\. Animated Loading Screen (during the 2-minute site generation)

Build a full-screen loading overlay that displays while the site is being generated. It should:

- Show a progress bar that fills over \~120 seconds  
- Cycle through the following value flash messages one at a time, fading in/out every \~10 seconds:

"Picking your fonts..."

  → A freelancer would charge $2,000+ for this

"Writing your homepage copy..."

  → IONOS gives you a blank page. We write it for you.

"Optimizing for mobile..."

  → Your site works on every device — automatically

"Setting up your SEO..."

  → Google-ready before you even launch

"Adding your contact info..."

  → Professional email @ your domain included

"Securing your site..."

  → SSL, backups, and uptime monitoring included

"Almost ready..."

  → Built & supported in St. Louis

"Your site is ready."

  → Claim it for $39/mo — cancel anytime

- Style: dark background, gold or warm accent color for the value callouts, clean sans-serif font  
- On completion, auto-redirect or reveal the claim page

---

### 2\. Competitor Comparison Table (on the claim page, above the pricing plans)

Add a comparison section with the headline:

**"Why not just use IONOS for $1/mo?"** *"Every competitor gives you tools and a blank canvas. We give you a finished website."*

Build a responsive comparison table/grid with these columns:

- IONOS  
- Wix  
- Squarespace  
- **Izende** (highlighted/accented column)

And these rows:

| Feature | IONOS | Wix | Squarespace | Izende |
| :---- | :---- | :---- | :---- | :---- |
| You build it yourself | ✅ You do all the work | ✅ You do all the work | ✅ You do all the work | ✗ |
| Site ready in 2 minutes | ✗ | ✗ | ✗ | ✓ |
| Copy written for you | ✗ | AI assist only | AI assist only | ✓ |
| Mobile-ready on day 1 | DIY required | DIY required | DIY required | ✓ |
| Professional email | ✓ | Add-on cost | Add-on cost | ✓ |
| SSL \+ daily backups | ✓ | ✓ | ✓ | ✓ |
| Local St. Louis support | ✗ | ✗ | ✗ | ✓ |
| Online booking | ✗ | $16+/mo add-on | $16+/mo add-on | Included on top plan |
| Real cost year 1 | \~$144/yr \+ your time | \~$204/yr \+ your time | \~$192/yr \+ your time | $588/yr — done for you |

Pricing sources (accurate as of June 2026):

- IONOS WordPress: $1/mo intro, renews at $12/mo  
- Wix: starts at $17/mo (Light plan, billed annually)  
- Squarespace: starts at $16/mo (Basic plan, billed annually)

Style notes:

- Izende column should be visually accented (border highlight or background tint)  
- Mobile responsive — stack gracefully on small screens  
- Place this section ABOVE the three pricing plan cards

---

### 3\. Urgency Element (countdown timer)

A countdown timer already exists on the page. Ensure it is:

- Prominently visible near the top of the claim page  
- Tied to the slug so each prospect has their own expiry  
- Displays something like: **"Your site expires in 3 days 14:22:09 — claim it before it's gone"**  
- If not already implemented, build it as a JS countdown pulling expiry from a query param or API

---

## Technical Notes

- Stack: PHP site, static HTML/CSS/JS, hosted on cPanel/Apache  
- No React or build tools — vanilla JS and CSS only  
- The claim page URL format is: `/claim-site.php?slug={slug}`  
- All styles should match or complement the existing site at `https://izendestudioweb.com`  
- Mobile-first responsive design required  
- Do not break existing plan selection buttons or checkout flow (`/adminIzende/cart.php?a=add&pid={id}&slug={slug}`)

---

## Deliverables

1. `loading-screen.html` — standalone loading screen component (or injectable snippet)  
2. `comparison-table.html` — comparison section HTML/CSS (injectable into claim page)  
3. `countdown-timer.js` — countdown timer JS (if not already working)  
4. Integration notes for where each snippet drops into `claim-site.php`


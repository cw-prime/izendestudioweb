# Claude Code Handoff: AI Website Builder on Izende Hosting

## Mission

Build an AI-powered "build your site, host it here" funnel for Izende Studio Web so prospects stop leaving for Wix/10Web. Prospect fills a form on izendestudioweb.com → AI generates a live preview site hosted on Izende's WHM/cPanel infrastructure → "Claim this site" converts them to a paid hosting account, auto-provisioned via WHM API.

## Environment (verify, don't assume)

- **Hosting:** WHM/cPanel (root/reseller access available). Marko will generate a WHM API token: WHM → Development → Manage API Tokens.  
- **Main site:** izendestudioweb.com — WordPress. Existing app password: user `support@izendestudioweb.com`, credential name `claude-post`. Direct REST posts from external IPs are blocked; the n8n server IP is whitelisted.  
- **Automation:** n8n self-hosted in Docker on a basement Ubuntu server.  
- **Other infra in play:** Supabase (project `ocgearsjyqeoscjvcdrz`), Cloudflare R2, Brevo SMTP (n8n credential `Lf3xmbkVez9LDNSJ`).

## Known n8n/Docker environment constraints (hard-won — respect these)

1. `$env` references are blocked (`N8N_BLOCK_ENV_ACCESS_IN_NODE=true`). Use hardcoded values or n8n credentials.  
2. Webhook triggers have been unreliable in this Docker setup. Preferred pattern: form writes to a Supabase table (or similar queue), n8n polls on a schedule (e.g., every 1–5 min).  
3. Known bug: after editing a schedule, `recurrenceRules: []` can require manually toggling the workflow off/on.  
4. n8n Docker resolves `localhost` as IPv6 `::1` — always use `127.0.0.1` or explicit IPs for local backend URLs.  
5. `updateNode` via MCP replaces the entire `parameters` object — fetch full params first, merge, then write.

## Build phases

### Phase 1 — Audit

- Inspect izendestudioweb.com WordPress: theme, page builder (if any), how forms are currently handled, child theme status.  
- Confirm WHM API access works: test `createacct` and `removeacct` against a throwaway test account via API token.  
- Decide/confirm preview domain (e.g., `preview.izendestudioweb.com` or a dedicated domain) with wildcard subdomain support (`*.preview.domain.com` → one cPanel account hosting all previews in subdirectories, or per-preview subdomains via cPanel UAPI `SubDomain::addsubdomain`).

### Phase 2 — Intake form \+ landing section

- New landing section/page: "Build Your Website with AI — Free Preview in 2 Minutes."  
- Form fields: business name, what the business does (textarea), style/vibe selector, contact email, optional phone.  
- Form submission writes a row to a Supabase table `site_builder_leads` (status: `pending`). Do NOT rely on n8n webhooks (see constraint \#2).  
- Honeypot \+ basic rate limiting for spam (Marko has prior lead-spam dedup patterns from VisiProp — `FB_Lead_ID`\-style primary-key dedup with retry logic).

### Phase 3 — Generation workflow (n8n)

- Schedule trigger polls `site_builder_leads` for `status = pending`.  
- Claude API call generates a complete single-page static site (inline CSS or single stylesheet, no build step). Mobile-responsive, includes hero, about, services, contact section with mailto/phone.  
- Deploy: push files to preview location via cPanel UAPI File Manager endpoints or SFTP.  
- Update lead row: `status = preview_live`, store preview URL.  
- Email the prospect their preview link via Brevo SMTP. Include a "Claim this site" CTA.  
- Optional: Telegram notification to Marko on each new preview generated.

### Phase 4 — Conversion \+ provisioning

- "Claim this site" → checkout (payment processor TBD — see open decisions).  
- On successful payment, n8n workflow:  
  1. WHM API 1 `createacct` — real cPanel account, assigned package.  
  2. Copy site files from preview into the new account.  
  3. Optionally install WordPress via WP Toolkit API and port content in (upsell path).  
  4. Email credentials \+ onboarding via Brevo.  
  5. Mark lead `status = converted`; tear down or redirect the preview.  
- Preview expiry: cron/n8n cleanup of previews older than X days (suggest 14).

### Phase 5 — Polish

- Admin dashboard (could be a simple page or Supabase view) showing leads, previews, conversions.  
- AI edit-request flow for paying clients (ticket → n8n → Claude regenerates section → redeploy).

## Open decisions for Marko (ask before building Phase 4\)

- Payment processor (Stripe vs. existing WHMCS/billing system if one exists).  
- Pricing: monthly hosting only, or build fee \+ hosting.  
- Preview domain choice.  
- Whether converted sites default to static or WordPress.

## Working style

Marko prefers step-by-step pacing with confirmation between steps, direct action over proposals, and pushing changes rather than describing them. Verify everything in Phase 1 before writing integration code.  

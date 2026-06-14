# Stage 3–4 — Conversion, Provisioning, and AI Edit Contract

Status: scaffold only. Do not activate live provisioning until WHM token, checkout path, package name, and preview domain are confirmed.

## Stage 3 goal

Turn a generated preview into a paid hosted site.

Flow:
1. Prospect clicks `Claim this site` from preview.
2. Checkout/payment succeeds.
3. n8n receives a payment-success event with `lead_id`, domain/contact info, and selected plan.
4. n8n creates a real cPanel account via WHM API 1 `createacct`.
5. n8n copies the preview files into the new account document root.
6. Supabase row moves from `preview_live` to `converted`.
7. Brevo sends credentials/onboarding.
8. Preview is expired, redirected, or left live for a short grace period.

## Required decisions before live build

- Payment processor: Stripe Checkout vs existing WHMCS/billing.
- Pricing: monthly hosting only vs build fee + hosting.
- Package name in WHM/cPanel.
- Domain behavior: customer-owned domain, temporary subdomain, or Izende-managed domain.
- Converted site default: static HTML vs WordPress install/upsell.
  - Current working assumption: static HTML is the fast default preview/product for speed and low support burden.
  - Revisit WordPress as an upsell tier because static sites have limits for client editing, blogging, plugin features, and long-term content management.
- Preview domain: recommended `preview.izendestudioweb.com`, subfolder per lead.

## Payment-success payload contract

```json
{
  "lead_id": "uuid",
  "customer_email": "owner@example.com",
  "business_name": "Business Name",
  "domain": "example.com",
  "selected_plan": "starter-hosting",
  "payment_id": "processor-session-or-invoice-id",
  "amount_paid": 0,
  "currency": "USD"
}
```

## WHM `createacct` minimum mapping

- `username`: generated from business/domain, max 8 chars if server requires legacy cPanel limit.
- `domain`: customer domain or temp domain.
- `contactemail`: lead/customer email.
- `plan`: confirmed WHM package name.
- `password`: generated strong password; do not log plaintext beyond the onboarding email path.
- `cgi`: 1 if package expects it.
- `hasshell`: 0 by default.

## Stage 3 n8n workflow skeleton

1. Trigger: payment success webhook or schedule poll from checkout table.
2. Fetch lead from Supabase by `lead_id`.
3. Validate `status = preview_live` and `preview_url` present.
4. Call WHM API `createacct`.
5. Copy preview HTML/assets into account document root.
   - Static v1: copy `index.html` only.
   - Later: copy folder of generated assets if generator expands beyond one file.
6. PATCH Supabase:
   - `status = converted`
   - `converted_at = now()`
   - `cpanel_username`
   - `domain`
   - `payment_id`
7. Send onboarding email through Brevo.
8. Notify Mark.
9. Schedule cleanup/redirect of preview after grace period.

## Stage 4 AI edit path

Use the 11-token CSS theming contract already enforced in the generation prompt:

- `--c-bg`
- `--c-surface`
- `--c-text`
- `--c-heading`
- `--c-muted`
- `--c-border`
- `--c-accent`
- `--c-accent-contrast`
- `--font-heading`
- `--font-body`
- `--radius`

Initial safe Stage 4 features:
1. Theme preset changes by rewriting only the 11 root tokens.
2. Section copy edits from a client request.
3. Regenerate a single section, not the whole site, after paid conversion.
4. Mark/client approval before redeploy.

## Safety gates

- Do not run WHM create/remove account without a throwaway smoke test first.
- Do not activate payment-triggered provisioning until checkout is tested end-to-end in test mode.
- Do not store WHM token, Anthropic key, or Supabase service-role key in code or committed JSON.
- n8n Docker has `$env` access blocked; use n8n variables or credentials.
- Keep generated workflow inactive until variables/credentials are verified.

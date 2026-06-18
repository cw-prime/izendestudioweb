# Claude Handoff: Site Drafter Free-Domain Offer

## Goal
Make the WHMCS checkout match the Site Drafter claim-page promise:

- Site Drafter plans include first-year standard domain registration when the customer registers a new eligible domain during checkout.
- Domain renewals bill separately each year at the registrar renewal rate.
- Customers who already own a domain can connect or transfer it instead.

## Product Settings To Verify
In WHMCS admin, update products `14`, `15`, and `16`.

First remove old public-facing product wording from each product name, description, and any configurable checkout text:

- Product `14`: rename from old technology-led wording to `Site Drafter - Get Online`, `$39/mo`.
- Product `15`: rename from old technology-led wording to `Site Drafter - Grow It Yourself`, `$49/mo`.
- Product `16`: rename from old technology-led wording to `Site Drafter - We Run It For You`, `$149/mo`.

Suggested short product descriptions:

- Product `14`: `The site you previewed, published on managed hosting with SSL, professional email setup, and first-year standard domain registration for eligible new domains.`
- Product `15`: `Your preview rebuilt in WordPress so you can edit text, images, pages, and posts yourself. Includes hosting, SSL, professional email setup, and first-year standard domain registration for eligible new domains.`
- Product `16`: `Managed WordPress for customers who want Izende to run everyday site edits, updates, backups, security, and support. Includes hosting, SSL, professional email setup, online booking, and first-year standard domain registration for eligible new domains.`

For each product:

- Enable WHMCS native **Free Domain**.
- Include at minimum `.com`; add other intended standard TLDs only if Izende wants to absorb those first-year registration costs.
- Enable the billing terms used by the claim page. The public cards are monthly, so **monthly must be eligible** if the first-year-domain promise remains on monthly cards.
- Do not hide renewal pricing. The cart should continue to show yearly renewal cost before checkout.

## Copy Already Updated In Code
These files already have expectation-setting copy:

- `claim-site.php`
  - Says first-year standard domain registration is included when registering a new domain during checkout.
  - Says renewals bill separately each year at the registrar renewal rate.
  - Says existing domains can be connected or transferred.
  - Uses professional email setup language instead of implying email includes domain ownership.
- `adminIzende/templates/orderforms/standard_cart/configureproductdomain.tpl`
  - Adds the note only when `$freedomaintlds` is present:
    “First-year registration is included with this website plan for eligible domains. Renewal is billed separately each year.”

## Site Drafter Language Requirement
Do not use customer-facing wording that brands this as a technology product or builder. The public offer is the website draft and the hosting plan, not the underlying generation method.

Use:

- Site Drafter
- Website Drafter
- website draft
- draft assistant
- smart automation only if a lower-level technical explanation is truly needed

Keep internal filenames, routes, IDs, and analytics names stable unless there is a separate migration plan.

## Verification
After WHMCS product settings are updated:

1. Start a checkout from `claim-site.php` for product `14`.
2. Register an eligible `.com` domain.
3. Confirm first-year registration is free or discounted to `$0.00`.
4. Confirm the domain renewal price remains visible.
5. Repeat quick checks for products `15` and `16`.
6. Confirm the existing-domain path still works without registration.

## Notes From Codex
Codex could not verify or update the WHMCS product flags from the shell because the local credentials in `adminIzende/configuration.php` were rejected by MySQL. This handoff assumes Claude will use WHMCS admin access or valid database credentials.

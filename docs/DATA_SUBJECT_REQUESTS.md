
# Data Subject Request (DSR) Handling — Operational Guide

This document provides a practical guide for handling Data Subject Requests submitted via the site's public forms. It is intended for site administrators, privacy officers, and developers maintaining the DSR workflow.

## Files and responsibilities
- `/data-subject-request.php` — public form users use to submit requests. Includes CSRF token and posts to the handler.
- `/do-not-sell.php` — page for California opt-out requests (posts to the same handler with `type=do_not_sell`).
- `/forms/data-subject-request.php` — server-side handler. Validates CSRF, sanitizes inputs, generates a unique request ID, logs the request to `logs/dsr.log`, notifies internal support, and sends a confirmation to the requester.
- `/forms/log-consent.php` — used by front-end to persist consent events via sendBeacon/fetch.

## Logging and retention
- Logs are written to the `logs/` directory as `dsr.log` and should not be publicly accessible.
- Retention: Keep DSR logs for a minimum of 3 years for audit; move older logs to an archival location with restricted access.

## Verification procedures
1. When you receive a request, first verify identity using reasonable means:
	- For account holders, require login or a secondary confirmation (email to address on record).
	- For non-account holders, request additional identifying information (e.g., recent invoice number) while minimizing data collected.
2. Record all verification steps in the internal ticket associated with the request ID.

## Handling specific request types
- Access: Provide a copy of the personal data in a commonly used format (CSV/JSON) within the legal timeframe (usually 30 days).
- Delete: Evaluate retention obligations; remove personal data where legally permitted and notify the requester of actions taken.
- Portability: Provide data in a structured, commonly used, machine-readable format.
- Do Not Sell / Do Not Share: Record the opt-out, honor it for the account and applicable cookies/marketing flags, and update marketing systems.

## Templates and emails
- Internal notification (already implemented in `forms/data-subject-request.php`) should include request ID, requester email, request type, IP, and user-agent.
- Confirmation email to requester should include the request ID and expected timelines.

## Security considerations
- Ensure `logs/` is not served by the webserver (place an empty `index.html` or restrict via server config).
- Limit access to logs and DSR handling code to authorized staff only.
- Use TLS for all form submissions (site should already use HTTPS).

## Operational timelines
- Acknowledge receipt within 5 business days and respond substantively within 30 calendar days (or the legal timeframe applicable in your jurisdiction). Document any lawful extensions.

## Audit and evidence
- When fulfilling a request, log what data was returned/erased, the date of action, and the staff member who executed the change. Store this in an audit trail associated with the request ID.

## Integrations and improvements
- Consider integrating the handler with your ticketing system or CRM using server-to-server communication rather than relying on PHP mail.
- Add rate limiting and captcha for anonymous DSR submissions to prevent abuse.

## Contact
For questions about this process, contact <support@izendestudioweb.com>.


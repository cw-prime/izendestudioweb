# Security Implementation Checklist

This checklist tracks the implementation of all security measures across the Izende Studio Web platform.

## Phase 1: Critical Security Infrastructure (Priority 1)

### Main Site Security
- [x] Create `config/security.php` with CSRF, rate limiting, and validation functions
- [x] Create `config/env-loader.php` for environment variable management
- [x] Create `config/.env.example` template
- [ ] Create actual `.env` file with secrets (not in git)
- [ ] Rotate reCAPTCHA secret key immediately
- [ ] Move reCAPTCHA secret to environment variable

### Form Security
- [x] Update `quote.php` with CSRF protection
- [x] Update `quote.php` with input validation and sanitization
- [x] Update `quote.php` with rate limiting
- [x] Update `quote.php` to use environment variables
- [x] Update `forms/contact.php` with CSRF protection
- [x] Update `forms/contact.php` with rate limiting
- [x] Add CSRF token to contact form in `index.php`
- [ ] Test all forms with valid and invalid inputs

### Server Hardening
- [x] Update `.htaccess` with comprehensive security rules
- [x] Force HTTPS redirect
- [x] Disable directory listing
- [x] Protect sensitive files and directories
- [x] Block common attack patterns
- [ ] Test .htaccess rules don't break site functionality

### Session Security
- [x] Implement secure session configuration
- [ ] Test session security settings
- [ ] Verify session cookies have Secure, HttpOnly, SameSite attributes

### Error Handling
- [x] Create `config/error-handler.php`
- [x] Create `logs/` directory
- [x] Create `logs/.htaccess` to protect log files
- [x] Configure error logging
- [ ] Test error handling (trigger test errors)
- [ ] Verify errors are logged but not displayed to users

## Phase 2: WordPress Security (Priority 2)

### WordPress Configuration
- [x] Update `articles/wp-config.php` with security hardening
- [x] Disable file editing (`DISALLOW_FILE_EDIT`)
- [x] Force SSL for admin (`FORCE_SSL_ADMIN`)
- [ ] Rotate all WordPress security keys
- [x] Disable XML-RPC if not needed
- [ ] Test WordPress admin access after changes

### WordPress Plugins
- [ ] Install Wordfence Security plugin
- [ ] Configure Wordfence (firewall, malware scanner, 2FA)
- [ ] Install iThemes Security plugin
- [ ] Configure iThemes Security (hardening, 2FA)
- [ ] Install Limit Login Attempts Reloaded plugin
- [ ] Configure login attempt limiting
- [ ] Enable 2FA for all admin accounts
- [ ] Run initial security scan
- [ ] Review and fix any security issues found

### WordPress File Permissions
- [ ] Set wp-config.php to 600 or 640
- [ ] Set .htaccess to 644
- [ ] Set wp-content to 755
- [ ] Set all files to 644
- [ ] Set all directories to 755
- [ ] Verify file permissions via cPanel or SSH

## Phase 3: WHMCS Security (Priority 2)

### WHMCS Hardening
- [ ] Rename admin directory from `/adminIzende/` to unique name
- [ ] Update `configuration.php` with new admin path
- [ ] Update all bookmarks/links to new admin URL
- [ ] Document new admin URL securely
- [ ] Test admin access at new URL

### WHMCS Access Control
- [ ] Enable 2FA for all admin accounts
- [ ] Configure IP whitelist restrictions
- [ ] Set admin session timeout to 15 minutes
- [ ] Enable CAPTCHA on admin login
- [ ] Disable "Remember Me" on admin login
- [ ] Test admin login with 2FA

### WHMCS Configuration
- [ ] Set `configuration.php` permissions to 600
- [ ] Verify .htaccess protects configuration.php
- [ ] Force SSL for admin area
- [ ] Enable security logging
- [ ] Review and configure all security settings
- [ ] Test WHMCS functionality after changes

### WHMCS Updates
- [ ] Check current WHMCS version
- [ ] Backup database and files
- [ ] Update WHMCS to latest version (if needed)
- [ ] Test WHMCS after update

## Phase 4: Security Headers (Priority 1)

### HTTP Security Headers
- [x] Implement `setSecurityHeaders()` function in `config/security.php`
- [x] Add Content-Security-Policy with nonce support
- [x] Add Strict-Transport-Security (HSTS)
- [x] Add X-Frame-Options
- [x] Add X-Content-Type-Options
- [x] Add X-XSS-Protection
- [x] Add Referrer-Policy
- [x] Add Permissions-Policy
- [ ] Test headers with online security scanner
- [ ] Verify CSP doesn't break site functionality

### CSP Rollout
- [ ] Start with Content-Security-Policy-Report-Only
- [ ] Monitor CSP violation reports for 1 week
- [ ] Fix any CSP violations
- [ ] Switch to enforcing Content-Security-Policy
- [ ] Continue monitoring for violations

## Phase 5: Testing & Validation (Priority 1)

### Security Testing
- [ ] Test CSRF protection (try submitting forms without token)
- [ ] Test rate limiting (submit forms rapidly)
- [ ] Test input validation (try XSS/SQL injection payloads)
- [ ] Test HTTPS redirect (access via HTTP)
- [ ] Test directory listing is disabled
- [ ] Test sensitive files are protected
- [ ] Test security headers are present
- [ ] Test session security (check cookie attributes)

### Penetration Testing
- [ ] Run OWASP ZAP or similar scanner
- [ ] Test for common vulnerabilities (OWASP Top 10)
- [ ] Review and fix any findings
- [ ] Document test results

### SSL/TLS Testing
- [ ] Test SSL configuration at https://www.ssllabs.com/ssltest/
- [ ] Verify A+ rating or fix issues
- [ ] Test HSTS is working
- [ ] Consider HSTS preload submission

## Phase 6: Monitoring & Maintenance (Ongoing)

### Daily Monitoring
- [ ] Set up automated log monitoring
- [ ] Configure email alerts for critical security events
- [ ] Monitor failed login attempts
- [ ] Review security logs

### Weekly Tasks
- [ ] Review error logs
- [ ] Review security event logs
- [ ] Check for WordPress/WHMCS updates
- [ ] Review Wordfence scan results
- [ ] Check for failed login attempts

### Monthly Tasks
- [ ] Full security audit
- [ ] Review and update security policies
- [ ] Test backup restoration
- [ ] Review user accounts (remove inactive)
- [ ] Update security documentation

### Quarterly Tasks
- [ ] Rotate admin passwords
- [ ] Review and update IP whitelists
- [ ] Security training for team members
- [ ] Penetration testing
- [ ] Review and update incident response plan

## Phase 7: Documentation (Priority 3)

### Security Documentation
- [x] Document all security measures implemented
- [ ] Create incident response plan
- [ ] Document backup and recovery procedures
- [ ] Create security policy document
- [ ] Document admin access procedures
- [ ] Create security training materials

### Operational Documentation
- [ ] Document new admin URLs (securely)
- [ ] Document 2FA setup procedures
- [ ] Document emergency access procedures
- [x] Document log locations and formats
- [ ] Document monitoring and alerting setup

## Emergency Procedures

### Security Incident Response
1. Identify and contain the incident
2. Assess the scope and impact
3. Notify stakeholders
4. Remediate the vulnerability
5. Restore from backup if necessary
6. Document the incident
7. Conduct post-incident review
8. Update security measures

### Emergency Contacts
- **Hosting Support:** cPanel support via hosting provider
- **WHMCS Support:** https://www.whmcs.com/support/
- **WordPress Support:** https://wordpress.org/support/

## Compliance & Standards

- [ ] Review GDPR compliance (if applicable)
- [ ] Review PCI DSS compliance (for payment processing)
- [ ] Review OWASP Top 10 coverage
- [ ] Review CIS Benchmarks compliance
- [ ] Document compliance status

## Next Steps (CRITICAL - DO THIS FIRST!)

1. **Create .env file:**
   ```bash
   cp config/.env.example config/.env
   chmod 600 config/.env
   ```

2. **Generate new reCAPTCHA keys:**
   - Visit https://www.google.com/recaptcha/admin
   - Create new v2 Checkbox reCAPTCHA
   - Add both site key and secret to `.env` file
   - Update site key in quote.php (line 291)

3. **Test forms:**
   - Visit quote.php and index.php
   - Test form submission with valid data
   - Verify emails are received
   - Check security logs at `/logs/security.log`

4. **Review security headers:**
   - Visit https://securityheaders.com
   - Test your site
   - Review and fix any issues

5. **WordPress security:**
   - Install required security plugins
   - Rotate security keys
   - Enable 2FA for all admins

6. **WHMCS security:**
   - Rename admin directory
   - Enable 2FA
   - Configure IP restrictions

## Files Created

### Security Infrastructure
- `/config/security.php` - Core security functions
- `/config/env-loader.php` - Environment variable management
- `/config/.env.example` - Environment variables template
- `/config/error-handler.php` - Error handling and logging
- `/.gitignore` - Prevent committing sensitive files

### Configuration
- `/.htaccess` - Server-level security rules
- `/logs/.htaccess` - Protect log files
- `/articles/wp-config.php` - Updated with security hardening

### Main Site Updates
- `/quote.php` - Completely rewritten with security
- `/forms/contact.php` - Updated with security
- `/index.php` - Updated with security initialization

### Documentation
- `/SECURITY_IMPLEMENTATION_CHECKLIST.md` - This file
- `/articles/wp-content/plugins/SECURITY_PLUGINS_INSTALLATION.md` - WordPress plugin guide
- `/adminIzende/WHMCS_SECURITY_HARDENING.md` - WHMCS hardening guide

## Sign-Off

**Implementation Completed By:** _________________
**Date:** _________________
**Reviewed By:** _________________
**Date:** _________________

## Notes

Add any additional notes, issues encountered, or deviations from the plan:

---

**Last Updated:** 2025-10-14
**Version:** 1.0

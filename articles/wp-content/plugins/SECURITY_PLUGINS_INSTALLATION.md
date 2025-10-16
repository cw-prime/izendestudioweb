# WordPress Security Plugins Installation Guide

## Required Plugins

### 1. Wordfence Security
**Purpose:** Firewall, malware scanner, login security

**Installation:**
1. Log in to WordPress admin at `/articles/wp-admin/`
2. Navigate to Plugins > Add New
3. Search for "Wordfence Security"
4. Click "Install Now" then "Activate"

**Configuration:**
- Enable firewall (Learning Mode first, then Protection Mode after 1 week)
- Enable malware scanner (schedule daily scans)
- Enable login security (2FA for admin users)
- Configure email alerts for security events
- Set up country blocking if needed

### 2. iThemes Security (formerly Better WP Security)
**Purpose:** Comprehensive security hardening

**Installation:**
1. Plugins > Add New
2. Search for "iThemes Security"
3. Install and activate

**Configuration:**
- Run Security Check wizard
- Enable 2FA for all admin accounts
- Enable strong password enforcement
- Enable file change detection
- Configure backup settings
- Enable database backups

### 3. Limit Login Attempts Reloaded
**Purpose:** Prevent brute force attacks

**Installation:**
1. Plugins > Add New
2. Search for "Limit Login Attempts Reloaded"
3. Install and activate

**Configuration:**
- Set max login attempts: 3
- Lockout duration: 20 minutes
- Increase lockout duration after multiple lockouts
- Enable email notifications
- Whitelist your IP address

## Existing Plugins Status

**Already Installed:**
- ✅ Loginizer (basic login security)
- ✅ Jetpack (includes some security features)
- ✅ Akismet (spam protection)

**Keep these plugins active** - they complement the new security plugins.

## Post-Installation Checklist

- [ ] All three security plugins installed and activated
- [ ] Wordfence firewall in Protection Mode (after 1 week learning)
- [ ] Malware scan completed with no issues
- [ ] 2FA enabled for all admin accounts
- [ ] Login attempt limiting active
- [ ] Email notifications configured
- [ ] Security scan completed with no critical issues
- [ ] File permissions verified (644 for files, 755 for directories)
- [ ] wp-config.php hardening completed
- [ ] XML-RPC disabled (if not needed)
- [ ] File editing disabled in wp-config.php

## Monitoring

**Daily:**
- Check Wordfence email alerts
- Review failed login attempts

**Weekly:**
- Review Wordfence scan results
- Check for plugin/theme updates
- Review security event logs

**Monthly:**
- Full security audit
- Review and update security policies
- Test backup restoration

## Support Resources

- Wordfence: https://www.wordfence.com/help/
- iThemes Security: https://ithemes.com/security/
- Limit Login Attempts: https://wordpress.org/plugins/limit-login-attempts-reloaded/

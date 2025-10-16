# WHMCS Security Hardening Guide

## Critical Actions Required

### 1. Rename Admin Directory
**Current:** `/adminIzende/`
**Recommended:** Choose a unique, non-guessable name

**Steps:**
1. Choose a new admin directory name (e.g., `admin_[random_string]`)
2. Via cPanel File Manager or SSH:
   ```bash
   mv /var/www/html/izendestudioweb/adminIzende /var/www/html/izendestudioweb/admin_[new_name]
   ```
3. Update WHMCS configuration:
   - Edit `configuration.php` line 18: `$customadminpath = 'admin_[new_name]';`
4. Update any bookmarks/links to admin area
5. Test admin access at new URL

**Important:** Document the new admin URL securely (password manager)

### 2. Enable Two-Factor Authentication (2FA)

**Steps:**
1. Log in to WHMCS admin
2. Navigate to: Setup > Staff Management > Administrators
3. For each admin user:
   - Click "Edit"
   - Enable "Two-Factor Authentication"
   - Scan QR code with authenticator app (Google Authenticator, Authy)
   - Save backup codes securely
4. Make 2FA mandatory for all admin accounts

### 3. IP Whitelist Restrictions

**Option A: Via .htaccess (in admin directory)**
Edit `/adminIzende/.htaccess` (or new admin directory):

```apache
# Add after WHMCS managed rules (after line 26)

# IP Whitelist for Admin Access
<RequireAny>
    Require ip YOUR_OFFICE_IP
    Require ip YOUR_HOME_IP
    Require ip YOUR_VPN_IP
</RequireAny>

# Or use Allow/Deny for Apache 2.2:
# Order Deny,Allow
# Deny from all
# Allow from YOUR_OFFICE_IP
# Allow from YOUR_HOME_IP
```

**Option B: Via WHMCS Admin Settings**
1. Setup > General Settings > Security
2. Enable "Admin IP Restriction"
3. Add allowed IP addresses

### 4. Protect Configuration File

**Current:** `configuration.php` contains database credentials in plain text

**Steps:**
1. Set strict file permissions:
   ```bash
   chmod 600 /var/www/html/izendestudioweb/adminIzende/configuration.php
   ```

2. Add .htaccess protection (already in place, verify):
   ```apache
   <Files "configuration.php">
       Require all denied
   </Files>
   ```

3. Consider encrypting database credentials:
   - WHMCS supports encrypted configuration
   - See: https://docs.whmcs.com/Configuration_File_Security

### 5. Review WHMCS Security Settings

**Navigate to: Setup > General Settings > Security**

**Enable/Configure:**
- ✅ Force SSL for Admin Area
- ✅ Admin Session Timeout: 15 minutes
- ✅ Password Strength Requirements: Strong
- ✅ Enable CAPTCHA on Admin Login
- ✅ Admin IP Restriction (if using Option B above)
- ✅ Enable Security Question on Password Reset
- ✅ Disable "Remember Me" on Admin Login

**Navigate to: Setup > General Settings > Security > Security Log**
- Enable logging of all admin actions
- Review logs regularly for suspicious activity

### 6. Update WHMCS

**Check current version:**
1. Log in to admin area
2. Check version in footer or System > System Information

**Update if needed:**
1. Backup database and files first
2. Follow WHMCS update guide: https://docs.whmcs.com/Updating_WHMCS
3. Test thoroughly after update

### 7. Database Security

**Current credentials in configuration.php:**
- Username: `izende6_whmc920`
- Password: `7)7(Sq77pG`
- Database: `izende6_whmc920`

**Recommendations:**
1. Ensure database user has minimal required privileges
2. Use strong, unique password (current password appears adequate)
3. Restrict database access to localhost only
4. Regular database backups (automated)

### 8. File Permissions

**Recommended permissions:**
```bash
# Directories
find /var/www/html/izendestudioweb/adminIzende -type d -exec chmod 755 {} \;

# Files
find /var/www/html/izendestudioweb/adminIzende -type f -exec chmod 644 {} \;

# Configuration file (more restrictive)
chmod 600 /var/www/html/izendestudioweb/adminIzende/configuration.php

# Writable directories (templates_c, attachments, downloads)
chmod 777 /home/izende6/whmcsdata/templates_c/
chmod 777 /home/izende6/whmcsdata/attachments/
chmod 777 /home/izende6/whmcsdata/downloads/
```

### 9. SSL/TLS Configuration

**Verify HTTPS is enforced:**
1. Check that all WHMCS URLs use https://
2. Ensure SSL certificate is valid and not expired
3. Test SSL configuration: https://www.ssllabs.com/ssltest/

**Force HTTPS in WHMCS:**
- Setup > General Settings > General
- Set "WHMCS System URL" to `https://izendestudioweb.com/adminIzende/`

### 10. Regular Security Audits

**Weekly:**
- Review security logs for suspicious activity
- Check for failed login attempts
- Verify 2FA is active for all admins

**Monthly:**
- Review and update IP whitelist
- Check for WHMCS updates
- Review admin user accounts (remove inactive)
- Test backup restoration

**Quarterly:**
- Full security audit
- Password rotation for admin accounts
- Review and update security policies

## Security Checklist

- [ ] Admin directory renamed to unique name
- [ ] 2FA enabled for all admin accounts
- [ ] IP whitelist configured
- [ ] configuration.php permissions set to 600
- [ ] SSL/HTTPS enforced
- [ ] Admin session timeout set to 15 minutes
- [ ] CAPTCHA enabled on admin login
- [ ] Security logging enabled
- [ ] WHMCS updated to latest version
- [ ] Database credentials secured
- [ ] File permissions verified
- [ ] Regular backup schedule configured

## Emergency Contacts

**WHMCS Support:**
- https://www.whmcs.com/support/
- Emergency: https://www.whmcs.com/members/submitticket.php

**Security Resources:**
- WHMCS Security Guide: https://docs.whmcs.com/Security
- WHMCS Security Advisories: https://www.whmcs.com/security/

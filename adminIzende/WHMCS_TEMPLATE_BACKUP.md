# WHMCS Template Backup & Restore Guide

## Pre-Customization Backup

**CRITICAL:** Always backup files before customization.

### Files to Backup

**Template Files (.tpl):**
- [x] header.tpl
- [x] footer.tpl
- [x] homepage.tpl
- [x] clientareahome.tpl
- [x] includes/head.tpl
- [ ] includes/navbar.tpl (no changes needed)
- [ ] includes/social-accounts.tpl (no changes needed)
- [ ] store/order.tpl (no changes needed - CSS updates only)

**CSS Files:**
- [x] css/theme.css
- [x] css/store.css
- [ ] css/custom.css (NEW FILE - backup after creation)

**Backup Location:**
```
/var/www/html/izendestudioweb/adminIzende/templates/twenty-one-backup/
```

### Backup Command (via SSH):
```bash
cd /var/www/html/izendestudioweb/adminIzende/templates/
cp -r twenty-one twenty-one-backup-$(date +%Y%m%d)
```

### Backup via cPanel:
1. Log in to cPanel
2. Navigate to File Manager
3. Go to `/adminIzende/templates/`
4. Right-click `twenty-one` folder
5. Select "Compress"
6. Download the ZIP file
7. Store securely with date stamp: `twenty-one-backup-20250115.zip`

---

## Backup Verification

- [x] Backup folder created successfully
- [ ] All files present in backup
- [ ] Backup is readable and not corrupted
- [ ] Backup stored in secure location
- [ ] Backup date documented: 2025-01-15

---

## Restore Procedure

### Full Restore

**If all customizations need to be reverted:**

**Via SSH:**
```bash
cd /var/www/html/izendestudioweb/adminIzende/templates/
# Make safety backup of current state
mv twenty-one twenty-one-current-$(date +%Y%m%d)
# Restore from backup
cp -r twenty-one-backup-YYYYMMDD twenty-one
# Clear WHMCS template cache
rm -rf ../templates_c/*
```

**Via cPanel:**
1. Navigate to `/adminIzende/templates/`
2. Rename current `twenty-one` folder to `twenty-one-current`
3. Upload backup ZIP
4. Extract to templates directory
5. Rename extracted folder to `twenty-one`
6. Clear template cache (see below)

**After restore:**
1. Clear WHMCS template cache: Utilities > System > Template Cache
2. Test WHMCS functionality
3. Verify all pages load correctly
4. Check client area, shopping cart, invoices

### Partial Restore (Single File)

**If only one file needs to be reverted:**

**Via SSH:**
```bash
# Example: Restore header.tpl only
cp /var/www/html/izendestudioweb/adminIzende/templates/twenty-one-backup-20250115/header.tpl \
   /var/www/html/izendestudioweb/adminIzende/templates/twenty-one/header.tpl

# Clear template cache
rm -rf /var/www/html/izendestudioweb/adminIzende/templates_c/*
```

**Via cPanel:**
1. Navigate to backup folder
2. Download specific file
3. Upload to `twenty-one` folder (overwrite when prompted)
4. Clear template cache

### Restore Individual CSS Files

**Restore theme.css:**
```bash
cp /path/to/backup/css/theme.css \
   /var/www/html/izendestudioweb/adminIzende/templates/twenty-one/css/theme.css
```

**Restore store.css:**
```bash
cp /path/to/backup/css/store.css \
   /var/www/html/izendestudioweb/adminIzende/templates/twenty-one/css/store.css
```

**Remove custom.css (revert to default):**
```bash
mv /var/www/html/izendestudioweb/adminIzende/templates/twenty-one/css/custom.css \
   /var/www/html/izendestudioweb/adminIzende/templates/twenty-one/css/custom.css.bak
```

---

## Template Cache Management

**Clear Template Cache After Changes:**

### Method 1: WHMCS Admin (Recommended)
1. Log in to WHMCS admin panel
2. Navigate to: **Utilities > System > Template Cache**
3. Click **"Empty Template Cache"**
4. Refresh client area to see changes

### Method 2: File System (via SSH)
```bash
cd /var/www/html/izendestudioweb/adminIzende
rm -rf templates_c/*
```

### Method 3: File Manager (via cPanel)
1. Navigate to `/adminIzende/templates_c/`
2. Select all files
3. Click "Delete"
4. Confirm deletion

**When to Clear Cache:**
- After modifying any .tpl file
- After updating CSS files
- After adding custom.css
- If changes don't appear immediately
- After WHMCS updates
- After restoring from backup

---

## Version Control

### Git Tracking (Recommended)

**Add to .gitignore:**
```gitignore
# WHMCS template cache
adminIzende/templates_c/

# WHMCS backups
adminIzende/templates/twenty-one-backup*/

# WHMCS configuration (contains sensitive data)
adminIzende/configuration.php

# WHMCS attachments and downloads
adminIzende/attachments/
adminIzende/downloads/
```

**Initial commit (before customization):**
```bash
cd /var/www/html/izendestudioweb
git add adminIzende/templates/twenty-one/
git commit -m "WHMCS: Initial Twenty-One template before customization"
git tag whmcs-template-v1.0-original
```

**Commit customizations:**
```bash
git add adminIzende/templates/twenty-one/
git commit -m "WHMCS: Customize template with Izende brand colors and typography

- Add Raleway font for headings
- Update primary color to #5cb874
- Add 'Back to Main Site' link in header
- Add legal links and service area in footer
- Update card accents and tiles to brand green
- Create custom.css with brand overrides"

git tag whmcs-template-v1.0-customized
```

**View changes:**
```bash
git diff whmcs-template-v1.0-original whmcs-template-v1.0-customized
```

### Manual Version Control

**Create dated backups:**
```bash
# Before customization
cp -r twenty-one twenty-one-backup-20250115-before

# After customization
cp -r twenty-one twenty-one-backup-20250115-after

# After testing
cp -r twenty-one twenty-one-backup-20250115-tested
```

**Backup naming convention:**
- `twenty-one-backup-YYYYMMDD-before` - Before any changes
- `twenty-one-backup-YYYYMMDD-after` - Immediately after customization
- `twenty-one-backup-YYYYMMDD-tested` - After successful testing
- `twenty-one-backup-YYYYMMDD-production` - Currently live version

---

## Troubleshooting

### Changes Don't Appear

**Symptoms:**
- CSS updates not visible
- Template changes not showing
- Old colors still appearing

**Solution:**
1. Clear WHMCS template cache (Utilities > System > Template Cache)
2. Hard refresh browser (Ctrl+Shift+R or Cmd+Shift+R)
3. Clear browser cache (Ctrl+Shift+Delete)
4. Check file permissions:
   ```bash
   # Files should be 644, directories 755
   find adminIzende/templates/twenty-one -type f -exec chmod 644 {} \;
   find adminIzende/templates/twenty-one -type d -exec chmod 755 {} \;
   ```
5. Verify file paths are correct
6. Check for typos in file names

### Broken Layout

**Symptoms:**
- Page elements overlapping
- Responsive design broken
- Missing styles

**Solution:**
1. Check browser console for CSS errors (F12 > Console)
2. Verify custom.css syntax is valid
3. Look for conflicting CSS rules
4. Restore original CSS file and re-apply changes incrementally:
   ```bash
   # Disable custom.css temporarily
   mv css/custom.css css/custom.css.disabled
   # Clear cache and test
   ```
5. Validate HTML structure in .tpl files
6. Check for unclosed HTML tags

### WHMCS Functionality Broken

**Symptoms:**
- Shopping cart not working
- Forms not submitting
- Client area errors
- White screen (PHP error)

**Solution:**
1. Check for Smarty syntax errors in .tpl files
2. Verify all WHMCS variables are preserved (e.g., `{$WEB_ROOT}`, `{$companyname}`)
3. Check for missing closing tags (`{/if}`, `{/foreach}`)
4. Review WHMCS error logs:
   - Admin: Utilities > Logs > Module Log
   - Server: `/adminIzende/configuration.php` check error log path
5. Restore original .tpl file and compare changes
6. Test in safe mode (rename custom.css temporarily)

### Colors Not Updating

**Symptoms:**
- Brand color #5cb874 not appearing
- Old blue #336699 still showing
- Inconsistent colors across pages

**Solution:**
1. Verify CSS variables are defined in `:root` (check custom.css)
2. Check for `!important` overrides blocking changes
3. Ensure custom.css loads after theme.css (verify in head.tpl)
4. Clear all caches:
   ```bash
   # WHMCS template cache
   rm -rf adminIzende/templates_c/*
   # Browser cache (Ctrl+Shift+Delete)
   ```
5. Use browser DevTools to inspect computed styles (F12 > Elements > Computed)
6. Verify sed replacements completed successfully:
   ```bash
   grep -c "#5cb874" adminIzende/templates/twenty-one/css/theme.css
   # Should return multiple matches
   ```

### Custom.css Not Loading

**Symptoms:**
- Custom styles not applied
- Brand colors missing
- Footer legal links unstyled

**Solution:**
1. Verify custom.css exists:
   ```bash
   ls -lh adminIzende/templates/twenty-one/css/custom.css
   ```
2. Check head.tpl includes custom.css (lines 7-9)
3. Verify file permissions (should be 644)
4. Clear template cache
5. Check browser DevTools > Network tab for 404 errors
6. Validate CSS syntax (use W3C CSS Validator or browser console)

---

## Best Practices

1. **Always backup before customizing**
   - Create dated backup folder
   - Store backup off-server (download to local machine)
   - Document what was changed

2. **Test in staging environment first** (if available)
   - Set up test WHMCS installation
   - Apply changes to test first
   - Verify functionality before production

3. **Use custom.css for overrides** (survives updates)
   - Never modify core WHMCS files
   - Put brand-specific styles in custom.css
   - Use CSS variables for easy color changes

4. **Document all changes** (this file + customization guide)
   - List modified files
   - Explain why changes were made
   - Include code examples

5. **Clear template cache after changes**
   - After every .tpl edit
   - After every CSS edit
   - If something doesn't look right

6. **Test thoroughly before going live**
   - Visual: Check all pages
   - Functional: Test cart, forms, login
   - Responsive: Test mobile, tablet, desktop
   - Cross-browser: Chrome, Firefox, Safari, Edge

7. **Keep WHMCS updated** (security and features)
   - Check for updates monthly
   - Read update notes carefully
   - Backup before updating
   - Test after updating

8. **Monitor error logs** after customization
   - WHMCS admin error logs
   - Server PHP error logs
   - Browser console errors

9. **Maintain brand consistency** with main site
   - Use exact same colors (#5cb874)
   - Use same fonts (Open Sans, Raleway)
   - Match button styles and spacing

10. **Follow WHMCS template guidelines** (don't break core functionality)
    - Keep all Smarty variables
    - Don't remove required elements
    - Preserve WHMCS classes and IDs
    - Test cart and checkout thoroughly

---

## Emergency Restore Checklist

If something goes wrong and you need to restore immediately:

- [ ] 1. Access server via SSH or cPanel File Manager
- [ ] 2. Navigate to `/adminIzende/templates/`
- [ ] 3. Rename `twenty-one` to `twenty-one-broken`
- [ ] 4. Copy backup: `cp -r twenty-one-backup-YYYYMMDD twenty-one`
- [ ] 5. Clear template cache: `rm -rf ../templates_c/*`
- [ ] 6. Test WHMCS client area
- [ ] 7. Test shopping cart and checkout
- [ ] 8. Verify no errors in WHMCS admin logs
- [ ] 9. Document what went wrong
- [ ] 10. Fix issue in broken folder, then re-apply carefully

---

## Contact Information

**If you need assistance:**
- WHMCS Support: https://support.whmcs.com/
- WHMCS Community Forums: https://whmcs.community/
- Template Developer: Check documentation at /docs/WHMCS_CUSTOMIZATION_GUIDE.md

---

## Backup Log

| Date | Backup Name | Status | Notes |
|------|-------------|--------|-------|
| 2025-01-15 | twenty-one-backup-20250115-before | ✓ | Before customization |
| 2025-01-15 | twenty-one-backup-20250115-after | ✓ | After customization applied |
| 2025-01-15 | twenty-one-backup-20250115-tested | - | After testing (pending) |

---

**Backup Created:** 2025-01-15
**Customization Completed:** 2025-01-15
**Last Tested:** (Pending)
**Next Review:** 2025-04-15 (3 months)

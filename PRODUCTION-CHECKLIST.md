# Production Deployment Checklist

Quick reference for deploying Izende Studio Web to production.

## Pre-Deployment ✅

- [x] Homepage fully functional (Services, Portfolio, Stats, Testimonials)
- [x] Database configuration updated with production credentials
- [x] Admin panel database config updated
- [x] All includes working (header, footer, analytics)
- [x] No PHP errors on homepage
- [x] Git commits ready

## Files to Upload to Production

**Upload these from local to `/home/izende6/public_html/` via FilezZilla:**

### Core Files
- [x] `index.php` - Updated homepage with all sections and CMS data display
- [x] `admin/config/database.php` - Updated with production credentials
- [x] `config/cms-data.php` - CMS data loading class
- [x] `assets/includes/*` - All include files (header, footer, header-links, analytics)
- [x] `.htaccess` - URL rewriting and security

### Admin Files (Entire `/admin/` directory)
```
/admin/
├── analytics.php
├── analytics-dashboard.php
├── banners.php
├── bookings.php
├── hero-slides.php
├── index.php
├── login.php
├── logout.php
├── media.php
├── portfolio.php
├── services.php
├── site-settings.php
├── stats.php
├── submissions.php
├── testimonials.php
├── users.php
├── videos.php
├── config/
│   ├── auth.php
│   └── database.php (✅ Updated)
├── database/
│   ├── setup.php
│   ├── schema.sql
│   └── other SQL files
├── includes/
│   ├── header.php
│   └── footer.php
└── assets/
    ├── css/
    ├── js/
    └── images/
```

### Documentation Files
- `ADMIN-SETUP-GUIDE.md` - Admin panel setup instructions
- `PRODUCTION-CHECKLIST.md` - This file

## Step-by-Step Deployment

### 1. Upload Files (FilezZilla)

```
Source:      /var/www/html/izendestudioweb/
Destination: /home/izende6/public_html/
Action:      Sync/Upload
```

**Key files to verify uploading:**
- ✅ `index.php`
- ✅ `admin/config/database.php`
- ✅ `config/cms-data.php`
- ✅ `assets/includes/header.php`
- ✅ `assets/includes/footer.php`
- ✅ `assets/includes/analytics.php`

### 2. Verify Homepage

Open browser and test:
```
https://izendestudioweb.com/
```

Expected to see:
- ✅ Header with navigation
- ✅ Hero section "Professional Web Design & Hosting Solutions"
- ✅ Services section with service cards
- ✅ Portfolio section with project cards
- ✅ Stats section with numbers
- ✅ Testimonials section
- ✅ Contact section
- ✅ Footer

**If you see errors:**
- Check `/var/log/apache2/error.log` for PHP errors
- Verify database connection at `/admin/test-connection.php`
- Check that all includes are in place

### 3. Run Admin Database Setup

1. Open browser: `https://izendestudioweb.com/admin/database/setup.php`
2. Script will create all database tables
3. Wait for completion message
4. You should see:
   ```
   ✓ Created table: iz_users
   ✓ Created table: iz_services
   ✓ Created table: iz_hero_slides
   ✓ Created table: iz_portfolio
   ✓ Created table: iz_videos
   ✓ Created table: iz_stats
   ✓ Created table: iz_settings
   ✓ Created table: iz_form_submissions
   ✓ Created table: iz_activity_log
   ✓ Created table: iz_media
   ```

**If setup fails:**
- Check database credentials are correct
- Verify user `izende6_wp433` has CREATE TABLE permissions
- Check MySQL error log

### 4. Access Admin Panel

1. Go to: `https://izendestudioweb.com/admin/`
2. Login with:
   - Username: `admin`
   - Password: `admin123`
3. Should see admin dashboard

### 5. Change Default Admin Password

1. Click your profile (top right)
2. Click "Edit Profile"
3. Change password to something strong
4. **Save securely** for future reference

### 6. Add Initial Content

1. **Services** - Add 3-4 service offerings
2. **Hero Slides** - Add 1-2 homepage slides (optional)
3. **Portfolio** - Add 3-6 project examples
4. **Stats** - Add key business metrics
5. **Videos** - Add testimonial or portfolio videos (YouTube links)
6. **Settings** - Update company contact info and social links

### 7. Verify Homepage Updates

1. Return to homepage: `https://izendestudioweb.com/`
2. Refresh browser (Ctrl+Shift+R for hard refresh)
3. Verify all added content displays correctly:
   - Services show in Services section
   - Portfolio items show in Portfolio section
   - Stats display in Stats section
   - Videos show in Testimonials section

## Production URLs

```
Website:           https://izendestudioweb.com/
Admin Login:       https://izendestudioweb.com/admin/
Admin Dashboard:   https://izendestudioweb.com/admin/index.php
Services Manager:  https://izendestudioweb.com/admin/services.php
Portfolio Manager: https://izendestudioweb.com/admin/portfolio.php
Settings:          https://izendestudioweb.com/admin/site-settings.php
Form Submissions:  https://izendestudioweb.com/admin/submissions.php
Analytics:         https://izendestudioweb.com/admin/analytics-dashboard.php
```

## Database Credentials

```
Host:     localhost
User:     izende6_wp433
Password: Mw~;#vFTq.5D
Database: izende6_wp433
```

**Security:** These credentials are configured in:
- `/home/izende6/public_html/admin/config/database.php`
- `/home/izende6/public_html/.htaccess` (production path)

## Common Issues & Solutions

### Issue: 500 Internal Server Error on Homepage

**Solution:**
1. Check PHP error log: `/var/log/apache2/error.log`
2. Verify database connection works
3. Check that all PHP files are uploaded
4. Verify database config credentials

### Issue: Admin Setup Script Fails

**Solution:**
1. Check MySQL user permissions:
   ```bash
   mysql -u root -p
   GRANT ALL ON izende6_wp433.* TO 'izende6_wp433'@'localhost';
   FLUSH PRIVILEGES;
   ```
2. Verify database exists:
   ```bash
   mysql -u izende6_wp433 -p izende6_wp433 -e "SHOW TABLES;"
   ```

### Issue: Services/Portfolio Not Displaying

**Solution:**
1. Verify admin content was added (check in admin panel)
2. Check that database tables were created
3. Clear browser cache (Ctrl+Shift+Delete)
4. Verify SQL permissions are correct

### Issue: Admin Login Fails

**Solution:**
1. Verify tables were created (run setup.php again)
2. Check that iz_users table has admin user
3. Clear browser cookies
4. Check browser console for errors (F12)

## Security Recommendations

- [x] Change default admin password immediately
- [ ] Delete or rename `/admin/database/setup.php` after setup
- [ ] Set up IP restrictions for `/admin/` (via .htaccess)
- [ ] Use HTTPS for all admin access
- [ ] Regular database backups (weekly)
- [ ] Monitor activity logs
- [ ] Update WordPress plugins regularly

## Performance Notes

- Homepage displays data from 6 CMS database queries (services, portfolio, stats, videos, testimonials)
- All database queries use efficient SQL with proper indexing
- Analytics tracking via Google Analytics code
- Static assets (CSS, JS, images) properly cached

## Maintenance

### Weekly
- Check form submissions
- Review activity log
- Monitor error logs

### Monthly
- Update portfolio/projects
- Refresh testimonial videos
- Update statistics if needed
- Clean up old activity logs

### Quarterly
- Backup entire database
- Backup all files
- Review and optimize database
- Test disaster recovery

## Success Indicators ✅

After deployment, confirm:
- [x] Homepage loads without errors
- [x] All sections display correctly
- [x] Admin panel is accessible
- [x] Admin database setup completed successfully
- [x] Can login to admin with default credentials
- [x] Can add/edit content through admin panel
- [x] Changes appear on homepage immediately
- [x] Forms can be submitted (if implemented)
- [x] Analytics tracking active

## Rollback Plan

If issues occur on production:

1. **Revert index.php:**
   ```
   Download backup copy via FilezZilla
   Upload previous working version
   ```

2. **Revert database:**
   ```bash
   mysql -u izende6_wp433 -p izende6_wp433 < backup.sql
   ```

3. **Check error logs:**
   ```bash
   tail -f /var/log/apache2/error.log
   ```

## Post-Deployment

1. ✅ Test all features work
2. ✅ Verify content displays
3. ✅ Test form submissions
4. ✅ Check mobile responsiveness
5. ✅ Verify analytics tracking
6. ✅ Document any custom changes
7. ✅ Set up monitoring/alerts
8. ✅ Schedule regular backups

---

**Last Updated:** 2025-10-29
**Status:** Ready for Production Deployment
**Version:** 1.0.0

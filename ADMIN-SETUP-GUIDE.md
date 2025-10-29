# Admin Panel Setup Guide - Production

This guide walks you through setting up the Izende Studio admin panel on the production server.

## Quick Start (5 minutes)

### Step 1: Access the Setup Script

1. Upload all files from local to `/home/izende6/public_html/` via FilezZilla
2. Open browser and go to: `https://izendestudioweb.com/admin/database/setup.php`
3. The script will automatically create all necessary database tables
4. You should see a success message with list of created tables

### Step 2: Access Admin Login

1. Go to: `https://izendestudioweb.com/admin/`
2. Log in with default credentials:
   - **Username:** `admin`
   - **Password:** `admin123`

### Step 3: Change Default Password

1. Once logged in, navigate to **Settings > Users** (if available)
2. Click your username to edit profile
3. Change password from `admin123` to a strong password
4. **IMPORTANT:** Save the new password securely

## Database Configuration

### Current Production Setup

```
Database Host:   localhost
Database User:   izende6_wp433
Database Pass:   Mw~;#vFTq.5D
Database Name:   izende6_wp433
```

This configuration is already set in `/admin/config/database.php`

### Tables Created

The setup script creates these tables:

| Table | Purpose |
|-------|---------|
| `iz_users` | Admin user accounts |
| `iz_services` | Service cards |
| `iz_hero_slides` | Homepage hero carousel slides |
| `iz_portfolio` | Portfolio/project items |
| `iz_videos` | YouTube videos (includes testimonials) |
| `iz_stats` | Homepage statistics/counters |
| `iz_settings` | Site-wide settings (key-value pairs) |
| `iz_form_submissions` | Contact form submissions |
| `iz_activity_log` | Admin action audit log |
| `iz_media` | Media library images |

## Admin Features

### Content Management

**Services** (`/admin/services.php`)
- Add, edit, delete service cards
- Customize icon, title, description, price
- Set display order
- Show/hide services from homepage

**Hero Slides** (`/admin/hero-slides.php`)
- Manage homepage carousel slides
- Upload images
- Add title and description
- Set slide order

**Portfolio** (`/admin/portfolio.php`)
- Add project showcase items
- Upload project images
- Categorize projects (Web Design, SEO, etc.)
- Feature projects on homepage

**Videos** (`/admin/videos.php`)
- Paste YouTube URLs
- Automatically extracts thumbnails and video IDs
- Categorize videos (Portfolio, Testimonials, etc.)
- Display on relevant sections

**Stats** (`/admin/stats.php`)
- Manage homepage counter statistics
- Add custom numbers and labels
- Update metrics displayed to visitors

### Settings & Admin

**Site Settings** (`/admin/site-settings.php`)
- Company information
- Contact details
- Social media links
- Site-wide configuration

**Users** (`/admin/users.php`)
- Manage admin user accounts
- Set user roles (Admin, Editor, Viewer)
- Change passwords
- Deactivate users

**Form Submissions** (`/admin/submissions.php`)
- View contact form submissions
- Mark as read/replied/archived
- Add internal notes

**Activity Log** (`/admin/activity-log.php`)
- Track all admin changes
- See who made changes and when
- Audit trail for content modifications

## Security Setup

### 1. Change Default Password (CRITICAL)

After first login, change the default password:

1. Click your user profile (top right)
2. Select "Edit Profile"
3. Enter new password (min 8 characters, mix of upper/lower/numbers)
4. Save changes

### 2. Secure Admin Access (Optional but Recommended)

Create `/admin/.htaccess` to restrict access to admin panel:

```apache
# Restrict admin access to specific IPs (optional)
# Replace 192.168.1.0/24 with your IP range
Order Deny,Allow
Deny from all
Allow from 192.168.1.0/24
Allow from YOUR_IP_ADDRESS
```

### 3. Use HTTPS

Always access admin panel over HTTPS:
- `https://izendestudioweb.com/admin/` ✅
- Never use `http://` for admin access

### 4. Remove Setup Files (Optional)

After initial setup, you can delete or rename the setup file:
- `/admin/database/setup.php` (or rename to `setup.php.backup`)

This prevents accidental re-running of setup.

## Frontend Integration

The homepage already integrates with the admin panel. CMS data is loaded via:

**File:** `/config/cms-data.php`

This file provides the `CMSData` class with methods to fetch data:
- `CMSData::getHeroSlides()` - Homepage carousel
- `CMSData::getFeaturedServices(6)` - Service cards
- `CMSData::getStats()` - Statistics/counters
- `CMSData::getFeaturedPortfolio(6)` - Portfolio projects
- `CMSData::getVideos('category', 6)` - Videos by category
- `CMSData::getTestimonials(6)` - Testimonial videos

The homepage displays this data in sections:
- Hero section (static with loaded data potential)
- Services section (displays featured services)
- Portfolio section (displays featured projects)
- Stats section (displays counters)
- Testimonials section (displays video testimonials)

## Accessing the Admin Panel

### URLs

```
Main Dashboard:     https://izendestudioweb.com/admin/
Login Page:         https://izendestudioweb.com/admin/login.php
Services Manager:   https://izendestudioweb.com/admin/services.php
Hero Slides:        https://izendestudioweb.com/admin/hero-slides.php
Portfolio:          https://izendestudioweb.com/admin/portfolio.php
Videos:             https://izendestudioweb.com/admin/videos.php
Stats:              https://izendestudioweb.com/admin/stats.php
Settings:           https://izendestudioweb.com/admin/site-settings.php
Users:              https://izendestudioweb.com/admin/users.php
Form Submissions:   https://izendestudioweb.com/admin/submissions.php
Activity Log:       https://izendestudioweb.com/admin/activity-log.php
Media Library:      https://izendestudioweb.com/admin/media.php
Bookings:           https://izendestudioweb.com/admin/bookings.php
Analytics:          https://izendestudioweb.com/admin/analytics-dashboard.php
```

## Troubleshooting

### Cannot Login

**Problem:** Getting "Invalid username or password" error

**Solution:**
1. Verify database connection in `/admin/config/database.php`
2. Check that database tables were created (run setup.php again)
3. Verify username is `admin` (case-sensitive)
4. Clear browser cookies and try again
5. Check PHP error log: `tail -f /var/log/apache2/error.log`

### Database Connection Failed

**Problem:** "Database connection failed" on setup page

**Solution:**
1. Verify credentials in `/admin/config/database.php`:
   ```
   DB_HOST: localhost
   DB_USER: izende6_wp433
   DB_PASS: Mw~;#vFTq.5D
   DB_NAME: izende6_wp433
   ```
2. Test MySQL connection via command line:
   ```bash
   mysql -h localhost -u izende6_wp433 -p -e "USE izende6_wp433; SHOW TABLES LIKE 'iz_%';"
   ```
3. Verify MySQL service is running:
   ```bash
   sudo systemctl status mysql
   ```

### Tables Not Created

**Problem:** Setup page doesn't show tables created

**Solution:**
1. Run setup.php again: `https://izendestudioweb.com/admin/database/setup.php`
2. Check for error messages
3. Verify user `izende6_wp433` has CREATE TABLE permissions:
   ```bash
   mysql -u root -p
   GRANT ALL PRIVILEGES ON izende6_wp433.* TO 'izende6_wp433'@'localhost';
   FLUSH PRIVILEGES;
   ```

### Blank Admin Dashboard

**Problem:** Dashboard loads but shows no content

**Solution:**
1. Check browser console (F12) for JavaScript errors
2. Check PHP error log: `tail /var/log/apache2/error.log`
3. Verify database connection works
4. Clear browser cache (Ctrl+Shift+Delete)

## User Roles

### Admin
- Full access to all features
- Can manage users
- Can view activity logs
- Can change site settings
- Can create backups

### Editor
- Can manage content (services, portfolio, videos, stats)
- Can view form submissions
- Can upload media
- Cannot manage users or settings

### Viewer
- Read-only access
- Can view dashboard and reports
- Cannot edit content
- Cannot access admin settings

## Best Practices

1. **Regular Backups**
   - Backup database weekly
   - Backup all files weekly
   - Keep offsite backup copies

2. **Activity Monitoring**
   - Check activity log regularly
   - Monitor form submissions
   - Set up email alerts for new submissions

3. **Content Updates**
   - Update portfolio regularly with new projects
   - Keep testimonials fresh
   - Update stats/metrics quarterly

4. **Security**
   - Change default password immediately
   - Use strong, unique passwords
   - Monitor login attempts
   - Restrict admin access by IP if possible

5. **Testing**
   - Test changes on staging before production
   - Verify all sections display correctly
   - Check mobile responsive design
   - Test form submissions

## Performance Tips

1. **Image Optimization**
   - Use optimized image sizes
   - Compress images before upload
   - Use WebP format when possible

2. **Database**
   - Clean up old activity logs monthly
   - Archive old form submissions
   - Optimize database tables: `OPTIMIZE TABLE iz_*`

3. **Caching**
   - Consider adding page caching for performance
   - Use browser caching for images

## Contact & Support

For issues or questions:
1. Check the activity log for error details
2. Review PHP error log at `/var/log/apache2/error.log`
3. Test database connection independently
4. Check that all required tables exist

## Version Info

- **Version:** 1.0.0
- **PHP:** 8.1.33
- **Database:** MySQL/MariaDB
- **Last Updated:** 2025-10-29

---

**Next Steps:**
1. ✅ Upload files to production
2. ✅ Run setup.php database setup
3. ✅ Log in with admin/admin123
4. ✅ Change default password
5. ✅ Add content (services, portfolio, etc.)
6. ✅ Test homepage displays content correctly

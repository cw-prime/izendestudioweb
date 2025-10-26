# Izende Studio Web - CMS Documentation

Welcome to your complete business website with integrated Content Management System!

## 📚 Documentation Quick Links

| Document | Purpose |
|----------|---------|
| [COMPLETED-FEATURES.md](COMPLETED-FEATURES.md) | Complete list of all implemented features |
| [GOOGLE-CALENDAR-SETUP.md](GOOGLE-CALENDAR-SETUP.md) | Detailed Google Calendar integration guide |
| [CALENDAR-QUICK-START.md](CALENDAR-QUICK-START.md) | Quick setup guide for Calendar features |
| [BLOG-INTEGRATION-README.md](BLOG-INTEGRATION-README.md) | WordPress blog integration (if applicable) |

---

## 🚀 What's Built

### ✅ Marketing & Lead Generation
- **SEO Manager** - Manage meta tags, Open Graph, Twitter Cards
- **Newsletter Signup** - Email subscription with database tracking
- **Promotional Banners** - Scheduled announcements (top/bottom)
- **Client Testimonials** - Star ratings, logos, featured reviews
- **Interactive Map** - 20-mile service area around St. Louis

### ✅ Booking & Scheduling
- **Consultation Booking** - Client-facing booking form
- **Google Calendar Integration** - Auto-create events with Meet links
- **Admin Booking Manager** - Approve, confirm, cancel appointments
- **Email Notifications** - Calendar invites sent automatically

### ✅ Content Management
- **Services Manager** - Create/edit service offerings
- **Portfolio Manager** - Showcase your work
- **Video Gallery** - Manage video content
- **Hero Slides** - Homepage carousel management
- **Media Library** - Upload and organize images

### ✅ Admin Dashboard
- **Statistics Overview** - 8 key metrics at a glance
- **Recent Activity** - Bookings, submissions, subscribers
- **Quick Actions** - Fast access to common tasks
- **System Info** - PHP version, database status, user role

### ✅ Analytics & Tracking
- **Google Analytics Integration** - Property ID: 376174814
- **Analytics Dashboard** - View traffic reports in admin panel
- **Event Tracking** - Form submissions, bookings, signups

---

## 🗂️ Project Structure

```
/var/www/html/izendestudioweb/
│
├── admin/                      # Admin panel
│   ├── index.php              # Dashboard homepage
│   ├── bookings.php           # Consultation bookings
│   ├── services.php           # Services manager
│   ├── portfolio.php          # Portfolio manager
│   ├── testimonials.php       # Testimonials manager
│   ├── banners.php            # Promotional banners
│   ├── seo-manager.php        # SEO configuration
│   ├── analytics-dashboard.php # Google Analytics
│   ├── config/                # Admin configuration files
│   └── database/              # Database migrations
│
├── api/                        # API endpoints
│   ├── book-consultation.php  # Booking API
│   ├── newsletter-signup.php  # Newsletter API
│   └── blog-api.php           # Blog data API
│
├── includes/                   # Helper classes
│   ├── SEOHelper.php          # SEO meta tags
│   ├── BannerHelper.php       # Banner display
│   ├── CalendarHelper.php     # Google Calendar
│   └── BlogHelper.php         # Blog integration
│
├── config/                     # Core configuration
│   ├── env-loader.php         # Environment variables
│   ├── security.php           # Security functions
│   ├── database.php           # Database connection
│   └── cms-data.php           # CMS data helpers
│
├── assets/                     # Frontend assets
│   ├── css/                   # Stylesheets
│   ├── js/                    # JavaScript files
│   ├── img/                   # Images
│   └── includes/              # Shared templates
│
├── index.php                   # Homepage
├── book-consultation.php       # Booking page
├── services.php               # Services page
├── portfolio.php              # Portfolio page
├── contact.php                # Contact page
├── .env                       # Environment config (not in Git)
└── composer.json              # PHP dependencies
```

---

## 🔧 Installation & Setup

### Prerequisites
- **PHP 8.0+** with mysqli extension
- **MySQL/MariaDB** database
- **Apache/Nginx** web server
- **Composer** (for Google Calendar integration)

### Initial Setup

1. **Configure Database**
   - Import database schema (if provided)
   - Or create tables using admin panel

2. **Set Up Environment Variables**
   ```bash
   cp .env.example .env
   nano .env
   ```

   Required variables:
   ```env
   DB_HOST=localhost
   DB_NAME=izendestudioweb_wp
   DB_USER=your_db_user
   DB_PASSWORD=your_db_password

   GOOGLE_ANALYTICS_PROPERTY_ID=376174814
   GOOGLE_CALENDAR_ID=your-calendar@gmail.com
   ```

3. **Set File Permissions**
   ```bash
   chmod 644 .env
   chmod -R 755 assets/
   chmod -R 755 admin/
   ```

4. **Install Dependencies** (Optional - for Calendar)
   ```bash
   composer install
   ```

### First Login
- **URL:** `http://yourdomain.com/admin/`
- **Default credentials:** Set up during installation

---

## 🗄️ Database Tables

| Table | Purpose |
|-------|---------|
| `iz_services` | Service offerings |
| `iz_portfolio` | Portfolio items |
| `iz_videos` | Video gallery |
| `iz_hero_slides` | Homepage carousel |
| `iz_testimonials` | Client reviews |
| `iz_promo_banners` | Promotional announcements |
| `iz_seo_meta` | SEO configuration |
| `iz_bookings` | Consultation bookings |
| `iz_newsletter_subscribers` | Email subscribers |
| `iz_form_submissions` | Contact form data |
| `iz_users` | Admin users |
| `iz_activity_log` | Admin action tracking |

**Prefix:** All tables use `iz_` prefix

---

## 📅 Google Calendar Setup

### Quick Setup (5 Steps)

1. **Enable API**
   - Google Cloud Console → APIs & Services → Library
   - Search "Google Calendar API" → Enable

2. **Get Service Account**
   - Use existing service account from Analytics
   - Or create new one with Calendar permissions

3. **Share Calendar**
   - Google Calendar → Settings → Share with service account email
   - Permission: "Make changes to events"

4. **Update .env**
   ```env
   GOOGLE_CALENDAR_ID=your-calendar@gmail.com
   GOOGLE_CALENDAR_TIMEZONE=America/Chicago
   ```

5. **Install Library**
   ```bash
   composer require google/apiclient
   ```

**Detailed Guide:** See [CALENDAR-QUICK-START.md](CALENDAR-QUICK-START.md)

---

## 🎯 Common Tasks

### Adding a New Service
1. Admin Panel → Services → Add New
2. Fill in title, description, icon, pricing
3. Set display order and status
4. Click Save

### Managing Bookings
1. Admin Panel → Bookings
2. View pending bookings
3. Click "View" to see details
4. Change status to "Confirmed" to create calendar event
5. Google Meet link is generated automatically

### Creating Promotional Banner
1. Admin Panel → Banners → Add New
2. Choose banner type (info/success/warning/danger)
3. Set message and optional link
4. Schedule start/end dates
5. Set to Active

### Configuring SEO
1. Admin Panel → SEO Manager
2. Select page to configure
3. Fill in meta tags, Open Graph, Twitter Cards
4. Preview how it looks on social media
5. Save and test with Facebook Debugger

### Viewing Analytics
1. Admin Panel → Analytics Dashboard
2. Select date range
3. View page views, sessions, top pages
4. Export reports if needed

---

## 🔒 Security Best Practices

### File Protection
```apache
# .htaccess rules already in place
<FilesMatch "\.(env|json)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### Database Security
- ✅ All queries use prepared statements
- ✅ Input sanitization with `htmlspecialchars()`
- ✅ Email validation with `FILTER_VALIDATE_EMAIL`
- ✅ Password hashing with `password_hash()`

### Session Management
- ✅ Secure session cookies
- ✅ CSRF protection on forms
- ✅ Session timeout after inactivity
- ✅ Login attempt throttling

---

## 🚨 Troubleshooting

### Calendar Not Working
```bash
# Check if library is installed
composer show google/apiclient

# Verify .env configuration
cat .env | grep CALENDAR

# Check PHP error logs
tail -f /var/log/apache2/error.log
```

### Database Connection Issues
```php
// Test database connection
php admin/test-db-connection.php
```

### Permission Errors
```bash
# Reset permissions
chmod -R 755 /var/www/html/izendestudioweb
chown -R www-data:www-data /var/www/html/izendestudioweb
```

### Blank Pages
```bash
# Enable PHP error display temporarily
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

---

## 📞 Support & Maintenance

### Regular Maintenance Tasks
- **Weekly:** Review new bookings and form submissions
- **Monthly:** Check newsletter subscriber growth
- **Quarterly:** Update SEO meta tags based on performance
- **As Needed:** Add new portfolio items and testimonials

### Backup Recommendations
1. **Database:** Export weekly via phpMyAdmin
2. **Files:** Backup .env, uploads, and custom code
3. **Service Account Keys:** Keep secure offline copy

### Monitoring
- **Uptime:** Use monitoring service (UptimeRobot, Pingdom)
- **Analytics:** Check traffic weekly in Analytics Dashboard
- **Errors:** Monitor Apache error logs
- **Performance:** Use Google PageSpeed Insights

---

## 🎓 Learning Resources

### Frontend Development
- Bootstrap 5: https://getbootstrap.com/docs/5.0/
- Bootstrap Icons: https://icons.getbootstrap.com/

### Backend Development
- PHP Documentation: https://www.php.net/manual/en/
- MySQLi Guide: https://www.php.net/manual/en/book.mysqli.php

### Google APIs
- Calendar API: https://developers.google.com/calendar
- Analytics API: https://developers.google.com/analytics

### SEO Best Practices
- Google Search Central: https://developers.google.com/search
- Open Graph Protocol: https://ogp.me/
- Twitter Cards: https://developer.twitter.com/en/docs/twitter-for-websites/cards

---

## 📝 Changelog

### Version 2.0 (Current)
- ✅ Enhanced admin dashboard with statistics
- ✅ Google Calendar integration with Meet links
- ✅ SEO Manager for all pages
- ✅ Newsletter subscription system
- ✅ Promotional banners
- ✅ Client testimonials
- ✅ Interactive service area map
- ✅ Booking system with calendar sync

### Version 1.0 (Initial)
- Basic CMS functionality
- Services, Portfolio, Videos management
- Contact form
- Google Analytics integration

---

## 🤝 Contributing

### Code Style
- Use 4 spaces for indentation
- Follow PSR-12 coding standard for PHP
- Comment complex logic
- Use meaningful variable names

### Git Workflow
```bash
git checkout -b feature/new-feature
# Make changes
git add .
git commit -m "Add new feature: description"
git push origin feature/new-feature
```

### Testing
- Test all forms before deployment
- Verify database queries
- Check responsive design on mobile
- Test in multiple browsers

---

## 📄 License

Proprietary software for Izende Studio Web.
All rights reserved.

---

## 📧 Contact

**Website:** https://izendestudioweb.com
**Email:** support@izendestudioweb.com
**Phone:** +1 (314) 312-6441

**Admin Panel:** https://izendestudioweb.com/admin/

---

**Last Updated:** October 2025
**Version:** 2.0

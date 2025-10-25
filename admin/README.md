# Izende Studio CMS - Admin Panel

A hybrid content management system for Izende Studio website that combines WordPress blog functionality with custom content management for services, portfolio, videos, and more.

## Features

- **Authentication System** - Secure login with session management and password hashing
- **Services Manager** - Add, edit, delete, and reorder service cards
- **Hero Slides Manager** - Manage homepage carousel slides
- **Portfolio Manager** - Showcase projects with images and categories
- **Video Manager** - YouTube video integration with thumbnails
- **Stats/Counters Manager** - Update homepage statistics
- **Site Settings** - Company info, contact details, social links
- **Forms Inbox** - View and manage form submissions
- **Media Library** - Upload and manage images
- **Activity Log** - Track all changes and actions
- **Responsive Design** - Works on desktop, tablet, and mobile

## Installation

### 1. Database Setup

The admin panel uses your existing WordPress database and adds new tables with the `iz_` prefix.

**Run the setup script:**

```bash
php /var/www/html/izendestudioweb/admin/database/setup.php
```

Or visit in browser:
```
http://yourdomain.com/admin/database/setup.php
```

This will create all necessary tables and insert default data.

### 2. Default Login Credentials

**Username:** `admin`
**Password:** `admin123`

**⚠️ IMPORTANT:** Change the default password immediately after first login!

### 3. Access the Admin Panel

```
http://yourdomain.com/admin/
```

## Directory Structure

```
/admin/
├── assets/
│   ├── css/
│   │   └── admin.css          # Admin panel styles
│   └── js/
│       └── admin.js           # Admin panel JavaScript
├── config/
│   ├── auth.php               # Authentication system
│   └── database.php           # Database configuration
├── database/
│   ├── schema.sql             # Database schema
│   └── setup.php              # Setup script
├── includes/
│   ├── header.php             # Admin header template
│   └── footer.php             # Admin footer template
├── index.php                  # Dashboard
├── login.php                  # Login page
├── logout.php                 # Logout handler
└── README.md                  # This file
```

## Database Tables

The following tables are created in your WordPress database:

| Table | Purpose |
|-------|---------|
| `iz_users` | Admin users (separate from WordPress users) |
| `iz_services` | Service cards displayed on homepage |
| `iz_hero_slides` | Homepage carousel slides |
| `iz_portfolio` | Portfolio items with images |
| `iz_videos` | YouTube videos for video portfolio |
| `iz_stats` | Homepage counter statistics |
| `iz_settings` | Site-wide settings (key-value pairs) |
| `iz_form_submissions` | Contact/quote form submissions |
| `iz_activity_log` | Audit log of all admin actions |
| `iz_media` | Media library (uploaded images) |

## User Roles

### Admin
- Full access to all features
- User management
- Activity logs
- Site settings

### Editor
- Manage content (services, portfolio, videos)
- View form submissions
- Upload media

### Viewer
- Read-only access to dashboard and content

## Security Features

- **Password Hashing** - Bcrypt password encryption
- **Session Management** - Secure sessions with timeout
- **CSRF Protection** - Token-based CSRF prevention (to be implemented)
- **Activity Logging** - All actions are logged with user, IP, and timestamp
- **Input Validation** - Server-side validation for all forms
- **Prepared Statements** - SQL injection prevention

## Configuration

### Database Connection

Edit `/admin/config/database.php` to change database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'your_database');
```

### Session Timeout

Edit `/admin/config/auth.php` to change session duration:

```php
private static $sessionTimeout = 7200; // 2 hours in seconds
```

## Usage

### Managing Services

1. Navigate to **Content > Services**
2. Click "Add New Service"
3. Fill in service details (title, description, icon)
4. Save and the service will appear on the homepage

### Managing Portfolio

1. Navigate to **Content > Portfolio**
2. Click "Add New Portfolio Item"
3. Upload images, add description
4. Select category (web design, SEO, etc.)
5. Save and it will appear in the portfolio section

### Managing Videos

1. Navigate to **Content > Videos**
2. Click "Add New Video"
3. Paste YouTube URL
4. The system will automatically extract video ID and thumbnail
5. Add title and description
6. Save to add to video portfolio

### Viewing Form Submissions

1. Navigate to **Forms & Media > Form Submissions**
2. View all contact/quote form submissions
3. Mark as read, replied, or archived
4. Add notes for follow-up

## Frontend Integration

After adding content through the admin panel, you need to update your frontend files to pull data from the database instead of hardcoded values.

Example for services:

```php
<?php
require_once 'admin/config/database.php';

// Get services from database
$result = mysqli_query($conn, "
    SELECT * FROM iz_services
    WHERE is_visible = 1
    ORDER BY display_order ASC
");

while ($service = mysqli_fetch_assoc($result)) {
    // Display service
    echo '<div class="service-item">';
    echo '<h3>' . htmlspecialchars($service['title']) . '</h3>';
    echo '<p>' . htmlspecialchars($service['description']) . '</p>';
    echo '</div>';
}
?>
```

## API Endpoints (Future)

The admin panel can be extended with REST API endpoints:

- `GET /admin/api/services.php` - Get all services
- `POST /admin/api/services.php` - Create service
- `PUT /admin/api/services.php?id=1` - Update service
- `DELETE /admin/api/services.php?id=1` - Delete service

## Troubleshooting

### Cannot login / Session issues

1. Check that PHP sessions are working:
```php
<?php
session_start();
$_SESSION['test'] = 'working';
echo $_SESSION['test']; // Should output "working"
?>
```

2. Check session directory permissions:
```bash
ls -ld /var/lib/php/sessions
```

### Database connection failed

1. Verify database credentials in `/admin/config/database.php`
2. Check that MySQL is running:
```bash
sudo systemctl status mysql
```

3. Test database connection:
```bash
mysql -u admin -p izendestudioweb_wp
```

### Tables not created

Run the setup script again:
```bash
php /var/www/html/izendestudioweb/admin/database/setup.php
```

Check for errors in the output.

## Development

### Adding New Features

1. Create new PHP file for the manager (e.g., `testimonials.php`)
2. Add navigation link in `/admin/includes/header.php`
3. Create database table in `/admin/database/schema.sql`
4. Add CRUD operations
5. Update frontend to display the data

### Adding New Settings

```php
// Add new setting to database
INSERT INTO iz_settings (setting_key, setting_value, setting_type, setting_group, setting_label)
VALUES ('new_setting', 'default_value', 'text', 'general', 'New Setting Label');
```

## Security Recommendations

1. **Change Default Password** - Immediately after installation
2. **Use HTTPS** - Always access admin panel over HTTPS
3. **Restrict Access** - Use `.htaccess` to limit IP access
4. **Regular Backups** - Backup database regularly
5. **Update PHP** - Keep PHP version up to date
6. **Delete Setup File** - Remove or restrict access to `setup.php` after installation

### Restrict Admin Access (.htaccess)

Create `/admin/.htaccess`:

```apache
# Restrict to specific IPs
Order Deny,Allow
Deny from all
Allow from 192.168.1.100
Allow from 10.0.0.0/8
```

## Future Enhancements

- [ ] WYSIWYG editor for rich text content
- [ ] Bulk actions (delete multiple items)
- [ ] Export data (CSV, JSON)
- [ ] Image cropping and editing
- [ ] Multi-language support
- [ ] Email notifications for form submissions
- [ ] Two-factor authentication
- [ ] API key management
- [ ] Scheduled content publishing
- [ ] Content versioning and rollback

## Support

For issues or questions:
- Check the activity log for errors
- Review browser console for JavaScript errors
- Check PHP error logs: `tail -f /var/log/apache2/error.log`

## Credits

Built for Izende Studio using:
- PHP 8.x
- MySQL/MariaDB
- Bootstrap 5
- Alpine.js
- Bootstrap Icons

---

**Version:** 1.0.0
**Last Updated:** 2025-01-25

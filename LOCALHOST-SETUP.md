# Local Development Setup

Guide to get the admin panel working on localhost for local testing and development.

## Quick Setup (5 minutes)

### 1. Enable Local Database Mode

Edit `/admin/config/.env.local`:

```
# Change this:
DB_ENV=production

# To this:
DB_ENV=local
```

Save the file.

### 2. Create Local Database

```bash
# Create database with root MySQL access
mysql -u root -e "CREATE DATABASE IF NOT EXISTS izendestudioweb_wp;"

# If MySQL root requires password:
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS izendestudioweb_wp;"
```

### 3. Run Database Setup

Open browser and go to:
```
http://localhost/izendestudioweb/admin/database/setup.php
```

**You should see:**
```
✓ Created table: iz_users
✓ Created table: iz_services
... (10 tables total)
Setup Complete!
```

### 4. Login to Local Admin

Go to:
```
http://localhost/izendestudioweb/admin/
```

**Login with:**
- Username: `admin`
- Password: `admin123`

### 5. Test Homepage

Go to:
```
http://localhost/izendestudioweb/
```

**You should see:**
- ✅ Header with navigation
- ✅ Hero section
- ✅ Services section (empty until you add content)
- ✅ Portfolio section (empty until you add content)
- ✅ Stats section
- ✅ Testimonials section
- ✅ Contact section
- ✅ Footer

---

## Switching Back to Production

To switch back to production credentials:

1. Edit `/admin/config/.env.local`
2. Change `DB_ENV=local` to `DB_ENV=production`
3. Or just comment out `DB_ENV=local` with `#`

**Production will use:**
- Database User: `izende6_wp433`
- Database: `izende6_wp433` (on production server)

---

## Troubleshooting

### "Access denied for user 'root'"

**Problem:** MySQL root requires a password or sudo

**Solution 1 - If root has no password:**
```bash
# Make sure sudo allows passwordless MySQL
sudo visudo
# Add line: `%sudo ALL=(ALL:ALL) NOPASSWD: /usr/bin/mysql`
```

**Solution 2 - If root has password:**
Update `.env.local` to use a different MySQL user:

```php
// In admin/config/database.php, modify local section:
define('DB_USER', 'your_mysql_user');
define('DB_PASS', 'your_mysql_password');
```

### "Database connection failed"

**Check that:**
1. MySQL is running: `sudo systemctl status mysql`
2. Database exists: `mysql -u root -e "SHOW DATABASES;"`
3. Database credentials are correct in `.env.local` (should say `DB_ENV=local`)

### Setup Script Shows Errors

**Try running setup again:**
```
http://localhost/izendestudioweb/admin/database/setup.php
```

Or check error log:
```bash
tail -f /var/log/apache2/error.log
```

### Pages Show Blank or 500 Error

**Check:**
1. That you're in local mode: `.env.local` has `DB_ENV=local`
2. That database tables were created (run setup.php again)
3. Error log: `tail -f /var/log/apache2/error.log`

---

## Local Development Workflow

1. **Enable local mode** - `.env.local` has `DB_ENV=local`
2. **Make code changes** locally
3. **Test on localhost** - http://localhost/izendestudioweb/
4. **Verify admin works** - http://localhost/izendestudioweb/admin/
5. **Switch to production mode** - Change `.env.local` to `DB_ENV=production`
6. **Upload to production** - Via FTP to `/home/izende6/public_html/`
7. **Test on production** - https://izendestudioweb.com/admin/

---

## Database Structure

### Local Database (izendestudioweb_wp)
- Same tables as production
- Separate data (for testing)
- User: `root` / Password: (empty or your password)

### Production Database (izende6_wp433)
- Live data
- User: `izende6_wp433` / Password: `Mw~;#vFTq.5D`

---

## Notes

- Never commit `.env.local` changes to git with `DB_ENV=local` - always set to `production` before committing
- Local database is for testing only - use production database for live content
- The `.env.local` file automatically switches which database credentials are used
- All other code is identical between local and production

---

## Next Steps

1. ✅ Set up local database
2. ✅ Run setup.php to create tables
3. ✅ Login to admin
4. ✅ Add test content (services, portfolio, etc.)
5. ✅ Test homepage displays content
6. ✅ Make code changes and test locally
7. ✅ Switch to production mode
8. ✅ Upload files to production
9. ✅ Verify production works

**Enjoy local development!** 🎉

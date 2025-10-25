# Admin Panel Access Instructions

## Issue Identified

The root `.htaccess` file has a security rule that redirects all HTTP to HTTPS (line 12).
This causes a 500 error when accessing the admin panel via HTTP on localhost.

---

## Solution Options

### Option 1: Access via HTTPS (Recommended if SSL is configured)

If you have SSL/HTTPS configured on your server:

**URL:** `https://192.168.1.253/izendestudioweb/admin/login.php`

or

**URL:** `https://yourdomain.com/admin/login.php`

**Login Credentials:**
- Username: `admin`
- Password: `admin123`

---

### Option 2: Temporarily Disable HTTPS Redirect for Testing

If you need to test via HTTP (localhost), temporarily comment out the HTTPS redirect:

1. Edit `/var/www/html/izendestudioweb/.htaccess`
2. Comment out lines 9-13:

```apache
# Temporarily disabled for local testing
# <IfModule mod_rewrite.c>
#     RewriteEngine On
#     RewriteCond %{HTTPS} off
#     RewriteRule ^(.*) https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
# </IfModule>
```

3. Access via: `http://192.168.1.253/izendestudioweb/admin/login.php`

**IMPORTANT:** Re-enable HTTPS redirect after testing!

---

### Option 3: Use the Command Line to Disable HTTP Redirect

Run this command:

```bash
cd /var/www/html/izendestudioweb
cp .htaccess .htaccess.backup
sed -i '10,12s/^/# /' .htaccess
```

This will:
- Create a backup of .htaccess
- Comment out the HTTPS redirect lines

**To restore later:**
```bash
cd /var/www/html/izendestudioweb
cp .htaccess.backup .htaccess
```

---

## After Getting Access

Once you can access the login page:

### Step 1: Login
- Go to the login URL
- Enter username: `admin`
- Enter password: `admin123`
- Click "Sign In"

### Step 2: Verify Dashboard
- You should see the dashboard with 4 stat cards
- Navigation menu on the left
- Your user info in top-right

### Step 3: Test Services Manager
1. Click "Content > Services" in left menu
2. Click "Add New Service"
3. Fill in:
   - Title: WordPress Development
   - Description: Custom WordPress websites...
   - Icon Class: bi bi-wordpress
   - Make sure "Visible on website" is checked
4. Click "Add Service"
5. Verify the service appears in the list

### Step 4: Test Videos Manager
1. Click "Content > Videos" in left menu
2. Click "Add New Video"
3. Paste a YouTube URL (e.g., https://www.youtube.com/watch?v=dQw4w9WgXcQ)
4. Add title and description
5. Select category: Portfolio
6. Click "Add Video"
7. Verify thumbnail loads automatically

### Step 5: Test Drag-and-Drop Reordering
1. In Services or Videos list
2. Click "Reorder" button
3. Drag items to new positions
4. Click "Save Order"
5. Verify order persists after page reload

---

## Troubleshooting

### If you see "Database connection failed"
Check database credentials in `/var/www/html/izendestudioweb/admin/config/database.php`

### If login fails
Password hash has been updated. Credentials should work:
- Username: `admin`
- Password: `admin123`

### If you get 403 Forbidden
The .htaccess security rules may be too strict. Check `/var/www/html/izendestudioweb/.htaccess`

### If sessions don't work
Check PHP session directory permissions:
```bash
ls -ld /var/lib/php/sessions
```

---

## Quick Command to Disable HTTPS Redirect

Copy and paste this command to allow HTTP access for testing:

```bash
cd /var/www/html/izendestudioweb && cp .htaccess .htaccess.backup && sed -i '10,12s/^/# /' .htaccess && echo "HTTPS redirect disabled. Access admin at: http://192.168.1.253/izendestudioweb/admin/login.php"
```

---

## Re-enable HTTPS Redirect After Testing

```bash
cd /var/www/html/izendestudioweb && cp .htaccess.backup .htaccess && echo "HTTPS redirect re-enabled"
```

---

## Your Server Details

- **Local IP:** 192.168.1.253
- **Document Root:** /var/www/html/izendestudioweb
- **Admin Panel Path:** /admin/
- **Database:** izendestudioweb_wp
- **Apache Status:** ✅ Running

---

## Need Help?

Run the connection test:
```bash
php /var/www/html/izendestudioweb/admin/test-connection.php
```

This will verify:
- Database connection
- All tables exist
- Admin user is configured
- File permissions are correct

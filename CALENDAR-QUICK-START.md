# Google Calendar Integration - Quick Start Guide

## TL;DR - What It Does

When you click "Confirm" on a booking in the admin panel:
- ✅ Google Calendar event is created automatically
- ✅ Google Meet video link is generated
- ✅ Client receives calendar invite via email
- ✅ Event appears in your Google Calendar
- ✅ Meeting link is saved and displayed in admin panel

---

## Setup Checklist (First Time Only)

### ☐ Step 1: Enable Google Calendar API
1. Go to https://console.cloud.google.com/
2. Select your project (same as Analytics)
3. Click "APIs & Services" > "Library"
4. Search "Google Calendar API"
5. Click "Enable"

### ☐ Step 2: Use Your Service Account
You already have a service account from Analytics setup. Just note the email address:
- It's in your JSON key file as `client_email`
- Looks like: `your-service@project-123.iam.gserviceaccount.com`

### ☐ Step 3: Share Your Calendar
1. Open https://calendar.google.com/
2. Find the calendar you want to use (or create "Izende Consultations")
3. Click ⋮ (3 dots) > "Settings and sharing"
4. Scroll to "Share with specific people"
5. Click "+ Add people"
6. Paste the service account email from Step 2
7. Set permission: **Make changes to events**
8. Click "Send"

### ☐ Step 4: Get Calendar ID
Still in Calendar settings:
1. Scroll to "Integrate calendar"
2. Copy the **Calendar ID**
3. Usually looks like: `youremail@gmail.com` or `abc123@group.calendar.google.com`

### ☐ Step 5: Download Service Account Key
If you don't have it already:
1. Go to Google Cloud Console > "APIs & Services" > "Credentials"
2. Click your service account
3. Go to "Keys" tab
4. Click "Add Key" > "Create new key" > JSON
5. Save file as `calendar-service-account.json`

### ☐ Step 6: Install Files
1. Place `calendar-service-account.json` in: `/var/www/html/izendestudioweb/`
2. Make sure it's listed in `.gitignore`

### ☐ Step 7: Update .env File
Add these lines to `/var/www/html/izendestudioweb/.env`:

```env
# Google Calendar Configuration
GOOGLE_CALENDAR_ID=your-calendar-id@gmail.com
GOOGLE_CALENDAR_TIMEZONE=America/Chicago
GOOGLE_CALENDAR_SERVICE_ACCOUNT_FILE=calendar-service-account.json
```

Replace `your-calendar-id@gmail.com` with the Calendar ID from Step 4.

### ☐ Step 8: Install Google API Client
Run this command in your project directory:

```bash
cd /var/www/html/izendestudioweb
composer require google/apiclient:"^2.0"
```

If you don't have Composer:
```bash
curl -sS https://getcomposer.org/installer | php
php composer.phar require google/apiclient:"^2.0"
```

---

## How to Use (After Setup)

### Confirming a Booking

1. Go to **Admin Panel** > **Bookings**
2. Click **View** on any pending booking
3. Change status from "Pending" to **"Confirmed"**
4. Click **Update Booking**

**What happens automatically:**
- ✅ Calendar event created in your Google Calendar
- ✅ Google Meet link generated
- ✅ Client receives calendar invite email
- ✅ Event appears in both calendars
- ✅ Meeting link stored in database

### Finding the Google Meet Link

After confirming:
1. Click **View** on the booking again
2. You'll see a blue box with "Video Consultation Link"
3. Click **Join Google Meet** button
4. Copy link to share with client

### Cancelling a Booking

1. Change status to **"Cancelled"**
2. Click **Update Booking**

**What happens automatically:**
- ✅ Calendar event deleted
- ✅ Client receives cancellation email
- ✅ Meeting link removed from database

### Updating a Booking

If you need to change the date/time:
1. Update the booking in database (future feature: edit form)
2. Change status to **"Confirmed"** again
3. System updates the calendar event automatically

---

## Visual Indicators

### In Bookings Table
- **Green "Meet" badge** appears next to confirmed bookings that have Google Meet links
- Shows at a glance which appointments are calendar-synced

### In Booking Details Modal
- **"Video Consultation Link"** section shows Google Meet button
- **"Calendar Event: Synced"** badge confirms calendar integration
- **Warning message** if Calendar not configured (helps you troubleshoot)

---

## Troubleshooting

### "Service account does not have access to calendar"
**Solution:** Make sure you shared the calendar with the service account email (Step 3)
- The email is in your JSON file as `client_email`
- Permission must be "Make changes to events"

### "Calendar ID not configured"
**Solution:** Check your .env file has `GOOGLE_CALENDAR_ID` set correctly
- Copy the Calendar ID from Google Calendar settings
- Don't use quotes in .env file

### "Google API Client not installed"
**Solution:** Run the composer command from Step 8
```bash
composer require google/apiclient:"^2.0"
```

### Events not appearing in calendar
**Solution:**
1. Check that service account has calendar access
2. Verify Calendar ID is correct in .env
3. Check error logs: `/var/log/apache2/error.log`

### No Google Meet link generated
**Solution:** This is normal for the first event. Google Meet links appear after event creation.
- Refresh the booking details modal
- Or just use the calendar event - Meet link is there too

---

## Testing the Integration

### Test #1: Create a Test Booking
1. Go to your website: http://localhost/izendestudioweb/book-consultation.php
2. Fill out the form with your own email
3. Submit booking

### Test #2: Confirm the Booking
1. Go to Admin > Bookings
2. Find your test booking
3. Click "View"
4. Change status to "Confirmed"
5. Click "Update Booking"

### Test #3: Check Your Calendar
1. Open Google Calendar
2. Look for the new event
3. Click the event
4. You should see:
   - Client name and email
   - Google Meet link
   - Event details

### Test #4: Check Admin Panel
1. Go back to Bookings
2. Click "View" on the confirmed booking
3. You should see:
   - "Video Consultation Link" section
   - "Join Google Meet" button
   - "Calendar Event: Synced" badge

### Test #5: Cancel the Booking
1. Change status to "Cancelled"
2. Click "Update Booking"
3. Check Google Calendar - event should be deleted

**If all 5 tests pass, you're all set!** 🎉

---

## Pro Tips

### Tip 1: Create a Dedicated Calendar
Instead of using your main calendar, create a new one called "Client Consultations"
- Keeps business meetings organized
- Easy to share with team members
- Can have different colors/settings

### Tip 2: Calendar Permissions
Only give service account access to ONE calendar, not all calendars
- More secure
- Prevents accidental event creation in wrong calendar

### Tip 3: Email Notifications
In Google Calendar settings, you can customize:
- Who receives event notifications
- How far in advance
- SMS reminders (optional)

### Tip 4: Team Access
If you have a team, share the calendar with them:
- They can see all consultation bookings
- They can join the same Google Meet
- Everyone stays in sync

### Tip 5: Backup Plan
Always have a backup video conferencing option:
- Zoom link in booking notes
- Phone number for audio fallback
- Don't rely 100% on Google Meet

---

## What If I Don't Set This Up?

**No problem!** The booking system works perfectly without Google Calendar:
- Clients can still book consultations
- You still get email notifications
- You can manually create calendar events
- Everything else functions normally

**You can set up Calendar integration anytime in the future.**

---

## Security Notes

### ✅ Do This:
- Keep JSON key file outside web root (or block with .htaccess)
- Add `*.json` to `.gitignore`
- Never commit service account keys to Git
- Use environment variables for sensitive config

### ❌ Don't Do This:
- Don't share service account JSON file publicly
- Don't give service account more permissions than needed
- Don't use personal Google account credentials
- Don't hardcode Calendar ID in PHP files

---

## Need Help?

1. Check the detailed guide: `GOOGLE-CALENDAR-SETUP.md`
2. Review completed features: `COMPLETED-FEATURES.md`
3. Check error logs: `/var/log/apache2/error.log`
4. Test the CalendarHelper availability:
   ```php
   CalendarHelper::isAvailable(); // returns true/false
   ```

---

**Happy scheduling!** 📅

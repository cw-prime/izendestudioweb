# Google Calendar API Integration Setup Guide

This guide will walk you through setting up Google Calendar integration for automatic consultation booking events.

## Benefits

- ✅ Auto-create calendar events when bookings are confirmed
- ✅ Generate Google Meet links for virtual consultations
- ✅ Send calendar invites to both admin and clients
- ✅ Sync cancellations and updates automatically
- ✅ Track event IDs in database for easy management

## Step 1: Enable Google Calendar API

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select your existing project (the one you used for Analytics) or create a new one
3. Navigate to **APIs & Services > Library**
4. Search for "Google Calendar API"
5. Click **Enable**

## Step 2: Create Service Account (if you don't have one already)

**Note:** If you already have a service account from the Analytics setup, you can use the same one.

1. Go to **APIs & Services > Credentials**
2. Click **Create Credentials** > **Service Account**
3. Enter details:
   - **Name:** Izende Studio Web Calendar Service
   - **Description:** Service account for calendar event management
4. Click **Create and Continue**
5. Grant role: **Editor** (or create custom role with Calendar permissions)
6. Click **Done**

## Step 3: Create Service Account Key

1. Click on the service account you just created
2. Go to **Keys** tab
3. Click **Add Key** > **Create new key**
4. Choose **JSON** format
5. Click **Create**
6. The JSON file will download automatically

## Step 4: Install the JSON Key

1. Save the downloaded JSON file as: `calendar-service-account.json`
2. Place it in the root directory: `/var/www/html/izendestudioweb/`
3. Make sure it's listed in `.gitignore` to prevent committing sensitive credentials

## Step 5: Share Your Google Calendar with Service Account

This is CRITICAL - the service account needs permission to create events on your calendar:

1. Open [Google Calendar](https://calendar.google.com/)
2. Find the calendar you want to use (or create a new one for consultations)
3. Click the 3 dots next to the calendar name > **Settings and sharing**
4. Scroll down to **Share with specific people**
5. Click **Add people**
6. Enter the service account email (found in the JSON file as `client_email`)
   - It looks like: `izende-studio-web-calendar@project-name.iam.gserviceaccount.com`
7. Set permission to **Make changes to events**
8. Click **Send**

## Step 6: Get Your Calendar ID

1. In Google Calendar settings (same page as above)
2. Scroll down to **Integrate calendar**
3. Copy the **Calendar ID** (looks like: `youremail@gmail.com` or `abc123@group.calendar.google.com`)
4. Save this - you'll add it to your `.env` file

## Step 7: Update .env File

Add these lines to your `/var/www/html/izendestudioweb/.env` file:

```env
# Google Calendar Configuration
GOOGLE_CALENDAR_ID=your-calendar-id@gmail.com
GOOGLE_CALENDAR_TIMEZONE=America/Chicago
GOOGLE_CALENDAR_SERVICE_ACCOUNT_FILE=calendar-service-account.json
```

Replace `your-calendar-id@gmail.com` with your actual calendar ID from Step 6.

## Step 8: Install Google API PHP Client

Run this command in your project directory:

```bash
composer require google/apiclient:"^2.0"
```

If you don't have Composer installed, install it first:

```bash
curl -sS https://getcomposer.org/installer | php
php composer.phar require google/apiclient:"^2.0"
```

## What Happens Next

Once setup is complete, the system will automatically:

1. **On Booking Confirmation:**
   - Create a Google Calendar event with 30-minute duration
   - Generate a Google Meet link for the consultation
   - Add client email as attendee
   - Send calendar invite to client
   - Store event ID in database (`google_event_id` field)

2. **On Booking Cancellation:**
   - Delete the calendar event
   - Send cancellation notice to client

3. **On Booking Update:**
   - Update the calendar event time/details
   - Notify attendees of changes

## Troubleshooting

### "Service account does not have access to calendar"
- Make sure you shared the calendar with the service account email (Step 5)
- Wait a few minutes for permissions to propagate

### "Calendar API not enabled"
- Go back to Step 1 and enable the API
- Make sure you're using the correct Google Cloud project

### "File not found: calendar-service-account.json"
- Check that the JSON file is in the root directory
- Verify the filename matches exactly in .env file

### Events not appearing in calendar
- Check that GOOGLE_CALENDAR_ID in .env matches your actual calendar ID
- Verify the service account has "Make changes to events" permission

---

**Ready to use?** Once you complete all steps above, the system will automatically create calendar events when you confirm bookings in the admin panel!

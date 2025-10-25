# Google Analytics Dashboard Setup Guide

## 📊 Overview

Your admin panel now includes a full Analytics Dashboard that displays:
- ✅ Page views over time (line chart)
- ✅ Total users and sessions
- ✅ Average session duration
- ✅ Top pages (table with bars)
- ✅ Traffic sources (pie chart)
- ✅ All data cached to avoid rate limits

## 🚀 Setup Instructions

### Step 1: Enable Google Analytics Data API

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select or create a project
3. Click **APIs & Services** → **Library**
4. Search for **"Google Analytics Data API"**
5. Click **ENABLE**

### Step 2: Create Service Account

1. In Google Cloud Console, go to **IAM & Admin** → **Service Accounts**
2. Click **CREATE SERVICE ACCOUNT**
3. Enter a name (e.g., "Analytics Dashboard")
4. Click **CREATE AND CONTINUE**
5. Skip role selection (click CONTINUE)
6. Click **DONE**

### Step 3: Create JSON Key

1. Click on the service account you just created
2. Go to **KEYS** tab
3. Click **ADD KEY** → **Create new key**
4. Select **JSON** format
5. Click **CREATE**
6. The JSON file will download automatically - SAVE IT SECURELY!

### Step 4: Grant Service Account Access to Analytics

1. Go to [Google Analytics](https://analytics.google.com)
2. Click **Admin** (gear icon bottom left)
3. In the **Property** column, click **Property Access Management**
4. Click **+** (Add Users)
5. Enter the service account email (from the JSON file, looks like `analytics-dashboard@your-project.iam.gserviceaccount.com`)
6. Select role: **Viewer**
7. Uncheck "Notify new users by email"
8. Click **ADD**

### Step 5: Get Your Property ID

1. In Google Analytics, click **Admin**
2. Under **Property**, click **Property Settings**
3. Copy the **Property ID** (numeric, e.g., `123456789`)

### Step 6: Configure in Admin Panel

1. Go to your admin panel: `/admin/analytics.php`
2. Scroll to **Analytics Dashboard (Advanced)** section
3. Enable "Enable Analytics Dashboard"
4. Enter your **Analytics Property ID** (from Step 5)
5. Upload the JSON key file (from Step 3) OR paste the JSON content
6. Click **Save Settings**

### Step 7: View Your Dashboard

1. Go to **Analytics Dashboard** in the admin sidebar
2. You should see graphs and data!
3. If you see an error, check:
   - Property ID is correct
   - Service account has been added to Analytics
   - JSON key file is valid

## 📁 Files Created

- `/admin/analytics-dashboard.php` - Dashboard page with charts
- `/admin/includes/AnalyticsFetcher.php` - API integration class
- `/admin/cache/analytics/` - Cache directory for API responses

## 🔒 Security Notes

- Service account JSON is stored **base64 encoded** in the database
- API responses are **cached for 1 hour** to avoid rate limits
- Only admins can access the dashboard
- Access tokens are cached and auto-refreshed

## 🐛 Troubleshooting

### "Failed to get access token"
- Check that the JSON key file is valid
- Ensure the service account exists in Google Cloud Console

### "API request failed"
- Verify the Property ID is correct
- Check that Google Analytics Data API is enabled
- Ensure service account has "Viewer" role in Analytics

### "No data available"
- Make sure your website has recent traffic
- Check that GA4 tracking is working on your site
- Wait a few hours for data to populate

### Cache directory permission errors
```bash
chmod 755 /var/www/html/izendestudioweb/admin/cache/analytics
```

## 📊 What Gets Tracked

The dashboard shows data from the last 7 days:
- **Page Views** - Total views over time
- **Users** - Unique visitors
- **Sessions** - Number of visits
- **Avg Duration** - Average time spent on site
- **Top Pages** - Most visited pages
- **Traffic Sources** - Where visitors come from

## 🎨 Technologies Used

- Google Analytics Data API v1beta
- Chart.js for graphs
- JWT authentication for service accounts
- File-based caching system
- Bootstrap 5 UI

## ⏱️ Data Freshness

- API data is cached for **1 hour**
- Access tokens are cached until expiry
- Clear cache by deleting files in `/admin/cache/analytics/`

---

**Need Help?** Check the Google Analytics Data API documentation:
https://developers.google.com/analytics/devguides/reporting/data/v1

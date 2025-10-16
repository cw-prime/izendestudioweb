# Conversion Tracking Setup Guide

Complete guide for implementing Google Tag Manager, GA4, Facebook Pixel, and call tracking for Izende Studio Web.

**Last Updated:** 2025-10-15
**Next Review:** 2025-11-15

---

## Table of Contents

1. [Overview](#overview)
2. [Phase 1: Google Tag Manager Setup](#phase-1-google-tag-manager-setup)
3. [Phase 2: GA4 Configuration](#phase-2-ga4-configuration)
4. [Phase 3: Facebook Pixel Setup](#phase-3-facebook-pixel-setup)
5. [Phase 4: Conversion Events](#phase-4-conversion-events)
6. [Phase 5: Call Tracking](#phase-5-call-tracking)
7. [Testing & Verification](#testing--verification)
8. [Monitoring & Optimization](#monitoring--optimization)

---

## Overview

### Goals

Track all conversion events to measure marketing ROI and optimize website performance:

- Form submissions (contact, quote, lead magnet)
- Button clicks (CTAs, phone, email)
- Scroll depth (user engagement)
- Chat interactions (Tawk.to)
- Phone calls (via call tracking)

### Tools Used

✅ **Google Tag Manager** - Tag management system
✅ **Google Analytics 4** - Web analytics
✅ **Facebook Pixel** - Social media advertising
✅ **Call Tracking** - Phone call attribution

### Current Implementation

The GTM container code is already installed in `index.php`:
- Head script: Lines 112-117
- Body noscript: Lines 134-136

---

## Phase 1: Google Tag Manager Setup

### Step 1: Create GTM Account

1. Go to [tagmanager.google.com](https://tagmanager.google.com)
2. Click "Create Account"
3. Account Name: **Izende Studio Web**
4. Country: **United States**
5. Container Name: **izendestudioweb.com**
6. Target Platform: **Web**

### Step 2: Get Container ID

After creating the container, GTM will show you the container ID (format: `GTM-XXXXXXX`).

### Step 3: Update Website Code

Replace `GTM-XXXXXXX` in `/index.php` (lines 116 and 134) with your actual Container ID.

**Before:**
```javascript
})(window,document,'script','dataLayer','GTM-XXXXXXX');
```

**After:**
```javascript
})(window,document,'script','dataLayer','GTM-AB12CD3');
```

### Step 4: Verify Installation

1. Install [Google Tag Assistant Chrome Extension](https://chrome.google.com/webstore/detail/google-tag-assistant)
2. Visit your website
3. Click Tag Assistant icon
4. Verify GTM container is firing

---

## Phase 2: GA4 Configuration

### Step 1: Link Existing GA4 Property

You already have GA4 set up (Measurement ID: `G-JJ5VJ6SS5X`).

Now we'll manage it through GTM instead of the hardcoded script.

### Step 2: Create GA4 Configuration Tag in GTM

1. In GTM, go to **Tags** → **New**
2. Tag Configuration → **Google Analytics: GA4 Configuration**
3. Measurement ID: `G-JJ5VJ6SS5X`
4. Tag Name: `GA4 - Configuration`
5. Triggering: **All Pages**
6. Save

### Step 3: Enhanced Measurement

In GA4 property (not GTM):

1. Go to **Admin** → **Data Streams**
2. Click your web stream
3. Enable **Enhanced Measurement**:
   - ✅ Page views
   - ✅ Scrolls (90%)
   - ✅ Outbound clicks
   - ✅ Site search
   - ✅ Video engagement
   - ✅ File downloads

### Step 4: Create Custom Events (GA4)

In GA4, go to **Configure** → **Events** → **Create Event**

**Event 1: Form Submission**
- Event name: `generate_lead`
- Matching conditions: event_name = `form_submit`

**Event 2: Phone Click**
- Event name: `phone_click`
- Matching conditions: event_name = `phone_click`

**Event 3: Lead Magnet Conversion**
- Event name: `lead_magnet_conversion`
- Matching conditions: event_name = `lead_magnet_conversion`
- Mark as **Conversion**: Yes

---

## Phase 3: Facebook Pixel Setup

### Step 1: Create Facebook Pixel

1. Go to [Facebook Events Manager](https://business.facebook.com/events_manager)
2. Click **Connect Data Sources** → **Web**
3. Select **Facebook Pixel** → **Connect**
4. Name: **Izende Studio Web**
5. Enter website URL: `https://izendestudioweb.com`
6. Copy your Pixel ID (format: `123456789012345`)

### Step 2: Install Pixel via GTM

#### Tag 1: Pixel Base Code

1. In GTM, **Tags** → **New**
2. Tag Configuration → **Custom HTML**
3. HTML:

```html
<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', 'YOUR_PIXEL_ID');
fbq('track', 'PageView');
</script>
<noscript>
<img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=YOUR_PIXEL_ID&ev=PageView&noscript=1"/>
</noscript>
<!-- End Facebook Pixel Code -->
```

4. Replace `YOUR_PIXEL_ID` with actual Pixel ID
5. Tag Name: `Facebook Pixel - Base Code`
6. Triggering: **All Pages**
7. Advanced Settings → Tag Firing Options: **Once per page**
8. Save

### Step 3: Test Pixel

1. Install [Facebook Pixel Helper Chrome Extension](https://chrome.google.com/webstore/detail/facebook-pixel-helper)
2. Visit your website
3. Click extension icon
4. Verify pixel is active and PageView event fires

---

## Phase 4: Conversion Events

### Event 1: Contact Form Submission

**GTM Tag:**

1. **Tags** → **New**
2. Tag Type: **Google Analytics: GA4 Event**
3. Configuration Tag: `GA4 - Configuration`
4. Event Name: `form_submit`
5. Event Parameters:
   - `form_name`: `contact_form`
   - `form_location`: `contact_section`

**Trigger:**

1. **Triggers** → **New**
2. Trigger Type: **Custom Event**
3. Event name: `form_submit`
4. Save as: `CE - Form Submit - Contact`

**Facebook Pixel Tag (Contact):**

1. **Tags** → **New**
2. Tag Type: **Custom HTML**
3. HTML:
```html
<script>
fbq('track', 'Contact', {
  form_name: 'contact_form'
});
</script>
```
4. Triggering: `CE - Form Submit - Contact`
5. Save as: `FB Pixel - Contact Form`

### Event 2: Lead Magnet Conversion

**GTM Tag:**

1. **Tags** → **New**
2. Tag Type: **Google Analytics: GA4 Event**
3. Event Name: `lead_magnet_conversion`
4. Event Parameters:
   - `value`: `25` (estimated lead value)
   - `currency`: `USD`

**Trigger:**

1. **Triggers** → **New**
2. Trigger Type: **Custom Event**
3. Event name: `lead_magnet_conversion`
4. Save as: `CE - Lead Magnet Conversion`

**Facebook Pixel Tag (Lead):**

1. **Tags** → **New**
2. Tag Type: **Custom HTML**
3. HTML:
```html
<script>
fbq('track', 'Lead', {
  content_name: 'Website Launch Checklist',
  value: 25.00,
  currency: 'USD'
});
</script>
```
4. Triggering: `CE - Lead Magnet Conversion`
5. Save as: `FB Pixel - Lead Magnet`

### Event 3: Phone Click Tracking

**GTM Tag:**

1. **Tags** → **New**
2. Tag Type: **Google Analytics: GA4 Event**
3. Event Name: `phone_click`
4. Event Parameters:
   - `phone_number`: `{{Click URL}}`
   - `location`: `{{Page Path}}`

**Trigger:**

1. **Triggers** → **New**
2. Trigger Type: **Click - All Elements**
3. This trigger fires on: **Some Clicks**
4. Conditions:
   - Click URL contains `tel:`
5. Save as: `Click - Phone Number`

**Facebook Pixel Tag (Phone):**

1. **Tags** → **New**
2. Tag Type: **Custom HTML**
3. HTML:
```html
<script>
fbq('trackCustom', 'PhoneClick', {
  phone_number: '314-312-6441'
});
</script>
```
4. Triggering: `Click - Phone Number`
5. Save as: `FB Pixel - Phone Click`

### Event 4: CTA Button Clicks

**GTM Tag:**

1. **Tags** → **New**
2. Tag Type: **Google Analytics: GA4 Event**
3. Event Name: `cta_click`
4. Event Parameters:
   - `button_text`: `{{Click Text}}`
   - `button_url`: `{{Click URL}}`

**Trigger:**

1. **Triggers** → **New**
2. Trigger Type: **Click - All Elements**
3. This trigger fires on: **Some Clicks**
4. Conditions:
   - Click Classes contains `btn-get-started` OR
   - Click Classes contains `cta-btn` OR
   - Click Classes contains `btn-brand`
5. Save as: `Click - CTA Buttons`

### Event 5: Scroll Depth

The JavaScript in `main.js` already handles this via `trackConversion()` function which pushes to dataLayer.

**GTM Tag:**

1. **Tags** → **New**
2. Tag Type: **Google Analytics: GA4 Event**
3. Event Name: `scroll_depth`
4. Event Parameters:
   - `depth_percent`: `{{Event - depth_percent}}`

**Trigger:**

1. **Triggers** → **New**
2. Trigger Type: **Custom Event**
3. Event name: `scroll_depth`
4. Save as: `CE - Scroll Depth`

---

## Phase 5: Call Tracking

### Option 1: CallRail (Recommended)

**Setup:**

1. Sign up at [callrail.com](https://www.callrail.com)
2. Create a tracking number
3. Set source number as: `314-312-6441`
4. Enable dynamic number insertion
5. Add CallRail script to website via GTM:

**GTM Tag:**

1. **Tags** → **New**
2. Tag Type: **Custom HTML**
3. HTML:
```html
<script>
(function(){var w=window;var d=document;var s=d.createElement('script');s.src='https://cdn.callrail.com/companies/YOUR_COMPANY_ID/YOUR_SCRIPT_ID.js';s.async=true;var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);})();
</script>
```
4. Triggering: **All Pages**
5. Save as: `CallRail - Dynamic Number Insertion`

**Integration with GTM:**

Enable CallRail's GTM integration to automatically push call events to dataLayer.

### Option 2: Google Ads Call Extensions

If running Google Ads:

1. In Google Ads, create call extension
2. Use forwarding number provided by Google
3. Enable call reporting
4. Calls automatically tracked in Google Ads and GA4

### Option 3: Manual Call Tracking

Update phone numbers on site to unique tracking numbers for different sources:

- **Organic Traffic:** One number
- **Google Ads:** Another number
- **Facebook Ads:** Another number
- **Email Campaigns:** Another number

Track which number receives calls to attribute source.

**Note:** For simplicity, we recommend starting with CallRail.

---

## Testing & Verification

### Step 1: GTM Preview Mode

1. In GTM, click **Preview**
2. Enter your website URL
3. GTM Tag Assistant window opens
4. Navigate your site and trigger events
5. Verify all tags fire correctly

### Step 2: GA4 DebugView

1. In GA4, go to **Configure** → **DebugView**
2. With GTM Preview Mode active, perform actions on site
3. Watch events appear in real-time in DebugView
4. Verify all custom events are tracked

### Step 3: Facebook Pixel Testing

1. Open [Facebook Events Manager](https://business.facebook.com/events_manager)
2. Click **Test Events**
3. Enter your website URL
4. Perform conversions on site
5. Verify events appear in Test Events panel

### Step 4: Checklist

Test each conversion:

- [ ] Page view tracked in GA4 and FB Pixel
- [ ] Contact form submission tracked
- [ ] Lead magnet conversion tracked
- [ ] Phone number click tracked
- [ ] CTA button clicks tracked
- [ ] Email link clicks tracked
- [ ] Scroll depth milestones tracked
- [ ] Chat interactions tracked (if integrated)
- [ ] Call tracking numbers working (if enabled)

---

## Monitoring & Optimization

### Daily Checks

**GA4 Real-Time Report:**
- Monitor active users
- Check for tracking errors
- Verify events are firing

**Facebook Events Manager:**
- Review event activity
- Check pixel status
- Monitor ad performance

### Weekly Review

**Key Metrics:**

1. **Traffic Sources**
   - Organic search
   - Direct
   - Social media
   - Paid ads

2. **Conversion Events**
   - Total form submissions
   - Lead magnet conversions
   - Phone clicks
   - Email clicks

3. **User Behavior**
   - Average scroll depth
   - Pages per session
   - Bounce rate
   - Average session duration

4. **Top Pages**
   - Most visited
   - Highest conversion rate
   - Longest time on page

### Monthly Deep Dive

**GA4 Reports:**

1. **Acquisition Report**
   - Which channels bring the most traffic?
   - Which channels have the highest conversion rate?
   - ROI by channel

2. **Engagement Report**
   - Most engaging content
   - Drop-off points
   - User flow through site

3. **Conversion Reports**
   - Conversion rate by traffic source
   - Which CTAs perform best?
   - Lead magnet conversion rate

**Actions Based on Data:**

✓ Double down on high-performing channels
✓ Optimize underperforming pages
✓ A/B test CTAs and copy
✓ Adjust ad spend based on ROI
✓ Create content for high-traffic topics

### GTM Variable Setup

Create these variables for easier tracking:

**Variable 1: Page Path**
- Type: **URL**
- Component Type: **Path**
- Name: `Page Path`

**Variable 2: Click Text**
- Type: **Click Text**
- Name: `Click Text`

**Variable 3: Click URL**
- Type: **Click URL**
- Name: `Click URL`

**Variable 4: Click Classes**
- Type: **Click Classes**
- Name: `Click Classes`

**Variable 5: Form ID**
- Type: **Form ID**
- Name: `Form ID`

---

## Advanced: Conversion Attribution

### UTM Parameters

Use UTM parameters to track campaign performance:

**Structure:**
```
https://izendestudioweb.com/?utm_source=facebook&utm_medium=cpc&utm_campaign=website-launch&utm_content=carousel-ad
```

**Parameters:**
- `utm_source`: facebook, google, email, etc.
- `utm_medium`: cpc, email, social, organic
- `utm_campaign`: campaign name
- `utm_content`: ad variation
- `utm_term`: keyword (for paid search)

**Tools:**
- [Google's Campaign URL Builder](https://ga-dev-tools.web.app/campaign-url-builder/)

### Multi-Touch Attribution

In GA4, review **Advertising** → **Attribution** to see:

- First-click attribution (which channel brought them first)
- Last-click attribution (which channel converted them)
- Data-driven attribution (GA4's AI model)

Use this to understand the full customer journey.

---

## Call Tracking Implementation Details

### Dynamic Number Insertion (DNI)

With CallRail or similar service:

1. Different phone numbers show based on traffic source
2. Visitor from Google Ads sees one number
3. Visitor from Facebook sees another
4. Organic visitor sees base number (314-312-6441)

This allows precise attribution of phone calls to marketing channels.

### Call Recording & Analytics

Enable call recording to:
- ✓ Review call quality
- ✓ Train staff
- ✓ Identify common questions
- ✓ Improve conversion rate

**Privacy:** Always notify callers that calls are recorded.

### Call Tracking Metrics

Monitor:
- **Total calls** per day/week/month
- **Call duration** (longer usually = more qualified)
- **Calls by source** (which marketing channel drives calls)
- **Call conversion rate** (calls that become customers)
- **Peak call times** (staff accordingly)

---

## Troubleshooting

### GTM Container Not Firing

**Check:**
1. Container ID is correct in code
2. No JavaScript errors on page (check console)
3. Code is in both `<head>` and `<body>`
4. Ad blockers disabled (for testing)

### GA4 Events Not Showing

**Check:**
1. Measurement ID is correct
2. Events are pushed to dataLayer correctly
3. Use DebugView to see events in real-time
4. Wait 24-48 hours for data to populate in standard reports

### Facebook Pixel Not Working

**Check:**
1. Pixel ID is correct
2. Pixel fires on all pages (check with Pixel Helper)
3. Ad blockers disabled
4. Events match standard Facebook event names
5. Test Events tool shows activity

### Call Tracking Issues

**Check:**
1. Forwarding number is correct (314-312-6441)
2. Call tracking script loads without errors
3. Dynamic number insertion works across all pages
4. Test by calling displayed number

---

## Security & Privacy

### GDPR/CCPA Compliance

**Required:**

1. **Cookie Consent Banner**
   - Notify users of tracking
   - Allow opt-out
   - Use tool like Osano or OneTrust

2. **Privacy Policy**
   - Disclose use of GA4, Facebook Pixel, call tracking
   - Explain what data is collected
   - Provide opt-out instructions

3. **Data Retention**
   - In GA4: Admin → Data Settings → Data Retention
   - Set to 14 months (default) or shorter if required

### Best Practices

- ✅ Never track personally identifiable information (PII)
- ✅ Anonymize IP addresses (enabled by default in GA4)
- ✅ Use consent mode (Google's Consent Mode)
- ✅ Respect Do Not Track browser settings
- ✅ Regularly audit what data you collect

---

## Resources

**Official Documentation:**
- [Google Tag Manager](https://support.google.com/tagmanager)
- [Google Analytics 4](https://support.google.com/analytics)
- [Facebook Pixel](https://www.facebook.com/business/help/952192354843755)
- [CallRail](https://support.callrail.com/)

**Testing Tools:**
- [Google Tag Assistant](https://tagassistant.google.com/)
- [Facebook Pixel Helper](https://chrome.google.com/webstore/detail/facebook-pixel-helper)
- [GA Debugger Chrome Extension](https://chrome.google.com/webstore/detail/google-analytics-debugger)

**Internal Contacts:**
- Setup Help: support@izendestudioweb.com
- Phone: 314-312-6441

---

**Document Status:** ✅ Complete
**Maintained By:** Izende Studio Web Team
**Version:** 1.0
**Phone:** 314-312-6441

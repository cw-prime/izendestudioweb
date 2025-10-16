# Google Tag Manager Setup - Quick Reference

## Overview
This guide provides step-by-step instructions for configuring Google Tag Manager to track all conversion events on the Izende Studio Web website.

---

## Prerequisites

1. Google Tag Manager account created
2. GTM container installed on website (✅ Already done in `index.php`)
3. Google Analytics 4 (GA4) property set up
4. Access to GTM container

---

## Step 1: Update GTM Container ID

**File:** `index.php` (lines 111-117)

Replace placeholder with your actual GTM container ID:

```javascript
// Current (PLACEHOLDER):
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-XXXXXXX');

// Update to (EXAMPLE):
})(window,document,'script','dataLayer','GTM-K12ABC3');
```

---

## Step 2: Create Custom Event Triggers in GTM

Navigate to: **Triggers** → **New**

Create a trigger for each of these custom events:

| Trigger Name | Trigger Type | Event Name |
|--------------|--------------|------------|
| Contact Form Submit | Custom Event | `contact_form_submit` |
| Quote Form Submit | Custom Event | `quote_form_submit` |
| Lead Magnet Conversion | Custom Event | `lead_magnet_conversion` |
| Phone Click | Custom Event | `phone_click` |
| Email Click | Custom Event | `email_click` |
| CTA Click | Custom Event | `cta_click` |
| Scroll Depth | Custom Event | `scroll_depth` |
| Lead Magnet Shown | Custom Event | `lead_magnet_shown` |

### Example: Contact Form Submit Trigger

1. Click **New** under Triggers
2. **Name:** "Contact Form Submit"
3. **Trigger Type:** Custom Event
4. **Event name:** `contact_form_submit`
5. **This trigger fires on:** All Custom Events
6. Click **Save**

Repeat for all 8 events above.

---

## Step 3: Create Data Layer Variables

Navigate to: **Variables** → **User-Defined Variables** → **New**

Create these variables to capture event data:

| Variable Name | Variable Type | Data Layer Variable Name |
|---------------|---------------|--------------------------|
| DL - Form Name | Data Layer Variable | `form_name` |
| DL - Email | Data Layer Variable | `email` |
| DL - Name | Data Layer Variable | `name` |
| DL - Phone Number | Data Layer Variable | `phone_number` |
| DL - Location | Data Layer Variable | `location` |
| DL - Button Text | Data Layer Variable | `button_text` |
| DL - Button URL | Data Layer Variable | `button_url` |
| DL - Depth Percent | Data Layer Variable | `depth_percent` |
| DL - Page Path | Data Layer Variable | `page_path` |

### Example: Form Name Variable

1. Click **New** under User-Defined Variables
2. **Name:** "DL - Form Name"
3. **Variable Type:** Data Layer Variable
4. **Data Layer Variable Name:** `form_name`
5. Click **Save**

---

## Step 4: Create GA4 Event Tags

Navigate to: **Tags** → **New**

Create a GA4 Event tag for each conversion event:

### Example: Contact Form Submit Tag

1. Click **New** under Tags
2. **Name:** "GA4 - Contact Form Submit"
3. **Tag Type:** Google Analytics: GA4 Event
4. **Configuration Tag:** Select your GA4 Configuration tag
5. **Event Name:** `contact_form_submit`
6. **Event Parameters:**
   - Parameter Name: `form_name`
   - Value: `{{DL - Form Name}}`
7. **Triggering:** Select "Contact Form Submit" trigger
8. Click **Save**

### All GA4 Event Tags to Create:

| Tag Name | Event Name | Event Parameters | Trigger |
|----------|------------|------------------|---------|
| GA4 - Contact Form Submit | `contact_form_submit` | `form_name` | Contact Form Submit |
| GA4 - Quote Form Submit | `quote_form_submit` | `form_name` | Quote Form Submit |
| GA4 - Lead Magnet Conversion | `lead_magnet_conversion` | `email`, `name` | Lead Magnet Conversion |
| GA4 - Phone Click | `phone_click` | `phone_number`, `location` | Phone Click |
| GA4 - Email Click | `email_click` | `email`, `location` | Email Click |
| GA4 - CTA Click | `cta_click` | `button_text`, `button_url`, `location` | CTA Click |
| GA4 - Scroll Depth | `scroll_depth` | `depth_percent`, `page_path` | Scroll Depth |
| GA4 - Lead Magnet Shown | `lead_magnet_shown` | (none) | Lead Magnet Shown |

---

## Step 5: Test in Preview Mode

1. In GTM, click **Preview** (top right)
2. Enter your website URL: `https://izendestudioweb.com`
3. Click **Connect**
4. Test each conversion event:
   - Submit contact form → Check for `contact_form_submit`
   - Start quote form → Check for `quote_form_submit`
   - Trigger exit-intent modal → Check for `lead_magnet_shown`
   - Submit lead capture → Check for `lead_magnet_conversion`
   - Click phone number → Check for `phone_click`
   - Click email link → Check for `email_click`
   - Click CTA button → Check for `cta_click`
   - Scroll to 25%, 50%, 75%, 100% → Check for `scroll_depth`

5. Verify in Preview:
   - Events fire at correct times
   - Data layer variables populate correctly
   - Tags fire successfully

---

## Step 6: Submit and Publish

1. Click **Submit** (top right)
2. **Version Name:** "Added conversion tracking for forms and CTAs"
3. **Version Description:** "Implemented tracking for contact form, quote form, lead magnet, phone/email clicks, CTA clicks, and scroll depth"
4. Click **Publish**

---

## Step 7: Verify in GA4

1. Go to **Google Analytics 4** → **Reports** → **Realtime**
2. Test events on your website
3. Check that events appear in real-time report within 30 seconds
4. Navigate to **Configure** → **Events** to see all custom events

### Expected Events in GA4:

- `contact_form_submit`
- `quote_form_submit`
- `lead_magnet_conversion`
- `lead_magnet_shown`
- `phone_click`
- `email_click`
- `cta_click`
- `scroll_depth`

---

## Conversion Goals (Optional)

Create conversions for high-value events:

1. In GA4, go to **Configure** → **Conversions**
2. Click **New conversion event**
3. Enter event name (e.g., `contact_form_submit`)
4. Click **Save**

Recommended conversions to mark:
- ✅ `contact_form_submit`
- ✅ `quote_form_submit`
- ✅ `lead_magnet_conversion`
- ✅ `phone_click`

---

## Debugging Tips

### Check Console Logs
Open browser console (F12) and look for:
```
Conversion tracked: contact_form_submit {form_name: "contact_form"}
Conversion tracked: quote_form_submit {form_name: "quote_form"}
```

### Check Data Layer
In console, type:
```javascript
dataLayer
```
You should see an array with your custom events.

### GTM Preview Mode Not Working?
- Clear browser cache
- Ensure GTM container ID is correct
- Check for JavaScript errors in console
- Verify dataLayer is defined before GTM loads

---

## Facebook Pixel Integration (Optional)

The `trackConversion()` function already supports Facebook Pixel. To enable:

1. Install Facebook Pixel base code in `index.php`
2. Events will automatically fire as custom Facebook events
3. No additional GTM configuration needed

---

## Event Naming Convention

All events follow this naming pattern:
- Lowercase with underscores
- Descriptive action names
- Consistent parameter names

**Examples:**
- ✅ `contact_form_submit`
- ✅ `lead_magnet_conversion`
- ❌ `ContactFormSubmit`
- ❌ `leadMagnetConversion`

---

## Support & Troubleshooting

### Common Issues:

**Events not firing:**
- Check browser console for JavaScript errors
- Verify GTM container ID is correct
- Ensure dataLayer is defined

**Data layer variables empty:**
- Check variable names match exactly (case-sensitive)
- Verify event data is passed in trackConversion() call
- Use GTM Preview to inspect data layer values

**GA4 events not appearing:**
- Allow up to 24 hours for events to appear in standard reports
- Check Realtime reports for immediate verification
- Verify GA4 measurement ID is correct in GTM config tag

---

## Additional Resources

- [GTM Documentation](https://support.google.com/tagmanager)
- [GA4 Event Tracking Guide](https://support.google.com/analytics/answer/9216061)
- [Data Layer Documentation](https://developers.google.com/tag-manager/devguide/datalayer)

---

**Setup Time Estimate:** 30-45 minutes
**Difficulty:** Intermediate
**Required Access:** GTM Admin, GA4 Editor

---

*Last Updated: October 15, 2025*
*Izende Studio Web - GTM Configuration*

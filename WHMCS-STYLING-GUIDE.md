# WHMCS Styling Integration Guide
## Izende Studio Web - WHMCS & Main Site Integration

**Date:** October 24, 2025
**Status:** CSS Styling Complete - Product Configuration Needed

---

## Overview

This guide documents the integration of WHMCS with the main Izende Studio Web site, ensuring consistent branding, styling, and user experience across both platforms.

---

## ✅ What's Already Done

### 1. Navigation Link from WHMCS to Main Site
- **Location:** [adminIzende/templates/twenty-one/header.tpl](adminIzende/templates/twenty-one/header.tpl#L90-L92)
- **Feature:** "Main Site" button in WHMCS header
- **Functionality:** Allows users to easily return to the main website

### 2. Brand Colors Already Match
- **Main Site Color:** `#5cb874` (Green)
- **WHMCS Color:** `#5cb874` (Green)
- **Files:**
  - Main site: [assets/css/style.css](assets/css/style.css)
  - WHMCS: [adminIzende/templates/twenty-one/css/theme.css](adminIzende/templates/twenty-one/css/theme.css)

### 3. Typography Matches
- **Headings:** Raleway font (Both sites)
- **Body:** Open Sans font (Both sites)
- **Configured in:** [adminIzende/templates/twenty-one/css/custom.css](adminIzende/templates/twenty-one/css/custom.css)

### 4. Enhanced Custom CSS
- **File:** [adminIzende/templates/twenty-one/css/custom.css](adminIzende/templates/twenty-one/css/custom.css)
- **Size:** 1,024 lines
- **Features Added:**
  - Section separators (gradient lines matching main site)
  - Enhanced pricing tables with hover effects
  - Icon boxes matching main site style
  - Button animations (lift effect on hover)
  - Breadcrumb styling
  - Footer enhancements
  - Domain search styling
  - Cart/checkout improvements
  - Responsive design adjustments
  - Print styles

### 5. Logo File Copied
- **Source:** `/assets/img/izende-T.png`
- **Destination:** `/adminIzende/assets/img/izende-T.png`
- **Status:** ✅ File copied successfully

### 6. Hosting Page Links
- **Main Site Files:**
  - [hosting.php](hosting.php) - General hosting page
  - [missouri-web-hosting.php](missouri-web-hosting.php) - Regional hosting page
- **Links to WHMCS:**
  - Shared Hosting → `/adminIzende/index.php?rp=/store/shared-hosting`
  - VPS Hosting → `/adminIzende/index.php?rp=/store/vps-hosting`
  - Dedicated Servers → `/adminIzende/index.php?rp=/store/dedicated-servers`

---

## 🔧 What You Need to Do

### Step 1: Configure WHMCS Hosting Products

#### Access WHMCS Admin Panel
1. Navigate to: `http://localhost:8000/adminIzende/admin/`
2. Log in with your WHMCS admin credentials

#### Create Product Groups (if not exist)
Go to: **Setup → Products/Services → Product Groups**

Create 3 groups with these **exact slugs**:
- Group 1: Name: "Shared Hosting", Slug: `shared-hosting`
- Group 2: Name: "VPS Hosting", Slug: `vps-hosting`
- Group 3: Name: "Dedicated Servers", Slug: `dedicated-servers`

> **Important:** The slugs must match exactly for the URLs from your main site to work!

#### Configure Products
Go to: **Setup → Products/Services → Products/Services**

**Product 1: Shared Hosting - $4.99/month**
- Product Type: Hosting Account
- Product Group: Shared Hosting
- Product Name: Shared Hosting - Starter
- Monthly Price: $4.99
- Annual Price: $59.88 (or your preferred discount)
- Description: Include these features:
  - 10GB SSD Storage
  - Unlimited Bandwidth
  - 1 Website
  - 10 Email Accounts
  - Free SSL Certificate
  - Free Domain (1 year)
  - cPanel Control Panel
  - 99.9% Uptime Guarantee
  - 24/7 Support

**Product 2: VPS Hosting - $29.99/month**
- Product Type: Hosting Account
- Product Group: VPS Hosting
- Product Name: VPS Hosting - Professional
- Monthly Price: $29.99
- Annual Price: $359.88 (or your preferred discount)
- Description: Include these features:
  - 50GB SSD Storage
  - Unlimited Bandwidth
  - 5 Websites
  - 50 Email Accounts
  - Free SSL Certificate
  - Free Domain (1 year)
  - cPanel/WHM Access
  - 2-4 CPU Cores
  - 2-8GB RAM
  - Root Access
  - Dedicated IP
  - Daily Backups
  - Priority Support

**Product 3: Dedicated Server - $99.99/month**
- Product Type: Dedicated/VPS Server
- Product Group: Dedicated Servers
- Product Name: Dedicated Server - Enterprise
- Monthly Price: $99.99
- Annual Price: $1,199.88 (or your preferred discount)
- Description: Include these features:
  - 500GB SSD Storage
  - Unlimited Bandwidth
  - Unlimited Websites
  - Unlimited Email Accounts
  - Free SSL Certificate
  - Free Domain (1 year)
  - Full Root Access
  - 8-16 CPU Cores
  - 16-64GB RAM
  - Multiple Dedicated IPs
  - Daily Backups
  - Managed Services Available
  - Priority Support
  - 99.99% Uptime SLA

### Step 2: Configure WHMCS Logo

#### Method A: Via Admin Panel (Recommended)
1. Go to: **Setup → General Settings → General**
2. Scroll to "Logo URL"
3. Enter: `/assets/img/izende-T.png`
4. Click "Save Changes"

#### Method B: Via Configuration File
1. Go to: **Setup → General Settings → General**
2. Look for "Company Logo" or "Logo" setting
3. Upload the logo file: `adminIzende/assets/img/izende-T.png`

### Step 3: Test WHMCS Pages

Visit these URLs to verify styling:
- Homepage: `http://localhost:8000/adminIzende/`
- Shared Hosting Store: `http://localhost:8000/adminIzende/index.php?rp=/store/shared-hosting`
- VPS Hosting Store: `http://localhost:8000/adminIzende/index.php?rp=/store/vps-hosting`
- Dedicated Servers Store: `http://localhost:8000/adminIzende/index.php?rp=/store/dedicated-servers`
- Cart: `http://localhost:8000/adminIzende/cart.php?a=view`
- Client Area: `http://localhost:8000/adminIzende/clientarea.php`

#### Checklist:
- [ ] Logo displays correctly in header
- [ ] Brand color (#5cb874) is consistent
- [ ] Buttons have hover effects (lift animation)
- [ ] Pricing tables match main site style
- [ ] "Main Site" button works in header
- [ ] Footer matches main site (dark background)
- [ ] Typography matches (Raleway for headings)
- [ ] Mobile responsive design works

---

## 📋 CSS Features Implemented

### Section Separators
Gradient line separators matching your main site:
```css
.section-separator {
  background: linear-gradient(90deg, rgba(92, 184, 116, 0), #5cb874, rgba(92, 184, 116, 0));
  height: 4px;
  width: 140px;
  margin: 80px auto 40px;
}
```

### Pricing Tables
Enhanced styling with:
- Hover effect (lifts 5px)
- Border highlight on hover
- Featured plan styling
- "Most Popular" badge
- Consistent spacing and shadows

### Button Animations
All buttons now have:
- Lift effect on hover (`translateY(-2px)`)
- Box shadow on hover
- Smooth transitions (0.3s)

### Icon Boxes
Service/feature boxes with:
- Hover lift effect
- Color-coded icons (brand, blue, orange, red, teal, yellow)
- Consistent padding and shadows

### Domain Search
Gradient background matching brand colors:
- Primary green to dark green gradient
- White input with rounded corners
- Dark button matching footer

### Footer Styling
Matching main site footer:
- Dark background (#090909)
- White text
- Social icons with hover effects
- Brand color on link hover

---

## 🎨 Brand Color Variables

The following CSS variables are defined in [custom.css](adminIzende/templates/twenty-one/css/custom.css):

```css
:root {
  --izende-brand: #5cb874;           /* Primary green */
  --izende-brand-hover: #6ec083;     /* Lighter green on hover */
  --izende-brand-dark: #449d5b;      /* Darker green for active states */
  --izende-brand-light: #e8f5eb;     /* Very light green for backgrounds */
  --izende-brand-lighter: #f4faf6;   /* Ultra light green */
}
```

---

## 📁 Files Modified/Created

### Modified Files:
1. **[adminIzende/templates/twenty-one/css/custom.css](adminIzende/templates/twenty-one/css/custom.css)**
   - Added 600+ lines of custom styling
   - Section separators, pricing tables, buttons, icons, etc.

### Created Files:
1. **[WHMCS-STYLING-GUIDE.md](WHMCS-STYLING-GUIDE.md)** (This file)
   - Complete documentation of changes

### Copied Files:
1. **[adminIzende/assets/img/izende-T.png](adminIzende/assets/img/izende-T.png)**
   - Logo file from main site

---

## 🔗 Integration Points

### Main Site → WHMCS
From your main site, users can navigate to WHMCS via:
- Hosting page "Get Started" buttons
- Navigation menu (if configured)
- Direct links to product pages

### WHMCS → Main Site
From WHMCS, users can return via:
- "Main Site" button in header
- Logo click (if configured)
- Footer links

---

## 🎯 Product Pricing Summary

Ensure these prices are configured in WHMCS:

| Product | Monthly Price | Annual Price* | Features |
|---------|---------------|---------------|----------|
| Shared Hosting | $4.99 | $59.88 | 10GB SSD, 1 Website, 10 Email |
| VPS Hosting | $29.99 | $359.88 | 50GB SSD, 5 Websites, Root Access |
| Dedicated Server | $99.99 | $1,199.88 | 500GB SSD, Unlimited Sites, Managed |

*Annual pricing can be adjusted with discounts

---

## 🚀 Next Steps

1. **Configure WHMCS Products** (15-20 minutes)
   - Create product groups
   - Add 3 hosting products
   - Set pricing and features

2. **Set Logo** (2 minutes)
   - Update WHMCS settings to use logo file

3. **Test All Pages** (10 minutes)
   - Browse through WHMCS store
   - Test cart and checkout flow
   - Verify mobile responsive design

4. **Optional Enhancements:**
   - Configure payment gateways
   - Set up email templates with brand colors
   - Add custom pages to WHMCS
   - Configure domain registrar integrations

---

## 🛠️ Troubleshooting

### Logo Not Showing
- Clear WHMCS template cache: `Utilities → System → Clear Template Cache`
- Check file permissions on logo file
- Verify logo path in admin settings

### Colors Not Applied
- Clear browser cache (Ctrl+F5)
- Verify custom.css is loaded (check browser dev tools)
- Check for CSS conflicts in browser console

### Store Pages Not Found
- Verify product group slugs match exactly:
  - `shared-hosting`
  - `vps-hosting`
  - `dedicated-servers`
- Check product group is set to "visible" in WHMCS

### Styling Looks Different on Mobile
- Custom CSS includes responsive breakpoints
- Test on actual mobile device, not just browser resize
- Check for JavaScript errors in mobile browser

---

## 📞 Support Resources

### WHMCS Documentation
- Product Setup: https://docs.whmcs.com/Products_and_Services
- Template Customization: https://docs.whmcs.com/Templates
- Logo Configuration: https://docs.whmcs.com/Customising_the_Client_Area

### Custom CSS Location
- File: `/adminIzende/templates/twenty-one/css/custom.css`
- Automatically loaded by WHMCS
- Changes take effect immediately (may require browser cache clear)

---

## ✨ Summary

Your WHMCS installation is now styled to match your main Izende Studio Web site with:

✅ Consistent brand colors (#5cb874 green)
✅ Matching typography (Raleway + Open Sans)
✅ Enhanced pricing tables with hover effects
✅ Section separators matching main site
✅ Button animations and transitions
✅ Dark footer matching main site
✅ Mobile responsive design
✅ Logo file ready to use
✅ Links from main site to WHMCS configured
✅ "Main Site" navigation button in WHMCS

**Remaining Tasks:**
- Configure WHMCS hosting products (3 products)
- Set logo in WHMCS admin settings
- Test all pages for consistency

**Estimated Time to Complete:** 30 minutes

---

**Generated:** October 24, 2025
**For:** Izende Studio Web
**By:** Claude Code Assistant

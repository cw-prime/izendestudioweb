# WordPress Customizer Settings Guide

## Overview
Step-by-step guide to configure WordPress Customizer settings for brand consistency across both onepress and izendetheme themes.

---

## Accessing the Customizer

1. Log in to WordPress admin: https://izendestudioweb.com/articles/wp-admin/
2. Navigate to: **Appearance > Customize**
3. Customizer will open with live preview

---

## Site Identity Settings

**Path:** Appearance > Customize > Site Identity

### Site Title
- **Setting:** Site Title
- **Value:** Izende Studio Web Blog
- **Display:** Show (checkbox checked)

### Tagline
- **Setting:** Tagline
- **Value:** Web Design & Hosting Insights from St. Louis
- **Display:** Show (checkbox checked)

### Logo Upload
- **Setting:** Logo
- **Action:** Click "Select logo"
- **Upload:** /assets/img/izende-T.png (from main site)
- **Recommended Size:** 300x100px
- **Crop:** No crop (use full logo)
- **Display:** Logo replaces site title

### Site Icon (Favicon)
- **Setting:** Site Icon
- **Upload:** Favicon image (512x512px minimum)
- **Format:** PNG or ICO
- **Displays:** Browser tab, bookmarks, mobile home screen

---

## Colors Settings

**Path:** Appearance > Customize > Colors

### Background Color (izendetheme)
- **Setting:** Background Color
- **Value:** #5cb874 OR #ffffff (white recommended for readability)
- **Note:** izendetheme supports custom background color

### Link Color (if available)
- **Setting:** Link Color
- **Value:** #5cb874
- **Note:** May not be available in all themes (set via CSS instead)

### Primary Color (onepress)
- **Setting:** Primary Color (if available in onepress Customizer)
- **Value:** #5cb874
- **Note:** onepress may have theme-specific color settings

---

## Typography Settings (if theme supports)

**Path:** Appearance > Customize > Typography (if available)

### Body Font
- **Font Family:** Open Sans
- **Font Weight:** 400 (regular)
- **Font Size:** 16px
- **Line Height:** 1.7

### Heading Font
- **Font Family:** Raleway
- **Font Weight:** 600 (semi-bold for H1-H3), 400 (regular for H4-H6)
- **Line Height:** 1.3

**Note:** If theme doesn't have typography settings in Customizer, fonts are controlled via CSS (already updated in style.css).

---

## Menus Settings

**Path:** Appearance > Customize > Menus

### Create Primary Menu
1. Click "Create New Menu"
2. Menu Name: "Primary Menu" or "Main Navigation"
3. Add menu items:
   - **Home** → Link to /articles/ (blog homepage)
   - **Categories** → Dropdown with category links
   - **About** → Link to /articles/about/ (if page exists)
   - **Contact** → Link to https://izendestudioweb.com/quote.php
   - **Main Site** → Link to https://izendestudioweb.com/
4. Assign to location: "Primary Menu"
5. Click "Publish"

### Create Footer Menu (optional)
1. Click "Create New Menu"
2. Menu Name: "Footer Menu"
3. Add menu items:
   - Legal links (Privacy, Terms, etc.)
   - Service links
4. Assign to location: "Footer Menu" (if theme supports)
5. Click "Publish"

**Note:** Legal links are hardcoded in footer.php template, so footer menu is optional for additional links.

---

## Homepage Settings

**Path:** Settings > Reading (not in Customizer)

### Posts Page
- **Setting:** Your homepage displays
- **Value:** Your latest posts (for blog homepage)
- **OR:** A static page (if using custom homepage)

### Posts per page
- **Setting:** Blog pages show at most
- **Value:** 9 posts (matches blog.php pagination)

### For each post in a feed, include
- **Setting:** Syndication feeds show
- **Value:** Summary (excerpt) - better for RSS readers

---

## onepress Theme-Specific Settings

**Path:** Appearance > Customize > OnePress Settings (if available)

### Hero Section
- **Disable:** Yes (if not using one-page layout for blog)
- **OR Configure:** Add hero image, title, CTA for blog homepage

### Sections
- **Disable unused sections:** Features, Services, About, Team, etc.
- **Enable:** News/Blog section for blog posts display

### Header Settings
- **Sticky Header:** Enable (keeps navigation visible on scroll)
- **Header Transparent:** Disable (for blog, solid header is better)
- **Vertical Align Menu:** Enable (centers menu items)

### Footer Settings
- **Footer Widgets:** Enable (4 widget areas available)
- **Back to Top Button:** Enable (smooth scroll to top)
- **Footer Copyright:** Customize text to "© 2025 Izende Studio Web. All rights reserved."

---

## izendetheme Theme-Specific Settings

**Path:** Appearance > Customize > Twenty Twenty-One Settings (if available)

### Background Color
- **Setting:** Background Color
- **Value:** #ffffff (white) OR #5cb874 (brand green)
- **Recommendation:** White for better readability

### Dark Mode
- **Setting:** Dark Mode Support
- **Value:** Disable (optional - keep if desired)
- **Note:** Dark mode may conflict with brand colors

### Content Width
- **Setting:** Content Width
- **Value:** 750px (default) OR 900px (wider for better readability)

---

## Widgets Configuration

**Path:** Appearance > Customize > Widgets

### Footer Widgets (onepress)
**Footer 1:**
- Add: Text widget with "About Izende Studio Web" content
- Include: Logo, brief description, social links

**Footer 2:**
- Add: Custom Menu widget with "Quick Links"
- Links: Services, Hosting, Blog, Contact

**Footer 3:**
- Add: Recent Posts widget
- Title: "Latest Articles"
- Number of posts: 5

**Footer 4:**
- Add: Text widget with "Contact Information"
- Include: Phone, email, address

### Sidebar (if using sidebar layout)
- Add: Search widget
- Add: Categories widget
- Add: Recent Posts widget
- Add: Tag Cloud widget
- Add: Text widget with newsletter signup (if implemented)

---

## Additional Configuration

### Permalinks
**Path:** Settings > Permalinks
- **Setting:** Post name
- **Format:** https://izendestudioweb.com/articles/post-name/
- **Benefit:** SEO-friendly URLs

### Discussion Settings
**Path:** Settings > Discussion
- **Enable:** Users must be registered to comment (reduces spam)
- **Enable:** Comment moderation (approve before publishing)
- **Enable:** Email notification for new comments

### Media Settings
**Path:** Settings > Media
- **Thumbnail size:** 300x300 (cropped)
- **Medium size:** 640x400 (cropped)
- **Large size:** 1024x640 (cropped)
- **Ensure:** Crop thumbnails to exact dimensions (checked)

---

## Theme Activation

### Activate onepress-child
1. Navigate to: Appearance > Themes
2. Find "OnePress Child - Izende Studio Web"
3. Click "Activate"
4. Verify customizations appear
5. Test all functionality

### Activate izendetheme
1. Navigate to: Appearance > Themes
2. Find "Izende Studio Web Theme" or "izendetheme"
3. Click "Activate"
4. Verify customizations appear
5. Test all functionality

### Switch Between Themes
- Both themes are customized and ready to use
- Can switch anytime via Appearance > Themes
- Customizer settings may need reconfiguration when switching
- Menus and widgets are theme-specific (may need reassignment)

---

## Troubleshooting

### Customizations Don't Appear
1. Clear WordPress cache (if caching plugin installed)
2. Clear browser cache (Ctrl+Shift+R)
3. Check if correct theme is active
4. Verify child theme files are in correct directory
5. Check file permissions (644 for files, 755 for directories)

### "Back to Main Site" Link Missing
1. Verify header.php exists in child theme directory
2. Check if template is being overridden correctly
3. Clear WordPress cache
4. Inspect page source to see if HTML is present but hidden by CSS

### Legal Links Missing
1. Verify footer.php exists in child theme directory
2. Check if template is being overridden correctly
3. Verify links are not hidden by CSS
4. Check browser console for JavaScript errors

### Colors Not Updating
1. Clear WordPress cache
2. Clear browser cache
3. Verify CSS variables are updated in style.css
4. Check for !important overrides blocking changes
5. Use browser DevTools to inspect computed styles

---

## Configuration Checklist

### Before Going Live
- [ ] Logo uploaded and displays correctly
- [ ] Site title and tagline configured
- [ ] Favicon uploaded
- [ ] Primary menu created and assigned
- [ ] Footer menu created (if needed)
- [ ] Background color set (white recommended)
- [ ] Permalink structure set to "Post name"
- [ ] Comments settings configured
- [ ] Widget areas populated
- [ ] Test all pages on mobile and desktop
- [ ] Verify all links work (header, footer, navigation)
- [ ] Test comment submission
- [ ] Check page load speed

### Monthly Maintenance
- [ ] Check for theme updates (onepress only)
- [ ] Check for WordPress core updates
- [ ] Check for plugin updates
- [ ] Test all functionality after updates
- [ ] Backup theme files before major updates
- [ ] Review and moderate comments
- [ ] Check for broken links
- [ ] Monitor site performance

---

**Configuration Completed:** [Date]
**Last Tested:** [Date]
**Next Review:** [Date + 3 months]

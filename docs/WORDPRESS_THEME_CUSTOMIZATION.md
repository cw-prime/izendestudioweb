# WordPress Theme Customization Guide

## Overview
This guide documents the customization of WordPress themes (onepress and izendetheme) to match the Izende Studio Web brand identity with #5cb874 color scheme and Open Sans/Raleway typography.

---

## Themes Overview

### onepress Theme (Child Theme Approach)
- **Type:** Commercial theme from FameThemes
- **Version:** 2.2.8
- **Approach:** Create child theme (onepress-child)
- **Status:** Already 80% branded (#5cb874 links, Raleway headings)
- **Updates:** Receives regular updates from FameThemes
- **Customization:** Child theme preserves customizations during updates

### izendetheme Theme (Direct Customization)
- **Type:** Custom theme based on Twenty Twenty-One
- **Version:** Custom
- **Approach:** Direct modification (already customized)
- **Status:** Needs color and typography updates
- **Updates:** No parent theme updates (standalone custom theme)
- **Customization:** Direct file modification appropriate

---

## Brand Standards Applied

### Colors
- **Primary Brand:** #5cb874 (green)
- **Hover State:** #6ec083 (lighter green)
- **Active State:** #449d5b (darker green)
- **Light Background:** #e8f5eb (very light green)
- **Lighter Background:** #f4faf6 (almost white green tint)

### Typography
- **Body Font:** Open Sans (400, 600, 700 weights)
- **Heading Font:** Raleway (300, 400, 600, 700 weights)
- **Monospace:** SFMono-Regular, Consolas, Monaco
- **Base Size:** 16px (desktop), 15px (tablet), 14px (mobile)
- **Line Height:** 1.7 (body), 1.3 (headings)

---

## onepress Child Theme Implementation

### Files Created
1. **style.css** - Child theme stylesheet with brand overrides
2. **functions.php** - Enqueue parent/child styles, add customizations
3. **header.php** - Override with "Back to Main Site" link
4. **footer.php** - Override with legal links and service area
5. **single.php** - Custom post template for better blog display

### Customizations Applied
- Added "Back to Main Site" link in header (above main navigation)
- Added legal links in footer (8 links to main site legal pages)
- Added service area information in footer
- Created enhanced single post template with:
  - Larger featured images
  - Better typography
  - Author bio section
  - Related posts section
  - Improved post meta display

### Parent Theme Preserved
- All parent theme files remain unchanged
- Child theme overrides only specific templates
- Parent theme updates won't affect customizations
- Can easily switch back to parent theme if needed

---

## izendetheme Direct Customization

### Files Modified
1. **style.css** - Updated CSS variables, added Raleway font, updated colors
2. **functions.php** - Updated editor color palette ($green variable)
3. **header.php** - Added "Back to Main Site" link
4. **footer.php** - Added legal links and service area
5. **single.php** - Existing template uses template-parts (preserved)

### CSS Variable Updates (style.css lines 106-117)
- `--global--color-green`: #d1e4dd → #5cb874
- `--global--font-primary`: Added "Raleway" as first font
- `--global--font-secondary`: Added "Open Sans" as first font
- Added new variables: `--global--color-brand`, `--global--color-brand-hover`, `--global--color-brand-dark`, `--global--color-brand-light`

### Color Palette Updates (functions.php)
- `$green` variable: #D1E4DD → #5cb874 (line 206)
- Default background color: d1e4dd → 5cb874 (line 198)
- Editor color palette now shows brand green

### Template Updates
- Header: Added "Back to Main Site" link above site-header template-part
- Footer: Added service area and legal links before site-info
- Single: Preserved existing template-part structure

---

## WordPress Customizer Configuration

### Logo Upload
1. Navigate to: Appearance > Customize > Site Identity
2. Upload Izende Studio Web logo (izende-T.png)
3. Recommended size: 300x100px
4. Set logo width: 200px (adjust as needed)

### Menu Assignment
1. Navigate to: Appearance > Customize > Menus
2. Create "Primary Menu" if not exists
3. Add menu items: Home, Services, Blog, About, Contact
4. Assign to "Primary Menu" location
5. Create "Footer Menu" for footer navigation (optional)

### Color Settings (izendetheme)
1. Navigate to: Appearance > Customize > Colors
2. Background Color: #5cb874 or #ffffff (white)
3. Link Color: #5cb874 (if customizer option available)
4. Heading Color: #333333 (dark gray)

### Typography Settings (if theme supports)
1. Body Font: Open Sans
2. Heading Font: Raleway
3. Font Size: 16px base
4. Line Height: 1.7

### Homepage Settings
1. Navigate to: Settings > Reading
2. Set "Your homepage displays": A static page
3. Select homepage: [Create "Blog" page]
4. Select posts page: [Create "Articles" page or use default]

---

## Custom Post Templates

### single.php Features

**Both Themes Include:**
- Enhanced featured image display (larger, better aspect ratio)
- Improved post meta (author, date, category, read time, comments)
- Better typography (larger font, better line-height)
- Author bio section (avatar, name, bio, social links)
- Related posts section (3 posts from same category)
- Social sharing buttons (Facebook, Twitter, LinkedIn)
- Styled tags (pills with #5cb874 background)
- Improved comments section

**Layout:**
- Wider content area (col-lg-8 instead of default)
- Sidebar (col-lg-4) if enabled
- Single column on mobile
- Responsive images and embeds

**Styling:**
- Use theme CSS classes where possible
- Add custom classes for new elements
- Ensure brand color (#5cb874) used for accents
- Mobile-responsive with proper breakpoints

---

## Testing Checklist

### Visual Testing
- [x] Both themes use #5cb874 for links, buttons, accents
- [x] Both themes use Open Sans for body text
- [x] Both themes use Raleway for headings
- [x] "Back to Main Site" link appears in both theme headers
- [x] Legal links appear in both theme footers
- [x] Service area text appears in both theme footers
- [ ] Logo displays correctly in both themes
- [ ] Navigation menus work in both themes
- [ ] Single post template displays correctly in both themes

### Functional Testing
- [x] "Back to Main Site" link navigates to https://izendestudioweb.com/
- [x] Legal links navigate to correct main site pages
- [ ] Comments work on single posts
- [ ] Related posts display correctly
- [ ] Author bio displays when author has description
- [ ] Social sharing buttons work (if implemented)
- [ ] WordPress admin/Customizer works without errors

### Responsive Testing
- [ ] Mobile (320px-767px): Single column, readable text, touch-friendly
- [ ] Tablet (768px-991px): Proper layout, sidebar positioning
- [ ] Desktop (992px+): Full layout, all elements visible
- [x] Legal links wrap properly on mobile
- [ ] Featured images maintain aspect ratio on all sizes

### Cross-Browser Testing
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Mobile Chrome (Android)

### Integration Testing
- [ ] Transition from main site blog.php to WordPress is seamless
- [x] Brand consistency maintained (colors, typography, spacing)
- [x] "Back to Main Site" returns to main site homepage
- [x] Legal links open correct pages
- [ ] No broken links or 404 errors
- [ ] WordPress REST API still works (for blog.php integration)

---

## Maintenance

### onepress Child Theme
**When parent theme updates:**
1. Backup child theme files
2. Update parent theme via WordPress admin
3. Test child theme functionality
4. Verify customizations still work
5. Update child theme version number in style.css

**When adding new customizations:**
1. Add CSS to child theme style.css
2. Add PHP to child theme functions.php
3. Override templates by copying to child theme directory
4. Test thoroughly
5. Document changes in this file

### izendetheme Theme
**When WordPress updates:**
1. Backup entire theme directory
2. Update WordPress core
3. Test theme functionality
4. Fix any compatibility issues
5. Update theme version in style.css header

**When adding new customizations:**
1. Update style.css CSS variables first (cascades globally)
2. Add custom CSS at end of file
3. Update functions.php for PHP customizations
4. Override templates as needed
5. Test thoroughly
6. Document changes

### Color Changes
**If brand color changes:**
1. **onepress-child:** Update CSS variables in style.css
2. **izendetheme:** Update CSS variables in style.css (lines 116-117)
3. **izendetheme:** Update $green in functions.php (line 206)
4. Test all pages for visual consistency
5. Update main site simultaneously

---

## Rollback Procedures

### onepress Child Theme Rollback
**Quick rollback:**
1. Activate parent onepress theme via Appearance > Themes
2. Child theme customizations are hidden but preserved
3. Can reactivate child theme anytime

**Full rollback:**
1. Delete onepress-child directory
2. Activate parent onepress theme
3. Reconfigure Customizer settings if needed

### izendetheme Rollback
**Requires backup:**
1. Restore files from backup (created before customization)
2. Replace modified files:
   - style.css
   - functions.php
   - header.php
   - footer.php
3. Test WordPress functionality
4. Reconfigure Customizer if needed

---

## Active Theme Determination

**To check which theme is currently active:**
1. Log in to WordPress admin at /articles/wp-admin/
2. Navigate to: Appearance > Themes
3. Active theme will have "Active" badge
4. Likely: onepress or izendetheme

**Recommendation:**
- If onepress is active: Activate onepress-child after creation
- If izendetheme is active: Keep active, customizations applied directly
- If other theme active: Activate onepress-child or izendetheme after customization

---

## Support Resources

- **WordPress Theme Development:** https://developer.wordpress.org/themes/
- **Child Themes:** https://developer.wordpress.org/themes/advanced-topics/child-themes/
- **Template Hierarchy:** https://developer.wordpress.org/themes/basics/template-hierarchy/
- **Customizer API:** https://developer.wordpress.org/themes/customize-api/
- **onepress Documentation:** https://www.famethemes.com/themes/onepress/
- **Twenty Twenty-One:** https://wordpress.org/themes/twentytwentyone/

---

**Last Updated:** 2025-10-15
**Version:** 1.0
**Customized By:** Claude Code

# WHMCS Template Customization Guide

## Overview
This guide documents the customization of the WHMCS Twenty-One template to match the Izende Studio Web brand identity with #5cb874 color scheme and Open Sans/Raleway typography.

---

## Customization Summary

### Files Modified
1. `includes/head.tpl` - Added Raleway font import
2. `header.tpl` - Added "Back to Main Site" link, updated branding
3. `footer.tpl` - Added legal links and service area information
4. `homepage.tpl` - Updated card accent colors to brand green
5. `clientareahome.tpl` - Updated tile highlight colors to brand green
6. `store/order.tpl` - Verified button colors (auto-updated via CSS)
7. `css/theme.css` - Updated root variables, colors, typography
8. `css/store.css` - Updated order form and landing page colors
9. `css/custom.css` - Created brand-specific overrides (NEW FILE)

### Brand Colors Applied
- **Primary:** #5cb874 (Izende brand green)
- **Hover:** #6ec083 (lighter green)
- **Active:** #449d5b (darker green)
- **Light:** #e8f5eb (very light green for backgrounds)

### Typography Applied
- **Body:** Open Sans (already in WHMCS)
- **Headings:** Raleway (added to match main site)
- **Monospace:** SFMono-Regular, Consolas (unchanged)

---

## Color Migration Map

### Root Variables (theme.css lines 14-48)
| Variable | Old Value | New Value |
|----------|-----------|----------|
| --primary | #336699 | #5cb874 |
| --green | #28a745 | #5cb874 |
| --success | #28a745 | #5cb874 |

### Card Accents (theme.css - applied globally)
| Class | Old Color | New Color | Usage |
|-------|-----------|-----------|-------|
| .card-accent-green | #5cb85c | #5cb874 | Primary brand |
| .card-accent-teal | #00aba9 | #5cb874 | Unified brand |
| .card-accent-turquoise | #1abc9c | #5cb874 | Unified brand |
| .card-accent-emerald | #2ecc71 | #5cb874 | Unified brand |
| .card-accent-blue | #5bc0de | #5cb874 | Unified brand |
| .card-accent-red | #d9534f | #d9534f | Keep (errors) |
| .card-accent-gold | #f0ad4e | #f0ad4e | Keep (warnings) |
| .card-accent-orange | #ff6600 | #ff6600 | Keep (alerts) |

### Status Colors (theme.css - applied globally)
| Status | Old Color | New Color |
|--------|-----------|----------|
| .status-active | #5cb85c | #5cb874 |
| .status-paid | #5cb85c | #5cb874 |
| .status-accepted | #5cb85c | #5cb874 |
| .requestor-type-owner | #5cb85c | #5cb874 |

---

## Template Modifications

### Header (header.tpl)

**Added (line 90-92):**
- "Back to Main Site" link after logo
- Link URL: https://izendestudioweb.com/
- Icon: Font Awesome fa-arrow-left
- Class: btn btn-sm btn-outline-primary

**Code:**
```html
<a href="https://izendestudioweb.com/" class="btn btn-sm btn-outline-primary ml-3" aria-label="Return to main website">
    <i class="fas fa-arrow-left"></i> Main Site
</a>
```

### Footer (footer.tpl)

**Added Service Area Information (lines 47-50):**
- Text: "Serving St. Louis Metro, Missouri & Illinois"
- Icon: Font Awesome fa-map-marker-alt
- Styling: White text, 14px font size

**Added Legal Links Section (lines 52-62):**
- Privacy Policy
- Terms of Service
- Cookie Policy
- Refund Policy
- SLA
- Accessibility
- Do Not Sell or Share
- All links point to main site legal pages
- Layout: Flexbox with separators
- Font size: 13px

### Homepage (homepage.tpl)

**Updated Action Icon Button Accents (lines 54, 62, 70, 78, 86):**
- Announcements: card-accent-teal → card-accent-green
- Server Status: card-accent-pomegranate → card-accent-red (kept for distinction)
- Knowledgebase: card-accent-sun-flower → card-accent-green
- Downloads: card-accent-asbestos → card-accent-green
- Submit Ticket: card-accent-green (unchanged)

**Updated "Your Account" Section Accents (lines 99, 107, 116, 125, 133):**
- All changed from card-accent-midnight-blue to card-accent-green

### Client Area Home (clientareahome.tpl)

**Updated Tile Highlight Colors (lines 10, 19, 28, 46, 54):**
- Services: bg-color-blue → bg-color-green
- Domains: bg-color-green (kept, updated to exact #5cb874)
- Affiliates/Quotes: bg-color-green (kept)
- Tickets: bg-color-red (kept for distinction)
- Invoices: bg-color-gold → bg-color-green

---

## CSS Modifications

### theme.css Updates

**Root Variables (lines 14-48):**
- Updated --primary to #5cb874
- Updated --green to #5cb874
- Updated --success to #5cb874
- Added --font-family-headings with Raleway
- Added brand color variations (--brand-primary, --brand-hover, --brand-dark, --brand-light)

**Global Color Replacements:**
- All instances of #336699 → #5cb874 (primary blue to brand green)
- All instances of #5cb85c → #5cb874 (old green to exact brand green)
- All instances of #28a745 → #5cb874 (Bootstrap green to brand green)

### store.css Updates

**Global Find & Replace:**
- #336699 → #5cb874 (primary color)
- #5cb85c → #5cb874 (old green)
- #28a745 → #5cb874 (Bootstrap green)
- #204060 → #449d5b (hover/dark state)

**Sections Updated:**
- Button styles (primary, success)
- Tab active states
- Pricing table highlights
- Feature list checkmarks
- Form validation success states

### custom.css (NEW FILE)

**Contents:**
- Brand color CSS variables
- Typography overrides (Raleway for headings)
- Header customizations (back link, logo, navbar)
- Footer customizations (legal links, service area)
- Button overrides (ensure brand color)
- Form focus states (brand color)
- Card accent overrides
- Link color overrides
- Pagination, tabs, badges, status indicators
- Mobile responsive adjustments
- Accessibility enhancements
- Print styles

**Size:** ~450 lines

---

## Testing Checklist

### Visual Testing
- [x] Header displays "Back to Main Site" link
- [x] Logo matches main site styling
- [x] All primary buttons use #5cb874 background
- [x] Card accents use #5cb874 border
- [x] Footer includes legal links and service area
- [x] Typography uses Open Sans (body) and Raleway (headings)
- [ ] Hover states use #6ec083 (lighter brand)
- [ ] Active states use #449d5b (darker brand)
- [ ] Form focus states use #5cb874 border
- [ ] Links use #5cb874 color

### Functional Testing
- [ ] Navigation works (all links functional)
- [ ] Shopping cart functions properly
- [ ] Order forms submit correctly
- [ ] Domain validation works
- [ ] Client area login/logout works
- [ ] Invoice viewing/payment works
- [ ] Support ticket submission works
- [ ] Product ordering works end-to-end

### Responsive Testing
- [ ] Mobile navigation works (hamburger menu)
- [ ] Tablets display correctly (768px-991px)
- [ ] Desktop displays correctly (992px+)
- [ ] Footer legal links wrap properly on mobile
- [ ] Order forms are mobile-friendly
- [ ] All buttons are touch-friendly (44x44px minimum)

### Cross-Browser Testing
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Mobile Chrome (Android)

### Integration Testing
- [ ] Transition from main site to WHMCS is seamless
- [ ] "Back to Main Site" link returns to homepage
- [ ] Legal links open correct pages on main site
- [ ] Brand consistency maintained throughout user journey
- [ ] No broken links or 404 errors

---

## Maintenance Notes

### WHMCS Updates
When updating WHMCS:
1. Backup custom.css before update
2. Update WHMCS via admin panel
3. Verify custom.css still loads (check head.tpl)
4. Test all customizations still work
5. Re-apply any lost customizations

### Theme Updates
If WHMCS updates the Twenty-One template:
1. Backup all modified files (header.tpl, footer.tpl, homepage.tpl, clientareahome.tpl)
2. Backup custom.css
3. Update template via WHMCS
4. Re-apply customizations from backups
5. Test thoroughly

### Color Changes
If brand color changes in future:
1. Update CSS variables in custom.css :root section
2. Update --primary, --green, --success in theme.css :root
3. All brand colors will update automatically
4. Test all pages for visual consistency
5. Update main site simultaneously

### Adding New Legal Pages
If new legal pages added to main site:
1. Add link to footer.tpl legal links section (lines 52-62)
2. Use same format as existing links
3. Test link functionality

---

## Rollback Procedure

If customizations cause issues:

### Quick Rollback (custom.css only):
- Rename custom.css to custom.css.bak
- WHMCS will revert to default theme styling
- Customizations in .tpl files remain

### Full Rollback:
- Restore original files from backup:
  - header.tpl
  - footer.tpl
  - homepage.tpl
  - clientareahome.tpl
  - theme.css
  - store.css
- Delete custom.css
- Clear WHMCS template cache: Utilities > System > Template Cache

### Partial Rollback:
- Restore only problematic files
- Keep working customizations
- Test incrementally

---

## File Locations

```
/var/www/html/izendestudioweb/adminIzende/templates/twenty-one/
├── includes/
│   └── head.tpl (modified)
├── header.tpl (modified)
├── footer.tpl (modified)
├── homepage.tpl (modified)
├── clientareahome.tpl (modified)
├── css/
│   ├── theme.css (modified)
│   ├── store.css (modified)
│   └── custom.css (NEW)
└── store/
    └── order.tpl (no changes needed)
```

---

## Support Resources

- **WHMCS Documentation:** https://docs.whmcs.com/
- **Template Customization:** https://docs.whmcs.com/Customising_the_Client_Area
- **Smarty Documentation:** https://www.smarty.net/docs/en/
- **Bootstrap 4 Documentation:** https://getbootstrap.com/docs/4.5/
- **Font Awesome Icons:** https://fontawesome.com/icons

---

## Changelog

### Version 1.0 - 2025-01-15
- Initial customization implementation
- Added brand color scheme #5cb874
- Added Raleway font for headings
- Added "Back to Main Site" link
- Added footer legal links and service area
- Updated all primary colors throughout template
- Created comprehensive custom.css file

---

**Last Updated:** 2025-01-15
**Version:** 1.0
**Customized For:** Izende Studio Web

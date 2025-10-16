# Accessibility Testing Guide

This guide provides comprehensive testing procedures to ensure the Izende Studio Web website meets WCAG 2.1 Level AA standards.

## Accessibility Targets

### WCAG 2.1 Level AA Compliance
- **Perceivable**: All content must be perceivable to all users
- **Operable**: Interface must be operable by all users
- **Understandable**: Content and interface must be understandable
- **Robust**: Content must work with current and future technologies

### Target Metrics
- **axe DevTools Score**: 0 violations
- **WAVE Errors**: 0 errors
- **Lighthouse Accessibility Score**: 95+
- **Keyboard Navigation**: 100% operable
- **Screen Reader Compatibility**: Full content access

## Implemented Accessibility Features

### 1. Keyboard Navigation
- **Skip Links**: Jump to main content, navigation, footer (index.php:148-150)
- **Focus Visible**: Clear focus indicators on all interactive elements
- **Tab Order**: Logical flow through all interactive elements
- **Keyboard Shortcuts**: '/' focuses search field

### 2. ARIA Landmarks & Labels
- **role="main"**: Main content area properly marked (index.php:233)
- **aria-label**: All icon-only buttons labeled
- **aria-pressed**: Portfolio filters toggle state (index.php:532-537)
- **aria-live="polite"**: Hero carousel announces changes (index.php:161)

### 3. Semantic HTML
- **Proper Heading Hierarchy**: h1 → h2 → h3 without skips
- **Button vs Link**: Semantic distinction maintained
- **Form Labels**: All inputs properly labeled with for/id
- **Alt Text**: Descriptive alt attributes on all images

### 4. Color & Contrast
- **Minimum Contrast Ratio**: 4.5:1 for normal text, 3:1 for large text
- **Non-Color Indicators**: State changes not communicated by color alone
- **Focus Indicators**: Visible 3px solid outline with 2px offset

### 5. Screen Reader Support
- **ARIA Live Regions**: Dynamic content changes announced
- **Hidden Content**: aria-hidden for decorative elements
- **Form Validation**: Error messages associated with inputs
- **Image Alternatives**: Meaningful alt text, empty alt for decorative images

## Accessibility Testing Checklist

### Automated Testing
- [ ] Run axe DevTools browser extension
- [ ] Run WAVE Web Accessibility Evaluation Tool
- [ ] Run Lighthouse accessibility audit
- [ ] Validate HTML with W3C validator
- [ ] Check color contrast with WebAIM tool

### Keyboard Testing
- [ ] Tab through entire page (logical order)
- [ ] Shift+Tab reverse navigation works
- [ ] Enter/Space activates buttons and links
- [ ] Arrow keys navigate carousel
- [ ] Escape closes modals and dropdowns
- [ ] Skip links are visible on focus
- [ ] No keyboard traps

### Screen Reader Testing

#### NVDA (Windows - Free)
```
Test Scenarios:
1. Navigate page structure (headings: H)
2. List all links (Insert+F7)
3. List all form fields (Insert+F5)
4. Read from top to bottom (Down arrow)
5. Test carousel announcements
6. Verify form validation messages
```

#### JAWS (Windows - Commercial)
```
Test Scenarios:
1. Navigate landmarks (R)
2. List all buttons (Insert+Ctrl+B)
3. Forms mode (Enter on form field)
4. Virtual cursor mode (Numpad Plus)
5. Quick navigation keys
```

#### VoiceOver (Mac/iOS - Built-in)
```
Test Scenarios:
1. Activate VoiceOver (Cmd+F5)
2. Navigate with VO+Right Arrow
3. Web rotor (VO+U)
4. Quick nav (Left/Right arrows)
5. Mobile gestures (two-finger swipe)
```

### Manual Testing Procedures

#### 1. Keyboard-Only Navigation
```
Steps:
1. Disable/hide mouse
2. Start at top of page
3. Tab through all interactive elements
4. Verify focus indicator visibility
5. Activate all controls with Enter/Space
6. Navigate carousel with arrow keys
7. Test form submission
```

#### 2. Zoom & Text Resize
```
Steps:
1. Zoom to 200% (Ctrl/Cmd ++)
2. Verify no horizontal scroll
3. Verify all text readable
4. Test all functionality
5. Zoom to 400%
6. Verify content reflows properly
```

#### 3. Color Contrast
```
Tools:
- WebAIM Contrast Checker
- Chrome DevTools Accessibility panel
- Stark plugin (Figma/Chrome)

Check:
- Text on backgrounds
- Icon colors
- Button states
- Form placeholders
- Error messages
```

#### 4. Form Accessibility
```
Test Points:
- Labels associated with inputs (for/id)
- Error messages announced
- Required fields indicated
- Fieldset/legend for groups
- Autocomplete attributes
- aria-describedby for hints
```

## Common Issues & Fixes

### Issue: Missing Alt Text
**Problem**: Images without alt attributes
**Fix**: Add descriptive alt text or alt="" for decorative images
```html
<!-- Good -->
<img src="logo.jpg" alt="Izende Studio Web Logo">
<img src="decoration.jpg" alt="" aria-hidden="true">
```

### Issue: Low Contrast
**Problem**: Text fails 4.5:1 contrast ratio
**Fix**: Darken text or lighten background
```css
/* Before: 3.2:1 */
color: #888;

/* After: 4.6:1 */
color: #666;
```

### Issue: Missing Focus Indicator
**Problem**: Can't see which element has focus
**Fix**: Add visible outline
```css
:focus-visible {
  outline: 3px solid #5cb874;
  outline-offset: 2px;
}
```

### Issue: Keyboard Trap
**Problem**: Can't tab out of modal/dropdown
**Fix**: Implement focus trap with Escape key exit
```javascript
// See main.js CookieConsent._handleTrap() for implementation
```

## Testing Tools & Resources

### Browser Extensions
- **axe DevTools** (Chrome, Firefox, Edge)
- **WAVE** (Chrome, Firefox, Edge)
- **Lighthouse** (Chrome DevTools)
- **HeadingsMap** (Chrome, Firefox)
- **Accessibility Insights** (Chrome, Edge)

### Screen Readers
- **NVDA** (Windows - Free): https://www.nvaccess.org/
- **JAWS** (Windows - Trial): https://www.freedomscientific.com/
- **VoiceOver** (Mac/iOS - Built-in)
- **TalkBack** (Android - Built-in)

### Online Tools
- **WebAIM Contrast Checker**: https://webaim.org/resources/contrastchecker/
- **WAVE**: https://wave.webaim.org/
- **axe DevTools**: https://www.deque.com/axe/devtools/
- **HTML Validator**: https://validator.w3.org/

## Compliance Checklist

### WCAG 2.1 Level A
- [ ] 1.1.1 Non-text Content
- [ ] 1.3.1 Info and Relationships
- [ ] 1.4.1 Use of Color
- [ ] 2.1.1 Keyboard
- [ ] 2.1.2 No Keyboard Trap
- [ ] 2.4.1 Bypass Blocks
- [ ] 2.4.2 Page Titled
- [ ] 3.1.1 Language of Page
- [ ] 4.1.1 Parsing
- [ ] 4.1.2 Name, Role, Value

### WCAG 2.1 Level AA (Additional)
- [ ] 1.4.3 Contrast (Minimum)
- [ ] 1.4.5 Images of Text
- [ ] 2.4.6 Headings and Labels
- [ ] 2.4.7 Focus Visible
- [ ] 3.2.3 Consistent Navigation
- [ ] 3.2.4 Consistent Identification
- [ ] 3.3.3 Error Suggestion
- [ ] 3.3.4 Error Prevention

## Reporting & Documentation

### Issue Template
```markdown
## Accessibility Issue

**WCAG Criterion**: [e.g., 2.4.7 Focus Visible]
**Severity**: [Critical/High/Medium/Low]
**Location**: [Page URL + element]
**Description**: [What's wrong]
**Impact**: [Who it affects]
**Fix**: [How to resolve]
**Status**: [Open/In Progress/Fixed]
```

### Test Report Template
```markdown
## Accessibility Test Report

**Date**: YYYY-MM-DD
**Tester**: [Name]
**Tools Used**: [List tools]

### Summary
- Issues Found: X
- Critical: X
- High: X
- Medium: X
- Low: X

### Details
[List each issue with template above]

### Recommendations
[Priority fixes and improvements]
```

---

Last Updated: 2025-10-16

# Dark Mode Implementation Guide

This guide documents the dark mode feature implementation for the Izende Studio Web website.

## Overview

Dark mode provides a darker color scheme that reduces eye strain in low-light environments and can save battery on OLED screens. The implementation uses CSS custom properties for instant theme switching with user preference persistence.

## Implementation Architecture

### 1. CSS Variables (style.css:3433-3461)

```css
/* Light mode (default) */
:root {
  --bg-primary: #ffffff;
  --bg-surface: #f8f9fa;
  --bg-elevated: #ffffff;
  --text-primary: #333333;
  --text-secondary: #666666;
  --text-disabled: #999999;
  --brand-primary: #5cb874;
  --brand-hover: #6ec083;
  --brand-dark: #449d5b;
  --border-color: rgba(0,0,0,0.1);
  --shadow: rgba(0,0,0,0.1);
}

/* Dark mode */
[data-theme="dark"] {
  --bg-primary: #1a1a1a;
  --bg-surface: #2d2d2d;
  --bg-elevated: #3a3a3a;
  --text-primary: #e0e0e0;
  --text-secondary: #b0b0b0;
  --text-disabled: #666666;
  --brand-primary: #6ec083;
  --brand-hover: #7dd194;
  --brand-dark: #5cb874;
  --border-color: rgba(255,255,255,0.1);
  --shadow: rgba(0,0,0,0.5);
}
```

### 2. Toggle Button (header.php:33-35)

```html
<button id="dark-mode-toggle" class="dark-mode-toggle"
        aria-label="Toggle dark mode"
        title="Toggle dark mode">
  <i class="bx bx-moon"></i>
</button>
```

### 3. JavaScript Controller (main.js:1547-1605)

```javascript
class DarkMode {
  constructor() {
    this.key = 'izende_theme_preference';
    this.toggle = document.getElementById('dark-mode-toggle');
    if (!this.toggle) return;
    this.init();
  }

  init() {
    // Check saved preference or system preference
    const savedTheme = localStorage.getItem(this.key);
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const theme = savedTheme || (prefersDark ? 'dark' : 'light');

    this.applyTheme(theme);

    // Toggle button click
    this.toggle.addEventListener('click', () => this.toggleTheme());

    // Listen for system preference changes
    window.matchMedia('(prefers-color-scheme: dark)')
      .addEventListener('change', (e) => {
        if (!localStorage.getItem(this.key)) {
          this.applyTheme(e.matches ? 'dark' : 'light');
        }
      });
  }

  toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    this.setTheme(newTheme);
  }

  setTheme(theme) {
    document.body.classList.add('theme-transition');
    this.applyTheme(theme);
    localStorage.setItem(this.key, theme);

    setTimeout(() => {
      document.body.classList.remove('theme-transition');
    }, 300);
  }

  applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    if (this.toggle) {
      this.toggle.innerHTML = theme === 'dark'
        ? '<i class="bx bx-sun"></i>'
        : '<i class="bx bx-moon"></i>';
      this.toggle.setAttribute('aria-label',
        `Switch to ${theme === 'dark' ? 'light' : 'dark'} mode`);
    }
  }
}
```

## Configuration

### Enable/Disable Dark Mode

To enable dark mode throughout the site:

1. **Add Toggle Button**: Already implemented in `header.php`
2. **Initialize JavaScript**: Already initialized in `main.js:1861`
3. **Apply CSS Variables**: Update components to use CSS variables

### CSS Variable Usage

Replace hardcoded colors with CSS variables:

```css
/* Before */
.element {
  color: #333;
  background: #fff;
}

/* After */
.element {
  color: var(--text-primary);
  background: var(--bg-primary);
}
```

## Component Updates

### Priority Components for Variable Conversion

#### High Priority
- [ ] Header/Navigation
- [ ] Hero Section
- [ ] Main Content Area
- [ ] Footer
- [ ] Buttons (primary, secondary)

#### Medium Priority
- [ ] Cards (portfolio, blog)
- [ ] Forms & Inputs
- [ ] Modal Dialogs
- [ ] Dropdown Menus

#### Low Priority
- [ ] Icon colors
- [ ] Borders & Dividers
- [ ] Subtle backgrounds

### Example Conversions

#### Header
```css
/* Before */
#header {
  background: #fff;
  color: #333;
}

/* After */
#header {
  background: var(--bg-primary);
  color: var(--text-primary);
}
```

#### Buttons
```css
/* Before */
.btn-primary {
  background: #5cb874;
  color: #fff;
}

/* After */
.btn-primary {
  background: var(--brand-primary);
  color: var(--bg-primary);
}
```

#### Cards
```css
/* Before */
.card {
  background: #f8f9fa;
  border: 1px solid rgba(0,0,0,0.1);
  color: #333;
}

/* After */
.card {
  background: var(--bg-surface);
  border: 1px solid var(--border-color);
  color: var(--text-primary);
}
```

## Testing Checklist

### Visual Testing
- [ ] Header/Footer in both themes
- [ ] All button states (normal, hover, active)
- [ ] Form inputs (focus, disabled, error)
- [ ] Cards and containers
- [ ] Text hierarchy (h1-h6, body, captions)
- [ ] Images and media
- [ ] Shadows and borders

### Functional Testing
- [ ] Toggle button switches themes
- [ ] Theme persists across page loads
- [ ] System preference respected when no saved preference
- [ ] Smooth transition between themes
- [ ] No FOUC (Flash of Unstyled Content)
- [ ] Accessible in both themes

### Contrast Testing
- [ ] Run WebAIM Contrast Checker on both themes
- [ ] Text meets 4.5:1 ratio (normal), 3:1 (large)
- [ ] Interactive elements meet 3:1 ratio
- [ ] Focus indicators visible in both themes

## User Preferences

### Storage
- **Key**: `izende_theme_preference`
- **Values**: `'light'` or `'dark'`
- **Location**: `localStorage`
- **Expiry**: Never (until cleared by user)

### System Preference Detection
```javascript
// Detects OS/browser preference
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
```

### Preference Hierarchy
1. **User Selection** (localStorage)
2. **System Preference** (prefers-color-scheme)
3. **Default** (light mode)

## Advanced Features

### Smooth Transitions
```css
.theme-transition {
  transition: background-color 0.3s ease,
              color 0.3s ease,
              border-color 0.3s ease;
}
```

### Per-Component Override
```css
/* Override dark mode for specific component */
[data-theme="dark"] .keep-light {
  --text-primary: #333333;
  --bg-primary: #ffffff;
}
```

### Image Adjustments
```css
/* Reduce brightness of images in dark mode */
[data-theme="dark"] img:not(.logo) {
  filter: brightness(0.9);
}
```

## Troubleshooting

### Issue: Flash of Wrong Theme on Load
**Cause**: Theme applied after page render
**Fix**: Add inline script in `<head>` to set theme before render
```html
<script>
(function() {
  const theme = localStorage.getItem('izende_theme_preference');
  if (theme) {
    document.documentElement.setAttribute('data-theme', theme);
  }
})();
</script>
```

### Issue: Some Colors Not Switching
**Cause**: Hardcoded colors instead of CSS variables
**Fix**: Replace with CSS variables
```css
/* Find all hardcoded colors */
grep -r "#[0-9a-fA-F]\{6\}" assets/css/

/* Replace with variables */
color: var(--text-primary);
```

### Issue: Poor Contrast in Dark Mode
**Cause**: Light mode colors used in dark mode
**Fix**: Adjust variable values or add specific overrides
```css
[data-theme="dark"] .element {
  /* Lighten text for better contrast */
  --text-secondary: #c0c0c0;
}
```

### Issue: Icons Not Visible
**Cause**: Icon colors not updated
**Fix**: Use currentColor or variables
```css
.icon {
  color: var(--text-secondary);
  /* or */
  fill: currentColor;
}
```

## Best Practices

### 1. Use Semantic Variable Names
```css
/* Good */
--text-primary
--bg-surface
--border-color

/* Avoid */
--color-1
--dark-gray
--light-background
```

### 2. Test in Both Modes Early
Don't wait until the end to test dark mode. Test components as you build them.

### 3. Respect User Preference
Always check for saved preference first, then system preference, then default.

### 4. Provide Visual Feedback
Animate the theme transition and update the toggle icon to show current state.

### 5. Consider Images
Some images may look odd in dark mode. Consider:
- Reducing brightness
- Providing dark mode variants
- Adding subtle borders

## Accessibility Considerations

### Focus Indicators
Ensure focus indicators are visible in both themes:
```css
:focus-visible {
  outline: 3px solid var(--brand-primary);
  outline-offset: 2px;
}
```

### Contrast Requirements
- Normal text: 4.5:1 minimum
- Large text: 3:1 minimum
- Interactive elements: 3:1 minimum

### ARIA Labels
Toggle button must indicate current state:
```html
<button aria-label="Switch to dark mode" aria-pressed="false">
```

## Performance

### CSS Custom Properties Performance
- ✅ Near-instant switching (no repaint needed)
- ✅ Minimal CSS footprint (one set of variables)
- ✅ No JavaScript computation on each element

### Storage Performance
- ✅ `localStorage` is synchronous and fast
- ✅ No server round-trip required
- ✅ Persists across sessions

---

Last Updated: 2025-10-16

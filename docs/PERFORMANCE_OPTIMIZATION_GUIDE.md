# Performance Optimization Guide

This guide provides comprehensive strategies for optimizing the performance of the Izende Studio Web website.

## Performance Targets

### Core Web Vitals
- **LCP (Largest Contentful Paint)**: < 2.5s
- **FID (First Input Delay)**: < 100ms
- **CLS (Cumulative Layout Shift)**: < 0.1

### Page Speed Targets
- **Time to First Byte (TTFB)**: < 600ms
- **First Contentful Paint (FCP)**: < 1.8s
- **Time to Interactive (TTI)**: < 3.9s
- **Total Page Size**: < 1.5MB
- **Total HTTP Requests**: < 50

## Implemented Optimizations

### 1. Image Optimization
- **WebP Format**: All images use `<picture>` elements with WebP first, JPEG fallback
- **Lazy Loading**: Below-the-fold images use `loading="lazy" decoding="async"`
- **Responsive Images**: Explicit width/height prevent layout shift
- **Hero Images**: Above-the-fold use `loading="eager"` for immediate display

### 2. CSS Optimization
- **Critical CSS Preloading**: Main stylesheet preloaded with `rel="preload"`
- **Font Preloading**: Google Fonts preloaded to reduce render-blocking
- **CSS Variables**: Dark mode uses CSS custom properties for instant theme switching
- **Reduced Animations**: Respects `prefers-reduced-motion` for accessibility

### 3. JavaScript Optimization
- **Deferred Loading**: All scripts use `defer` attribute
- **Code Splitting**: Functionality modularized into separate concerns
- **Event Delegation**: Minimizes event listeners
- **Intersection Observer**: Efficient scroll-based animations

### 4. Resource Hints
- **DNS Prefetch**: Google services pre-resolved
- **Preconnect**: Font origins connected early
- **Preload**: Critical CSS and fonts loaded with priority

## Optimization Checklist

### Before Deployment
- [ ] Run Lighthouse audit (target score: 90+)
- [ ] Test on 3G connection (< 5s load time)
- [ ] Verify all images have WebP variants
- [ ] Check bundle size (JS < 300KB, CSS < 150KB)
- [ ] Validate Core Web Vitals in Chrome DevTools

### Image Optimization Steps
1. Convert all JPEGs to WebP using `cwebp` tool
2. Add explicit dimensions to prevent CLS
3. Use appropriate `srcset` for responsive delivery
4. Compress images (JPEG quality: 80-85, WebP quality: 75-80)
5. Implement lazy loading for below-fold content

### CSS Best Practices
- Minimize render-blocking CSS
- Use CSS containment (`contain` property) for isolated components
- Avoid `@import` statements
- Inline critical above-the-fold CSS if needed
- Remove unused CSS with PurgeCSS

### JavaScript Best Practices
- Minimize main thread work
- Use `requestAnimationFrame` for animations
- Debounce scroll/resize handlers
- Lazy-load non-critical functionality
- Tree-shake unused dependencies

## Performance Monitoring

### Tools
- **Google Lighthouse**: Overall performance score
- **WebPageTest**: Real-world performance testing
- **Chrome DevTools**: Core Web Vitals tracking
- **GTmetrix**: Detailed performance analysis

### Key Metrics to Track
```javascript
// Implemented in main.js PerformanceMonitor
- LCP (Largest Contentful Paint)
- FID (First Input Delay)
- CLS (Cumulative Layout Shift)
- TTFB (Time to First Byte)
```

## Testing Instructions

### Local Testing
```bash
# 1. Start local server
php -S localhost:8000

# 2. Run Lighthouse
lighthouse http://localhost:8000 --view

# 3. Check bundle sizes
du -sh assets/js/*.js
du -sh assets/css/*.css
```

### Production Testing
1. Test on real devices (mobile & desktop)
2. Use throttled connections (Fast 3G, Slow 4G)
3. Monitor RUM (Real User Monitoring) data
4. Check CDN cache hit rates
5. Verify image compression ratios

## Advanced Optimizations

### Future Enhancements
- [ ] Implement HTTP/2 Server Push for critical assets
- [ ] Add Service Worker for offline functionality
- [ ] Enable Brotli compression on server
- [ ] Implement adaptive loading based on connection speed
- [ ] Add resource hints for above-the-fold images
- [ ] Consider using CDN for static assets

### Server-Side Optimizations
```apache
# Enable compression
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/css application/javascript
</IfModule>

# Enable caching
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType image/webp "access plus 1 year"
  ExpiresByType text/css "access plus 1 month"
  ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

## Troubleshooting

### Common Issues
1. **High LCP**: Optimize hero images, preload critical assets
2. **Poor CLS**: Add explicit dimensions, reserve space for dynamic content
3. **Slow FID**: Reduce JavaScript execution time, split long tasks
4. **Large Bundle**: Enable tree-shaking, lazy-load routes

### Debug Steps
1. Use Coverage tab in DevTools to find unused code
2. Network waterfall to identify slow resources
3. Performance profiler to find long tasks
4. Memory profiler to detect leaks

---

Last Updated: 2025-10-16

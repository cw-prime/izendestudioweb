/**
 * Google Analytics Event Tracking
 * Tracks user interactions like form submissions, video plays, phone clicks, etc.
 */

(function() {
  'use strict';

  // Check if gtag is available (GA4)
  const hasGtag = typeof gtag !== 'undefined';

  // Check if dataLayer is available (GTM)
  const hasDataLayer = typeof window.dataLayer !== 'undefined';

  /**
   * Send event to Google Analytics
   */
  function trackEvent(eventName, eventParams = {}) {
    if (hasGtag) {
      gtag('event', eventName, eventParams);
      console.log('[Analytics] Event tracked:', eventName, eventParams);
    } else if (hasDataLayer) {
      window.dataLayer.push({
        'event': eventName,
        ...eventParams
      });
      console.log('[Analytics] Event pushed to dataLayer:', eventName, eventParams);
    }
  }

  /**
   * Track form submissions
   */
  function trackFormSubmissions() {
    // Track contact form
    const contactForm = document.querySelector('#contact form');
    if (contactForm) {
      contactForm.addEventListener('submit', function(e) {
        trackEvent('form_submission', {
          'form_name': 'contact_form',
          'form_location': 'contact_section'
        });
      });
    }

    // Track quote forms
    const quoteForms = document.querySelectorAll('form[action*="quote"]');
    quoteForms.forEach(function(form) {
      form.addEventListener('submit', function(e) {
        trackEvent('form_submission', {
          'form_name': 'quote_form',
          'form_location': window.location.pathname
        });
      });
    });
  }

  /**
   * Track video plays
   */
  function trackVideoPlays() {
    // Track YouTube video clicks via lightbox
    const videoLinks = document.querySelectorAll('a[data-glightbox*="video"], .video-lightbox');
    videoLinks.forEach(function(link) {
      link.addEventListener('click', function(e) {
        const videoUrl = this.getAttribute('href');
        const videoTitle = this.getAttribute('title') || this.querySelector('h4')?.textContent || 'Unknown Video';

        trackEvent('video_play', {
          'video_title': videoTitle,
          'video_url': videoUrl,
          'video_location': 'portfolio'
        });
      });
    });
  }

  /**
   * Track external link clicks
   */
  function trackExternalLinks() {
    const currentDomain = window.location.hostname;
    const externalLinks = document.querySelectorAll('a[href^="http"]');

    externalLinks.forEach(function(link) {
      const linkDomain = new URL(link.href).hostname;

      // Check if link is external
      if (linkDomain !== currentDomain) {
        link.addEventListener('click', function(e) {
          trackEvent('external_link_click', {
            'link_url': this.href,
            'link_text': this.textContent.trim(),
            'link_domain': linkDomain
          });
        });
      }
    });
  }

  /**
   * Track phone number clicks
   */
  function trackPhoneClicks() {
    const phoneLinks = document.querySelectorAll('a[href^="tel:"]');

    phoneLinks.forEach(function(link) {
      link.addEventListener('click', function(e) {
        const phoneNumber = this.href.replace('tel:', '');

        trackEvent('phone_click', {
          'phone_number': phoneNumber,
          'link_location': window.location.pathname
        });
      });
    });
  }

  /**
   * Track portfolio item views
   */
  function trackPortfolioViews() {
    const portfolioLinks = document.querySelectorAll('.portfolio-links a[href*="portfolio-details"]');

    portfolioLinks.forEach(function(link) {
      link.addEventListener('click', function(e) {
        const portfolioTitle = this.closest('.portfolio-item')?.querySelector('h4')?.textContent || 'Unknown Project';

        trackEvent('portfolio_view', {
          'project_title': portfolioTitle,
          'view_type': 'case_study'
        });
      });
    });
  }

  /**
   * Track scroll depth
   */
  function trackScrollDepth() {
    let scrollThresholds = [25, 50, 75, 100];
    let triggeredThresholds = [];

    window.addEventListener('scroll', function() {
      const scrollPercent = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;

      scrollThresholds.forEach(function(threshold) {
        if (scrollPercent >= threshold && !triggeredThresholds.includes(threshold)) {
          triggeredThresholds.push(threshold);

          trackEvent('scroll_depth', {
            'scroll_percentage': threshold,
            'page_path': window.location.pathname
          });
        }
      });
    });
  }

  /**
   * Track time on page (send event after 30 seconds)
   */
  function trackTimeOnPage() {
    setTimeout(function() {
      trackEvent('time_on_page', {
        'time_threshold': '30_seconds',
        'page_path': window.location.pathname
      });
    }, 30000); // 30 seconds
  }

  /**
   * Initialize all tracking
   */
  function initTracking() {
    if (!hasGtag && !hasDataLayer) {
      console.log('[Analytics] No tracking library detected. Skipping event tracking.');
      return;
    }

    console.log('[Analytics] Initializing event tracking...');

    // Get tracking settings from data attributes (set by PHP)
    const trackForms = document.body.dataset.trackForms !== 'false';
    const trackVideos = document.body.dataset.trackVideos !== 'false';
    const trackExternal = document.body.dataset.trackExternal !== 'false';
    const trackPhone = document.body.dataset.trackPhone !== 'false';

    if (trackForms) trackFormSubmissions();
    if (trackVideos) trackVideoPlays();
    if (trackExternal) trackExternalLinks();
    if (trackPhone) trackPhoneClicks();

    // Always track these
    trackPortfolioViews();
    trackScrollDepth();
    trackTimeOnPage();

    console.log('[Analytics] Event tracking initialized');
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTracking);
  } else {
    initTracking();
  }

})();

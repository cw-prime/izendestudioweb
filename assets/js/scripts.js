/**
 * Izende Studio Web - Utility Functions & Polyfills
 * Repurposed for Phase 15: Performance & Polish
 */

(function() {
  'use strict';

  /**
   * Feature Detection
   */
  const FeatureDetection = {
    hasWebP: function() {
      return document.documentElement.classList.contains('webp');
    },

    hasIntersectionObserver: function() {
      return 'IntersectionObserver' in window;
    },

    hasLocalStorage: function() {
      try {
        const test = '__localStorage_test__';
        localStorage.setItem(test, test);
        localStorage.removeItem(test);
        return true;
      } catch (e) {
        return false;
      }
    },

    hasServiceWorker: function() {
      return 'serviceWorker' in navigator;
    },

    hasTouchScreen: function() {
      return ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
    },

    isReducedMotion: function() {
      return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }
  };

  /**
   * DOM Utilities
   */
  const DOMUtils = {
    createElement: function(tag, classes = [], attributes = {}) {
      const element = document.createElement(tag);
      if (classes.length) element.classList.add(...classes);
      Object.keys(attributes).forEach(key => {
        element.setAttribute(key, attributes[key]);
      });
      return element;
    },

    getScrollPosition: function() {
      return {
        x: window.scrollX || window.pageXOffset,
        y: window.scrollY || window.pageYOffset
      };
    },

    isInViewport: function(element) {
      const rect = element.getBoundingClientRect();
      return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
      );
    },

    getOffset: function(element) {
      const rect = element.getBoundingClientRect();
      return {
        top: rect.top + window.scrollY,
        left: rect.left + window.scrollX
      };
    }
  };

  /**
   * String Utilities
   */
  const StringUtils = {
    slugify: function(text) {
      return text.toString().toLowerCase()
        .replace(/\s+/g, '-')           // Replace spaces with -
        .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
        .replace(/\-\-+/g, '-')         // Replace multiple - with single -
        .replace(/^-+/, '')             // Trim - from start of text
        .replace(/-+$/, '');            // Trim - from end of text
    },

    truncate: function(text, length, suffix = '...') {
      if (text.length <= length) return text;
      return text.substring(0, length).trim() + suffix;
    },

    stripHTML: function(html) {
      const tmp = document.createElement('div');
      tmp.innerHTML = html;
      return tmp.textContent || tmp.innerText || '';
    }
  };

  /**
   * Number Utilities
   */
  const NumberUtils = {
    formatNumber: function(num) {
      return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    },

    formatCurrency: function(amount, currency = '$') {
      return currency + this.formatNumber(amount.toFixed(2));
    },

    clamp: function(value, min, max) {
      return Math.min(Math.max(value, min), max);
    }
  };

  /**
   * Date Utilities
   */
  const DateUtils = {
    formatDate: function(date) {
      const d = new Date(date);
      return d.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },

    timeAgo: function(date) {
      const seconds = Math.floor((new Date() - new Date(date)) / 1000);

      let interval = seconds / 31536000;
      if (interval > 1) return Math.floor(interval) + ' years ago';

      interval = seconds / 2592000;
      if (interval > 1) return Math.floor(interval) + ' months ago';

      interval = seconds / 86400;
      if (interval > 1) return Math.floor(interval) + ' days ago';

      interval = seconds / 3600;
      if (interval > 1) return Math.floor(interval) + ' hours ago';

      interval = seconds / 60;
      if (interval > 1) return Math.floor(interval) + ' minutes ago';

      return Math.floor(seconds) + ' seconds ago';
    },

    isToday: function(date) {
      const today = new Date();
      const d = new Date(date);
      return d.getDate() === today.getDate() &&
             d.getMonth() === today.getMonth() &&
             d.getFullYear() === today.getFullYear();
    }
  };

  /**
   * Performance Helpers
   */
  const PerformanceHelpers = {
    // Debounce function (delay execution until after calls have stopped)
    debounce: function(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    },

    // Throttle function (limit execution to once per interval)
    throttle: function(func, limit) {
      let inThrottle;
      return function(...args) {
        if (!inThrottle) {
          func.apply(this, args);
          inThrottle = true;
          setTimeout(() => inThrottle = false, limit);
        }
      };
    },

    // RequestAnimationFrame wrapper
    raf: function(callback) {
      return window.requestAnimationFrame(callback);
    },

    cancelRaf: function(id) {
      return window.cancelAnimationFrame(id);
    }
  };

  /**
   * Accessibility Helpers
   */
  const AccessibilityHelpers = {
    setAriaExpanded: function(element, expanded) {
      element.setAttribute('aria-expanded', expanded.toString());
    },

    setAriaHidden: function(element, hidden) {
      element.setAttribute('aria-hidden', hidden.toString());
    },

    announceToScreenReader: function(message, priority = 'polite') {
      if (typeof window.announce === 'function') {
        window.announce(message, priority);
      }
    },

    trapFocus: function(container) {
      const focusableElements = 'a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, [tabindex]:not([tabindex="-1"])';
      const focusable = container.querySelectorAll(focusableElements);
      const firstFocusable = focusable[0];
      const lastFocusable = focusable[focusable.length - 1];

      container.addEventListener('keydown', function(e) {
        if (e.key !== 'Tab') return;

        if (e.shiftKey) {
          if (document.activeElement === firstFocusable) {
            lastFocusable.focus();
            e.preventDefault();
          }
        } else {
          if (document.activeElement === lastFocusable) {
            firstFocusable.focus();
            e.preventDefault();
          }
        }
      });

      firstFocusable.focus();
    },

    restoreFocus: function(element) {
      if (element && element.focus) {
        element.focus();
      }
    },

    getFocusableElements: function(container) {
      const focusableElements = 'a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, [tabindex]:not([tabindex="-1"])';
      return Array.from(container.querySelectorAll(focusableElements));
    }
  };

  /**
   * Global Error Handler
   */
  window.addEventListener('error', function(e) {
    // Log errors in development
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
      console.error('Global error:', e.error);
    }

    // Optionally send to error tracking service
    if (typeof window.dataLayer !== 'undefined') {
      window.dataLayer.push({
        event: 'javascript_error',
        error_message: e.message,
        error_source: e.filename,
        error_line: e.lineno
      });
    }

    // Prevent white screen of death - show user-friendly error
    // (Don't implement this in production without testing)
  });

  /**
   * Export Utilities to Global Scope
   */
  window.IzendeUtils = {
    FeatureDetection,
    DOMUtils,
    StringUtils,
    NumberUtils,
    DateUtils,
    PerformanceHelpers,
    AccessibilityHelpers
  };

  // Convenience aliases
  window.debounce = PerformanceHelpers.debounce;
  window.throttle = PerformanceHelpers.throttle;

})();

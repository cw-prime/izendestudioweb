/**
* Template Name: Green - v4.6.0
* Template URL: https://bootstrapmade.com/green-free-one-page-bootstrap-template/
* Author: BootstrapMade.com
* License: https://bootstrapmade.com/license/
*/
(function() {
  "use strict";

  /**
   * Easy selector helper function
   */
  const select = (el, all = false) => {
    el = el.trim()
    if (all) {
      return [...document.querySelectorAll(el)]
    } else {
      return document.querySelector(el)
    }
  }

  /**
   * Easy event listener function
   */
  const on = (type, el, listener, all = false) => {
    let selectEl = select(el, all)
    if (selectEl) {
      if (all) {
        selectEl.forEach(e => e.addEventListener(type, listener))
      } else {
        selectEl.addEventListener(type, listener)
      }
    }
  }

  /**
   * Easy on scroll event listener
   */
  const onscroll = (el, listener) => {
    el.addEventListener('scroll', listener)
  }

  /**
   * Navbar links active state on scroll
   */
  let navbarlinks = select('#navbar .scrollto', true)
  const navbarlinksActive = () => {
    let position = window.scrollY + 200
    navbarlinks.forEach(navbarlink => {
      if (!navbarlink.hash) return
      let section = select(navbarlink.hash)
      if (!section) return
      if (position >= section.offsetTop && position <= (section.offsetTop + section.offsetHeight)) {
        navbarlink.classList.add('active')
      } else {
        navbarlink.classList.remove('active')
      }
    })
  }
  window.addEventListener('load', navbarlinksActive)
  onscroll(document, navbarlinksActive)

  /**
   * Scrolls to an element with header offset
   */
  const scrollto = (el) => {
    let header = select('#header')
    let offset = header.offsetHeight

    if (!header.classList.contains('header-scrolled')) {
      offset -= 16
    }

    let elementPos = select(el).offsetTop
    window.scrollTo({
      top: elementPos - offset,
      behavior: 'smooth'
    })
  }

  /**
   * Header fixed top on scroll
   */
  let selectHeader = select('#header')
  if (selectHeader) {
    let headerOffset = selectHeader.offsetTop
    let nextElement = selectHeader.nextElementSibling
    const headerFixed = () => {
      if ((headerOffset - window.scrollY) <= 0) {
        selectHeader.classList.add('fixed-top')
        nextElement.classList.add('scrolled-offset')
      } else {
        selectHeader.classList.remove('fixed-top')
        nextElement.classList.remove('scrolled-offset')
      }
    }
    window.addEventListener('load', headerFixed)
    onscroll(document, headerFixed)
  }

  /**
   * Back to top button
   */
  let backtotop = select('.back-to-top')
  if (backtotop) {
    const toggleBacktotop = () => {
      if (window.scrollY > 100) {
        backtotop.classList.add('active')
      } else {
        backtotop.classList.remove('active')
      }
    }
    window.addEventListener('load', toggleBacktotop)
    onscroll(document, toggleBacktotop)
  }

  /**
   * Mobile nav toggle
   */
  on('click', '.mobile-nav-toggle', function(e) {
    select('#navbar').classList.toggle('navbar-mobile')
    this.classList.toggle('bi-list')
    this.classList.toggle('bi-x')
  })

  /**
   * Mobile nav dropdowns activate
   */
  on('click', '.navbar .dropdown > a', function(e) {
    if (select('#navbar').classList.contains('navbar-mobile')) {
      e.preventDefault()
      this.nextElementSibling.classList.toggle('dropdown-active')
    }
  }, true)

  /**
   * Scrool with ofset on links with a class name .scrollto
   */
  on('click', '.scrollto', function(e) {
    if (select(this.hash)) {
      e.preventDefault()

      let navbar = select('#navbar')
      if (navbar.classList.contains('navbar-mobile')) {
        navbar.classList.remove('navbar-mobile')
        let navbarToggle = select('.mobile-nav-toggle')
        navbarToggle.classList.toggle('bi-list')
        navbarToggle.classList.toggle('bi-x')
      }
      scrollto(this.hash)
    }
  }, true)

  /**
   * Scroll with ofset on page load with hash links in the url
   */
  window.addEventListener('load', () => {
    if (window.location.hash) {
      if (select(window.location.hash)) {
        scrollto(window.location.hash)
      }
    }
  });

  /**
   * Hero carousel indicators
   */
  let heroCarouselIndicators = select("#hero-carousel-indicators")
  let heroCarouselItems = select('#heroCarousel .carousel-item', true)

  heroCarouselItems.forEach((item, index) => {
    (index === 0) ?
    heroCarouselIndicators.innerHTML += "<li data-bs-target='#heroCarousel' data-bs-slide-to='" + index + "' class='active'></li>":
      heroCarouselIndicators.innerHTML += "<li data-bs-target='#heroCarousel' data-bs-slide-to='" + index + "'></li>"
  });

  /**
   * Clients Slider
   */
  new Swiper('.clients-slider', {
    speed: 400,
    loop: true,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false
    },
    slidesPerView: 'auto',
    pagination: {
      el: '.swiper-pagination',
      type: 'bullets',
      clickable: true
    },
    breakpoints: {
      320: {
        slidesPerView: 2,
        spaceBetween: 40
      },
      480: {
        slidesPerView: 3,
        spaceBetween: 60
      },
      640: {
        slidesPerView: 4,
        spaceBetween: 80
      },
      992: {
        slidesPerView: 6,
        spaceBetween: 120
      }
    }
  });

  /**
   * Porfolio isotope and filter
   */
  window.addEventListener('load', () => {
    let portfolioContainer = select('.portfolio-container');
    if (portfolioContainer) {
      let portfolioIsotope = new Isotope(portfolioContainer, {
        itemSelector: '.portfolio-item'
      });

      let portfolioFilters = select('#portfolio-flters li', true);

      on('click', '#portfolio-flters li', function(e) {
        e.preventDefault();
        portfolioFilters.forEach(function(el) {
          el.classList.remove('filter-active');
          el.setAttribute('aria-pressed', 'false');
        });
        this.classList.add('filter-active');
        this.setAttribute('aria-pressed', 'true');

        portfolioIsotope.arrange({
          filter: this.getAttribute('data-filter')
        });

      }, true);
    }

  });

  /**
   * Initiate portfolio and video lightbox
   */
  const portfolioLightbox = GLightbox({
    selector: '.portfolio-lightbox, .video-lightbox'
  });

  /**
   * Portfolio details slider
   */
  new Swiper('.portfolio-details-slider', {
    speed: 400,
    loop: true,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false
    },
    pagination: {
      el: '.swiper-pagination',
      type: 'bullets',
      clickable: true
    }
  });

  //SHow Hide Quote on Quote Page
  if(window.location.href.indexOf('quote') != -1){
    document.getElementById("quote").hidden = true;
  }

  /**
   * Validation Utilities
   */
  const ValidationUtils = {
    validateEmail: (email) => {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return re.test(email);
    },

    validatePhone: (phone) => {
      const re = /^[0-9]{3}-[0-9]{3}-[0-9]{4}$/;
      return re.test(phone);
    },

    validateURL: (url) => {
      try {
        new URL(url);
        return true;
      } catch {
        return false;
      }
    },

    validateLength: (value, min, max) => {
      const len = value.trim().length;
      return len >= min && len <= max;
    }
  };

  /**
   * Toast Notification System
   */
  const Toast = {
    container: null,

    init: function() {
      if (!this.container) {
        this.container = document.createElement('div');
        this.container.className = 'toast-container';
        this.container.setAttribute('role', 'region');
        this.container.setAttribute('aria-live', 'polite');
        this.container.setAttribute('aria-atomic', 'true');
        document.body.appendChild(this.container);
      }
    },

    show: function(message, type = 'info', duration = 5000) {
      this.init();

      const toast = document.createElement('div');
      toast.className = `toast toast-${type}`;
      toast.setAttribute('role', type === 'error' ? 'alert' : 'status');

      const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ';

      toast.innerHTML = `
        <div class="toast-icon">${icon}</div>
        <div class="toast-content">
          <div class="toast-title">${type.charAt(0).toUpperCase() + type.slice(1)}</div>
          <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" aria-label="Close notification">&times;</button>
      `;

      this.container.appendChild(toast);

      // Close button handler
      const closeBtn = toast.querySelector('.toast-close');
      closeBtn.addEventListener('click', () => this.hide(toast));

      // Auto-hide after duration
      if (duration > 0) {
        setTimeout(() => this.hide(toast), duration);
      }

      return toast;
    },

    hide: function(toast) {
      toast.classList.add('hiding');
      setTimeout(() => {
        if (toast.parentNode) {
          toast.parentNode.removeChild(toast);
        }
      }, 300);
    },

    success: function(message, duration) {
      return this.show(message, 'success', duration);
    },

    error: function(message, duration) {
      return this.show(message, 'error', duration);
    },

    info: function(message, duration) {
      return this.show(message, 'info', duration);
    }
  };

  /**
   * Form Validation Handler
   */
  const FormValidator = {
    setValid: function(input) {
      input.classList.remove('is-invalid');
      input.classList.add('is-valid');
      const feedback = input.parentElement.parentElement.querySelector('.invalid-feedback');
      if (feedback) feedback.style.display = 'none';
    },

    setInvalid: function(input, message) {
      input.classList.remove('is-valid');
      input.classList.add('is-invalid');
      const feedback = input.parentElement.parentElement.querySelector('.invalid-feedback');
      if (feedback) {
        feedback.textContent = message;
        feedback.style.display = 'block';
      }
    },

    clearValidation: function(input) {
      input.classList.remove('is-valid', 'is-invalid');
      const feedback = input.parentElement.parentElement.querySelector('.invalid-feedback');
      if (feedback) feedback.style.display = 'none';
    },

    validateField: function(input) {
      const value = input.value.trim();
      const type = input.type;
      const name = input.name;

      if (input.hasAttribute('required') && !value) {
        this.setInvalid(input, 'This field is required.');
        return false;
      }

      if (value) {
        switch (type) {
          case 'email':
            if (!ValidationUtils.validateEmail(value)) {
              this.setInvalid(input, 'Please enter a valid email address.');
              return false;
            }
            break;
          case 'tel':
            if (!ValidationUtils.validatePhone(value)) {
              this.setInvalid(input, 'Please enter a valid phone number (format: 123-456-7890).');
              return false;
            }
            break;
          case 'url':
            if (!ValidationUtils.validateURL(value)) {
              this.setInvalid(input, 'Please enter a valid URL.');
              return false;
            }
            break;
        }
      }

      if (value) {
        this.setValid(input);
      }
      return true;
    }
  };

  /**
   * Quote Wizard Implementation
   */
  class QuoteWizard {
    constructor(formSelector) {
      this.form = document.querySelector(formSelector);
      if (!this.form) return;

      this.currentStep = 1;
      this.totalSteps = 3;
      this.steps = this.form.querySelectorAll('.wizard-step');
      this.progressItems = document.querySelectorAll('.wizard-progress li');

      this.init();
    }

    init() {
      // Bind navigation buttons
      this.form.querySelectorAll('.btn-next').forEach(btn => {
        btn.addEventListener('click', () => this.nextStep());
      });

      this.form.querySelectorAll('.btn-prev').forEach(btn => {
        btn.addEventListener('click', () => this.prevStep());
      });

      // Add validation listeners
      this.form.querySelectorAll('input, select, textarea').forEach(input => {
        input.addEventListener('blur', () => FormValidator.validateField(input));
        input.addEventListener('input', debounce(() => {
          if (input.classList.contains('is-invalid') || input.classList.contains('is-valid')) {
            FormValidator.validateField(input);
          }
        }, 500));
      });

      // Handle form submission
      this.form.addEventListener('submit', (e) => this.handleSubmit(e));

      // Show first step
      this.goToStep(1);
    }

    validateStep(stepNumber) {
      const step = this.steps[stepNumber - 1];
      const inputs = step.querySelectorAll('input[required], select[required], textarea[required]');
      let valid = true;

      inputs.forEach(input => {
        if (!FormValidator.validateField(input)) {
          valid = false;
        }
      });

      return valid;
    }

    nextStep() {
      if (!this.validateStep(this.currentStep)) {
        Toast.error('Please fill in all required fields correctly.');
        // Focus on first invalid field
        const firstInvalid = this.steps[this.currentStep - 1].querySelector('.is-invalid');
        if (firstInvalid) firstInvalid.focus();
        return;
      }

      if (this.currentStep < this.totalSteps) {
        this.goToStep(this.currentStep + 1);
      }
    }

    prevStep() {
      if (this.currentStep > 1) {
        this.goToStep(this.currentStep - 1);
      }
    }

    goToStep(stepNumber) {
      // Hide all steps
      this.steps.forEach(step => step.style.display = 'none');

      // Show current step
      this.steps[stepNumber - 1].style.display = 'block';

      // Update progress indicator
      this.progressItems.forEach((item, index) => {
        item.removeAttribute('aria-current');
        item.classList.remove('completed');

        if (index + 1 === stepNumber) {
          item.setAttribute('aria-current', 'step');
        } else if (index + 1 < stepNumber) {
          item.classList.add('completed');
        }
      });

      this.currentStep = stepNumber;

      // Focus on first input of new step
      setTimeout(() => {
        const firstInput = this.steps[stepNumber - 1].querySelector('input, select, textarea');
        if (firstInput) firstInput.focus();
      }, 100);
    }

    handleSubmit(e) {
      e.preventDefault();

      if (!this.validateStep(this.currentStep)) {
        Toast.error('Please fill in all required fields correctly.');
        return;
      }

      const submitBtn = this.form.querySelector('.btn-submit');
      submitBtn.classList.add('loading');
      submitBtn.disabled = true;

      // Track conversion before form submission
      if (typeof trackConversion === 'function') {
        trackConversion('quote_form_submit', { form_name: 'quote_form' });
      }

      // Allow normal form submission (non-AJAX for quote form)
      setTimeout(() => {
        this.form.submit();
      }, 100);
    }
  }

  // Debounce utility
  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  /**
   * Contact Form AJAX Handler
   */
  const contactForm = document.getElementById('contact-form');
  if (contactForm) {
    // Add validation listeners
    contactForm.querySelectorAll('input, textarea').forEach(input => {
      input.addEventListener('blur', () => FormValidator.validateField(input));
      input.addEventListener('input', debounce(() => {
        if (input.classList.contains('is-invalid') || input.classList.contains('is-valid')) {
          FormValidator.validateField(input);
        }
      }, 500));
    });

    contactForm.addEventListener('submit', async function(e) {
      e.preventDefault();

      // Validate all fields
      const inputs = contactForm.querySelectorAll('input[required], textarea[required]');
      let valid = true;
      inputs.forEach(input => {
        if (!FormValidator.validateField(input)) {
          valid = false;
        }
      });

      if (!valid) {
        Toast.error('Please fill in all required fields correctly.');
        const firstInvalid = contactForm.querySelector('.is-invalid');
        if (firstInvalid) firstInvalid.focus();
        return;
      }

      // Get form elements
      const submitBtn = contactForm.querySelector('button[type="submit"]');
      const loadingEl = document.getElementById('contact-loading');
      const errorEl = document.getElementById('contact-error');
      const successEl = document.getElementById('contact-success');

      // Show loading state
      submitBtn.classList.add('loading');
      submitBtn.disabled = true;
      if (loadingEl) loadingEl.style.display = 'block';
      if (errorEl) errorEl.style.display = 'none';
      if (successEl) successEl.style.display = 'none';

      try {
        const formData = new FormData(contactForm);
        const response = await fetch(contactForm.action, {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.success) {
          // Show success
          if (successEl) {
            successEl.textContent = result.message;
            successEl.style.display = 'block';
          }
          Toast.success(result.message);

          // Track conversion
          trackConversion('contact_form_submit', { form_name: 'contact_form' });

          // Reset form
          contactForm.reset();
          inputs.forEach(input => FormValidator.clearValidation(input));
        } else {
          // Show error
          if (errorEl) {
            errorEl.textContent = result.message;
            errorEl.style.display = 'block';
          }
          Toast.error(result.message);
        }
      } catch (error) {
        const errorMessage = 'An error occurred. Please try again later.';
        if (errorEl) {
          errorEl.textContent = errorMessage;
          errorEl.style.display = 'block';
        }
        Toast.error(errorMessage);
        console.error('Form submission error:', error);
      } finally {
        // Hide loading state
        submitBtn.classList.remove('loading');
        submitBtn.disabled = false;
        if (loadingEl) loadingEl.style.display = 'none';
      }
    });
  }

  /**
   * Initialize Quote Wizard
   */
  if (document.querySelector('#myform')) {
    new QuoteWizard('#myform');
  }

})()
  /**
   * Stats Counter Animation with IntersectionObserver
   */
  const StatsCounter = {
    init: function() {
      const counters = document.querySelectorAll('.counter');
      if (counters.length === 0) return;

      const options = {
        threshold: 0.5,
        rootMargin: '0px'
      };

      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
            this.animateCounter(entry.target);
            entry.target.classList.add('counted');
          }
        });
      }, options);

      counters.forEach(counter => observer.observe(counter));
    },

    animateCounter: function(element) {
      const targetStr = element.getAttribute('data-target');
      const suffix = element.getAttribute('data-suffix') || '';
      const target = parseFloat(targetStr);
      const isDecimal = targetStr.includes('.');
      const decimalPlaces = isDecimal ? (targetStr.split('.')[1] || '').length : 0;

      const duration = 2000;
      const increment = target / (duration / 16);
      let current = 0;

      const updateCounter = () => {
        current += increment;
        if (current < target) {
          if (isDecimal) {
            element.textContent = current.toFixed(decimalPlaces) + suffix;
          } else {
            element.textContent = Math.floor(current) + suffix;
          }
          requestAnimationFrame(updateCounter);
        } else {
          if (isDecimal) {
            element.textContent = target.toFixed(decimalPlaces) + suffix;
          } else {
            element.textContent = target + suffix;
          }
        }
      };

      // Check for reduced motion preference
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        element.textContent = (isDecimal ? target.toFixed(decimalPlaces) : target) + suffix;
      } else {
        updateCounter();
      }
    }
  };

  /**
   * Exit-Intent Popup System
   */
  class ExitIntentPopup {
    constructor() {
      this.modal = document.getElementById('leadMagnetModal');
      if (!this.modal) return;

      this.closeBtn = document.getElementById('closeLeadModal');
      this.form = document.getElementById('leadCaptureForm');
      this.hasShown = sessionStorage.getItem('leadMagnetShown') === 'true';
      this.hasConverted = localStorage.getItem('leadMagnetConverted') === 'true';

      if (!this.hasConverted) {
        this.init();
      }
    }

    init() {
      // Desktop: Mouse leave detection
      document.addEventListener('mouseleave', (e) => {
        if (e.clientY <= 0 && !this.hasShown) {
          this.show();
        }
      });

      // Mobile: Scroll-based trigger (75% down page)
      let scrollTriggered = false;
      window.addEventListener('scroll', () => {
        if (scrollTriggered || this.hasShown) return;

        const scrollPercent = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
        if (scrollPercent > 75) {
          scrollTriggered = true;
          setTimeout(() => this.show(), 1000);
        }
      });

      // Close handlers
      if (this.closeBtn) {
        this.closeBtn.addEventListener('click', () => this.hide());
      }

      this.modal.addEventListener('click', (e) => {
        if (e.target === this.modal) {
          this.hide();
        }
      });

      // Form submission
      if (this.form) {
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
      }

      // ESC key to close
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && this.modal.style.display === 'flex') {
          this.hide();
        }
      });
    }

    show() {
      if (this.hasShown || this.hasConverted) return;

      this.modal.style.display = 'flex';
      this.hasShown = true;
      sessionStorage.setItem('leadMagnetShown', 'true');

      // Track event
      if (typeof trackConversion === 'function') {
        trackConversion('lead_magnet_shown');
      }

      // Focus first input
      const firstInput = this.form.querySelector('input[type="text"]');
      if (firstInput) {
        setTimeout(() => firstInput.focus(), 300);
      }
    }

    hide() {
      this.modal.style.display = 'none';
    }

    async handleSubmit(e) {
      e.preventDefault();

      const submitBtn = this.form.querySelector('button[type="submit"]');
      const errorEl = document.getElementById('lead-error');
      const formData = new FormData(this.form);

      // Show loading state
      submitBtn.querySelector('.btn-text').style.display = 'none';
      submitBtn.querySelector('.btn-spinner').style.display = 'inline-flex';
      submitBtn.disabled = true;
      if (errorEl) errorEl.style.display = 'none';

      try {
        const response = await fetch(this.form.action, {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.success) {
          // Mark as converted
          localStorage.setItem('leadMagnetConverted', 'true');
          this.hasConverted = true;

          // Show success message
          Toast.success(result.message || 'Thank you! Check your email for the download links.');

          // Track conversion
          if (typeof trackConversion === 'function') {
            trackConversion('lead_magnet_conversion', {
              email: formData.get('email'),
              name: formData.get('name')
            });
          }

          // Close modal after delay
          setTimeout(() => {
            this.hide();
          }, 2000);
        } else {
          throw new Error(result.message || 'Submission failed');
        }
      } catch (error) {
        if (errorEl) {
          errorEl.textContent = error.message || 'An error occurred. Please try again.';
          errorEl.style.display = 'block';
        }
        Toast.error(error.message || 'An error occurred. Please try again.');
      } finally {
        // Hide loading state
        submitBtn.querySelector('.btn-text').style.display = 'inline';
        submitBtn.querySelector('.btn-spinner').style.display = 'none';
        submitBtn.disabled = false;
      }
    }
  }

  /**
   * Conversion Tracking Helper
   */
  window.trackConversion = function(eventName, eventData = {}) {
    // GTM/GA4 tracking
    if (typeof window.dataLayer !== 'undefined') {
      window.dataLayer.push({
        event: eventName,
        ...eventData
      });
    }

    // Facebook Pixel tracking (if implemented)
    if (typeof fbq !== 'undefined') {
      fbq('trackCustom', eventName, eventData);
    }

    // Console log for debugging
    console.log('Conversion tracked:', eventName, eventData);
  };

  /**
   * Track CTA Clicks
   */
  document.addEventListener('click', function(e) {
    const target = e.target.closest('a, button');
    if (!target) return;

    // Track phone clicks
    if (target.href && target.href.startsWith('tel:')) {
      trackConversion('phone_click', {
        phone_number: target.href.replace('tel:', ''),
        location: target.closest('section')?.id || 'unknown'
      });
    }

    // Track email clicks
    if (target.href && target.href.startsWith('mailto:')) {
      trackConversion('email_click', {
        email: target.href.replace('mailto:', ''),
        location: target.closest('section')?.id || 'unknown'
      });
    }

    // Track CTA button clicks
    if (target.classList.contains('btn-get-started') ||
        target.classList.contains('cta-btn') ||
        target.classList.contains('btn-brand')) {
      trackConversion('cta_click', {
        button_text: target.textContent.trim(),
        button_url: target.href || 'form_submit',
        location: target.closest('section')?.id || 'unknown'
      });
    }
  });

  /**
   * Scroll Depth Tracking
   */
  const ScrollDepthTracker = {
    milestones: [25, 50, 75, 100],
    tracked: new Set(),

    init: function() {
      window.addEventListener('scroll', this.track.bind(this), { passive: true });
    },

    track: function() {
      const scrollPercent = Math.round((window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100);

      this.milestones.forEach(milestone => {
        if (scrollPercent >= milestone && !this.tracked.has(milestone)) {
          this.tracked.add(milestone);
          trackConversion('scroll_depth', {
            depth_percent: milestone,
            page_path: window.location.pathname
          });
        }
      });
    }
  };

  /**
   * Initialize All Enhancements
   */
  window.addEventListener('load', () => {
    // Initialize stats counters
    StatsCounter.init();

    // Initialize exit-intent popup
    new ExitIntentPopup();

    // Initialize scroll tracking
    ScrollDepthTracker.init();
    // Initialize cookie consent manager
    try { window.__cookieConsent = new CookieConsent(); } catch (e) { console.error('CookieConsent init failed', e); }
  });

  // AOS (Animate On Scroll) initialization
  if (typeof AOS !== 'undefined') {
    AOS.init({
      duration: 1000,
      easing: 'ease-in-out',
      once: true,
      mirror: false,
      disable: 'mobile'
    });
  }

  /**
   * Cookie Consent Manager
   */
  class CookieConsent {
    constructor() {
      this.key = 'izende_cookie_consent_v1';
      this.expiryDays = 180; // 6 months
      this.banner = null;
      this.modal = null;
      this.init();
    }

    init() {
      // Respect Global Privacy Control (GPC)
      const gpc = (navigator.globalPrivacyControl === true);

      // Initialize dataLayer and set Consent Mode defaults to denied (v2-like)
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({ 'event': 'consent_defaults_set', 'consent_defaults': true });
      // If gtag present, set conservative defaults
      if (typeof window.gtag === 'function') {
        try {
          window.gtag('consent', 'default', {
            'ad_storage': 'denied',
            'analytics_storage': 'denied',
            'ad_personalization': 'denied'
          });
        } catch (e) { /* ignore */ }
      }

      const existing = this.getConsent();
      if (!existing) {
        this.showBanner(gpc);
      } else {
        // Apply consent settings (e.g., opt-out analytics)
        this.applyConsent(existing);
      }

      // Wire cookie settings link
      const csLink = document.getElementById('cookie-settings-link');
      if (csLink) {
        csLink.addEventListener('click', (e) => {
          e.preventDefault();
          this.openModal();
        });
      }
    }

    showBanner(gpc) {
      // Create banner if not present
      this.banner = document.createElement('div');
      this.banner.className = 'cookie-consent-banner';
      this.banner.setAttribute('role', 'dialog');
      this.banner.setAttribute('aria-live', 'polite');
      this.banner.innerHTML = `
        <div class="cc-body" aria-label="Cookie consent banner">We use cookies to improve your experience. Manage your preferences or accept optional cookies.</div>
        <div class="cc-actions">
          <button class="cc-btn" id="cc-reject">Reject All</button>
          <button class="cc-btn secondary" id="cc-manage">Manage</button>
          <button class="cc-btn" id="cc-accept">Accept All</button>
        </div>
      `;
      document.body.appendChild(this.banner);
      document.getElementById('cc-manage').addEventListener('click', () => this.openModal());
      document.getElementById('cc-accept').addEventListener('click', () => {
        this.saveConsent({ analytics: true, marketing: true, functional: true });
        if (this.banner) this.banner.remove();
        this.applyConsent({ analytics: true, marketing: true, functional: true });
      });
      document.getElementById('cc-reject').addEventListener('click', () => {
        // Reject all optional cookies
        this.saveConsent({ analytics: false, marketing: false, functional: false });
        if (this.banner) this.banner.remove();
        this.applyConsent({ analytics: false, marketing: false, functional: false });
      });

      if (gpc) {
        // Treat GPC as do-not-sell/limit tracking: opt-out of marketing/analytics
        this.saveConsent({ analytics: false, marketing: false, functional: true });
        if (this.banner) this.banner.remove();
      }
    }

    openModal() {
      // Create modal if needed
      if (!this.modal) {
        this.modal = document.createElement('div');
        this.modal.className = 'cookie-consent-modal';
        this.modal.innerHTML = `
          <div class="cc-modal-content" role="dialog" aria-modal="true" aria-labelledby="cc-modal-title">
            <button class="cc-modal-close" aria-label="Close cookie preferences">&times;</button>
            <h2 id="cc-modal-title">Cookie Preferences</h2>
            <p>Choose which cookies you allow us to use.</p>
            <form id="cc-preferences">
              <div>
                <label><input type="checkbox" name="functional" checked disabled> Essential/Functional (required)</label>
              </div>
              <div>
                <label><input type="checkbox" name="analytics"> Analytics</label>
              </div>
              <div>
                <label><input type="checkbox" name="marketing"> Marketing</label>
              </div>
              <div style="margin-top:12px; text-align:right;">
                <button type="button" class="cc-btn secondary" id="cc-cancel">Cancel</button>
                <button type="submit" class="cc-btn" id="cc-save">Save preferences</button>
              </div>
            </form>
          </div>
        `;
        document.body.appendChild(this.modal);
        const modalClose = this.modal.querySelector('.cc-modal-close');
        const modalEl = this.modal;
        modalClose.addEventListener('click', () => this.closeModalAndRestoreFocus());
        modalEl.addEventListener('click', (e) => { if (e.target === modalEl) this.closeModalAndRestoreFocus(); });
        this.modal.querySelector('#cc-cancel').addEventListener('click', () => this.closeModalAndRestoreFocus());
        this.modal.querySelector('#cc-preferences').addEventListener('submit', (e) => {
          e.preventDefault();
          const form = e.target;
          const analytics = form.querySelector('input[name="analytics"]').checked;
          const marketing = form.querySelector('input[name="marketing"]').checked;
          this.saveConsent({ analytics: !!analytics, marketing: !!marketing, functional: true });
          this.applyConsent({ analytics: !!analytics, marketing: !!marketing, functional: true });
          this.closeModalAndRestoreFocus();
          if (this.banner) this.banner.remove();
        });

        // Focus trap variables
        this._focusableElementsString = 'a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, [tabindex]:not([tabindex="-1"])';
        this._firstFocusable = null;
        this._lastFocusable = null;
        this._previousActive = null;
      }
      this._previousActive = document.activeElement;
      this.modal.style.display = 'flex';
      // setup focus trap
      setTimeout(() => {
        const focusable = this.modal.querySelectorAll(this._focusableElementsString);
        if (focusable.length) {
          this._firstFocusable = focusable[0];
          this._lastFocusable = focusable[focusable.length -1];
          this._firstFocusable.focus();
        }
        document.addEventListener('keydown', this._trapHandler = (e) => this._handleTrap(e));
      }, 50);
    }

    _handleTrap(e) {
      if (e.key === 'Tab') {
        if (e.shiftKey) { // Shift + Tab
          if (document.activeElement === this._firstFocusable) {
            e.preventDefault();
            this._lastFocusable.focus();
          }
        } else {
          if (document.activeElement === this._lastFocusable) {
            e.preventDefault();
            this._firstFocusable.focus();
          }
        }
      }
      if (e.key === 'Escape') {
        this.closeModalAndRestoreFocus();
      }
    }

    closeModalAndRestoreFocus() {
      if (this.modal) this.modal.style.display = 'none';
      document.removeEventListener('keydown', this._trapHandler);
      if (this._previousActive) this._previousActive.focus();
    }

    saveConsent(obj) {
      const payload = {
        preferences: obj,
        ts: new Date().toISOString()
      };
      localStorage.setItem(this.key, JSON.stringify(payload));
      // Optionally, send to server for logging via fetch
      try {
        navigator.sendBeacon && navigator.sendBeacon('/forms/log-consent.php', JSON.stringify(payload));
      } catch (e) { /* ignore */ }
    }

    getConsent() {
      const raw = localStorage.getItem(this.key);
      if (!raw) return null;
      try { return JSON.parse(raw).preferences; } catch { return null; }
    }

    applyConsent(prefs) {
      // Push to dataLayer for GTM and tracking
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({ event: 'consent_updated', preferences: prefs });

      // Update Google Consent Mode via gtag if available
      if (typeof window.gtag === 'function') {
        try {
          window.gtag('consent', 'update', {
            'ad_storage': prefs.marketing ? 'granted' : 'denied',
            'analytics_storage': prefs.analytics ? 'granted' : 'denied'
          });
        } catch (e) { console.warn('gtag consent update failed', e); }
      }

      // Disable analytics flags for legacy GA
      if (!prefs.analytics) {
        window['ga-disable-UA'] = true;
      }

      // Lazy-load functional scripts if functional consent is true
      if (prefs.functional) {
        this._loadFunctionalFeatures();
      } else {
        // Optionally remove or avoid loading functional features
      }

      // Expose preferences for other scripts
      window.cookieConsent = prefs;
    }

    _loadFunctionalFeatures() {
      // Example: load reCAPTCHA if a placeholder exists
      const recaptchaPlaceholder = document.getElementById('recaptcha-placeholder');
      if (recaptchaPlaceholder && !recaptchaPlaceholder.dataset.loaded) {
        const script = document.createElement('script');
        script.src = 'https://www.google.com/recaptcha/api.js?onload=__izende_recaptcha_loaded&render=explicit';
        script.async = true;
        // define a global callback to render recaptcha into the placeholder once grecaptcha is ready
        window.__izende_recaptcha_loaded = function() {
          try {
            const widget = recaptchaPlaceholder.querySelector('.g-recaptcha');
            if (typeof grecaptcha !== 'undefined' && widget) {
              // ensure visible and render
              widget.style.display = '';
              grecaptcha.render(widget, { 'sitekey': widget.getAttribute('data-sitekey') });
            }
          } catch (e) { console.warn('recaptcha render failed', e); }
          recaptchaPlaceholder.dataset.loaded = '1';
        };
        document.head.appendChild(script);
      }

      // Load maps when a placeholder exists
      const mapPlaceholder = document.getElementById('map-placeholder');
      if (mapPlaceholder && !mapPlaceholder.dataset.loaded) {
        // If you use an iframe, we can set src from data-src
        const iframe = mapPlaceholder.querySelector('iframe[data-src]');
        if (iframe) {
          iframe.src = iframe.getAttribute('data-src');
          mapPlaceholder.dataset.loaded = '1';
        }
      }

      // Load Tawk.to or chat widgets if functional consent is given
      if (window.__tawk_to_src && !window.__tawk_loaded) {
        const s = document.createElement('script');
        s.src = window.__tawk_to_src;
        s.async = true;
        s.onload = () => { window.__tawk_loaded = true; };
        document.body.appendChild(s);
      }
  }

  // CookieConsent is initialized in the main load handler above

  /**
   * WordPress Blog Integration
   */
  class WordPressBlog {
    constructor(apiUrl) {
      this.apiUrl = apiUrl;
      this.postsPerPage = 9;
      this.currentPage = 1;
      this.currentCategory = '';
      this.currentSearch = '';
      this.isLoading = false;
    }

    async fetchPosts(page = 1, category = '', search = '') {
      if (this.isLoading) return null;

      this.isLoading = true;
      this.currentPage = page;
      this.currentCategory = category;
      this.currentSearch = search;

      try {
        let url = this.apiUrl + '/wp-json/wp/v2/posts?per_page=' + this.postsPerPage + '&page=' + page + '&_embed';

        if (category) {
          url += '&categories=' + category;
        }

        if (search) {
          url += '&search=' + encodeURIComponent(search);
        }

        const response = await fetch(url);

        if (!response.ok) {
          throw new Error('Failed to fetch posts');
        }

        const posts = await response.json();
        const totalPages = parseInt(response.headers.get('X-WP-TotalPages')) || 1;

        this.isLoading = false;
        return { posts, totalPages };
      } catch (error) {
        console.error('Error fetching posts:', error);
        this.isLoading = false;
        return null;
      }
    }

    async fetchCategories() {
      try {
        const response = await fetch(this.apiUrl + '/wp-json/wp/v2/categories?per_page=100&hide_empty=true');

        if (!response.ok) {
          throw new Error('Failed to fetch categories');
        }

        return await response.json();
      } catch (error) {
        console.error('Error fetching categories:', error);
        return [];
      }
    }

    renderPost(post) {
      const title = post.title.rendered;
      const excerpt = post.excerpt.rendered.replace(/<\/?[^>]+(>|$)/g, "").substring(0, 150) + '...';
      const date = new Date(post.date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
      const featuredImage = post._embedded && post._embedded['wp:featuredmedia'] && post._embedded['wp:featuredmedia'][0] ? post._embedded['wp:featuredmedia'][0].source_url : '/assets/img/blog-placeholder.jpg';
      const categories = post._embedded && post._embedded['wp:term'] && post._embedded['wp:term'][0] ? post._embedded['wp:term'][0] : [];
      const categoryName = categories.length > 0 ? categories[0].name : 'Uncategorized';
      const postUrl = '/articles/' + post.slug;

      return '<div class="col-lg-4 col-md-6 mb-4"><article class="blog-card"><img src="' + featuredImage + '" alt="' + title + '" class="blog-card-image" loading="lazy"><div class="blog-card-content"><span class="blog-card-category">' + categoryName + '</span><h3>' + title + '</h3><p>' + excerpt + '</p><div class="blog-card-meta"><span><i class="bx bx-calendar"></i> ' + date + '</span></div><a href="' + postUrl + '" class="blog-card-link">Read More <i class="bx bx-right-arrow-alt"></i></a></div></article></div>';
    }

    renderPagination(currentPage, totalPages) {
      if (totalPages <= 1) return '';

      let html = '<ul>';

      html += '<li><button ' + (currentPage === 1 ? 'disabled' : '') + ' data-page="' + (currentPage - 1) + '">Previous</button></li>';

      for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
          html += '<li><button class="' + (i === currentPage ? 'active' : '') + '" data-page="' + i + '">' + i + '</button></li>';
        } else if (i === currentPage - 3 || i === currentPage + 3) {
          html += '<li><span>...</span></li>';
        }
      }

      html += '<li><button ' + (currentPage === totalPages ? 'disabled' : '') + ' data-page="' + (currentPage + 1) + '">Next</button></li>';

      html += '</ul>';
      return html;
    }

    renderEmptyState() {
      return '<div class="col-12"><div class="blog-empty-state"><i class="bx bx-search-alt"></i><h3>No posts found</h3><p>Try adjusting your search or filter to find what you are looking for.</p></div></div>';
    }
  }

  /**
   * Initialize Homepage Blog
   */
  async function loadHomepageBlog() {
    const container = document.getElementById('homepage-blog-posts');
    if (!container) return;

    const blog = new WordPressBlog('https://izendestudioweb.com/articles');

    try {
      const result = await blog.fetchPosts(1, '', '');

      if (!result || !result.posts || result.posts.length === 0) {
        container.innerHTML = blog.renderEmptyState();
        return;
      }

      const homepagePosts = result.posts.slice(0, 3);
      container.innerHTML = homepagePosts.map(post => blog.renderPost(post)).join('');
    } catch (error) {
      console.error('Error loading homepage blog:', error);
      container.innerHTML = blog.renderEmptyState();
    }
  }

  /**
   * Initialize Blog Landing Page
   */
  async function initBlogPage() {
    const postsContainer = document.getElementById('blog-posts-container');
    if (!postsContainer) return;

    const paginationContainer = document.getElementById('blog-pagination');
    const searchInput = document.getElementById('blog-search');
    const categoryFilter = document.getElementById('category-filter');

    const blog = new WordPressBlog('https://izendestudioweb.com/articles');

    const categories = await blog.fetchCategories();
    if (categories && categories.length > 0 && categoryFilter) {
      categories.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat.id;
        option.textContent = cat.name;
        categoryFilter.appendChild(option);
      });
    }

    async function loadPosts(page, category, search) {
      postsContainer.setAttribute('aria-busy', 'true');

      const result = await blog.fetchPosts(page || 1, category || '', search || '');

      postsContainer.removeAttribute('aria-busy');

      if (!result || !result.posts || result.posts.length === 0) {
        postsContainer.innerHTML = blog.renderEmptyState();
        if (paginationContainer) paginationContainer.innerHTML = '';
        return;
      }

      postsContainer.innerHTML = result.posts.map(post => blog.renderPost(post)).join('');

      if (paginationContainer) {
        paginationContainer.innerHTML = blog.renderPagination(page || 1, result.totalPages);

        paginationContainer.querySelectorAll('button[data-page]').forEach(btn => {
          btn.addEventListener('click', () => {
            const newPage = parseInt(btn.getAttribute('data-page'));
            loadPosts(newPage, category, search);
            window.scrollTo({ top: 0, behavior: 'smooth' });
          });
        });
      }
    }

    let searchTimeout;
    if (searchInput) {
      searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
          loadPosts(1, categoryFilter ? categoryFilter.value : '', e.target.value);
        }, 500);
      });
    }

    if (categoryFilter) {
      categoryFilter.addEventListener('change', (e) => {
        loadPosts(1, e.target.value, searchInput ? searchInput.value : '');
      });
    }

    loadPosts(1, '', '');
  }

  /**
   * Auto-initialize blog components
   */
  document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('homepage-blog-posts')) {
      loadHomepageBlog();
    }

    if (document.getElementById('blog-posts-container')) {
      initBlogPage();
    }
  });

  /**
   * Phase 15: Performance & Polish Enhancements
   */

  /**
   * Lazy Loading Enhancement (Native + Fallback)
   */
  class LazyLoader {
    constructor() {
      this.images = document.querySelectorAll('img[data-src]');
      this.init();
    }

    init() {
      // Use native lazy loading if available
      if ('loading' in HTMLImageElement.prototype) {
        this.images.forEach(img => {
          img.src = img.dataset.src;
          img.removeAttribute('data-src');
        });
      } else {
        // Fallback to IntersectionObserver
        this.observer = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              this.loadImage(entry.target);
            }
          });
        }, { rootMargin: '50px' });

        this.images.forEach(img => this.observer.observe(img));
      }
    }

    loadImage(img) {
      img.src = img.dataset.src;
      img.classList.add('loaded');
      img.removeAttribute('data-src');
      if (this.observer) this.observer.unobserve(img);
    }
  }

  /**
   * Ripple Effect on Buttons
   */
  function addRippleEffect(button, event) {
    // Check for reduced motion
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      return;
    }

    button.classList.add('ripple-active');
    setTimeout(() => {
      button.classList.remove('ripple-active');
    }, 600);
  }

  // Apply ripple to all buttons with .btn-ripple class
  document.addEventListener('click', function(e) {
    const button = e.target.closest('.btn-ripple');
    if (button) {
      addRippleEffect(button, e);
    }
  });

  /**
   * Dark Mode Toggle (Optional)
   */
  class DarkMode {
    constructor() {
      this.key = 'izende_theme_preference';
      this.toggle = document.getElementById('dark-mode-toggle');
      if (!this.toggle) return;

      this.init();
    }

    init() {
      // Check for saved preference or system preference
      const savedTheme = localStorage.getItem(this.key);
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      const theme = savedTheme || (prefersDark ? 'dark' : 'light');

      this.applyTheme(theme);

      // Toggle button click
      this.toggle.addEventListener('click', () => this.toggleTheme());

      // Listen for system preference changes
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
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
      // Add transition class
      document.body.classList.add('theme-transition');

      this.applyTheme(theme);
      localStorage.setItem(this.key, theme);

      // Remove transition class after animation
      setTimeout(() => {
        document.body.classList.remove('theme-transition');
      }, 300);
    }

    applyTheme(theme) {
      document.documentElement.setAttribute('data-theme', theme);
      if (this.toggle) {
        this.toggle.innerHTML = theme === 'dark' ? '<i class="bx bx-sun"></i>' : '<i class="bx bx-moon"></i>';
        this.toggle.setAttribute('aria-label', `Switch to ${theme === 'dark' ? 'light' : 'dark'} mode`);
      }
    }

    getTheme() {
      return document.documentElement.getAttribute('data-theme') || 'light';
    }
  }

  /**
   * Scroll Progress Indicator
   */
  class ScrollProgress {
    constructor() {
      this.progressBar = document.getElementById('scroll-progress');
      if (!this.progressBar) {
        // Create if doesn't exist
        this.progressBar = document.createElement('div');
        this.progressBar.id = 'scroll-progress';
        document.body.appendChild(this.progressBar);
      }
      this.init();
    }

    init() {
      // Only show on long pages
      if (document.documentElement.scrollHeight <= window.innerHeight * 2) {
        return;
      }

      // Check for reduced motion
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
      }

      window.addEventListener('scroll', () => this.update(), { passive: true });
      this.update();
    }

    update() {
      const scrollTop = window.scrollY;
      const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
      const scrollPercent = (scrollTop / scrollHeight) * 100;
      this.progressBar.style.width = scrollPercent + '%';
    }
  }

  /**
   * Image Load Error Handling with Loop Prevention
   */
  document.addEventListener('error', function(e) {
    if (e.target.tagName === 'IMG') {
      const placeholderUrl = '/assets/img/placeholder.jpg';
      // Guard against infinite loop if placeholder itself fails
      if (e.target.src === placeholderUrl || e.target.dataset.errorHandled) {
        e.target.style.display = 'none';
        return;
      }
      // Mark as handled and replace with placeholder
      e.target.dataset.errorHandled = 'true';
      e.target.src = placeholderUrl;
      e.target.alt = 'Image unavailable';
      console.warn('Image load error:', e.target.src);
    }
  }, true);

  /**
   * Performance Monitoring (Core Web Vitals)
   */
  class PerformanceMonitor {
    constructor() {
      this.metrics = {};
      this.init();
    }

    init() {
      if (!('PerformanceObserver' in window)) return;

      // Track Largest Contentful Paint (LCP)
      try {
        const lcpObserver = new PerformanceObserver((list) => {
          const entries = list.getEntries();
          const lastEntry = entries[entries.length - 1];
          this.metrics.lcp = lastEntry.renderTime || lastEntry.loadTime;
          this.log('LCP', this.metrics.lcp);
        });
        lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });
      } catch (e) { /* ignore */ }

      // Track First Input Delay (FID)
      try {
        const fidObserver = new PerformanceObserver((list) => {
          list.getEntries().forEach(entry => {
            this.metrics.fid = entry.processingStart - entry.startTime;
            this.log('FID', this.metrics.fid);
          });
        });
        fidObserver.observe({ entryTypes: ['first-input'] });
      } catch (e) { /* ignore */ }

      // Track Cumulative Layout Shift (CLS)
      try {
        let clsScore = 0;
        const clsObserver = new PerformanceObserver((list) => {
          list.getEntries().forEach(entry => {
            if (!entry.hadRecentInput) {
              clsScore += entry.value;
            }
          });
          this.metrics.cls = clsScore;
          this.log('CLS', this.metrics.cls);
        });
        clsObserver.observe({ entryTypes: ['layout-shift'] });
      } catch (e) { /* ignore */ }
    }

    log(metric, value) {
      console.log(`[Performance] ${metric}:`, value.toFixed(2));

      // Send to analytics if available
      if (typeof window.dataLayer !== 'undefined') {
        window.dataLayer.push({
          event: 'web_vitals',
          metric: metric,
          value: Math.round(value)
        });
      }
    }
  }

  /**
   * Accessibility Announcer
   */
  function announce(message, priority = 'polite') {
    let liveRegion = document.getElementById('aria-live-region');
    if (!liveRegion) {
      liveRegion = document.createElement('div');
      liveRegion.id = 'aria-live-region';
      liveRegion.setAttribute('role', 'status');
      liveRegion.setAttribute('aria-live', priority);
      liveRegion.setAttribute('aria-atomic', 'true');
      document.body.appendChild(liveRegion);
    }

    liveRegion.textContent = message;

    // Clear after 1 second
    setTimeout(() => {
      liveRegion.textContent = '';
    }, 1000);
  }

  // Make announce function globally available
  window.announce = announce;

  /**
   * Enhanced IntersectionObserver for Multiple Uses
   */
  class EnhancedObserver {
    constructor() {
      this.observers = {};
      this.init();
    }

    init() {
      // Lazy load images
      this.observeImages();

      // Animate elements on scroll (if AOS not present)
      if (typeof AOS === 'undefined') {
        this.observeAnimations();
      }
    }

    observeImages() {
      const lazyImages = document.querySelectorAll('img[loading="lazy"]');
      if (lazyImages.length === 0) return;

      const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const img = entry.target;
            img.classList.add('loaded');
            imageObserver.unobserve(img);
          }
        });
      }, { rootMargin: '50px' });

      lazyImages.forEach(img => imageObserver.observe(img));
    }

    observeAnimations() {
      const animatedElements = document.querySelectorAll('[data-animate]');
      if (animatedElements.length === 0) return;

      // Check for reduced motion
      const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      if (prefersReducedMotion) return;

      const animationObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const element = entry.target;
            const animation = element.getAttribute('data-animate');
            element.classList.add('anim-complete', animation);
            animationObserver.unobserve(element);
          }
        });
      }, { threshold: 0.1 });

      animatedElements.forEach(el => animationObserver.observe(el));
    }
  }

  /**
   * Keyboard Shortcuts
   */
  document.addEventListener('keydown', function(e) {
    // Don't trigger if user is typing in input
    if (e.target.matches('input, textarea, select')) return;

    // '/' key: Focus search input
    if (e.key === '/') {
      e.preventDefault();
      const searchInput = document.getElementById('blog-search');
      if (searchInput) {
        searchInput.focus();
        announce('Search field focused', 'polite');
      }
    }

    // 'Escape' key: Close modals, dropdowns, etc (already handled by specific components)
    // Aria be handled in individual modal/dropdown handlers
  });

  /**
   * Enhanced will-change Performance Optimization
   */
  function optimizeAnimations() {
    const animatedElements = document.querySelectorAll('[data-aos], .portfolio-wrap, .blog-card, .icon-box');

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('will-animate');
        } else {
          entry.target.classList.remove('will-animate');
        }
      });
    }, { rootMargin: '200px' });

    animatedElements.forEach(el => observer.observe(el));

    // Remove will-change after animation completes
    document.addEventListener('animationend', function(e) {
      if (e.target.classList.contains('will-animate')) {
        e.target.classList.remove('will-animate');
        e.target.classList.add('anim-complete');
      }
    });
  }

  /**
   * Initialize All Phase 15 Enhancements
   */
  window.addEventListener('load', () => {
    // Initialize LazyLoader for images with data-src
    new LazyLoader();

    // Initialize Dark Mode (if toggle exists)
    new DarkMode();

    // Initialize Scroll Progress
    new ScrollProgress();

    // Initialize Performance Monitor
    new PerformanceMonitor();

    // Initialize Enhanced Observer
    new EnhancedObserver();

    // Optimize animations with will-change
    optimizeAnimations();

    // Add ripple class to all primary buttons
    document.querySelectorAll('.btn-brand, .btn-primary, .cta-btn, .btn-get-started').forEach(btn => {
      btn.classList.add('btn-ripple');
    });

    // Announce page load complete to screen readers
    announce('Page loaded successfully', 'polite');
  });

  // AOS initialization remains at lines 988-996 (already implemented)

})();

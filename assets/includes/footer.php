<?php
// Detect if we're in a subdirectory
$base_path = (basename(dirname($_SERVER['SCRIPT_FILENAME'])) !== 'izendestudioweb') ? '../' : '';
?>
  <!-- ======= Footer ======= -->
  <footer id="footer">
    <div class="container">
      <!-- Newsletter Signup -->
      <div class="newsletter-signup" style="max-width: 600px; margin: 0 auto 40px;">
        <h3><i class="bx bx-envelope"></i> Subscribe to Our Newsletter</h3>
        <p>Get web development tips, SEO insights, and special offers delivered to your inbox!</p>
        <form id="newsletterForm" style="margin-top: 20px;">
          <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
            <input type="email"
                   name="email"
                   placeholder="Enter your email address"
                   required
                   style="flex: 1; min-width: 250px; padding: 12px 20px; border: none; border-radius: 50px; font-size: 15px;">
            <button type="submit"
                    style="padding: 12px 30px; background: linear-gradient(45deg, #5cb874 0%, #4aa360 100%); color: white; border: none; border-radius: 50px; font-weight: bold; cursor: pointer; transition: all 0.3s;">
              Subscribe
            </button>
          </div>
          <div id="newsletterMessage" style="margin-top: 15px; font-size: 14px;"></div>
        </form>
      </div>

      <h3>Follow Us</h3>
      <p>Connect with us on social media!</p>
      <div class="social-links">
        <a href="https://twitter.com/IzendeWeb" target="_blank" class="twitter"><i class="bx bxl-twitter"></i></a>
        <a href="https://www.facebook.com/Izende-Studio-Web-109880234906868" target="_blank" class="facebook"><i class="bx bxl-facebook"></i></a>
        <!-- <a href="#" class="instagram"><i class="bx bxl-instagram"></i></a> -->
        <!-- <a href="https://web.whatsapp.com" target="_blank" class="google-plus"><i class="bx bxl-whatsapp"></i></a> -->
        <a href="https://www.linkedin.com/company/izende-studio-web" target="_blank" class="linkedin"><i class="bx bxl-linkedin"></i></a>
      </div>

      <!-- Service Area Information -->
      <div class="footer-service-area" style="margin-top: 30px; margin-bottom: 20px;">
        <p style="font-size: 14px; color: #fff; margin-bottom: 10px;">
          <i class="bx bx-map" style="margin-right: 5px;"></i>
          <strong>Serving St. Louis Metro, Missouri & Illinois</strong>
        </p>
        <p style="font-size: 13px; color: rgba(255,255,255,0.8); margin-bottom: 0;">
          Professional web design, hosting, and digital marketing services for businesses throughout the St. Louis region and beyond.
        </p>
      </div>

      <!-- Optional: NAP Consistency Block (uncomment if desired) -->
      <!--
      <div class="footer-contact" style="margin-top: 20px; margin-bottom: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 30px; font-size: 13px; color: rgba(255,255,255,0.8);">
          <div>
            <i class="bx bx-map" style="margin-right: 5px; color: #5cb874;"></i>
            <span>St. Louis, MO 63156</span>
          </div>
          <div>
            <i class="bx bx-phone" style="margin-right: 5px; color: #5cb874;"></i>
            <a href="tel:314-312-6441" style="color: rgba(255,255,255,0.8);">+1 314.312.6441</a>
          </div>
          <div>
            <i class="bx bx-envelope" style="margin-right: 5px; color: #5cb874;"></i>
            <a href="mailto:support@izendestudioweb.com" style="color: rgba(255,255,255,0.8);">support@izendestudioweb.com</a>
          </div>
        </div>
      </div>
      -->

      <div class="footer-legal-links" style="margin-top:18px; margin-bottom:12px;">
        <nav aria-label="Legal">
          <a href="<?php echo $base_path; ?>privacy-policy.php" class="text-white me-3" aria-label="Privacy Policy">Privacy</a>
          <a href="<?php echo $base_path; ?>terms-of-service.php" class="text-white me-3" aria-label="Terms of Service">Terms</a>
          <a href="<?php echo $base_path; ?>cookie-policy.php" class="text-white me-3" aria-label="Cookie Policy">Cookies</a>
          <a href="<?php echo $base_path; ?>refund-policy.php" class="text-white me-3" aria-label="Refund Policy">Refunds</a>
          <a href="<?php echo $base_path; ?>service-level-agreement.php" class="text-white me-3" aria-label="Service Level Agreement">SLA</a>
          <a href="<?php echo $base_path; ?>accessibility-statement.php" class="text-white me-3" aria-label="Accessibility Statement">Accessibility</a>
          <a href="<?php echo $base_path; ?>do-not-sell.php" class="text-white me-3" aria-label="Do Not Sell or Share">Do Not Sell</a>
          <a href="#" id="cookie-settings-link" class="text-white" aria-label="Cookie Settings">Cookie Settings</a>
        </nav>
      </div>

      <div class="copyright">
        &copy; Copyright <strong><span>Izende Studio Web</span></strong>. All Rights Reserved
      </div>

    </div>
  </footer><!-- End Footer -->
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="<?php echo $base_path; ?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js" defer></script>
    <script src="<?php echo $base_path; ?>assets/vendor/glightbox/js/glightbox.min.js" defer></script>
    <script src="<?php echo $base_path; ?>assets/vendor/isotope-layout/isotope.pkgd.min.js" defer></script>
    <script src="<?php echo $base_path; ?>assets/vendor/php-email-form/validate.js" defer></script>
    <script src="https://unpkg.com/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer crossorigin=""></script>

    <!-- Hero Carousel Script -->
    <script>
    function initHeroCarousel() {
        if (typeof Swiper === 'undefined') {
            // If Swiper not loaded yet, retry
            setTimeout(initHeroCarousel, 100);
            return;
        }

        const heroCarousel = new Swiper('.hero-carousel', {
            loop: false,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            effect: 'fade',
            speed: 1000,
            fadeEffect: {
                crossFade: true
            }
        });
        console.log('Hero carousel initialized');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeroCarousel);
    } else {
        initHeroCarousel();
    }
    </script>

    <!-- Service Area Map -->
    <script>
    (function() {
        const mapContainer = document.getElementById('service-area-map');
        if (!mapContainer) {
            return;
        }

        const stLouis = [38.6270, -90.1994];
        const serviceRadiusMeters = 32187; // ~20 miles

        const initMap = () => {
            if (typeof L === 'undefined') {
                setTimeout(initMap, 100);
                return;
            }

            const map = L.map(mapContainer, {
                center: stLouis,
                zoom: 11,
                scrollWheelZoom: false
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
                maxZoom: 18,
                minZoom: 6
            }).addTo(map);

            L.circle(stLouis, {
                radius: serviceRadiusMeters,
                color: '#3a6ff3',
                weight: 2,
                fillColor: '#3a6ff3',
                fillOpacity: 0.18
            }).addTo(map);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMap);
        } else {
            initMap();
        }
    })();
    </script>

    <!-- Template Main JS File -->
    <script src="<?php echo $base_path; ?>assets/js/main.js" defer></script>

    <!-- Newsletter Signup Script -->
    <script>
    document.getElementById('newsletterForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();

        const form = this;
        const submitBtn = form.querySelector('button[type="submit"]');
        const messageDiv = document.getElementById('newsletterMessage');
        const emailInput = form.querySelector('input[name="email"]');

        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.textContent = 'Subscribing...';
        messageDiv.textContent = '';
        messageDiv.className = '';

        try {
            const response = await fetch('<?php echo $base_path; ?>api/newsletter-signup.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    email: emailInput.value,
                    source: 'footer_form'
                })
            });

            const data = await response.json();

            if (data.success) {
                messageDiv.textContent = data.message;
                messageDiv.style.color = '#5cb874';
                emailInput.value = '';

                // Track newsletter signup in analytics
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'newsletter_signup', {
                        'method': 'footer_form'
                    });
                }
            } else {
                messageDiv.textContent = data.message;
                messageDiv.style.color = '#ff6b6b';
            }
        } catch (error) {
            messageDiv.textContent = 'An error occurred. Please try again.';
            messageDiv.style.color = '#ff6b6b';
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Subscribe';
        }
    });
    </script>

    <!-- Blog Page Script -->
    <?php if (basename($_SERVER['SCRIPT_FILENAME']) === 'blog.php'): ?>
    <script src="<?php echo $base_path; ?>assets/js/blog.js" defer></script>
    <?php endif; ?>

    <!-- Analytics Event Tracking -->
    <?php if ((CMSData::getSetting('analytics_enabled') ?? '1') == '1' && (!empty(CMSData::getSetting('google_analytics_id')) || !empty(CMSData::getSetting('google_tag_manager_id')))): ?>
    <script src="<?php echo $base_path; ?>assets/js/analytics-events.js" defer></script>
    <?php endif; ?>

    <!-- Tidio Live Chat Widget -->
    <?php include __DIR__ . '/tidio-widget.php'; ?>

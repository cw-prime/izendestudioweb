<?php
/**
 * Book Consultation Page
 */

require_once __DIR__ . '/config/env-loader.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/cms-data.php';
require_once __DIR__ . '/admin/config/database.php';
require_once __DIR__ . '/includes/SEOHelper.php';

initSecureSession();
setSecurityHeaders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

<?php
SEOHelper::outputMetaTags('book-consultation', [
    'page_title' => 'Book a Free Consultation | Izende Studio Web',
    'meta_description' => 'Schedule a free consultation call to discuss your web development, SEO, or video editing project. Quick and easy online booking.',
    'canonical_url' => 'https://izendestudioweb.com/book-consultation.php'
]);
?>

  <?php include './assets/includes/header-links.php'; ?>
</head>

<body>
  <?php include './assets/includes/header.php'; ?>

  <main id="main">
    <!-- Breadcrumbs -->
    <section class="breadcrumbs">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <h2>Book a Consultation</h2>
          <ol>
            <li><a href="index.php">Home</a></li>
            <li>Book Consultation</li>
          </ol>
        </div>
      </div>
    </section>

    <!-- Booking Section -->
    <section class="booking-section" style="padding: 60px 0;">
      <div class="container">
        <div class="row">
          <div class="col-lg-7">
            <div class="section-title">
              <h2>Schedule Your Free Consultation</h2>
              <p>Let's discuss your project! Book a 30-minute consultation call to explore how we can help grow your business.</p>
            </div>

            <div class="card shadow-sm">
              <div class="card-body p-4">
                <form id="bookingForm">
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label">Full Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" name="client_name" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Email <span class="text-danger">*</span></label>
                      <input type="email" class="form-control" name="client_email" required>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" name="client_phone" placeholder="(314) 555-1234">
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Service Interest <span class="text-danger">*</span></label>
                    <select class="form-select" name="service_type" required>
                      <option value="">Select a service...</option>
                      <option value="Web Development">Web Development</option>
                      <option value="Video Editing">Video Editing</option>
                      <option value="SEO Services">SEO Services</option>
                      <option value="Social Media Management">Social Media Management</option>
                      <option value="General Inquiry">General Inquiry</option>
                    </select>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label">Preferred Date <span class="text-danger">*</span></label>
                      <input type="date" class="form-control" name="preferred_date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Preferred Time <span class="text-danger">*</span></label>
                      <select class="form-select" name="preferred_time" required>
                        <option value="">Select time...</option>
                        <option value="09:00">9:00 AM</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="13:00">1:00 PM</option>
                        <option value="14:00">2:00 PM</option>
                        <option value="15:00">3:00 PM</option>
                        <option value="16:00">4:00 PM</option>
                      </select>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Tell us about your project</label>
                    <textarea class="form-control" name="message" rows="4" placeholder="Brief description of what you're looking for..."></textarea>
                  </div>

                  <div id="bookingMessage" class="mb-3"></div>

                  <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                    <i class="bi bi-calendar-check"></i> Book Consultation
                  </button>
                </form>
              </div>
            </div>
          </div>

          <div class="col-lg-5">
            <div class="card bg-light mt-4 mt-lg-0">
              <div class="card-body p-4">
                <h4><i class="bi bi-info-circle"></i> What to Expect</h4>
                <ul class="list-unstyled mt-3">
                  <li class="mb-3">
                    <i class="bi bi-check-circle text-success"></i>
                    <strong>30-minute video call</strong> - We'll discuss your goals and needs
                  </li>
                  <li class="mb-3">
                    <i class="bi bi-check-circle text-success"></i>
                    <strong>Free consultation</strong> - No obligation or commitment
                  </li>
                  <li class="mb-3">
                    <i class="bi bi-check-circle text-success"></i>
                    <strong>Expert advice</strong> - Get professional recommendations
                  </li>
                  <li class="mb-3">
                    <i class="bi bi-check-circle text-success"></i>
                    <strong>Custom quote</strong> - Tailored pricing for your project
                  </li>
                </ul>

                <hr>

                <h5>Need immediate assistance?</h5>
                <p class="mb-2">
                  <i class="bi bi-telephone"></i>
                  <a href="tel:314-312-6441">+1 (314) 312-6441</a>
                </p>
                <p>
                  <i class="bi bi-envelope"></i>
                  <a href="mailto:support@izendestudioweb.com">support@izendestudioweb.com</a>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include './assets/includes/footer.php'; ?>

  <script>
  document.getElementById('bookingForm').addEventListener('submit', async function(e) {
      e.preventDefault();

      const form = this;
      const submitBtn = document.getElementById('submitBtn');
      const messageDiv = document.getElementById('bookingMessage');

      // Get form data
      const formData = new FormData(form);
      const data = {
          client_name: formData.get('client_name'),
          client_email: formData.get('client_email'),
          client_phone: formData.get('client_phone'),
          service_type: formData.get('service_type'),
          preferred_date: formData.get('preferred_date') + ' ' + formData.get('preferred_time') + ':00',
          message: formData.get('message')
      };

      // Disable button
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Booking...';
      messageDiv.textContent = '';

      try {
          const response = await fetch('api/book-consultation.php', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json'
              },
              body: JSON.stringify(data)
          });

          const result = await response.json();

          if (result.success) {
              messageDiv.innerHTML = `<div class="alert alert-success"><i class="bi bi-check-circle"></i> ${result.message}</div>`;
              form.reset();

              // Track in analytics
              if (typeof gtag !== 'undefined') {
                  gtag('event', 'booking_submitted', {
                      'service_type': data.service_type
                  });
              }
          } else {
              messageDiv.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ${result.message}</div>`;
          }
      } catch (error) {
          messageDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again or call us directly.</div>';
      } finally {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="bi bi-calendar-check"></i> Book Consultation';
      }
  });
  </script>

</body>
</html>

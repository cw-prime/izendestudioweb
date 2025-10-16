<?php
require_once __DIR__ . '/config/env-loader.php';
require_once __DIR__ . '/config/security.php';
initSecureSession();
setSecurityHeaders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Data Subject Request | Izende Studio Web</title>
  <?php include 'assets/includes/header-links.php'; ?>
</head>
<body>
  <?php include 'assets/includes/topbar.php'; ?>
  <?php include 'assets/includes/header.php'; ?>
  <main id="main">
    <section class="breadcrumbs">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <h2>Data Subject Request</h2>
          <ol>
            <li><a href="/">Home</a></li>
            <li>Data Subject Request</li>
          </ol>
        </div>
      </div>
    </section>

    <section class="legal-content">
      <div class="container">
        <h2>Submit a Data Subject Request</h2>
        <p>Use the form below to request access, deletion, correction, or portability of your personal data. We'll respond within the timeframes required by applicable law.</p>
        <form method="post" action="/forms/data-subject-request.php">
          <div class="mb-3">
            <label for="dsr_email" class="form-label">Email</label>
            <input id="dsr_email" name="email" type="email" class="form-control" required>
          </div>
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
          <div class="mb-3">
            <label for="dsr_name" class="form-label">Full name</label>
            <input id="dsr_name" name="fullname" type="text" class="form-control">
          </div>
          <div class="mb-3">
            <label for="dsr_type" class="form-label">Request type</label>
            <select id="dsr_type" name="request_type" class="form-select" required>
              <option value="access">Access my data</option>
              <option value="delete">Request deletion</option>
              <option value="rectify">Rectify / correct</option>
              <option value="portability">Data portability</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="dsr_details" class="form-label">Details</label>
            <textarea id="dsr_details" name="details" class="form-control" rows="4"></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Submit Request</button>
        </form>
      </div>
    </section>

  </main>
  <?php include 'assets/includes/footer.php'; ?>
</body>
</html>

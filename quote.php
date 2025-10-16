<?php
/**
 * Quote Request Form
 * Secured with CSRF protection, rate limiting, and input validation
 */

// Load security infrastructure
require_once __DIR__ . '/config/env-loader.php';
require_once __DIR__ . '/config/security.php';

// Initialize secure session and set security headers
initSecureSession();
setSecurityHeaders();

// Initialize variables
$fname = '';
$lname = '';
$email = '';
$phone = '';
$company = '';
$website = '';
$companySize = '';
$industry = '';
$companyService = '';
$companyBudget = '';
$comment = '';
$name = '';
$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sendMailbtn'])) {

    // 1. CSRF Token Validation
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        logSecurityEvent('csrf_validation_failed', [
            'form' => 'quote',
            'ip' => getClientIP()
        ], 'WARNING');
        $errors[] = 'Invalid security token. Please refresh the page and try again.';
    } else {

        // 2. Rate Limiting
        $identifier = getClientIP() . '_quote_form';
        if (!checkRateLimit($identifier, 5, 300)) {
            logSecurityEvent('rate_limit_exceeded', [
                'form' => 'quote',
                'ip' => getClientIP()
            ], 'WARNING');
            $errors[] = 'Too many requests. Please try again in 5 minutes.';
        } else {

            // 3. Input Validation & Sanitization

            // Required consent checkbox (must be present and checked)
            if (!isset($_POST['consent']) || ($_POST['consent'] !== 'on' && $_POST['consent'] !== '1' && $_POST['consent'] !== 'yes')) {
                $errors[] = 'You must agree to the privacy policy to submit this form.';
            }

            // Marketing consent (optional) - record opt-in if provided
            $marketing_consent = 0;
            if (isset($_POST['marketing_consent'])) {
                $val = $_POST['marketing_consent'];
                if ($val === '1' || $val === 'on' || $val === 'true' || $val === 'yes') {
                    $marketing_consent = 1;
                }
            }

            // First Name
            $fname = sanitizeInput($_POST['fname'] ?? '', 'string');
            if (empty($fname)) {
                $errors[] = 'First name is required.';
            } elseif (!validateLength($fname, 1, 50)) {
                $errors[] = 'First name must be between 1 and 50 characters.';
            }

            // Last Name
            $lname = sanitizeInput($_POST['lname'] ?? '', 'string');
            if (empty($lname)) {
                $errors[] = 'Last name is required.';
            } elseif (!validateLength($lname, 1, 50)) {
                $errors[] = 'Last name must be between 1 and 50 characters.';
            }

            // Email
            $emailInput = sanitizeInput($_POST['email'] ?? '', 'string');
            $email = validateEmail($emailInput);
            if ($email === false) {
                $errors[] = 'Please enter a valid email address.';
            }

            // Phone
            $phoneInput = sanitizeInput($_POST['phone'] ?? '', 'string');
            $phone = validatePhone($phoneInput);
            if ($phone === false) {
                $errors[] = 'Please enter a valid phone number (format: 123-456-7890).';
            }

            // Company Name
            $company = sanitizeInput($_POST['company'] ?? '', 'string');
            if (!empty($company) && !validateLength($company, 0, 100)) {
                $errors[] = 'Company name must not exceed 100 characters.';
            }

            // Website
            $website = sanitizeInput($_POST['website'] ?? '', 'url');
            if (empty($website)) {
                $errors[] = 'Website URL is required.';
            } elseif (!filter_var($website, FILTER_VALIDATE_URL)) {
                $errors[] = 'Please enter a valid website URL.';
            }

            // Company Size
            $companySize = sanitizeInput($_POST['selectSize'] ?? '', 'int');
            if (!in_array($companySize, ['1', '2', '3', '4', '5'], true)) {
                $errors[] = 'Please select a valid company size.';
            }

            // Service
            $companyService = sanitizeInput($_POST['selectService'] ?? '', 'int');
            if (!in_array($companyService, ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'], true)) {
                $errors[] = 'Please select a valid service.';
            }

            // Budget
            $companyBudget = sanitizeInput($_POST['selectBudget'] ?? '', 'int');
            if (!in_array($companyBudget, ['1', '2', '3', '4', '5'], true)) {
                $errors[] = 'Please select a valid budget range.';
            }

            // Industry
            $industry = sanitizeInput($_POST['industry'] ?? '', 'string');
            if (empty($industry)) {
                $errors[] = 'Industry is required.';
            } elseif (!validateLength($industry, 1, 100)) {
                $errors[] = 'Industry must be between 1 and 100 characters.';
            }

            // Comment
            $comment = sanitizeInput($_POST['comment'] ?? '', 'string');
            if (empty($comment)) {
                $errors[] = 'Please tell us about your business.';
            } elseif (!validateLength($comment, 1, 2000)) {
                $errors[] = 'Comment must be between 1 and 2000 characters.';
            }

            // 4. reCAPTCHA Verification
            if (empty($errors) && isset($_POST['g-recaptcha-response'])) {
                $recaptchaSecret = getEnv('RECAPTCHA_SECRET_KEY');
                $recaptchaResponse = $_POST['g-recaptcha-response'];

                if (empty($recaptchaSecret)) {
                    $errors[] = 'reCAPTCHA is not configured properly. Please contact support.';
                    logSecurityEvent('recaptcha_not_configured', ['form' => 'quote'], 'CRITICAL');
                } else {
                    // Verify reCAPTCHA
                    $verifyURL = 'https://www.google.com/recaptcha/api/siteverify';
                    $postData = [
                        'secret' => $recaptchaSecret,
                        'response' => $recaptchaResponse,
                        'remoteip' => getClientIP()
                    ];

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $verifyURL);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if ($response === false || $httpCode !== 200) {
                        $errors[] = 'Failed to verify reCAPTCHA. Please try again.';
                        logSecurityEvent('recaptcha_verification_failed', [
                            'form' => 'quote',
                            'http_code' => $httpCode
                        ], 'WARNING');
                    } else {
                        $result = json_decode($response, true);

                        if (!isset($result['success']) || $result['success'] !== true) {
                            $errors[] = 'reCAPTCHA verification failed. Please try again.';
                            logSecurityEvent('recaptcha_failed', [
                                'form' => 'quote',
                                'result' => $result
                            ], 'WARNING');
                        }
                    }
                }
            } elseif (empty($errors)) {
                $errors[] = 'Please complete the reCAPTCHA verification.';
            }

            // 5. Send Email if No Errors
            if (empty($errors)) {

                // Combine name
                $name = $fname . ' ' . $lname;

                // Map service selection to subject
                $serviceMap = [
                    '1' => 'CUSTOM WEBSITE / APPLICATION',
                    '2' => 'WORDPRESS',
                    '3' => 'SEO',
                    '4' => 'CONSULTING',
                    '5' => 'VIDEO EDITING',
                    '6' => 'SECURITY & MAINTENANCE',
                    '7' => 'E-COMMERCE SOLUTIONS',
                    '8' => 'SOCIAL MEDIA MANAGEMENT',
                    '9' => 'EMAIL MARKETING',
                    '10' => 'SPEED OPTIMIZATION'
                ];
                $subject = $serviceMap[$companyService] ?? 'Quote Request';

                // Map company size
                $sizeMap = [
                    '1' => '1-10',
                    '2' => '11-20',
                    '3' => '21-50',
                    '4' => '51-100',
                    '5' => '100+'
                ];
                $companySizeEmp = $sizeMap[$companySize] ?? 'Unknown';

                // Map budget
                $budgetMap = [
                    '1' => '< 1K',
                    '2' => '2-5K',
                    '3' => '5-10K',
                    '4' => '10-20K',
                    '5' => '30K+'
                ];
                $companyBudgetAmt = $budgetMap[$companyBudget] ?? 'Unknown';

                // Build email message (sanitize all user input for HTML)
                $messageHTML = "<html><body>";
                $messageHTML .= "<h2>New Quote Request - $subject</h2>";
                $messageHTML .= "<p><b>Customer:</b> " . sanitizeHTML($name) . "</p>";
                $messageHTML .= "<p><b>Email:</b> " . sanitizeHTML($email) . "</p>";
                $messageHTML .= "<p><b>Phone:</b> " . sanitizeHTML($phone) . "</p>";
                if (!empty($company)) {
                    $messageHTML .= "<p><b>Company Name:</b> " . sanitizeHTML($company) . "</p>";
                }
                $messageHTML .= "<p><b>Website:</b> <a href=\"" . sanitizeHTML($website) . "\">" . sanitizeHTML($website) . "</a></p>";
                $messageHTML .= "<p><b>Company Size:</b> $companySizeEmp</p>";
                $messageHTML .= "<p><b>Budget:</b> $companyBudgetAmt</p>";
                $messageHTML .= "<p><b>Industry:</b> " . sanitizeHTML($industry) . "</p>";
                $messageHTML .= "<p><b>Comments:</b><br>" . nl2br(sanitizeHTML($comment)) . "</p>";
                // Append consent status to email for audit traceability
                $messageHTML .= "<h3>Consent Status</h3>";
                $messageHTML .= "<p><strong>Privacy Policy Consent:</strong> Yes</p>";
                $messageHTML .= "<p><strong>Marketing Consent:</strong> " . ($marketing_consent ? 'Yes' : 'No') . "</p>";
                $messageHTML .= "<p><strong>Consent Timestamp:</strong> " . date('c') . "</p>";
                $messageHTML .= "<p><strong>IP Address:</strong> " . getClientIP() . "</p>";
                $messageHTML .= "</body></html>";

                // Email headers
                $to = getEnv('MAIL_TO', 'support@izendestudioweb.com');
                $fromEmail = getEnv('MAIL_FROM', 'noreply@izendestudioweb.com');
                $fromName = 'Izende Studio Web - Quote Form';

                $headers = [];
                $headers[] = 'MIME-Version: 1.0';
                $headers[] = 'Content-type: text/html; charset=UTF-8';
                $headers[] = 'From: ' . $fromName . ' <' . $fromEmail . '>';
                $headers[] = 'Reply-To: ' . sanitizeHTML($name) . ' <' . $email . '>';
                $headers[] = 'X-Mailer: PHP/' . phpversion();
                $headers[] = 'X-Form-Source: quote.php';

                // Send email
                $mailSent = mail($to, $subject, $messageHTML, implode("\r\n", $headers));

                if ($mailSent) {
                    $success = true;

                    // Log successful submission
                    logSecurityEvent('quote_form_submitted', [
                        'name' => $name,
                        'email' => $email,
                        'service' => $subject,
                        'marketing_consent' => $marketing_consent
                    ], 'INFO');

                    // Append a lightweight consent audit to logs/form-consent.log
                    $consentLine = json_encode([
                        'ts' => date('c'),
                        'request' => 'quote',
                        'name' => $name,
                        'email' => $email,
                        'marketing_consent' => (bool)$marketing_consent,
                        'privacy_consent' => true,
                        'ip' => getClientIP()
                    ]) . PHP_EOL;
                    @file_put_contents(__DIR__ . '/logs/form-consent.log', $consentLine, FILE_APPEND | LOCK_EX);

                    // Clear form values on success
                    $fname = '';
                    $lname = '';
                    $email = '';
                    $phone = '';
                    $company = '';
                    $website = '';
                    $companySize = '';
                    $industry = '';
                    $companyService = '';
                    $companyBudget = '';
                    $comment = '';
                    $name = '';

                    // Regenerate CSRF token
                    regenerateCSRFToken();
                } else {
                    $errors[] = 'Failed to send email. Please try again or contact us directly.';
                    logSecurityEvent('quote_email_send_failed', [
                        'to' => $to
                    ], 'CRITICAL');
                }
            }
        }
    }
}

// Generate CSRF token for form
$csrfToken = generateCSRFToken();
$recaptchaSiteKey = getEnv('RECAPTCHA_SITE_KEY');

// Validate reCAPTCHA configuration
if (empty($recaptchaSiteKey)) {
    $errors[] = 'reCAPTCHA is not configured properly. Please contact support.';
    logSecurityEvent('recaptcha_not_configured', [
        'form' => 'quote',
        'location' => 'site_key_missing'
    ], 'CRITICAL');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <?php include './assets/includes/header-links.php'; ?>
</head>

<body>
    <!-- ======= Top Bar ======= -->
    <?php include './assets/includes/topbar.php'; ?>
    <!-- ======= Header ======= -->
    <?php include './assets/includes/header.php'; ?>
    <!-- End Header -->
    <main id="main">
        <!-- ======= Breadcrumbs ======= -->
        <section id="breadcrumbs" class="breadcrumbs">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Quote</h2>
                </div>
            </div>
        </section>
        <!-- End Breadcrumbs -->

        <!-- ======= Quote Form Section ======= -->
        <section id="portfolio-details" class="portfolio-details">
            <div class="container">

                <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    Thank you! Your quote request has been submitted successfully. We will contact you soon.
                </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <strong>Please correct the following errors:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo sanitizeHTML($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="wizard-container">
                    <!-- Wizard Progress Indicator -->
                    <nav class="wizard-progress" aria-label="Quote form progress">
                        <ol>
                            <li aria-current="step"><span>1</span> Contact Info</li>
                            <li><span>2</span> Business Details</li>
                            <li><span>3</span> Project Info</li>
                        </ol>
                    </nav>

                    <form action="" id="myform" method="post" role="form">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo sanitizeHTML($csrfToken); ?>">

                        <!-- Step 1: Contact Information -->
                        <div class="wizard-step" data-step="1">
                            <h3 class="step-title">Contact Information</h3>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-floating">
                                        <input type="text" name="fname" value="<?php echo sanitizeHTML($fname); ?>" class="form-control" placeholder=" " id="fname" required aria-describedby="fname-error">
                                        <label for="fname">First Name</label>
                                    </div>
                                    <div class="invalid-feedback" id="fname-error" role="alert"></div>
                                    <div class="valid-feedback">Looks good!</div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="lname" value="<?php echo sanitizeHTML($lname); ?>" placeholder=" " id="lname" required aria-describedby="lname-error">
                                        <label for="lname">Last Name</label>
                                    </div>
                                    <div class="invalid-feedback" id="lname-error" role="alert"></div>
                                    <div class="valid-feedback">Looks good!</div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-lg-6">
                                    <div class="form-floating">
                                        <input type="tel" id="phone" name="phone" value="<?php echo sanitizeHTML($phone); ?>" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" required class="form-control" placeholder=" " aria-describedby="phone-error">
                                        <label for="phone">Phone (123-456-7890)</label>
                                    </div>
                                    <div class="invalid-feedback" id="phone-error" role="alert"></div>
                                    <div class="valid-feedback">Looks good!</div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-floating">
                                        <input type="email" name="email" value="<?php echo sanitizeHTML($email); ?>" class="form-control" placeholder=" " id="email" required aria-describedby="email-error">
                                        <label for="email">Email</label>
                                    </div>
                                    <div class="invalid-feedback" id="email-error" role="alert"></div>
                                    <div class="valid-feedback">Looks good!</div>
                                </div>
                            </div>

                            <div class="wizard-buttons mt-4">
                                <button type="button" class="btn btn-brand btn-next">Next <i class="bi bi-arrow-right"></i></button>
                            </div>
                        </div>

                        <!-- Step 2: Business Details -->
                        <div class="wizard-step" data-step="2" style="display: none;">
                            <h3 class="step-title">Business Details</h3>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" value="<?php echo sanitizeHTML($company); ?>" name="company" placeholder=" " id="company" aria-describedby="company-error">
                                        <label for="company">Company Name (Optional)</label>
                                    </div>
                                    <div class="invalid-feedback" id="company-error" role="alert"></div>
                                    <div class="valid-feedback">Looks good!</div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-floating">
                                        <input type="url" class="form-control" name="website" value="<?php echo sanitizeHTML($website); ?>" placeholder=" " id="website" required aria-describedby="website-error">
                                        <label for="website">Website URL</label>
                                    </div>
                                    <div class="invalid-feedback" id="website-error" role="alert"></div>
                                    <div class="valid-feedback">Looks good!</div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-lg-6">
                                    <div class="form-floating">
                                        <select id="selectSize" name="selectSize" class="form-select" required aria-describedby="selectSize-error">
                                            <option value="" disabled <?php echo empty($companySize) ? 'selected' : ''; ?>>Choose...</option>
                                            <option value="1" <?php echo $companySize == '1' ? 'selected' : ''; ?>>1-10</option>
                                            <option value="2" <?php echo $companySize == '2' ? 'selected' : ''; ?>>11-20</option>
                                            <option value="3" <?php echo $companySize == '3' ? 'selected' : ''; ?>>21-50</option>
                                            <option value="4" <?php echo $companySize == '4' ? 'selected' : ''; ?>>51-100</option>
                                            <option value="5" <?php echo $companySize == '5' ? 'selected' : ''; ?>>100+</option>
                                        </select>
                                        <label for="selectSize">Company Size</label>
                                    </div>
                                    <div class="invalid-feedback" id="selectSize-error" role="alert"></div>
                                    <div class="valid-feedback">Looks good!</div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="industry" value="<?php echo sanitizeHTML($industry); ?>" placeholder=" " id="industry" required aria-describedby="industry-error">
                                        <label for="industry">Industry</label>
                                    </div>
                                    <div class="invalid-feedback" id="industry-error" role="alert"></div>
                                    <div class="valid-feedback">Looks good!</div>
                                </div>
                            </div>

                            <div class="wizard-buttons mt-4">
                                <button type="button" class="btn btn-secondary btn-prev"><i class="bi bi-arrow-left"></i> Back</button>
                                <button type="button" class="btn btn-brand btn-next">Next <i class="bi bi-arrow-right"></i></button>
                            </div>
                        </div>

                        <!-- Step 3: Project Information -->
                        <div class="wizard-step" data-step="3" style="display: none;">
                            <h3 class="step-title">Project Information</h3>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-floating">
                                        <select id="selectService" name="selectService" class="form-select" required aria-describedby="selectService-error">
                                            <option value="" disabled <?php echo empty($companyService) ? 'selected' : ''; ?>>Choose...</option>
                                            <option value="1" <?php echo $companyService == '1' ? 'selected' : ''; ?>>CUSTOM WEBSITE / APPLICATION</option>
                                            <option value="2" <?php echo $companyService == '2' ? 'selected' : ''; ?>>WORDPRESS</option>
                                            <option value="3" <?php echo $companyService == '3' ? 'selected' : ''; ?>>SEO</option>
                                            <option value="4" <?php echo $companyService == '4' ? 'selected' : ''; ?>>CONSULTING</option>
                                            <option value="5" <?php echo $companyService == '5' ? 'selected' : ''; ?>>VIDEO EDITING</option>
                                            <option value="6" <?php echo $companyService == '6' ? 'selected' : ''; ?>>SECURITY & MAINTENANCE</option>
                                            <option value="7" <?php echo $companyService == '7' ? 'selected' : ''; ?>>E-COMMERCE SOLUTIONS</option>
                                            <option value="8" <?php echo $companyService == '8' ? 'selected' : ''; ?>>SOCIAL MEDIA MANAGEMENT</option>
                                            <option value="9" <?php echo $companyService == '9' ? 'selected' : ''; ?>>EMAIL MARKETING</option>
                                            <option value="10" <?php echo $companyService == '10' ? 'selected' : ''; ?>>SPEED OPTIMIZATION</option>
                                        </select>
                                        <label for="selectService">Select Service</label>
                                    </div>
                                    <div class="invalid-feedback" id="selectService-error" role="alert"></div>
                                    <div class="valid-feedback">Looks good!</div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-floating">
                                        <select id="selectBudget" name="selectBudget" class="form-select" required aria-describedby="selectBudget-error">
                                            <option value="" disabled <?php echo empty($companyBudget) ? 'selected' : ''; ?>>Choose...</option>
                                            <option value="1" <?php echo $companyBudget == '1' ? 'selected' : ''; ?>>< 1K</option>
                                            <option value="2" <?php echo $companyBudget == '2' ? 'selected' : ''; ?>>2-5K</option>
                                            <option value="3" <?php echo $companyBudget == '3' ? 'selected' : ''; ?>>5-10K</option>
                                            <option value="4" <?php echo $companyBudget == '4' ? 'selected' : ''; ?>>11-20K</option>
                                            <option value="5" <?php echo $companyBudget == '5' ? 'selected' : ''; ?>>30K+</option>
                                        </select>
                                        <label for="selectBudget">Budget</label>
                                    </div>
                                    <div class="invalid-feedback" id="selectBudget-error" role="alert"></div>
                                    <div class="valid-feedback">Looks good!</div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control" name="comment" id="comment" placeholder=" " style="height: 120px;" required aria-describedby="comment-error"><?php echo sanitizeHTML($comment); ?></textarea>
                                        <label for="comment">Tell Us About Your Business</label>
                                    </div>
                                    <div class="invalid-feedback" id="comment-error" role="alert"></div>
                                    <div class="valid-feedback">Looks good!</div>
                                </div>
                            </div>

                                            <div class="recaptcha-container mt-3" id="recaptcha-placeholder">
                                                <!-- reCAPTCHA will be lazy-loaded here when functional consent is given -->
                                                <div class="g-recaptcha" data-sitekey="<?php echo sanitizeHTML($recaptchaSiteKey); ?>" style="display:none;"></div>
                                            </div>

                                            <!-- Consent checkboxes (required consent + optional marketing) -->
                                            <div class="form-group mt-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="quote-consent" name="consent" required>
                                                    <label class="form-check-label" for="quote-consent">I agree to the <a href="/privacy-policy.php" target="_blank" rel="noopener">Privacy Policy</a>.</label>
                                                </div>
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" id="quote-marketing-consent" name="marketing_consent" value="1">
                                                    <label class="form-check-label" for="quote-marketing-consent">I agree to receive marketing emails.</label>
                                                </div>
                                            </div>

                            <div class="wizard-buttons mt-4">
                                <button type="button" class="btn btn-secondary btn-prev"><i class="bi bi-arrow-left"></i> Back</button>
                                <button type="submit" name="sendMailbtn" class="btn btn-brand btn-submit">
                                    <span class="btn-text"><i class="bi bi-check-circle"></i> Submit Quote Request</span>
                                    <span class="btn-spinner" style="display: none;">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Submitting...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </section><!-- End Quote Form Section -->

    </main><!-- End #main -->

    <!-- ======= Footer ======= -->
    <?php include './assets/includes/footer.php'; ?>
    <!-- End Footer -->
</body>

</html>

<?php
/**
 * Spam Protection Helper Class
 * Provides additional anti-spam measures beyond CSRF and rate limiting
 */

class SpamProtection {

    /**
     * Generate a honeypot field (invisible to humans, visible to bots)
     * @param string $formId Unique identifier for the form
     * @return string HTML for honeypot field
     */
    public static function generateHoneypot($formId = 'contact') {
        $fieldName = 'website_url_' . substr(md5($formId . session_id()), 0, 8);

        // Store field name in session for validation
        $_SESSION['honeypot_field_' . $formId] = $fieldName;

        // Return hidden field with CSS to make it invisible
        return '
        <div class="form-field-hp" style="position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden;" aria-hidden="true" tabindex="-1">
            <label for="' . $fieldName . '">Website URL (leave blank)</label>
            <input type="text" name="' . $fieldName . '" id="' . $fieldName . '" value="" autocomplete="off" tabindex="-1">
        </div>';
    }

    /**
     * Validate honeypot field
     * @param string $formId Unique identifier for the form
     * @param array $postData POST data
     * @return bool True if valid (honeypot is empty), false if spam detected
     */
    public static function validateHoneypot($formId, $postData) {
        $fieldName = $_SESSION['honeypot_field_' . $formId] ?? null;

        if (!$fieldName) {
            // Honeypot not set up for this form, allow submission
            return true;
        }

        // Check if honeypot field was filled (indicates bot)
        if (isset($postData[$fieldName]) && !empty($postData[$fieldName])) {
            // Spam detected - bot filled the honeypot
            self::logSpamAttempt('honeypot_filled', $formId, [
                'field_name' => $fieldName,
                'field_value' => substr($postData[$fieldName], 0, 100)
            ]);
            return false;
        }

        return true;
    }

    /**
     * Generate a timestamp token for form submission timing
     * @param string $formId Unique identifier for the form
     * @return string Hidden input field HTML
     */
    public static function generateTimestamp($formId = 'contact') {
        $timestamp = time();
        $token = base64_encode($timestamp . '|' . hash_hmac('sha256', $timestamp . $formId, session_id()));

        return '<input type="hidden" name="form_timestamp" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Validate form submission timing
     * Rejects forms submitted too quickly (bots) or too slowly (replay attacks)
     *
     * @param string $formId Unique identifier for the form
     * @param array $postData POST data
     * @param int $minSeconds Minimum seconds before submission (default: 3)
     * @param int $maxSeconds Maximum seconds before submission (default: 3600 = 1 hour)
     * @return bool True if valid timing, false if suspicious
     */
    public static function validateTiming($formId, $postData, $minSeconds = 3, $maxSeconds = 3600) {
        if (!isset($postData['form_timestamp'])) {
            // No timestamp provided - reject for security
            self::logSpamAttempt('no_timestamp', $formId);
            return false;
        }

        $token = base64_decode($postData['form_timestamp']);
        if ($token === false) {
            self::logSpamAttempt('invalid_timestamp_encoding', $formId);
            return false;
        }

        list($timestamp, $hash) = explode('|', $token);

        // Verify HMAC to prevent tampering
        $expected_hash = hash_hmac('sha256', $timestamp . $formId, session_id());
        if (!hash_equals($expected_hash, $hash)) {
            self::logSpamAttempt('timestamp_tampering', $formId);
            return false;
        }

        $elapsedTime = time() - $timestamp;

        // Check if submitted too quickly (bot)
        if ($elapsedTime < $minSeconds) {
            self::logSpamAttempt('submitted_too_quickly', $formId, [
                'elapsed_seconds' => $elapsedTime,
                'minimum_required' => $minSeconds
            ]);
            return false;
        }

        // Check if submitted too slowly (replay attack or stale form)
        if ($elapsedTime > $maxSeconds) {
            self::logSpamAttempt('submitted_too_slowly', $formId, [
                'elapsed_seconds' => $elapsedTime,
                'maximum_allowed' => $maxSeconds
            ]);
            return false;
        }

        return true;
    }

    /**
     * Validate IP address reputation
     * Checks if IP is in known spam blacklists
     *
     * @param string $ip IP address to check
     * @return array ['is_spam' => bool, 'reason' => string]
     */
    public static function checkIPReputation($ip) {
        // Local blacklist check
        $blacklistedIPs = self::getBlacklistedIPs();
        if (in_array($ip, $blacklistedIPs)) {
            return ['is_spam' => true, 'reason' => 'IP in local blacklist'];
        }

        // Check rate of submissions from this IP
        $submissionCount = self::getIPSubmissionCount($ip, 3600); // Last hour
        if ($submissionCount > 10) {
            return ['is_spam' => true, 'reason' => 'Too many submissions from IP'];
        }

        // All checks passed
        return ['is_spam' => false, 'reason' => ''];
    }

    /**
     * Detect suspicious patterns in form content
     *
     * @param array $data Form data to analyze
     * @return array ['is_spam' => bool, 'reason' => string]
     */
    public static function detectSpamPatterns($data) {
        $content = implode(' ', array_values($data));

        // Common spam keywords
        $spamKeywords = [
            'viagra', 'cialis', 'porn', 'xxx', 'casino', 'lottery',
            'bitcoin', 'crypto investment', 'double your money',
            'click here now', 'limited time offer', 'act now',
            'weight loss miracle', 'work from home', 'make money fast'
        ];

        foreach ($spamKeywords as $keyword) {
            if (stripos($content, $keyword) !== false) {
                return ['is_spam' => true, 'reason' => 'Spam keyword detected: ' . $keyword];
            }
        }

        // Check for excessive links
        $linkCount = preg_match_all('/https?:\/\//', $content);
        if ($linkCount > 5) {
            return ['is_spam' => true, 'reason' => 'Excessive links detected'];
        }

        // Check for excessive special characters
        $specialCharCount = preg_match_all('/[^\w\s\.\,\!\?\-\'\"]/', $content);
        $totalLength = strlen($content);
        if ($totalLength > 0 && ($specialCharCount / $totalLength) > 0.3) {
            return ['is_spam' => true, 'reason' => 'Excessive special characters'];
        }

        return ['is_spam' => false, 'reason' => ''];
    }

    /**
     * Complete spam validation for a form submission
     * Runs all spam checks and returns comprehensive result
     *
     * @param string $formId Form identifier
     * @param array $postData POST data
     * @param array $options Options: ['check_honeypot' => true, 'check_timing' => true, 'check_ip' => true, 'check_content' => true]
     * @return array ['is_spam' => bool, 'reason' => string]
     */
    public static function validateSubmission($formId, $postData, $options = []) {
        $defaults = [
            'check_honeypot' => true,
            'check_timing' => true,
            'check_ip' => true,
            'check_content' => true,
            'min_seconds' => 3,
            'max_seconds' => 3600
        ];
        $options = array_merge($defaults, $options);

        // Honeypot validation
        if ($options['check_honeypot']) {
            if (!self::validateHoneypot($formId, $postData)) {
                return ['is_spam' => true, 'reason' => 'Honeypot field was filled'];
            }
        }

        // Timing validation
        if ($options['check_timing']) {
            if (!self::validateTiming($formId, $postData, $options['min_seconds'], $options['max_seconds'])) {
                return ['is_spam' => true, 'reason' => 'Suspicious submission timing'];
            }
        }

        // IP reputation check
        if ($options['check_ip']) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $ipCheck = self::checkIPReputation($ip);
            if ($ipCheck['is_spam']) {
                return $ipCheck;
            }
        }

        // Content pattern check
        if ($options['check_content']) {
            $contentCheck = self::detectSpamPatterns($postData);
            if ($contentCheck['is_spam']) {
                return $contentCheck;
            }
        }

        // All checks passed
        return ['is_spam' => false, 'reason' => ''];
    }

    /**
     * Log spam attempt for analysis
     */
    private static function logSpamAttempt($type, $formId, $details = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'form_id' => $formId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details
        ];

        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0750, true);
        }

        $logFile = $logDir . '/spam-attempts.log';
        @file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);

        // Also log to security event if security.php is loaded
        if (function_exists('logSecurityEvent')) {
            logSecurityEvent('spam_detected', $logEntry, 'WARNING');
        }
    }

    /**
     * Get blacklisted IPs from database or file
     */
    private static function getBlacklistedIPs() {
        // TODO: Implement database storage for blacklisted IPs
        // For now, return empty array
        return [];
    }

    /**
     * Get number of submissions from IP in given timeframe
     */
    private static function getIPSubmissionCount($ip, $seconds = 3600) {
        global $conn;

        if (!$conn) {
            return 0;
        }

        $since = date('Y-m-d H:i:s', time() - $seconds);

        $stmt = $conn->prepare("
            SELECT COUNT(*) as count
            FROM iz_form_submissions
            WHERE ip_address = ?
            AND created_at > ?
        ");

        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param('ss', $ip, $since);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return $result['count'] ?? 0;
    }
}

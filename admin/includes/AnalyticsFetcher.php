<?php
/**
 * Google Analytics Data Fetcher
 * Fetches analytics data using Google Analytics Data API v1
 * Uses service account authentication via HTTP requests
 */

class AnalyticsFetcher {
    private $propertyId;
    private $serviceAccountData;
    private $cacheDir;
    private $cacheDuration;

    public function __construct($propertyId, $serviceAccountJson, $cacheDuration = 3600) {
        $this->propertyId = $propertyId;
        $this->serviceAccountData = json_decode($serviceAccountJson, true);
        $this->cacheDir = __DIR__ . '/../cache/analytics/';
        $this->cacheDuration = $cacheDuration;

        // Create cache directory if it doesn't exist
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Get access token using service account
     */
    private function getAccessToken() {
        // Check cache first
        $cacheFile = $this->cacheDir . 'access_token.json';
        if (file_exists($cacheFile)) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if ($cached && $cached['expires_at'] > time()) {
                return $cached['access_token'];
            }
        }

        if (!$this->serviceAccountData) {
            throw new Exception('Service account credentials not configured');
        }

        // Create JWT for service account
        $now = time();
        $jwt = $this->createJWT([
            'iss' => $this->serviceAccountData['client_email'],
            'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ], $this->serviceAccountData['private_key']);

        // Exchange JWT for access token
        $response = $this->httpPost('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]);

        if (!$response || !isset($response['access_token'])) {
            throw new Exception('Failed to get access token');
        }

        // Cache the token
        file_put_contents($cacheFile, json_encode([
            'access_token' => $response['access_token'],
            'expires_at' => $now + 3500 // 5 min before actual expiry
        ]));

        return $response['access_token'];
    }

    /**
     * Create JWT for service account authentication
     */
    private function createJWT($payload, $privateKey) {
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];

        $segments = [];
        $segments[] = $this->base64UrlEncode(json_encode($header));
        $segments[] = $this->base64UrlEncode(json_encode($payload));

        $signingInput = implode('.', $segments);

        $signature = '';
        openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    /**
     * Base64 URL encode
     */
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * HTTP POST request
     */
    private function httpPost($url, $data) {
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($data)
            ]
        ];

        $response = @file_get_contents($url, false, stream_context_create($options));
        return $response ? json_decode($response, true) : null;
    }

    /**
     * Run report query
     */
    private function runReport($dimensions, $metrics, $dateRange) {
        $cacheKey = md5(json_encode([$dimensions, $metrics, $dateRange]));
        $cacheFile = $this->cacheDir . $cacheKey . '.json';

        // Check cache
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $this->cacheDuration) {
            return json_decode(file_get_contents($cacheFile), true);
        }

        try {
            $accessToken = $this->getAccessToken();

            $url = "https://analyticsdata.googleapis.com/v1beta/properties/{$this->propertyId}:runReport";

            $requestBody = [
                'dimensions' => $dimensions,
                'metrics' => $metrics,
                'dateRanges' => [$dateRange]
            ];

            $options = [
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        "Authorization: Bearer {$accessToken}",
                        "Content-Type: application/json"
                    ],
                    'content' => json_encode($requestBody),
                    'ignore_errors' => true
                ]
            ];

            $response = @file_get_contents($url, false, stream_context_create($options));

            if ($response === false) {
                $error = error_get_last();
                throw new Exception('API request failed: ' . ($error['message'] ?? 'Unknown error'));
            }

            $data = json_decode($response, true);

            // Check for API errors
            if (isset($data['error'])) {
                throw new Exception('Google Analytics API error: ' . ($data['error']['message'] ?? 'Unknown API error') . ' (Status: ' . ($data['error']['status'] ?? 'Unknown') . ')');
            }

            // Cache the result
            file_put_contents($cacheFile, json_encode($data));

            return $data;

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get page views for date range
     */
    public function getPageViews($days = 7) {
        $dateRange = [
            'startDate' => $days . 'daysAgo',
            'endDate' => 'today'
        ];

        $result = $this->runReport(
            [['name' => 'date']],
            [['name' => 'screenPageViews']],
            $dateRange
        );

        return $this->formatPageViewsData($result);
    }

    /**
     * Get top pages
     */
    public function getTopPages($limit = 10) {
        $dateRange = [
            'startDate' => '7daysAgo',
            'endDate' => 'today'
        ];

        $result = $this->runReport(
            [['name' => 'pageTitle']],
            [['name' => 'screenPageViews']],
            $dateRange
        );

        return $this->formatTopPagesData($result, $limit);
    }

    /**
     * Get traffic sources
     */
    public function getTrafficSources() {
        $dateRange = [
            'startDate' => '7daysAgo',
            'endDate' => 'today'
        ];

        $result = $this->runReport(
            [['name' => 'sessionSource']],
            [['name' => 'sessions']],
            $dateRange
        );

        return $this->formatTrafficSourcesData($result);
    }

    /**
     * Get summary stats
     */
    public function getSummaryStats($days = 7) {
        $dateRange = [
            'startDate' => $days . 'daysAgo',
            'endDate' => 'today'
        ];

        $result = $this->runReport(
            [],
            [
                ['name' => 'screenPageViews'],
                ['name' => 'sessions'],
                ['name' => 'totalUsers'],
                ['name' => 'averageSessionDuration']
            ],
            $dateRange
        );

        return $this->formatSummaryData($result);
    }

    /**
     * Get blog-specific analytics
     * Filters for pages containing 'blog-post.php' or '/blog/'
     */
    public function getBlogStats($days = 30) {
        $dateRange = [
            'startDate' => $days . 'daysAgo',
            'endDate' => 'today'
        ];

        // Get blog page views over time
        $result = $this->runReport(
            [['name' => 'pageTitle'], ['name' => 'pagePath']],
            [['name' => 'screenPageViews'], ['name' => 'averageSessionDuration']],
            $dateRange
        );

        return $this->formatBlogData($result);
    }

    /**
     * Get top blog posts
     */
    public function getTopBlogPosts($limit = 10, $days = 30) {
        $dateRange = [
            'startDate' => $days . 'daysAgo',
            'endDate' => 'today'
        ];

        $result = $this->runReport(
            [['name' => 'pageTitle'], ['name' => 'pagePath']],
            [['name' => 'screenPageViews'], ['name' => 'averageSessionDuration']],
            $dateRange
        );

        return $this->formatTopBlogPosts($result, $limit);
    }

    /**
     * Get blog traffic sources
     */
    public function getBlogTrafficSources($days = 30) {
        $dateRange = [
            'startDate' => $days . 'daysAgo',
            'endDate' => 'today'
        ];

        $result = $this->runReport(
            [['name' => 'sessionSource'], ['name' => 'pagePath']],
            [['name' => 'sessions']],
            $dateRange
        );

        return $this->formatBlogTrafficSources($result);
    }

    /**
     * Format page views data for charts
     */
    private function formatPageViewsData($data) {
        if (isset($data['error'])) {
            return ['error' => $data['error']];
        }

        if (!isset($data['rows'])) {
            return ['labels' => [], 'values' => []];
        }

        $labels = [];
        $values = [];

        foreach ($data['rows'] as $row) {
            $date = $row['dimensionValues'][0]['value'];
            $views = $row['metricValues'][0]['value'];

            $labels[] = date('M j', strtotime($date));
            $values[] = (int)$views;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Format top pages data
     */
    private function formatTopPagesData($data, $limit) {
        if (isset($data['error'])) {
            return ['error' => $data['error']];
        }

        if (!isset($data['rows'])) {
            return [];
        }

        $pages = [];
        $count = 0;

        foreach ($data['rows'] as $row) {
            if ($count >= $limit) break;

            $pages[] = [
                'page' => $row['dimensionValues'][0]['value'],
                'views' => (int)$row['metricValues'][0]['value']
            ];

            $count++;
        }

        return $pages;
    }

    /**
     * Format traffic sources data
     */
    private function formatTrafficSourcesData($data) {
        if (isset($data['error'])) {
            return ['error' => $data['error']];
        }

        if (!isset($data['rows'])) {
            return ['labels' => [], 'values' => []];
        }

        $labels = [];
        $values = [];

        foreach ($data['rows'] as $row) {
            $source = $row['dimensionValues'][0]['value'];
            $sessions = $row['metricValues'][0]['value'];

            $labels[] = ucfirst($source);
            $values[] = (int)$sessions;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Format summary data
     */
    private function formatSummaryData($data) {
        if (isset($data['error'])) {
            return ['error' => $data['error']];
        }

        if (!isset($data['rows']) || empty($data['rows'])) {
            return [
                'pageViews' => 0,
                'sessions' => 0,
                'users' => 0,
                'avgDuration' => 0
            ];
        }

        $row = $data['rows'][0];

        return [
            'pageViews' => (int)$row['metricValues'][0]['value'],
            'sessions' => (int)$row['metricValues'][1]['value'],
            'users' => (int)$row['metricValues'][2]['value'],
            'avgDuration' => round((float)$row['metricValues'][3]['value'])
        ];
    }

    /**
     * Format blog-specific data
     */
    private function formatBlogData($data) {
        if (isset($data['error'])) {
            return ['error' => $data['error']];
        }

        if (!isset($data['rows'])) {
            return [
                'totalViews' => 0,
                'avgDuration' => 0,
                'postCount' => 0
            ];
        }

        $totalViews = 0;
        $totalDuration = 0;
        $blogPosts = 0;

        foreach ($data['rows'] as $row) {
            $pagePath = $row['dimensionValues'][1]['value'] ?? '';

            // Filter for blog posts (blog-post.php or /blog/ paths)
            if (strpos($pagePath, 'blog-post.php') !== false || strpos($pagePath, '/blog/') !== false) {
                $totalViews += (int)$row['metricValues'][0]['value'];
                $totalDuration += (float)$row['metricValues'][1]['value'];
                $blogPosts++;
            }
        }

        return [
            'totalViews' => $totalViews,
            'avgDuration' => $blogPosts > 0 ? round($totalDuration / $blogPosts) : 0,
            'postCount' => $blogPosts
        ];
    }

    /**
     * Format top blog posts
     */
    private function formatTopBlogPosts($data, $limit) {
        if (isset($data['error'])) {
            return ['error' => $data['error']];
        }

        if (!isset($data['rows'])) {
            return [];
        }

        $posts = [];

        foreach ($data['rows'] as $row) {
            $pagePath = $row['dimensionValues'][1]['value'] ?? '';

            // Filter for blog posts only
            if (strpos($pagePath, 'blog-post.php') !== false || strpos($pagePath, '/blog/') !== false) {
                $posts[] = [
                    'title' => $row['dimensionValues'][0]['value'],
                    'path' => $pagePath,
                    'views' => (int)$row['metricValues'][0]['value'],
                    'avgDuration' => round((float)$row['metricValues'][1]['value'])
                ];
            }
        }

        // Sort by views descending
        usort($posts, function($a, $b) {
            return $b['views'] - $a['views'];
        });

        return array_slice($posts, 0, $limit);
    }

    /**
     * Format blog traffic sources
     */
    private function formatBlogTrafficSources($data) {
        if (isset($data['error'])) {
            return ['error' => $data['error']];
        }

        if (!isset($data['rows'])) {
            return ['labels' => [], 'values' => []];
        }

        $sources = [];

        foreach ($data['rows'] as $row) {
            $pagePath = $row['dimensionValues'][1]['value'] ?? '';

            // Filter for blog posts only
            if (strpos($pagePath, 'blog-post.php') !== false || strpos($pagePath, '/blog/') !== false) {
                $source = $row['dimensionValues'][0]['value'];
                $sessions = (int)$row['metricValues'][0]['value'];

                if (!isset($sources[$source])) {
                    $sources[$source] = 0;
                }
                $sources[$source] += $sessions;
            }
        }

        // Sort by sessions descending
        arsort($sources);

        $labels = array_map('ucfirst', array_keys($sources));
        $values = array_values($sources);

        return ['labels' => $labels, 'values' => $values];
    }
}

<?php
/**
 * Google Calendar Helper Class
 * Handles automatic calendar event creation for consultation bookings
 */

class CalendarHelper {
    private $client;
    private $service;
    private $calendarId;
    private $timezone;

    public function __construct() {
        // Load environment variables
        if (!defined('GOOGLE_CALENDAR_ID')) {
            $this->loadEnv();
        }

        $this->calendarId = $_ENV['GOOGLE_CALENDAR_ID'] ?? null;
        $this->timezone = $_ENV['GOOGLE_CALENDAR_TIMEZONE'] ?? 'America/Chicago';

        if (!$this->calendarId) {
            throw new Exception('Google Calendar ID not configured in .env file');
        }

        // Initialize Google Client
        $this->initializeClient();
    }

    /**
     * Load environment variables from .env
     */
    private function loadEnv() {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
                define(trim($key), trim($value));
            }
        }
    }

    /**
     * Initialize Google API Client
     */
    private function initializeClient() {
        // Check if composer autoload exists
        $autoloadPath = __DIR__ . '/../vendor/autoload.php';
        if (!file_exists($autoloadPath)) {
            throw new Exception('Google API Client not installed. Run: composer require google/apiclient');
        }

        require_once $autoloadPath;

        // Initialize client
        $this->client = new Google_Client();
        $this->client->setApplicationName('Izende Studio Web Calendar');

        // Set service account credentials
        $serviceAccountFile = __DIR__ . '/../' . ($_ENV['GOOGLE_CALENDAR_SERVICE_ACCOUNT_FILE'] ?? 'calendar-service-account.json');

        if (!file_exists($serviceAccountFile)) {
            throw new Exception('Calendar service account file not found: ' . $serviceAccountFile);
        }

        $this->client->setAuthConfig($serviceAccountFile);
        $this->client->setScopes([Google_Service_Calendar::CALENDAR]);

        // Initialize calendar service
        $this->service = new Google_Service_Calendar($this->client);
    }

    /**
     * Create a calendar event for a consultation booking
     *
     * @param array $booking Booking data from database
     * @return string|null Google Calendar Event ID or null on failure
     */
    public function createBookingEvent($booking) {
        try {
            $event = new Google_Service_Calendar_Event([
                'summary' => 'Consultation: ' . $booking['service_type'] . ' - ' . $booking['client_name'],
                'description' => $this->generateEventDescription($booking),
                'start' => [
                    'dateTime' => date('c', strtotime($booking['preferred_date'])),
                    'timeZone' => $this->timezone,
                ],
                'end' => [
                    'dateTime' => date('c', strtotime($booking['preferred_date'] . ' +' . ($booking['duration'] ?? 30) . ' minutes')),
                    'timeZone' => $this->timezone,
                ],
                'attendees' => [
                    ['email' => $booking['client_email']],
                ],
                'reminders' => [
                    'useDefault' => false,
                    'overrides' => [
                        ['method' => 'email', 'minutes' => 24 * 60], // 1 day before
                        ['method' => 'popup', 'minutes' => 60],      // 1 hour before
                    ],
                ],
                'conferenceData' => [
                    'createRequest' => [
                        'requestId' => uniqid('izende-'),
                        'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                    ],
                ],
                'guestsCanModify' => false,
                'guestsCanInviteOthers' => false,
                'guestsCanSeeOtherGuests' => false,
            ]);

            // Create event with conference data (Google Meet)
            $createdEvent = $this->service->events->insert(
                $this->calendarId,
                $event,
                ['conferenceDataVersion' => 1, 'sendUpdates' => 'all']
            );

            return $createdEvent->getId();

        } catch (Exception $e) {
            error_log('Google Calendar Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update an existing calendar event
     *
     * @param string $eventId Google Calendar Event ID
     * @param array $booking Updated booking data
     * @return bool Success status
     */
    public function updateBookingEvent($eventId, $booking) {
        try {
            // Get existing event
            $event = $this->service->events->get($this->calendarId, $eventId);

            // Update event details
            $event->setSummary('Consultation: ' . $booking['service_type'] . ' - ' . $booking['client_name']);
            $event->setDescription($this->generateEventDescription($booking));

            $event->setStart(new Google_Service_Calendar_EventDateTime([
                'dateTime' => date('c', strtotime($booking['preferred_date'])),
                'timeZone' => $this->timezone,
            ]));

            $event->setEnd(new Google_Service_Calendar_EventDateTime([
                'dateTime' => date('c', strtotime($booking['preferred_date'] . ' +' . ($booking['duration'] ?? 30) . ' minutes')),
                'timeZone' => $this->timezone,
            ]));

            // Update the event
            $this->service->events->update(
                $this->calendarId,
                $eventId,
                $event,
                ['sendUpdates' => 'all']
            );

            return true;

        } catch (Exception $e) {
            error_log('Google Calendar Update Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete/Cancel a calendar event
     *
     * @param string $eventId Google Calendar Event ID
     * @return bool Success status
     */
    public function deleteBookingEvent($eventId) {
        try {
            $this->service->events->delete(
                $this->calendarId,
                $eventId,
                ['sendUpdates' => 'all']
            );
            return true;

        } catch (Exception $e) {
            error_log('Google Calendar Delete Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Google Meet link from event
     *
     * @param string $eventId Google Calendar Event ID
     * @return string|null Google Meet link or null if not found
     */
    public function getMeetLink($eventId) {
        try {
            $event = $this->service->events->get($this->calendarId, $eventId);

            if ($event->getConferenceData()) {
                $conferenceData = $event->getConferenceData();
                $entryPoints = $conferenceData->getEntryPoints();

                foreach ($entryPoints as $entryPoint) {
                    if ($entryPoint->getEntryPointType() === 'video') {
                        return $entryPoint->getUri();
                    }
                }
            }

            return null;

        } catch (Exception $e) {
            error_log('Google Calendar Get Meet Link Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate event description from booking data
     *
     * @param array $booking Booking data
     * @return string Event description
     */
    private function generateEventDescription($booking) {
        $description = "FREE CONSULTATION CALL\n\n";
        $description .= "Service: " . $booking['service_type'] . "\n";
        $description .= "Duration: " . ($booking['duration'] ?? 30) . " minutes\n\n";

        $description .= "CLIENT INFORMATION:\n";
        $description .= "Name: " . $booking['client_name'] . "\n";
        $description .= "Email: " . $booking['client_email'] . "\n";

        if (!empty($booking['client_phone'])) {
            $description .= "Phone: " . $booking['client_phone'] . "\n";
        }

        if (!empty($booking['message'])) {
            $description .= "\nCLIENT MESSAGE:\n" . $booking['message'] . "\n";
        }

        $description .= "\n---\nManage this booking: https://izendestudioweb.com/admin/bookings.php?id=" . $booking['id'];

        return $description;
    }

    /**
     * Check if Calendar API is available and configured
     *
     * @return bool
     */
    public static function isAvailable() {
        try {
            $helper = new self();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

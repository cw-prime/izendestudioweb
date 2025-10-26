<?php
/**
 * Database Migration: Add Google Calendar Fields
 * Run this once to add google_event_id and google_meet_link to iz_bookings table
 */

require_once __DIR__ . '/../../config/env-loader.php';
require_once __DIR__ . '/../config/database.php';

echo "<h2>Database Migration: Google Calendar Fields</h2>\n";
echo "<pre>\n";

// Check if columns already exist
$result = mysqli_query($conn, "SHOW COLUMNS FROM iz_bookings LIKE 'google_event_id'");
$hasEventId = mysqli_num_rows($result) > 0;

$result = mysqli_query($conn, "SHOW COLUMNS FROM iz_bookings LIKE 'google_meet_link'");
$hasMeetLink = mysqli_num_rows($result) > 0;

if ($hasEventId && $hasMeetLink) {
    echo "✓ Google Calendar fields already exist!\n\n";
} else {
    echo "Adding Google Calendar integration fields...\n\n";

    // Add google_event_id column
    if (!$hasEventId) {
        $sql = "ALTER TABLE iz_bookings ADD COLUMN google_event_id VARCHAR(255) NULL AFTER notes";
        if (mysqli_query($conn, $sql)) {
            echo "✓ Added google_event_id column\n";
        } else {
            echo "✗ Error adding google_event_id: " . mysqli_error($conn) . "\n";
        }
    }

    // Add google_meet_link column
    if (!$hasMeetLink) {
        $sql = "ALTER TABLE iz_bookings ADD COLUMN google_meet_link VARCHAR(500) NULL AFTER google_event_id";
        if (mysqli_query($conn, $sql)) {
            echo "✓ Added google_meet_link column\n";
        } else {
            echo "✗ Error adding google_meet_link: " . mysqli_error($conn) . "\n";
        }
    }

    // Add index for performance
    $sql = "ALTER TABLE iz_bookings ADD INDEX idx_google_event_id (google_event_id)";
    if (mysqli_query($conn, $sql)) {
        echo "✓ Added index on google_event_id\n";
    } else {
        // Index might already exist, that's okay
        if (strpos(mysqli_error($conn), 'Duplicate key name') === false) {
            echo "Note: " . mysqli_error($conn) . "\n";
        }
    }

    echo "\n✓ Migration completed successfully!\n\n";
}

// Show updated table structure
echo "Updated iz_bookings table structure:\n";
echo str_repeat('-', 80) . "\n";

$result = mysqli_query($conn, "DESCRIBE iz_bookings");
while ($row = mysqli_fetch_assoc($result)) {
    printf("%-25s %-20s %-8s %-8s %-20s\n",
        $row['Field'],
        $row['Type'],
        $row['Null'],
        $row['Key'],
        $row['Extra']
    );
}

echo str_repeat('-', 80) . "\n";
echo "</pre>\n";

echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ol>\n";
echo "  <li>Follow the instructions in <code>GOOGLE-CALENDAR-SETUP.md</code></li>\n";
echo "  <li>Enable Google Calendar API in Google Cloud Console</li>\n";
echo "  <li>Download and install service account JSON key</li>\n";
echo "  <li>Update .env with GOOGLE_CALENDAR_ID</li>\n";
echo "  <li>Run: <code>composer require google/apiclient</code></li>\n";
echo "  <li>Test by confirming a booking in the admin panel</li>\n";
echo "</ol>\n";

echo "<p><a href='../bookings.php' class='btn btn-primary'>Go to Bookings Manager</a></p>\n";
?>

<?php
/**
 * Bookings Manager
 */

define('ADMIN_PAGE', true);
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/../includes/CalendarHelper.php';
Auth::requireAuth();

global $conn;
$pageTitle = 'Consultation Bookings';

// Check if table exists
$tableCheck = @mysqli_query($conn, "SHOW TABLES LIKE 'iz_bookings'");
$tableExists = ($tableCheck && mysqli_num_rows($tableCheck) > 0);

if (!$tableExists) {
    // Table doesn't exist - show disabled message
    include __DIR__ . '/includes/header.php';
?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h4 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Feature Not Available</h4>
        <p>The Booking Management feature requires additional database setup. This feature will be available in a future update.</p>
        <hr>
        <p class="mb-0">For now, you can manage your content through the <a href="services.php">Services</a>, <a href="portfolio.php">Portfolio</a>, and <a href="videos.php">Videos</a> managers.</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Check if Google Calendar is available
$calendarAvailable = CalendarHelper::isAvailable();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $bookingId = $_POST['id'];
        $newStatus = $_POST['status'];
        $notes = $_POST['notes'];

        // Get current booking data
        $stmt = $conn->prepare("SELECT * FROM iz_bookings WHERE id = ?");
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        $oldStatus = $booking['status'];

        // Update booking status and notes
        $stmt = $conn->prepare("UPDATE iz_bookings SET status = ?, notes = ? WHERE id = ?");
        $stmt->bind_param('ssi', $newStatus, $notes, $bookingId);

        if ($stmt->execute()) {
            // Handle Google Calendar integration
            if ($calendarAvailable) {
                try {
                    $calendar = new CalendarHelper();

                    // Status changed to confirmed - create calendar event
                    if ($newStatus === 'confirmed' && $oldStatus !== 'confirmed' && empty($booking['google_event_id'])) {
                        $eventId = $calendar->createBookingEvent($booking);
                        if ($eventId) {
                            // Get Google Meet link
                            $meetLink = $calendar->getMeetLink($eventId);

                            // Update booking with event ID and meet link
                            $updateStmt = $conn->prepare("UPDATE iz_bookings SET google_event_id = ?, google_meet_link = ? WHERE id = ?");
                            $updateStmt->bind_param('ssi', $eventId, $meetLink, $bookingId);
                            $updateStmt->execute();

                            $_SESSION['success_message'] = 'Booking confirmed! Calendar event created and invite sent to client.';
                        }
                    }
                    // Status changed from confirmed to cancelled - delete calendar event
                    elseif ($newStatus === 'cancelled' && !empty($booking['google_event_id'])) {
                        $calendar->deleteBookingEvent($booking['google_event_id']);

                        // Clear event ID
                        $updateStmt = $conn->prepare("UPDATE iz_bookings SET google_event_id = NULL, google_meet_link = NULL WHERE id = ?");
                        $updateStmt->bind_param('i', $bookingId);
                        $updateStmt->execute();

                        $_SESSION['success_message'] = 'Booking cancelled! Calendar event deleted and cancellation sent to client.';
                    }
                    // Still confirmed, update event details
                    elseif ($newStatus === 'confirmed' && !empty($booking['google_event_id'])) {
                        $calendar->updateBookingEvent($booking['google_event_id'], $booking);
                        $_SESSION['success_message'] = 'Booking updated! Calendar event updated and notification sent to client.';
                    }
                    else {
                        $_SESSION['success_message'] = 'Booking updated successfully!';
                    }

                } catch (Exception $e) {
                    error_log('Calendar integration error: ' . $e->getMessage());
                    $_SESSION['success_message'] = 'Booking updated! (Calendar sync unavailable)';
                }
            } else {
                $_SESSION['success_message'] = 'Booking updated successfully!';
            }
        }
    } elseif ($_POST['action'] === 'delete') {
        $bookingId = $_POST['id'];

        // Get booking data to check for calendar event
        $stmt = $conn->prepare("SELECT google_event_id FROM iz_bookings WHERE id = ?");
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        // Delete calendar event if exists
        if ($calendarAvailable && !empty($result['google_event_id'])) {
            try {
                $calendar = new CalendarHelper();
                $calendar->deleteBookingEvent($result['google_event_id']);
            } catch (Exception $e) {
                error_log('Calendar delete error: ' . $e->getMessage());
            }
        }

        // Delete booking from database
        $stmt = $conn->prepare("DELETE FROM iz_bookings WHERE id = ?");
        $stmt->bind_param('i', $bookingId);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Booking deleted!';
        }
    }

    header('Location: bookings.php');
    exit;
}

// Fetch bookings
$filter = $_GET['filter'] ?? 'upcoming';
$where = '';
switch ($filter) {
    case 'pending':
        $where = "WHERE status = 'pending'";
        break;
    case 'confirmed':
        $where = "WHERE status = 'confirmed'";
        break;
    case 'upcoming':
        $where = "WHERE preferred_date >= NOW() AND status IN ('pending', 'confirmed')";
        break;
    case 'past':
        $where = "WHERE preferred_date < NOW() OR status IN ('completed', 'cancelled')";
        break;
}

$bookings = [];
if ($tableExists) {
    $result = mysqli_query($conn, "SELECT * FROM iz_bookings $where ORDER BY preferred_date ASC");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $bookings[] = $row;
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-calendar-check"></i> Consultation Bookings</h2>
        <p class="text-muted">Manage client consultation appointments</p>
    </div>
</div>

<!-- Filter Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?php echo $filter === 'upcoming' ? 'active' : ''; ?>" href="?filter=upcoming">
            Upcoming (<?php echo mysqli_num_rows(mysqli_query($conn, "SELECT id FROM iz_bookings WHERE preferred_date >= NOW() AND status IN ('pending', 'confirmed')")); ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $filter === 'pending' ? 'active' : ''; ?>" href="?filter=pending">
            Pending (<?php echo mysqli_num_rows(mysqli_query($conn, "SELECT id FROM iz_bookings WHERE status = 'pending'")); ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $filter === 'confirmed' ? 'active' : ''; ?>" href="?filter=confirmed">Confirmed</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $filter === 'past' ? 'active' : ''; ?>" href="?filter=past">Past</a>
    </li>
</ul>

<!-- Bookings Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="bi bi-inbox"></i> No bookings found for this filter
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>
                                <strong><?php echo date('M j, Y', strtotime($b['preferred_date'])); ?></strong><br>
                                <small class="text-muted"><?php echo date('g:i A', strtotime($b['preferred_date'])); ?> (<?php echo $b['duration']; ?> min)</small>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($b['client_name']); ?></strong><br>
                                <small>
                                    <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($b['client_email']); ?><br>
                                    <?php if (!empty($b['client_phone'])): ?>
                                        <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($b['client_phone']); ?>
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?php echo htmlspecialchars($b['service_type']); ?></span>
                                <?php if (!empty($b['message'])): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars(substr($b['message'], 0, 50)); ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $badges = [
                                    'pending' => 'warning',
                                    'confirmed' => 'success',
                                    'cancelled' => 'danger',
                                    'completed' => 'secondary'
                                ];
                                $badge = $badges[$b['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $badge; ?>"><?php echo ucfirst($b['status']); ?></span>
                                <?php if ($b['status'] === 'confirmed' && !empty($b['google_meet_link'])): ?>
                                    <br><small class="text-success"><i class="bi bi-camera-video-fill"></i> Meet</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary view-booking-btn"
                                        data-booking='<?php echo json_encode($b); ?>'>
                                    <i class="bi bi-eye"></i> View
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-booking-btn"
                                        data-id="<?php echo $b['id']; ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View/Edit Booking Modal -->
<div class="modal fade" id="viewBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="bookings.php" id="editBookingForm">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="id" id="booking_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-calendar-event"></i> Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="bookingDetails"></div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="booking_status">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Admin Notes</label>
                        <textarea class="form-control" name="notes" id="booking_notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden Delete Form -->
<form method="POST" action="bookings.php" id="deleteBookingForm" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteBookingId">
</form>

<script>
// View booking
document.querySelectorAll('.view-booking-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const booking = JSON.parse(this.dataset.booking);

        document.getElementById('booking_id').value = booking.id;
        document.getElementById('booking_status').value = booking.status;
        document.getElementById('booking_notes').value = booking.notes || '';

        // Build Google Meet link section if available
        let meetLinkSection = '';
        if (booking.google_meet_link) {
            meetLinkSection = `
                <div class="alert alert-info">
                    <h6><i class="bi bi-camera-video"></i> Video Consultation Link</h6>
                    <p class="mb-2">
                        <a href="${booking.google_meet_link}" target="_blank" class="btn btn-sm btn-primary">
                            <i class="bi bi-camera-video"></i> Join Google Meet
                        </a>
                    </p>
                    <small>Share this link with the client or send it via email</small>
                </div>
            `;
        } else if (booking.status === 'confirmed') {
            meetLinkSection = `
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    Google Calendar not configured. See GOOGLE-CALENDAR-SETUP.md for instructions.
                </div>
            `;
        }

        const details = `
            <h6>Client Information</h6>
            <p><strong>Name:</strong> ${booking.client_name}<br>
            <strong>Email:</strong> ${booking.client_email}<br>
            <strong>Phone:</strong> ${booking.client_phone || 'N/A'}</p>

            <h6>Appointment Details</h6>
            <p><strong>Service:</strong> ${booking.service_type}<br>
            <strong>Date:</strong> ${new Date(booking.preferred_date).toLocaleString()}<br>
            <strong>Duration:</strong> ${booking.duration} minutes<br>
            ${booking.google_event_id ? '<strong>Calendar Event:</strong> <span class="badge bg-success">Synced</span>' : ''}</p>

            ${meetLinkSection}

            ${booking.message ? `<h6>Client Message</h6><p>${booking.message}</p>` : ''}

            <p><small class="text-muted">Booked on: ${new Date(booking.created_at).toLocaleString()}</small></p>
        `;

        document.getElementById('bookingDetails').innerHTML = details;

        new bootstrap.Modal(document.getElementById('viewBookingModal')).show();
    });
});

// Delete booking
document.querySelectorAll('.delete-booking-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (confirm('Delete this booking?')) {
            document.getElementById('deleteBookingId').value = this.dataset.id;
            document.getElementById('deleteBookingForm').submit();
        }
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

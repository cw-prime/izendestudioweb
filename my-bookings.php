<?php
/**
 * Tenant bookings dashboard (no login) — the business owner opens this from the
 * signed link in their booking-notification email. Lists their appointment
 * requests and lets them confirm / complete / cancel. Scoped strictly to their
 * own tenant_lead_id and gated by a per-site HMAC token.
 */
require_once __DIR__ . '/config/env-loader.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/admin/config/database.php';

initSecureSession();
setSecurityHeaders();
$nonce = getCSPNonce();

function ownerBookingToken($leadId) {
    return substr(hash_hmac('sha256', 'owner:' . $leadId, (string)getEnv('SITE_BOOKING_SECRET', '')), 0, 40);
}

$leadId = (string)($_GET['lead'] ?? ($_POST['lead'] ?? ''));
$token  = (string)($_GET['t'] ?? ($_POST['t'] ?? ''));
$valid  = preg_match('/^[0-9a-f-]{36}$/i', $leadId) && hash_equals(ownerBookingToken($leadId), $token);

if (!$valid) {
    http_response_code(403);
    echo '<!DOCTYPE html><meta charset="utf-8"><title>Bookings</title><body style="font-family:system-ui;max-width:560px;margin:60px auto;padding:0 20px;color:#334155"><h1>Link not valid</h1><p>This bookings link is invalid or expired. Please use the link from your latest booking email, or call (314) 886-6356.</p></body>';
    exit;
}

// ---- status action (PRG) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bid = (int)($_POST['booking_id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');
    $map = ['confirm'=>'confirmed', 'complete'=>'completed', 'cancel'=>'cancelled'];
    if ($bid && isset($map[$action]) && isset($conn) && $conn instanceof mysqli) {
        $new = $map[$action];
        $stmt = $conn->prepare("UPDATE iz_bookings SET status = ? WHERE id = ? AND tenant_lead_id = ?");
        $stmt->bind_param('sis', $new, $bid, $leadId);
        $stmt->execute();
    }
    header('Location: my-bookings.php?lead=' . rawurlencode($leadId) . '&t=' . rawurlencode($token));
    exit;
}

// ---- load this tenant's bookings ----
$rows = [];
$business = '';
if (isset($conn) && $conn instanceof mysqli) {
    $stmt = $conn->prepare("SELECT id, client_name, client_email, client_phone, service_type, preferred_date, message, status, tenant_business, created_at FROM iz_bookings WHERE tenant_lead_id = ? ORDER BY preferred_date DESC");
    $stmt->bind_param('s', $leadId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) { $rows[] = $r; if ($business === '' && !empty($r['tenant_business'])) { $business = $r['tenant_business']; } }
}
$pending = array_filter($rows, fn($r) => $r['status'] === 'pending');
$badge = ['pending'=>'#b45309;background:#fef3c7', 'confirmed'=>'#15803d;background:#dcfce7', 'completed'=>'#475569;background:#e2e8f0', 'cancelled'=>'#b91c1c;background:#fee2e2'];
function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title>Bookings<?= $business ? ' — ' . esc($business) : '' ?></title>
  <style nonce="<?= esc($nonce) ?>">
    body{font-family:system-ui,-apple-system,'Segoe UI',sans-serif;background:#f8fafc;color:#0f172a;margin:0}
    .wrap{max-width:860px;margin:0 auto;padding:32px 18px 64px}
    h1{font-size:24px;margin:0 0 4px}.sub{color:#64748b;margin:0 0 24px}
    .count{display:inline-block;background:#2563eb;color:#fff;border-radius:999px;font-size:13px;font-weight:700;padding:2px 10px;margin-left:8px}
    .card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px;margin:0 0 12px;box-shadow:0 1px 3px rgba(15,23,42,.04)}
    .card .top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap}
    .who{font-weight:700;font-size:16px}
    .when{color:#2563eb;font-weight:600;font-size:14px;margin:2px 0 8px}
    .meta{color:#475569;font-size:14px;line-height:1.6}
    .meta a{color:#2563eb;text-decoration:none}
    .status{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;padding:3px 10px;border-radius:999px}
    .actions{margin-top:12px;display:flex;gap:8px;flex-wrap:wrap}
    .actions button{border:0;cursor:pointer;font-weight:600;font-size:13px;padding:8px 14px;border-radius:8px}
    .b-confirm{background:#16a34a;color:#fff}.b-complete{background:#475569;color:#fff}.b-cancel{background:#fee2e2;color:#b91c1c}
    .empty{text-align:center;color:#64748b;padding:48px 0}
  </style>
</head>
<body>
  <div class="wrap">
    <h1><?= $business ? esc($business) . ' — Bookings' : 'Your Bookings' ?><?php if (count($pending)): ?><span class="count"><?= count($pending) ?> new</span><?php endif; ?></h1>
    <p class="sub">Appointment requests from your website. Confirm to let the customer know you're on.</p>

    <?php if (!count($rows)): ?>
      <div class="empty">No bookings yet. They'll appear here the moment someone books on your site.</div>
    <?php else: foreach ($rows as $r): $st = $r['status']; ?>
      <div class="card">
        <div class="top">
          <div>
            <div class="who"><?= esc($r['client_name']) ?></div>
            <div class="when"><?= esc(date('l, M j Y \a\t g:i A', strtotime($r['preferred_date']))) ?></div>
          </div>
          <span class="status" style="color:<?= $badge[$st] ?? '#475569;background:#e2e8f0' ?>"><?= esc($st) ?></span>
        </div>
        <div class="meta">
          <?php if (!empty($r['service_type'])): ?><?= esc($r['service_type']) ?><br><?php endif; ?>
          <?php if (!empty($r['client_phone'])): ?>📞 <a href="tel:<?= esc($r['client_phone']) ?>"><?= esc($r['client_phone']) ?></a>&nbsp;&nbsp;<?php endif; ?>
          <?php if (!empty($r['client_email'])): ?>✉ <a href="mailto:<?= esc($r['client_email']) ?>"><?= esc($r['client_email']) ?></a><?php endif; ?>
          <?php if (!empty($r['message'])): ?><br><em><?= esc($r['message']) ?></em><?php endif; ?>
        </div>
        <?php if ($st === 'pending' || $st === 'confirmed'): ?>
        <form class="actions" method="post">
          <input type="hidden" name="lead" value="<?= esc($leadId) ?>">
          <input type="hidden" name="t" value="<?= esc($token) ?>">
          <input type="hidden" name="booking_id" value="<?= (int)$r['id'] ?>">
          <?php if ($st === 'pending'): ?><button class="b-confirm" name="action" value="confirm">✓ Confirm</button><?php endif; ?>
          <button class="b-complete" name="action" value="complete">Mark done</button>
          <button class="b-cancel" name="action" value="cancel">Cancel</button>
        </form>
        <?php endif; ?>
      </div>
    <?php endforeach; endif; ?>
  </div>
</body>
</html>

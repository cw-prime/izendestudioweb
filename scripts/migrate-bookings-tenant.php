<?php
/**
 * One-off migration: make iz_bookings multi-tenant for the AI Website booking
 * upsell. Adds tenant_lead_id (Supabase lead uuid the booking belongs to) and
 * tenant_business (business name, for display/email). Izende's own consultation
 * bookings leave these NULL, so existing behavior is unchanged.
 *
 * Run once:  GET /scripts/migrate-bookings-tenant.php?token=<PREVIEW_DEPLOY_SECRET>
 * Idempotent — checks information_schema before each ALTER.
 */
require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../admin/config/database.php';

header('Content-Type: text/plain; charset=utf-8');
$secret = trim((string)getEnv('PREVIEW_DEPLOY_SECRET', ''));
if ($secret === '' || !hash_equals($secret, (string)($_GET['token'] ?? ''))) { http_response_code(403); echo "Forbidden\n"; exit; }

if (!isset($conn) || !($conn instanceof mysqli)) { echo "No DB connection (\$conn).\n"; exit(1); }

$cols = [
    'tenant_lead_id'  => "ADD COLUMN tenant_lead_id VARCHAR(36) NULL AFTER status",
    'tenant_business' => "ADD COLUMN tenant_business VARCHAR(255) NULL AFTER tenant_lead_id",
];
foreach ($cols as $name => $ddl) {
    $res = $conn->query("SELECT COUNT(*) c FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'iz_bookings' AND column_name = '" . $conn->real_escape_string($name) . "'");
    $exists = $res ? (int)$res->fetch_assoc()['c'] : 0;
    if ($exists) { echo "ok: $name already exists\n"; continue; }
    if ($conn->query("ALTER TABLE iz_bookings $ddl")) { echo "added: $name\n"; }
    else { echo "FAILED $name: " . $conn->error . "\n"; }
}
// Helpful index for per-tenant lookups.
$idx = $conn->query("SELECT COUNT(*) c FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name='iz_bookings' AND index_name='idx_tenant_lead'");
if ($idx && (int)$idx->fetch_assoc()['c'] === 0) {
    if ($conn->query("ALTER TABLE iz_bookings ADD INDEX idx_tenant_lead (tenant_lead_id)")) { echo "added index idx_tenant_lead\n"; }
    else { echo "index note: " . $conn->error . "\n"; }
}
echo "done\n";

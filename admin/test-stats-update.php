<?php
/**
 * Test Stats Update - Debug Script
 */

session_start();
require_once __DIR__ . '/config/database.php';

echo "<h1>Stats Update Test</h1>";
echo "<pre>";

// Simulate POST data like the form would send
$_POST = [
    'action' => 'update',
    'stats' => [
        1 => [
            'label' => 'Years Experience',
            'value' => '15',
            'suffix' => '+',
            'icon_class' => 'bi bi-award',
            'display_order' => '1',
            'is_visible' => '1'
        ],
        2 => [
            'label' => 'Projects Completed',
            'value' => '500',
            'suffix' => '+',
            'icon_class' => 'bi bi-check2-circle',
            'display_order' => '2',
            'is_visible' => '1'
        ]
    ]
];

echo "POST Data:\n";
print_r($_POST);
echo "\n\n";

// Run the update logic from stats.php
$action = $_POST['action'] ?? '';
echo "Action: {$action}\n\n";

if ($action === 'update') {
    $stats = $_POST['stats'] ?? [];

    if (empty($stats)) {
        echo "ERROR: No stats data received\n";
    } else {
        echo "Processing " . count($stats) . " stats...\n\n";

        $updateCount = 0;
        foreach ($stats as $id => $data) {
            $id = intval($id);
            $label = trim($data['label'] ?? '');
            $value = trim($data['value'] ?? '');
            $suffix = trim($data['suffix'] ?? '');
            $icon_class = trim($data['icon_class'] ?? '');
            $display_order = intval($data['display_order'] ?? 0);
            $is_visible = isset($data['is_visible']) ? 1 : 0;

            echo "Updating ID {$id}:\n";
            echo "  Label: {$label}\n";
            echo "  Value: {$value}\n";
            echo "  Suffix: {$suffix}\n";
            echo "  Icon: {$icon_class}\n";
            echo "  Order: {$display_order}\n";
            echo "  Visible: {$is_visible}\n";

            $stmt = mysqli_prepare($conn, "
                UPDATE iz_stats
                SET stat_label = ?, stat_value = ?, stat_suffix = ?, icon_class = ?, display_order = ?, is_visible = ?
                WHERE id = ?
            ");

            if (!$stmt) {
                echo "  ERROR: Prepare failed - " . mysqli_error($conn) . "\n";
                continue;
            }

            mysqli_stmt_bind_param($stmt, 'ssssiii', $label, $value, $suffix, $icon_class, $display_order, $is_visible, $id);

            if (mysqli_stmt_execute($stmt)) {
                $affected = mysqli_stmt_affected_rows($stmt);
                echo "  SUCCESS: {$affected} row(s) updated\n";
                $updateCount++;
            } else {
                echo "  ERROR: Execute failed - " . mysqli_error($conn) . "\n";
            }
            echo "\n";
        }

        echo "\n=== RESULT ===\n";
        echo "Successfully updated {$updateCount} out of " . count($stats) . " stats\n";

        $_SESSION['success_message'] = "Stats updated successfully! ({$updateCount} stats updated)";
        echo "Session message set: " . $_SESSION['success_message'] . "\n";
    }
}

echo "</pre>";
?>

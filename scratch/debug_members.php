<?php
require_once __DIR__ . '/../config.php';

echo "=== MEMBERS IN DATABASE ===\n";
$res = $db->query("SELECT id, name, mobile, password, approved, is_active, end_date, shift FROM members");
if ($res) {
    while ($m = $res->fetch_assoc()) {
        echo "ID: {$m['id']} | Name: {$m['name']} | Mobile: '{$m['mobile']}' | Approved: {$m['approved']} | Active: {$m['is_active']} | EndDate: {$m['end_date']} | Shift: {$m['shift']}\n";
    }
} else {
    echo "Query failed: " . $db->error . "\n";
}

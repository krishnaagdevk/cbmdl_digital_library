<?php
require_once __DIR__ . '/../config.php';

echo "Testing login for Kunal (Mobile: 8218772351)\n";
$stmt = $db->prepare("SELECT * FROM members WHERE mobile = ? LIMIT 1");
$m = '8218772351';
$stmt->bind_param("s", $m);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();
echo "Member Found: " . ($member ? $member['name'] : 'NO') . "\n";
if ($member) {
    echo "Approved: {$member['approved']}\n";
    echo "Active: {$member['is_active']}\n";
    echo "End Date: {$member['end_date']} (Current: " . date('Y-m-d') . ")\n";
    echo "Shift: {$member['shift']}\n";
    echo "Shift Allowed: " . (is_member_within_shift_time($member['shift'], $db) ? 'YES' : 'NO') . "\n";
}

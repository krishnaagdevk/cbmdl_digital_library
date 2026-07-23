<?php
require 'config.php';

$res = $db->query("SELECT * FROM work_shifts");
if ($res) {
    print_r($res->fetch_all(MYSQLI_ASSOC));
} else {
    echo "Error: " . $db->error . "\n";
}

$shifts = ['Morning', 'Evening', 'Both'];
foreach ($shifts as $s) {
    $allowed = is_member_within_shift_time($s, $db);
    $win = get_shift_time_window($s, $db);
    echo "Shift '$s' ({$win['start_time']} - {$win['end_time']}): Allowed now? " . ($allowed ? 'YES' : 'NO') . "\n";
}

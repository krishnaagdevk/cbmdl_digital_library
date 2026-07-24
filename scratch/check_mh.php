<?php
require_once 'config.php';
$res = $db->query("SELECT * FROM membership_history WHERE member_id = 11 OR membership_id = 'CBMDLM11'");
echo "--- MEMBERSHIP HISTORY FOR PIYUSH SIR ---\n";
while ($r = $res->fetch_assoc()) {
    print_r($r);
}

$resSchema = $db->query("SHOW CREATE TABLE membership_history");
echo "\n--- MEMBERSHIP HISTORY SCHEMA ---\n";
print_r($resSchema->fetch_assoc());

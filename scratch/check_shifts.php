<?php
require_once 'config.php';

// Migrate any member with shift = 'Both' or 'both' or empty to 'Full Day'
$db->query("UPDATE members SET shift = 'Full Day' WHERE shift = 'Both' OR shift = 'both' OR shift = '' OR shift IS NULL");

echo "UPDATED MEMBERS:\n";
$res2 = $db->query('SELECT id, name, shift FROM members');
while($r2 = $res2->fetch_assoc()) {
    echo "ID: {$r2['id']} | Name: '{$r2['name']}' | Shift: '{$r2['shift']}'\n";
}

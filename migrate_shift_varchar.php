<?php
// One-shot migration: relax members.shift ENUM('Both','Morning','Evening')
// to varchar(50) so custom work_shifts names are storable. Idempotent.
// Run once:  php migrate_shift_varchar.php   then delete this file.
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$name = getenv('DB_NAME') ?: 'cbmdl';
$port = (int)(getenv('DB_PORT') ?: 3306);

mysqli_report(MYSQLI_REPORT_OFF);
$db = @new mysqli($host, $user, $pass, $name, $port)
   ?: @new mysqli('127.0.0.1', $user, $pass, $name, $port);
if ($db->connect_error) { fwrite(STDERR, "Connect failed: {$db->connect_error}\n"); exit(1); }

$col = $db->query("SHOW COLUMNS FROM members LIKE 'shift'")->fetch_assoc();
if (stripos($col['Type'], 'enum') === false) {
    echo "Already varchar ({$col['Type']}). Nothing to do.\n";
    exit(0);
}
if ($db->query("ALTER TABLE members MODIFY `shift` varchar(50) DEFAULT 'Both'")) {
    echo "OK: members.shift -> varchar(50).\n";
} else {
    fwrite(STDERR, "ALTER failed: {$db->error}\n"); exit(1);
}

<?php
require_once __DIR__ . '/../config.php';
$res = $db->query("SELECT id, name, mobile, password FROM members");
while ($r = $res->fetch_assoc()) {
    echo "ID: {$r['id']} | Name: {$r['name']} | Mobile: {$r['mobile']} | Pwd: " . substr($r['password'], 0, 20) . "...\n";
}

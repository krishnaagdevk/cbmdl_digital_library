<?php
require_once 'config.php';
$res = $db->query("SELECT * FROM members WHERE name LIKE '%Piyush%' OR id = 11");
while ($r = $res->fetch_assoc()) {
    print_r($r);
}

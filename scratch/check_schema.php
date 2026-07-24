<?php
require_once 'config.php';
$res = $db->query("SHOW CREATE TABLE member_login_logs");
$row = $res->fetch_assoc();
print_r($row);

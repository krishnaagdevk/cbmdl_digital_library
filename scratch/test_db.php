<?php
require_once __DIR__ . '/../config.php';
$mCount = $db->query("SELECT COUNT(*) c FROM members")->fetch_assoc()['c'];
$bCount = $db->query("SELECT COUNT(*) c FROM ebooks")->fetch_assoc()['c'];
echo "DB Connected Successfully!\nMembers: $mCount\nE-Books: $bCount\n";

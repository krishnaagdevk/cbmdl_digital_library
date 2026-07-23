<?php
$hash = '$2y$10$DZgs2pJISWJqBE8hbpxmK.tfY5CdHke2NxbwBBaBZwT3WvgrSz5rC';
$tests = ['admin123', 'admin', '123456', 'member123', 'password', '1234', 'abhinav', 'abhinav123', '9457421088', 'cbmdl', 'test', 'test123'];
foreach ($tests as $pwd) {
    $ok = password_verify($pwd, $hash);
    if ($ok) echo "MATCH: '$pwd'\n";
}
echo "Done checking " . count($tests) . " passwords.\n";

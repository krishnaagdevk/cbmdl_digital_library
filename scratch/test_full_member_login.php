<?php
$_POST['mobile'] = '8218772351';
$_POST['password'] = '123456'; // or whatever Kunal password is

require_once __DIR__ . '/../config.php';

$stmt = $db->prepare("SELECT * FROM members WHERE mobile = ? LIMIT 1");
$m = $_POST['mobile'];
$stmt->bind_param("s", $m);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();

if ($member) {
    echo "Found member: " . $member['name'] . "\n";
    if (password_verify($_POST['password'], $member['password']) || $_POST['password'] === $member['password']) {
        echo "Password matches!\n";
        $_SESSION['member'] = $member['id'];
        session_write_close();
        echo "Session set to member ID: " . $_SESSION['member'] . "\n";
    } else {
        echo "Password verification failed.\n";
    }
} else {
    echo "Member not found.\n";
}

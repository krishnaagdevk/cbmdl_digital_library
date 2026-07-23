<?php
require_once __DIR__ . '/../config.php';

echo "BASE_URL: " . BASE_URL . "\n";
echo "Cookie Path: " . (rtrim(BASE_URL, '/') ?: '/') . "\n";
echo "Session ID: " . session_id() . "\n";
echo "CSRF Token: " . csrf_token() . "\n";

if (!empty($_SESSION['csrf_token'])) {
    echo "SUCCESS: CSRF token set properly in session.\n";
} else {
    echo "ERROR: CSRF token is empty!\n";
}

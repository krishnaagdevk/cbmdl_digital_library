<?php
// Secure Session configurations
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
    // If session has no data at all (stale cookie pointing to deleted session file),
    // regenerate the ID so the browser gets a fresh, valid session cookie.
    if (empty($_SESSION)) {
        session_regenerate_id(true);
    }
}

// Send strict No-Cache headers to prevent browser storage/history caching of session data
if (!headers_sent()) {
    header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
}

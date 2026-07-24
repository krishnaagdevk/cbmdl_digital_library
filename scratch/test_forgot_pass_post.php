<?php
$cookieFile = __DIR__ . '/test_cookie.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

// 1. GET admin-forgot-password page for CSRF token
$ch = curl_init('http://localhost/cbmdl/admin-forgot-password');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
]);
$html = curl_exec($ch);
curl_close($ch);

preg_match('/name="csrf_token"\s+value="([^"]+)"/', $html, $m);
$csrf = $m[1] ?? '';
echo "1. Obtained CSRF Token: " . $csrf . "\n";

// 2. POST to process_admin_forgot_password with Master PIN 1953
$ch = curl_init('http://localhost/cbmdl/admin-forgot-password?action=process_admin_forgot_password');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'csrf_token' => $csrf,
        'username' => 'admin',
        'verify_method' => 'pin',
        'recovery_pin' => '1953',
        'new_password' => 'admin',
        'confirm_password' => 'admin'
    ]),
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_HEADER => true,
    CURLOPT_FOLLOWLOCATION => false
]);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "2. POST Response HTTP Code: " . $code . "\n";

if (strpos($res, 'Location: admin-login') !== false || strpos($res, 'Location: http') !== false) {
    preg_match('/Location:\s*(.+)/i', $res, $loc);
    echo "SUCCESS: Redirected to: " . trim($loc[1] ?? '') . "\n";
} else {
    echo "RESPONSE BODY:\n" . substr($res, 0, 400) . "\n";
}

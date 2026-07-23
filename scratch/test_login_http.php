<?php
// Simulate the exact login POST flow
$ch = curl_init();
$cookieFile = tempnam(sys_get_temp_dir(), 'cbmdl_cookie_');

// Step 1: GET the login page to get CSRF token + session cookie
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost/cbmdl/admin-login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADER => true,
]);
$resp = curl_exec($ch);
echo "=== Step 1: GET admin-login ===\n";
echo "HTTP code: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";

// Extract CSRF token
preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $resp, $m);
$csrf = $m[1] ?? '';
echo "CSRF token: " . ($csrf ? substr($csrf, 0, 16) . '...' : 'NOT FOUND') . "\n";

if (!$csrf) {
    echo "ERROR: Could not get CSRF token from login page.\n";
    echo "Response headers:\n";
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    echo substr($resp, 0, $headerSize) . "\n";
    exit;
}

// Step 2: POST login credentials
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost/cbmdl/?action=admin_login',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'csrf_token' => $csrf,
        'username'   => 'admin',
        'password'   => 'admin123',
    ]),
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_COOKIEJAR  => $cookieFile,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADER => true,
]);
$resp2 = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "\n=== Step 2: POST admin_login ===\n";
echo "HTTP code: $httpCode\n";

// Extract headers
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($resp2, 0, $headerSize);
$body = substr($resp2, $headerSize);

echo "Response headers:\n$headers\n";
if (strlen($body) > 200) {
    echo "Body (first 500 chars):\n" . substr($body, 0, 500) . "\n";
} else {
    echo "Body:\n$body\n";
}

curl_close($ch);
unlink($cookieFile);

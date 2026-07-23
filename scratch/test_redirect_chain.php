<?php
// Follow full redirect chain
$ch = curl_init();
$cookieFile = tempnam(sys_get_temp_dir(), 'cbmdl_cookie_');

// Step 1: GET the admin-login page
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost/cbmdl/admin-login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADER => true,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
echo "Step 1 GET: $code | URL: $effectiveUrl\n";

preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $resp, $m);
$csrf = $m[1] ?? '';
echo "CSRF: " . ($csrf ? 'found' : 'MISSING') . "\n\n";

if (!$csrf) { exit("No CSRF!\n"); }

// Step 2: POST to admin-login path (as form action does from that page)
// The form action="?action=admin_login" from URL http://localhost/cbmdl/admin-login
// resolves to: http://localhost/cbmdl/admin-login?action=admin_login
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost/cbmdl/admin-login?action=admin_login',
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
$code2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers2 = substr($resp2, 0, $headerSize);
$body2 = substr($resp2, $headerSize);

echo "Step 2 POST to /cbmdl/admin-login?action=admin_login: $code2\n";
// Extract location
preg_match('/^Location: (.+)$/im', $headers2, $loc);
echo "Location header: " . ($loc[1] ?? 'NONE') . "\n";
echo "Full headers:\n$headers2\n";
echo "Body: " . substr($body2, 0, 300) . "\n\n";

// Step 3: Also test the other way - POST directly to /?action=admin_login
// Step 1b: GET admin-login again for fresh CSRF
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost/cbmdl/admin-login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPGET => true,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADER => true,
]);
$resp3 = curl_exec($ch);
preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $resp3, $m2);
$csrf2 = $m2[1] ?? $csrf;

curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost/cbmdl/?action=admin_login',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'csrf_token' => $csrf2,
        'username'   => 'admin',
        'password'   => 'admin123',
    ]),
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_COOKIEJAR  => $cookieFile,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADER => true,
]);
$resp4 = curl_exec($ch);
$code4 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize4 = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers4 = substr($resp4, 0, $headerSize4);
preg_match('/^Location: (.+)$/im', $headers4, $loc4);

echo "Step 3 POST to /cbmdl/?action=admin_login: $code4\n";
echo "Location header: " . ($loc4[1] ?? 'NONE') . "\n\n";

curl_close($ch);
unlink($cookieFile);

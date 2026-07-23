<?php
// Test member login redirect chain
$ch = curl_init();
$cookieFile = tempnam(sys_get_temp_dir(), 'cbmdl_cookie_');

// GET member-login page for CSRF token
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost/cbmdl/member-login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADER => true,
]);
$resp = curl_exec($ch);
preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $resp, $m);
$csrf = $m[1] ?? '';
echo "CSRF found: " . ($csrf ? 'YES' : 'NO') . "\n";

// POST from /cbmdl/member-login path (the real scenario)
$mobile = '9457421088'; // First member
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost/cbmdl/member-login?action=member_login',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'csrf_token' => $csrf,
        'mobile'     => $mobile,
        'password'   => 'test', // likely wrong, we just check redirect
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
preg_match('/^Location: (.+)$/im', $headers2, $loc);

echo "POST to /cbmdl/member-login?action=member_login: HTTP $code2\n";
echo "Location: " . ($loc[1] ?? 'NONE') . "\n";
echo "(Should be /cbmdl/member-login if wrong creds, not just 'member-login')\n";

curl_close($ch);
unlink($cookieFile);

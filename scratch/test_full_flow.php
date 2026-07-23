<?php
// Full login flow simulation - tracks exactly what happens at each step
$ch = curl_init();
$cookieFile = tempnam(sys_get_temp_dir(), 'cbmdl_test_');
$verbose = fopen('php://memory', 'w+');

function doRequest($ch, $url, $method = 'GET', $data = null, $cookieFile = '') {
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_HTTPGET => ($method === 'GET'),
    ]);
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($resp, 0, $headerSize);
    $body = substr($resp, $headerSize);
    preg_match('/^Location:\s*(.+)$/im', $headers, $loc);
    preg_match_all('/^Set-Cookie:\s*(.+)$/im', $headers, $cookies);
    return [
        'code' => $code,
        'headers' => $headers,
        'body' => $body,
        'location' => trim($loc[1] ?? ''),
        'set_cookies' => $cookies[1] ?? [],
    ];
}

function extractCsrf($body) {
    preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $body, $m);
    return $m[1] ?? '';
}

echo "=== FULL ADMIN LOGIN FLOW SIMULATION ===\n\n";

// ====== STEP 1: GET admin-login page ======
$r1 = doRequest($ch, 'http://localhost/cbmdl/admin-login', 'GET', null, $cookieFile);
$csrf = extractCsrf($r1['body']);
echo "STEP 1: GET /cbmdl/admin-login\n";
echo "  HTTP: {$r1['code']}\n";
echo "  CSRF token found: " . ($csrf ? 'YES (' . substr($csrf, 0, 12) . '...)' : 'NO ← PROBLEM') . "\n";
echo "  Set-Cookie: " . implode(' | ', $r1['set_cookies']) . "\n\n";

// ====== STEP 2: POST to the REAL form action URL ======
// The form action="?action=admin_login" from page http://localhost/cbmdl/admin-login
// Browser resolves: http://localhost/cbmdl/admin-login?action=admin_login
$r2 = doRequest($ch, 'http://localhost/cbmdl/admin-login?action=admin_login', 'POST', [
    'csrf_token' => $csrf,
    'username' => 'admin',
    'password' => 'admin123',
], $cookieFile);
echo "STEP 2: POST /cbmdl/admin-login?action=admin_login (with CSRF + credentials)\n";
echo "  HTTP: {$r2['code']}\n";
echo "  Location: {$r2['location']}\n";
echo "  Set-Cookie: " . implode(' | ', $r2['set_cookies']) . "\n\n";

if ($r2['code'] == 302) {
    $location = $r2['location'];
    // Resolve relative URL
    if (!preg_match('#^https?://#', $location)) {
        $location = 'http://localhost' . $location;
    }
    echo "  → Following redirect to: $location\n\n";
    
    // ====== STEP 3: Follow the redirect ======
    $r3 = doRequest($ch, $location, 'GET', null, $cookieFile);
    echo "STEP 3: GET $location\n";
    echo "  HTTP: {$r3['code']}\n";
    if ($r3['code'] == 302) {
        echo "  Location: {$r3['location']} ← REDIRECT LOOP?\n";
        echo "  Body snippet: " . substr(strip_tags($r3['body']), 0, 200) . "\n";
    } else {
        // Check if admin dashboard loaded or login page
        $isLoginPage = strpos($r3['body'], 'csrf_token') !== false && strpos($r3['body'], 'username') !== false;
        $isAdminPage = strpos($r3['body'], 'action=admin') !== false || strpos($r3['body'], 'Dashboard') !== false;
        echo "  → Login page loaded: " . ($isLoginPage ? 'YES ← BUG' : 'no') . "\n";
        echo "  → Admin page loaded: " . ($isAdminPage ? 'YES ✓' : 'no') . "\n";
        echo "  Body snippet: " . substr(strip_tags(str_replace(["\n","\r","\t"], ' ', $r3['body'])), 0, 300) . "\n";
    }
} else {
    echo "  Body: " . substr($r2['body'], 0, 500) . "\n";
}

curl_close($ch);
@unlink($cookieFile);

echo "\n\n=== MEMBER LOGIN FLOW ===\n\n";

$ch2 = curl_init();
$cookieFile2 = tempnam(sys_get_temp_dir(), 'cbmdl_m_');

$r1m = doRequest($ch2, 'http://localhost/cbmdl/member-login', 'GET', null, $cookieFile2);
$csrf_m = extractCsrf($r1m['body']);
echo "STEP 1: GET /cbmdl/member-login\n";
echo "  HTTP: {$r1m['code']}\n";
echo "  CSRF: " . ($csrf_m ? 'found' : 'NOT FOUND ← BUG') . "\n";
echo "  Set-Cookie: " . implode(' | ', $r1m['set_cookies']) . "\n\n";

// Get first member mobile
$firstMobile = '9457421088';
$r2m = doRequest($ch2, 'http://localhost/cbmdl/member-login?action=member_login', 'POST', [
    'csrf_token' => $csrf_m,
    'mobile' => $firstMobile,
    'password' => 'test_wrong_pass',  // Wrong pass to see failure redirect
], $cookieFile2);
echo "STEP 2: POST (wrong password) to /cbmdl/member-login?action=member_login\n";
echo "  HTTP: {$r2m['code']}\n";
echo "  Location: {$r2m['location']}\n\n";

// Now try with the session from step 2 (CSRF regenerated) - simulate second attempt
$r3m = doRequest($ch2, 'http://localhost/cbmdl/member-login', 'GET', null, $cookieFile2);
$csrf_m2 = extractCsrf($r3m['body']);
echo "STEP 3: GET login page after failed attempt\n";
echo "  CSRF on page: " . ($csrf_m2 ? 'found (' . substr($csrf_m2, 0, 12) . '...)' : 'MISSING') . "\n";
echo "  Same CSRF as before: " . ($csrf_m2 === $csrf_m ? 'YES (no rotation)' : 'NO (rotated ← explains second attempt working)') . "\n\n";

curl_close($ch2);
@unlink($cookieFile2);

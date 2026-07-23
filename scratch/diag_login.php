<?php
// Direct login simulation test - bypasses HTTP
require __DIR__ . '/../config.php';

echo "<h2>Login Flow Diagnostics</h2>";
echo "<style>body{font-family:monospace;padding:20px;} .ok{color:green;} .fail{color:red;} .warn{color:orange;}</style>";

// --- Admin Login Test ---
echo "<h3>1. Admin Login Test</h3>";
$stmt = $db->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
if (!$stmt) {
    echo "<p class='fail'>❌ PREPARE FAILED: " . $db->error . "</p>";
} else {
    $un = 'admin';
    $stmt->bind_param("s", $un);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$admin) {
        echo "<p class='fail'>❌ No admin found with username 'admin'</p>";
    } else {
        echo "<p class='ok'>✅ Admin record found: id={$admin['id']}, username={$admin['username']}</p>";
        echo "<p>Password hash (first 20 chars): " . substr($admin['password'], 0, 20) . "...</p>";
        $testPwds = ['admin123', 'admin', 'Admin@123', '123456'];
        foreach ($testPwds as $pwd) {
            $ok = password_verify($pwd, $admin['password']);
            echo "<p class='" . ($ok ? 'ok' : '') . "'>password_verify('$pwd'): " . ($ok ? '✅ MATCH' : '❌ no match') . "</p>";
        }
    }
}

// --- Member Login Test ---
echo "<h3>2. Member Login Test (first member)</h3>";
$res = $db->query("SELECT id, name, mobile, approved, is_active, shift, end_date, LEFT(password,10) as p FROM members WHERE approved=1 AND is_active=1 LIMIT 1");
if ($res && $res->num_rows > 0) {
    $m = $res->fetch_assoc();
    echo "<p class='ok'>✅ Member: {$m['name']} | mobile: {$m['mobile']} | shift: {$m['shift']} | end_date: {$m['end_date']}</p>";
    
    // Check shift
    $now = date('H:i:s');
    echo "<p>Current PHP time: $now</p>";
    $inShift = is_member_within_shift_time($m['shift'], $db);
    echo "<p class='" . ($inShift ? 'ok' : 'fail') . "'>" . ($inShift ? '✅' : '❌') . " Shift check for '{$m['shift']}': " . ($inShift ? 'WITHIN shift' : 'OUTSIDE shift — LOGIN BLOCKED') . "</p>";
    
    // Check expiry
    $expired = $m['end_date'] < date('Y-m-d');
    echo "<p class='" . ($expired ? 'fail' : 'ok') . "'>" . ($expired ? '❌ EXPIRED' : '✅ Active') . " end_date: {$m['end_date']}</p>";
} else {
    echo "<p class='fail'>❌ No approved active members found</p>";
}

// --- Shift Windows ---
echo "<h3>3. Defined Shift Windows</h3>";
$sw = $db->query("SELECT * FROM work_shifts");
if ($sw && $sw->num_rows > 0) {
    while ($s = $sw->fetch_assoc()) {
        $now = date('H:i:s');
        $in = ($now >= $s['start_time'] && $now <= $s['end_time']);
        echo "<p>" . $s['name'] . ": {$s['start_time']} - {$s['end_time']} → <span class='" . ($in ? 'ok' : 'warn') . "'>" . ($in ? 'OPEN' : 'CLOSED') . " at $now</span></p>";
    }
} else {
    echo "<p class='fail'>❌ No work_shifts defined</p>";
}

// --- CSRF token ---
echo "<h3>4. CSRF Token</h3>";
$tok = csrf_token();
echo "<p class='ok'>✅ CSRF token generated: " . substr($tok, 0, 20) . "...</p>";
echo "<p>Session ID: " . session_id() . "</p>";

// --- go() URL check ---
echo "<h3>5. go() URL check</h3>";
echo "<p>BASE_URL: " . BASE_URL . "</p>";
echo "<p>go('?action=admin') sends Location: ?action=admin</p>";
echo "<p>go('admin-login') sends Location: admin-login</p>";
echo "<p>go('?action=user') sends Location: ?action=user</p>";

echo "<hr><p><strong>Summary:</strong> If shift check shows OUTSIDE — that is the member login bug. Admin should work with password 'admin123'.</p>";

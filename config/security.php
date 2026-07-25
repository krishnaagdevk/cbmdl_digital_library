<?php
function log_admin_login($db_conn, $username, $status = 'Success') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $agent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Browser/System', 0, 255);
    $stmt = $db_conn->prepare("INSERT INTO admin_login_logs (username, ip_address, user_agent, status) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssss", $username, $ip, $agent, $status);
        $stmt->execute();
        $stmt->close();
    }
}

function log_member_login($db_conn, $mobile, $member_id = null, $member_name = null, $shift = null, $status = 'Success') {
    if ($shift === 'Both' || $shift === 'both') {
        $shift = 'Full Day';
    } elseif ($shift === '') {
        $shift = null;
    }
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $agent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Browser/System', 0, 255);
    $stmt = $db_conn->prepare("INSERT INTO member_login_logs (member_id, mobile, member_name, shift, ip_address, user_agent, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issssss", $member_id, $mobile, $member_name, $shift, $ip, $agent, $status);
        $stmt->execute();
        $stmt->close();
    }
}

function get_shift_time_window($shift_name, $db_conn) {
    $stmt = $db_conn->prepare("SELECT start_time, end_time FROM work_shifts WHERE name = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $shift_name);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($res) return $res;
    }
    // Default Fallbacks if custom record not defined
    if ($shift_name === 'Morning') return ['start_time' => '08:00:00', 'end_time' => '14:00:00'];
    if ($shift_name === 'Evening') return ['start_time' => '14:00:00', 'end_time' => '20:00:00'];
    return ['start_time' => '08:00:00', 'end_time' => '20:00:00']; // Both
}

function is_member_within_shift_time($shift_name, $db_conn) {
    $shift_info = get_shift_time_window($shift_name, $db_conn);
    $now_time = date('H:i:s');
    $start = $shift_info['start_time'];
    $end = $shift_info['end_time'];

    // Handle overnight shifts if start > end
    if ($start <= $end) {
        return ($now_time >= $start && $now_time <= $end);
    } else {
        return ($now_time >= $start || $now_time <= $end);
    }
}

// Brute-force protection helpers with IP-level persistence
function check_login_lockout() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $key = md5($ip);
    
    if (isset($_SESSION['lockout_until']) && $_SESSION['lockout_until'] > time()) {
        $remaining = $_SESSION['lockout_until'] - time();
        $_SESSION['flash'] = "Too many failed login attempts. Please wait " . ceil($remaining / 60) . " minute(s).";
        return true;
    }
    
    $lockFile = sys_get_temp_dir() . '/cbmdl_lockout_' . $key . '.json';
    if (is_file($lockFile)) {
        $data = json_decode(@file_get_contents($lockFile), true);
        if ($data && isset($data['until']) && $data['until'] > time()) {
            $remaining = $data['until'] - time();
            $_SESSION['flash'] = "Too many failed login attempts from your IP. Please wait " . ceil($remaining / 60) . " minute(s).";
            return true;
        }
    }
    return false;
}

function register_failed_attempt() {
    $_SESSION['failed_attempts'] = ($_SESSION['failed_attempts'] ?? 0) + 1;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $key = md5($ip);
    $lockFile = sys_get_temp_dir() . '/cbmdl_lockout_' . $key . '.json';
    
    $attempts = $_SESSION['failed_attempts'];
    if (is_file($lockFile)) {
        $data = json_decode(@file_get_contents($lockFile), true);
        if ($data && isset($data['attempts'])) {
            $attempts = max($attempts, $data['attempts'] + 1);
        }
    }
    
    if ($attempts >= 5) {
        $until = time() + 600; // 10 minutes lockout
        $_SESSION['lockout_until'] = $until;
        $_SESSION['failed_attempts'] = 0;
        @file_put_contents($lockFile, json_encode(['attempts' => 0, 'until' => $until]));
    } else {
        @file_put_contents($lockFile, json_encode(['attempts' => $attempts, 'until' => 0]));
    }
}

function clear_failed_attempts() {
    unset($_SESSION['failed_attempts']);
    unset($_SESSION['lockout_until']);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $key = md5($ip);
    $lockFile = sys_get_temp_dir() . '/cbmdl_lockout_' . $key . '.json';
    if (is_file($lockFile)) {
        @unlink($lockFile);
    }
}

// CSRF Protection Helpers
if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $currentToken = csrf_token();
        if (empty($token) || !hash_equals($currentToken, $token)) {
            // Rotate the CSRF token for next attempt
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                // Flush session before responding so the new token is saved
                session_write_close();
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Security token expired. Please try again.']);
                exit;
            }
            flash('⚠️ Security token expired or invalid. Please try again.');
            // Flush session NOW so the new CSRF token is persisted before redirect
            session_write_close();
            // Determine safe redirect URL based on current action (do NOT trust HTTP_REFERER)
            $action = $_GET['action'] ?? '';
            if (str_contains($action, 'admin') || str_contains($_SERVER['REQUEST_URI'] ?? '', 'admin-login')) {
                go('admin-login');
            } else {
                go('member-login');
            }
        }
    }
}

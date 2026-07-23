<?php
date_default_timezone_set('Asia/Kolkata');

// Load environment configuration
$envFile = __DIR__ . '/.env';
if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            // Strip matching quotes if any
            if (preg_match('/^"(.*)"$/', $val, $matches) || preg_match('/^\'(.*)\'$/', $val, $matches)) {
                $val = $matches[1];
            }
            $_ENV[$key] = $val;
            putenv("$key=$val");
        }
    }
}

define('BASE_URL', $_ENV['BASE_URL'] ?? '/cbmdl/');

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

spl_autoload_register(function ($class) {
  if (strpos($class, 'App\\') === 0) {
    $file = __DIR__.'/app/'.str_replace('\\', '/', substr($class, 4)).'.php';
    if (is_file($file)) require $file;
  }
});

$db = (new App\Core\Database())->connection();

// Auto-migration checks
$resLending = $db->query("SHOW COLUMNS FROM lendings LIKE 'fine_status'");
if ($resLending && $resLending->num_rows == 0) {
    $db->query("ALTER TABLE lendings ADD COLUMN fine_amount DECIMAL(10,2) DEFAULT 0.00");
    $db->query("ALTER TABLE lendings ADD COLUMN fine_status ENUM('None', 'Outstanding', 'Paid', 'Waived') DEFAULT 'None'");
    $db->query("ALTER TABLE lendings ADD COLUMN fine_payment_id VARCHAR(150) NULL");
}
$resMember = $db->query("SHOW COLUMNS FROM members LIKE 'is_active'");
if ($resMember && $resMember->num_rows == 0) {
    $db->query("ALTER TABLE members ADD COLUMN is_active TINYINT(1) DEFAULT 1");
}
$db->query("ALTER TABLE print_requests MODIFY COLUMN status ENUM('Pending', 'Completed', 'Rejected') DEFAULT 'Pending'");

// Auto-migration check: membership plans table
$db->query("CREATE TABLE IF NOT EXISTS membership_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    duration VARCHAR(50) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL
)");

// Prepopulate default plans if empty
$resPlanCountQuery = $db->query("SELECT COUNT(*) c FROM membership_plans");
if ($resPlanCountQuery) {
    $resPlanCount = $resPlanCountQuery->fetch_assoc()['c'];
    if ($resPlanCount == 0) {
        $db->query("INSERT INTO membership_plans (name, duration, amount) VALUES 
            ('Gold Yearly', 'Yearly', 1200.00),
            ('Standard Half Yearly', 'Half Yearly', 700.00),
            ('Quarterly Study Pass', 'Quarterly', 400.00),
            ('Monthly Reader', 'Monthly', 150.00),
            ('Daily Tourist', 'Daily', 20.00)");
    }
}

// Add membership_plan_id to members table
$resMemberPlan = $db->query("SHOW COLUMNS FROM members LIKE 'membership_plan_id'");
if ($resMemberPlan && $resMemberPlan->num_rows == 0) {
    $db->query("ALTER TABLE members ADD COLUMN membership_plan_id INT NULL");
}

// Add membership_fee to members table
$resMemberFee = $db->query("SHOW COLUMNS FROM members LIKE 'membership_fee'");
if ($resMemberFee && $resMemberFee->num_rows == 0) {
    $db->query("ALTER TABLE members ADD COLUMN membership_fee VARCHAR(50) DEFAULT ''");
}

// Add shelf_number to physical_books table
$resShelf = $db->query("SHOW COLUMNS FROM physical_books LIKE 'shelf_number'");
if ($resShelf && $resShelf->num_rows == 0) {
    $db->query("ALTER TABLE physical_books ADD COLUMN shelf_number VARCHAR(100) NULL AFTER book_code");
}

// Add approved column to members table (0 = Pending Self-Service Application, 1 = Approved Active Member)
$resMemberApproved = $db->query("SHOW COLUMNS FROM members LIKE 'approved'");
if ($resMemberApproved && $resMemberApproved->num_rows == 0) {
    $db->query("ALTER TABLE members ADD COLUMN approved TINYINT(1) DEFAULT 1");
}

// Auto-migration check: Unique index on Aadhar number in members table
$resAadharIdx = $db->query("SHOW INDEX FROM members WHERE Key_name = 'idx_aadhar_no'");
if ($resAadharIdx && $resAadharIdx->num_rows == 0) {
    @$db->query("ALTER TABLE members ADD UNIQUE INDEX idx_aadhar_no (aadhar_no)");
}


// Auto-migration check: Unique index on Payment Transaction ID in members table
$resPayIdx = $db->query("SHOW INDEX FROM members WHERE Key_name = 'idx_payment_id'");
if ($resPayIdx && $resPayIdx->num_rows == 0) {
    @$db->query("ALTER TABLE members ADD UNIQUE INDEX idx_payment_id (payment_id)");
}

// Auto-migration: Ensure unique index on mobile in members table
$resMobileIdx = $db->query("SHOW INDEX FROM members WHERE Key_name = 'idx_mobile'");
if ($resMobileIdx && $resMobileIdx->num_rows == 0) {
    @$db->query("ALTER TABLE members ADD UNIQUE INDEX idx_mobile (mobile)");
}

// Auto-migration check: Unique index on category_id + title in ebooks table
$resEbookIdx = $db->query("SHOW INDEX FROM ebooks WHERE Key_name = 'idx_category_title'");
if ($resEbookIdx && $resEbookIdx->num_rows == 0) {
    @$db->query("ALTER TABLE ebooks ADD UNIQUE INDEX idx_category_title (category_id, title)");
}

// Auto-migration check: duration_minutes and started_reading_at in reading_requests
$resRRDuration = $db->query("SHOW COLUMNS FROM reading_requests LIKE 'duration_minutes'");
if ($resRRDuration && $resRRDuration->num_rows == 0) {
    @$db->query("ALTER TABLE reading_requests ADD COLUMN duration_minutes INT DEFAULT 15");
}
$resRRStarted = $db->query("SHOW COLUMNS FROM reading_requests LIKE 'started_reading_at'");
if ($resRRStarted && $resRRStarted->num_rows == 0) {
    @$db->query("ALTER TABLE reading_requests ADD COLUMN started_reading_at DATETIME NULL");
}

// Add shift column to members table (Both, Morning, Evening)
$resShift = $db->query("SHOW COLUMNS FROM members LIKE 'shift'");
if ($resShift && $resShift->num_rows == 0) {
    $db->query("ALTER TABLE members ADD COLUMN shift ENUM('Both', 'Morning', 'Evening') DEFAULT 'Both' AFTER duration");
}

// Add gender column to members table (Male, Female, Other)
$resGender = $db->query("SHOW COLUMNS FROM members LIKE 'gender'");
if ($resGender && $resGender->num_rows == 0) {
    $db->query("ALTER TABLE members ADD COLUMN gender ENUM('Male', 'Female', 'Other') DEFAULT 'Male' AFTER name");
}

// Auto-migration check: membership_history table
$db->query("CREATE TABLE IF NOT EXISTS membership_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    membership_id VARCHAR(30) NOT NULL,
    membership_plan_id INT NULL,
    plan_name VARCHAR(100) NULL,
    duration VARCHAR(50) NOT NULL,
    shift VARCHAR(50) DEFAULT 'Both',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    membership_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_id VARCHAR(150) NULL,
    action_type ENUM('Initial Joining', 'Renewal', 'Plan Switch', 'Manual Adjustment') NOT NULL DEFAULT 'Initial Joining',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Backfill initial membership_history entries for existing approved members if history is empty
$resHistCountQuery = $db->query("SELECT COUNT(*) c FROM membership_history");
if ($resHistCountQuery) {
    $resHistCount = $resHistCountQuery->fetch_assoc()['c'];
    if ($resHistCount == 0) {
        $db->query("INSERT INTO membership_history (member_id, membership_id, membership_plan_id, plan_name, duration, shift, start_date, end_date, membership_fee, payment_id, action_type, created_at)
            SELECT 
                m.id as member_id,
                m.membership_id,
                m.membership_plan_id,
                p.name as plan_name,
                IF(m.duration != '', m.duration, 'Yearly') as duration,
                IF(m.shift != '', m.shift, 'Both') as shift,
                m.start_date,
                m.end_date,
                CAST(m.membership_fee AS DECIMAL(10,2)) as membership_fee,
                m.payment_id,
                'Initial Joining' as action_type,
                m.created_at
            FROM members m
            LEFT JOIN membership_plans p ON m.membership_plan_id = p.id
            WHERE m.approved = 1 AND m.membership_id != ''");
    }
}

// Auto-migration check: work_shifts table for dynamic shift time definitions
$db->query("CREATE TABLE IF NOT EXISTS work_shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL
)");

// Insert default market standard shifts if empty
$shiftCountRes = $db->query("SELECT COUNT(*) c FROM work_shifts");
if ($shiftCountRes && (int)($shiftCountRes->fetch_assoc()['c'] ?? 0) === 0) {
    $db->query("INSERT INTO work_shifts (name, start_time, end_time) VALUES 
        ('Morning', '08:00:00', '14:00:00'),
        ('Evening', '14:00:00', '20:00:00'),
        ('Both', '08:00:00', '20:00:00')");
}

// Auto-migration check: login log tables for admin and member login audits
$db->query("CREATE TABLE IF NOT EXISTS admin_login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Success',
    login_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS member_login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NULL,
    mobile VARCHAR(20) NOT NULL,
    member_name VARCHAR(100) DEFAULT NULL,
    shift VARCHAR(50) DEFAULT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Success',
    login_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

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

// Auto-migration check: hold requests table for physical book reservations
$db->query("CREATE TABLE IF NOT EXISTS hold_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    physical_book_id INT NOT NULL,
    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Active', 'Fulfilled', 'Cancelled') DEFAULT 'Active',
    FOREIGN KEY(member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY(physical_book_id) REFERENCES physical_books(id) ON DELETE CASCADE
)");

// Auto-migration check: renewal requests table for online member pass renewals
$db->query("CREATE TABLE IF NOT EXISTS renewal_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    membership_plan_id INT NOT NULL,
    shift VARCHAR(50) DEFAULT 'Morning',
    payment_id VARCHAR(150) NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    approved_at DATETIME NULL,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (membership_plan_id) REFERENCES membership_plans(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function admin(){ return isset($_SESSION['admin']); }
function member(){ return isset($_SESSION['member']); }
function expire_member_reading_requests($memberId, mysqli $db) {
    $memberId = (int)$memberId;
    if ($memberId <= 0) return;
    $stmt = $db->prepare("UPDATE reading_requests SET status = 'Expired', expires_at = NOW() WHERE member_id = ? AND status IN ('Pending', 'Approved')");
    if ($stmt) {
        $stmt->bind_param("i", $memberId);
        $stmt->execute();
        $stmt->close();
    }
}
function go($url){
    // Build absolute URL to prevent relative-redirect mis-resolution (e.g. from /cbmdl/admin-login path)
    if (!preg_match('#^https?://#i', $url)) {
        $base = rtrim(BASE_URL, '/');
        if (str_starts_with($url, '?') || str_starts_with($url, '#')) {
            // Query/fragment-only: append to base app root
            $url = $base . '/' . $url;
        } elseif (!str_starts_with($url, '/')) {
            // Relative path like 'admin-login' or 'member-login'
            $url = $base . '/' . $url;
        }
    }
    header('Location: ' . $url);
    exit;
}
function flash($msg=''){ if($msg !== ''){ $_SESSION['flash']=$msg; return $msg; } $x=$_SESSION['flash']??''; unset($_SESSION['flash']); return $x; }
function membership_end($duration, $startDate = null){ 
    $map = [
        'Yearly' => '+1 year',
        'Half Yearly' => '+6 months',
        'Quarterly' => '+3 months',
        'Monthly' => '+1 month',
        'Daily' => '+1 day'
    ]; 
    $span = isset($map[$duration]) ? $map[$duration] : '+1 year';
    $baseTime = $startDate ? strtotime($startDate) : time();
    return date('Y-m-d', strtotime($span, $baseTime)); 
}
function active_member($m){ return $m && $m['end_date'] >= date('Y-m-d') && (!isset($m['is_active']) || $m['is_active'] == 1); }

if (!function_exists('number_style_format')) {
    function number_style_format($n) {
        return number_format((float)$n, 2, '.', ',');
    }
}

function render_fine_column($r, $fine_data, $tab) {
    if ($r['fine_status'] === 'Paid') {
        return '<span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> Settled (Ref: ' . e($r['fine_payment_id']) . ')</span>';
    }
    if ($r['fine_status'] === 'Waived') {
        return '<span class="badge badge-blue"><i class="fa-solid fa-circle-xmark"></i> Waived (Ref: ' . e($r['fine_payment_id']) . ')</span>';
    }
    if ($fine_data['days'] > 0) {
        $out = '<div style="display:flex; flex-direction:column; gap:5px;">';
        $out .= '<span class="badge badge-red"><i class="fa-solid fa-triangle-exclamation"></i> Overdue (' . $fine_data['days'] . 'd)</span>';
        $out .= '<form method="post" action="?action=settle_fine" style="display:inline-flex; gap:4px; align-items:center; margin:0;">';
        $out .= csrf_input();
        $out .= '<input type="hidden" name="id" value="' . $r['id'] . '">';
        $out .= '<input type="hidden" name="tab" value="' . e($tab) . '">';
        $out .= '<input type="hidden" name="fine_amount" value="0">';
        $out .= '<select name="fine_status" style="width:80px; margin:0; padding:4px; font-size:11px;" required>';
        $out .= '<option value="Paid">Cleared</option>';
        $out .= '<option value="Waived">Waived</option>';
        $out .= '</select>';
        $out .= '<input name="fine_payment_id" placeholder="Ref ID" required style="width:70px; margin:0; padding:4px; font-size:11px;">';
        $out .= '<button style="padding:4px 8px; font-size:11px;"><i class="fa-solid fa-circle-check"></i> Clear</button>';
        $out .= '</form>';
        $out .= '</div>';
        return $out;
    }
    return '<span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> Clean</span>';
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

/**
 * Stream binary files with high-performance HTTP 206 Partial Content Range support.
 * Serves requested slices with fixed-memory stream loops, keeping constant server RAM footprint.
 */
function stream_file_ranged($file, $contentType = 'application/pdf', $isPrivate = true, $cacheMaxAge = 300) {
    if (!is_file($file)) {
        http_response_code(404);
        exit('File not found.');
    }
    
    // Release PHP session lock immediately so concurrent Range requests run in parallel with zero latency
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    
    // Clear all active output buffers to allow immediate chunk streaming
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Prevent script execution timeout on large file streams
    @set_time_limit(0);
    
    $size = filesize($file);
    $mtime = filemtime($file);
    
    // High-performance ETag based on mtime + size (O(1) instant lookup without reading entire file)
    $etag = sprintf('"%x-%x"', $mtime, $size);
    $lastMod = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
    
    // Handle conditional GET (304 Not Modified)
    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag) {
        http_response_code(304);
        exit;
    }
    
    // Parse Range header
    $rangeHeader = $_SERVER['HTTP_RANGE'] ?? null;
    $start = 0;
    $end = $size - 1;
    
    if ($rangeHeader && preg_match('/bytes=(\d*)-(\d*)/', $rangeHeader, $m)) {
        $start = $m[1] !== '' ? (int)$m[1] : $size - (int)$m[2];
        $end   = $m[2] !== '' ? min((int)$m[2], $size - 1) : $size - 1;
        http_response_code(206);
        header("Content-Range: bytes $start-$end/$size");
    } else {
        http_response_code(200);
    }
    
    $length = $end - $start + 1;
    
    header('Content-Type: ' . $contentType);
    header('Content-Length: ' . $length);
    header('Accept-Ranges: bytes');
    header('ETag: ' . $etag);
    header('Last-Modified: ' . $lastMod);
    
    if ($isPrivate) {
        header('Cache-Control: private, max-age=' . $cacheMaxAge . ', must-revalidate');
    } else {
        header('Cache-Control: public, max-age=' . $cacheMaxAge . ', must-revalidate');
    }
    
    header('Content-Disposition: inline');
    header('X-Content-Type-Options: nosniff');
    
    // Stream requested byte range in optimal 64KB chunks
    $fp = fopen($file, 'rb');
    if ($fp) {
        fseek($fp, $start);
        $remaining = $length;
        while ($remaining > 0 && !feof($fp)) {
            $chunk = min(65536, $remaining); // 64KB chunks
            echo fread($fp, $chunk);
            $remaining -= $chunk;
            if (connection_aborted()) break;
        }
        fclose($fp);
    }
    exit;
}
?>
<?php
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
        'path' => BASE_URL,
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
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

// Auto-migration check: Unique index on category_id + title in ebooks table
$resEbookIdx = $db->query("SHOW INDEX FROM ebooks WHERE Key_name = 'idx_category_title'");
if ($resEbookIdx && $resEbookIdx->num_rows == 0) {
    @$db->query("ALTER TABLE ebooks ADD UNIQUE INDEX idx_category_title (category_id, title)");
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

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function admin(){ return isset($_SESSION['admin']); }
function member(){ return isset($_SESSION['member']); }
function go($url){ header('Location: '.$url); exit; }
function flash($msg=''){ if($msg) $_SESSION['flash']=$msg; $x=$_SESSION['flash']??''; unset($_SESSION['flash']); return $x; }
function membership_end($duration){ 
    $map = [
        'Yearly' => '+1 year',
        'Half Yearly' => '+6 months',
        'Quarterly' => '+3 months',
        'Monthly' => '+1 month',
        'Daily' => '+1 day'
    ]; 
    $span = isset($map[$duration]) ? $map[$duration] : '+1 year';
    return date('Y-m-d', strtotime($span)); 
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

// Brute-force protection helpers
function check_login_lockout() {
    if (isset($_SESSION['lockout_until']) && $_SESSION['lockout_until'] > time()) {
        $remaining = $_SESSION['lockout_until'] - time();
        $_SESSION['flash'] = "Too many failed login attempts. Please wait " . ceil($remaining / 60) . " minute(s).";
        return true;
    }
    return false;
}
function register_failed_attempt() {
    $_SESSION['failed_attempts'] = ($_SESSION['failed_attempts'] ?? 0) + 1;
    if ($_SESSION['failed_attempts'] >= 5) {
        $_SESSION['lockout_until'] = time() + 600; // 10 minutes lockout
        $_SESSION['failed_attempts'] = 0;
    }
}
function clear_failed_attempts() {
    unset($_SESSION['failed_attempts']);
    unset($_SESSION['lockout_until']);
}

// CSRF Protection Helpers
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token() {
    return $_SESSION['csrf_token'] ?? '';
}

function csrf_input() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            exit('CSRF token validation failed.');
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
    
    $size = filesize($file);
    
    // ETag for caching
    $etag = '"' . md5_file($file) . '"';
    $lastMod = gmdate('D, d M Y H:i:s', filemtime($file)) . ' GMT';
    
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
        header('Cache-Control: private, max-age=' . $cacheMaxAge);
    } else {
        header('Cache-Control: public, max-age=' . $cacheMaxAge);
    }
    
    header('Content-Disposition: inline');
    header("Content-Security-Policy: default-src 'self'; script-src 'none'; object-src 'self'; style-src 'unsafe-inline'; frame-src 'self';");
    header('X-Content-Type-Options: nosniff');
    
    // Stream only requested byte range
    $fp = fopen($file, 'rb');
    fseek($fp, $start);
    $remaining = $length;
    while ($remaining > 0 && !feof($fp)) {
        $chunk = min(8192, $remaining); // 8KB chunks
        echo fread($fp, $chunk);
        $remaining -= $chunk;
        flush();
    }
    fclose($fp);
    exit;
}
?>
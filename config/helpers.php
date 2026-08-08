<?php
function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function admin() {
    return isset($_SESSION['admin']);
}

function member() {
    return isset($_SESSION['member']);
}

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

function go($url) {
    if (is_spa_request()) {
        if (!str_contains($url, 'spa=1')) {
            $url .= (str_contains($url, '?') ? '&' : '?') . 'spa=1';
        }
    }
    // Build absolute URL to prevent relative-redirect mis-resolution (e.g. from /cbmdl/admin-login path)
    if (!preg_match('#^https?://#i', $url)) {
        $base = rtrim(BASE_URL, '/');
        if (str_starts_with($url, '?') || str_starts_with($url, '#')) {
            // Query/fragment-only: append to base app root
            $url = $base . '/' . $url;
        } elseif (!str_starts_with($url, '/')) {
            $url = $base . '/' . $url;
        }
    }
    if (is_spa_request() && !str_contains($url, 'spa=1')) {
        $sep = str_contains($url, '?') ? '&' : '?';
        $url .= $sep . 'spa=1';
    }
    header('Location: ' . $url);
    exit;
}

function flash($msg = '') {
    if ($msg !== '') {
        $_SESSION['flash'] = $msg;
        return $msg;
    }
    $x = $_SESSION['flash'] ?? '';
    unset($_SESSION['flash']);
    return $x;
}

function membership_end($duration, $startDate = null) { 
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

function valid_shift($db, $shift, $default = 'Both') {
    $shift = trim((string)$shift);
    if ($shift === '') return $default;
    $stmt = $db->prepare("SELECT name FROM work_shifts WHERE LOWER(TRIM(name)) = LOWER(?) LIMIT 1");
    if (!$stmt) return $shift; // don't clobber input if the check can't run
    $stmt->bind_param("s", $shift);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $stmt->close();
        return $row['name'];
    }
    $stmt->close();
    return $default;
}

function active_member($m, $db = null, $date = null) {
    if (!$m || (isset($m['is_active']) && $m['is_active'] == 0) || (isset($m['approved']) && $m['approved'] == 0)) {
        return false;
    }
    $targetDate = $date ?? date('Y-m-d');
    
    // Check if member's start_date and end_date cover targetDate
    if (!empty($m['start_date']) && !empty($m['end_date'])) {
        if ($m['start_date'] <= $targetDate && $m['end_date'] >= $targetDate) {
            return true;
        }
    }
    
    // Fallback check on membership_history table for any pass active on targetDate
    if ($db && !empty($m['id'])) {
        $stmt = $db->prepare("SELECT id FROM membership_history WHERE member_id = ? AND start_date <= ? AND end_date >= ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("iss", $m['id'], $targetDate, $targetDate);
            $stmt->execute();
            $res = $stmt->get_result();
            $hasValidPass = ($res && $res->num_rows > 0);
            $stmt->close();
            if ($hasValidPass) {
                return true;
            }
        }
    }
    
    return false;
}

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

function get_smart_pagination_items(int $current_page, int $total_pages): array {
    if ($total_pages <= 1) return [1];
    if ($total_pages <= 5) return range(1, $total_pages);

    $items = [1];

    $start = max(2, $current_page - 1);
    $end = min($total_pages - 1, $current_page + 1);

    if ($current_page <= 2) {
        $start = 2;
        $end = 3;
    } elseif ($current_page >= $total_pages - 1) {
        $start = $total_pages - 2;
        $end = $total_pages - 1;
    }

    if ($start > 2) {
        $items[] = '...';
    }

    for ($i = $start; $i <= $end; $i++) {
        $items[] = $i;
    }

    if ($end < $total_pages - 1) {
        $items[] = '...';
    }

    $items[] = $total_pages;

    return $items;
}

function calculate_fine($due_date, $returned_at = null) {
    $due = strtotime($due_date);
    $end = $returned_at ? strtotime($returned_at) : time();
    
    // Normalize timestamps to date only
    $due_day = strtotime(date('Y-m-d', $due));
    $end_day = strtotime(date('Y-m-d', $end));
    
    if ($end_day > $due_day) {
        $diff = $end_day - $due_day;
        $days = (int)ceil($diff / (60 * 60 * 24));
        $rate = 0.00; // ₹5 per day
        return [
            'days' => $days,
            'fine' => $days * $rate
        ];
    }
    return ['days' => 0, 'fine' => 0.00];
}

function log_membership_history($db, $member_id, $action_type = 'Initial Joining') {
    $stmt = $db->prepare("SELECT m.*, p.name as plan_name FROM members m LEFT JOIN membership_plans p ON m.membership_plan_id = p.id WHERE m.id = ?");
    if (!$stmt) return;
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $m = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($m && !empty($m['membership_id'])) {
        $pName = !empty($m['duration']) ? $m['duration'] : (!empty($m['plan_name']) ? $m['plan_name'] : 'N/A');
        $fee = (float)($m['membership_fee'] ?? 0.00);
        $shift = !empty($m['shift']) ? valid_shift($db, $m['shift'], 'Full Day') : 'Full Day';
        $histStmt = $db->prepare("INSERT INTO membership_history (member_id, membership_id, membership_plan_id, plan_name, duration, shift, start_date, end_date, membership_fee, payment_id, action_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($histStmt) {
            $histStmt->bind_param("isisssssdss", $m['id'], $m['membership_id'], $m['membership_plan_id'], $pName, $m['duration'], $shift, $m['start_date'], $m['end_date'], $fee, $m['payment_id'], $action_type);
            $histStmt->execute();
            $histStmt->close();
        }
    }
}

function is_spa_request() {
    // 1. Direct browser document loads/reloads (F5, address bar entry) must ALWAYS receive full HTML page
    if (isset($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] === 'document') {
        return false;
    }

    // 2. Check for explicit SPA / AJAX headers sent by client JS fetch calls
    if (isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === '1') {
        return true;
    }
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        return true;
    }

    return false;
}

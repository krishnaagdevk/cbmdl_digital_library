<?php
// views/admin/requests.php
if (!defined('BASE_URL')) exit;

$status_filter = $_GET['status_filter'] ?? 'All';
$search = trim($_GET['search'] ?? '');

$where_clauses = [];
if ($status_filter === 'Pending') {
    $where_clauses[] = "r.status = 'Pending'";
} elseif ($status_filter === 'Approved') {
    $where_clauses[] = "r.status = 'Approved' AND (r.expires_at IS NULL OR r.expires_at > NOW())";
} elseif ($status_filter === 'Rejected') {
    $where_clauses[] = "r.status = 'Rejected'";
} elseif ($status_filter === 'Expired') {
    $where_clauses[] = "(r.status = 'Expired' OR (r.status = 'Approved' AND r.expires_at <= NOW()))";
}

if ($search !== '') {
    $search_escaped = $db->real_escape_string($search);
    $where_clauses[] = "(m.name LIKE '%$search_escaped%' OR m.membership_id LIKE '%$search_escaped%' OR e.title LIKE '%$search_escaped%')";
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = ' WHERE ' . implode(' AND ', $where_clauses);
}

$p_limit = 10;
$p_page = max(1, (int)($_GET['p_page'] ?? 1));

$cnt_query_str = "SELECT COUNT(*) c FROM reading_requests r JOIN members m ON m.id = r.member_id JOIN ebooks e ON e.id = r.ebook_id $where_sql";
$total_items = (int)($db->query($cnt_query_str)->fetch_assoc()['c'] ?? 0);
$total_pages = ceil($total_items / $p_limit);
$p_offset = ($p_page - 1) * $p_limit;

$query_str = "SELECT r.*, m.name, m.membership_id, e.title FROM reading_requests r JOIN members m ON m.id = r.member_id JOIN ebooks e ON e.id = r.ebook_id $where_sql ORDER BY r.requested_at DESC LIMIT $p_limit OFFSET $p_offset";
?>

<div class="card" style="margin-bottom: 25px;">
    <h3><i class="fa-solid fa-filter"></i>Filter</h3>
    <form method="get" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <input type="hidden" name="action" value="admin">
        <input type="hidden" name="tab" value="requests">
        
        <div>
            <label for="req_search">Search Member ID / Name / E-Book</label>
            <input id="req_search" name="search" value="<?= e($search) ?>" placeholder="Enter member ID, name, or book title...">
        </div>
        
        <div>
            <label for="req_status">Request Status</label>
            <select id="req_status" name="status_filter">
                <option value="All" <?= $status_filter === 'All' ? 'selected' : '' ?>>All Requests</option>
                <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending Requests Only</option>
                <option value="Approved" <?= $status_filter === 'Approved' ? 'selected' : '' ?>>Approved Requests Only</option>
                <option value="Rejected" <?= $status_filter === 'Rejected' ? 'selected' : '' ?>>Rejected Requests Only</option>
                <option value="Expired" <?= $status_filter === 'Expired' ? 'selected' : '' ?>>Expired Requests Only</option>
            </select>
        </div>
        
        <div>
            <label style="visibility:hidden; margin-bottom:6px; display:block;">Action</label>
            <div style="display:flex; gap:8px;">
                <button type="submit" style="flex:1; padding:12px;"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
                <a href="?action=admin&tab=requests" class="btn btn-secondary" style="display:flex; align-items:center; justify-content:center; padding:12px; background:var(--bg-slate); color:var(--text-color); border:1px solid var(--border-color); text-decoration:none;"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <h3><i class="fa-solid fa-inbox"></i> Member e-Reading Requests Inbox</h3>
    <div class="table-responsive">
        <table id="requestsTable">
            <thead>
                <tr>
                    <th>Member ID</th>
                    <th>Member Name</th>
                    <th>E-Book Title</th>
                    <th>Request Timestamp</th>
                    <th>Status</th>
                    <th>Permission / Expiry Duration</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $x = $db->query($query_str);
                if ($x->num_rows === 0) {
                    echo '<tr><td colspan="6" style="text-align:center; padding: 30px; color:var(--text-muted);"><i class="fa-solid fa-circle-info" style="font-size:20px; margin-bottom:10px; display:block; color:var(--primary);"></i> No requests match the selected filters.</td></tr>';
                } else {
                    while($r = $x->fetch_assoc()) {
                        $isExp = ($r['status'] === 'Expired') || ($r['status'] === 'Approved' && !empty($r['expires_at']) && strtotime($r['expires_at']) <= time());
                        $statusText = $isExp ? 'Expired' : $r['status'];
                        $badgeClass = 'badge-orange';
                        if ($r['status'] === 'Approved' && !$isExp) $badgeClass = 'badge-green';
                        if ($r['status'] === 'Rejected' || $isExp) $badgeClass = 'badge-red';
                        
                        echo '<tr>';
                        echo '<td><code>' . e($r['membership_id']) . '</code></td>';
                        echo '<td><strong style="color:var(--navy-dark);">' . e($r['name']) . '</strong></td>';
                        echo '<td>' . e($r['title']) . '</td>';
                        echo '<td>' . date('d-m-Y h:i A', strtotime($r['requested_at'])) . '</td>';
                        echo '<td><span class="badge ' . $badgeClass . '">' . $statusText . '</span></td>';
                        echo '<td>';
                        if ($r['status'] === 'Pending') {
                            echo '
                            <div style="display:inline-flex; gap:6px; align-items:center;">
                                 <form method="post" action="?action=approve" style="display:inline-flex; gap:6px; align-items:center; margin:0;">
                                     ' . csrf_input() . '
                                     <input type="hidden" name="request_id" value="' . $r['id'] . '">
                                     <input type="number" name="minutes" min="1" placeholder="Minutes" required style="width:90px; margin:0; padding:8px;">
                                     <button class="btn" style="padding:8px 12px; background:var(--accent-green);"><i class="fa-solid fa-check"></i> Grant Access</button>
                                 </form>
                                 <form method="post" action="?action=reject" style="display:inline-block; margin:0;">
                                     ' . csrf_input() . '
                                     <input type="hidden" name="request_id" value="' . $r['id'] . '">
                                     <button class="btn btn-danger" style="padding:8px 12px;"><i class="fa-solid fa-xmark"></i> Reject</button>
                                 </form>
                            </div>';
                        } elseif ($r['status'] === 'Approved' && !$isExp) {
                            if (!empty($r['expires_at'])) {
                                echo '<span style="font-size:12px; color:var(--accent-green); font-weight:600;"><i class="fa-solid fa-circle-check"></i> Active session till ' . date('d-m-Y h:i A', strtotime($r['expires_at'])) . '</span>';
                            } elseif (!empty($r['approved_at'])) {
                                echo '<span style="font-size:12px; color:var(--accent-green); font-weight:600;"><i class="fa-solid fa-circle-check"></i> Approved at ' . date('d-m-Y h:i A', strtotime($r['approved_at'])) . ' (' . (int)$r['duration_minutes'] . ' mins)</span>';
                            } else {
                                echo '<span style="font-size:12px; color:var(--accent-green); font-weight:600;"><i class="fa-solid fa-circle-check"></i> Approved (' . (int)$r['duration_minutes'] . ' mins)</span>';
                            }
                        } elseif ($isExp) {
                            echo '<span style="font-size:12px; color:var(--text-muted); font-weight:600;"><i class="fa-solid fa-clock-rotate-left"></i> Session Expired</span>';
                        } else {
                            echo '<span style="font-size:12px; color:var(--accent-red); font-weight:600;"><i class="fa-solid fa-circle-xmark"></i> Permission Declined</span>';
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Premium Pagination Component -->
    <?php if ($total_pages > 1): ?>
        <?php
        $qs = $_GET;
        unset($qs['p_page']);
        $qs_str = http_build_query($qs);
        $qs_str = $qs_str ? '&' . $qs_str : '';
        ?>
        <div class="pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; flex-wrap:wrap; gap:15px; border-top:1px solid var(--border-color); padding-top:15px;">
            <div style="font-size:13px; color:var(--text-muted);">
                Showing <strong><?= $p_offset + 1 ?></strong> to <strong><?= min($p_offset + $p_limit, $total_items) ?></strong> of <strong><?= $total_items ?></strong> requests
            </div>
            <div class="pagination" style="display:flex; align-items:center; gap:6px;">
                <?php if ($p_page > 1): ?>
                    <a href="?p_page=1<?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="First Page"><i class="fa-solid fa-angles-left"></i></a>
                    <a href="?p_page=<?= $p_page - 1 ?><?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Previous Page"><i class="fa-solid fa-angle-left"></i> Prev</a>
                <?php else: ?>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-left"></i></span>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angle-left"></i> Prev</span>
                <?php endif; ?>

                <?php 
                $start_p = max(1, $p_page - 2);
                $end_p = min($total_pages, $p_page + 2);
                for($i = $start_p; $i <= $end_p; $i++): 
                ?>
                    <?php if ($i == $p_page): ?>
                        <span class="btn" style="padding:6px 12px; background:var(--primary); color:white; font-size:12px; font-weight:700; border-radius:6px;"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?p_page=<?= $i ?><?= $qs_str ?>" class="btn" style="padding:6px 12px; background:var(--bg-slate); color:var(--text-color); font-size:12px; border-radius:6px;"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($p_page < $total_pages): ?>
                    <a href="?p_page=<?= $p_page + 1 ?><?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Next Page">Next <i class="fa-solid fa-angle-right"></i></a>
                    <a href="?p_page=<?= $total_pages ?><?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="Last Page"><i class="fa-solid fa-angles-right"></i></a>
                <?php else: ?>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;">Next <i class="fa-solid fa-angle-right"></i></span>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-right"></i></span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

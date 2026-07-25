<?php
// views/admin/prints.php
if (!defined('BASE_URL')) exit;

$status_filter = $_GET['status_filter'] ?? 'All';
$search = trim($_GET['search'] ?? '');
$from_date = $_GET['from_date'] ?? date('Y-m-01');
$to_date = $_GET['to_date'] ?? date('Y-m-t');

$where_clauses = [];
if ($status_filter === 'Pending') {
    $where_clauses[] = "p.status = 'Pending'";
} elseif ($status_filter === 'Completed') {
    $where_clauses[] = "p.status = 'Completed'";
} elseif ($status_filter === 'Rejected') {
    $where_clauses[] = "p.status = 'Rejected'";
}

if ($search !== '') {
    $search_escaped = $db->real_escape_string($search);
    $where_clauses[] = "(m.name LIKE '%$search_escaped%' OR m.membership_id LIKE '%$search_escaped%' OR e.title LIKE '%$search_escaped%' OR p.pages LIKE '%$search_escaped%')";
}

if ($from_date !== '') {
    $where_clauses[] = "p.requested_at >= '" . $db->real_escape_string($from_date) . " 00:00:00'";
}
if ($to_date !== '') {
    $where_clauses[] = "p.requested_at <= '" . $db->real_escape_string($to_date) . " 23:59:59'";
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = ' WHERE ' . implode(' AND ', $where_clauses);
}

$p_limit = max(5, min(200, (int)($_GET['p_limit'] ?? 10)));
$p_page = max(1, (int)($_GET['prt_page'] ?? 1));

$cnt_query_str = "SELECT COUNT(*) c FROM print_requests p JOIN members m ON m.id = p.member_id JOIN ebooks e ON e.id = p.ebook_id $where_sql";
$total_items = (int)($db->query($cnt_query_str)->fetch_assoc()['c'] ?? 0);
$total_pages = ceil($total_items / $p_limit);
$p_offset = ($p_page - 1) * $p_limit;

$query_str = "SELECT p.*, m.name, m.membership_id, e.title FROM print_requests p JOIN members m ON m.id = p.member_id JOIN ebooks e ON e.id = p.ebook_id $where_sql ORDER BY p.requested_at DESC LIMIT $p_limit OFFSET $p_offset";
?>

<div class="card" style="margin-bottom: 25px;">
    <h3><i class="fa-solid fa-filter"></i> Filter Print Requests</h3>
    <form method="get" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
        <input type="hidden" name="action" value="admin">
        <input type="hidden" name="tab" value="prints">
        
        <div>
            <label for="prt_search">Search Member ID / Name / E-Book / Pages</label>
            <input id="prt_search" name="search" value="<?= e($search) ?>" placeholder="Enter member ID, name, book title, or page numbers...">
        </div>
        
        <div>
            <label for="prt_status">Job Status</label>
            <select id="prt_status" name="status_filter">
                <option value="All" <?= $status_filter === 'All' ? 'selected' : '' ?>>All Jobs</option>
                <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending Jobs Only</option>
                <option value="Completed" <?= $status_filter === 'Completed' ? 'selected' : '' ?>>Completed Jobs Only</option>
                <option value="Rejected" <?= $status_filter === 'Rejected' ? 'selected' : '' ?>>Rejected Jobs Only</option>
            </select>
        </div>

        <div>
            <label for="prt_from_date">From Date</label>
            <input type="date" id="prt_from_date" name="from_date" value="<?= e($from_date) ?>">
        </div>
        
        <div>
            <label for="prt_to_date">To Date</label>
            <input type="date" id="prt_to_date" name="to_date" value="<?= e($to_date) ?>">
        </div>
        
        <div>
            <label style="visibility:hidden; margin-bottom:6px; display:block;">Action</label>
            <div style="display:flex; gap:8px;">
                <button type="submit" style="flex:1; padding:12px;"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
                <a href="?action=admin&tab=prints" class="btn btn-secondary" style="display:flex; align-items:center; justify-content:center; padding:12px; background:var(--bg-slate); color:var(--text-color); border:1px solid var(--border-color); text-decoration:none;"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <h3><i class="fa-solid fa-print"></i> PDF Page Printing Queue</h3>
    <div class="table-responsive">
        <table id="printRequestsTable">
            <thead>
                <tr>
                    <th>Member ID</th>
                    <th>Member Name</th>
                    <th>E-Book Title</th>
                    <th>Pages Target</th>
                    <th>Request Timestamp</th>
                    <th>Job Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $x = $db->query($query_str);
                if (!$x || $x->num_rows === 0) {
                    echo '<tr><td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted);"><i class="fa-solid fa-circle-info" style="font-size:20px; margin-bottom:10px; display:block; color:var(--primary);"></i> No print requests match the selected filters.</td></tr>';
                } else {
                    while($r = $x->fetch_assoc()) {
                        $badgeClass = $r['status'] === 'Pending' ? 'badge-orange' : ($r['status'] === 'Completed' ? 'badge-green' : 'badge-red');
                        $actionBtn = '';
                        if ($r['status'] === 'Pending') {
                            $actionBtn = ' &nbsp; <form method="post" action="?action=complete_print" style="display:inline; margin:0;"><input type="hidden" name="id" value="' . $r['id'] . '">' . csrf_input() . '<button class="btn" type="submit" style="padding:6px 12px; background:var(--accent-green, #10b981); color:#fff; border:none; border-radius:6px; cursor:pointer;"><i class="fa-solid fa-check"></i> Complete</button></form>'
                                      . ' &nbsp; <form method="post" action="?action=reject_print" style="display:inline; margin:0;"><input type="hidden" name="id" value="' . $r['id'] . '">' . csrf_input() . '<button class="btn" type="submit" style="padding:6px 12px; background:var(--accent-red, #ef4444); color:#fff; border:none; border-radius:6px; cursor:pointer;" onclick="return confirm(\'Are you sure you want to reject this print request?\');"><i class="fa-solid fa-xmark"></i> Reject</button></form>';
                        }
                        echo '
                        <tr>
                            <td><code>' . e($r['membership_id']) . '</code></td>
                            <td><strong style="color:var(--navy-dark);">' . e($r['name']) . '</strong></td>
                            <td>' . e($r['title']) . '</td>
                            <td><span style="font-weight:600;">' . e($r['pages']) . '</span></td>
                            <td>' . date('d-m-Y h:i A', strtotime($r['requested_at'])) . '</td>
                            <td>
                                <span class="badge ' . $badgeClass . '">' . e($r['status']) . '</span>
                                ' . $actionBtn . '
                            </td>
                        </tr>';
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Premium Pagination Component -->
    <?php if ($total_items > 0): ?>
        <?php
        $qs = $_GET;
        unset($qs['prt_page']);
        $qs_str = http_build_query($qs);
        $qs_str = $qs_str ? '&' . $qs_str : '';

        // Generate smart page list with ellipses
        $pages_to_show = get_smart_pagination_items($p_page, $total_pages);
        ?>
        <div class="pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; flex-wrap:wrap; gap:15px; border-top:1px solid var(--border-color); padding-top:15px;">
            <div style="display:flex; align-items:center; gap:15px; flex-wrap:wrap;">
                <div style="font-size:13px; color:var(--text-muted);">
                    Showing <strong><?= $p_offset + 1 ?></strong> to <strong><?= min($p_offset + $p_limit, $total_items) ?></strong> of <strong><?= $total_items ?></strong> print jobs
                </div>
                <div style="display:inline-flex; align-items:center; gap:6px; font-size:13px; color:var(--text-muted);">
                    <span>Per page:</span>
                    <select onchange="window.location.href=this.value;" style="padding:4px 8px; font-size:12px; border-radius:6px; border:1px solid var(--border-color); background:var(--card-bg, #fff); color:var(--text-color); cursor:pointer; font-weight:600;">
                        <?php foreach ([10, 25, 50, 100, 200] as $lim): ?>
                            <?php
                            $lim_qs = $_GET;
                            $lim_qs['p_limit'] = $lim;
                            $lim_qs['prt_page'] = 1;
                            $lim_url = '?' . http_build_query($lim_qs);
                            ?>
                            <option value="<?= e($lim_url) ?>" <?= $p_limit == $lim ? 'selected' : '' ?>><?= $lim ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="pagination" style="display:flex; align-items:center; gap:6px;">
                <?php if ($p_page > 1): ?>
                    <a href="?prt_page=1<?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="First Page"><i class="fa-solid fa-angles-left"></i></a>
                    <a href="?prt_page=<?= $p_page - 1 ?><?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Previous Page"><i class="fa-solid fa-angle-left"></i> Prev</a>
                <?php else: ?>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-left"></i></span>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angle-left"></i> Prev</span>
                <?php endif; ?>

                <?php foreach ($pages_to_show as $p_item): ?>
                    <?php if ($p_item === '...'): ?>
                        <span style="padding:6px 8px; color:var(--text-muted); font-size:12px; font-weight:700;">...</span>
                    <?php elseif ($p_item == $p_page): ?>
                        <span class="btn" style="padding:6px 12px; background:var(--primary); color:white; font-size:12px; font-weight:700; border-radius:6px;"><?= $p_item ?></span>
                    <?php else: ?>
                        <a href="?prt_page=<?= $p_item ?><?= $qs_str ?>" class="btn" style="padding:6px 12px; background:var(--bg-slate); color:var(--text-color); font-size:12px; border-radius:6px; text-decoration:none;"><?= $p_item ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if ($p_page < $total_pages): ?>
                    <a href="?prt_page=<?= $p_page + 1 ?><?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Next Page">Next <i class="fa-solid fa-angle-right"></i></a>
                    <a href="?prt_page=<?= $total_pages ?><?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="Last Page"><i class="fa-solid fa-angles-right"></i></a>
                <?php else: ?>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;">Next <i class="fa-solid fa-angle-right"></i></span>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-right"></i></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

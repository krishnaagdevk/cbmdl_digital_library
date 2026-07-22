<?php
// views/admin/prints.php
if (!defined('BASE_URL')) exit;

$status_filter = $_GET['status_filter'] ?? 'All';
$search = trim($_GET['search'] ?? '');

$where_clauses = [];
if ($status_filter === 'Pending') {
    $where_clauses[] = "p.status = 'Pending'";
} elseif ($status_filter === 'Completed') {
    $where_clauses[] = "p.status = 'Completed'";
}

if ($search !== '') {
    $search_escaped = $db->real_escape_string($search);
    $where_clauses[] = "(m.name LIKE '%$search_escaped%' OR m.membership_id LIKE '%$search_escaped%' OR e.title LIKE '%$search_escaped%' OR p.pages LIKE '%$search_escaped%')";
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = ' WHERE ' . implode(' AND ', $where_clauses);
}

$p_limit = 10;
$p_page = max(1, (int)($_GET['prt_page'] ?? 1));

$cnt_query_str = "SELECT COUNT(*) c FROM print_requests p JOIN members m ON m.id = p.member_id JOIN ebooks e ON e.id = p.ebook_id $where_sql";
$total_items = (int)($db->query($cnt_query_str)->fetch_assoc()['c'] ?? 0);
$total_pages = ceil($total_items / $p_limit);
$p_offset = ($p_page - 1) * $p_limit;

$query_str = "SELECT p.*, m.name, m.membership_id, e.title FROM print_requests p JOIN members m ON m.id = p.member_id JOIN ebooks e ON e.id = p.ebook_id $where_sql ORDER BY p.requested_at DESC LIMIT $p_limit OFFSET $p_offset";
?>

<div class="card" style="margin-bottom: 25px;">
    <h3><i class="fa-solid fa-filter"></i> Filter Print Requests</h3>
    <form method="get" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
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
            </select>
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
                        $badgeClass = $r['status'] === 'Pending' ? 'badge-orange' : 'badge-green';
                        $actionBtn = $r['status'] === 'Pending' 
                            ? ' &nbsp; <form method="post" action="?action=complete_print" style="display:inline; margin:0;"><input type="hidden" name="id" value="' . $r['id'] . '">' . csrf_input() . '<button class="btn" type="submit" style="padding:6px 12px;"><i class="fa-solid fa-check"></i> Complete Job</button></form>' 
                            : '';
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
    <?php if ($total_pages > 1): ?>
        <?php
        $qs = $_GET;
        unset($qs['prt_page']);
        $qs_str = http_build_query($qs);
        $qs_str = $qs_str ? '&' . $qs_str : '';
        ?>
        <div class="pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; flex-wrap:wrap; gap:15px; border-top:1px solid var(--border-color); padding-top:15px;">
            <div style="font-size:13px; color:var(--text-muted);">
                Showing <strong><?= $p_offset + 1 ?></strong> to <strong><?= min($p_offset + $p_limit, $total_items) ?></strong> of <strong><?= $total_items ?></strong> print jobs
            </div>
            <div class="pagination" style="display:flex; align-items:center; gap:6px;">
                <?php if ($p_page > 1): ?>
                    <a href="?prt_page=1<?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="First Page"><i class="fa-solid fa-angles-left"></i></a>
                    <a href="?prt_page=<?= $p_page - 1 ?><?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Previous Page"><i class="fa-solid fa-angle-left"></i> Prev</a>
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
                        <a href="?prt_page=<?= $i ?><?= $qs_str ?>" class="btn" style="padding:6px 12px; background:var(--bg-slate); color:var(--text-color); font-size:12px; border-radius:6px;"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($p_page < $total_pages): ?>
                    <a href="?prt_page=<?= $p_page + 1 ?><?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Next Page">Next <i class="fa-solid fa-angle-right"></i></a>
                    <a href="?prt_page=<?= $total_pages ?><?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="Last Page"><i class="fa-solid fa-angles-right"></i></a>
                <?php else: ?>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;">Next <i class="fa-solid fa-angle-right"></i></span>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-right"></i></span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

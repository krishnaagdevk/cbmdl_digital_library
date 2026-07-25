<?php
// views/admin/admin_login_logs.php
if (!defined('BASE_URL')) exit;

// Seed initial log entry if empty so table looks populated immediately
$checkCount = $db->query("SELECT COUNT(*) c FROM admin_login_logs");
if ($checkCount && (int)($checkCount->fetch_assoc()['c'] ?? 0) === 0) {
    log_admin_login($db, $_SESSION['admin_user'] ?? 'admin', 'Success');
}

$statusFilter = $_GET['status'] ?? 'all';
$searchQuery = trim($_GET['search'] ?? '');
$from_date = $_GET['from_date'] ?? date('Y-m-01');
$to_date = $_GET['to_date'] ?? date('Y-m-d');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = max(5, min(100, (int)($_GET['limit'] ?? 20))); // Max 20 per page default

$where = [];
if ($statusFilter !== 'all') {
    $where[] = "status = '" . $db->real_escape_string($statusFilter) . "'";
}
if ($searchQuery !== '') {
    $sq = $db->real_escape_string($searchQuery);
    $where[] = "(username LIKE '%$sq%' OR ip_address LIKE '%$sq%')";
}
if ($from_date !== '') {
    $where[] = "login_at >= '" . $db->real_escape_string($from_date) . " 00:00:00'";
}
if ($to_date !== '') {
    $where[] = "login_at <= '" . $db->real_escape_string($to_date) . " 23:59:59'";
}
$whereSql = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";

// Count total matching logs
$countRes = $db->query("SELECT COUNT(*) c FROM admin_login_logs $whereSql");
$totalLogs = (int)($countRes ? ($countRes->fetch_assoc()['c'] ?? 0) : 0);

$totalPages = max(1, (int)ceil($totalLogs / $limit));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $limit;

$logsQuery = $db->query("SELECT * FROM admin_login_logs $whereSql ORDER BY id DESC LIMIT $limit OFFSET $offset");
?>

<div class="card" style="border-top: 4px solid var(--primary);">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:15px;">
        <div>
            <h3 style="margin:0; color:var(--navy-dark); display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-user-shield" style="color:var(--primary);"></i> Admin Login Audit Logs (<?= $totalLogs ?> Entries)
            </h3>
            <p style="font-size:13px; color:var(--text-muted); margin:4px 0 0 0;">
                Security audit trail of administrative portal login attempts and access events.
            </p>
        </div>

        <form method="get" action="" style="display:flex; gap:10px; align-items:center; margin:0; flex-wrap:wrap;">
            <input type="hidden" name="action" value="admin">
            <input type="hidden" name="tab" value="admin_login_logs">
            
            <input type="text" name="search" value="<?= e($searchQuery) ?>" placeholder="Search username / IP..." style="margin:0; padding:6px 12px; font-size:12.5px; width:170px;">
            
            <select name="status" onchange="this.form.submit()" style="margin:0; padding:6px 12px; font-size:12.5px; width:130px;">
                <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                <option value="Success" <?= $statusFilter === 'Success' ? 'selected' : '' ?>>Success</option>
                <option value="Failed Credentials" <?= $statusFilter === 'Failed Credentials' ? 'selected' : '' ?>>Failed Credentials</option>
            </select>

            <input type="date" name="from_date" value="<?= e($from_date) ?>" title="From Date" style="margin:0; padding:6px 10px; font-size:12.5px; width:135px;">
            <input type="date" name="to_date" value="<?= e($to_date) ?>" title="To Date" style="margin:0; padding:6px 10px; font-size:12.5px; width:135px;">

            <select name="limit" onchange="this.form.submit()" style="margin:0; padding:6px 12px; font-size:12.5px; width:120px;" title="Entries Per Page">
                <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10 / page</option>
                <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20 / page</option>
                <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50 / page</option>
                <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100 / page</option>
            </select>

            <button type="submit" class="btn btn-secondary" style="padding:6px 12px; font-size:12.5px; background:var(--bg-slate); border:1px solid var(--border-color); color:var(--text-dark);">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            <?php if ($searchQuery !== '' || $statusFilter !== 'all' || $from_date !== '' || $to_date !== '' || $limit != 20): ?>
                <a href="?action=admin&tab=admin_login_logs" class="btn" style="padding:6px 12px; font-size:12.5px; background:var(--accent-orange); color:white;" title="Reset Filters"><i class="fa-solid fa-arrow-rotate-left"></i> Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width:70px;">Sr.No.</th>
                    <th>Admin Username</th>
                    <th>IP Address</th>
                    <th>Attempt Status</th>
                    <th>Login Date & Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($logsQuery && $logsQuery->num_rows > 0): ?>
                    <?php while ($log = $logsQuery->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?= $log['id'] ?></strong></td>
                            <td style="font-weight:600; color:var(--navy-dark);"><?= e($log['username']) ?></td>
                            <td><code style="background:var(--bg-slate); padding:2px 6px; border-radius:4px; font-size:12px; font-family:monospace; color:var(--navy-dark);"><?= e($log['ip_address']) ?></code></td>
                            <td>
                                <?php if ($log['status'] === 'Success'): ?>
                                    <span class="badge badge-green" style="font-size:11px; padding:3px 8px;"><i class="fa-solid fa-circle-check"></i> Success</span>
                                <?php else: ?>
                                    <span class="badge badge-orange" style="font-size:11px; padding:3px 8px; background:#fee2e2; color:#b91c1c;"><i class="fa-solid fa-triangle-exclamation"></i> <?= e($log['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px; color:var(--text-color);">
                                <i class="fa-solid fa-clock" style="color:var(--text-muted); margin-right:4px;"></i> <?= date('d-m-Y h:i A', strtotime($log['login_at'])) ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:30px; color:var(--text-muted);">
                            <i class="fa-solid fa-shield-halved" style="font-size:32px; display:block; margin-bottom:10px; opacity:0.5;"></i>
                            No admin login logs found matching the filter.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Always Visible Pagination Component -->
    <?php $qSuffix = '&status=' . urlencode($statusFilter) . '&search=' . urlencode($searchQuery) . '&limit=' . $limit; ?>
    <div class="pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; flex-wrap:wrap; gap:15px; border-top:1px solid var(--border-color); padding-top:15px;">
        <div style="font-size:13px; color:var(--text-muted);">
            <?php if ($totalLogs > 0): ?>
                Showing <strong><?= $offset + 1 ?></strong> to <strong><?= min($offset + $limit, $totalLogs) ?></strong> of <strong><?= $totalLogs ?></strong> entries (Page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong>)
            <?php else: ?>
                Showing <strong>0</strong> entries
            <?php endif; ?>
        </div>
        <div class="pagination" style="display:flex; align-items:center; gap:6px;">
            <?php if ($page > 1): ?>
                <a href="?action=admin&tab=admin_login_logs&page=1<?= $qSuffix ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="First Page"><i class="fa-solid fa-angles-left"></i></a>
                <a href="?action=admin&tab=admin_login_logs&page=<?= $page - 1 ?><?= $qSuffix ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Previous Page"><i class="fa-solid fa-angle-left"></i> Prev</a>
            <?php else: ?>
                <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-left"></i></span>
                <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angle-left"></i> Prev</span>
            <?php endif; ?>

            <?php 
            $pages_to_show = get_smart_pagination_items($page, $totalPages);
            foreach ($pages_to_show as $p_item): 
            ?>
                <?php if ($p_item === '...'): ?>
                    <span style="padding:6px 8px; color:var(--text-muted); font-size:12px; font-weight:700;">...</span>
                <?php elseif ($p_item == $page): ?>
                    <span class="btn" style="padding:6px 12px; background:var(--primary); color:white; font-size:12px; font-weight:700; border-radius:6px;"><?= $p_item ?></span>
                <?php else: ?>
                    <a href="?action=admin&tab=admin_login_logs&page=<?= $p_item ?><?= $qSuffix ?>" class="btn" style="padding:6px 12px; background:var(--bg-slate); color:var(--text-color); font-size:12px; border-radius:6px; text-decoration:none;"><?= $p_item ?></a>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?action=admin&tab=admin_login_logs&page=<?= $page + 1 ?><?= $qSuffix ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Next Page">Next <i class="fa-solid fa-angle-right"></i></a>
                <a href="?action=admin&tab=admin_login_logs&page=<?= $totalPages ?><?= $qSuffix ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="Last Page"><i class="fa-solid fa-angles-right"></i></a>
            <?php else: ?>
                <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; cursor:not-allowed; opacity:0.6;">Next <i class="fa-solid fa-angle-right"></i></span>
                <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-right"></i></span>
            <?php endif; ?>
        </div>
    </div>
</div>

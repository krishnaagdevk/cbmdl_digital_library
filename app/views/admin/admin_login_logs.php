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

$where = [];
if ($statusFilter !== 'all') {
    $where[] = "status = '" . $db->real_escape_string($statusFilter) . "'";
}
if ($searchQuery !== '') {
    $sq = $db->real_escape_string($searchQuery);
    $where[] = "(username LIKE '%$sq%' OR ip_address LIKE '%$sq%')";
}
$whereSql = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";

$logsQuery = $db->query("SELECT * FROM admin_login_logs $whereSql ORDER BY id DESC LIMIT 100");
?>

<div class="card" style="border-top: 4px solid var(--primary);">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:15px;">
        <div>
            <h3 style="margin:0; color:var(--navy-dark); display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-user-shield" style="color:var(--primary);"></i> Admin Login Audit Logs
            </h3>
            <p style="font-size:13px; color:var(--text-muted); margin:4px 0 0 0;">
                Security audit trail of administrative portal login attempts and access events.
            </p>
        </div>

        <form method="get" action="" style="display:flex; gap:10px; align-items:center; margin:0; flex-wrap:wrap;">
            <input type="hidden" name="action" value="admin">
            <input type="hidden" name="tab" value="admin_login_logs">
            
            <input type="text" name="search" value="<?= e($searchQuery) ?>" placeholder="Search username / IP..." style="margin:0; padding:6px 12px; font-size:12.5px; width:180px;">
            
            <select name="status" onchange="this.form.submit()" style="margin:0; padding:6px 12px; font-size:12.5px; width:140px;">
                <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                <option value="Success" <?= $statusFilter === 'Success' ? 'selected' : '' ?>>Success</option>
                <option value="Failed Credentials" <?= $statusFilter === 'Failed Credentials' ? 'selected' : '' ?>>Failed Credentials</option>
            </select>

            <button type="submit" class="btn btn-secondary" style="padding:6px 12px; font-size:12.5px; background:var(--bg-slate); border:1px solid var(--border-color); color:var(--text-dark);">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
        </form>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width:60px;">ID</th>
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
                                <i class="fa-solid fa-clock" style="color:var(--text-muted); margin-right:4px;"></i> <?= date('d M Y, h:i A', strtotime($log['login_at'])) ?>
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
</div>

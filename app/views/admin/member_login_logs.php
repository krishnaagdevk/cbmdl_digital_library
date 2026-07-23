<?php
// views/admin/member_login_logs.php
if (!defined('BASE_URL')) exit;

// Seed sample log entry if table is empty
$checkCount = $db->query("SELECT COUNT(*) c FROM member_login_logs");
if ($checkCount && (int)($checkCount->fetch_assoc()['c'] ?? 0) === 0) {
    $sampleMem = $db->query("SELECT * FROM members ORDER BY id DESC LIMIT 1")->fetch_assoc();
    if ($sampleMem) {
        log_member_login($db, $sampleMem['mobile'], $sampleMem['id'], $sampleMem['name'], $sampleMem['shift'] ?? 'Both', 'Success');
    }
}

$statusFilter = $_GET['status'] ?? 'all';
$searchQuery = trim($_GET['search'] ?? '');

$where = [];
if ($statusFilter !== 'all') {
    $where[] = "status = '" . $db->real_escape_string($statusFilter) . "'";
}
if ($searchQuery !== '') {
    $sq = $db->real_escape_string($searchQuery);
    $where[] = "(member_name LIKE '%$sq%' OR mobile LIKE '%$sq%' OR ip_address LIKE '%$sq%')";
}
$whereSql = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";

$logsQuery = $db->query("SELECT * FROM member_login_logs $whereSql ORDER BY id DESC LIMIT 100");
?>

<div class="card" style="border-top: 4px solid var(--primary);">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:15px;">
        <div>
            <h3 style="margin:0; color:var(--navy-dark); display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-users-rectangle" style="color:var(--primary);"></i> Member Login Audit Logs
            </h3>
            <p style="font-size:13px; color:var(--text-muted); margin:4px 0 0 0;">
                Track library member authentication activity, shift access compliance, and portal log events.
            </p>
        </div>

        <form method="get" action="" style="display:flex; gap:10px; align-items:center; margin:0; flex-wrap:wrap;">
            <input type="hidden" name="action" value="admin">
            <input type="hidden" name="tab" value="member_login_logs">
            
            <input type="text" name="search" value="<?= e($searchQuery) ?>" placeholder="Search name / mobile..." style="margin:0; padding:6px 12px; font-size:12.5px; width:180px;">
            
            <select name="status" onchange="this.form.submit()" style="margin:0; padding:6px 12px; font-size:12.5px; width:160px;">
                <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                <option value="Success" <?= $statusFilter === 'Success' ? 'selected' : '' ?>>Success</option>
                <option value="Shift Restricted" <?= $statusFilter === 'Shift Restricted' ? 'selected' : '' ?>>Shift Restricted</option>
                <option value="Failed Credentials" <?= $statusFilter === 'Failed Credentials' ? 'selected' : '' ?>>Failed Credentials</option>
                <option value="Pending Approval" <?= $statusFilter === 'Pending Approval' ? 'selected' : '' ?>>Pending Approval</option>
                <option value="Inactive Account" <?= $statusFilter === 'Inactive Account' ? 'selected' : '' ?>>Inactive Account</option>
                <option value="Membership Expired" <?= $statusFilter === 'Membership Expired' ? 'selected' : '' ?>>Membership Expired</option>
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
                    <th>Member Name & Mobile</th>
                    <th>Assigned Shift</th>
                    <th>IP Address</th>
                    <th>Status</th>
                    <th>Login Date & Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($logsQuery && $logsQuery->num_rows > 0): ?>
                    <?php while ($log = $logsQuery->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?= $log['id'] ?></strong></td>
                            <td>
                                <strong style="color:var(--navy-dark); display:block; font-size:13px;"><?= e($log['member_name'] ?: 'Unknown User') ?></strong>
                                <span style="font-size:11.5px; color:var(--text-muted);"><i class="fa-solid fa-phone" style="font-size:10px;"></i> <?= e($log['mobile']) ?></span>
                            </td>
                            <td>
                                <span class="badge badge-blue" style="font-size:11px; padding:3px 8px;">
                                    <i class="fa-solid fa-sun"></i> <?= e($log['shift'] ?: 'Both') ?>
                                </span>
                            </td>
                            <td><code style="background:var(--bg-slate); padding:2px 6px; border-radius:4px; font-size:12px; font-family:monospace; color:var(--navy-dark);"><?= e($log['ip_address']) ?></code></td>
                            <td>
                                <?php 
                                $st = $log['status'];
                                if ($st === 'Success') {
                                    echo '<span class="badge badge-green" style="font-size:11px; padding:3px 8px;"><i class="fa-solid fa-circle-check"></i> Success</span>';
                                } elseif ($st === 'Shift Restricted') {
                                    echo '<span class="badge" style="background:#fef3c7; color:#b45309; font-size:11px; padding:3px 8px;"><i class="fa-solid fa-lock"></i> Shift Restricted</span>';
                                } else {
                                    echo '<span class="badge badge-orange" style="font-size:11px; padding:3px 8px; background:#fee2e2; color:#b91c1c;"><i class="fa-solid fa-triangle-exclamation"></i> ' . e($st) . '</span>';
                                }
                                ?>
                            </td>
                            <td style="font-size:12px; color:var(--text-color);">
                                <i class="fa-solid fa-clock" style="color:var(--text-muted); margin-right:4px;"></i> <?= date('d M Y, h:i A', strtotime($log['login_at'])) ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted);">
                            <i class="fa-solid fa-users-slash" style="font-size:32px; display:block; margin-bottom:10px; opacity:0.5;"></i>
                            No member login logs found matching the filter.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

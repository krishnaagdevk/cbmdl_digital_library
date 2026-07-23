<?php
// views/admin/membership_history.php
if (!defined('BASE_URL')) exit;

$search = trim($_GET['q'] ?? '');
$action_filter = trim($_GET['type'] ?? '');

// Build query
$where = [];
$params = [];
$types = "";

if ($search !== '') {
    $where[] = "(m.name LIKE ? OR h.membership_id LIKE ? OR h.payment_id LIKE ? OR h.plan_name LIKE ? OR m.mobile LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sssss";
}

if ($action_filter !== '') {
    $where[] = "h.action_type = ?";
    $params[] = $action_filter;
    $types .= "s";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT h.*, m.name as member_name, m.mobile, m.email 
        FROM membership_history h 
        LEFT JOIN members m ON h.member_id = m.id 
        {$whereClause} 
        ORDER BY h.id DESC";

$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$historyRes = $stmt->get_result();

// Summary metrics
$totalCount = (int)$db->query("SELECT COUNT(*) c FROM membership_history")->fetch_assoc()['c'];
$totalRevenue = (float)$db->query("SELECT SUM(membership_fee) s FROM membership_history")->fetch_assoc()['s'];
$totalRenewals = (int)$db->query("SELECT COUNT(*) c FROM membership_history WHERE action_type = 'Renewal'")->fetch_assoc()['c'];
$totalJoins = (int)$db->query("SELECT COUNT(*) c FROM membership_history WHERE action_type = 'Initial Joining'")->fetch_assoc()['c'];
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
        <h3 style="margin:0;"><i class="fa-solid fa-clock-rotate-left"></i> Comprehensive Membership History Log</h3>
        <button class="btn btn-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i> Print Report</button>
    </div>

    <!-- Quick Metric Cards -->
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; margin-bottom:25px;">
        <div style="background:var(--bg-slate); border:1px solid var(--border-color); border-radius:12px; padding:15px; display:flex; align-items:center; gap:12px;">
            <div style="width:42px; height:42px; border-radius:10px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:18px;">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div>
                <span style="font-size:12px; color:var(--text-muted); font-weight:600; text-transform:uppercase;">Total Logs</span>
                <strong style="display:block; font-size:18px; color:var(--navy-dark);"><?= number_format($totalCount) ?></strong>
            </div>
        </div>

        <div style="background:var(--bg-slate); border:1px solid var(--border-color); border-radius:12px; padding:15px; display:flex; align-items:center; gap:12px;">
            <div style="width:42px; height:42px; border-radius:10px; background:#f0fdf4; color:#16a34a; display:flex; align-items:center; justify-content:center; font-size:18px;">
                <i class="fa-solid fa-indian-rupee-sign"></i>
            </div>
            <div>
                <span style="font-size:12px; color:var(--text-muted); font-weight:600; text-transform:uppercase;">Fees Collected</span>
                <strong style="display:block; font-size:18px; color:#16a34a;">₹<?= number_format($totalRevenue, 2) ?></strong>
            </div>
        </div>

        <div style="background:var(--bg-slate); border:1px solid var(--border-color); border-radius:12px; padding:15px; display:flex; align-items:center; gap:12px;">
            <div style="width:42px; height:42px; border-radius:10px; background:#faf5ff; color:#9333ea; display:flex; align-items:center; justify-content:center; font-size:18px;">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div>
                <span style="font-size:12px; color:var(--text-muted); font-weight:600; text-transform:uppercase;">New Registrations</span>
                <strong style="display:block; font-size:18px; color:var(--navy-dark);"><?= number_format($totalJoins) ?></strong>
            </div>
        </div>

        <div style="background:var(--bg-slate); border:1px solid var(--border-color); border-radius:12px; padding:15px; display:flex; align-items:center; gap:12px;">
            <div style="width:42px; height:42px; border-radius:10px; background:#fff7ed; color:#ea580c; display:flex; align-items:center; justify-content:center; font-size:18px;">
                <i class="fa-solid fa-rotate"></i>
            </div>
            <div>
                <span style="font-size:12px; color:var(--text-muted); font-weight:600; text-transform:uppercase;">Renewals Recorded</span>
                <strong style="display:block; font-size:18px; color:var(--navy-dark);"><?= number_format($totalRenewals) ?></strong>
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <form method="get" action="" style="display:flex; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
        <input type="hidden" name="action" value="admin">
        <input type="hidden" name="tab" value="membership_history">
        
        <div style="flex:1; min-width:220px; position:relative;">
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search member name, ID, Payment ID..." style="width:100%; padding-left:35px;">
            <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted);"></i>
        </div>

        <div style="width:200px;">
            <select name="type" onchange="this.form.submit()" style="width:100%;">
                <option value="">All Action Types</option>
                <option value="Initial Joining" <?= $action_filter === 'Initial Joining' ? 'selected' : '' ?>>Initial Joining</option>
                <option value="Renewal" <?= $action_filter === 'Renewal' ? 'selected' : '' ?>>Renewal</option>
                <option value="Plan Switch" <?= $action_filter === 'Plan Switch' ? 'selected' : '' ?>>Plan Switch</option>
                <option value="Manual Adjustment" <?= $action_filter === 'Manual Adjustment' ? 'selected' : '' ?>>Manual Adjustment</option>
            </select>
        </div>

        <button type="submit" class="btn"><i class="fa-solid fa-filter"></i> Filter</button>
        <?php if ($search !== '' || $action_filter !== ''): ?>
            <a href="?action=admin&tab=membership_history" class="btn btn-secondary" style="display:inline-flex; align-items:center; justify-content:center;"><i class="fa-solid fa-xmark"></i> Clear</a>
        <?php endif; ?>
    </form>

    <!-- Table -->
    <div style="overflow-x:auto;">
        <table class="table" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:var(--bg-slate); text-align:left; font-size:12px; color:var(--text-muted);">
                    <th style="padding:12px;">#</th>
                    <th style="padding:12px;">Member Details</th>
                    <th style="padding:12px;">Membership Plan</th>
                    <th style="padding:12px;">Validity Period</th>
                    <th style="padding:12px;">Fee & Transaction</th>
                    <th style="padding:12px;">Action Type</th>
                    <th style="padding:12px;">Date Logged</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($historyRes->num_rows === 0): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding:30px; color:var(--text-muted);">
                            <i class="fa-solid fa-clock-rotate-left" style="font-size:32px; margin-bottom:10px; display:block; opacity:0.5;"></i>
                            No membership history logs found matching your criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $count = 1; while ($row = $historyRes->fetch_assoc()): ?>
                        <?php 
                        $isCurrent = (strtotime($row['start_date']) <= time() && strtotime($row['end_date']) >= time());
                        ?>
                        <tr style="border-bottom:1px solid var(--border-color); font-size:13px;">
                            <td style="padding:12px; color:var(--text-muted); font-weight:600;"><?= $count++ ?></td>
                            <td style="padding:12px;">
                                <a href="?action=admin&tab=view_members&view=<?= $row['member_id'] ?>" style="font-weight:700; color:var(--primary); text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                                    <i class="fa-solid fa-user"></i> <?= e($row['member_name'] ?? 'Member #' . $row['member_id']) ?>
                                </a>
                                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">
                                    <span class="badge badge-blue" style="font-size:10px; padding:2px 6px;"><?= e($row['membership_id']) ?></span>
                                    <?php if (!empty($row['mobile'])): ?>
                                        <span style="margin-left:5px;"><i class="fa-solid fa-phone"></i> <?= e($row['mobile']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="padding:12px;">
                                <strong style="color:var(--navy-dark);"><?= e($row['plan_name'] ?? $row['duration']) ?></strong>
                                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">
                                    <span><i class="fa-solid fa-hourglass-half"></i> <?= e($row['duration']) ?></span>
                                    <span style="margin-left:6px;"><i class="fa-solid fa-sun"></i> <?= e($row['shift']) ?> Shift</span>
                                </div>
                            </td>
                            <td style="padding:12px;">
                                <div style="font-size:12px; font-weight:600;">
                                    <?= date('d M Y', strtotime($row['start_date'])) ?> &rarr; <?= date('d M Y', strtotime($row['end_date'])) ?>
                                </div>
                                <div style="margin-top:3px;">
                                    <?php if ($isCurrent): ?>
                                        <span class="badge badge-green" style="font-size:10px; padding:2px 6px;"><i class="fa-solid fa-circle-check"></i> Active Period</span>
                                    <?php else: ?>
                                        <span class="badge badge-red" style="font-size:10px; padding:2px 6px;"><i class="fa-solid fa-clock"></i> Expired Period</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="padding:12px;">
                                <strong style="color:#16a34a; font-size:14px;">₹<?= number_format($row['membership_fee'], 2) ?></strong>
                                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;" title="Transaction ID">
                                    <i class="fa-solid fa-hashtag"></i> <?= e($row['payment_id'] ?? 'N/A') ?>
                                </div>
                            </td>
                            <td style="padding:12px;">
                                <?php if ($row['action_type'] === 'Initial Joining'): ?>
                                    <span class="badge badge-blue" style="font-size:11px; padding:4px 8px;"><i class="fa-solid fa-user-plus"></i> Initial Joining</span>
                                <?php elseif ($row['action_type'] === 'Renewal'): ?>
                                    <span class="badge badge-green" style="font-size:11px; padding:4px 8px;"><i class="fa-solid fa-rotate"></i> Renewal</span>
                                <?php elseif ($row['action_type'] === 'Plan Switch'): ?>
                                    <span class="badge badge-orange" style="font-size:11px; padding:4px 8px;"><i class="fa-solid fa-right-left"></i> Plan Switch</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary" style="font-size:11px; padding:4px 8px;"><i class="fa-solid fa-pen-to-square"></i> <?= e($row['action_type']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:12px; color:var(--text-muted); font-size:12px;">
                                <i class="fa-regular fa-calendar"></i> <?= date('d M Y, h:i A', strtotime($row['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

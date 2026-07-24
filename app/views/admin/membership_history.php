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

// Pagination setup
$p_limit = 10;
$p_page = max(1, (int)($_GET['p'] ?? 1));

// Count total matching items
$cntSql = "SELECT COUNT(*) c FROM membership_history h LEFT JOIN members m ON h.member_id = m.id {$whereClause}";
$cntStmt = $db->prepare($cntSql);
if (!empty($params)) {
    $cntStmt->bind_param($types, ...$params);
}
$cntStmt->execute();
$total_items = (int)($cntStmt->get_result()->fetch_assoc()['c'] ?? 0);
$cntStmt->close();

$total_pages = max(1, ceil($total_items / $p_limit));
$p_offset = ($p_page - 1) * $p_limit;

// Fetch paginated history logs
$sql = "SELECT h.*, m.name as member_name, m.mobile, m.email 
        FROM membership_history h 
        LEFT JOIN members m ON h.member_id = m.id 
        {$whereClause} 
        ORDER BY h.id DESC 
        LIMIT ? OFFSET ?";

$paramsWithLimit = $params;
$typesWithLimit = $types . "ii";
$paramsWithLimit[] = $p_limit;
$paramsWithLimit[] = $p_offset;

$stmt = $db->prepare($sql);
if (!empty($paramsWithLimit)) {
    $stmt->bind_param($typesWithLimit, ...$paramsWithLimit);
}
$stmt->execute();
$historyRes = $stmt->get_result();

// Summary metrics
$totalCount = (int)$db->query("SELECT COUNT(*) c FROM membership_history")->fetch_assoc()['c'];
$totalRevenue = (float)$db->query("SELECT SUM(membership_fee) s FROM membership_history")->fetch_assoc()['s'];
$totalRenewals = (int)$db->query("SELECT COUNT(*) c FROM membership_history WHERE action_type = 'Renewal'")->fetch_assoc()['c'];
$totalJoins = (int)$db->query("SELECT COUNT(*) c FROM membership_history WHERE action_type = 'Initial Joining'")->fetch_assoc()['c'];
?>

<div class="card" style="margin-bottom:25px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
        <h3 style="margin:0; font-family:inherit;"><i class="fa-solid fa-clock-rotate-left"></i> Comprehensive Membership History Log</h3>
    </div>

    <!-- Quick Metric Cards -->
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px;">
        <div style="background:var(--bg-slate); border:1px solid var(--border-color); border-radius:12px; padding:15px; display:flex; align-items:center; gap:12px;">
            <div style="width:42px; height:42px; border-radius:10px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:18px;">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div>
                <span style="font-size:12px; color:var(--text-muted); font-weight:600; text-transform:uppercase;">Total Logs</span>
                <strong style="display:block; font-size:18px; color:var(--navy-dark); font-family:inherit;"><?= number_format($totalCount) ?></strong>
            </div>
        </div>

        <div style="background:var(--bg-slate); border:1px solid var(--border-color); border-radius:12px; padding:15px; display:flex; align-items:center; gap:12px;">
            <div style="width:42px; height:42px; border-radius:10px; background:#f0fdf4; color:#16a34a; display:flex; align-items:center; justify-content:center; font-size:18px;">
                <i class="fa-solid fa-indian-rupee-sign"></i>
            </div>
            <div>
                <span style="font-size:12px; color:var(--text-muted); font-weight:600; text-transform:uppercase;">Fees Collected</span>
                <strong style="display:block; font-size:18px; color:#16a34a; font-family:inherit;">₹<?= number_format($totalRevenue, 2) ?></strong>
            </div>
        </div>

        <div style="background:var(--bg-slate); border:1px solid var(--border-color); border-radius:12px; padding:15px; display:flex; align-items:center; gap:12px;">
            <div style="width:42px; height:42px; border-radius:10px; background:#faf5ff; color:#9333ea; display:flex; align-items:center; justify-content:center; font-size:18px;">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div>
                <span style="font-size:12px; color:var(--text-muted); font-weight:600; text-transform:uppercase;">New Registrations</span>
                <strong style="display:block; font-size:18px; color:var(--navy-dark); font-family:inherit;"><?= number_format($totalJoins) ?></strong>
            </div>
        </div>

        <div style="background:var(--bg-slate); border:1px solid var(--border-color); border-radius:12px; padding:15px; display:flex; align-items:center; gap:12px;">
            <div style="width:42px; height:42px; border-radius:10px; background:#fff7ed; color:#ea580c; display:flex; align-items:center; justify-content:center; font-size:18px;">
                <i class="fa-solid fa-rotate"></i>
            </div>
            <div>
                <span style="font-size:12px; color:var(--text-muted); font-weight:600; text-transform:uppercase;">Renewals Recorded</span>
                <strong style="display:block; font-size:18px; color:var(--navy-dark); font-family:inherit;"><?= number_format($totalRenewals) ?></strong>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter Controls -->
<div class="card" style="margin-bottom:25px;">
    <h3 style="margin-top:0; margin-bottom:15px; font-family:inherit; font-size:16px;"><i class="fa-solid fa-filter"></i> Filter Membership History</h3>
    <form method="get" action="" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:15px; align-items:end;">
        <input type="hidden" name="action" value="admin">
        <input type="hidden" name="tab" value="membership_history">
        
        <div>
            <label for="hist_search" style="font-size:12px; font-weight:600; color:var(--text-muted); margin-bottom:6px; display:block; font-family:inherit;">Search Member / ID / Payment UTR</label>
            <div style="position:relative;">
                <input id="hist_search" type="text" name="q" value="<?= e($search) ?>" placeholder="Search member name, ID, Payment ID..." style="width:100%; padding-left:35px; height:42px; border-radius:8px; font-family:inherit; font-size:13px; border:1px solid var(--border-color); margin:0; box-sizing:border-box;">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted);"></i>
            </div>
        </div>

        <div>
            <label for="hist_type" style="font-size:12px; font-weight:600; color:var(--text-muted); margin-bottom:6px; display:block; font-family:inherit;">Action Type</label>
            <select id="hist_type" name="type" style="width:100%; height:42px; border-radius:8px; font-family:inherit; font-size:13px; border:1px solid var(--border-color); margin:0; padding:0 12px; box-sizing:border-box; background:var(--bg-card); color:var(--navy-dark);">
                <option value="">All Action Types</option>
                <option value="Initial Joining" <?= $action_filter === 'Initial Joining' ? 'selected' : '' ?>>Initial Joining</option>
                <option value="Renewal" <?= $action_filter === 'Renewal' ? 'selected' : '' ?>>Renewal</option>
            </select>
        </div>

        <div>
            <label style="visibility:hidden; margin-bottom:6px; display:block; font-size:12px; font-family:inherit;">Actions</label>
            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn" style="flex:1; height:42px; padding:0 20px; font-family:inherit; font-size:13px; font-weight:600; display:inline-flex; align-items:center; justify-content:center; gap:6px; border-radius:8px; cursor:pointer; margin:0; box-sizing:border-box;"><i class="fa-solid fa-filter"></i> Filter</button>
                <a href="?action=admin&tab=membership_history" class="btn btn-secondary" style="height:42px; padding:0 16px; font-family:inherit; font-size:13px; font-weight:600; display:inline-flex; align-items:center; justify-content:center; gap:6px; background:var(--bg-slate); color:var(--text-color); border:1px solid var(--border-color); text-decoration:none; border-radius:8px; margin:0; box-sizing:border-box;"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            </div>
        </div>
    </form>
</div>

<!-- History Log Table -->
<div class="card">
    <div style="overflow-x:auto;">
        <table class="table" style="width:100%; border-collapse:collapse; font-family:inherit;">
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
                        <td colspan="7" style="text-align:center; padding:30px; color:var(--text-muted); font-family:inherit;">
                            <i class="fa-solid fa-clock-rotate-left" style="font-size:32px; margin-bottom:10px; display:block; opacity:0.5;"></i>
                            No membership history logs found matching your criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $count = $p_offset + 1; while ($row = $historyRes->fetch_assoc()): ?>
                        <?php 
                        $todayStr = date('Y-m-d');
                        $startDate = $row['start_date'] ?? '';
                        $endDate = $row['end_date'] ?? '';

                        $isCurrent = (!empty($startDate) && !empty($endDate) && $startDate <= $todayStr && $endDate >= $todayStr);
                        $isUpcoming = (!empty($startDate) && $startDate > $todayStr);
                        ?>
                        <tr style="border-bottom:1px solid var(--border-color); font-size:13px; font-family:inherit;">
                            <td style="padding:12px; color:var(--text-muted); font-weight:600;"><?= $count++ ?></td>
                            <td style="padding:12px;">
                                <a href="?action=admin&tab=view_members&view=<?= $row['member_id'] ?>" style="font-weight:700; color:var(--primary); text-decoration:none; display:inline-flex; align-items:center; gap:5px; font-family:inherit;">
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
                                <strong style="color:var(--navy-dark); font-family:inherit;"><?= e(!empty($row['duration']) ? $row['duration'] : $row['plan_name']) ?></strong>
                                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">
                                    <span><i class="fa-solid fa-sun"></i> <?= e($row['shift']) ?> Shift</span>
                                </div>
                            </td>
                            <td style="padding:12px;">
                                <div style="font-size:12px; font-weight:600; font-family:inherit;">
                                    <?= date('d M Y', strtotime($row['start_date'])) ?> &rarr; <?= date('d M Y', strtotime($row['end_date'])) ?>
                                </div>
                                <div style="margin-top:3px;">
                                    <?php if ($isCurrent): ?>
                                        <span class="badge badge-green" style="font-size:10px; padding:2px 6px;"><i class="fa-solid fa-circle-check"></i> Active Period</span>
                                    <?php elseif ($isUpcoming): ?>
                                        <span class="badge badge-blue" style="font-size:10px; padding:2px 6px; background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd;"><i class="fa-solid fa-calendar-check"></i> Upcoming Period</span>
                                    <?php else: ?>
                                        <span class="badge badge-red" style="font-size:10px; padding:2px 6px;"><i class="fa-solid fa-clock"></i> Expired Period</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="padding:12px;">
                                <strong style="color:#16a34a; font-size:14px; font-family:inherit;">₹<?= number_format($row['membership_fee'], 2) ?></strong>
                                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;" title="Transaction ID">
                                    <i class="fa-solid fa-hashtag"></i> <?= e($row['payment_id'] ?? 'N/A') ?>
                                </div>
                            </td>
                            <td style="padding:12px;">
                                <?php if ($row['action_type'] === 'Initial Joining'): ?>
                                    <span class="badge badge-blue" style="font-size:11px; padding:4px 10px; font-weight:600;"><i class="fa-solid fa-user-plus"></i> Initial Joining</span>
                                <?php elseif ($row['action_type'] === 'Renewal'): ?>
                                    <span class="badge" style="font-size:11px; padding:4px 10px; font-weight:600; background:#dcfce7; color:#15803d; border:1px solid #bbf7d0;"><i class="fa-solid fa-rotate-right"></i> Renewal</span>
                                <?php elseif ($row['action_type'] === 'Plan Switch'): ?>
                                    <span class="badge badge-orange" style="font-size:11px; padding:4px 10px; font-weight:600;"><i class="fa-solid fa-right-left"></i> Plan Switch</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary" style="font-size:11px; padding:4px 10px; font-weight:600;"><i class="fa-solid fa-pen-to-square"></i> <?= e($row['action_type']) ?></span>
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

    <!-- Pagination Controls -->
    <?php if ($total_pages > 1): ?>
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-top:25px; padding-top:15px; border-top:1px solid var(--border-color); font-family:inherit;">
        <div style="font-size:13px; color:var(--text-muted);">
            Showing <strong><?= $total_items > 0 ? $p_offset + 1 : 0 ?></strong> to <strong><?= min($total_items, $p_offset + $p_limit) ?></strong> of <strong><?= number_format($total_items) ?></strong> history logs
        </div>
        <div style="display:flex; gap:6px; flex-wrap:wrap;">
            <?php if ($p_page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['p' => 1])) ?>" class="btn btn-secondary" style="padding:6px 12px; font-size:12px; font-family:inherit;"><i class="fa-solid fa-angles-left"></i> First</a>
                <a href="?<?= http_build_query(array_merge($_GET, ['p' => $p_page - 1])) ?>" class="btn btn-secondary" style="padding:6px 12px; font-size:12px; font-family:inherit;"><i class="fa-solid fa-chevron-left"></i> Prev</a>
            <?php endif; ?>

            <?php
            $start_p = max(1, $p_page - 2);
            $end_p = min($total_pages, $p_page + 2);
            for ($i = $start_p; $i <= $end_p; $i++):
            ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['p' => $i])) ?>" class="btn <?= $i === $p_page ? '' : 'btn-secondary' ?>" style="padding:6px 12px; font-size:12px; font-family:inherit; <?= $i === $p_page ? 'font-weight:700;' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($p_page < $total_pages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['p' => $p_page + 1])) ?>" class="btn btn-secondary" style="padding:6px 12px; font-size:12px; font-family:inherit;">Next <i class="fa-solid fa-chevron-right"></i></a>
                <a href="?<?= http_build_query(array_merge($_GET, ['p' => $total_pages])) ?>" class="btn btn-secondary" style="padding:6px 12px; font-size:12px; font-family:inherit;">Last <i class="fa-solid fa-angles-right"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

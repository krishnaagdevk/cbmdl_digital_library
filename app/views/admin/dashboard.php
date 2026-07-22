<?php
// app/views/admin/dashboard.php
if (!defined('BASE_URL')) exit;

// Fetch statistical numbers
$tot_cats = (int)$db->query('SELECT COUNT(*) c FROM categories')->fetch_assoc()['c'];
$tot_ebooks = (int)$db->query('SELECT COUNT(*) c FROM ebooks')->fetch_assoc()['c'];
$tot_physical = (int)$db->query('SELECT COUNT(*) c FROM physical_books')->fetch_assoc()['c'];
$tot_members = (int)$db->query('SELECT COUNT(*) c FROM members')->fetch_assoc()['c'];
$tot_requests = (int)$db->query("SELECT COUNT(*) c FROM reading_requests WHERE status='Pending'")->fetch_assoc()['c'];
$tot_prints = (int)$db->query("SELECT COUNT(*) c FROM print_requests WHERE status='Pending'")->fetch_assoc()['c'];
$tot_lent = (int)$db->query("SELECT COUNT(*) c FROM lendings WHERE returned_at IS NULL")->fetch_assoc()['c'];
$avail_physical = max(0, $tot_physical - $tot_lent);

// Calculate overdue lendings count & total fine accumulated
$overdue_query = $db->query("SELECT due_date, returned_at FROM lendings WHERE returned_at IS NULL");
$tot_overdue_count = 0;
$tot_overdue_fines = 0.00;
while ($l_row = $overdue_query->fetch_assoc()) {
    $f_data = calculate_fine($l_row['due_date'], $l_row['returned_at']);
    if ($f_data['days'] > 0) {
        $tot_overdue_count++;
        $tot_overdue_fines += $f_data['fine'];
    }
}

// Fetch members expiring within 7 days
$expiring_7_days_count = 0;
$exp_query = $db->query("SELECT end_date, is_active FROM members WHERE is_active = 1 AND end_date >= DATE(NOW()) AND end_date <= DATE_ADD(NOW(), INTERVAL 7 DAY)");
if ($exp_query) {
    $expiring_7_days_count = $exp_query->num_rows;
}

// Category distribution for progress meters
$cat_distribution = [];
$cat_query = $db->query("SELECT c.name, COUNT(e.id) AS ebook_count FROM categories c LEFT JOIN ebooks e ON e.category_id = c.id GROUP BY c.id ORDER BY ebook_count DESC");
while ($c_row = $cat_query->fetch_assoc()) {
    $cat_distribution[] = $c_row;
}

// Recent lending transactions
$recent_lendings = [];
$rl_query = $db->query("SELECT l.*, p.title, m.name FROM lendings l JOIN physical_books p ON p.id = l.physical_book_id JOIN members m ON m.id = l.member_id ORDER BY l.lent_at DESC LIMIT 4");
while ($r_row = $rl_query->fetch_assoc()) {
    $recent_lendings[] = $r_row;
}
?>
<h3 style="margin-top:0; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
    <span><i class="fa-solid fa-gauge-high"></i> Dashboard Live Analytics & Statistics</span>
</h3>

<style>
@media (min-width: 992px) {
    .stats-grid-3cols {
        grid-template-columns: repeat(3, 1fr) !important;
    }
}
@media (max-width: 991px) and (min-width: 600px) {
    .stats-grid-3cols {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}
@media (max-width: 599px) {
    .stats-grid-3cols {
        grid-template-columns: 1fr !important;
    }
}
</style>

<!-- KPI Highlight Cards Grid -->
<div class="stats-grid stats-grid-3cols" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px;">
    <div class="stat-card">
        <div class="stat-info" style="flex:1;">
            <h4>Physical Books</h4>
            <div style="display:flex; align-items:baseline; gap:12px; margin-top:4px;">
                <span style="font-size:22px; font-weight:700; color:var(--navy-dark);"><?= $avail_physical ?> <span style="font-size:12px; color:var(--accent-green); font-weight:600;">Avail</span></span>
                <span style="font-size:16px; font-weight:600; color:var(--accent-red);"><?= $tot_lent ?> <span style="font-size:11px; color:var(--text-muted); font-weight:500;">Lent</span></span>
            </div>
            <span style="font-size:11px; color:var(--text-muted); display:block; margin-top:2px;">Total: <?= $tot_physical ?> books</span>
        </div>
        <div class="stat-icon stat-blue"><i class="fa-solid fa-book"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h4>E-Books Available</h4>
            <p><?= $tot_ebooks ?></p>
        </div>
        <div class="stat-icon stat-green"><i class="fa-solid fa-file-pdf"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h4>Active Members</h4>
            <p><?= $tot_members ?></p>
        </div>
        <div class="stat-icon stat-orange"><i class="fa-solid fa-users"></i></div>
    </div>
    <!-- <div class="stat-card">
        <div class="stat-info">
            <h4>Overdue Fines</h4>
            <p style="font-size:20px; color:var(--accent-red);">₹<?= number_style_format($tot_overdue_fines) ?></p>
            <span style="font-size:11px; color:var(--text-muted);"><?= $tot_overdue_count ?> book(s) overdue</span>
        </div>
        <div class="stat-icon stat-red"><i class="fa-solid fa-clock"></i></div>
    </div> -->
    <div class="stat-card">
        <div class="stat-info">
            <h4>Pending Inbox</h4>
            <p style="font-size:20px; color:var(--primary);"><?= $tot_requests + $tot_prints ?></p>
            <span style="font-size:11px; color:var(--text-muted);"><?= $tot_requests ?> req / <?= $tot_prints ?> print</span>
        </div>
        <div class="stat-icon stat-blue"><i class="fa-solid fa-inbox"></i></div>
    </div>
    <!-- Feature 4: Expiring Members KPI Card -->
    <div class="stat-card" style="border-left: 4px solid var(--accent-orange); cursor: pointer;" onclick="switchDashboardTab('inbox');">
        <div class="stat-info">
            <h4>Expiring Membership<br> (7 days)</h4>
            <p style="font-size:20px; color:var(--accent-orange);"><?= $expiring_7_days_count ?></p>
            <span style="font-size:11px; color:var(--text-muted);">Membership renewals</span>
        </div>
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--accent-orange); border-radius: 50%; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-hourglass-half"></i></div>
    </div>
</div>

<!-- Beautiful Dynamic Tabbed Layout Control -->
<div class="dashboard-tabs" style="display:flex; gap:10px; margin-top:25px; margin-bottom:20px; border-bottom:2px solid var(--border-color); padding-bottom:10px; overflow-x:auto;">
    <button id="tab-stats" class="tab-btn active" onclick="switchDashboardTab('stats')">
        📊 Visual Charts & Analytics
    </button>
    <button id="tab-inbox" class="tab-btn" onclick="switchDashboardTab('inbox')">
        📬 Inbox Queues & Operations
        <?php if ($tot_requests + $tot_prints + $expiring_7_days_count > 0): ?>
            <span class="badge badge-orange" style="font-size:10px; padding:2px 6px; margin-left: 5px;"><?= $tot_requests + $tot_prints + $expiring_7_days_count ?></span>
        <?php endif; ?>
    </button>
    <button id="tab-density" class="tab-btn" onclick="switchDashboardTab('density')">
        📁 e-books Density
    </button>
    <button id="tab-lending" class="tab-btn" onclick="switchDashboardTab('lending')">
        ⏳ Lending Activity Logs
    </button>
</div>

<!-- Tabbed Panes Styling -->
<style>
.dashboard-tabs::-webkit-scrollbar {
    height: 4px;
}
.dashboard-tabs::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 2px;
}
.tab-btn {
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 13.5px;
    font-weight: 700;
    padding: 10px 18px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    border-radius: 8px;
    white-space: nowrap;
}
.tab-btn.active {
    background: var(--primary) !important;
    color: white !important;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}
.tab-btn:hover:not(.active) {
    background: var(--bg-slate);
    color: var(--navy-dark);
}
.dashboard-pane {
    animation: fadeInPane 0.25s ease-in-out;
}
@keyframes fadeInPane {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<!-- TAB 1: CHARTS & ANALYTICS PANE -->
<div id="pane-stats" class="dashboard-pane">
    <?php 
        $gradient_segments = [];
        $current_angle = 0;
        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f43f5e', '#64748b'];
        $slice_index = 0;
        
        foreach ($cat_distribution as $segment) {
            $count = (int)$segment['ebook_count'];
            if ($count <= 0) continue;
            $pct = ($tot_ebooks > 0) ? ($count / $tot_ebooks) : 0;
            $angle_delta = $pct * 360;
            $end_angle = $current_angle + $angle_delta;
            
            $color = $colors[$slice_index % count($colors)];
            $gradient_segments[] = "{$color} {$current_angle}deg {$end_angle}deg";
            
            $current_angle = $end_angle;
            $slice_index++;
        }
        
        $conic_gradient_str = !empty($gradient_segments) ? implode(', ', $gradient_segments) : "#e2e8f0 0deg 360deg";
    ?>
    <div class="charts-container" style="margin-bottom: 0;">
        <div class="chart-wrapper">
            <h4><i class="fa-solid fa-chart-column"></i> Physical Books Inventory</h4>
            <div style="display:flex; flex-direction:column; justify-content:space-between; height:100%; min-height:220px; padding:10px 0;">
                <div style="display:flex; justify-content:space-around; align-items:flex-end; flex:1; padding-bottom:15px; border-bottom:2px solid var(--border-color);">
                    <div style="display:flex; flex-direction:column; align-items:center; width:25%;">
                        <div style="font-size:14px; font-weight:700; color:#2563eb; margin-bottom:6px;"><?= $tot_physical ?></div>
                        <div style="background:linear-gradient(to top, #2563eb, #3b82f6); width:100%; max-width:45px; height:130px; border-radius:6px 6px 0 0; box-shadow:0 4px 10px rgba(37,99,235,0.15); transition:transform 0.2s;" onmouseover="this.style.transform='scaleY(1.05)'" onmouseout="this.style.transform='scaleY(1)'"></div>
                    </div>
                    <?php 
                        $lent_pct = $tot_physical > 0 ? ($tot_lent / $tot_physical) : 0;
                        $lent_height = max(10, round($lent_pct * 130));
                    ?>
                    <div style="display:flex; flex-direction:column; align-items:center; width:25%;">
                        <div style="font-size:14px; font-weight:700; color:#ef4444; margin-bottom:6px;"><?= $tot_lent ?></div>
                        <div style="background:linear-gradient(to top, #ef4444, #f87171); width:100%; max-width:45px; height:<?= $lent_height ?>px; border-radius:6px 6px 0 0; box-shadow:0 4px 10px rgba(239,68,68,0.15); transition:transform 0.2s;" onmouseover="this.style.transform='scaleY(1.05)'" onmouseout="this.style.transform='scaleY(1)'"></div>
                    </div>
                    <?php 
                        $stock_qty = max(0, $tot_physical - $tot_lent);
                        $stock_pct = $tot_physical > 0 ? ($stock_qty / $tot_physical) : 1;
                        $stock_height = max(10, round($stock_pct * 130));
                    ?>
                    <div style="display:flex; flex-direction:column; align-items:center; width:25%;">
                        <div style="font-size:14px; font-weight:700; color:#10b981; margin-bottom:6px;"><?= $stock_qty ?></div>
                        <div style="background:linear-gradient(to top, #10b981, #34d399); width:100%; max-width:45px; height:<?= $stock_height ?>px; border-radius:6px 6px 0 0; box-shadow:0 4px 10px rgba(16,185,129,0.15); transition:transform 0.2s;" onmouseover="this.style.transform='scaleY(1.05)'" onmouseout="this.style.transform='scaleY(1)'"></div>
                    </div>
                </div>
                <div style="display:flex; justify-content:space-around; text-align:center; font-size:11px; font-weight:600; color:var(--text-muted); margin-top:10px;">
                    <span style="width:33%;">Total Books</span>
                    <span style="width:33%;">Total Lend</span>
                    <span style="width:33%;">In-House Stock</span>
                </div>
            </div>
        </div>
        
        <div class="chart-wrapper">
            <h4><i class="fa-solid fa-chart-pie"></i> E-Book Category Spread</h4>
            <div style="display:flex; align-items:center; justify-content:space-between; height:100%; min-height:220px; gap:20px; padding:10px 0;">
                <div style="position:relative; width:130px; height:130px; flex-shrink:0; border-radius:50%; background:conic-gradient(<?= $conic_gradient_str ?>); box-shadow:0 8px 24px rgba(0,0,0,0.06); display:flex; align-items:center; justify-content:center;">
                    <div style="width:75px; height:75px; background:white; border-radius:50%; display:flex; flex-direction:column; align-items:center; justify-content:center; box-shadow:inset 0 2px 6px rgba(0,0,0,0.04);">
                        <span style="font-size:20px; font-weight:700; color:var(--navy-dark);"><?= $tot_ebooks ?></span>
                        <span style="font-size:10px; color:var(--text-muted); font-weight:600;">E-Books</span>
                    </div>
                </div>
                
                <div style="flex:1; display:flex; flex-direction:column; gap:8px; max-height:180px; overflow-y:auto; padding-right:5px;">
                    <?php 
                    $legend_index = 0;
                    if (empty($cat_distribution)) {
                        echo '<div style="font-size:12px; color:var(--text-muted);">No categories available.</div>';
                    } else {
                        foreach ($cat_distribution as $segment) {
                            $count = (int)$segment['ebook_count'];
                            if ($count <= 0) continue;
                            $pct = $tot_ebooks > 0 ? round(($count / $tot_ebooks) * 100) : 0;
                            $color = $colors[$legend_index % count($colors)];
                            echo '
                            <div style="display:flex; align-items:center; justify-content:space-between; font-size:11px; font-weight:600; color:var(--navy-dark);">
                                <span style="display:flex; align-items:center; gap:6px;">
                                    <span style="width:8px; height:8px; border-radius:50%; background:' . $color . '; display:inline-block;"></span>
                                    <span>' . e($segment['name']) . '</span>
                                </span>
                                <span class="muted">' . $count . ' (' . $pct . '%)</span>
                            </div>';
                            $legend_index++;
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TAB 2: INBOUND QUEUES & OPERATIONS -->
<div id="pane-inbox" class="dashboard-pane" style="display:none;">
    
    <!-- Consolidated Pending Requests Queue -->
    <div class="card" style="margin-bottom:25px;">
        <h3><i class="fa-solid fa-bell" style="color:var(--accent-orange);"></i> Consolidated Pending Requests Queue</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Member Name</th>
                        <th>E-Book Target</th>
                        <th>Category Type</th>
                        <th>Details / Specs</th>
                        <th>Request Time</th>
                        <th>Quick Jump</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $p_read = [];
                    $pr_q = $db->query("SELECT r.*, m.name, e.title FROM reading_requests r JOIN members m ON m.id = r.member_id JOIN ebooks e ON e.id = r.ebook_id WHERE r.status = 'Pending'");
                    while($row = $pr_q->fetch_assoc()) {
                        $p_read[] = [
                            'name' => $row['name'],
                            'title' => $row['title'],
                            'type' => 'Reading',
                            'details' => 'Online Secure Reader Access',
                            'time' => $row['requested_at'],
                            'link' => '?action=admin&tab=requests'
                        ];
                    }

                    $p_print = [];
                    $pp_q = $db->query("SELECT p.*, m.name, e.title FROM print_requests p JOIN members m ON m.id = p.member_id JOIN ebooks e ON e.id = p.ebook_id WHERE p.status = 'Pending'");
                    while($row = $pp_q->fetch_assoc()) {
                        $p_print[] = [
                            'name' => $row['name'],
                            'title' => $row['title'],
                            'type' => 'Print',
                            'details' => 'Pages requested: ' . $row['pages'],
                            'time' => $row['requested_at'],
                            'link' => '?action=admin&tab=prints'
                        ];
                    }

                    $all_pending = array_merge($p_read, $p_print);
                    usort($all_pending, function($a, $b) {
                        return strcmp($b['time'], $a['time']);
                    });

                    if (empty($all_pending)) {
                        echo '<tr><td colspan="6" style="text-align:center; color:var(--text-muted)"><i class="fa-solid fa-circle-check" style="color:var(--accent-green);"></i> All member inbox queues are empty! Excellent work.</td></tr>';
                    } else {
                        foreach ($all_pending as $item) {
                            $badge = $item['type'] === 'Reading' 
                                ? '<span class="badge badge-blue"><i class="fa-solid fa-book-open"></i> Reading Request</span>' 
                                : '<span class="badge badge-orange"><i class="fa-solid fa-print"></i> Print Request</span>';
                            echo '
                            <tr>
                                <td>' . e($item['name']) . '</td>
                                <td>' . e($item['title']) . '</td>
                                <td>' . $badge . '</td>
                                <td><strong style="font-size:12px;">' . e($item['details']) . '</strong></td>
                                <td>' . date('d-m-Y h:i A', strtotime($item['time'])) . '</td>
                                <td><a class="btn" style="padding:4px 8px; font-size:12px;" href="' . $item['link'] . '"><i class="fa-solid fa-arrow-up-right-from-square"></i> Process</a></td>
                            </tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Feature 4: Expiring Members Alert Panel -->
    <?php if ($expiring_7_days_count > 0): ?>
        <div class="card" style="margin-bottom: 25px; border-left: 5px solid var(--accent-orange);">
            <h3 style="color: var(--accent-orange);"><i class="fa-solid fa-triangle-exclamation"></i> Memberships Expiring within 7 Days</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Membership ID</th>
                            <th>Name</th>
                            <th>Mobile Number</th>
                            <th>Expiry Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $exp_list = $db->query("SELECT id, membership_id, name, mobile, end_date FROM members WHERE is_active = 1 AND end_date >= DATE(NOW()) AND end_date <= DATE_ADD(NOW(), INTERVAL 7 DAY) ORDER BY end_date ASC");
                        while ($el = $exp_list->fetch_assoc()) {
                            echo '
                            <tr>
                                <td><strong>' . e($el['membership_id']) . '</strong></td>
                                <td>' . e($el['name']) . '</td>
                                <td>' . e($el['mobile']) . '</td>
                                <td style="color: var(--accent-orange); font-weight:700;">' . date('d-m-Y', strtotime($el['end_date'])) . '</td>
                                <td><a class="btn" style="padding: 4px 10px; font-size: 11px; background: var(--accent-orange);" href="?action=admin&tab=view_members&view=' . $el['id'] . '#renew"><i class="fa-solid fa-rotate"></i> Renew Plan</a></td>
                            </tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Operations Quick-Jumps Grid -->
    <div class="grid" style="gap: 20px;">
        <div class="card" style="margin-bottom:0;">
            <h3><i class="fa-solid fa-envelope-open-text" style="color:var(--primary);"></i> Inbound Member Inbox</h3>
            <p><i class="fa-solid fa-circle-exclamation" style="color:var(--accent-orange);"></i> Pending Reading Requests: <strong><?= $tot_requests ?></strong></p>
            <p><i class="fa-solid fa-print" style="color:var(--primary);"></i> Pending Print Jobs: <strong><?= $tot_prints ?></strong></p>
            <div style="margin-top:20px; display:flex; gap:10px; flex-wrap:wrap;">
                <a href="?action=admin&tab=requests" class="btn"><i class="fa-solid fa-receipt"></i> E-Reading Inbox</a>
                <a href="?action=admin&tab=prints" class="btn" style="background:var(--navy-light);"><i class="fa-solid fa-print"></i> Print Jobs</a>
            </div>
        </div>
        <div class="card" style="margin-bottom:0;">
            <h3><i class="fa-solid fa-hand-holding-hand" style="color:var(--accent-green);"></i> Circulation & Lending Control</h3>
            <p><i class="fa-solid fa-book" style="color:var(--primary);"></i> Currently Issued Books: <strong><?= $tot_lent ?></strong></p>
            <p><i class="fa-solid fa-triangle-exclamation" style="color:var(--accent-red);"></i> Overdue Accounts: <strong><?= $tot_overdue_count ?></strong></p>
            <div style="margin-top:20px; display:flex; gap:10px; flex-wrap:wrap;">
                <a href="?action=admin&tab=lending" class="btn" style="background:var(--accent-green);"><i class="fa-solid fa-plus"></i> Issue / Lend Book</a>
                <a href="?action=admin&tab=view_lending" class="btn" style="background:var(--navy-light);"><i class="fa-solid fa-list"></i> Lending Logs</a>
            </div>
        </div>
    </div>
</div>

<!-- TAB 3: CATEGORY DENSITY METER -->
<div id="pane-density" class="dashboard-pane" style="display:none;">
    <div class="card" style="margin-bottom:0;">
        <h3><i class="fa-solid fa-layer-group"></i> E-Book Category</h3>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:15px; margin-top:15px;">
            <?php 
            if (empty($cat_distribution)) {
                echo '<p class="muted">No categories created yet.</p>';
            } else {
                foreach ($cat_distribution as $c_item) {
                    $cnt = (int)$c_item['ebook_count'];
                    $pct = $tot_ebooks > 0 ? round(($cnt / $tot_ebooks) * 100) : 0;
                    echo '
                    <div style="background:var(--bg-slate); padding:12px 16px; border-radius:10px; border:1px solid var(--border-color);">
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:13px; font-weight:600;">
                            <span>' . e($c_item['name']) . '</span>
                            <span class="badge badge-blue">' . $cnt . ' E-Books (' . $pct . '%)</span>
                        </div>
                        <div style="background:#e2e8f0; height:8px; border-radius:4px; overflow:hidden;">
                            <div style="background:var(--primary); width:' . max(5, $pct) . '%; height:100%; border-radius:4px; transition:width 0.5s ease;"></div>
                        </div>
                    </div>';
                }
            }
            ?>
        </div>
    </div>
</div>

<!-- TAB 4: LENDING LOGS -->
<div id="pane-lending" class="dashboard-pane" style="display:none;">
    <div class="card" style="margin-bottom:0;">
        <h3><i class="fa-solid fa-clock-rotate-left"></i> Recent Book Lending Activity</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Member Name</th>
                        <th>Lending Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (empty($recent_lendings)) {
                        echo '<tr><td colspan="5" style="text-align:center; color:var(--text-muted);">No lending transactions logged yet.</td></tr>';
                    } else {
                        foreach ($recent_lendings as $rl) {
                            $f_info = calculate_fine($rl['due_date'], $rl['returned_at']);
                            $due_time = strtotime($rl['due_date']);
                            $today_time = strtotime(date('Y-m-d'));
                            $days_diff = (int)floor(($due_time - $today_time) / 86400);

                            $due_col_html = date('d-m-Y', $due_time);
                            if (!$rl['returned_at']) {
                                if ($days_diff < 0) {
                                    $due_col_html = '<span style="color:var(--accent-red); font-weight:700;">' . date('d-m-Y', $due_time) . '</span>';
                                } elseif ($days_diff <= 3) {
                                    $due_col_html = '<span style="color:var(--accent-orange); font-weight:700;">' . date('d-m-Y', $due_time) . '</span>';
                                }
                            }

                            $l_badge = $rl['returned_at'] 
                                ? '<span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> Returned</span>'
                                : ($days_diff < 0 
                                    ? '<span class="badge badge-red"><i class="fa-solid fa-clock"></i> Overdue (' . abs($days_diff) . 'd)</span>' 
                                    : ($days_diff <= 3
                                        ? '<span class="badge" style="background:#fffbeb; color:var(--accent-orange); border:1px solid var(--accent-orange); font-weight:600;"><i class="fa-solid fa-hourglass-half"></i> Due Soon (' . ($days_diff == 0 ? 'Today' : $days_diff . 'd') . ')</span>'
                                        : '<span class="badge badge-red"><i class="fa-solid fa-book"></i> Not Returned</span>'));
                            echo '
                            <tr>
                                <td>' . e($rl['title']) . '</td>
                                <td>' . e($rl['name']) . '</td>
                                <td>' . date('d-m-Y', strtotime($rl['lent_at'])) . '</td>
                                <td>' . $due_col_html . '</td>
                                <td>' . $l_badge . '</td>
                            </tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tab Switcher Script Engine -->
<script class="dynamic-script">
window.switchDashboardTab = function(tabId) {
    // Hide all panes
    document.querySelectorAll('.dashboard-pane').forEach(el => el.style.display = 'none');
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    
    // Show selected pane
    const activePane = document.getElementById('pane-' + tabId);
    if (activePane) {
        activePane.style.display = 'block';
    }
    // Add active class to clicked button
    const activeBtn = document.getElementById('tab-' + tabId);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
};
</script>

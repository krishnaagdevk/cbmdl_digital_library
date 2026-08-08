<?php
// views/admin/view_lending.php
if (!defined('BASE_URL')) exit;

$status = $_GET['status'] ?? 'not_returned';
$search = trim($_GET['search'] ?? '');
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$where = [];
if ($status === 'returned') {
    $where[] = 'l.returned_at IS NOT NULL';
} elseif ($status === 'not_returned') {
    $where[] = 'l.returned_at IS NULL';
}

if ($search !== '') {
    $sq = $db->real_escape_string($search);
    $where[] = "(p.title LIKE '%$sq%' OR p.book_code LIKE '%$sq%' OR m.name LIKE '%$sq%' OR m.membership_id LIKE '%$sq%')";
}

if ($from_date !== '') {
    $where[] = "l.lent_at >= '" . $db->real_escape_string($from_date) . " 00:00:00'";
}
if ($to_date !== '') {
    $where[] = "l.lent_at <= '" . $db->real_escape_string($to_date) . " 23:59:59'";
}

$whereClause = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';
?>

<div class="card" style="margin-bottom: 25px;">
    <h3><i class="fa-solid fa-filter"></i> Filter Lending History</h3>
    <form method="get" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
        <input type="hidden" name="action" value="admin">
        <input type="hidden" name="tab" value="view_lending">
        
        <div>
            <label for="len_search">Search Title / Book Code / Member</label>
            <input id="len_search" name="search" value="<?= e($search) ?>" placeholder="Search book, code, or member...">
        </div>
        
        <div>
            <label for="lendingStatusSelect">Return Status</label>
            <select id="lendingStatusSelect" name="status">
                <option value="not_returned" <?= $status === 'not_returned' ? 'selected' : '' ?>>Not Returned</option>
                <option value="returned" <?= $status === 'returned' ? 'selected' : '' ?>>Returned</option>
                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Records</option>
            </select>
        </div>

        <div>
            <label for="len_from_date">From Date</label>
            <input type="date" id="len_from_date" name="from_date" value="<?= e($from_date) ?>">
        </div>
        
        <div>
            <label for="len_to_date">To Date</label>
            <input type="date" id="len_to_date" name="to_date" value="<?= e($to_date) ?>">
        </div>
        
        <div>
            <label style="visibility:hidden; margin-bottom:6px; display:block;">Action</label>
            <div style="display:flex; gap:8px;">
                <button type="submit" style="flex:1; padding:12px;"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
                <a href="?action=admin&tab=view_lending" class="btn btn-secondary" style="display:flex; align-items:center; justify-content:center; padding:12px; background:var(--bg-slate); color:var(--text-color); border:1px solid var(--border-color); text-decoration:none;"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <h3><i class="fa-solid fa-timeline"></i> Lending History</h3>

    <div class="table-responsive">
        <table id="lendingLogTable">
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Member Name</th>
                    <th>Date of Lending</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $p_limit = max(5, min(200, (int)($_GET['p_limit'] ?? 10)));
                $p_page = max(1, (int)($_GET['p_page'] ?? 1));

                $cnt_res = $db->query("SELECT COUNT(*) c FROM lendings l JOIN physical_books p ON p.id = l.physical_book_id JOIN members m ON m.id = l.member_id" . $whereClause);
                $total_items = (int)($cnt_res ? $cnt_res->fetch_assoc()['c'] : 0);
                $total_pages = ceil($total_items / $p_limit);
                $p_offset = ($p_page - 1) * $p_limit;

                $x = $db->query("SELECT l.*, p.title, p.book_code, m.name, m.membership_id FROM lendings l JOIN physical_books p ON p.id = l.physical_book_id JOIN members m ON m.id = l.member_id" . $whereClause . " ORDER BY l.lent_at DESC LIMIT $p_limit OFFSET $p_offset");
                $lCount = 0;
                while($r = $x->fetch_assoc()) {
                    $lCount++;
                    
                    $is_late_return = false;
                    if ($r['returned_at']) {
                        $ret_date_str = date('Y-m-d', strtotime($r['returned_at']));
                        $due_date_str = date('Y-m-d', strtotime($r['due_date']));
                        if ($ret_date_str > $due_date_str) {
                            $is_late_return = true;
                        }
                    }

                    if ($r['returned_at']) {
                        if ($is_late_return) {
                            $returnCol = '<span style="font-size:12.5px; color:var(--accent-red, #dc2626); font-weight:700;"><i class="fa-solid fa-box-archive"></i> ' . date('d-m-Y', strtotime($r['returned_at'])) . ' <small>(Late Return)</small></span>';
                        } else {
                            $returnCol = '<span style="font-size:12px; color:var(--text-muted); font-weight:600;"><i class="fa-solid fa-box-archive"></i> ' . date('d-m-Y', strtotime($r['returned_at'])) . '</span>';
                        }
                    } else {
                        $returnCol = '<form method="post" action="?action=return_book" style="display:inline; margin:0;"><input type="hidden" name="id" value="' . $r['id'] . '"><input type="hidden" name="tab" value="view_lending">' . csrf_input() . '<button class="btn" type="submit" style="padding:6px 12px;"><i class="fa-solid fa-rotate-left"></i> Tag Return</button></form>';
                    }
                    
                    $due_time = strtotime($r['due_date']);
                    $today_time = strtotime(date('Y-m-d'));
                    $days_diff = (int)floor(($due_time - $today_time) / 86400);

                    $due_col_html = date('d-m-Y', $due_time);
                    $row_style = '';
                    if (!$r['returned_at']) {
                        if ($days_diff < 0) {
                            $row_style = ' style="background-color: #fef2f2;"';
                            $due_col_html = '<span style="color:var(--accent-red, #ef4444); font-weight:700;"><i class="fa-solid fa-circle-exclamation"></i> ' . date('d-m-Y', $due_time) . ' <small>(Overdue)</small></span>';
                        } elseif ($days_diff <= 3) {
                            $row_style = ' style="background-color: #fffbeb;"';
                            $due_label = ($days_diff === 0) ? 'Due Today' : 'Due in ' . $days_diff . 'd';
                            $due_col_html = '<span style="color:var(--accent-orange, #f59e0b); font-weight:700;"><i class="fa-solid fa-clock"></i> ' . date('d-m-Y', $due_time) . ' <small>(' . $due_label . ')</small></span>';
                        }
                    }
                    
                    $mem_id = !empty($r['membership_id']) ? $r['membership_id'] : ('MID-' . $r['member_id']);

                    echo '
                    <tr' . $row_style . '>
                        <td>
                            <span style="font-weight:600; color:var(--navy-dark); display:block;">' . e($r['title']) . '</span>
                            <small style="font-size:11px; color:var(--text-muted); font-weight:600;"><i class="fa-solid fa-barcode"></i> Book ID: <code style="background:var(--bg-slate); padding:1px 5px; border-radius:4px; font-weight:700; font-size:11px; color:var(--navy-dark); border:1px solid var(--border-color);">' . e($r['book_code']) . '</code></small>
                        </td>
                        <td>
                            <span style="font-weight:600; color:var(--navy-dark); display:block;">' . e($r['name']) . '</span>
                            <small style="font-size:11px; color:var(--text-muted); font-weight:600;"><i class="fa-solid fa-id-card"></i> Member ID: <code style="background:var(--bg-slate); padding:1px 5px; border-radius:4px; font-weight:700; font-size:11px; color:var(--navy-dark); border:1px solid var(--border-color);">' . e($mem_id) . '</code></small>
                        </td>
                        <td>' . date('d-m-Y h:i A', strtotime($r['lent_at'])) . '</td>
                        <td>' . $due_col_html . '</td>
                        <td>' . $returnCol . '</td>
                    </tr>';
                }
                if ($lCount === 0) {
                    echo '<tr><td colspan="5" style="text-align:center; padding:30px; color:var(--text-muted);">No lending records found in database.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Premium Pagination Component -->
    <?php if ($total_items > 0): ?>
        <?php
        $qs = $_GET;
        unset($qs['p_page']);
        $qs_str = http_build_query($qs);
        $qs_str = $qs_str ? '&' . $qs_str : '';

        // Generate smart page list with ellipses
        $pages_to_show = get_smart_pagination_items($p_page, $total_pages);
        ?>
        <div class="pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; flex-wrap:wrap; gap:15px; border-top:1px solid var(--border-color); padding-top:15px;">
            <div style="display:flex; align-items:center; gap:15px; flex-wrap:wrap;">
                <div style="font-size:13px; color:var(--text-muted);">
                    Showing <strong><?= $p_offset + 1 ?></strong> to <strong><?= min($p_offset + $p_limit, $total_items) ?></strong> of <strong><?= $total_items ?></strong> lending records
                </div>
                <div style="display:inline-flex; align-items:center; gap:6px; font-size:13px; color:var(--text-muted);">
                    <span>Per page:</span>
                    <select onchange="window.location.href=this.value;" style="padding:4px 8px; font-size:12px; border-radius:6px; border:1px solid var(--border-color); background:var(--card-bg, #fff); color:var(--text-color); cursor:pointer; font-weight:600;">
                        <?php foreach ([10, 25, 50, 100, 200] as $lim): ?>
                            <?php
                            $lim_qs = $_GET;
                            $lim_qs['p_limit'] = $lim;
                            $lim_qs['p_page'] = 1;
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
                    <a href="?p_page=1<?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="First Page"><i class="fa-solid fa-angles-left"></i></a>
                    <a href="?p_page=<?= $p_page - 1 ?><?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Previous Page"><i class="fa-solid fa-angle-left"></i> Prev</a>
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
                        <a href="?p_page=<?= $p_item ?><?= $qs_str ?>" class="btn" style="padding:6px 12px; background:var(--bg-slate); color:var(--text-color); font-size:12px; border-radius:6px; text-decoration:none;"><?= $p_item ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if ($p_page < $total_pages): ?>
                    <a href="?p_page=<?= $p_page + 1 ?><?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Next Page">Next <i class="fa-solid fa-angle-right"></i></a>
                    <a href="?p_page=<?= $total_pages ?><?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="Last Page"><i class="fa-solid fa-angles-right"></i></a>
                <?php else: ?>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;">Next <i class="fa-solid fa-angle-right"></i></span>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-right"></i></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
// views/admin/view_lending.php
if (!defined('BASE_URL')) exit;

$sort = $_GET['sort'] ?? 'lent_desc';

$orderBy = 'l.lent_at DESC';
if ($sort === 'lent_asc') {
    $orderBy = 'l.lent_at ASC';
} elseif ($sort === 'due_desc') {
    $orderBy = 'l.due_date DESC';
} elseif ($sort === 'due_asc') {
    $orderBy = 'l.due_date ASC';
} elseif ($sort === 'title_asc') {
    $orderBy = 'p.title ASC';
} elseif ($sort === 'title_desc') {
    $orderBy = 'p.title DESC';
} elseif ($sort === 'member_asc') {
    $orderBy = 'm.name ASC';
} elseif ($sort === 'member_desc') {
    $orderBy = 'm.name DESC';
}
?>
<div class="card">
    <h3><i class="fa-solid fa-timeline"></i>Lending History</h3>
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; gap:15px; flex-wrap:wrap;">
        <div style="flex:1; min-width:280px;">
            <input type="text" id="lendingFilterInput" placeholder="Type to filter lending logs..." style="margin-bottom:0; width:100%;">
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <label for="lendingSortSelect" style="margin:0; font-size:13px; font-weight:600; white-space:nowrap; color:var(--text-muted);"><i class="fa-solid fa-arrow-down-up-wide"></i> Sort By:</label>
            <select id="lendingSortSelect" onchange="location.href='?action=admin&tab=view_lending&sort=' + this.value" style="margin:0; padding:8px 12px; font-size:13px; width:auto; background:var(--bg-slate); border:1px solid var(--border-color); border-radius:8px;">
                <option value="lent_desc" <?= $sort === 'lent_desc' ? 'selected' : '' ?>>Lent Date (Newest First)</option>
                <option value="lent_asc" <?= $sort === 'lent_asc' ? 'selected' : '' ?>>Lent Date (Oldest First)</option>
                <option value="due_desc" <?= $sort === 'due_desc' ? 'selected' : '' ?>>Due Date (Newest First)</option>
                <option value="due_asc" <?= $sort === 'due_asc' ? 'selected' : '' ?>>Due Date (Oldest First)</option>
                <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>Book Title (A-Z)</option>
                <option value="title_desc" <?= $sort === 'title_desc' ? 'selected' : '' ?>>Book Title (Z-A)</option>
                <option value="member_asc" <?= $sort === 'member_asc' ? 'selected' : '' ?>>Member Name (A-Z)</option>
                <option value="member_desc" <?= $sort === 'member_desc' ? 'selected' : '' ?>>Member Name (Z-A)</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table id="lendingLogTable">
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Book ID</th>
                    <th>Member Name</th>
                    <th>Date of Lending</th>
                    <th>Due Date</th>
                    <th>Return Operations</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $p_limit = 10;
                $p_page = max(1, (int)($_GET['p_page'] ?? 1));

                $cnt_res = $db->query("SELECT COUNT(*) c FROM lendings l JOIN physical_books p ON p.id = l.physical_book_id JOIN members m ON m.id = l.member_id");
                $total_items = (int)($cnt_res ? $cnt_res->fetch_assoc()['c'] : 0);
                $total_pages = ceil($total_items / $p_limit);
                $p_offset = ($p_page - 1) * $p_limit;

                $x = $db->query("SELECT l.*, p.title, p.book_code, m.name FROM lendings l JOIN physical_books p ON p.id = l.physical_book_id JOIN members m ON m.id = l.member_id ORDER BY $orderBy LIMIT $p_limit OFFSET $p_offset");
                $lCount = 0;
                while($r = $x->fetch_assoc()) {
                    $lCount++;
                    
                    $returnCol = $r['returned_at'] 
                        ? '<span style="font-size:12px; color:var(--text-muted); font-weight:600;"><i class="fa-solid fa-box-archive"></i> Settled ' . date('d-m-Y', strtotime($r['returned_at'])) . '</span>' 
                        : '<form method="post" action="?action=return_book" style="display:inline; margin:0;"><input type="hidden" name="id" value="' . $r['id'] . '"><input type="hidden" name="tab" value="view_lending">' . csrf_input() . '<button class="btn" type="submit" style="padding:6px 12px;"><i class="fa-solid fa-rotate-left"></i> Tag Return</button></form>';
                    
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
                    
                    echo '
                    <tr' . $row_style . '>
                        <td style="font-weight:600; color:var(--navy-dark);">' . e($r['title']) . '</td>
                        <td><code style="background:var(--bg-slate); padding:2px 6px; border-radius:4px; font-weight:700; font-size:12px; color:var(--navy-dark); border:1px solid var(--border-color);">' . e($r['book_code']) . '</code></td>
                        <td>' . e($r['name']) . '</td>
                        <td>' . date('d-m-Y h:i A', strtotime($r['lent_at'])) . '</td>
                        <td>' . $due_col_html . '</td>
                        <td>' . $returnCol . '</td>
                    </tr>';
                }
                if ($lCount === 0) {
                    echo '<tr><td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted);">No lending records found in database.</td></tr>';
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
                Showing <strong><?= $p_offset + 1 ?></strong> to <strong><?= min($p_offset + $p_limit, $total_items) ?></strong> of <strong><?= $total_items ?></strong> lending records
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

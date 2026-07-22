<?php
// views/user/lending.php
if (!defined('BASE_URL')) exit;

// Pagination logic for Physical Books Lending History
$p_limit = 10;
$p_page = max(1, (int)($_GET['p_page'] ?? 1));

$total_res = $db->query("SELECT COUNT(*) c FROM lendings WHERE member_id = $mid");
$total_items = (int)($total_res ? $total_res->fetch_assoc()['c'] : 0);
$total_pages = ceil($total_items / $p_limit);
$p_offset = ($p_page - 1) * $p_limit;

$stmt = $db->prepare("SELECT l.*, p.title, p.book_code FROM lendings l JOIN physical_books p ON p.id = l.physical_book_id WHERE l.member_id = ? ORDER BY l.lent_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("iii", $mid, $p_limit, $p_offset);
$stmt->execute();
$x = $stmt->get_result();
?>

<div class="card">
    <h3><i class="fa-solid fa-clock-rotate-left"></i> My Physical Books Lending History</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Book Name</th>
                    <th>Book ID</th>
                    <th>Lending Date</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $count = 0;
                while($r = $x->fetch_assoc()) {
                    $count++;
                    $fine_data = calculate_fine($r['due_date'], $r['returned_at']);
                    $due_time = strtotime($r['due_date']);
                    $today_time = strtotime(date('Y-m-d'));
                    $days_diff = (int)floor(($due_time - $today_time) / 86400);

                    $due_col_html = date('d-m-Y', $due_time);
                    if (!$r['returned_at']) {
                        if ($days_diff < 0) {
                            $due_col_html = '<span style="color:var(--accent-red, #ef4444); font-weight:700;"><i class="fa-solid fa-circle-exclamation"></i> ' . date('d-m-Y', $due_time) . ' <small>(Overdue)</small></span>';
                        } elseif ($days_diff <= 3) {
                            $due_label = ($days_diff === 0) ? 'Due Today' : 'Due in ' . $days_diff . 'd';
                            $due_col_html = '<span style="color:var(--accent-orange, #f59e0b); font-weight:700;"><i class="fa-solid fa-clock"></i> ' . date('d-m-Y', $due_time) . ' <small>(' . $due_label . ')</small></span>';
                        }
                    }

                    $badge = '<span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> Returned</span>';
                    if (!$r['returned_at']) {
                        if ($days_diff < 0) {
                            $badge = '<span class="badge badge-red"><i class="fa-solid fa-clock"></i> Overdue (' . abs($days_diff) . 'd)</span>';
                        } elseif ($days_diff <= 3) {
                            $badge = '<span class="badge" style="background:#fffbeb; color:var(--accent-orange); border:1px solid var(--accent-orange); font-weight:600;"><i class="fa-solid fa-hourglass-half"></i> Due Soon</span>';
                        } else {
                            $badge = '<span class="badge badge-red"><i class="fa-solid fa-book"></i> Not Returned</span>';
                        }
                    }
                    
                    echo '
                    <tr>
                        <td>' . e($r['title']) . '</td>
                        <td><code style="background:var(--bg-slate); padding:2px 6px; border-radius:4px; font-weight:700; font-size:12px; color:var(--navy-dark); border:1px solid var(--border-color);">' . e($r['book_code']) . '</code></td>
                        <td>' . date('d-m-Y', strtotime($r['lent_at'])) . '</td>
                        <td>' . $due_col_html . '</td>
                        <td>' . ($r['returned_at'] ? date('d-m-Y', strtotime($r['returned_at'])) : ($days_diff < 0 ? '<span style="color:var(--accent-red); font-weight:600;"><i class="fa-solid fa-triangle-exclamation"></i> Overdue</span>' : ($days_diff <= 3 ? '<span style="color:var(--accent-orange); font-weight:600;"><i class="fa-solid fa-clock"></i> Due Soon</span>' : '<span style="color:var(--primary); font-weight:600;"><i class="fa-solid fa-hourglass-half"></i> Book with you</span>'))) . '</td>
                        <td>' . $badge . '</td>
                    </tr>';
                }
                if ($count === 0) {
                    echo '<tr><td colspan="6" style="text-align:center; color:var(--text-muted)">No lending transactions recorded on this profile.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Premium Pagination Component -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; flex-wrap:wrap; gap:15px; border-top:1px solid var(--border-color); padding-top:15px;">
            <div style="font-size:13px; color:var(--text-muted);">
                Showing <strong><?= $p_offset + 1 ?></strong> to <strong><?= min($p_offset + $p_limit, $total_items) ?></strong> of <strong><?= $total_items ?></strong> records
            </div>
            <div class="pagination" style="display:flex; align-items:center; gap:6px;">
                <?php if ($p_page > 1): ?>
                    <a href="?action=user&tab=lending&p_page=1" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="First Page"><i class="fa-solid fa-angles-left"></i></a>
                    <a href="?action=user&tab=lending&p_page=<?= $p_page - 1 ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Previous Page"><i class="fa-solid fa-angle-left"></i> Prev</a>
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
                        <a href="?action=user&tab=lending&p_page=<?= $i ?>" class="btn" style="padding:6px 12px; background:var(--bg-slate); color:var(--text-color); font-size:12px; border-radius:6px;"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($p_page < $total_pages): ?>
                    <a href="?action=user&tab=lending&p_page=<?= $p_page + 1 ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Next Page">Next <i class="fa-solid fa-angle-right"></i></a>
                    <a href="?action=user&tab=lending&p_page=<?= $total_pages ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="Last Page"><i class="fa-solid fa-angles-right"></i></a>
                <?php else: ?>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;">Next <i class="fa-solid fa-angle-right"></i></span>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-right"></i></span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

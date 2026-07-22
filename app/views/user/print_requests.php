<?php
// views/user/print_requests.php
if (!defined('BASE_URL')) exit;

// Pagination settings
$pr_limit = 10;
$pr_page = max(1, (int)($_GET['pr_page'] ?? 1));

$total_res = $db->query("SELECT COUNT(*) c FROM print_requests WHERE member_id = $mid");
$total_items = (int)($total_res ? $total_res->fetch_assoc()['c'] : 0);
$total_pages = ceil($total_items / $pr_limit);
$pr_offset = ($pr_page - 1) * $pr_limit;

$stmt = $db->prepare("SELECT p.*, e.title FROM print_requests p JOIN ebooks e ON e.id = p.ebook_id WHERE p.member_id = ? ORDER BY p.requested_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("iii", $mid, $pr_limit, $pr_offset);
$stmt->execute();
$print_query = $stmt->get_result();
?>

<div class="card">
    <h3><i class="fa-solid fa-print"></i> My E-Book Page Print Requests History</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>E-Book Title</th>
                    <th>Pages Requested</th>
                    <th>Request Timestamp</th>
                    <th>Job Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $print_count = 0;
                while ($pr = $print_query->fetch_assoc()) {
                    $print_count++;
                    $p_badge = $pr['status'] === 'Pending' 
                        ? '<span class="badge badge-orange"><i class="fa-solid fa-clock"></i> Pending (Awaiting Print)</span>' 
                        : '<span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> Completed</span>';
                    echo '
                    <tr>
                        <td>' . e($pr['title']) . '</td>
                        <td><strong>' . e($pr['pages']) . '</strong></td>
                        <td>' . date('d-m-Y h:i A', strtotime($pr['requested_at'])) . '</td>
                        <td>' . $p_badge . '</td>
                    </tr>';
                }
                if ($print_count === 0) {
                    echo '<tr><td colspan="4" style="text-align:center; color:var(--text-muted)">No print request jobs submitted on this profile.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Premium Pagination Component -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; flex-wrap:wrap; gap:15px; border-top:1px solid var(--border-color); padding-top:15px;">
            <div style="font-size:13px; color:var(--text-muted);">
                Showing <strong><?= $pr_offset + 1 ?></strong> to <strong><?= min($pr_offset + $pr_limit, $total_items) ?></strong> of <strong><?= $total_items ?></strong> print request records
            </div>
            <div class="pagination" style="display:flex; align-items:center; gap:6px;">
                <?php if ($pr_page > 1): ?>
                    <a href="?action=user&tab=print_requests&pr_page=1" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="First Page"><i class="fa-solid fa-angles-left"></i></a>
                    <a href="?action=user&tab=print_requests&pr_page=<?= $pr_page - 1 ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Previous Page"><i class="fa-solid fa-angle-left"></i> Prev</a>
                <?php else: ?>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-left"></i></span>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angle-left"></i> Prev</span>
                <?php endif; ?>

                <?php 
                $start_p = max(1, $pr_page - 2);
                $end_p = min($total_pages, $pr_page + 2);
                for($i = $start_p; $i <= $end_p; $i++): 
                ?>
                    <?php if ($i == $pr_page): ?>
                        <span class="btn" style="padding:6px 12px; background:var(--primary); color:white; font-size:12px; font-weight:700; border-radius:6px;"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?action=user&tab=print_requests&pr_page=<?= $i ?>" class="btn" style="padding:6px 12px; background:var(--bg-slate); color:var(--text-color); font-size:12px; border-radius:6px;"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pr_page < $total_pages): ?>
                    <a href="?action=user&tab=print_requests&pr_page=<?= $pr_page + 1 ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Next Page">Next <i class="fa-solid fa-angle-right"></i></a>
                    <a href="?action=user&tab=print_requests&pr_page=<?= $total_pages ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="Last Page"><i class="fa-solid fa-angles-right"></i></a>
                <?php else: ?>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;">Next <i class="fa-solid fa-angle-right"></i></span>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-right"></i></span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

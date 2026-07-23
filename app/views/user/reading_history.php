<?php
// views/user/reading_history.php
if (!defined('BASE_URL')) exit;

// Pagination settings
$b_limit = 10;
$b_page = max(1, (int)($_GET['b_page'] ?? 1));

$total_res = $db->query("SELECT COUNT(*) c FROM reading_requests WHERE member_id = $mid");
$total_items = (int)($total_res ? $total_res->fetch_assoc()['c'] : 0);
$total_pages = ceil($total_items / $b_limit);
$b_offset = ($b_page - 1) * $b_limit;

$stmt = $db->prepare("SELECT r.*, e.title FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE r.member_id = ? ORDER BY r.requested_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("iii", $mid, $b_limit, $b_offset);
$stmt->execute();
$x = $stmt->get_result();
?>
<div class="card">
    <h3><i class="fa-solid fa-clock-rotate-left"></i> My E-Book Reading Approvals History</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>E-Book Title</th>
                    <th>Request Date</th>
                    <th>Approved Duration</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $count = 0;
                while($r = $x->fetch_assoc()) {
                    $count++;
                    $isExp = ($r['status'] === 'Expired') || (!empty($r['expires_at']) && strtotime($r['expires_at']) <= time());
                    $badgeClass = 'badge-orange';
                    if ($r['status'] === 'Approved' && !$isExp) {
                        $badgeClass = 'badge-green';
                    } elseif ($r['status'] === 'Rejected' || $isExp) {
                        $badgeClass = 'badge-red';
                    }
                    
                    $statusText = $isExp ? 'Expired' : $r['status'];
                    
                    echo '
                    <tr>
                        <td>' . e($r['title']) . '</td>
                        <td>' . date('d-m-Y h:i A', strtotime($r['requested_at'])) . '</td>
                        <td>' . ($r['approved_at'] ? date('d-m-Y h:i A', strtotime($r['approved_at'])) . ' to ' . date('d-m-Y h:i A', strtotime($r['expires_at'])) : '--') . '</td>
                        <td><span class="badge ' . $badgeClass . '">' . $statusText . '</span></td>
                    </tr>';
                }
                if ($count === 0) {
                    echo '<tr><td colspan="4" style="text-align:center; color:var(--text-muted)">No e-reading request history on this profile.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Premium Pagination Component -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; flex-wrap:wrap; gap:15px; border-top:1px solid var(--border-color); padding-top:15px;">
            <div style="font-size:13px; color:var(--text-muted);">
                Showing <strong><?= $b_offset + 1 ?></strong> to <strong><?= min($b_offset + $b_limit, $total_items) ?></strong> of <strong><?= $total_items ?></strong> reading history records
            </div>
            <div class="pagination" style="display:flex; align-items:center; gap:6px;">
                <?php if ($b_page > 1): ?>
                    <a href="?action=user&tab=reading_history&b_page=1" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="First Page"><i class="fa-solid fa-angles-left"></i></a>
                    <a href="?action=user&tab=reading_history&b_page=<?= $b_page - 1 ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Previous Page"><i class="fa-solid fa-angle-left"></i> Prev</a>
                <?php else: ?>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-left"></i></span>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angle-left"></i> Prev</span>
                <?php endif; ?>

                <?php 
                $start_p = max(1, $b_page - 2);
                $end_p = min($total_pages, $b_page + 2);
                for($i = $start_p; $i <= $end_p; $i++): 
                ?>
                    <?php if ($i == $b_page): ?>
                        <span class="btn" style="padding:6px 12px; background:var(--primary); color:white; font-size:12px; font-weight:700; border-radius:6px;"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?action=user&tab=reading_history&b_page=<?= $i ?>" class="btn" style="padding:6px 12px; background:var(--bg-slate); color:var(--text-color); font-size:12px; border-radius:6px;"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($b_page < $total_pages): ?>
                    <a href="?action=user&tab=reading_history&b_page=<?= $b_page + 1 ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Next Page">Next <i class="fa-solid fa-angle-right"></i></a>
                    <a href="?action=user&tab=reading_history&b_page=<?= $total_pages ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="Last Page"><i class="fa-solid fa-angles-right"></i></a>
                <?php else: ?>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;">Next <i class="fa-solid fa-angle-right"></i></span>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-right"></i></span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

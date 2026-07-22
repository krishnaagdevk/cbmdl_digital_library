<?php
// views/user/reading_history.php
if (!defined('BASE_URL')) exit;
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
                $x = $db->query("SELECT r.*, e.title FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE r.member_id = $mid ORDER BY r.requested_at DESC");
                $count = 0;
                while($r = $x->fetch_assoc()) {
                    $count++;
                    $badgeClass = 'badge-orange';
                    if ($r['status'] === 'Approved') {
                        if (strtotime($r['expires_at']) > time()) {
                            $badgeClass = 'badge-green';
                        } else {
                            $badgeClass = 'badge-red';
                        }
                    } elseif ($r['status'] === 'Rejected') {
                        $badgeClass = 'badge-red';
                    }
                    
                    $statusText = $r['status'];
                    if ($r['status'] === 'Approved' && strtotime($r['expires_at']) <= time()) {
                        $statusText = 'Expired';
                    }
                    
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
</div>

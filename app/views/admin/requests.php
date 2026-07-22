<?php
// views/admin/requests.php
if (!defined('BASE_URL')) exit;

$status_filter = $_GET['status_filter'] ?? 'All';
$search = trim($_GET['search'] ?? '');

$where_clauses = [];
if ($status_filter === 'Pending') {
    $where_clauses[] = "r.status = 'Pending'";
} elseif ($status_filter === 'Approved') {
    $where_clauses[] = "r.status = 'Approved'";
} elseif ($status_filter === 'Rejected') {
    $where_clauses[] = "r.status = 'Rejected'";
}

if ($search !== '') {
    $search_escaped = $db->real_escape_string($search);
    $where_clauses[] = "(m.name LIKE '%$search_escaped%' OR e.title LIKE '%$search_escaped%')";
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = ' WHERE ' . implode(' AND ', $where_clauses);
}

$query_str = "SELECT r.*, m.name, e.title FROM reading_requests r JOIN members m ON m.id = r.member_id JOIN ebooks e ON e.id = r.ebook_id $where_sql ORDER BY r.requested_at DESC";
?>

<div class="card" style="margin-bottom: 25px;">
    <h3><i class="fa-solid fa-filter"></i> Filter Inbox</h3>
    <form method="get" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <input type="hidden" name="action" value="admin">
        <input type="hidden" name="tab" value="requests">
        
        <div>
            <label for="req_search">Search Member / E-Book</label>
            <input id="req_search" name="search" value="<?= e($search) ?>" placeholder="Enter member name or book title...">
        </div>
        
        <div>
            <label for="req_status">Request Status</label>
            <select id="req_status" name="status_filter">
                <option value="All" <?= $status_filter === 'All' ? 'selected' : '' ?>>All Requests</option>
                <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending Requests Only</option>
                <option value="Approved" <?= $status_filter === 'Approved' ? 'selected' : '' ?>>Approved Requests Only</option>
                <option value="Rejected" <?= $status_filter === 'Rejected' ? 'selected' : '' ?>>Rejected Requests Only</option>
            </select>
        </div>
        
        <div>
            <label style="visibility:hidden; margin-bottom:6px; display:block;">Action</label>
            <div style="display:flex; gap:8px;">
                <button type="submit" style="flex:1; padding:12px;"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
                <a href="?action=admin&tab=requests" class="btn btn-secondary" style="display:flex; align-items:center; justify-content:center; padding:12px; background:var(--bg-slate); color:var(--text-color); border:1px solid var(--border-color); text-decoration:none;"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <h3><i class="fa-solid fa-inbox"></i> Member e-Reading Requests Inbox</h3>
    <div class="table-responsive">
        <table id="requestsTable">
            <thead>
                <tr>
                    <th>Member Name</th>
                    <th>E-Book Title</th>
                    <th>Request Category</th>
                    <th>Request Timestamp</th>
                    <th>Status</th>
                    <th>Permission / Expiry Duration</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $x = $db->query($query_str);
                if ($x->num_rows === 0) {
                    echo '<tr><td colspan="6" style="text-align:center; padding: 30px; color:var(--text-muted);"><i class="fa-solid fa-circle-info" style="font-size:20px; margin-bottom:10px; display:block; color:var(--primary);"></i> No requests match the selected filters.</td></tr>';
                } else {
                    while($r = $x->fetch_assoc()) {
                        $statusText = $r['status'];
                        $badgeClass = 'badge-orange';
                        if ($r['status'] === 'Approved') $badgeClass = 'badge-green';
                        if ($r['status'] === 'Rejected') $badgeClass = 'badge-red';
                        
                        echo '<tr>';
                        echo '<td>' . e($r['name']) . '</td>';
                        echo '<td>' . e($r['title']) . '</td>';
                        echo '<td><span class="badge badge-blue"><i class="fa-solid fa-book-open"></i> Reading Request</span></td>';
                        echo '<td>' . date('d-m-Y h:i A', strtotime($r['requested_at'])) . '</td>';
                        echo '<td><span class="badge ' . $badgeClass . '">' . $statusText . '</span></td>';
                        echo '<td>';
                        if ($r['status'] === 'Pending') {
                            echo '
                            <div style="display:inline-flex; gap:6px; align-items:center;">
                                 <form method="post" action="?action=approve" style="display:inline-flex; gap:6px; align-items:center; margin:0;">
                                     ' . csrf_input() . '
                                     <input type="hidden" name="request_id" value="' . $r['id'] . '">
                                     <input type="number" name="minutes" min="1" placeholder="Minutes" required style="width:90px; margin:0; padding:8px;">
                                     <button style="padding:8px 12px;"><i class="fa-solid fa-circle-check"></i> Grant</button> 
                                 </form>
                                 <form method="post" action="?action=reject" style="display:inline-flex; margin:0;">
                                     ' . csrf_input() . '
                                     <input type="hidden" name="id" value="' . $r['id'] . '">
                                     <button class="danger btn btn-danger" type="submit" style="padding:8px 12px;"><i class="fa-solid fa-circle-xmark"></i> Reject</button>
                                 </form>
                             </div>';
                        } else {
                            echo $r['expires_at'] ? date('d-m-Y h:i A', strtotime($r['expires_at'])) : '--';
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

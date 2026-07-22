<?php
// views/user/lending.php
if (!defined('BASE_URL')) exit;
?>
<div class="card">
    <h3><i class="fa-solid fa-clock-rotate-left"></i> My Physical Books Lending History</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Book Name</th>
                    <th>Lending Timestamp</th>
                    <th>Due Date Target</th>
                    <th>Return Timestamp</th>
                    <th>Fine Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $x = $db->query("SELECT l.*, p.title FROM lendings l JOIN physical_books p ON p.id = l.physical_book_id WHERE l.member_id = $mid ORDER BY l.lent_at DESC");
                $count = 0;
                while($r = $x->fetch_assoc()) {
                    $count++;
                    $fine_data = calculate_fine($r['due_date'], $r['returned_at']);
                    $badge = '<span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> Settled</span>';
                    
                    if ($fine_data['days'] > 0) {
                        $badge = '<span class="badge badge-red"><i class="fa-solid fa-clock"></i> Overdue (₹' . number_style_format($fine_data['fine']) . ')</span>';
                    }
                    
                    echo '
                    <tr>
                        <td>' . e($r['title']) . '</td>
                        <td>' . date('d-m-Y', strtotime($r['lent_at'])) . '</td>
                        <td>' . date('d-m-Y', strtotime($r['due_date'])) . '</td>
                        <td>' . ($r['returned_at'] ? date('d-m-Y', strtotime($r['returned_at'])) : '<span style="color:var(--accent-orange); font-weight:600;"><i class="fa-solid fa-hourglass-half"></i> Book with you</span>') . '</td>
                        <td>' . $badge . '</td>
                    </tr>';
                }
                if ($count === 0) {
                    echo '<tr><td colspan="5" style="text-align:center; color:var(--text-muted)">No lending transactions recorded on this profile.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- My E-Book Print Requests -->
<div class="card" style="margin-top:20px;">
    <h3><i class="fa-solid fa-print"></i> My E-Book Page Print Requests</h3>
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
                $print_query = $db->query("SELECT p.*, e.title FROM print_requests p JOIN ebooks e ON e.id = p.ebook_id WHERE p.member_id = $mid ORDER BY p.requested_at DESC");
                $print_count = 0;
                while ($pr = $print_query->fetch_assoc()) {
                    $print_count++;
                    $p_badge = $pr['status'] === 'Pending' 
                        ? '<span class="badge badge-orange"><i class="fa-solid fa-clock"></i> Pending (Awaiting Print)</span>' 
                        : '<span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> Completed (Handout Ready)</span>';
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
</div>

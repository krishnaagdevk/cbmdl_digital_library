<?php
// views/admin/prints.php
if (!defined('BASE_URL')) exit;
?>
<div class="card">
    <h3><i class="fa-solid fa-print"></i> PDF Page Printing Queue</h3>
    <div class="table-responsive">
        <table id="printRequestsTable">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>E-Book Target Volume</th>
                    <th>Pages Target</th>
                    <th>Request Timestamp</th>
                    <th>Job Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $x = $db->query('SELECT p.*, m.name, e.title FROM print_requests p JOIN members m ON m.id = p.member_id JOIN ebooks e ON e.id = p.ebook_id ORDER BY p.requested_at DESC');
                while($r = $x->fetch_assoc()) {
                    $badgeClass = $r['status'] === 'Pending' ? 'badge-orange' : 'badge-green';
                    $actionBtn = $r['status'] === 'Pending' 
                        ? ' &nbsp; <form method="post" action="?action=complete_print" style="display:inline; margin:0;"><input type="hidden" name="id" value="' . $r['id'] . '">' . csrf_input() . '<button class="btn" type="submit" style="padding:6px 12px;"><i class="fa-solid fa-check"></i> Complete Job</button></form>' 
                        : '';
                    echo '
                    <tr>
                        <td>' . e($r['name']) . '</td>
                        <td>' . e($r['title']) . '</td>
                        <td><span style="font-weight:600;">' . e($r['pages']) . '</span></td>
                        <td>' . date('d-m-Y h:i A', strtotime($r['requested_at'])) . '</td>
                        <td>
                            <span class="badge ' . $badgeClass . '">' . e($r['status']) . '</span>
                            ' . $actionBtn . '
                        </td>
                    </tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

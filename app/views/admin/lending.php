<?php
// views/admin/lending.php
if (!defined('BASE_URL')) exit;

$lookup = trim($_GET['lookup'] ?? '');
$looked = null;
if ($lookup !== '') {
    $lStmt = $db->prepare("SELECT * FROM members WHERE membership_id = ? OR mobile = ? LIMIT 1");
    $lStmt->bind_param("ss", $lookup, $lookup);
    $lStmt->execute();
    $looked = $lStmt->get_result()->fetch_assoc();
    $lStmt->close();
}

?>

<div class="grid" style="grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
    <div class="card" style="margin-bottom:0;">
        <h3><i class="fa-solid fa-passport"></i> Issue Registry / Quick Member Lookup</h3>
        <form method="get" class="grid" style="grid-template-columns: 1fr;">
            <input type="hidden" name="action" value="admin">
            <input type="hidden" name="tab" value="lending">
            <div style="display:flex; gap:10px; width:100%; align-items:flex-end;">
                <div style="flex:1;">
                    <label for="lk_input">Registered ID or Phone Number</label>
                    <input id="lk_input" name="lookup" value="<?= e($lookup) ?>" placeholder="Membership Code or Mobile No..." style="margin-bottom:0;">
                </div>
                <button style="margin:6px 0;"><i class="fa-solid fa-magnifying-glass"></i> Lookup Member</button>
            </div>
        </form>
        <?php if ($lookup !== ''): ?>
            <div style="margin-top: 15px; padding: 15px; background: var(--bg-slate); border-radius: 10px; border:1px solid var(--border-color);">
                <?php if ($looked): ?>
                    <?php
                    $isExpired = strtotime($looked['end_date']) < time();
                    if ($looked['is_active'] == 0) {
                        $statusBadge = '<span class="badge badge-red"><i class="fa-solid fa-circle-xmark"></i> Suspended / Inactive</span>';
                    } elseif ($isExpired) {
                        $statusBadge = '<span class="badge badge-red"><i class="fa-solid fa-circle-exclamation"></i> Expired</span>';
                    } else {
                        $statusBadge = '<span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> Active</span>';
                    }
                    ?>
                    <p style="margin:0; font-size:14px; font-weight:600; color:var(--navy-dark);">
                        Matched Account: <span style="color:var(--primary); font-weight:700;"><?= e($looked['name']) ?></span> &nbsp;|&nbsp; 
                        Code: <code><?= e($looked['membership_id']) ?></code> &nbsp;|&nbsp; 
                        Validity: <span class="badge badge-green"><?= date('d-m-Y', strtotime($looked['end_date'])) ?></span> &nbsp;|&nbsp;
                        Status: <?= $statusBadge ?>
                    </p>
                <?php else: ?>
                    <p style="margin:0; font-size:14px; font-weight:600; color:var(--accent-red);"><i class="fa-solid fa-triangle-exclamation"></i> No matched active account found in database.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3><i class="fa-solid fa-book-medical"></i> Register Physical Lending Issue</h3>
        <form method="post" action="?action=lend" class="grid" style="grid-template-columns: 1fr 1fr;">
            <?= csrf_input() ?>
            <div style="grid-column: span 2;">
                <label for="ld_member">Member ID *</label>
                <input id="ld_member" name="member" value="<?= $looked ? e($looked['membership_id']) : '' ?>" placeholder="Enter Member ID" required>
            </div>
            <div>
                <label for="ld_book">Book ID *</label>
                <input id="ld_book" name="book_code" placeholder="Enter Book ID" required>
            </div>
            <div>
                <label for="ld_due">Issue Return Due Date *</label>
                <input id="ld_due" type="date" name="due_date" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
            </div>
            <div style="grid-column: span 2;">
                <label for="ld_tx">Payment Transaction ID *</label>
                <input id="ld_tx" name="transaction_id" placeholder="Enter Transaction ID" required>
            </div>
            <div style="grid-column: span 2; display:flex; align-items:flex-end; margin-top: 10px;">
                <button style="width:100%;"><i class="fa-solid fa-square-check"></i>Register Lending Issue</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <h3><i class="fa-solid fa-clock-rotate-left"></i>Lending History</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Book ID</th>
                    <th>Borrower Member</th>
                    <th>Lending Date</th>
                    <th>Due Date Target</th>
                    <th>Operation</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $filterClause = $looked ? ' WHERE l.member_id = ' . (int)$looked['id'] : '';
                $x = $db->query('SELECT l.*, p.title, p.book_code, m.name FROM lendings l JOIN physical_books p ON p.id = l.physical_book_id JOIN members m ON m.id = l.member_id' . $filterClause . ' ORDER BY l.lent_at DESC');
                while($r = $x->fetch_assoc()) {
                    $returnCol = $r['returned_at'] 
                        ? '<span style="font-size:12px; color:var(--text-muted); font-weight:600;"><i class="fa-solid fa-circle-check"></i> Returned ' . date('d-m-Y', strtotime($r['returned_at'])) . '</span>' 
                        : '<form method="post" action="?action=return_book" style="display:inline; margin:0;"><input type="hidden" name="id" value="' . $r['id'] . '"><input type="hidden" name="tab" value="lending">' . csrf_input() . '<button class="btn" type="submit" style="padding:6px 12px;"><i class="fa-solid fa-right-left"></i> Register Return</button></form>';
                    
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
                ?>
            </tbody>
        </table>
    </div>
</div>

<script class="dynamic-script">
(function() {
    function initLendingScript() {
        const lkInput = document.getElementById('lk_input');
        const ldBook = document.getElementById('ld_book');
        const looked = <?= $looked ? 'true' : 'false' ?>;
        
        if (looked && ldBook) {
            ldBook.focus();
            ldBook.select();
        } else if (lkInput) {
            lkInput.focus();
            lkInput.select();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLendingScript);
    } else {
        initLendingScript();
    }
})();
</script>

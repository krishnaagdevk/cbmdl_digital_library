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

$holdsQuery = $db->query("SELECT h.*, m.name as member_name, m.membership_id, p.title as book_title, p.book_code FROM hold_requests h JOIN members m ON m.id = h.member_id JOIN physical_books p ON p.id = h.physical_book_id WHERE h.status IN ('Active', 'Awaiting Collection') ORDER BY h.id ASC");
$holdsCount = $holdsQuery ? $holdsQuery->num_rows : 0;
?>

<div class="grid" style="grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 25px;">
    <div>
        <div class="card" style="margin-bottom:25px;">
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
                        <p style="margin:0; font-size:14px; font-weight:600; color:var(--navy-dark);">
                            Matched Account: <span style="color:var(--primary); font-weight:700;"><?= e($looked['name']) ?></span> &nbsp;|&nbsp; 
                            Code: <code><?= e($looked['membership_id']) ?></code> &nbsp;|&nbsp; 
                            Validity: <span class="badge badge-green"><?= date('d-m-Y', strtotime($looked['end_date'])) ?></span>
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
                    <label for="ld_member">Member Phone or ID Code *</label>
                    <input id="ld_member" name="member" value="<?= $looked ? e($looked['membership_id']) : '' ?>" placeholder="Member Mobile / Card ID" required>
                </div>
                <div>
                    <label for="ld_book">Book Code / Catalog ID *</label>
                    <input id="ld_book" name="book_code" placeholder="Target book code/barcode" required>
                </div>
                <div>
                    <label for="ld_due">Issue Return Due Date *</label>
                    <input id="ld_due" type="date" name="due_date" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
                </div>
                <div style="grid-column: span 2;">
                    <label for="ld_tx">Mandatory Collateral Receipt / Payment Transaction ID *</label>
                    <input id="ld_tx" name="transaction_id" placeholder="Transaction/Challan Reference (or Cash / Security collateral info)" required>
                </div>
                <div style="grid-column: span 2; display:flex; align-items:flex-end; margin-top: 10px;">
                    <button style="width:100%;"><i class="fa-solid fa-square-check"></i> Register Lending Issue</button>
                </div>
            </form>
        </div>
    </div>

    <div>
        <div class="card" style="height:100%; display:flex; flex-direction:column; justify-content:space-between; border: 1px solid var(--border-color);">
            <div>
                <h3 style="margin-top:0; display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-hourglass-half" style="color:var(--accent-orange);"></i> Hold Queue (<?= $holdsCount ?>)</h3>
                <p style="font-size:12px; color:var(--text-muted); margin-bottom:15px;">Smart reservation queue. When checked-out books are returned, they are marked here as awaiting collection.</p>
                
                <?php if ($holdsCount === 0): ?>
                    <div style="text-align:center; padding:30px 10px; color:var(--text-muted); border:1px dashed var(--border-color); border-radius:8px; background:var(--bg-slate);">
                        <i class="fa-solid fa-folder-open" style="font-size:24px; margin-bottom:8px; opacity:0.5;"></i>
                        <p style="font-size:12px; margin:0;">No active holds or book reservations currently pending.</p>
                    </div>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:12px; max-height: 380px; overflow-y:auto; padding-right:5px;">
                        <?php while ($h = $holdsQuery->fetch_assoc()): 
                            $status_color = $h['status'] === 'Awaiting Collection' ? '#10b981' : '#f59e0b';
                            $status_bg = $h['status'] === 'Awaiting Collection' ? '#ecfdf5' : '#fffbeb';
                            $status_border = $h['status'] === 'Awaiting Collection' ? '#a7f3d0' : '#fef3c7';
                        ?>
                            <div style="padding:12px; border-radius:8px; border:1px solid <?= $status_border ?>; background:<?= $status_bg ?>; font-size:12px; position:relative;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                    <strong style="color:var(--navy-dark); font-size:13px;"><?= e($h['book_title']) ?></strong>
                                    <span style="font-size:9px; padding:2px 6px; border-radius:4px; font-weight:700; background:<?= $status_color ?>; color:white;"><?= e($h['status']) ?></span>
                                </div>
                                <p style="margin:2px 0; color:var(--text-muted);"><strong>Code:</strong> <code><?= e($h['book_code']) ?></code></p>
                                <p style="margin:2px 0; color:var(--text-muted);"><strong>Reserved By:</strong> <?= e($h['member_name']) ?> (<code><?= e($h['membership_id']) ?></code>)</p>
                                <p style="margin:4px 0 0 0; font-size:10px; color:var(--text-muted); font-style:italic;">Requested on <?= date('d-m-Y', strtotime($h['created_at'])) ?></p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div style="margin-top:20px; font-size:11px; color:var(--text-muted); line-height:1.4; border-top:1px solid var(--border-color); padding-top:10px;">
                Hold requests are cleared automatically once the book is formally issued to the queued member.
            </div>
        </div>
    </div>
</div>

<div class="card">
    <h3><i class="fa-solid fa-clock-rotate-left"></i> Current Active Lending Index</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Borrower Member</th>
                    <th>Lent Timestamp</th>
                    <th>Due Date Target</th>
                    <th>Fine Status & Calculation</th>
                    <th>Operation</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $filterClause = $looked ? ' WHERE l.member_id = ' . (int)$looked['id'] : '';
                $x = $db->query('SELECT l.*, p.title, m.name FROM lendings l JOIN physical_books p ON p.id = l.physical_book_id JOIN members m ON m.id = l.member_id' . $filterClause . ' ORDER BY l.lent_at DESC');
                while($r = $x->fetch_assoc()) {
                    $fine_data = calculate_fine($r['due_date'], $r['returned_at']);
                    $fine_html = render_fine_column($r, $fine_data, 'lending');
                    
                    $returnCol = $r['returned_at'] 
                        ? '<span style="font-size:12px; color:var(--text-muted); font-weight:600;"><i class="fa-solid fa-circle-check"></i> Returned ' . date('d-m-Y', strtotime($r['returned_at'])) . '</span>' 
                        : '<form method="post" action="?action=return_book" style="display:inline; margin:0;"><input type="hidden" name="id" value="' . $r['id'] . '"><input type="hidden" name="tab" value="lending">' . csrf_input() . '<button class="btn" type="submit" style="padding:6px 12px;"><i class="fa-solid fa-right-left"></i> Register Return</button></form>';
                    
                    $row_style = '';
                    if (!$r['returned_at']) {
                        if ($fine_data['days'] > 0) {
                            $row_style = ' style="background-color: #fef2f2;"';
                        } elseif ($r['due_date'] === date('Y-m-d')) {
                            $row_style = ' style="background-color: #fffbeb;"';
                        }
                    }
                    
                    echo '
                    <tr' . $row_style . '>
                        <td>' . e($r['title']) . '</td>
                        <td>' . e($r['name']) . '</td>
                        <td>' . date('d-m-Y h:i A', strtotime($r['lent_at'])) . '</td>
                        <td>' . date('d-m-Y', strtotime($r['due_date'])) . '</td>
                        <td>' . $fine_html . '</td>
                        <td>' . $returnCol . '</td>
                    </tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>

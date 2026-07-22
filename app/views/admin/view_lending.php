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
    <h3><i class="fa-solid fa-timeline"></i> System Issue & Lending History Log</h3>
    
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
                    <th>Member Name</th>
                    <th>Date of Lending</th>
                    <th>Due Date</th>
                    <th>Overdue Fine status</th>
                    <th>Return Operations</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $x = $db->query("SELECT l.*, p.title, m.name FROM lendings l JOIN physical_books p ON p.id = l.physical_book_id JOIN members m ON m.id = l.member_id ORDER BY $orderBy");
                while($r = $x->fetch_assoc()) {
                    $fine_data = calculate_fine($r['due_date'], $r['returned_at']);
                    $fine_html = render_fine_column($r, $fine_data, 'view_lending');
                    
                    $returnCol = $r['returned_at'] 
                        ? '<span style="font-size:12px; color:var(--text-muted); font-weight:600;"><i class="fa-solid fa-box-archive"></i> Settled ' . date('d-m-Y', strtotime($r['returned_at'])) . '</span>' 
                        : '<form method="post" action="?action=return_book" style="display:inline; margin:0;"><input type="hidden" name="id" value="' . $r['id'] . '"><input type="hidden" name="tab" value="view_lending">' . csrf_input() . '<button class="btn" type="submit" style="padding:6px 12px;"><i class="fa-solid fa-rotate-left"></i> Tag Return</button></form>';
                    
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

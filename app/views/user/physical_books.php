<?php
// app/views/user/physical_books.php
if (!defined('BASE_URL')) exit;

$m_search = trim($_GET['p_search'] ?? '');
$p_limit = max(6, (int)($_GET['p_limit'] ?? 12));
$p_page = max(1, (int)($_GET['p_page'] ?? 1));
?>

<div class="card" style="margin-bottom:20px;">
    <h3 style="border:none; margin-bottom:15px; font-size:17px;"><i class="fa-solid fa-magnifying-glass" style="color:var(--primary);"></i> Explore Physical Book Inventory</h3>
    <form method="get" action="index.php" class="grid" style="grid-template-columns: 2fr 1fr 1fr; gap:15px; align-items:end;">
        <input type="hidden" name="action" value="user">
        <input type="hidden" name="tab" value="physical_books">
        
        <div>
            <label for="p_sc_term" style="font-size:12px; font-weight:600; margin-bottom:4px; display:block;">Search Title, Author, Publisher, or Book Code</label>
            <input id="p_sc_term" name="p_search" value="<?= e($m_search) ?>" placeholder="Type keywords..." style="padding:9px 12px; font-size:13px;">
        </div>

        <div>
            <label for="p_sc_limit" style="font-size:12px; font-weight:600; margin-bottom:4px; display:block;">Per Page</label>
            <select id="p_sc_limit" name="p_limit" style="padding:9px 12px; font-size:13px;" onchange="this.form.submit();">
                <option value="12" <?= $p_limit === 12 ? 'selected' : '' ?>>12 books</option>
                <option value="24" <?= $p_limit === 24 ? 'selected' : '' ?>>24 books</option>
                <option value="48" <?= $p_limit === 48 ? 'selected' : '' ?>>48 books</option>
                <option value="96" <?= $p_limit === 96 ? 'selected' : '' ?>>96 books</option>
            </select>
        </div>

        <div>
            <button style="width:100%; padding:10px 16px; font-size:13px; font-weight:600;"><i class="fa-solid fa-filter"></i> Search Catalog</button>
        </div>
    </form>
</div>

<?php
// Compute total count
if ($m_search !== '') {
    $stmtCnt = $db->prepare("SELECT COUNT(*) c FROM physical_books p WHERE p.title LIKE ? OR p.author LIKE ? OR p.publisher LIKE ? OR p.book_code LIKE ?");
    $like = "%" . $m_search . "%";
    $stmtCnt->bind_param("ssss", $like, $like, $like, $like);
    $stmtCnt->execute();
    $tot = (int)$stmtCnt->get_result()->fetch_assoc()['c'];
    $stmtCnt->close();
} else {
    $tot = (int)$db->query("SELECT COUNT(*) c FROM physical_books")->fetch_assoc()['c'];
}

$p_pages = max(1, (int)ceil($tot / $p_limit));
if ($p_page > $p_pages) $p_page = $p_pages;
$p_offset = ($p_page - 1) * $p_limit;

// Fetch page slice
if ($m_search !== '') {
    $stmt = $db->prepare("SELECT p.* FROM physical_books p WHERE p.title LIKE ? OR p.author LIKE ? OR p.publisher LIKE ? OR p.book_code LIKE ? ORDER BY p.title LIMIT ? OFFSET ?");
    $like = "%" . $m_search . "%";
    $stmt->bind_param("ssssii", $like, $like, $like, $like, $p_limit, $p_offset);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $stmt = $db->prepare("SELECT p.* FROM physical_books p ORDER BY p.title LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $p_limit, $p_offset);
    $stmt->execute();
    $res = $stmt->get_result();
}

$link_p_base = "?action=user&tab=physical_books&p_search=" . urlencode($m_search) . "&p_limit=" . $p_limit . "&p_page=";
?>

<!-- Catalog Summary Bar -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px; background:#fff; padding:12px 18px; border-radius:12px; border:1px solid var(--border-color); box-shadow:0 4px 12px rgba(0,0,0,0.02);">
    <div style="font-size:13px; color:var(--navy-dark); font-weight:600;">
        <i class="fa-solid fa-book" style="color:var(--primary); margin-right:6px;"></i>
        Showing <strong><?= $tot > 0 ? ($p_offset + 1) : 0 ?></strong> – <strong><?= min($p_offset + $p_limit, $tot) ?></strong> of <strong><?= number_format($tot) ?></strong> physical books
    </div>

    <?php if ($p_pages > 1): ?>
        <div style="display:flex; align-items:center; gap:8px;">
            <label for="jump_phy_top" style="font-size:12px; color:var(--text-muted); font-weight:600;">Jump to Page:</label>
            <select id="jump_phy_top" style="padding:4px 8px; font-size:12px; border-radius:6px;" onchange="window.location.href='<?= $link_p_base ?>' + this.value;">
                <?php for($p=1; $p<=$p_pages; $p++): ?>
                    <option value="<?= $p ?>" <?= $p === $p_page ? 'selected' : '' ?>>Page <?= $p ?> of <?= $p_pages ?></option>
                <?php endfor; ?>
            </select>
        </div>
    <?php endif; ?>
</div>

<!-- Catalog Grid -->
<div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:18px;">
    <?php
    if ($res->num_rows === 0) {
        echo '<div class="card" style="grid-column: 1 / -1; text-align:center; padding:50px 20px; color:var(--text-muted); border-radius:12px;"><i class="fa-solid fa-book-open" style="font-size:36px; margin-bottom:12px; color:var(--primary); opacity:0.5;"></i><p style="font-size:15px; font-weight:600; margin:0;">No physical books match your search or catalog is empty.</p></div>';
    } else {
        while ($b = $res->fetch_assoc()) {
            $lendStmt = $db->prepare("SELECT id FROM lendings WHERE physical_book_id = ? AND returned_at IS NULL LIMIT 1");
            $lendStmt->bind_param("i", $b['id']);
            $lendStmt->execute();
            $on_loan = $lendStmt->get_result()->fetch_assoc();
            $lendStmt->close();

            $status_badge = $on_loan 
                ? '<span class="badge badge-red" style="font-size:10px; padding:3px 8px;"><i class="fa-solid fa-circle-minus"></i> On Lend</span>'
                : '<span class="badge badge-green" style="font-size:10px; padding:3px 8px;"><i class="fa-solid fa-circle-check"></i> Available</span>';
            ?>
            <div class="card physical-book-card" style="display:flex; flex-direction:column; justify-content:space-between; height:100%; padding:18px; transition:all 0.2s ease; border-radius:12px; border:1px solid var(--border-color); background:var(--card-bg);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <div>
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                        <span style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;"><i class="fa-solid fa-hashtag"></i> <?= e($b['book_code']) ?></span>
                        <?= $status_badge ?>
                    </div>
                    <h4 style="font-size:15px; margin:0 0 8px 0; font-weight:700; color:var(--navy-dark); line-height:1.3;"><?= e($b['title']) ?></h4>
                    <p style="font-size:12px; margin:0 0 5px 0; color:var(--text-muted);"><i class="fa-solid fa-layer-group"></i> <strong>Shelf:</strong> <?= e($b['shelf_number'] ?: '—') ?></p>
                    <p style="font-size:12px; margin:0 0 5px 0; color:var(--text-muted);"><i class="fa-solid fa-user-tie"></i> <strong>Author:</strong> <?= e($b['author']) ?></p>
                    <p style="font-size:12px; margin:0 0 5px 0; color:var(--text-muted);"><i class="fa-solid fa-warehouse"></i> <strong>Publisher:</strong> <?= e($b['publisher']) ?></p>
                    <p style="font-size:12px; margin:0 0 5px 0; color:var(--text-muted);"><i class="fa-solid fa-receipt"></i> <strong>Cost:</strong> ₹<?= number_style_format($b['price']) ?></p>
                </div>
                <div style="margin-top:12px; padding-top:10px; border-top:1px solid var(--border-color); font-size:12px; text-align:center;">
                    <?php if ($on_loan): ?>
                        <span style="color:var(--accent-red); font-weight:600;"><i class="fa-solid fa-circle-minus"></i> Issued to Member</span>
                    <?php else: ?>
                        <span style="color:var(--accent-green); font-weight:600;"><i class="fa-solid fa-circle-check"></i> Available at Library Desk</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    }
    $stmt->close();
    ?>
</div>

<!-- Smart Pagination Bar -->
<?php if ($p_pages > 1): ?>
    <div class="pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:25px; flex-wrap:wrap; gap:15px; background:#fff; padding:16px 20px; border-radius:12px; border:1px solid var(--border-color); box-shadow:0 4px 12px rgba(0,0,0,0.02);">
        
        <div style="font-size:13px; color:var(--text-muted);">
            Page <strong><?= $p_page ?></strong> of <strong><?= $p_pages ?></strong> (<?= number_format($tot) ?> total books)
        </div>

        <div class="pagination" style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
            <?php if ($p_page > 1): ?>
                <a href="<?= $link_p_base ?>1" class="btn" style="padding:7px 11px; background:var(--bg-slate); color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none;" title="First Page"><i class="fa-solid fa-angles-left"></i></a>
                <a href="<?= $link_p_base ?><?= $p_page - 1 ?>" class="btn" style="padding:7px 11px; background:var(--bg-slate); color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center; gap:4px;" title="Previous Page"><i class="fa-solid fa-angle-left"></i> Prev</a>
            <?php else: ?>
                <span class="btn disabled" style="padding:7px 11px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; border-radius:8px; opacity:0.5; cursor:not-allowed;"><i class="fa-solid fa-angles-left"></i></span>
                <span class="btn disabled" style="padding:7px 11px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; border-radius:8px; opacity:0.5; cursor:not-allowed;"><i class="fa-solid fa-angle-left"></i> Prev</span>
            <?php endif; ?>

            <?php 
            $start_p = max(1, $p_page - 2);
            $end_p = min($p_pages, $p_page + 2);

            if ($start_p > 1) {
                echo '<a href="' . $link_p_base . '1" class="btn" style="padding:7px 12px; background:var(--bg-slate); color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none;">1</a>';
                if ($start_p > 2) {
                    echo '<span style="padding:0 4px; color:var(--text-muted); font-weight:700;">...</span>';
                }
            }

            for($i = $start_p; $i <= $end_p; $i++): 
            ?>
                <?php if ($i == $p_page): ?>
                    <span class="btn" style="padding:7px 13px; background:var(--primary); color:white; font-size:12px; font-weight:700; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.15);"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= $link_p_base ?><?= $i ?>" class="btn" style="padding:7px 13px; background:var(--bg-slate); color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none;"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php
            if ($end_p < $p_pages) {
                if ($end_p < $p_pages - 1) {
                    echo '<span style="padding:0 4px; color:var(--text-muted); font-weight:700;">...</span>';
                }
                echo '<a href="' . $link_p_base . $p_pages . '" class="btn" style="padding:7px 12px; background:var(--bg-slate); color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none;">' . $p_pages . '</a>';
            }
            ?>

            <?php if ($p_page < $p_pages): ?>
                <a href="<?= $link_p_base ?><?= $p_page + 1 ?>" class="btn" style="padding:7px 11px; background:var(--bg-slate); color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center; gap:4px;" title="Next Page">Next <i class="fa-solid fa-angle-right"></i></a>
                <a href="<?= $link_p_base ?><?= $p_pages ?>" class="btn" style="padding:7px 11px; background:var(--bg-slate); color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none;" title="Last Page"><i class="fa-solid fa-angles-right"></i></a>
            <?php else: ?>
                <span class="btn disabled" style="padding:7px 11px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; border-radius:8px; opacity:0.5; cursor:not-allowed;">Next <i class="fa-solid fa-angle-right"></i></span>
                <span class="btn disabled" style="padding:7px 11px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; border-radius:8px; opacity:0.5; cursor:not-allowed;"><i class="fa-solid fa-angles-right"></i></span>
            <?php endif; ?>
        </div>

        <div style="display:flex; align-items:center; gap:6px;">
            <label for="jump_phy_bottom" style="font-size:12px; color:var(--text-muted); font-weight:600;">Go to:</label>
            <select id="jump_phy_bottom" style="padding:6px 10px; font-size:12px; border-radius:8px; border:1px solid var(--border-color);" onchange="window.location.href='<?= $link_p_base ?>' + this.value;">
                <?php for($p=1; $p<=$p_pages; $p++): ?>
                    <option value="<?= $p ?>" <?= $p === $p_page ? 'selected' : '' ?>>Page <?= $p ?></option>
                <?php endfor; ?>
            </select>
        </div>

    </div>
<?php endif; ?>

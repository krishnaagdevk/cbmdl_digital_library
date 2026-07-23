<?php
// views/user/books.php
if (!defined('BASE_URL')) exit;

// Get per-page limit preference (default 12)
$limit_param = $_GET['limit'] ?? '12';
$e_limit = ($limit_param === 'all') ? 10000 : max(6, (int)$limit_param);
$e_page = max(1, (int)($_GET['e_page'] ?? 1));
?>

<!-- Search & Filter Controls -->
<div class="card" style="margin-bottom:20px;">
    <h3 style="border:none; margin-bottom:15px; font-size:17px;"><i class="fa-solid fa-magnifying-glass" style="color:var(--primary);"></i> Search & Filter e-Library</h3>
    <form method="get" action="index.php" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:15px; align-items:end;">
        <input type="hidden" name="action" value="user">
        <input type="hidden" name="tab" value="books">
        
        <div>
            <label for="m_sc_term" style="font-size:12px; font-weight:600; margin-bottom:4px; display:block;">Search Keyword</label>
            <input id="m_sc_term" name="search" value="<?= e($_GET['search'] ?? '') ?>" placeholder="Type title or keywords..." style="padding:9px 12px; font-size:13px;">
        </div>
        
        <div>
            <label for="m_sc_cat" style="font-size:12px; font-weight:600; margin-bottom:4px; display:block;">Catalog Category</label>
            <select id="m_sc_cat" name="cat" style="padding:9px 12px; font-size:13px;">
                <option value="">Show All Categories</option>
                <?php 
                $cats = $db->query('SELECT * FROM categories ORDER BY name');
                while($r = $cats->fetch_assoc()) {
                    echo '<option value="' . $r['id'] . '" ' . ($cat == $r['id'] ? 'selected' : '') . '>' . e($r['name']) . '</option>';
                }
                ?>
            </select>
        </div>
        
        <div>
            <label for="m_sc_sort" style="font-size:12px; font-weight:600; margin-bottom:4px; display:block;">Sort By</label>
            <select id="m_sc_sort" name="sort" style="padding:9px 12px; font-size:13px;">
                <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>Title (A-Z)</option>
                <option value="title_desc" <?= $sort === 'title_desc' ? 'selected' : '' ?>>Title (Z-A)</option>
                <option value="category_asc" <?= $sort === 'category_asc' ? 'selected' : '' ?>>Category Name (A-Z)</option>
                <option value="category_desc" <?= $sort === 'category_desc' ? 'selected' : '' ?>>Category Name (Z-A)</option>
                <option value="id_desc" <?= $sort === 'id_desc' ? 'selected' : '' ?>>Date Uploaded (Newest)</option>
                <option value="id_asc" <?= $sort === 'id_asc' ? 'selected' : '' ?>>Date Uploaded (Oldest)</option>
            </select>
        </div>

        <div>
            <label for="m_sc_limit" style="font-size:12px; font-weight:600; margin-bottom:4px; display:block;">Per Page</label>
            <select id="m_sc_limit" name="limit" style="padding:9px 12px; font-size:13px;" onchange="this.form.submit();">
                <option value="12" <?= $limit_param === '12' ? 'selected' : '' ?>>12 e-books</option>
                <option value="24" <?= $limit_param === '24' ? 'selected' : '' ?>>24 e-books</option>
                <option value="48" <?= $limit_param === '48' ? 'selected' : '' ?>>48 e-books</option>
                <option value="96" <?= $limit_param === '96' ? 'selected' : '' ?>>96 e-books</option>
                <option value="all" <?= $limit_param === 'all' ? 'selected' : '' ?>>Show All</option>
            </select>
        </div>

        <div>
            <button style="width:100%; padding:10px 16px; font-size:13px; font-weight:600;"><i class="fa-solid fa-filter"></i> Apply Filters</button>
        </div>
    </form>
</div>

<?php 
// Pre-fetch reading & print requests for active member
$all_reading_reqs = [];
$reqQuery = $db->prepare("SELECT r.ebook_id, r.id, r.status, r.duration_minutes, r.started_reading_at, r.expires_at FROM reading_requests r WHERE r.member_id = ? ORDER BY r.id ASC");
$reqQuery->bind_param("i", $mid);
$reqQuery->execute();
$res = $reqQuery->get_result();
while ($row = $res->fetch_assoc()) {
    $all_reading_reqs[$row['ebook_id']] = $row;
}
$reqQuery->close();

$all_print_reqs = [];
$prtQuery = $db->prepare("SELECT p.ebook_id, p.id, p.pages FROM print_requests p WHERE p.member_id = ? AND p.status = 'Pending'");
$prtQuery->bind_param("i", $mid);
$prtQuery->execute();
$res = $prtQuery->get_result();
while ($row = $res->fetch_assoc()) {
    $all_print_reqs[$row['ebook_id']] = $row;
}
$prtQuery->close();

// Compute total catalog count
if (empty($params)) {
    $cntRes = $db->query("SELECT COUNT(*) c FROM ebooks e JOIN categories c ON c.id = e.category_id");
} else {
    $stmtCnt = $db->prepare("SELECT COUNT(*) c FROM ebooks e JOIN categories c ON c.id = e.category_id $whereStr");
    $stmtCnt->bind_param($types, ...$params);
    $stmtCnt->execute();
    $cntRes = $stmtCnt->get_result();
    $stmtCnt->close();
}
$total_ebooks = (int)($cntRes ? $cntRes->fetch_assoc()['c'] : 0);
$total_pages = max(1, (int)ceil($total_ebooks / $e_limit));
if ($e_page > $total_pages) $e_page = $total_pages;
$e_offset = ($e_page - 1) * $e_limit;

// Fetch e-books slice
if (empty($params)) {
    $stmt = $db->prepare("SELECT e.*, c.name category FROM ebooks e JOIN categories c ON c.id = e.category_id ORDER BY $orderBy LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $e_limit, $e_offset);
} else {
    $stmt = $db->prepare("SELECT e.*, c.name category FROM ebooks e JOIN categories c ON c.id = e.category_id $whereStr ORDER BY $orderBy LIMIT ? OFFSET ?");
    $types_limit = $types . "ii";
    $bind_params = array_merge($params, [$e_limit, $e_offset]);
    $stmt->bind_param($types_limit, ...$bind_params);
}
$stmt->execute();
$x = $stmt->get_result();

$p_search = $_GET['search'] ?? '';
$p_cat = (int)($_GET['cat'] ?? 0);
$p_sort = $_GET['sort'] ?? '';
$link_base = "?action=user&tab=books&search=" . urlencode($p_search) . "&cat=" . $p_cat . "&sort=" . urlencode($p_sort) . "&limit=" . urlencode($limit_param) . "&e_page=";
?>

<!-- Catalog Summary Bar & Top Navigation -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px; background:#fff; padding:12px 18px; border-radius:12px; border:1px solid var(--border-color); box-shadow:0 4px 12px rgba(0,0,0,0.02);">
    <div style="font-size:13px; color:var(--navy-dark); font-weight:600;">
        <i class="fa-solid fa-book-open" style="color:var(--primary); margin-right:6px;"></i>
        Showing <strong><?= $total_ebooks > 0 ? ($e_offset + 1) : 0 ?></strong> – <strong><?= min($e_offset + $e_limit, $total_ebooks) ?></strong> of <strong><?= number_format($total_ebooks) ?></strong> e-books
    </div>

    <?php if ($total_pages > 1): ?>
        <div style="display:flex; align-items:center; gap:8px;">
            <label for="jump_top_page" style="font-size:12px; color:var(--text-muted); font-weight:600;">Jump to Page:</label>
            <select id="jump_top_page" style="padding:4px 8px; font-size:12px; border-radius:6px;" onchange="window.location.href='<?= $link_base ?>' + this.value;">
                <?php for($p=1; $p<=$total_pages; $p++): ?>
                    <option value="<?= $p ?>" <?= $p === $e_page ? 'selected' : '' ?>>Page <?= $p ?> of <?= $total_pages ?></option>
                <?php endfor; ?>
            </select>
        </div>
    <?php endif; ?>
</div>

<!-- Card Grid for E-books -->
<div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:18px;">
    <?php 
    $ebookCount = 0;
    while($r = $x->fetch_assoc()) {
        $ebookCount++;
        $req = $all_reading_reqs[$r['id']] ?? null;
        $has_pending_print = $all_print_reqs[$r['id']] ?? null;
        
        $cardAction = '';
        if ($req) {
            $isStarted = !empty($req['started_reading_at']);
            $isExpired = ($req['status'] === 'Expired') || (!empty($req['expires_at']) && strtotime($req['expires_at']) <= time());
            if ($req['status'] === 'Approved' && !$isExpired) {
                $btnText = $isStarted ? 'Continue Reading' : 'Read Now (Granted)';
                $expUnix = ($isStarted && !empty($req['expires_at'])) ? strtotime($req['expires_at']) : 0;
                $cardAction = '<button class="btn" style="width:100%; background:var(--accent-green);" onclick="openPdfModal(' . $req['id'] . ', ' . $expUnix . ', \'' . addslashes(e($r['title'])) . '\')"><i class="fa-solid fa-book-open"></i> ' . $btnText . '</button>';
            } elseif ($req['status'] === 'Pending') {
                $cardAction = '<button class="btn" style="width:100%; background:var(--text-muted); cursor:not-allowed;" disabled><i class="fa-solid fa-clock"></i> Request Sent (Pending)</button>';
            } else {
                $cardAction = '<a class="btn" style="width:100%;" href="?action=request_read&id=' . $r['id'] . '" onclick="this.style.pointerEvents=\'none\'; this.style.opacity=\'0.6\'; this.innerHTML=\'<i class=\\\'fa-solid fa-spinner fa-spin\\\'></i> Sending Request...\';"><i class="fa-solid fa-key"></i> Request e-Reading Permission</a>';
            }
        } else {
            $cardAction = '<a class="btn" style="width:100%;" href="?action=request_read&id=' . $r['id'] . '" onclick="this.style.pointerEvents=\'none\'; this.style.opacity=\'0.6\'; this.innerHTML=\'<i class=\\\'fa-solid fa-spinner fa-spin\\\'></i> Sending Request...\';"><i class="fa-solid fa-key"></i> Request e-Reading Permission</a>';
        }

        $printFormHtml = '
        <form method="post" action="?action=request_print" style="margin:0; border-top:1px solid var(--border-color); padding-top:10px;" onsubmit="const btn = this.querySelector(\'button\'); setTimeout(() => { btn.disabled = true; btn.innerHTML = \'<i class=\\\'fa-solid fa-spinner fa-spin\\\'></i> Sending...\'; }, 10);">
            ' . csrf_input() . '
            <input type="hidden" name="ebook_id" value="' . $r['id'] . '">
            <label style="font-size:11px; margin-bottom:4px; font-weight:600; color:var(--text-muted);" for="pages_' . $r['id'] . '">Pages for print (e.g. 1-10)</label>
            <div style="display:flex; gap:6px;">';
        if ($has_pending_print) {
            $printFormHtml .= '
                <input id="pages_' . $r['id'] . '" name="pages" value="' . e($has_pending_print['pages']) . '" disabled style="margin:0; padding:6px 10px; font-size:12px; background:var(--bg-slate); cursor:not-allowed;">
                <button disabled style="font-size:12px; padding:6px 10px; white-space:nowrap; background:var(--text-muted); cursor:not-allowed;"><i class="fa-solid fa-clock"></i> Print Pending</button>';
        } else {
            $printFormHtml .= '
                <input id="pages_' . $r['id'] . '" name="pages" placeholder="e.g. 1-5" required style="margin:0; padding:6px 10px; font-size:12px;">
                <button style="font-size:12px; padding:6px 10px; white-space:nowrap;"><i class="fa-solid fa-print"></i> Print</button>';
        }
        $printFormHtml .= '
            </div>
        </form>';
        
        echo '
        <div class="card" style="display:flex; flex-direction:column; justify-content:space-between; height:100%; border-radius:12px; border:1px solid var(--border-color);" data-ebook-id="' . $r['id'] . '">
            <div>
                <div style="margin-bottom:8px;">
                    <span class="badge badge-blue" style="font-size:10px; padding:3px 8px; text-transform:uppercase; letter-spacing:0.5px;">' . e($r['category']) . '</span>
                </div>
                <h3 style="border:none; margin-bottom:8px; font-size:16px; font-weight:700; line-height:1.3; color:var(--navy-dark);">' . e($r['title']) . '</h3>
                ' . (!empty($r['keywords']) ? '<p class="muted" style="margin:0 0 12px 0; font-size:12px;"><i class="fa-solid fa-tags" style="opacity:0.6;"></i> ' . e($r['keywords']) . '</p>' : '') . '
            </div>
            
            <div style="display:flex; flex-direction:column; gap:8px; margin-top:12px;">
                <div id="action-btn-container-' . $r['id'] . '">
                    ' . $cardAction . '
                </div>
                ' . $printFormHtml . '
            </div>
        </div>';
    }
    $stmt->close();

    if ($ebookCount === 0) {
        echo '<div class="card" style="grid-column: 1 / -1; text-align:center; padding:50px 20px; color:var(--text-muted); border-radius:12px;"><i class="fa-solid fa-book-open" style="font-size:36px; margin-bottom:12px; color:var(--primary); opacity:0.5;"></i><p style="font-size:15px; font-weight:600; margin:0;">No e-books found matching your search criteria.</p></div>';
    }
    ?>
</div>

<!-- Premium Smart Pagination Bar -->
<?php if ($total_pages > 1): ?>
    <div class="pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:25px; flex-wrap:wrap; gap:15px; background:#fff; padding:16px 20px; border-radius:12px; border:1px solid var(--border-color); box-shadow:0 4px 12px rgba(0,0,0,0.02);">
        
        <!-- Summary Text -->
        <div style="font-size:13px; color:var(--text-muted);">
            Page <strong><?= $e_page ?></strong> of <strong><?= $total_pages ?></strong> (<?= number_format($total_ebooks) ?> total items)
        </div>

        <!-- Pagination Controls -->
        <div class="pagination" style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
            
            <!-- First Page Button -->
            <?php if ($e_page > 1): ?>
                <a href="<?= $link_base ?>1" class="btn" style="padding:7px 11px; background:var(--bg-slate); color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none;" title="First Page"><i class="fa-solid fa-angles-left"></i></a>
                <a href="<?= $link_base ?><?= $e_page - 1 ?>" class="btn" style="padding:7px 11px; background:var(--bg-slate); color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center; gap:4px;" title="Previous Page"><i class="fa-solid fa-angle-left"></i> Prev</a>
            <?php else: ?>
                <span class="btn disabled" style="padding:7px 11px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; border-radius:8px; opacity:0.5; cursor:not-allowed;"><i class="fa-solid fa-angles-left"></i></span>
                <span class="btn disabled" style="padding:7px 11px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; border-radius:8px; opacity:0.5; cursor:not-allowed;"><i class="fa-solid fa-angle-left"></i> Prev</span>
            <?php endif; ?>

            <!-- Smart Window Page Numbers -->
            <?php 
            $start_p = max(1, $e_page - 2);
            $end_p = min($total_pages, $e_page + 2);

            // Always show Page 1
            if ($start_p > 1) {
                echo '<a href="' . $link_base . '1" class="btn" style="padding:7px 12px; background:var(--bg-slate); color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none;">1</a>';
                if ($start_p > 2) {
                    echo '<span style="padding:0 4px; color:var(--text-muted); font-weight:700;">...</span>';
                }
            }

            for($i = $start_p; $i <= $end_p; $i++): 
            ?>
                <?php if ($i == $e_page): ?>
                    <span class="btn" style="padding:7px 13px; background:var(--primary); color:white; font-size:12px; font-weight:700; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.15);"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= $link_base ?><?= $i ?>" class="btn" style="padding:7px 13px; background:var(--bg-slate); color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none;"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php
            // Always show Last Page
            if ($end_p < $total_pages) {
                if ($end_p < $total_pages - 1) {
                    echo '<span style="padding:0 4px; color:var(--text-muted); font-weight:700;">...</span>';
                }
                echo '<a href="' . $link_base . $total_pages . '" class="btn" style="padding:7px 12px; background:var(--bg-slate); color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none;">' . $total_pages . '</a>';
            }
            ?>

            <!-- Next & Last Page Buttons -->
            <?php if ($e_page < $total_pages): ?>
                <a href="<?= $link_base ?><?= $e_page + 1 ?>" class="btn" style="padding:7px 11px; background:var(--bg-slate); color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center; gap:4px;" title="Next Page">Next <i class="fa-solid fa-angle-right"></i></a>
                <a href="<?= $link_base ?><?= $total_pages ?>" class="btn" style="padding:7px 11px; background:var(--bg-slate); color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none;" title="Last Page"><i class="fa-solid fa-angles-right"></i></a>
            <?php else: ?>
                <span class="btn disabled" style="padding:7px 11px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; border-radius:8px; opacity:0.5; cursor:not-allowed;">Next <i class="fa-solid fa-angle-right"></i></span>
                <span class="btn disabled" style="padding:7px 11px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; border-radius:8px; opacity:0.5; cursor:not-allowed;"><i class="fa-solid fa-angles-right"></i></span>
            <?php endif; ?>

        </div>

        <!-- Direct Jump Page Selector -->
        <div style="display:flex; align-items:center; gap:6px;">
            <label for="jump_bottom_page" style="font-size:12px; color:var(--text-muted); font-weight:600;">Go to:</label>
            <select id="jump_bottom_page" style="padding:6px 10px; font-size:12px; border-radius:8px; border:1px solid var(--border-color);" onchange="window.location.href='<?= $link_base ?>' + this.value;">
                <?php for($p=1; $p<=$total_pages; $p++): ?>
                    <option value="<?= $p ?>" <?= $p === $e_page ? 'selected' : '' ?>>Page <?= $p ?></option>
                <?php endfor; ?>
            </select>
        </div>

    </div>
<?php endif; ?>

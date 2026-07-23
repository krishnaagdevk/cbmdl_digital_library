<?php
// views/user/books.php
if (!defined('BASE_URL')) exit;
?>
<div class="card">
    <h3><i class="fa-solid fa-magnifying-glass"></i> Search e-book</h3>
    <form method="get" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <input type="hidden" name="action" value="user">
        <input type="hidden" name="tab" value="books">
        <div>
            <label for="m_sc_term">Search Keyword</label>
            <input id="m_sc_term" name="search" value="<?= e($_GET['search'] ?? '') ?>" placeholder="Type book title keywords...">
        </div>
        <div>
            <label for="m_sc_cat">Catalog category</label>
            <select id="m_sc_cat" name="cat">
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
            <label for="m_sc_sort">Sort By</label>
            <select id="m_sc_sort" name="sort">
                <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>Title (A-Z)</option>
                <option value="title_desc" <?= $sort === 'title_desc' ? 'selected' : '' ?>>Title (Z-A)</option>
                <option value="category_asc" <?= $sort === 'category_asc' ? 'selected' : '' ?>>Category Name (A-Z)</option>
                <option value="category_desc" <?= $sort === 'category_desc' ? 'selected' : '' ?>>Category Name (Z-A)</option>
                <option value="id_desc" <?= $sort === 'id_desc' ? 'selected' : '' ?>>Date Uploaded (Newest First)</option>
                <option value="id_asc" <?= $sort === 'id_asc' ? 'selected' : '' ?>>Date Uploaded (Oldest First)</option>
            </select>
        </div>
        <div>
            <label style="visibility:hidden; margin-bottom:6px;">Filter Action</label>
            <button style="width:100%; margin:6px 0 18px 0; padding:12px 24px; box-sizing:border-box;"><i class="fa-solid fa-filter"></i> Search Catalog</button>
        </div>
    </form>
</div>

<!-- Card Grid for E-books -->
<div class="grid">
    <?php 
    // Pre-fetch latest reading request per ebook for the active member to avoid N+1 queries
    $all_reading_reqs = [];
    $reqQuery = $db->prepare("SELECT r.ebook_id, r.id, r.status, r.duration_minutes, r.started_reading_at, r.expires_at FROM reading_requests r WHERE r.member_id = ? ORDER BY r.id ASC");
    $reqQuery->bind_param("i", $mid);
    $reqQuery->execute();
    $res = $reqQuery->get_result();
    while ($row = $res->fetch_assoc()) {
        $all_reading_reqs[$row['ebook_id']] = $row;
    }
    $reqQuery->close();

    // Pre-fetch all pending print requests for the active member
    $all_print_reqs = [];
    $prtQuery = $db->prepare("SELECT p.ebook_id, p.id, p.pages FROM print_requests p WHERE p.member_id = ? AND p.status = 'Pending'");
    $prtQuery->bind_param("i", $mid);
    $prtQuery->execute();
    $res = $prtQuery->get_result();
    while ($row = $res->fetch_assoc()) {
        $all_print_reqs[$row['ebook_id']] = $row;
    }
    $prtQuery->close();

    // Pagination settings for e-library catalog
    $e_limit = 10;
    $e_page = max(1, (int)($_GET['e_page'] ?? 1));

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
    $total_pages = ceil($total_ebooks / $e_limit);
    $e_offset = ($e_page - 1) * $e_limit;

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
                $cardAction = '<button class="btn" style="width:100%; background:var(--text-muted); cursor:not-allowed;" disabled><i class="fa-solid fa-clock"></i> Request Sent (Pending Approval)</button>';
            } else {
                // Rejected or expired approved request: allow them to request again
                $cardAction = '<a class="btn" style="width:100%;" href="?action=request_read&id=' . $r['id'] . '" onclick="this.style.pointerEvents=\'none\'; this.style.opacity=\'0.6\'; this.innerHTML=\'<i class=\\\'fa-solid fa-spinner fa-spin\\\'></i> Sending Request...\';"><i class="fa-solid fa-key"></i> Request e-Reading Permission</a>';
            }
        } else {
            // No request has been made yet
            $cardAction = '<a class="btn" style="width:100%;" href="?action=request_read&id=' . $r['id'] . '" onclick="this.style.pointerEvents=\'none\'; this.style.opacity=\'0.6\'; this.innerHTML=\'<i class=\\\'fa-solid fa-spinner fa-spin\\\'></i> Sending Request...\';"><i class="fa-solid fa-key"></i> Request e-Reading Permission</a>';
        }

        $printFormHtml = '
        <form method="post" action="?action=request_print" style="margin:0; border-top:1px solid var(--border-color); padding-top:12px;" onsubmit="const btn = this.querySelector(\'button\'); setTimeout(() => { btn.disabled = true; btn.innerHTML = \'<i class=\\\'fa-solid fa-spinner fa-spin\\\'></i> Sending...\'; }, 10);">
            ' . csrf_input() . '
            <input type="hidden" name="ebook_id" value="' . $r['id'] . '">
            <label style="font-size:12px; margin-bottom:4px;" for="pages_' . $r['id'] . '">Pages for print (e.g. 1-10)</label>
            <div style="display:flex; gap:6px;">';
        if ($has_pending_print) {
            $printFormHtml .= '
                <input id="pages_' . $r['id'] . '" name="pages" value="' . e($has_pending_print['pages']) . '" disabled style="margin:0; padding:6px 10px; font-size:12px; background:var(--bg-slate); cursor:not-allowed;">
                <button disabled style="font-size:12px; padding:6px 12px; white-space:nowrap; background:var(--text-muted); cursor:not-allowed;"><i class="fa-solid fa-clock"></i> Print Requested (Pending)</button>';
        } else {
            $printFormHtml .= '
                <input id="pages_' . $r['id'] . '" name="pages" placeholder="e.g. 1-5" required style="margin:0; padding:6px 10px; font-size:12px;">
                <button style="font-size:12px; padding:6px 12px; white-space:nowrap;"><i class="fa-solid fa-print"></i> Request Print</button>';
        }
        $printFormHtml .= '
            </div>
        </form>';
        
        echo '
        <div class="card" style="display:flex; flex-direction:column; justify-content:space-between;" data-ebook-id="' . $r['id'] . '">
            <div>
                <h3 style="border:none; margin-bottom:8px; font-size:18px;">' . e($r['title']) . '</h3>
                <p class="muted" style="margin:0 0 15px 0;"><span class="badge badge-blue">' . e($r['category']) . '</span> &nbsp; ' . e($r['keywords']) . '</p>
            </div>
            
            <div style="display:flex; flex-direction:column; gap:10px;">
                <div id="action-btn-container-' . $r['id'] . '">
                    ' . $cardAction . '
                </div>
                ' . $printFormHtml . '
            </div>
        </div>';
    }
    $stmt->close();

    if ($ebookCount === 0) {
        echo '<div class="card" style="grid-column: 1 / -1; text-align:center; padding:40px; color:var(--text-muted);"><i class="fa-solid fa-book-open" style="font-size:32px; margin-bottom:10px;"></i><p>No e-books match your search criteria.</p></div>';
    }
    ?>
</div>

<!-- Premium Pagination Component -->
<?php if ($total_pages > 1): ?>
    <?php
    $p_search = $_GET['search'] ?? '';
    $p_cat = (int)($_GET['cat'] ?? 0);
    $p_sort = $_GET['sort'] ?? '';
    $link_base = "?action=user&tab=books&search=" . urlencode($p_search) . "&cat=" . $p_cat . "&sort=" . urlencode($p_sort) . "&e_page=";
    ?>
    <div class="pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:25px; flex-wrap:wrap; gap:15px; border-top:1px solid var(--border-color); padding-top:15px;">
        <div style="font-size:13px; color:var(--text-muted);">
            Showing <strong><?= $e_offset + 1 ?></strong> to <strong><?= min($e_offset + $e_limit, $total_ebooks) ?></strong> of <strong><?= $total_ebooks ?></strong> e-books
        </div>
        <div class="pagination" style="display:flex; align-items:center; gap:6px;">
            <?php if ($e_page > 1): ?>
                <a href="<?= $link_base ?>1" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="First Page"><i class="fa-solid fa-angles-left"></i></a>
                <a href="<?= $link_base ?><?= $e_page - 1 ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Previous Page"><i class="fa-solid fa-angle-left"></i> Prev</a>
            <?php else: ?>
                <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-left"></i></span>
                <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angle-left"></i> Prev</span>
            <?php endif; ?>

            <?php 
            $start_p = max(1, $e_page - 2);
            $end_p = min($total_pages, $e_page + 2);
            for($i = $start_p; $i <= $end_p; $i++): 
            ?>
                <?php if ($i == $e_page): ?>
                    <span class="btn" style="padding:6px 12px; background:var(--primary); color:white; font-size:12px; font-weight:700; border-radius:6px;"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= $link_base ?><?= $i ?>" class="btn" style="padding:6px 12px; background:var(--bg-slate); color:var(--text-color); font-size:12px; border-radius:6px;"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($e_page < $total_pages): ?>
                <a href="<?= $link_base ?><?= $e_page + 1 ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Next Page">Next <i class="fa-solid fa-angle-right"></i></a>
                <a href="<?= $link_base ?><?= $total_pages ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="Last Page"><i class="fa-solid fa-angles-right"></i></a>
            <?php else: ?>
                <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;">Next <i class="fa-solid fa-angle-right"></i></span>
                <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-right"></i></span>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

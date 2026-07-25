<?php
// views/user/books.php
if (!defined('BASE_URL')) exit;

// Get per-page limit preference (default 12)
$limit_param = $_GET['limit'] ?? '12';
$e_limit = ($limit_param === 'all') ? 10000 : max(6, (int)$limit_param);
$e_page = max(1, (int)($_GET['e_page'] ?? 1));

// Color gradient presets for dynamic 3D ebook covers (Grid Mode)
$coverGradients = [
    'linear-gradient(135deg, #1e3a8a, #3b82f6)', // Deep Sapphire
    'linear-gradient(135deg, #065f46, #10b981)', // Emerald Forest
    'linear-gradient(135deg, #581c87, #8b5cf6)', // Royal Amethyst
    'linear-gradient(135deg, #8c2b0e, #f97316)', // Sunset Amber
    'linear-gradient(135deg, #831843, #ec4899)', // Crimson Rose
    'linear-gradient(135deg, #0f766e, #14b8a6)', // Ocean Teal
    'linear-gradient(135deg, #1e293b, #475569)', // Slate Graphite
];
?>

<style>
/* High-Density Space-Saving List View Transformations */
#catalogContainer.catalog-list {
    display: flex !important;
    flex-direction: column !important;
    gap: 10px !important;
}

#catalogContainer.catalog-list .ebook-card-item {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding: 10px 18px !important;
    border-radius: 12px !important;
    gap: 16px !important;
    box-shadow: 0 2px 8px rgba(15,23,42,0.02) !important;
    min-height: 56px !important;
}

/* Hide large banner header in list view to save space */
#catalogContainer.catalog-list .ebook-cover-header {
    display: none !important;
}

#catalogContainer.catalog-list .ebook-content-body {
    padding: 0 !important;
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    flex: 1 !important;
    flex-wrap: wrap !important;
}

#catalogContainer.catalog-list .ebook-cat-badge {
    margin: 0 !important;
    flex-shrink: 0 !important;
}

#catalogContainer.catalog-list .ebook-title-heading {
    min-height: auto !important;
    margin: 0 !important;
    font-size: 14.5px !important;
    font-weight: 700 !important;
    color: var(--navy-dark) !important;
    display: inline-block !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    max-width: 380px !important;
}

#catalogContainer.catalog-list .ebook-tags-wrap {
    margin: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    flex-shrink: 0 !important;
}

#catalogContainer.catalog-list .ebook-actions-footer {
    padding: 0 !important;
    margin: 0 !important;
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 10px !important;
    flex-shrink: 0 !important;
}

#catalogContainer.catalog-list .action-btn-wrap {
    width: auto !important;
    flex-shrink: 0 !important;
}

#catalogContainer.catalog-list .action-btn-wrap .btn,
#catalogContainer.catalog-list .action-btn-wrap a.btn {
    padding: 7px 14px !important;
    font-size: 12px !important;
    white-space: nowrap !important;
    height: 36px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

#catalogContainer.catalog-list .print-form-inline {
    background: transparent !important;
    border: none !important;
    padding: 0 !important;
    margin: 0 !important;
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 6px !important;
    flex-shrink: 0 !important;
}

#catalogContainer.catalog-list .print-form-label {
    display: none !important;
}

#catalogContainer.catalog-list .print-form-controls {
    margin: 0 !important;
}

#catalogContainer.catalog-list .print-input-field {
    width: 120px !important;
    padding: 6px 10px !important;
    font-size: 12px !important;
    height: 36px !important;
    border-radius: 8px !important;
}

#catalogContainer.catalog-list .print-submit-btn {
    padding: 6px 12px !important;
    font-size: 12px !important;
    height: 36px !important;
    white-space: nowrap !important;
    border-radius: 8px !important;
}

@media (max-width: 900px) {
    #catalogContainer.catalog-list .ebook-card-item {
        flex-direction: column !important;
        align-items: flex-start !important;
        padding: 14px !important;
    }
    #catalogContainer.catalog-list .ebook-title-heading {
        white-space: normal !important;
        max-width: 100% !important;
    }
    #catalogContainer.catalog-list .ebook-actions-footer {
        width: 100% !important;
        flex-wrap: wrap !important;
        justify-content: flex-start !important;
    }
}
</style>

<!-- Search & Filter Controls Card -->
<div class="card" style="margin-bottom:24px; padding:22px; border-radius:16px; border:1px solid var(--border-color); box-shadow:0 4px 20px rgba(15,23,42,0.03); background:#ffffff;">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:18px; padding-bottom:14px; border-bottom:1px solid #f1f5f9;">
        <div>
            <h3 style="border:none; margin:0; font-size:18px; font-weight:800; color:var(--navy-dark); display:flex; align-items:center; gap:10px;">
                <i class="fa-solid fa-magnifying-glass" style="color:var(--primary);"></i> Explore & Search Digital Catalog
            </h3>
            <p style="margin:3px 0 0 0; font-size:12.5px; color:var(--text-muted);">
                Browse instant digital e-books, request reading access, or submit print copies.
            </p>
        </div>
    </div>

    <form method="get" action="index.php" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:14px; align-items:end;">
        <input type="hidden" name="action" value="user">
        <input type="hidden" name="tab" value="books">
        
        <div>
            <label for="m_sc_term" style="font-size:11.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--navy-dark); margin-bottom:5px; display:block;">Search Keyword</label>
            <div style="position:relative;">
                <i class="fa-solid fa-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:13px;"></i>
                <input id="m_sc_term" name="search" value="<?= e($_GET['search'] ?? '') ?>" placeholder="Title, Author, Publisher" style="padding:9px 12px 9px 34px; font-size:13px; border-radius:8px; border:1px solid var(--border-color); width:100%;">
            </div>
        </div>
        
        <div>
            <label for="m_sc_cat" style="font-size:11.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--navy-dark); margin-bottom:5px; display:block;">Category</label>
            <select id="m_sc_cat" name="cat" style="padding:9px 12px; font-size:13px; border-radius:8px; border:1px solid var(--border-color); width:100%;">
                <option value="">All Categories</option>
                <?php 
                $cats = $db->query('SELECT * FROM categories ORDER BY name');
                while($r = $cats->fetch_assoc()) {
                    echo '<option value="' . $r['id'] . '" ' . ($cat == $r['id'] ? 'selected' : '') . '>' . e($r['name']) . '</option>';
                }
                ?>
            </select>
        </div>
        
        <div>
            <label for="m_sc_sort" style="font-size:11.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--navy-dark); margin-bottom:5px; display:block;">Sort Order</label>
            <select id="m_sc_sort" name="sort" style="padding:9px 12px; font-size:13px; border-radius:8px; border:1px solid var(--border-color); width:100%;">
                <option value="category_asc" <?= $sort === 'category_asc' ? 'selected' : '' ?>>Category & Book Title (A-Z)</option>
                <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>Title (A-Z)</option>
                <option value="title_desc" <?= $sort === 'title_desc' ? 'selected' : '' ?>>Title (Z-A)</option>
                <option value="category_desc" <?= $sort === 'category_desc' ? 'selected' : '' ?>>Category (Z-A)</option>
                <option value="id_desc" <?= $sort === 'id_desc' ? 'selected' : '' ?>>Newest Uploads</option>
                <option value="id_asc" <?= $sort === 'id_asc' ? 'selected' : '' ?>>Oldest Uploads</option>
            </select>
        </div>

        <div>
            <label for="m_sc_limit" style="font-size:11.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--navy-dark); margin-bottom:5px; display:block;">Per Page</label>
            <select id="m_sc_limit" name="limit" style="padding:9px 12px; font-size:13px; border-radius:8px; border:1px solid var(--border-color); width:100%;" onchange="this.form.submit();">
                <option value="12" <?= $limit_param === '12' ? 'selected' : '' ?>>12 e-books</option>
                <option value="24" <?= $limit_param === '24' ? 'selected' : '' ?>>24 e-books</option>
                <option value="48" <?= $limit_param === '48' ? 'selected' : '' ?>>48 e-books</option>
                <option value="96" <?= $limit_param === '96' ? 'selected' : '' ?>>96 e-books</option>
                <option value="all" <?= $limit_param === 'all' ? 'selected' : '' ?>>Show All</option>
            </select>
        </div>

        <div>
            <button class="btn" style="width:100%; padding:10px 16px; font-size:13px; font-weight:600; border-radius:8px; background:var(--primary); color:white; display:flex; align-items:center; justify-content:center; gap:8px;">
                <i class="fa-solid fa-filter"></i> Apply Filters
            </button>
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

<!-- Catalog Summary Bar & Interactive View Switcher -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px; background:#fff; padding:14px 20px; border-radius:14px; border:1px solid var(--border-color); box-shadow:0 4px 12px rgba(15,23,42,0.02);">
    <div style="font-size:13.5px; color:var(--navy-dark); font-weight:700; display:flex; align-items:center; gap:8px;">
        <span style="background:rgba(37, 99, 235, 0.1); color:var(--primary); padding:4px 10px; border-radius:8px; font-size:12px; font-weight:700;">
            <i class="fa-solid fa-book-bookmark"></i> e-Catalog
        </span>
        Showing <span style="color:var(--primary); font-weight:800;"><?= $total_ebooks > 0 ? ($e_offset + 1) : 0 ?> – <?= min($e_offset + $e_limit, $total_ebooks) ?></span> of <span style="color:var(--navy-dark); font-weight:800;"><?= number_format($total_ebooks) ?></span> available e-books
    </div>

    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        
        <!-- Interactive View Format Toggle -->
        <div style="display:flex; background:#f1f5f9; border-radius:10px; padding:3px; border:1px solid #e2e8f0;">
            <button type="button" id="btnGridView" onclick="switchCatalogView('grid')" style="border:none; background:#ffffff; color:var(--primary); padding:6px 14px; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer; box-shadow:0 2px 4px rgba(0,0,0,0.05); display:flex; align-items:center; gap:6px; transition:all 0.2s;" title="Switch to Grid View">
                <i class="fa-solid fa-table-cells-large"></i> Grid View
            </button>
            <button type="button" id="btnListView" onclick="switchCatalogView('list')" style="border:none; background:transparent; color:var(--text-muted); padding:6px 14px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px; transition:all 0.2s;" title="Switch to List View">
                <i class="fa-solid fa-list-ul"></i> List View
            </button>
        </div>

        <?php if ($total_pages > 1): ?>
            <div style="display:flex; align-items:center; gap:6px;">
                <label for="jump_top_page" style="font-size:12px; color:var(--text-muted); font-weight:600;">Page:</label>
                <select id="jump_top_page" style="padding:6px 10px; font-size:12px; border-radius:8px; border:1px solid var(--border-color); background:#f8fafc;" onchange="window.location.href='<?= $link_base ?>' + this.value;">
                    <?php for($p=1; $p<=$total_pages; $p++): ?>
                        <option value="<?= $p ?>" <?= $p === $e_page ? 'selected' : '' ?>><?= $p ?> / <?= $total_pages ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- Interactive Catalog Container (Grid / List format) -->
<div id="catalogContainer" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:22px;">
    <?php 
    $ebookCount = 0;
    while($r = $x->fetch_assoc()) {
        $ebookCount++;
        $req = $all_reading_reqs[$r['id']] ?? null;
        $has_pending_print = $all_print_reqs[$r['id']] ?? null;
        
        // Dynamic cover gradient based on ID
        $grad = $coverGradients[$r['id'] % count($coverGradients)];

        $cardAction = '';
        if ($req) {
            $isStarted = !empty($req['started_reading_at']);
            $isExpired = ($req['status'] === 'Expired') || (!empty($req['expires_at']) && strtotime($req['expires_at']) <= time());
            if ($req['status'] === 'Approved' && !$isExpired) {
                $btnText = $isStarted ? 'Continue Reading' : 'Read Now (Granted)';
                $expUnix = ($isStarted && !empty($req['expires_at'])) ? strtotime($req['expires_at']) : 0;
                $cardAction = '<button class="btn" style="width:100%; background:linear-gradient(135deg, #16a34a, #15803d); color:white; border:none; padding:10px 14px; font-weight:700; border-radius:8px; box-shadow:0 4px 12px rgba(22,163,74,0.25); display:flex; align-items:center; justify-content:center; gap:8px;" onclick="openPdfModal(' . $req['id'] . ', ' . $expUnix . ', \'' . addslashes(e($r['title'])) . '\')"><i class="fa-solid fa-book-open"></i> ' . $btnText . '</button>';
            } elseif ($req['status'] === 'Pending') {
                $cardAction = '<button class="btn" style="width:100%; background:#fef3c7; color:#92400e; border:1px solid #fde047; padding:10px 14px; font-weight:700; border-radius:8px; cursor:not-allowed; display:flex; align-items:center; justify-content:center; gap:8px;" disabled><i class="fa-solid fa-clock"></i> Reading Request Pending</button>';
            } else {
                $cardAction = '<a class="btn" style="width:100%; background:var(--primary); color:white; padding:10px 14px; font-weight:700; border-radius:8px; text-decoration:none; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 4px 12px rgba(37,99,235,0.2);" href="?action=request_read&id=' . $r['id'] . '" onclick="this.style.pointerEvents=\'none\'; this.style.opacity=\'0.6\'; this.innerHTML=\'<i class=\\\'fa-solid fa-spinner fa-spin\\\'></i> Requesting Permission...\';"><i class="fa-solid fa-key"></i> Request Reading Permission</a>';
            }
        } else {
            $cardAction = '<a class="btn" style="width:100%; background:var(--primary); color:white; padding:10px 14px; font-weight:700; border-radius:8px; text-decoration:none; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 4px 12px rgba(37,99,235,0.2);" href="?action=request_read&id=' . $r['id'] . '" onclick="this.style.pointerEvents=\'none\'; this.style.opacity=\'0.6\'; this.innerHTML=\'<i class=\\\'fa-solid fa-spinner fa-spin\\\'></i> Requesting Permission...\';"><i class="fa-solid fa-key"></i> Request Reading Permission</a>';
        }

        $printFormHtml = '
        <form class="print-form-inline" method="post" action="?action=request_print" style="margin:0; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:10px 12px;" onsubmit="const btn = this.querySelector(\'button\'); setTimeout(() => { btn.disabled = true; btn.innerHTML = \'<i class=\\\'fa-solid fa-spinner fa-spin\\\'></i> Sending...\'; }, 10);">
            ' . csrf_input() . '
            <input type="hidden" name="ebook_id" value="' . $r['id'] . '">
            <div class="print-form-label" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                <label style="font-size:11px; font-weight:700; color:var(--navy-dark); display:flex; align-items:center; gap:4px;" for="pages_' . $r['id'] . '"><i class="fa-solid fa-print" style="color:var(--primary);"></i> Request Hard Copy Print</label>
            </div>
            <div class="print-form-controls" style="display:flex; gap:6px;">';
        if ($has_pending_print) {
            $printFormHtml .= '
                <input id="pages_' . $r['id'] . '" class="print-input-field" name="pages" value="' . e($has_pending_print['pages']) . '" disabled style="margin:0; padding:7px 10px; font-size:12px; background:#e2e8f0; cursor:not-allowed; border-radius:6px; flex:1; border:1px solid #cbd5e1;">
                <button class="print-submit-btn" disabled style="font-size:11.5px; padding:7px 12px; white-space:nowrap; background:#f1f5f9; color:#64748b; border:1px solid #cbd5e1; border-radius:6px; cursor:not-allowed; font-weight:600;"><i class="fa-solid fa-clock"></i> Print Pending</button>';
        } else {
            $printFormHtml .= '
                <input id="pages_' . $r['id'] . '" class="print-input-field" name="pages" placeholder="Pages e.g. 1-10" required style="margin:0; padding:7px 10px; font-size:12px; border-radius:6px; border:1px solid var(--border-color); flex:1; background:white;">
                <button class="print-submit-btn" style="font-size:11.5px; padding:7px 12px; white-space:nowrap; background:var(--navy-dark); color:white; border-radius:6px; border:none; font-weight:600;"><i class="fa-solid fa-paper-plane"></i> Submit Print</button>';
        }
        $printFormHtml .= '
            </div>
        </form>';
        
        echo '
        <div class="ebook-card-item" style="background:#ffffff; border-radius:16px; border:1px solid var(--border-color); overflow:hidden; display:flex; flex-direction:column; justify-content:space-between; box-shadow:0 6px 18px rgba(15,23,42,0.03); transition:all 0.25s ease;" onmouseover="this.style.transform=\'translateY(-2px)\'; this.style.boxShadow=\'0 8px 20px rgba(15,23,42,0.06)\'; this.style.borderColor=\'var(--primary)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 6px 18px rgba(15,23,42,0.03)\'; this.style.borderColor=\'var(--border-color)\';" data-ebook-id="' . $r['id'] . '">
            
            <!-- Stylized 3D E-Book Cover Header (Visible in Grid View) -->
            <div class="ebook-cover-header" style="background:' . $grad . '; height:120px; padding:14px 16px; position:relative; display:flex; flex-direction:column; justify-content:space-between; color:white; overflow:hidden;">
                
                <!-- Decorative Background Watermark Icon -->
                <i class="fa-solid fa-book-open" style="position:absolute; right:-15px; bottom:-15px; font-size:90px; color:rgba(255,255,255,0.12); pointer-events:none;"></i>
                
                <div style="display:flex; justify-content:space-between; align-items:flex-start; position:relative; z-index:2;">
                    <span style="background:rgba(255,255,255,0.22); backdrop-filter:blur(4px); color:white; padding:4px 10px; border-radius:20px; font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; border:1px solid rgba(255,255,255,0.3);">
                        ' . e($r['category']) . '
                    </span>
                    <span style="background:rgba(15,23,42,0.4); color:white; padding:3px 8px; border-radius:6px; font-size:11px; font-family:monospace; font-weight:600;">
                        <i class="fa-solid fa-file-pdf"></i> PDF
                    </span>
                </div>

                <div style="display:flex; align-items:center; gap:8px; position:relative; z-index:2;">
                    <div style="width:32px; height:32px; border-radius:8px; background:rgba(255,255,255,0.25); backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; font-size:16px; color:white; flex-shrink:0;">
                        <i class="fa-solid fa-book-bookmark"></i>
                    </div>
                    <span style="font-size:11px; font-weight:600; opacity:0.9; text-transform:uppercase; letter-spacing:0.5px;">Digital E-Book</span>
                </div>
            </div>

            <!-- Book Details Content Body -->
            <div class="ebook-content-body" style="padding:16px 18px 10px 18px;">
                <span class="badge badge-blue ebook-cat-badge" style="font-size:10.5px; padding:3px 9px; text-transform:uppercase; letter-spacing:0.5px; font-weight:700; border-radius:6px;">
                    ' . e($r['category']) . '
                </span>

                <h3 class="ebook-title-heading" style="margin:8px 0 8px 0; font-size:16px; font-weight:800; line-height:1.35; color:var(--navy-dark); min-height:42px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;" title="' . e($r['title']) . '">
                    ' . e($r['title']) . '
                </h3>
                
                ' . (!empty($r['keywords']) ? '
                <div class="ebook-tags-wrap" style="margin-bottom:10px; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                    <span style="font-size:11.5px; color:var(--text-muted); background:#f1f5f9; padding:3px 8px; border-radius:6px; border:1px solid #e2e8f0; display:inline-flex; align-items:center; gap:4px;">
                        <i class="fa-solid fa-tags" style="color:var(--primary); font-size:10px;"></i> ' . e($r['keywords']) . '
                    </span>
                </div>' : '') . '
            </div>
            
            <!-- Actions Footer (1 Single Line in List View) -->
            <div class="ebook-actions-footer" style="padding:0 18px 18px 18px; display:flex; flex-direction:column; gap:10px;">
                <div id="action-btn-container-' . $r['id'] . '" class="action-btn-wrap">
                    ' . $cardAction . '
                </div>
                ' . $printFormHtml . '
            </div>

        </div>';
    }
    $stmt->close();

    if ($ebookCount === 0) {
        echo '<div class="card" style="grid-column: 1 / -1; text-align:center; padding:60px 20px; color:var(--text-muted); border-radius:16px; border:2px dashed #cbd5e1; background:#f8fafc;"><i class="fa-solid fa-book-open" style="font-size:42px; margin-bottom:14px; color:var(--primary); opacity:0.6;"></i><strong style="font-size:16px; color:var(--navy-dark); display:block; margin-bottom:4px;">No e-books found matching your filter</strong><p style="font-size:13px; margin:0;">Try clearing your keyword search or changing the selected category.</p></div>';
    }
    ?>
</div>

<!-- Premium Smart Pagination Bar -->
<?php if ($total_pages > 1): ?>
    <div class="pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:28px; flex-wrap:wrap; gap:15px; background:#fff; padding:16px 22px; border-radius:14px; border:1px solid var(--border-color); box-shadow:0 4px 12px rgba(15,23,42,0.02);">
        
        <!-- Summary Text -->
        <div style="font-size:13px; color:var(--text-muted); font-weight:600;">
            Page <strong style="color:var(--navy-dark);"><?= $e_page ?></strong> of <strong style="color:var(--navy-dark);"><?= $total_pages ?></strong> (<?= number_format($total_ebooks) ?> total e-books)
        </div>

        <!-- Pagination Controls -->
        <div class="pagination" style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
            
            <!-- First & Prev Page Buttons -->
            <?php if ($e_page > 1): ?>
                <a href="<?= $link_base ?>1" class="btn" style="padding:8px 12px; background:#f1f5f9; color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none; font-weight:600;" title="First Page"><i class="fa-solid fa-angles-left"></i></a>
                <a href="<?= $link_base ?><?= $e_page - 1 ?>" class="btn" style="padding:8px 12px; background:#f1f5f9; color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-weight:600;" title="Previous Page"><i class="fa-solid fa-angle-left"></i> Prev</a>
            <?php else: ?>
                <span class="btn disabled" style="padding:8px 12px; background:#f8fafc; color:#94a3b8; font-size:12px; border-radius:8px; opacity:0.6; cursor:not-allowed;"><i class="fa-solid fa-angles-left"></i></span>
                <span class="btn disabled" style="padding:8px 12px; background:#f8fafc; color:#94a3b8; font-size:12px; border-radius:8px; opacity:0.6; cursor:not-allowed;"><i class="fa-solid fa-angle-left"></i> Prev</span>
            <?php endif; ?>

            <!-- Smart Window Page Numbers -->
            <?php 
            $start_p = max(1, $e_page - 2);
            $end_p = min($total_pages, $e_page + 2);

            // Always show Page 1
            if ($start_p > 1) {
                echo '<a href="' . $link_base . '1" class="btn" style="padding:8px 13px; background:#f1f5f9; color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none; font-weight:600;">1</a>';
                if ($start_p > 2) {
                    echo '<span style="padding:0 4px; color:var(--text-muted); font-weight:700;">...</span>';
                }
            }

            for($i = $start_p; $i <= $end_p; $i++): 
            ?>
                <?php if ($i == $e_page): ?>
                    <span class="btn" style="padding:8px 14px; background:var(--primary); color:white; font-size:12px; font-weight:700; border-radius:8px; box-shadow:0 3px 8px rgba(37,99,235,0.3);"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= $link_base ?><?= $i ?>" class="btn" style="padding:8px 13px; background:#f1f5f9; color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none; font-weight:600;"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php
            // Always show Last Page
            if ($end_p < $total_pages) {
                if ($end_p < $total_pages - 1) {
                    echo '<span style="padding:0 4px; color:var(--text-muted); font-weight:700;">...</span>';
                }
                echo '<a href="' . $link_base . $total_pages . '" class="btn" style="padding:8px 13px; background:#f1f5f9; color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none; font-weight:600;">' . $total_pages . '</a>';
            }
            ?>

            <!-- Next & Last Page Buttons -->
            <?php if ($e_page < $total_pages): ?>
                <a href="<?= $link_base ?><?= $e_page + 1 ?>" class="btn" style="padding:8px 12px; background:#f1f5f9; color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-weight:600;" title="Next Page">Next <i class="fa-solid fa-angle-right"></i></a>
                <a href="<?= $link_base ?><?= $total_pages ?>" class="btn" style="padding:8px 12px; background:#f1f5f9; color:var(--navy-dark); font-size:12px; border-radius:8px; text-decoration:none; font-weight:600;" title="Last Page"><i class="fa-solid fa-angles-right"></i></a>
            <?php else: ?>
                <span class="btn disabled" style="padding:8px 12px; background:#f8fafc; color:#94a3b8; font-size:12px; border-radius:8px; opacity:0.6; cursor:not-allowed;">Next <i class="fa-solid fa-angle-right"></i></span>
                <span class="btn disabled" style="padding:8px 12px; background:#f8fafc; color:#94a3b8; font-size:12px; border-radius:8px; opacity:0.6; cursor:not-allowed;"><i class="fa-solid fa-angles-right"></i></span>
            <?php endif; ?>

        </div>

        <!-- Direct Jump Page Selector -->
        <div style="display:flex; align-items:center; gap:6px;">
            <label for="jump_bottom_page" style="font-size:12px; color:var(--text-muted); font-weight:600;">Go to:</label>
            <select id="jump_bottom_page" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid var(--border-color); background:#f8fafc;" onchange="window.location.href='<?= $link_base ?>' + this.value;">
                <?php for($p=1; $p<=$total_pages; $p++): ?>
                    <option value="<?= $p ?>" <?= $p === $e_page ? 'selected' : '' ?>>Page <?= $p ?></option>
                <?php endfor; ?>
            </select>
        </div>

    </div>
<?php endif; ?>

<script class="dynamic-script">
(function() {
    window.switchCatalogView = function(mode) {
        const container = document.getElementById('catalogContainer');
        const btnGrid = document.getElementById('btnGridView');
        const btnList = document.getElementById('btnListView');
        if (!container) return;

        if (mode === 'list') {
            container.classList.add('catalog-list');
            if (btnList) {
                btnList.style.background = '#ffffff';
                btnList.style.color = 'var(--primary)';
                btnList.style.fontWeight = '700';
                btnList.style.boxShadow = '0 2px 4px rgba(0,0,0,0.05)';
            }
            if (btnGrid) {
                btnGrid.style.background = 'transparent';
                btnGrid.style.color = 'var(--text-muted)';
                btnGrid.style.fontWeight = '600';
                btnGrid.style.boxShadow = 'none';
            }
            localStorage.setItem('cbmdl_catalog_view', 'list');
        } else {
            container.classList.remove('catalog-list');
            if (btnGrid) {
                btnGrid.style.background = '#ffffff';
                btnGrid.style.color = 'var(--primary)';
                btnGrid.style.fontWeight = '700';
                btnGrid.style.boxShadow = '0 2px 4px rgba(0,0,0,0.05)';
            }
            if (btnList) {
                btnList.style.background = 'transparent';
                btnList.style.color = 'var(--text-muted)';
                btnList.style.fontWeight = '600';
                btnList.style.boxShadow = 'none';
            }
            localStorage.setItem('cbmdl_catalog_view', 'grid');
        }
    };

    function initCatalogView() {
        const savedView = localStorage.getItem('cbmdl_catalog_view') || 'grid';
        window.switchCatalogView(savedView);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCatalogView);
    } else {
        initCatalogView();
    }
})();
</script>

<?php
// views/admin/view_ebooks.php
if (!defined('BASE_URL')) exit;

$e_search = trim($_GET['e_search'] ?? '');
$e_cat = (int)($_GET['e_cat'] ?? 0);
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? date('Y-m-d');

$e_limit = max(5, min(200, (int)($_GET['e_limit'] ?? 10)));
$e_page = max(1, (int)($_GET['e_page'] ?? 1));

// Fetch all categories for filter dropdown
$categories_res = $db->query("SELECT * FROM categories ORDER BY name ASC");
$all_categories = [];
if ($categories_res) {
    while ($cat_row = $categories_res->fetch_assoc()) {
        $all_categories[] = $cat_row;
    }
}

// Global catalog stats
$grand_total = (int)($db->query("SELECT COUNT(*) c FROM ebooks")->fetch_assoc()['c'] ?? 0);
$m_start = date('Y-m-01 00:00:00');
$m_end = date('Y-m-t 23:59:59');
$this_month_total = (int)($db->query("SELECT COUNT(*) c FROM ebooks WHERE created_at >= '$m_start' AND created_at <= '$m_end'")->fetch_assoc()['c'] ?? 0);

$where_clauses = [];
$params = [];
$types = "";

if ($e_cat > 0) {
    $where_clauses[] = "e.category_id = ?";
    $params[] = $e_cat;
    $types .= "i";
}

if ($e_search !== '') {
    $where_clauses[] = "(e.title LIKE ? OR e.keywords LIKE ? OR c.name LIKE ?)";
    $like_search = '%' . $e_search . '%';
    $params = array_merge($params, [$like_search, $like_search, $like_search]);
    $types .= "sss";
}

if ($from_date !== '') {
    $where_clauses[] = "e.created_at >= ?";
    $params[] = $from_date . ' 00:00:00';
    $types .= "s";
}

if ($to_date !== '') {
    $where_clauses[] = "e.created_at <= ?";
    $params[] = $to_date . ' 23:59:59';
    $types .= "s";
}

$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Count total matching ebooks using Prepared Statement
if (!empty($where_clauses)) {
    $count_stmt = $db->prepare("SELECT COUNT(*) c FROM ebooks e JOIN categories c ON c.id = e.category_id $where_sql");
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total_ebooks = (int)($count_stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $count_stmt->close();
} else {
    $total_ebooks = $grand_total;
}

$total_pages = ceil($total_ebooks / $e_limit);
if ($total_pages < 1) $total_pages = 1;
if ($e_page > $total_pages) $e_page = $total_pages;
$e_offset = ($e_page - 1) * $e_limit;

if (!empty($where_clauses)) {
    $stmt = $db->prepare("SELECT e.*, c.name category FROM ebooks e JOIN categories c ON c.id = e.category_id $where_sql ORDER BY c.name ASC, e.title ASC LIMIT ? OFFSET ?");
    $types_limit = $types . "ii";
    $bind_params = array_merge($params, [$e_limit, $e_offset]);
    $stmt->bind_param($types_limit, ...$bind_params);
} else {
    $stmt = $db->prepare("SELECT e.*, c.name category FROM ebooks e JOIN categories c ON c.id = e.category_id ORDER BY c.name ASC, e.title ASC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $e_limit, $e_offset);
}
$stmt->execute();
$ebooks_result = $stmt->get_result();
$stmt->close();
?>

<!-- Quick Metric Summary Cards -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; margin-bottom:20px;">
    <div style="background:#fff; border:1px solid var(--border-color); border-radius:12px; padding:15px; display:flex; align-items:center; gap:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <div style="width:42px; height:40px; border-radius:10px; background:rgba(37, 99, 235, 0.1); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:18px;">
            <i class="fa-solid fa-book-bookmark"></i>
        </div>
        <div>
            <span style="font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; display:block;">Total e-books</span>
            <h3 style="margin:2px 0 0 0; font-size:20px; font-weight:700; color:var(--navy-dark);"><?= number_format($total_ebooks) ?></h3>
        </div>
    </div>

    <div style="background:#fff; border:1px solid var(--border-color); border-radius:12px; padding:15px; display:flex; align-items:center; gap:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <div style="width:42px; height:40px; border-radius:10px; background:rgba(16, 185, 129, 0.1); color:var(--accent-green, #10b981); display:flex; align-items:center; justify-content:center; font-size:18px;">
            <i class="fa-solid fa-calendar-plus"></i>
        </div>
        <div>
            <span style="font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; display:block;">Added This Month</span>
            <h3 style="margin:2px 0 0 0; font-size:20px; font-weight:700; color:#15803d;"><?= number_format($this_month_total) ?></h3>
        </div>
    </div>

    <div style="background:#fff; border:1px solid var(--border-color); border-radius:12px; padding:15px; display:flex; align-items:center; gap:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <div style="width:42px; height:40px; border-radius:10px; background:rgba(245, 158, 11, 0.1); color:var(--accent-orange); display:flex; align-items:center; justify-content:center; font-size:18px;">
            <i class="fa-solid fa-layer-group"></i>
        </div>
        <div>
            <span style="font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; display:block;">Total Categories</span>
            <h3 style="margin:2px 0 0 0; font-size:20px; font-weight:700; color:#b45309;"><?= count($all_categories) ?></h3>
        </div>
    </div>

    <div style="background:#fff; border:1px solid var(--border-color); border-radius:12px; padding:15px; display:flex; align-items:center; gap:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <div style="width:42px; height:40px; border-radius:10px; background:rgba(100, 116, 139, 0.1); color:var(--text-muted); display:flex; align-items:center; justify-content:center; font-size:18px;">
            <i class="fa-solid fa-database"></i>
        </div>
        <div>
            <span style="font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; display:block;">Grand Total e-book</span>
            <h3 style="margin:2px 0 0 0; font-size:20px; font-weight:700; color:var(--text-color);"><?= number_format($grand_total) ?></h3>
        </div>
    </div>
</div>

<!-- Date & Keyword Filter Form Card -->
<div class="card" style="margin-bottom: 25px;">
    <h3><i class="fa-solid fa-filter"></i> Filter E-Books Catalog</h3>
    <form method="get" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 15px;">
        <input type="hidden" name="action" value="admin">
        <input type="hidden" name="tab" value="view_ebooks">
        
        <div>
            <label for="e_search">Search Keyword</label>
            <input id="e_search" name="e_search" value="<?= e($e_search) ?>" placeholder="Title, Author, Publisher">
        </div>
        
        <div>
            <label for="e_cat">Category</label>
            <select id="e_cat" name="e_cat">
                <option value="0">All Categories</option>
                <?php foreach ($all_categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $e_cat === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="e_from_date">Added From Date</label>
            <input type="date" id="e_from_date" name="from_date" value="<?= e($from_date) ?>">
        </div>
        
        <div>
            <label for="e_to_date">Added To Date</label>
            <input type="date" id="e_to_date" name="to_date" value="<?= e($to_date) ?>">
        </div>
        
        <div>
            <label style="visibility:hidden; margin-bottom:6px; display:block;">Action</label>
            <div style="display:flex; gap:8px;">
                <button type="submit" style="flex:1; padding:12px;"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
                <a href="?action=admin&tab=view_ebooks" class="btn btn-secondary" style="display:flex; align-items:center; justify-content:center; padding:12px; background:var(--bg-slate); color:var(--text-color); border:1px solid var(--border-color); text-decoration:none;"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:15px;">
        <h3 style="margin:0;"><i class="fa-solid fa-book-open"></i> E-Books Directory (<?= number_format($total_ebooks) ?> Records)</h3>
    </div>
    
    <div class="table-responsive">
        <table id="viewEbooksTable">
            <thead>
                <tr>
                    <th style="vertical-align:middle;">E-Book Title</th>
                    <th style="vertical-align:middle;">Author (Publisher)</th>
                    <th style="vertical-align:middle;">Category</th>
                    <th style="vertical-align:middle;">Added On</th>
                    <th style="vertical-align:middle;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($ebooks_result->num_rows === 0): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:30px; color:var(--text-muted);">
                            <i class="fa-solid fa-circle-info" style="font-size:24px; margin-bottom:10px; display:block; color:var(--primary);"></i>
                            No e-books found matching your filter criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while($r = $ebooks_result->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight:600; color:var(--navy-dark); vertical-align:middle;"><?= e($r['title']) ?></td>
                            <td style="font-size:12px; color:var(--text-secondary); vertical-align:middle;"><?= e($r['keywords'] ?: '—') ?></td>
                            <td style="vertical-align:middle;"><span class="badge badge-blue" style="font-size:11px; padding:4px 8px; font-weight:600;"><?= e($r['category']) ?></span></td>
                            <td style="font-size:12px; color:var(--text-muted); vertical-align:middle; white-space:nowrap;">
                                <i class="fa-regular fa-calendar" style="margin-right:4px;"></i><?= !empty($r['created_at']) ? date('d-m-Y', strtotime($r['created_at'])) : '—' ?>
                            </td>
                            <td style="vertical-align:middle;">
                                <div style="display:flex; gap:5px; flex-wrap:nowrap;">
                                    <a class="btn" style="background:var(--navy-light); padding:6px 12px; font-size:12px; display:inline-flex; align-items:center; gap:4px;" href="?action=view_pdf_content&id=<?= $r['id'] ?>" target="_blank"><i class="fa-solid fa-eye"></i> View PDF</a> 
                                    <a class="btn" style="background:var(--primary); padding:6px 12px; font-size:12px; display:inline-flex; align-items:center; gap:4px;" href="?action=admin&tab=ebooks&edit=<?= $r['id'] ?>"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                    <form method="post" action="?action=delete_ebook" class="delete-form" style="display:inline; margin:0;">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <button class="danger btn btn-danger" type="submit" style="padding:6px 12px; font-size:12px; display:inline-flex; align-items:center; gap:4px;"><i class="fa-solid fa-trash-can"></i> Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Premium Pagination Component -->
    <?php if ($total_ebooks > 0): ?>
        <?php
        $qs = $_GET;
        unset($qs['e_page']);
        $qs_str = http_build_query($qs);
        $qs_str = $qs_str ? '&' . $qs_str : '';

        $pages_to_show = get_smart_pagination_items($e_page, $total_pages);
        ?>
        <div class="pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; flex-wrap:wrap; gap:15px; border-top:1px solid var(--border-color); padding-top:15px;">
            <div style="display:flex; align-items:center; gap:15px; flex-wrap:wrap;">
                <div style="font-size:13px; color:var(--text-muted);">
                    Showing <strong><?= $e_offset + 1 ?></strong> to <strong><?= min($e_offset + $e_limit, $total_ebooks) ?></strong> of <strong><?= number_format($total_ebooks) ?></strong> e-books
                </div>
                <div style="display:inline-flex; align-items:center; gap:6px; font-size:13px; color:var(--text-muted);">
                    <span>Per page:</span>
                    <select onchange="window.location.href=this.value;" style="padding:4px 8px; font-size:12px; border-radius:6px; border:1px solid var(--border-color); background:var(--card-bg, #fff); color:var(--text-color); cursor:pointer; font-weight:600;">
                        <?php foreach ([10, 25, 50, 100, 200] as $lim): ?>
                            <?php
                            $lim_qs = $_GET;
                            $lim_qs['e_limit'] = $lim;
                            $lim_qs['e_page'] = 1;
                            $lim_url = '?' . http_build_query($lim_qs);
                            ?>
                            <option value="<?= e($lim_url) ?>" <?= $e_limit == $lim ? 'selected' : '' ?>><?= $lim ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="pagination" style="display:flex; align-items:center; gap:6px;">
                <!-- First Page Button -->
                <?php if ($e_page > 1): ?>
                    <a href="?e_page=1<?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="First Page"><i class="fa-solid fa-angles-left"></i></a>
                    <a href="?e_page=<?= $e_page - 1 ?><?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Previous Page"><i class="fa-solid fa-angle-left"></i> Prev</a>
                <?php else: ?>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-left"></i></span>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angle-left"></i> Prev</span>
                <?php endif; ?>

                <!-- Page numbers (smart sliding window) -->
                <?php foreach ($pages_to_show as $p_item): ?>
                    <?php if ($p_item === '...'): ?>
                        <span style="padding:6px 8px; color:var(--text-muted); font-size:12px; font-weight:700;">...</span>
                    <?php elseif ($p_item == $e_page): ?>
                        <span class="btn" style="padding:6px 12px; background:var(--primary); color:white; font-size:12px; font-weight:700; border-radius:6px;"><?= $p_item ?></span>
                    <?php else: ?>
                        <a href="?e_page=<?= $p_item ?><?= $qs_str ?>" class="btn" style="padding:6px 12px; background:var(--bg-slate); color:var(--text-color); font-size:12px; border-radius:6px; text-decoration:none;"><?= $p_item ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <!-- Next Page Button -->
                <?php if ($e_page < $total_pages): ?>
                    <a href="?e_page=<?= $e_page + 1 ?><?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Next Page">Next <i class="fa-solid fa-angle-right"></i></a>
                    <a href="?e_page=<?= $total_pages ?><?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="Last Page"><i class="fa-solid fa-angles-right"></i></a>
                <?php else: ?>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;">Next <i class="fa-solid fa-angle-right"></i></span>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-right"></i></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

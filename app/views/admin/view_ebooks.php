<?php
// views/admin/view_ebooks.php
if (!defined('BASE_URL')) exit;

$e_search = trim($_GET['e_search'] ?? '');
$e_cat = (int)($_GET['e_cat'] ?? 0);
$e_page = max(1, (int)($_GET['e_page'] ?? 1));
$e_limit = 10;

// Fetch all categories for filter dropdown
$categories_res = $db->query("SELECT * FROM categories ORDER BY name ASC");
$all_categories = [];
if ($categories_res) {
    while ($cat_row = $categories_res->fetch_assoc()) {
        $all_categories[] = $cat_row;
    }
}

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

$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Count total matching ebooks
if (!empty($where_clauses)) {
    $count_stmt = $db->prepare("SELECT COUNT(*) c FROM ebooks e JOIN categories c ON c.id = e.category_id $where_sql");
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total_ebooks = (int)($count_stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $count_stmt->close();
} else {
    $total_ebooks = (int)($db->query("SELECT COUNT(*) c FROM ebooks")->fetch_assoc()['c'] ?? 0);
}

$total_pages = ceil($total_ebooks / $e_limit);
if ($total_pages < 1) $total_pages = 1;
if ($e_page > $total_pages) $e_page = $total_pages;
$e_offset = ($e_page - 1) * $e_limit;

if (!empty($where_clauses)) {
    $stmt = $db->prepare("SELECT e.*, c.name category FROM ebooks e JOIN categories c ON c.id = e.category_id $where_sql ORDER BY e.id DESC LIMIT ? OFFSET ?");
    $types_limit = $types . "ii";
    $bind_params = array_merge($params, [$e_limit, $e_offset]);
    $stmt->bind_param($types_limit, ...$bind_params);
} else {
    $stmt = $db->prepare("SELECT e.*, c.name category FROM ebooks e JOIN categories c ON c.id = e.category_id ORDER BY e.id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $e_limit, $e_offset);
}
$stmt->execute();
$ebooks_result = $stmt->get_result();
$stmt->close();
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:15px;">
        <h3 style="margin:0;"><i class="fa-solid fa-book-open"></i> View All E-Books Directory (<?= $total_ebooks ?> Volumes)</h3>
        
        <!-- Search and Filter form -->
        <form method="get" style="margin:0; display:flex; gap:8px; width:100%; max-width:620px; flex-wrap:wrap; align-items:center;">
            <input type="hidden" name="action" value="admin">
            <input type="hidden" name="tab" value="view_ebooks">
            
            <!-- Category Filter Dropdown -->
            <select name="e_cat" onchange="this.form.submit()" style="margin:0; font-size:13px; padding:8px 12px; width:170px; flex-shrink:0;">
                <option value="0">All Categories</option>
                <?php foreach ($all_categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $e_cat === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Keyword Search -->
            <input type="text" name="e_search" value="<?= e($e_search) ?>" placeholder="Search Title, Category, or Keywords..." style="margin:0; font-size:13px; padding:8px 12px; flex:1; min-width:180px;">
            
            <button class="btn" style="padding:8px 15px; margin:0; background:var(--navy-dark);"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            <?php if ($e_search !== '' || $e_cat > 0): ?>
                <a href="?action=admin&tab=view_ebooks" class="btn" style="padding:8px 15px; margin:0; background:var(--accent-orange); display:inline-flex; align-items:center; justify-content:center;" title="Reset Filters"><i class="fa-solid fa-arrow-rotate-left"></i> Reset</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="table-responsive">
        <table id="viewEbooksTable">
            <thead>
                <tr>
                    <th>E-Book Title</th>
                    <th>Category</th>
                    <th>Keywords</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($ebooks_result->num_rows === 0): ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding:30px; color:var(--text-muted);">
                            <i class="fa-solid fa-triangle-exclamation" style="font-size:24px; margin-bottom:10px; display:block;"></i>
                            No e-books found matching your filter criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while($r = $ebooks_result->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight:600; color:var(--navy-dark);"><?= e($r['title']) ?></td>
                            <td><span class="badge badge-blue" style="font-size:11px; padding:3px 8px; font-weight:600;"><?= e($r['category']) ?></span></td>
                            <td style="font-size:12px; color:var(--text-secondary);"><?= e($r['keywords'] ?: '—') ?></td>
                            <td>
                                <div style="display:flex; gap:5px;">
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
    <?php if ($total_pages > 1): ?>
        <?php $query_suffix = '&e_search=' . urlencode($e_search) . '&e_cat=' . $e_cat; ?>
        <div class="pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; flex-wrap:wrap; gap:15px; border-top:1px solid var(--border-color); padding-top:15px;">
            <div style="font-size:13px; color:var(--text-muted);">
                Showing <strong><?= $e_offset + 1 ?></strong> to <strong><?= min($e_offset + $e_limit, $total_ebooks) ?></strong> of <strong><?= $total_ebooks ?></strong> e-books
            </div>
            <div class="pagination" style="display:flex; align-items:center; gap:6px;">
                <!-- First Page Button -->
                <?php if ($e_page > 1): ?>
                    <a href="?action=admin&tab=view_ebooks&e_page=1<?= $query_suffix ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="First Page"><i class="fa-solid fa-angles-left"></i></a>
                    <a href="?action=admin&tab=view_ebooks&e_page=<?= $e_page - 1 ?><?= $query_suffix ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Previous Page"><i class="fa-solid fa-angle-left"></i> Prev</a>
                <?php else: ?>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-left"></i></span>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angle-left"></i> Prev</span>
                <?php endif; ?>

                <!-- Page numbers (smart sliding window) -->
                <?php 
                $start_e = max(1, $e_page - 2);
                $end_e = min($total_pages, $e_page + 2);
                for($i = $start_e; $i <= $end_e; $i++): 
                ?>
                    <?php if ($i == $e_page): ?>
                        <span class="btn" style="padding:6px 12px; background:var(--primary); color:white; font-size:12px; font-weight:700; border-radius:6px;"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?action=admin&tab=view_ebooks&e_page=<?= $i ?><?= $query_suffix ?>" class="btn" style="padding:6px 12px; background:var(--bg-slate); color:var(--text-color); font-size:12px; border-radius:6px;"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <!-- Next Page Button -->
                <?php if ($e_page < $total_pages): ?>
                    <a href="?action=admin&tab=view_ebooks&e_page=<?= $e_page + 1 ?><?= $query_suffix ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Next Page">Next <i class="fa-solid fa-angle-right"></i></a>
                    <a href="?action=admin&tab=view_ebooks&e_page=<?= $total_pages ?><?= $query_suffix ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="Last Page"><i class="fa-solid fa-angles-right"></i></a>
                <?php else: ?>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;">Next <i class="fa-solid fa-angle-right"></i></span>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-right"></i></span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// views/admin/categories.php
if (!defined('BASE_URL')) exit;

$cat_search = trim($_GET['cat_search'] ?? '');
$cat_sort = trim($_GET['cat_sort'] ?? 'name_asc');
$cat_page = max(1, (int)($_GET['cat_page'] ?? 1));
$cat_limit = 12;

$sort_options = [
    'name_asc' => 'c.name ASC',
    'name_desc' => 'c.name DESC',
    'books_desc' => 'ebook_count DESC, c.name ASC',
    'books_asc' => 'ebook_count ASC, c.name ASC',
    'id_desc' => 'c.id DESC',
    'id_asc' => 'c.id ASC',
];

$order_by = $sort_options[$cat_sort] ?? 'c.name ASC';

$where_sql = "";
$params = [];
$types = "";

if ($cat_search !== '') {
    $where_sql = "WHERE LOWER(c.name) LIKE ?";
    $params[] = '%' . strtolower($cat_search) . '%';
    $types .= "s";
}

// Count total matching categories
if ($cat_search !== '') {
    $countStmt = $db->prepare("SELECT COUNT(*) c FROM categories c $where_sql");
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $total_categories = (int)($countStmt->get_result()->fetch_assoc()['c'] ?? 0);
    $countStmt->close();
} else {
    $total_categories = (int)($db->query("SELECT COUNT(*) c FROM categories")->fetch_assoc()['c'] ?? 0);
}

$total_pages = (int)ceil($total_categories / $cat_limit);
if ($total_pages < 1) $total_pages = 1;
if ($cat_page > $total_pages) $cat_page = $total_pages;
$cat_offset = ($cat_page - 1) * $cat_limit;

// Fetch paginated & sorted categories along with e-book counts
if ($cat_search !== '') {
    $stmt = $db->prepare("
        SELECT c.id, c.name, COUNT(e.id) as ebook_count 
        FROM categories c 
        LEFT JOIN ebooks e ON e.category_id = c.id 
        $where_sql 
        GROUP BY c.id, c.name 
        ORDER BY $order_by 
        LIMIT ? OFFSET ?
    ");
    $types_bind = $types . "ii";
    $bind_params = array_merge($params, [$cat_limit, $cat_offset]);
    $stmt->bind_param($types_bind, ...$bind_params);
    $stmt->execute();
    $res = $stmt->get_result();
    $categories = [];
    while ($row = $res->fetch_assoc()) {
        $categories[] = $row;
    }
    $stmt->close();
} else {
    $res = $db->query("
        SELECT c.id, c.name, COUNT(e.id) as ebook_count 
        FROM categories c 
        LEFT JOIN ebooks e ON e.category_id = c.id 
        GROUP BY c.id, c.name 
        ORDER BY $order_by 
        LIMIT $cat_limit OFFSET $cat_offset
    ");
    $categories = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $categories[] = $row;
        }
    }
}
?>

<style>
.categories-container {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 24px;
    align-items: start;
}

@media (max-width: 992px) {
    .categories-container {
        grid-template-columns: 1fr;
    }
}

.view-toggle-btn {
    padding: 6px 14px;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid var(--border-color);
    background: var(--bg-slate);
    color: var(--text-muted);
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.view-toggle-btn.active {
    background: var(--primary);
    color: #ffffff;
    border-color: var(--primary);
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
}

.view-toggle-btn:hover:not(.active) {
    background: #e2e8f0;
    color: var(--navy-dark);
}

/* Category Grid Card Styling */
.category-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 18px;
    margin-top: 16px;
}

.category-card {
    background: #ffffff;
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 18px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 14px;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    position: relative;
    overflow: hidden;
}

.category-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary), #3b82f6);
    border-radius: 14px 14px 0 0;
    opacity: 0.85;
}

.category-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
    border-color: #cbd5e1;
}

.category-card-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.category-icon-box {
    width: 42px;
    height: 42px;
    min-width: 42px;
    border-radius: 10px;
    background: #eff6ff;
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.1);
}

.category-card-info {
    flex: 1;
    min-width: 0;
}

.category-card-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--navy-dark);
    margin: 0 0 4px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.category-card-count {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #f1f5f9;
    padding: 3px 8px;
    border-radius: 6px;
}

.category-card-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-top: 10px;
    border-top: 1px dashed var(--border-color);
    margin-top: auto;
}

.empty-categories-notice {
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px 20px;
    background: #f8fafc;
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    color: var(--text-muted);
}
</style>

<div class="categories-container">
    <!-- Left Column: Add Category Form -->
    <div class="card" style="height: fit-content;">
        <h3 style="margin-top:0; font-size:16px; font-weight:700; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-circle-plus" style="color:var(--primary);"></i> Add New e-books Category
        </h3>
        <form method="post" action="?action=add_category">
            <?= csrf_input() ?>
            <div style="margin-bottom:14px;">
                <label for="cat_input" style="display:block; font-weight:600; font-size:13px; margin-bottom:6px; color:var(--navy-dark);">Category Name</label>
                <input id="cat_input" name="name" placeholder="e.g. UPSC, Engineering, Novel" required style="width:100%; padding:10px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:14px;">
            </div>
            <button class="btn" style="width:100%; background:var(--primary); color:white; font-weight:600; padding:10px 16px; border-radius:8px; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                <i class="fa-solid fa-floppy-disk"></i> Save Category
            </button>
        </form>
    </div>

    <!-- Right Column: Saved Categories View (Grid & List Toggle + Search + Sorting + Pagination) -->
    <div class="card">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:16px; border-bottom:1px solid var(--border-color); padding-bottom:12px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <h3 style="margin:0; font-size:17px; font-weight:700; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-folder-open" style="color:var(--primary);"></i> Saved Categories
                </h3>
                <span class="badge badge-blue" style="font-size:12px; padding:3px 10px; font-weight:700; border-radius:12px;">
                    <?= $total_categories ?> Total
                </span>
            </div>

            <!-- Grid / List View Toggle Switch -->
            <div style="display:flex; align-items:center; gap:6px;">
                <button type="button" id="catGridViewBtn" class="view-toggle-btn active" title="Switch to Grid View" onclick="setCategoryViewMode('grid')">
                    <i class="fa-solid fa-border-all"></i> Grid
                </button>
                <button type="button" id="catListViewBtn" class="view-toggle-btn" title="Switch to List View" onclick="setCategoryViewMode('list')">
                    <i class="fa-solid fa-list"></i> List
                </button>
            </div>
        </div>

        <!-- Search & Sorting Toolbar -->
        <form method="get" action="index.php" style="margin-bottom:16px;">
            <input type="hidden" name="action" value="admin">
            <input type="hidden" name="tab" value="categories">
            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                <!-- Search Box -->
                <div style="position:relative; flex:1; min-width:200px;">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:14px;"></i>
                    <input type="text" id="catFilterInput" name="cat_search" value="<?= e($cat_search) ?>" placeholder="Instant Category Search..." style="width:100%; padding:10px 14px 10px 38px; border:1px solid var(--border-color); border-radius:8px; font-size:14px; background:#fafafa;">
                </div>
                
                <!-- Sort Dropdown -->
                <div style="display:flex; align-items:center; gap:6px;">
                    <label for="catSortSelect" style="font-size:13px; font-weight:600; color:var(--text-muted); white-space:nowrap;"><i class="fa-solid fa-arrow-down-wide-short"></i> Sort:</label>
                    <select id="catSortSelect" name="cat_sort" onchange="this.form.submit()" style="padding:10px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13.5px; background:#fafafa; font-weight:600; color:var(--navy-dark); cursor:pointer; outline:none;">
                        <option value="name_asc" <?= $cat_sort === 'name_asc' ? 'selected' : '' ?>>Name (A - Z)</option>
                        <option value="name_desc" <?= $cat_sort === 'name_desc' ? 'selected' : '' ?>>Name (Z - A)</option>
                        <option value="books_desc" <?= $cat_sort === 'books_desc' ? 'selected' : '' ?>>Most E-Books</option>
                        <option value="books_asc" <?= $cat_sort === 'books_asc' ? 'selected' : '' ?>>Least E-Books</option>
                        <option value="id_desc" <?= $cat_sort === 'id_desc' ? 'selected' : '' ?>>Newest First</option>
                        <option value="id_asc" <?= $cat_sort === 'id_asc' ? 'selected' : '' ?>>Oldest First</option>
                    </select>
                </div>

                <?php if ($cat_search !== '' || $cat_sort !== 'name_asc'): ?>
                    <a href="?action=admin&tab=categories" class="btn" style="padding:10px 14px; background:#e2e8f0; color:var(--text-dark); border-radius:8px; text-decoration:none; font-size:13px; font-weight:600; display:inline-flex; align-items:center; gap:6px;">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- 1. Grid View Section -->
        <div id="catGridViewContainer">
            <?php if (empty($categories)): ?>
                <div class="empty-categories-notice">
                    <i class="fa-solid fa-folder-open" style="font-size:32px; color:#94a3b8; margin-bottom:8px; display:block;"></i>
                    <strong style="display:block; font-size:15px;">No Categories Found</strong>
                    <span style="font-size:13px;"><?= $cat_search !== '' ? 'No categories matched your search term.' : 'Add your first category using the form on the left.' ?></span>
                </div>
            <?php else: ?>
                <div id="categoriesGrid" class="category-cards-grid">
                    <?php foreach ($categories as $cat): ?>
                        <div class="category-card" data-name="<?= e(strtolower($cat['name'])) ?>">
                            <div class="category-card-header">
                                <div class="category-icon-box">
                                    <i class="fa-solid fa-folder-open"></i>
                                </div>
                                <div class="category-card-info">
                                    <h4 class="category-card-title" title="<?= e($cat['name']) ?>"><?= e($cat['name']) ?></h4>
                                    <span class="category-card-count">
                                        <i class="fa-solid fa-book-open" style="color:var(--primary);"></i>
                                        <?= (int)$cat['ebook_count'] ?> E-Book<?= (int)$cat['ebook_count'] === 1 ? '' : 's' ?>
                                    </span>
                                </div>
                            </div>
                            <div class="category-card-footer">
                                <form method="post" action="?action=delete_category" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete category \'<?= e(addslashes($cat['name'])) ?>\'? All associated e-books under this category will also be cleaned up.');">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                    <button type="submit" class="btn btn-danger" style="padding:6px 12px; font-size:12px; border-radius:6px; font-weight:600; display:inline-flex; align-items:center; gap:5px;">
                                        <i class="fa-solid fa-trash-can"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- 2. Table / List View Section -->
        <div id="catListViewContainer" style="display:none;">
            <div class="table-responsive">
                <table id="categoriesTable" style="width:100%;">
                    <thead>
                        <tr>
                            <th style="width:60px;">#</th>
                            <th>Category Name</th>
                            <th>Associated E-Books</th>
                            <th style="width:120px; text-align:right;">Operations</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sr = $cat_offset + 1; 
                        foreach ($categories as $cat) {
                            echo "
                            <tr data-name='" . e(strtolower($cat['name'])) . "'>
                                <td>{$sr}</td>
                                <td class='cat-name-cell' style='font-weight:600; color:var(--navy-dark);'>" . e($cat['name']) . "</td>
                                <td><span class='badge badge-blue'><i class='fa-solid fa-book-open'></i> " . (int)$cat['ebook_count'] . " E-Book(s)</span></td>
                                <td style='text-align:right;'>
                                    <form method='post' action='?action=delete_category' style='display:inline; margin:0;' onsubmit=\"return confirm('Are you sure you want to delete category \'" . e(addslashes($cat['name'])) . "\'?');\">
                                        " . csrf_input() . "
                                        <input type='hidden' name='id' value='{$cat['id']}'>
                                        <button class='btn btn-danger' type='submit' style='padding:6px 12px; font-size:12px; border-radius:6px; font-weight:600;'><i class='fa-solid fa-trash-can'></i> Delete</button>
                                    </form>
                                </td>
                            </tr>";
                            $sr++; 
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Component -->
        <?php if ($total_pages > 1): ?>
            <?php $query_suffix = '&cat_search=' . urlencode($cat_search) . '&cat_sort=' . urlencode($cat_sort); ?>
            <div class="pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; flex-wrap:wrap; gap:15px; border-top:1px solid var(--border-color); padding-top:15px;">
                <div style="font-size:13px; color:var(--text-muted);">
                    Showing <strong><?= min($cat_offset + 1, $total_categories) ?></strong> to <strong><?= min($cat_offset + $cat_limit, $total_categories) ?></strong> of <strong><?= $total_categories ?></strong> categories
                </div>
                <div class="pagination" style="display:flex; align-items:center; gap:6px;">
                    <!-- First Page Button -->
                    <?php if ($cat_page > 1): ?>
                        <a href="?action=admin&tab=categories&cat_page=1<?= $query_suffix ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="First Page"><i class="fa-solid fa-angles-left"></i></a>
                        <a href="?action=admin&tab=categories&cat_page=<?= $cat_page - 1 ?><?= $query_suffix ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Previous Page"><i class="fa-solid fa-angle-left"></i> Prev</a>
                    <?php else: ?>
                        <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-left"></i></span>
                        <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angle-left"></i> Prev</span>
                    <?php endif; ?>

                    <!-- Page Numbers -->
                    <?php 
                    $pages_to_show = get_smart_pagination_items($cat_page, $total_pages);
                    foreach ($pages_to_show as $p_item): 
                    ?>
                        <?php if ($p_item === '...'): ?>
                            <span style="padding:6px 8px; color:var(--text-muted); font-size:12px; font-weight:700;">...</span>
                        <?php elseif ($p_item == $cat_page): ?>
                            <span class="btn" style="padding:6px 12px; background:var(--primary); color:white; font-size:12px; font-weight:700; border-radius:6px;"><?= $p_item ?></span>
                        <?php else: ?>
                            <a href="?action=admin&tab=categories&cat_page=<?= $p_item ?><?= $query_suffix ?>" class="btn" style="padding:6px 12px; background:var(--bg-slate); color:var(--text-color); font-size:12px; border-radius:6px; text-decoration:none;"><?= $p_item ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <!-- Next Page Button -->
                    <?php if ($cat_page < $total_pages): ?>
                        <a href="?action=admin&tab=categories&cat_page=<?= $cat_page + 1 ?><?= $query_suffix ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Next Page">Next <i class="fa-solid fa-angle-right"></i></a>
                        <a href="?action=admin&tab=categories&cat_page=<?= $total_pages ?><?= $query_suffix ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="Last Page"><i class="fa-solid fa-angles-right"></i></a>
                    <?php else: ?>
                        <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;">Next <i class="fa-solid fa-angle-right"></i></span>
                        <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-right"></i></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
window.setCategoryViewMode = function(mode) {
    const gridBtn = document.getElementById('catGridViewBtn');
    const listBtn = document.getElementById('catListViewBtn');
    const gridContainer = document.getElementById('catGridViewContainer');
    const listContainer = document.getElementById('catListViewContainer');

    if (!gridContainer || !listContainer) return;

    if (mode === 'list') {
        gridContainer.style.display = 'none';
        listContainer.style.display = 'block';
        if (gridBtn) gridBtn.classList.remove('active');
        if (listBtn) listBtn.classList.add('active');
        localStorage.setItem('cbmdl_category_view_mode', 'list');
    } else {
        gridContainer.style.display = 'block';
        listContainer.style.display = 'none';
        if (gridBtn) gridBtn.classList.add('active');
        if (listBtn) listBtn.classList.remove('active');
        localStorage.setItem('cbmdl_category_view_mode', 'grid');
    }
};

(function initCategoryView() {
    const savedView = localStorage.getItem('cbmdl_category_view_mode') || 'grid';
    window.setCategoryViewMode(savedView);

    const searchInput = document.getElementById('catFilterInput');
    if (searchInput && !searchInput.dataset.catSearchInit) {
        searchInput.dataset.catSearchInit = 'true';
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            
            // 1. Filter Grid Cards
            const cards = document.querySelectorAll('#categoriesGrid .category-card');
            cards.forEach(card => {
                const name = card.getAttribute('data-name') || card.textContent.toLowerCase();
                card.style.display = name.includes(query) ? 'flex' : 'none';
            });

            // 2. Filter Table Rows
            const rows = document.querySelectorAll('#categoriesTable tbody tr');
            rows.forEach(row => {
                const name = row.getAttribute('data-name') || row.textContent.toLowerCase();
                row.style.display = name.includes(query) ? '' : 'none';
            });
        });
    }
})();
</script>

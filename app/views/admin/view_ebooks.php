<?php
// views/admin/view_ebooks.php
if (!defined('BASE_URL')) exit;

$search = trim($_GET['search'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);
$sort = $_GET['sort'] ?? 'title_asc';

$orderBy = 'e.title ASC';
if ($sort === 'title_desc') {
    $orderBy = 'e.title DESC';
} elseif ($sort === 'category_asc') {
    $orderBy = 'category ASC';
} elseif ($sort === 'category_desc') {
    $orderBy = 'category DESC';
} elseif ($sort === 'id_desc') {
    $orderBy = 'e.id DESC';
} elseif ($sort === 'id_asc') {
    $orderBy = 'e.id ASC';
}

$whereClauses = [];
$params = [];
$types = '';

if ($cat) {
    $whereClauses[] = 'e.category_id = ?';
    $params[] = $cat;
    $types .= 'i';
}
if ($search !== '') {
    $whereClauses[] = 'e.title LIKE ?';
    $params[] = '%' . $search . '%';
    $types .= 's';
}

$whereStr = '';
if (!empty($whereClauses)) {
    $whereStr = 'WHERE ' . implode(' AND ', $whereClauses);
}

$queryStr = "SELECT e.*, c.name category FROM ebooks e JOIN categories c ON c.id = e.category_id $whereStr ORDER BY $orderBy";

if (!empty($params)) {
    $stmt = $db->prepare($queryStr);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $x = $stmt->get_result();
    $stmt->close();
} else {
    $x = $db->query($queryStr);
}
?>
<div class="card">
    <h3><i class="fa-solid fa-magnifying-glass"></i> Filter Catalog</h3>
    <form method="get" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <input type="hidden" name="action" value="admin">
        <input type="hidden" name="tab" value="view_ebooks">
        <div>
            <label for="sc_title">Search Title</label>
            <input id="sc_title" name="search" value="<?= e($search) ?>" placeholder="Search title name...">
        </div>
        <div>
            <label for="sc_cat">Category</label>
            <select id="sc_cat" name="cat">
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
            <label for="sc_sort">Sort By</label>
            <select id="sc_sort" name="sort">
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
            <button style="width:100%; margin:6px 0 18px 0; padding:12px 24px; box-sizing:border-box;"><i class="fa-solid fa-circle-check"></i> Filter Repository</button>
        </div>
    </form>
</div>

<div class="card">
    <h3><i class="fa-solid fa-layer-group"></i> Catalog Book Index (Total Matches: <?= $x->num_rows ?>)</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Keywords</th>
                    <th>Safe Download</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                while($r = $x->fetch_assoc()) {
                    echo '
                    <tr>
                        <td>' . e($r['title']) . '</td>
                        <td><span class="badge badge-blue">' . e($r['category']) . '</span></td>
                        <td>' . e($r['keywords']) . '</td>
                        <td>
                            <a class="btn" target="_blank" href="' . BASE_URL . '?action=secure_pdf_viewer&source=admin&id=' . e($r['id']) . '" style="background:var(--accent-green);"><i class="fa-solid fa-book-open"></i> Stream PDF File</a>
                        </td>
                    </tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

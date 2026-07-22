<?php
// views/admin/view_physical.php
if (!defined('BASE_URL')) exit;

// 1. Pagination & Filter parameters
$p_search = trim($_GET['p_search'] ?? '');
$p_shelf = trim($_GET['p_shelf'] ?? '');
$p_status = trim($_GET['p_status'] ?? 'all'); // 'all', 'available', 'issued'
$p_sort = trim($_GET['p_sort'] ?? 'newest'); // newest, oldest, shelf_asc, shelf_desc, title_asc
$p_page = max(1, (int)($_GET['p_page'] ?? 1));
$p_limit = 10; // Number of items per page

// Fetch distinct shelves breakdown with book counts for shelf summary analytics
$shelf_counts = [];
$shelf_query = $db->query("SELECT COALESCE(NULLIF(TRIM(shelf_number), ''), 'Unassigned') as shelf, COUNT(*) as cnt FROM physical_books GROUP BY shelf ORDER BY shelf ASC");
while ($srow = $shelf_query->fetch_assoc()) {
    $shelf_counts[] = $srow;
}

// Fetch availability totals for filter pill badges
$tot_all = (int)$db->query("SELECT COUNT(*) c FROM physical_books")->fetch_assoc()['c'];
$tot_issued = (int)$db->query("SELECT COUNT(DISTINCT physical_book_id) c FROM lendings WHERE returned_at IS NULL")->fetch_assoc()['c'];
$tot_available = max(0, $tot_all - $tot_issued);

// Constructing filter queries
$where_clauses = [];
$params = [];
$types = "";

if ($p_search !== '') {
    $where_clauses[] = "(p.title LIKE ? OR p.book_code LIKE ? OR p.shelf_number LIKE ? OR p.author LIKE ? OR p.publisher LIKE ?)";
    $like_search = '%' . $p_search . '%';
    $params = array_merge($params, [$like_search, $like_search, $like_search, $like_search, $like_search]);
    $types .= "sssss";
}

if ($p_shelf !== '') {
    if ($p_shelf === '__unassigned__') {
        $where_clauses[] = "(p.shelf_number IS NULL OR TRIM(p.shelf_number) = '')";
    } else {
        $where_clauses[] = "p.shelf_number = ?";
        $params[] = $p_shelf;
        $types .= "s";
    }
}

if ($p_status === 'available') {
    $where_clauses[] = "NOT EXISTS(SELECT 1 FROM lendings l WHERE l.physical_book_id = p.id AND l.returned_at IS NULL)";
} elseif ($p_status === 'issued') {
    $where_clauses[] = "EXISTS(SELECT 1 FROM lendings l WHERE l.physical_book_id = p.id AND l.returned_at IS NULL)";
}

$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Sorting logic
$order_by = "ORDER BY p.id DESC";
if ($p_sort === 'oldest') {
    $order_by = "ORDER BY p.id ASC";
} elseif ($p_sort === 'shelf_asc') {
    $order_by = "ORDER BY p.shelf_number ASC, p.id DESC";
} elseif ($p_sort === 'shelf_desc') {
    $order_by = "ORDER BY p.shelf_number DESC, p.id DESC";
} elseif ($p_sort === 'title_asc') {
    $order_by = "ORDER BY p.title ASC";
}

// Total records
if (!empty($params)) {
    $count_stmt = $db->prepare("SELECT COUNT(*) c FROM physical_books p $where_sql");
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total_books = (int)($count_stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $count_stmt->close();
} else {
    $total_books = (int)($db->query("SELECT COUNT(*) c FROM physical_books p $where_sql")->fetch_assoc()['c'] ?? 0);
}

$total_pages = ceil($total_books / $p_limit);
if ($total_pages < 1) $total_pages = 1;
if ($p_page > $total_pages) $p_page = $total_pages;
$p_offset = ($p_page - 1) * $p_limit;

// Fetch paginated books
if (!empty($params)) {
    $stmt = $db->prepare("SELECT p.*, EXISTS(SELECT 1 FROM lendings l WHERE l.physical_book_id = p.id AND l.returned_at IS NULL) busy FROM physical_books p $where_sql $order_by LIMIT ? OFFSET ?");
    $types_limit = $types . "ii";
    $bind_params = array_merge($params, [$p_limit, $p_offset]);
    $stmt->bind_param($types_limit, ...$bind_params);
} else {
    $stmt = $db->prepare("SELECT p.*, EXISTS(SELECT 1 FROM lendings l WHERE l.physical_book_id = p.id AND l.returned_at IS NULL) busy FROM physical_books p $where_sql $order_by LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $p_limit, $p_offset);
}
$stmt->execute();
$physical_books_result = $stmt->get_result();
$stmt->close();
?>

<!-- Availability Status Filter Pills Bar -->
<div class="card" style="margin-bottom:20px; background:var(--card-bg); padding:16px 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
        <div style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:700; color:var(--navy-dark);">
            <i class="fa-solid fa-filter" style="color:var(--primary);"></i> Availability Status:
        </div>
        
        <!-- Toggle Button Pills -->
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="?action=admin&tab=view_physical&p_status=all&p_shelf=<?= urlencode($p_shelf) ?>&p_search=<?= urlencode($p_search) ?>&p_sort=<?= urlencode($p_sort) ?>" 
               class="btn" 
               style="padding:7px 15px; font-size:12px; font-weight:600; border-radius:30px; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:all 0.2s ease; <?= $p_status === 'all' ? 'background:var(--navy-dark); color:white; box-shadow:0 4px 10px rgba(15,23,42,0.2);' : 'background:var(--bg-slate); color:var(--text-dark); border:1px solid var(--border-color);' ?>">
                <i class="fa-solid fa-layer-group"></i> All Books (<?= $tot_all ?>)
            </a>
            
            <a href="?action=admin&tab=view_physical&p_status=available&p_shelf=<?= urlencode($p_shelf) ?>&p_search=<?= urlencode($p_search) ?>&p_sort=<?= urlencode($p_sort) ?>" 
               class="btn" 
               style="padding:7px 15px; font-size:12px; font-weight:600; border-radius:30px; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:all 0.2s ease; <?= $p_status === 'available' ? 'background:var(--accent-green); color:white; box-shadow:0 4px 10px rgba(16,185,129,0.25);' : 'background:var(--bg-slate); color:var(--accent-green); border:1px solid var(--border-color);' ?>">
                <i class="fa-solid fa-circle-check"></i> Available Only (<?= $tot_available ?>)
            </a>
            
            <a href="?action=admin&tab=view_physical&p_status=issued&p_shelf=<?= urlencode($p_shelf) ?>&p_search=<?= urlencode($p_search) ?>&p_sort=<?= urlencode($p_sort) ?>" 
               class="btn" 
               style="padding:7px 15px; font-size:12px; font-weight:600; border-radius:30px; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:all 0.2s ease; <?= $p_status === 'issued' ? 'background:var(--accent-red); color:white; box-shadow:0 4px 10px rgba(239,68,68,0.25);' : 'background:var(--bg-slate); color:var(--accent-red); border:1px solid var(--border-color);' ?>">
                <i class="fa-solid fa-circle-minus"></i> Issued / Not Available (<?= $tot_issued ?>)
            </a>
        </div>
    </div>
</div>

<!-- Shelf Distribution Summary Counters -->
<div class="card" style="margin-bottom:20px; background:var(--card-bg); border-left:4px solid var(--primary);">
    <h4 style="margin:0 0 12px 0; font-size:14px; color:var(--navy-dark); display:flex; align-items:center; justify-content:space-between;">
        <span><i class="fa-solid fa-layer-group" style="color:var(--primary);"></i> Shelf Distribution Analytics & Inventory Count</span>
        <span class="badge badge-blue" style="font-size:11px; padding:3px 8px;"><?= count($shelf_counts) ?> Active Shelves</span>
    </h4>
    <div style="display:flex; flex-wrap:wrap; gap:10px;">
        <a href="?action=admin&tab=view_physical&p_status=<?= urlencode($p_status) ?>&p_search=<?= urlencode($p_search) ?>&p_sort=<?= urlencode($p_sort) ?>" 
           class="badge" 
           style="padding:6px 12px; font-size:12px; text-decoration:none; display:inline-flex; align-items:center; gap:6px; border:1px solid var(--border-color); <?= $p_shelf === '' ? 'background:var(--primary); color:white; font-weight:700;' : 'background:var(--bg-slate); color:var(--text-color);' ?>">
            <i class="fa-solid fa-border-all"></i> All Shelves (<?= array_sum(array_column($shelf_counts, 'cnt')) ?>)
        </a>
        <?php foreach ($shelf_counts as $sc): ?>
            <?php 
            $shelf_val = $sc['shelf'] === 'Unassigned' ? '__unassigned__' : $sc['shelf'];
            $is_active_shelf = ($p_shelf === $shelf_val);
            ?>
            <a href="?action=admin&tab=view_physical&p_shelf=<?= urlencode($shelf_val) ?>&p_status=<?= urlencode($p_status) ?>&p_search=<?= urlencode($p_search) ?>&p_sort=<?= urlencode($p_sort) ?>" 
               class="badge" 
               style="padding:6px 12px; font-size:12px; text-decoration:none; display:inline-flex; align-items:center; gap:6px; border:1px solid var(--border-color); <?= $is_active_shelf ? 'background:var(--primary); color:white; font-weight:700;' : 'background:var(--bg-slate); color:var(--text-color);' ?>">
                <i class="fa-solid fa-box-archive"></i> <?= e($sc['shelf']) ?> 
                <span style="background:rgba(0,0,0,0.15); padding:1px 6px; border-radius:10px; font-size:10px; font-weight:700;"><?= $sc['cnt'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Wide Physical Inventory Section -->
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:15px;">
        <h3 style="margin:0;"><i class="fa-solid fa-clipboard-list"></i> Physical Books Directory (<?= $total_books ?> Books Found)</h3>
        
        <!-- Live Filter / Search & Sorting form -->
        <form method="get" style="margin:0; display:flex; gap:8px; width:100%; max-width:650px; flex-wrap:wrap;">
            <input type="hidden" name="action" value="admin">
            <input type="hidden" name="tab" value="view_physical">
            <?php if ($p_shelf !== ''): ?>
                <input type="hidden" name="p_shelf" value="<?= e($p_shelf) ?>">
            <?php endif; ?>
            <?php if ($p_status !== 'all'): ?>
                <input type="hidden" name="p_status" value="<?= e($p_status) ?>">
            <?php endif; ?>
            
            <input type="text" name="p_search" value="<?= e($p_search) ?>" placeholder="Search Title, Code, Author, Shelf..." style="margin:0; font-size:13px; padding:8px 12px; flex:1; min-width:180px;">
            
            <!-- Sort dropdown -->
            <select name="p_sort" onchange="this.form.submit()" style="margin:0; font-size:13px; padding:8px 10px; width:auto;">
                <option value="newest" <?= $p_sort === 'newest' ? 'selected' : '' ?>>Sort: Newest First</option>
                <option value="oldest" <?= $p_sort === 'oldest' ? 'selected' : '' ?>>Sort: Oldest First</option>
                <option value="shelf_asc" <?= $p_sort === 'shelf_asc' ? 'selected' : '' ?>>Sort: Shelf (A &rarr; Z)</option>
                <option value="shelf_desc" <?= $p_sort === 'shelf_desc' ? 'selected' : '' ?>>Sort: Shelf (Z &rarr; A)</option>
                <option value="title_asc" <?= $p_sort === 'title_asc' ? 'selected' : '' ?>>Sort: Title (A &rarr; Z)</option>
            </select>

            <button class="btn" style="padding:8px 15px; margin:0; background:var(--navy-dark);"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
            <?php if ($p_search !== '' || $p_shelf !== '' || $p_status !== 'all' || $p_sort !== 'newest'): ?>
                <a href="?action=admin&tab=view_physical" class="btn" style="padding:8px 15px; margin:0; background:var(--accent-orange); display:inline-flex; align-items:center; justify-content:center;" title="Reset Filters"><i class="fa-solid fa-arrow-rotate-left"></i> Reset</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="table-responsive">
        <table id="physicalTable">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Book ID</th>
                    <th>
                        <a href="?action=admin&tab=view_physical&p_status=<?= urlencode($p_status) ?>&p_search=<?= urlencode($p_search) ?>&p_shelf=<?= urlencode($p_shelf) ?>&p_sort=<?= $p_sort === 'shelf_asc' ? 'shelf_desc' : 'shelf_asc' ?>" style="color:inherit; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                            Shelf No <i class="fa-solid fa-sort" style="font-size:11px; opacity:0.7;"></i>
                        </a>
                    </th>
                    <th>Cover Price</th>
                    <th>Author</th>
                    <th>Publisher</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($physical_books_result->num_rows === 0): ?>
                    <tr>
                        <td colspan="8" style="text-align:center; padding:30px; color:var(--text-muted);">
                            <i class="fa-solid fa-triangle-exclamation" style="font-size:24px; margin-bottom:10px; display:block;"></i>
                            No physical books found matching your filter criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while($r = $physical_books_result->fetch_assoc()): ?>
                        <?php 
                        $statusBadge = $r['busy'] 
                            ? '<span class="badge badge-red" style="font-size:11px; padding:3px 8px;"><i class="fa-solid fa-circle-minus"></i> Not Available</span>' 
                            : '<span class="badge badge-green" style="font-size:11px; padding:3px 8px;"><i class="fa-solid fa-circle-check"></i> Available</span>';
                        ?>
                        <tr>
                            <td style="font-weight:600; color:var(--navy-dark);"><?= e($r['title']) ?></td>
                            <td><code style="background:var(--bg-slate); padding:2px 6px; border-radius:4px; font-weight:700; font-size:12px; color:var(--navy-dark); border:1px solid var(--border-color);"><?= e($r['book_code']) ?></code></td>
                            <td>
                                <?php if (!empty($r['shelf_number'])): ?>
                                    <a href="?action=admin&tab=view_physical&p_status=<?= urlencode($p_status) ?>&p_shelf=<?= urlencode($r['shelf_number']) ?>" style="text-decoration:none;">
                                        <span class="badge badge-blue" style="font-size:11px; padding:3px 8px; font-weight:600;"><i class="fa-solid fa-layer-group"></i> <?= e($r['shelf_number']) ?></span>
                                    </a>
                                <?php else: ?>
                                    <span style="color:var(--text-muted); font-size:12px;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>₹<?= e(number_format($r['price'], 2)) ?></td>
                            <td><?= e($r['author'] ?: 'Unknown') ?></td>
                            <td><?= e($r['publisher'] ?: '—') ?></td>
                            <td><?= $statusBadge ?></td>
                            <td>
                                <div style="display:flex; gap:5px;">
                                    <a class="btn" style="background:var(--primary); padding:6px 12px; font-size:12px; display:inline-flex; align-items:center; gap:4px;" href="?action=admin&tab=physical&edit=<?= $r['id'] ?>"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                    <form method="post" action="?action=delete_physical" class="delete-form" style="display:inline; margin:0;">
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
        <div class="pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; flex-wrap:wrap; gap:15px; border-top:1px solid var(--border-color); padding-top:15px;">
            <div style="font-size:13px; color:var(--text-muted);">
                Showing <strong><?= $p_offset + 1 ?></strong> to <strong><?= min($p_offset + $p_limit, $total_books) ?></strong> of <strong><?= $total_books ?></strong> physical books
            </div>
            <div class="pagination" style="display:flex; align-items:center; gap:6px;">
                <!-- First Page Button -->
                <?php if ($p_page > 1): ?>
                    <a href="?action=admin&tab=view_physical&p_page=1&p_search=<?= urlencode($p_search) ?>&p_shelf=<?= urlencode($p_shelf) ?>&p_status=<?= urlencode($p_status) ?>&p_sort=<?= urlencode($p_sort) ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="First Page"><i class="fa-solid fa-angles-left"></i></a>
                    <a href="?action=admin&tab=view_physical&p_page=<?= $p_page - 1 ?>&p_search=<?= urlencode($p_search) ?>&p_shelf=<?= urlencode($p_shelf) ?>&p_status=<?= urlencode($p_status) ?>&p_sort=<?= urlencode($p_sort) ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Previous Page"><i class="fa-solid fa-angle-left"></i> Prev</a>
                <?php else: ?>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-left"></i></span>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angle-left"></i> Prev</span>
                <?php endif; ?>

                <!-- Page numbers (smart sliding window) -->
                <?php 
                $start_p = max(1, $p_page - 2);
                $end_p = min($total_pages, $p_page + 2);
                for($i = $start_p; $i <= $end_p; $i++): 
                ?>
                    <?php if ($i == $p_page): ?>
                        <span class="btn" style="padding:6px 12px; background:var(--primary); color:white; font-size:12px; font-weight:700; border-radius:6px;"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?action=admin&tab=view_physical&p_page=<?= $i ?>&p_search=<?= urlencode($p_search) ?>&p_shelf=<?= urlencode($p_shelf) ?>&p_status=<?= urlencode($p_status) ?>&p_sort=<?= urlencode($p_sort) ?>" class="btn" style="padding:6px 12px; background:var(--bg-slate); color:var(--text-color); font-size:12px; border-radius:6px;"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <!-- Next Page Button -->
                <?php if ($p_page < $total_pages): ?>
                    <a href="?action=admin&tab=view_physical&p_page=<?= $p_page + 1 ?>&p_search=<?= urlencode($p_search) ?>&p_shelf=<?= urlencode($p_shelf) ?>&p_status=<?= urlencode($p_status) ?>&p_sort=<?= urlencode($p_sort) ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Next Page">Next <i class="fa-solid fa-angle-right"></i></a>
                    <a href="?action=admin&tab=view_physical&p_page=<?= $total_pages ?>&p_search=<?= urlencode($p_search) ?>&p_shelf=<?= urlencode($p_shelf) ?>&p_status=<?= urlencode($p_status) ?>&p_sort=<?= urlencode($p_sort) ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="Last Page"><i class="fa-solid fa-angles-right"></i></a>
                <?php else: ?>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;">Next <i class="fa-solid fa-angle-right"></i></span>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-right"></i></span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// views/admin/view_physical.php
if (!defined('BASE_URL')) exit;

$search = trim($_GET['search'] ?? '');
$type = $_GET['type'] ?? 'title';

if ($search !== '') {
    $likeSearch = '%' . $search . '%';
    if ($type === 'book_code') {
        $stmt = $db->prepare("SELECT p.*, EXISTS(SELECT 1 FROM lendings l WHERE l.physical_book_id = p.id AND l.returned_at IS NULL) busy FROM physical_books p WHERE p.book_code LIKE ? ORDER BY p.title");
    } elseif ($type === 'author') {
        $stmt = $db->prepare("SELECT p.*, EXISTS(SELECT 1 FROM lendings l WHERE l.physical_book_id = p.id AND l.returned_at IS NULL) busy FROM physical_books p WHERE p.author LIKE ? ORDER BY p.title");
    } else {
        $stmt = $db->prepare("SELECT p.*, EXISTS(SELECT 1 FROM lendings l WHERE l.physical_book_id = p.id AND l.returned_at IS NULL) busy FROM physical_books p WHERE p.title LIKE ? ORDER BY p.title");
    }
    $stmt->bind_param("s", $likeSearch);
} else {
    $stmt = $db->prepare("SELECT p.*, EXISTS(SELECT 1 FROM lendings l WHERE l.physical_book_id = p.id AND l.returned_at IS NULL) busy FROM physical_books p ORDER BY p.title");
}
$stmt->execute();
$x = $stmt->get_result();
$stmt->close();

// Summary Stats
$total_books = $db->query('SELECT COUNT(*) c FROM physical_books')->fetch_assoc()['c']; 
$lent_books = $db->query('SELECT COUNT(DISTINCT physical_book_id) c FROM lendings WHERE returned_at IS NULL')->fetch_assoc()['c']; 
$available_books = $total_books - $lent_books;
?>
<div class="card">
    <h3><i class="fa-solid fa-magnifying-glass"></i> Search Book Code Directory</h3>
    <form method="get" class="grid">
        <input type="hidden" name="action" value="admin">
        <input type="hidden" name="tab" value="view_physical">
        <div>
            <label for="srch_term">Search Query</label>
            <input id="srch_term" name="search" value="<?= e($search) ?>" placeholder="Title, Author, or Book Code...">
        </div>
        <div>
            <label for="srch_type">Match Type</label>
            <select id="srch_type" name="type">
                <option value="title" <?= $type === 'title' ? 'selected' : '' ?>>Book Title</option>
                <option value="book_code" <?= $type === 'book_code' ? 'selected' : '' ?>>Book ID / Barcode</option>
                <option value="author" <?= $type === 'author' ? 'selected' : '' ?>>Author</option>
            </select>
        </div>
        <div style="display:flex; align-items:flex-end;">
            <button style="width:100%;"><i class="fa-solid fa-circle-check"></i> Filter Directory</button>
        </div>
    </form>
</div>

<div class="card">
    <h3><i class="fa-solid fa-warehouse"></i> Inventory Distribution Metrics</h3>
    <p style="font-size:16px;">
        Total Volumes: <strong><?= $total_books ?></strong> &nbsp;|&nbsp; 
        Available: <strong style="color:var(--accent-green);"><?= $available_books ?></strong> &nbsp;|&nbsp; 
        Currently Checked Out: <strong style="color:var(--accent-red);"><?= $lent_books ?></strong>
    </p>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Book ID</th>
                    <th>Author</th>
                    <th>Publisher</th>
                    <th>Cover Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                while($r = $x->fetch_assoc()) {
                    $statusBadge = $r['busy'] 
                        ? '<span class="badge badge-red"><i class="fa-solid fa-circle-minus"></i> Not Available</span>' 
                        : '<span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> Available</span>';
                    echo '
                    <tr>
                        <td>' . e($r['title']) . '</td>
                        <td>' . e($r['book_code']) . '</td>
                        <td>' . e($r['author']) . '</td>
                        <td>' . e($r['publisher']) . '</td>
                        <td>₹' . e($r['price']) . '</td>
                        <td>' . $statusBadge . '</td>
                    </tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

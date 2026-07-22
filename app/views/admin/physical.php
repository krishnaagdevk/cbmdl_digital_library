<?php
// views/admin/physical.php
if (!defined('BASE_URL')) exit;

$editId = (int)($_GET['edit'] ?? 0);
$editBook = null;
if ($editId) {
    $pbStmt = $db->prepare("SELECT * FROM physical_books WHERE id = ?");
    $pbStmt->bind_param("i", $editId);
    $pbStmt->execute();
    $editBook = $pbStmt->get_result()->fetch_assoc();
    $pbStmt->close();
}

// Fetch latest 5 physical books for quick management
$latest_physical_result = $db->query("SELECT p.*, EXISTS(SELECT 1 FROM lendings l WHERE l.physical_book_id = p.id AND l.returned_at IS NULL) busy FROM physical_books p ORDER BY p.id DESC LIMIT 5");
?>

<div class="grid">
    <?php if ($editBook): ?>
        <div class="card">
            <h3><i class="fa-solid fa-user-pen"></i> Edit Physical Inventory — <?= e($editBook['title']) ?></h3>
            <form method="post" action="?action=update_physical">
                <?= csrf_input() ?>
                <input type="hidden" name="id" value="<?= $editBook['id'] ?>">
                
                <label for="shelf_number"><i class="fa-solid fa-layer-group"></i> Shelf Number</label>
                <input id="shelf_number" name="shelf_number" value="<?= e($editBook['shelf_number'] ?? '') ?>" placeholder="Input Shelf Number">

                <label for="book_code"><i class="fa-solid fa-barcode"></i> Bar Code / Book ID *</label>
                <input id="book_code" name="book_code" value="<?= e($editBook['book_code']) ?>" placeholder="Input Book Code" required>
                
                <label for="title">Title of Book *</label>
                <input id="title" name="title" value="<?= e($editBook['title']) ?>" placeholder="Book Title Name" required>
                
                <label for="price">Cover Price (INR) *</label>
                <input id="price" type="number" step=".01" name="price" value="<?= e($editBook['price']) ?>" placeholder="Price (₹)" required>
                
                <label for="author">Author Name</label>
                <input id="author" name="author" value="<?= e($editBook['author']) ?>" placeholder="Author name">
                
                <label for="publisher">Publisher Name</label>
                <input id="publisher" name="publisher" value="<?= e($editBook['publisher']) ?>" placeholder="Publisher company">
                
                <button><i class="fa-solid fa-circle-check"></i> Update Physical Book</button>
                <a href="?action=admin&tab=physical" class="btn" style="background:var(--navy-light); margin-left: 10px;"><i class="fa-solid fa-arrow-left"></i> Cancel Edit</a>
            </form>
        </div>
    <?php else: ?>
        <div class="card">
            <h3><i class="fa-solid fa-square-plus"></i> Add Physical Books</h3>
            <form method="post" action="?action=add_physical">
                <?= csrf_input() ?>
                <label for="shelf_number"><i class="fa-solid fa-layer-group"></i> Shelf Number</label>
                <input id="shelf_number" name="shelf_number" placeholder="Input Shelf Number">

                <label for="book_code"><i class="fa-solid fa-barcode"></i> Bar Code / Book ID *</label>
                <input id="book_code" name="book_code" placeholder="Input Book Code" required>
                
                <label for="title">Title of Book *</label>
                <input id="title" name="title" placeholder="Book Title Name" required>
                
                <label for="price">Cover Price (INR) *</label>
                <input id="price" type="number" step=".01" name="price" placeholder="Price (₹)" required>
                
                <label for="author">Author Name</label>
                <input id="author" name="author" placeholder="Author name">
                
                <label for="publisher">Publisher Name</label>
                <input id="publisher" name="publisher" placeholder="Publisher company">
                
                <button><i class="fa-solid fa-bookmark"></i> Save Physical Book</button>
            </form>
        </div>

        <!-- Bulk CSV Tools for Physical Books -->
        <div class="card" style="border: 1px dashed var(--border-color); background: var(--bg-slate);">
            <h3 style="margin-top:0; color:var(--navy-dark);"><i class="fa-solid fa-file-csv" style="color: #10b981;"></i> Bulk CSV Catalog & Local Backups</h3>
            <p style="font-size:12px; color:var(--text-muted); margin-bottom:15px;">Export your physical books inventory to CSV spreadsheets or batch-import new catalog logs instantly.</p>
            <div style="display:flex; gap:10px; margin-bottom:15px; flex-wrap:wrap;">
                <a href="?action=export_physical_csv" class="btn" style="background:var(--navy-dark); font-size:12px; display:inline-flex; align-items:center; gap:5px;"><i class="fa-solid fa-download"></i> Export Inventory CSV</a>
            </div>
            <form method="post" action="?action=import_books_csv" enctype="multipart/form-data" style="border-top:1px solid var(--border-color); padding-top:15px; margin:0;">
                <?= csrf_input() ?>
                <input type="hidden" name="import_type" value="physical">
                <label for="import_csv_phys" style="font-size:12px; font-weight:700;">Select CSV Spreadsheet to Ingest</label>
                <div style="display:flex; gap:10px; align-items:center; margin-top:5px;">
                    <input type="file" id="import_csv_phys" name="csv_file" accept=".csv, .txt, text/csv, text/plain" required style="margin:0; font-size:12px; padding:6px 10px; flex:1;">
                    <button class="btn" type="submit" style="font-size:12px; padding:8px 15px; background:var(--primary);"><i class="fa-solid fa-file-import"></i> Batch Import</button>
                </div>
                <p style="font-size:10px; color:var(--text-muted); margin: 6px 0 0 0;"><i class="fa-solid fa-circle-info"></i> CSV Format: <code>Category, Book Code, Title, Author, Publisher, Price, Shelf Number</code> (First row treated as header & skipped)</p>
            </form>
        </div>
    <?php endif; ?>
</div>

<!-- Recently Added Physical Books (Latest 5) -->
<div class="card" style="margin-top: 25px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:15px;">
        <h3 style="margin:0;"><i class="fa-solid fa-clock-rotate-left"></i> Recently Added Physical Books (Latest 5)</h3>
        <a href="?action=admin&tab=view_physical" class="btn" style="padding:8px 16px; background:var(--primary); font-size:13px; display:inline-flex; align-items:center; gap:6px;"><i class="fa-solid fa-book"></i> View All Physical Books &rarr;</a>
    </div>
    
    <div class="table-responsive">
        <table id="physicalTable">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Book ID</th>
                    <th>Shelf No</th>
                    <th>Cover Price</th>
                    <th>Author</th>
                    <th>Publisher</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($latest_physical_result->num_rows === 0): ?>
                    <tr>
                        <td colspan="8" style="text-align:center; padding:30px; color:var(--text-muted);">
                            <i class="fa-solid fa-triangle-exclamation" style="font-size:24px; margin-bottom:10px; display:block;"></i>
                            No physical books added yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while($r = $latest_physical_result->fetch_assoc()): ?>
                        <?php 
                        $statusBadge = $r['busy'] 
                            ? '<span class="badge badge-red" style="font-size:11px; padding:3px 8px;"><i class="fa-solid fa-circle-minus"></i> Not Available</span>' 
                            : '<span class="badge badge-green" style="font-size:11px; padding:3px 8px;"><i class="fa-solid fa-circle-check"></i> Available</span>';
                        ?>
                        <tr>
                            <td style="font-weight:600; color:var(--navy-dark);"><?= e($r['title']) ?></td>
                            <td><code style="background:var(--bg-slate); padding:2px 6px; border-radius:4px; font-weight:700; font-size:12px; color:var(--navy-dark); border:1px solid var(--border-color);"><?= e($r['book_code']) ?></code></td>
                            <td><span class="badge badge-blue" style="font-size:11px; padding:3px 8px; font-weight:600;"><?= e($r['shelf_number'] ?: '—') ?></span></td>
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
</div>

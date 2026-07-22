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

// 1. Pagination & Search parameters
$p_search = trim($_GET['p_search'] ?? '');
$p_page = (int)($_GET['p_page'] ?? 1);
if ($p_page < 1) $p_page = 1;
$p_limit = 10; // Number of items per page

// Constructing filter queries
$where_clause = "";
$params = [];
$types = "";
if ($p_search !== '') {
    $where_clause = "WHERE p.title LIKE ? OR p.book_code LIKE ? OR p.author LIKE ? OR p.publisher LIKE ?";
    $like_search = '%' . $p_search . '%';
    $params = [$like_search, $like_search, $like_search, $like_search];
    $types = "ssss";
}

// Total records
if ($p_search !== '') {
    $count_stmt = $db->prepare("SELECT COUNT(*) c FROM physical_books p $where_clause");
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total_books = $count_stmt->get_result()->fetch_assoc()['c'];
    $count_stmt->close();
} else {
    $total_books = $db->query("SELECT COUNT(*) c FROM physical_books")->fetch_assoc()['c'];
}

$total_pages = ceil($total_books / $p_limit);
if ($total_pages < 1) $total_pages = 1;
if ($p_page > $total_pages) $p_page = $total_pages;
$p_offset = ($p_page - 1) * $p_limit;

// Fetch paginated books
if ($p_search !== '') {
    $stmt = $db->prepare("SELECT p.*, EXISTS(SELECT 1 FROM lendings l WHERE l.physical_book_id = p.id AND l.returned_at IS NULL) busy FROM physical_books p $where_clause ORDER BY p.id DESC LIMIT ? OFFSET ?");
    $types_limit = $types . "ii";
    $bind_params = array_merge($params, [$p_limit, $p_offset]);
    $stmt->bind_param($types_limit, ...$bind_params);
} else {
    $stmt = $db->prepare("SELECT p.*, EXISTS(SELECT 1 FROM lendings l WHERE l.physical_book_id = p.id AND l.returned_at IS NULL) busy FROM physical_books p ORDER BY p.id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $p_limit, $p_offset);
}
$stmt->execute();
$physical_books_result = $stmt->get_result();
$stmt->close();
?>

<div class="grid">
    <?php if ($editBook): ?>
        <div class="card">
            <h3><i class="fa-solid fa-user-pen"></i> Edit Physical Inventory — <?= e($editBook['title']) ?></h3>
            <form method="post" action="?action=update_physical">
                <?= csrf_input() ?>
                <input type="hidden" name="id" value="<?= $editBook['id'] ?>">
                
                <label for="book_code"><i class="fa-solid fa-barcode"></i> Bar Code / Book ID *</label>
                <input id="book_code" name="book_code" value="<?= e($editBook['book_code']) ?>" placeholder="Input Book Code / ISBN" required>
                
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
            <h3><i class="fa-solid fa-square-plus"></i> Add Physical Inventory</h3>
            <form method="post" action="?action=add_physical">
                <?= csrf_input() ?>
                <label for="book_code"><i class="fa-solid fa-barcode"></i> Bar Code / Book ID *</label>
                <input id="book_code" name="book_code" placeholder="Input Book Code / ISBN" required>
                
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
                <p style="font-size:10px; color:var(--text-muted); margin: 6px 0 0 0;"><i class="fa-solid fa-circle-info"></i> CSV Format: <code>Category, Book Code, Title, Author, Publisher, Price, Rack Location</code> (First row treated as header & skipped)</p>
            </form>
        </div>
    <?php endif; ?>
</div>

<!-- Wide Physical Inventory Section placed cleanly at the bottom -->
<div class="card" style="margin-top: 25px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:15px;">
        <h3 style="margin:0;"><i class="fa-solid fa-clipboard-list"></i> Physical Inventory Directory (<?= $total_books ?> Total Volumes)</h3>
        
        <!-- Live Filter / Search form -->
        <form method="get" style="margin:0; display:flex; gap:8px; width:100%; max-width:400px;">
            <input type="hidden" name="action" value="admin">
            <input type="hidden" name="tab" value="physical">
            <input type="text" name="p_search" value="<?= e($p_search) ?>" placeholder="Search Title, Author, or Code..." style="margin:0; font-size:13px; padding:8px 12px; flex:1;">
            <button class="btn" style="padding:8px 15px; margin:0; background:var(--navy-dark);"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            <?php if ($p_search !== ''): ?>
                <a href="?action=admin&tab=physical" class="btn" style="padding:8px 15px; margin:0; background:var(--accent-orange); display:inline-flex; align-items:center; justify-content:center;" title="Clear Search"><i class="fa-solid fa-arrow-rotate-left"></i></a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="table-responsive">
        <table id="physicalTable">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Book ID</th>
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
                        <td colspan="7" style="text-align:center; padding:30px; color:var(--text-muted);">
                            <i class="fa-solid fa-triangle-exclamation" style="font-size:24px; margin-bottom:10px; display:block;"></i>
                            No physical books found matching your criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while($r = $physical_books_result->fetch_assoc()): ?>
                        <?php 
                        $statusBadge = $r['busy'] 
                            ? '<span class="badge badge-red" style="font-size:11px; padding:3px 8px;"><i class="fa-solid fa-circle-minus"></i> Checked Out</span>' 
                            : '<span class="badge badge-green" style="font-size:11px; padding:3px 8px;"><i class="fa-solid fa-circle-check"></i> Available</span>';
                        ?>
                        <tr>
                            <td style="font-weight:600; color:var(--navy-dark);"><?= e($r['title']) ?></td>
                            <td><code style="background:var(--bg-slate); padding:2px 6px; border-radius:4px; font-weight:700; font-size:12px; color:var(--navy-dark); border:1px solid var(--border-color);"><?= e($r['book_code']) ?></code></td>
                            <td>₹<?= e(number_format($r['price'], 2)) ?></td>
                            <td><?= e($r['author'] ?: 'Unknown') ?></td>
                            <td><?= e($r['publisher'] ?: '—') ?></td>
                            <td><?= $statusBadge ?></td>
                            <td>
                                <div style="display:flex; gap:5px;">
                                    <button class="btn" style="background:#10b981; padding:6px 12px; font-size:12px; display:inline-flex; align-items:center; gap:4px;" onclick="printBarcodeLabel('<?= e(addslashes($r['title'])) ?>', '<?= e(addslashes($r['author'])) ?>', '<?= e($r['book_code']) ?>')"><i class="fa-solid fa-barcode"></i> Label</button>
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
                    <a href="?action=admin&tab=physical&p_page=1&p_search=<?= urlencode($p_search) ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="First Page"><i class="fa-solid fa-angles-left"></i></a>
                    <a href="?action=admin&tab=physical&p_page=<?= $p_page - 1 ?>&p_search=<?= urlencode($p_search) ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Previous Page"><i class="fa-solid fa-angle-left"></i> Prev</a>
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
                        <a href="?action=admin&tab=physical&p_page=<?= $i ?>&p_search=<?= urlencode($p_search) ?>" class="btn" style="padding:6px 12px; background:var(--bg-slate); color:var(--text-color); font-size:12px; border-radius:6px;"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <!-- Next Page Button -->
                <?php if ($p_page < $total_pages): ?>
                    <a href="?action=admin&tab=physical&p_page=<?= $p_page + 1 ?>&p_search=<?= urlencode($p_search) ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Next Page">Next <i class="fa-solid fa-angle-right"></i></a>
                    <a href="?action=admin&tab=physical&p_page=<?= $total_pages ?>&p_search=<?= urlencode($p_search) ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="Last Page"><i class="fa-solid fa-angles-right"></i></a>
                <?php else: ?>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;">Next <i class="fa-solid fa-angle-right"></i></span>
                    <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-right"></i></span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function printBarcodeLabel(title, author, code) {
    const popup = window.open('', '_blank', 'width=400,height=300');
    popup.document.write(`
        <html>
        <head>
            <title>Label - ${code}</title>
            <style>
                body {
                    margin: 0;
                    padding: 15px;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    text-align: center;
                    background: white;
                }
                .label-box {
                    border: 1px solid #333;
                    border-radius: 8px;
                    padding: 10px;
                    width: 280px;
                    margin: 0 auto;
                    box-sizing: border-box;
                }
                .header {
                    font-size: 9px;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    color: #111;
                    border-bottom: 1px solid #ddd;
                    padding-bottom: 4px;
                    margin-bottom: 6px;
                }
                .title {
                    font-size: 11px;
                    font-weight: 700;
                    margin: 0;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }
                .author {
                    font-size: 9px;
                    color: #555;
                    margin: 2px 0 8px 0;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }
                .barcode-stripes {
                    display: flex;
                    justify-content: center;
                    align-items: flex-end;
                    height: 30px;
                    background: white;
                    padding: 2px;
                    border: 1px solid #ccc;
                    border-radius: 3px;
                    margin: 0 auto;
                    width: 180px;
                }
                .code-text {
                    font-family: 'Courier New', Courier, monospace;
                    font-size: 10px;
                    font-weight: 700;
                    margin-top: 4px;
                    display: block;
                    letter-spacing: 1px;
                }
                @media print {
                    body { padding: 0; }
                    .label-box { border: none; }
                }
            </style>
        </head>
        <body onload="window.print(); window.close();">
            <div class="label-box">
                <div class="header">🏛️ Meerut Cantonment Board e-Library</div>
                <div class="title">${title}</div>
                <div class="author">By ${author || 'Unknown'}</div>
                <div class="barcode-stripes">
                    <!-- Pure HTML-CSS simulated barcode stripes -->
                    <div style="width:2px; height:100%; background:black; margin-right:1px;"></div>
                    <div style="width:1px; height:100%; background:black; margin-right:1px;"></div>
                    <div style="width:3px; height:100%; background:black; margin-right:1px;"></div>
                    <div style="width:1px; height:100%; background:black; margin-right:2px;"></div>
                    <div style="width:2px; height:100%; background:black; margin-right:1px;"></div>
                    <div style="width:4px; height:100%; background:black; margin-right:1px;"></div>
                    <div style="width:1px; height:100%; background:black; margin-right:1px;"></div>
                    <div style="width:2px; height:100%; background:black; margin-right:2px;"></div>
                    <div style="width:3px; height:100%; background:black; margin-right:1px;"></div>
                    <div style="width:1px; height:100%; background:black; margin-right:1px;"></div>
                    <div style="width:2px; height:100%; background:black; margin-right:1px;"></div>
                    <div style="width:1px; height:100%; background:black; margin-right:2px;"></div>
                    <div style="width:4px; height:100%; background:black; margin-right:1px;"></div>
                    <div style="width:2px; height:100%; background:black; margin-right:1px;"></div>
                    <div style="width:1px; height:100%; background:black; margin-right:1px;"></div>
                </div>
                <span class="code-text">${code}</span>
            </div>
        </body>
        </html>
    `);
    popup.document.close();
}
</script>

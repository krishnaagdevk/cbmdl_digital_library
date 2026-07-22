<?php
// app/views/user/physical_books.php
if (!defined('BASE_URL')) exit;

$m_search = trim($_GET['p_search'] ?? '');
?>
<div class="card">
    <h3><i class="fa-solid fa-magnifying-glass"></i> Explore Physical Book Inventory</h3>
    <form method="get" class="grid">
        <input type="hidden" name="action" value="user">
        <input type="hidden" name="tab" value="physical_books">
        <div style="grid-column: span 2;">
            <label for="p_sc_term">Search Title, Author, Publisher, or Book Code</label>
            <input id="p_sc_term" name="p_search" value="<?= e($m_search) ?>" placeholder="Type title, author, publisher, book code keywords...">
        </div>
        <div style="display:flex; align-items:flex-end;">
            <button style="width:100%;"><i class="fa-solid fa-filter"></i> Search Catalog</button>
        </div>
    </form>
</div>

<!-- Real-time Filter Search Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('p_sc_term');
    searchInput.addEventListener('keyup', function() {
        const value = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.physical-book-card');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(value)) {
                row.style.display = 'flex';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>

<h3 style="margin-top:25px; margin-bottom:15px;"><i class="fa-solid fa-book"></i> Available Physical Catalog</h3>

<!-- Catalog Grid -->
<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:20px;">
    <?php
    $sql = "SELECT p.* FROM physical_books p ORDER BY p.title";
    if ($m_search !== '') {
        $stmt = $db->prepare("SELECT p.* FROM physical_books p WHERE p.title LIKE ? OR p.author LIKE ? OR p.publisher LIKE ? OR p.book_code LIKE ? ORDER BY p.title");
        $like = "%" . $m_search . "%";
        $stmt->bind_param("ssss", $like, $like, $like, $like);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
    } else {
        $res = $db->query($sql);
    }

    if ($res->num_rows === 0) {
        echo '<div class="card" style="grid-column: 1 / -1; text-align:center; padding:40px; color:var(--text-muted);"><i class="fa-solid fa-book-open" style="font-size:32px; margin-bottom:10px;"></i><p>No physical books match your search or catalog is empty.</p></div>';
    } else {
        while ($b = $res->fetch_assoc()) {
            // Check active lending record to determine availability
            $lendStmt = $db->prepare("SELECT id FROM lendings WHERE physical_book_id = ? AND returned_at IS NULL LIMIT 1");
            $lendStmt->bind_param("i", $b['id']);
            $lendStmt->execute();
            $on_loan = $lendStmt->get_result()->fetch_assoc();
            $lendStmt->close();

            $status_badge = $on_loan 
                ? '<span class="badge badge-red" style="font-size:11px; padding:4px 10px;"><i class="fa-solid fa-circle-minus"></i> Not Available</span>'
                : '<span class="badge badge-green" style="font-size:11px; padding:4px 10px;"><i class="fa-solid fa-circle-check"></i> Available</span>';
            ?>
            <div class="card physical-book-card" style="display:flex; flex-direction:column; justify-content:space-between; height:100%; padding:20px; transition:all 0.3s ease; border-radius:12px; border:1px solid var(--border-color); background:var(--card-bg);" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                <div>
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                        <span style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;"><i class="fa-solid fa-hashtag"></i> <?= e($b['book_code']) ?></span>
                        <?= $status_badge ?>
                    </div>
                    <h4 style="font-size:16px; margin:0 0 8px 0; font-weight:700; color:var(--navy-dark); line-height:1.4;"><?= e($b['title']) ?></h4>
                    <p style="font-size:13px; margin:0 0 6px 0; color:var(--text-muted);"><i class="fa-solid fa-user-tie"></i> <strong>Author:</strong> <?= e($b['author']) ?></p>
                    <p style="font-size:13px; margin:0 0 6px 0; color:var(--text-muted);"><i class="fa-solid fa-warehouse"></i> <strong>Publisher:</strong> <?= e($b['publisher']) ?></p>
                    <p style="font-size:13px; margin:0 0 6px 0; color:var(--text-muted);"><i class="fa-solid fa-receipt"></i> <strong>Cost:</strong> ₹<?= number_style_format($b['price']) ?></p>
                </div>
                <div style="margin-top:12px; padding-top:10px; border-top:1px solid var(--border-color); font-size:12px; text-align:center;">
                    <?php if ($on_loan): ?>
                        <span style="color:var(--accent-red); font-weight:600;"><i class="fa-solid fa-circle-minus"></i> Currently Issued to Member</span>
                    <?php else: ?>
                        <span style="color:var(--accent-green); font-weight:600;"><i class="fa-solid fa-circle-check"></i> Available at Library Desk</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    }
    ?>
</div>

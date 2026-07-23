<?php
// views/home.php
if (!defined('BASE_URL')) exit;

$q = trim($_GET['kiosk_search'] ?? '');
$books = [];
if ($q !== '') {
    $search_term = "%$q%";
    $stmt = $db->prepare("SELECT p.*, 
        (SELECT l.due_date FROM lendings l WHERE l.physical_book_id = p.id AND l.returned_at IS NULL LIMIT 1) as due_date
        FROM physical_books p 
        WHERE p.title LIKE ? OR p.author LIKE ? OR p.book_code LIKE ? OR p.publisher LIKE ?
        ORDER BY p.title ASC");
    $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
    $stmt->execute();
    $books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $res = $db->query("SELECT p.*,
        (SELECT l.due_date FROM lendings l WHERE l.physical_book_id = p.id AND l.returned_at IS NULL LIMIT 1) as due_date
        FROM physical_books p 
        ORDER BY p.id DESC LIMIT 6");
    if ($res) {
        $books = $res->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<div style="text-align: center; margin-bottom: 30px;">
    <h1 style="font-size: 2.5rem; font-weight: 800; color: var(--navy-dark); margin: 0 0 10px 0; letter-spacing: -0.5px;">Meerut Cantonment Board e-Library Portal</h1>
</div>

<div class="grid" style="grid-template-columns: 2fr 1.2fr; gap: 30px; align-items: start; margin-bottom: 40px;">
    <!-- Kiosk / Guest Search Panel -->
    <div>
        <div class="card" style="margin-bottom: 25px; border-left: 4px solid var(--primary);">
            <h3 style="margin-top:0; color:var(--navy-dark); display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-desktop" style="color:var(--primary);"></i> Walk-In Guest Search Kiosk</h3>
            <p style="font-size:13px; color:var(--text-muted); margin-bottom:15px;">Search physical books currently stocked in our cantonment facility. Locate their shelf numbers and verify availability in real-time.</p>
            
            <form method="get" action="" style="display:flex; gap:10px; margin-bottom:0;">
                <input type="text" name="kiosk_search" value="<?= e($q) ?>" placeholder="Type Title, Author, Genre, or ISBN..." style="flex:1; margin-bottom:0; font-size:14px; padding:12px 15px;">
                <button type="submit" style="margin:0; padding:12px 25px;"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                <?php if ($q !== ''): ?>
                    <a href="?" class="btn" style="background:var(--navy-light); margin:0; padding:12px 20px; display:inline-flex; align-items:center;"><i class="fa-solid fa-arrows-rotate"></i> Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <h3 style="color:var(--navy-dark); margin-bottom:15px; font-weight:700;"><i class="fa-solid fa-book-bookmark"></i> <?= $q !== '' ? 'Search Results Match' : 'Recently Cataloged Books' ?> (<?= count($books) ?>)</h3>
        
        <?php if (count($books) === 0): ?>
            <div class="card" style="text-align:center; padding:50px 20px; color:var(--text-muted);">
                <i class="fa-solid fa-magnifying-glass-minus" style="font-size:40px; margin-bottom:15px; opacity:0.5;"></i>
                <p style="font-size:15px; margin:0; font-weight:600;">No physical volumes matched your search terms inside the catalog database.</p>
                <p style="font-size:12px; margin:5px 0 0 0;">Try searching by broader terms or contact the help desk.</p>
            </div>
        <?php else: ?>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                <?php foreach ($books as $b): 
                    $on_loan = !empty($b['due_date']);
                    $status_text = $on_loan ? 'Not Available' : 'Available';
                    $status_class = $on_loan ? 'badge-red' : 'badge-green';
                    $status_icon = $on_loan ? 'fa-circle-minus' : 'fa-circle-check';
                    $rack = 'Shelf A1';
                ?>
                    <div class="card" style="padding:15px; border-radius:10px; display:flex; flex-direction:column; justify-content:space-between; height:100%; border: 1px solid var(--border-color); background:var(--card-bg); transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                <span style="font-size:10px; font-weight:700; color:var(--text-muted); text-transform:uppercase;"><code><?= e($b['book_code']) ?></code></span>
                                <span class="badge <?= $status_class ?>" style="font-size:10px; padding:3px 8px;"><i class="fa-solid <?= $status_icon ?>"></i> <?= $status_text ?></span>
                            </div>
                            <h4 style="font-size:14px; margin:0 0 6px 0; font-weight:700; color:var(--navy-dark); line-height:1.4;"><?= e($b['title']) ?></h4>
                            <p style="font-size:12px; margin:0 0 4px 0; color:var(--text-muted);"><strong>Author:</strong> <?= e($b['author']) ?></p>
                            <p style="font-size:12px; margin:0 0 4px 0; color:var(--text-muted);"><strong>Publisher:</strong> <?= e($b['publisher'] ?? 'General') ?></p>
                        </div>
                        <div style="margin-top:12px; padding-top:8px; border-top:1px solid var(--border-color); font-size:11px; color:var(--text-muted); display:flex; justify-content:space-between; align-items:center;">
                            <span><i class="fa-solid fa-map-pin"></i> <strong>Rack:</strong> <?= $rack ?></span>
                            <?php if ($on_loan): ?>
                                <?php
                                $due_time = strtotime($b['due_date']);
                                $today_time = strtotime(date('Y-m-d'));
                                $days_diff = (int)floor(($due_time - $today_time) / 86400);
                                $due_color = ($days_diff < 0) ? 'var(--accent-red)' : (($days_diff <= 3) ? 'var(--accent-orange)' : 'var(--primary)');
                                $due_label = ($days_diff < 0) ? ' (Overdue)' : (($days_diff <= 3) ? ($days_diff == 0 ? ' (Due Today)' : ' (' . $days_diff . 'd left)') : '');
                                ?>
                                <span style="color:<?= $due_color ?>; font-weight:600;"><i class="fa-solid fa-clock"></i> Due <?= date('d-m-Y', strtotime($b['due_date'])) ?><?= $due_label ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Login / Contact Sidebar -->
    <div>
        <div class="card" style="margin-bottom:25px; border-top: 4px solid var(--primary); text-align: center;">
            <h3 style="margin-top:0; color:var(--navy-dark); font-weight:700;"><i class="fa-solid fa-right-to-bracket" style="color:var(--primary);"></i> Portal Gateways</h3>
            <p style="font-size:12px; color:var(--text-muted); margin-bottom:20px;">Access your digital library account or perform library administrative duties.</p>
            
            <div style="display:flex; flex-direction:column; gap:12px;">
                <a href="member-login" class="btn" style="padding:12px; font-weight:600; display:flex; align-items:center; justify-content:center; gap:8px; margin:0; text-decoration:none; width:100%; box-sizing:border-box;">
                    <i class="fa-solid fa-user-graduate"></i> Member Portal Login
                </a>
                <a href="admin-login" class="btn btn-secondary" style="padding:12px; font-weight:600; display:flex; align-items:center; justify-content:center; gap:8px; margin:0; text-decoration:none; background:var(--bg-slate); border:1px solid var(--border-color); color:var(--navy-dark); width:100%; box-sizing:border-box;">
                    <i class="fa-solid fa-user-shield"></i> Librarian Login
                </a>
            </div>
        </div>

        <div class="card" style="background:var(--bg-slate); border: 1px solid var(--border-color);">
            <h4 style="margin-top:0; color:var(--navy-dark); font-weight:700;"><i class="fa-solid fa-building-columns" style="color:var(--primary);"></i> MCB Help Desk</h4>
            <p style="font-size:12px; color:var(--text-muted); margin-bottom:15px; line-height:1.5;">For registration approvals, membership card print passes, or book issues, visit the librarian desk.</p>
            <div style="display:flex; flex-direction:column; gap:10px; font-size:12px; color:var(--navy-dark);">
                <div><i class="fa-solid fa-envelope" style="color:var(--primary); width:16px;"></i> <code>cbmeerut1@gmail.com</code></div>
                <div><i class="fa-solid fa-phone" style="color:var(--primary); width:16px;"></i> <code>Helpline number 0121-2652292</code></div>
            </div>
            <div style="margin-top:15px; display:flex; flex-direction:column; gap:5px;">
                <a href="https://meerut.cantt.gov.in/" target="_blank" class="btn" style="background:white; color:var(--navy-dark); border:1px solid var(--border-color); text-align:left; font-size:11px; padding:8px 12px; display:flex; justify-content:space-between; align-items:center; margin:0;">
                    <span><i class="fa-solid fa-globe"></i> MCB Official Website</span>
                    <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
                </a>
                <a href="https://echhawani.gov.in/" target="_blank" class="btn" style="background:white; color:var(--navy-dark); border:1px solid var(--border-color); text-align:left; font-size:11px; padding:8px 12px; display:flex; justify-content:space-between; align-items:center; margin:0;">
                    <span><i class="fa-solid fa-circle-info"></i> E-CHHAWANI Portal</span>
                    <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
                </a>
            </div>
        </div>
    </div>
</div>

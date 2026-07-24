<?php
// views/user/dashboard.php
if (!defined('BASE_URL')) exit;

$mid = (int)$_SESSION['member'];

// 1. Fetch member stats
// Active Issued Books (returned_at IS NULL)
$stmtAct = $db->prepare("SELECT COUNT(*) c FROM lendings WHERE member_id = ? AND returned_at IS NULL");
$stmtAct->bind_param("i", $mid);
$stmtAct->execute();
$activeIssuedCount = $stmtAct->get_result()->fetch_assoc()['c'] ?? 0;
$stmtAct->close();

// Total Books Read (E-Books Approved or Completed/Expired)
$stmtE = $db->prepare("SELECT COUNT(*) c FROM reading_requests WHERE member_id = ? AND (approved_at IS NOT NULL OR status IN ('Approved', 'Expired'))");
$stmtE->bind_param("i", $mid);
$stmtE->execute();
$totalEbooksRead = $stmtE->get_result()->fetch_assoc()['c'] ?? 0;
$stmtE->close();

// Total Print Requests
$stmtP = $db->prepare("SELECT COUNT(*) c FROM print_requests WHERE member_id = ?");
$stmtP->bind_param("i", $mid);
$stmtP->execute();
$totalPrints = $stmtP->get_result()->fetch_assoc()['c'] ?? 0;
$stmtP->close();

// Total Physical Books Borrowed Lifetime
$stmtTotPhys = $db->prepare("SELECT COUNT(*) c FROM lendings WHERE member_id = ?");
$stmtTotPhys->bind_param("i", $mid);
$stmtTotPhys->execute();
$totalPhysBorrowed = $stmtTotPhys->get_result()->fetch_assoc()['c'] ?? 0;
$stmtTotPhys->close();

// 2. Total Library Catalog Counts
$totCatalogEbooks = (int)($db->query("SELECT COUNT(*) c FROM ebooks")->fetch_assoc()['c'] ?? 0);
$totCatalogPhysical = (int)($db->query("SELECT COUNT(*) c FROM physical_books")->fetch_assoc()['c'] ?? 0);

// 3. Fetch Recent Physical Lending History (Limit 5)
$recentLending = [];
$stmtL = $db->prepare("SELECT l.*, p.title, p.book_code 
                       FROM lendings l 
                       JOIN physical_books p ON p.id = l.physical_book_id 
                       WHERE l.member_id = ? 
                       ORDER BY l.lent_at DESC LIMIT 5");
$stmtL->bind_param("i", $mid);
$stmtL->execute();
$resL = $stmtL->get_result();
while ($row = $resL->fetch_assoc()) {
    $recentLending[] = $row;
}
$stmtL->close();

// 4. Fetch Recent E-Book Reading Requests (Limit 5)
$recentReading = [];
$stmtR = $db->prepare("SELECT r.*, e.title, c.name as category_name 
                       FROM reading_requests r 
                       JOIN ebooks e ON e.id = r.ebook_id 
                       LEFT JOIN categories c ON c.id = e.category_id 
                       WHERE r.member_id = ? 
                       ORDER BY r.id DESC LIMIT 5");
$stmtR->bind_param("i", $mid);
$stmtR->execute();
$resR = $stmtR->get_result();
while ($row = $resR->fetch_assoc()) {
    $recentReading[] = $row;
}
$stmtR->close();
?>

<style>
.member-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-bottom: 25px;
}
@media (max-width: 1024px) {
    .member-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 640px) {
    .member-stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Market Standard Dashboard Stat Cards (3 per row) -->
<div class="member-stats-grid">
    
    <div class="stat-card card" style="background:var(--card-bg); border-left: 5px solid #0284c7; padding: 18px; display:flex; align-items:center; gap:16px;">
        <div style="width:48px; height:48px; border-radius:12px; background:rgba(2, 132, 199, 0.1); color:#0284c7; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0;">
            <i class="fa-solid fa-book-bookmark"></i>
        </div>
        <div>
            <span style="font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; display:block;">Total E-Books</span>
            <h3 style="margin:2px 0 0 0; font-size:22px; font-weight:700; color:var(--navy-dark);"><?= $totCatalogEbooks ?></h3>
        </div>
    </div>
        <div class="stat-card card" style="background:var(--card-bg); border-left: 5px solid var(--accent-green); padding: 18px; display:flex; align-items:center; gap:16px;">
        <div style="width:48px; height:48px; border-radius:12px; background:rgba(16, 185, 129, 0.1); color:var(--accent-green); display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0;">
            <i class="fa-solid fa-book-open"></i>
        </div>
        <div>
            <span style="font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; display:block;">E-Books Read</span>
            <h3 style="margin:2px 0 0 0; font-size:22px; font-weight:700; color:var(--navy-dark);"><?= $totalEbooksRead ?></h3>
        </div>
    </div>
        <div class="stat-card card" style="background:var(--card-bg); border-left: 5px solid var(--accent-orange); padding: 18px; display:flex; align-items:center; gap:16px;">
        <div style="width:48px; height:48px; border-radius:12px; background:rgba(245, 158, 11, 0.1); color:var(--accent-orange); display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0;">
            <i class="fa-solid fa-print"></i>
        </div>
        <div>
            <span style="font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; display:block;">Total Print Requests</span>
            <h3 style="margin:2px 0 0 0; font-size:22px; font-weight:700; color:var(--navy-dark);"><?= $totalPrints ?></h3>
        </div>
    </div>

    <div class="stat-card card" style="background:var(--card-bg); border-left: 5px solid #7c3aed; padding: 18px; display:flex; align-items:center; gap:16px;">
        <div style="width:48px; height:48px; border-radius:12px; background:rgba(124, 58, 237, 0.1); color:#7c3aed; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0;">
            <i class="fa-solid fa-book-atlas"></i>
        </div>
        <div>
            <span style="font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; display:block;">Total Physical Books</span>
            <h3 style="margin:2px 0 0 0; font-size:22px; font-weight:700; color:var(--navy-dark);"><?= $totCatalogPhysical ?></h3>
        </div>
    </div>



    <div class="stat-card card" style="background:var(--card-bg); border-left: 5px solid var(--primary); padding: 18px; display:flex; align-items:center; gap:16px;">
        <div style="width:48px; height:48px; border-radius:12px; background:rgba(37, 99, 235, 0.1); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0;">
            <i class="fa-solid fa-book"></i>
        </div>
        <div>
            <span style="font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; display:block;">Currently Issued</span>
            <h3 style="margin:2px 0 0 0; font-size:22px; font-weight:700; color:var(--navy-dark);"><?= $activeIssuedCount ?> <span style="font-size:12px; font-weight:500; color:var(--text-muted);">(Physical Books)</span></h3>
        </div>
    </div>

    <div class="stat-card card" style="background:var(--card-bg); border-left: 5px solid #8b5cf6; padding: 18px; display:flex; align-items:center; gap:16px;">
        <div style="width:48px; height:48px; border-radius:12px; background:rgba(139, 92, 246, 0.1); color:#8b5cf6; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0;">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div>
            <span style="font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; display:block;">Total Borrowed</span>
            <h3 style="margin:2px 0 0 0; font-size:22px; font-weight:700; color:var(--navy-dark);"><?= $totalPhysBorrowed ?> <span style="font-size:12px; font-weight:500; color:var(--text-muted);">(Physical Books)</span></h3>
        </div>
    </div>



</div>

<!-- Recent Activity Grids -->
<div class="grid" style="grid-template-columns: 1fr; gap: 25px;">

    <!-- Recent Physical Book Lending History -->
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h3 style="margin:0;"><i class="fa-solid fa-clipboard-list" style="color:var(--primary);"></i> Physical Book Lending History</h3>
            <a href="?action=user&tab=lending" class="btn" style="padding:5px 12px; font-size:12px; background:var(--bg-slate); color:var(--navy-dark); border:1px solid var(--border-color);"><i class="fa-solid fa-arrow-right"></i> View Full History</a>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Book Name</th>
                        <th>Book Code</th>
                        <th>Lending Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentLending)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted);">No physical book issue history found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentLending as $lh): ?>
                            <?php 
                            $due_time = strtotime($lh['due_date']);
                            $isOverdue = (empty($lh['returned_at']) && time() > $due_time);
                            ?>
                            <tr>
                                <td><strong style="color:var(--navy-dark);"><?= e($lh['title']) ?></strong></td>
                                <td><code><?= e($lh['book_code']) ?></code></td>
                                <td><?= date('d-m-Y', strtotime($lh['lent_at'])) ?></td>
                                <td>
                                    <span style="<?= $isOverdue ? 'color:var(--accent-red); font-weight:700;' : '' ?>">
                                        <?= date('d-m-Y', $due_time) ?>
                                    </span>
                                </td>
                                <td><?= $lh['returned_at'] ? date('d-m-Y', strtotime($lh['returned_at'])) : '-' ?></td>
                                <td>
                                    <?php if ($lh['returned_at']): ?>
                                        <span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> Returned</span>
                                    <?php elseif ($isOverdue): ?>
                                        <span class="badge badge-red"><i class="fa-solid fa-circle-exclamation"></i> Overdue</span>
                                    <?php else: ?>
                                        <span class="badge badge-blue"><i class="fa-solid fa-book-reader"></i> Issued</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent E-Book Reading History -->
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h3 style="margin:0;"><i class="fa-solid fa-book-open" style="color:var(--accent-green);"></i> Recent E-Book Reading Requests</h3>
            <a href="?action=user&tab=reading_history" class="btn" style="padding:5px 12px; font-size:12px; background:var(--bg-slate); color:var(--navy-dark); border:1px solid var(--border-color);"><i class="fa-solid fa-arrow-right"></i> View All Requests</a>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>E-Book Title</th>
                        <th>Requested On</th>
                        <th>Access Expiry</th>
                        <th>Approval Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentReading)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding:30px; color:var(--text-muted);">No e-book reading requests found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentReading as $rh): ?>
                            <?php 
                            $isExp = ($rh['status'] === 'Expired') || (!empty($rh['expires_at']) && strtotime($rh['expires_at']) <= time());
                            $hasStarted = !empty($rh['started_reading_at']);

                            if ($rh['status'] === 'Pending') {
                                $badgeHtml = '<span class="badge badge-orange"><i class="fa-solid fa-hourglass-half"></i> Pending</span>';
                            } elseif ($rh['status'] === 'Approved' && !$isExp) {
                                $badgeHtml = '<span class="badge badge-green"><i class="fa-solid fa-book-open-reader"></i> Active Reading</span>';
                            } elseif ($rh['status'] === 'Rejected') {
                                $badgeHtml = '<span class="badge badge-red"><i class="fa-solid fa-circle-xmark"></i> Permission Denied</span>';
                            } elseif ($isExp) {
                                if ($hasStarted) {
                                    $badgeHtml = '<span class="badge" style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; font-weight:700;"><i class="fa-solid fa-circle-check"></i> Completed Reading</span>';
                                } else {
                                    $badgeHtml = '<span class="badge badge-red" style="background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;"><i class="fa-solid fa-clock-rotate-left"></i> Session Expired</span>';
                                }
                            } else {
                                $badgeHtml = '<span class="badge badge-blue">' . e($rh['status']) . '</span>';
                            }

                            if (!empty($rh['expires_at'])) {
                                $expiryHtml = '<span style="font-size:12px; font-weight:600; color:var(--navy-dark);">' . date('d-m-Y h:i A', strtotime($rh['expires_at'])) . '</span>';
                            } elseif (!empty($rh['approved_at'])) {
                                $expiryHtml = '<span style="font-size:12px; font-weight:600; color:var(--navy-dark);">' . (int)$rh['duration_minutes'] . ' mins</span>';
                            } else {
                                $expiryHtml = '<span style="color:var(--text-muted);">-</span>';
                            }
                            ?>
                            <tr>
                                <td>
                                    <strong style="color:var(--navy-dark);"><?= e($rh['title']) ?></strong>
                                    <span style="font-size:11px; color:var(--text-muted); display:block;"><?= e($rh['category_name'] ?? 'General') ?></span>
                                </td>
                                <td><?= date('d-m-Y h:i A', strtotime($rh['requested_at'])) ?></td>
                                <td><?= $expiryHtml ?></td>
                                <td><?= $badgeHtml ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

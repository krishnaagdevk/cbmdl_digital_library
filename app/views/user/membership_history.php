<?php
// views/user/membership_history.php
if (!defined('BASE_URL')) exit;

$mid = (int)($_SESSION['member'] ?? $_SESSION['member_id'] ?? 0);

// Fetch logged in member's full history
$stmt = $db->prepare("SELECT * FROM membership_history WHERE member_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $mid);
$stmt->execute();
$historyRes = $stmt->get_result();

// Current active plan info
$meStmt = $db->prepare("SELECT m.*, p.name as plan_name FROM members m LEFT JOIN membership_plans p ON m.membership_plan_id = p.id WHERE m.id = ?");
$meStmt->bind_param("i", $mid);
$meStmt->execute();
$me = $meStmt->get_result()->fetch_assoc() ?: [];
$meStmt->close();

$isExpired = !empty($me['end_date']) && $me['end_date'] < date('Y-m-d');
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
        <div>
            <h3 style="margin:0;"><i class="fa-solid fa-id-card-clip"></i> My Membership & Renewal History</h3>
            <p style="margin:5px 0 0 0; font-size:12px; color:var(--text-muted);">View all past renewals and validity terms for your e-Library pass.</p>
        </div>
    </div>

    <!-- History Table -->
    <div style="overflow-x:auto;">
        <table class="table" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:var(--bg-slate); text-align:left; font-size:12px; color:var(--text-muted);">
                    <th style="padding:12px;">#</th>
                    <th style="padding:12px;">Action Type</th>
                    <th style="padding:12px;">Plan & Duration</th>
                    <th style="padding:12px;">Validity Term</th>
                    <th style="padding:12px;">Shift</th>
                    <th style="padding:12px;">Fee Paid</th>
                    <th style="padding:12px;">Payment Ref ID</th>
                    <th style="padding:12px;">Issue Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($historyRes->num_rows === 0): ?>
                    <tr>
                        <td colspan="8" style="text-align:center; padding:30px; color:var(--text-muted);">
                            <i class="fa-solid fa-clock-rotate-left" style="font-size:32px; margin-bottom:10px; display:block; opacity:0.5;"></i>
                            No membership history entries logged yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $count = 1; while ($row = $historyRes->fetch_assoc()): ?>
                        <?php 
                        $todayStr = date('Y-m-d');
                        $startDate = $row['start_date'] ?? '';
                        $endDate = $row['end_date'] ?? '';

                        $isCurrent = (!empty($startDate) && !empty($endDate) && $startDate <= $todayStr && $endDate >= $todayStr);
                        $isUpcoming = (!empty($startDate) && $startDate > $todayStr);
                        ?>
                        <tr style="border-bottom:1px solid var(--border-color); font-size:13px;">
                            <td style="padding:12px; color:var(--text-muted); font-weight:600;"><?= $count++ ?></td>
                            <td style="padding:12px;">
                                <?php if ($row['action_type'] === 'Initial Joining'): ?>
                                    <span class="badge badge-blue" style="font-size:11px; padding:4px 10px; font-weight:600;"><i class="fa-solid fa-user-plus"></i> Initial Joining</span>
                                <?php elseif ($row['action_type'] === 'Renewal'): ?>
                                    <span class="badge" style="font-size:11px; padding:4px 10px; font-weight:600; background:#dcfce7; color:#15803d; border:1px solid #bbf7d0;"><i class="fa-solid fa-rotate-right"></i> Renewal</span>
                                <?php else: ?>
                                    <span class="badge badge-orange" style="font-size:11px; padding:4px 10px; font-weight:600;"><?= e($row['action_type']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:12px;">
                                <strong style="color:var(--navy-dark);"><?= e(!empty($row['duration']) ? $row['duration'] : $row['plan_name']) ?></strong>
                            </td>
                            <td style="padding:12px;">
                                <div style="font-weight:600; font-size:12px;">
                                    <?= date('d M Y', strtotime($row['start_date'])) ?> &rarr; <?= date('d M Y', strtotime($row['end_date'])) ?>
                                </div>
                                <div style="margin-top:2px;">
                                    <?php if ($isCurrent): ?>
                                        <span class="badge badge-green" style="font-size:9px; padding:1px 5px;"><i class="fa-solid fa-check"></i> Current Term</span>
                                    <?php elseif ($isUpcoming): ?>
                                        <span class="badge badge-blue" style="font-size:9px; padding:1px 5px; background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd;"><i class="fa-solid fa-calendar-check"></i> Upcoming Term</span>
                                    <?php else: ?>
                                        <span class="badge badge-red" style="font-size:9px; padding:1px 5px;"><i class="fa-solid fa-clock"></i> Expired</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="padding:12px; font-size:12px;"><?= e($row['shift']) ?> Shift</td>
                            <td style="padding:12px;">
                                <strong style="color:#16a34a; font-size:14px;">₹<?= number_format($row['membership_fee'], 2) ?></strong>
                            </td>
                            <td style="padding:12px; font-family:monospace; font-size:12px; color:var(--text-muted);">
                                <?= e($row['payment_id'] ?? 'N/A') ?>
                            </td>
                            <td style="padding:12px; color:var(--text-muted); font-size:12px;">
                                <?= date('d M Y, h:i A', strtotime($row['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

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

$isExpired = !empty($me['end_date']) && strtotime($me['end_date']) < strtotime(date('Y-m-d'));
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
        <div>
            <h3 style="margin:0;"><i class="fa-solid fa-id-card-clip"></i> My Membership & Renewal History</h3>
            <p style="margin:5px 0 0 0; font-size:12px; color:var(--text-muted);">View all past renewals and validity terms for your e-Library pass.</p>
        </div>
    </div>

    <!-- Active Membership Overview Card -->
    <div style="background:var(--bg-slate); border:1px solid var(--border-color); border-radius:12px; padding:20px; margin-bottom:25px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px;">
        <div style="display:flex; align-items:center; gap:15px;">
            <div style="width:50px; height:50px; border-radius:50%; background:var(--primary); color:white; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:700;">
                <i class="fa-solid fa-id-card"></i>
            </div>
            <div>
                <span style="font-size:11px; text-transform:uppercase; font-weight:700; color:var(--text-muted); letter-spacing:0.5px;">Current Active Pass</span>
                <h4 style="margin:2px 0 0 0; color:var(--navy-dark); font-size:16px;">
                    <?= e($me['membership_id'] ?? 'N/A') ?> — <?= e(!empty($me['plan_name']) ? $me['plan_name'] : ($me['duration'] ?? 'N/A')) ?>
                </h4>
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">
                    <span><i class="fa-solid fa-sun"></i> <?= e($me['shift'] ?? 'Both') ?> Shift</span>
                    <?php if (!empty($me['start_date']) && !empty($me['end_date'])): ?>
                        <span style="margin-left:10px;"><i class="fa-solid fa-calendar"></i> Valid: <?= date('d M Y', strtotime($me['start_date'])) ?> to <?= date('d M Y', strtotime($me['end_date'])) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div>
            <?php if (isset($me['is_active']) && $me['is_active'] == 0): ?>
                <span class="badge badge-red" style="font-size:13px; padding:8px 14px;"><i class="fa-solid fa-circle-xmark"></i> Pass Suspended</span>
            <?php elseif ($isExpired): ?>
                <span class="badge badge-red" style="font-size:13px; padding:8px 14px;"><i class="fa-solid fa-circle-exclamation"></i> Pass Expired</span>
            <?php else: ?>
                <span class="badge badge-green" style="font-size:13px; padding:8px 14px;"><i class="fa-solid fa-circle-check"></i> Pass Active</span>
            <?php endif; ?>
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
                    <th style="padding:12px;">Date Recorded</th>
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
                        $isCurrent = (strtotime($row['start_date']) <= time() && strtotime($row['end_date']) >= time());
                        ?>
                        <tr style="border-bottom:1px solid var(--border-color); font-size:13px;">
                            <td style="padding:12px; color:var(--text-muted); font-weight:600;"><?= $count++ ?></td>
                            <td style="padding:12px;">
                                <?php if ($row['action_type'] === 'Initial Joining'): ?>
                                    <span class="badge badge-blue" style="font-size:11px; padding:4px 8px;"><i class="fa-solid fa-user-plus"></i> Initial Joining</span>
                                <?php elseif ($row['action_type'] === 'Renewal'): ?>
                                    <span class="badge badge-green" style="font-size:11px; padding:4px 8px;"><i class="fa-solid fa-rotate"></i> Renewal</span>
                                <?php else: ?>
                                    <span class="badge badge-orange" style="font-size:11px; padding:4px 8px;"><?= e($row['action_type']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:12px;">
                                <strong style="color:var(--navy-dark);"><?= e(!empty($row['plan_name']) ? $row['plan_name'] : $row['duration']) ?></strong>
                                <span style="font-size:11px; color:var(--text-muted); display:block; margin-top:2px;"><?= e($row['duration']) ?> Term</span>
                            </td>
                            <td style="padding:12px;">
                                <div style="font-weight:600; font-size:12px;">
                                    <?= date('d M Y', strtotime($row['start_date'])) ?> &rarr; <?= date('d M Y', strtotime($row['end_date'])) ?>
                                </div>
                                <div style="margin-top:2px;">
                                    <?php if ($isCurrent): ?>
                                        <span class="badge badge-green" style="font-size:9px; padding:1px 5px;"><i class="fa-solid fa-check"></i> Current Term</span>
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

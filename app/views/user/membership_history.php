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

// Check pending online renewal request
$pReqStmt = $db->prepare("SELECT r.*, p.name as plan_name, p.amount, p.duration FROM renewal_requests r JOIN membership_plans p ON r.membership_plan_id = p.id WHERE r.member_id = ? AND r.status = 'Pending' ORDER BY r.id DESC LIMIT 1");
$pReqStmt->bind_param("i", $mid);
$pReqStmt->execute();
$pendingReq = $pReqStmt->get_result()->fetch_assoc();
$pReqStmt->close();

// Active plans for modal
$plansRes = $db->query("SELECT * FROM membership_plans ORDER BY amount ASC");
$activePlans = [];
while ($p = $plansRes->fetch_assoc()) $activePlans[] = $p;

// Active shifts for modal
$shiftsRes = $db->query("SELECT * FROM work_shifts ORDER BY start_time ASC");
$activeShifts = [];
while ($s = $shiftsRes->fetch_assoc()) $activeShifts[] = $s;
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
        <div>
            <h3 style="margin:0;"><i class="fa-solid fa-id-card-clip"></i> My Membership & Renewal History</h3>
            <p style="margin:5px 0 0 0; font-size:12px; color:var(--text-muted);">View all past renewals, validity terms, and official fee payment receipts for your e-Library pass.</p>
        </div>
        <div>
            <?php if ($pendingReq): ?>
                <button class="btn" style="background:var(--accent-orange); opacity:0.9; cursor:not-allowed;" disabled>
                    <i class="fa-solid fa-hourglass-half"></i> Renewal Pending Approval
                </button>
            <?php else: ?>
                <button onclick="openRenewModal()" class="btn" style="background:var(--primary); font-weight:600;">
                    <i class="fa-solid fa-rotate-right"></i> Request Online Pass Renewal
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($pendingReq): ?>
        <!-- Pending Renewal Request Notice -->
        <div style="background:#fffbeb; border:1px solid #fde68a; border-left:5px solid var(--accent-orange); border-radius:10px; padding:16px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
            <div>
                <div style="display:flex; align-items:center; gap:8px; font-weight:700; color:#b45309; font-size:14px;">
                    <i class="fa-solid fa-clock-rotate-left"></i> Online Renewal Request Under Review
                </div>
                <div style="font-size:12px; color:#92400e; margin-top:4px;">
                    Requested Plan: <strong><?= e($pendingReq['plan_name']) ?> (<?= e($pendingReq['duration']) ?>, ₹<?= number_format($pendingReq['amount'], 2) ?>)</strong> | Shift: <strong><?= e($pendingReq['shift']) ?></strong> | Payment Ref: <code style="background:rgba(255,255,255,0.7); padding:2px 6px; border-radius:4px; font-weight:700;"><?= e($pendingReq['payment_id']) ?></code>
                </div>
            </div>
            <span class="badge badge-orange" style="padding:6px 12px; font-size:12px;"><i class="fa-solid fa-hourglass-half"></i> Awaiting Librarian Approval</span>
        </div>
    <?php endif; ?>

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
                    <th style="padding:12px; text-align:right;">Receipt</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($historyRes->num_rows === 0): ?>
                    <tr>
                        <td colspan="9" style="text-align:center; padding:30px; color:var(--text-muted);">
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
                            <td style="padding:12px; text-align:right;">
                                <button onclick='printReceipt(<?= json_encode([
                                    "memberName" => $me["name"] ?? "Member",
                                    "membershipId" => $me["membership_id"] ?? "N/A",
                                    "mobile" => $me["mobile"] ?? "N/A",
                                    "actionType" => $row["action_type"],
                                    "planName" => $row["plan_name"] ?: $row["duration"],
                                    "duration" => $row["duration"],
                                    "shift" => $row["shift"],
                                    "startDate" => date("d M Y", strtotime($row["start_date"])),
                                    "endDate" => date("d M Y", strtotime($row["end_date"])),
                                    "fee" => number_format($row["membership_fee"], 2),
                                    "paymentId" => $row["payment_id"] ?: "N/A",
                                    "date" => date("d M Y, h:i A", strtotime($row["created_at"]))
                                ]) ?>)' class="btn" style="padding:4px 8px; font-size:11px; background:var(--bg-slate); color:var(--navy-dark); border:1px solid var(--border-color);">
                                    <i class="fa-solid fa-print"></i> Receipt
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Online Renewal Request -->
<div id="renewModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center; padding:15px;">
    <div style="background:white; border-radius:12px; max-width:480px; width:100%; padding:25px; box-shadow:0 10px 25px rgba(0,0,0,0.2); position:relative;">
        <button onclick="closeRenewModal()" style="position:absolute; top:15px; right:15px; background:none; border:none; font-size:18px; cursor:pointer; color:var(--text-muted);">&times;</button>
        <h3 style="margin-top:0; color:var(--navy-dark);"><i class="fa-solid fa-rotate-right"></i> Request Online Membership Renewal</h3>
        <p style="font-size:12px; color:var(--text-muted); margin-bottom:20px;">Select your desired renewal pass plan, work shift, and enter your UPI / Net Banking UTR Transaction Reference ID.</p>

        <form method="post" action="?action=request_renewal">
            <?= csrf_input() ?>
            <div style="margin-bottom:15px;">
                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:5px;">Select Membership Plan *</label>
                <select name="plan_id" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border-color); font-size:13px;">
                    <option value="">-- Choose Renewal Plan --</option>
                    <?php foreach ($activePlans as $plan): ?>
                        <option value="<?= $plan['id'] ?>" <?= ($me['membership_plan_id'] ?? 0) == $plan['id'] ? 'selected' : '' ?>>
                            <?= e($plan['name']) ?> — ₹<?= number_format($plan['amount'], 2) ?> (<?= e($plan['duration']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:5px;">Select Shift Timing *</label>
                <select name="shift" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border-color); font-size:13px;">
                    <?php if (empty($activeShifts)): ?>
                        <option value="Morning" <?= ($me['shift'] ?? '') === 'Morning' ? 'selected' : '' ?>>Morning Shift</option>
                        <option value="Evening" <?= ($me['shift'] ?? '') === 'Evening' ? 'selected' : '' ?>>Evening Shift</option>
                        <option value="Both" <?= ($me['shift'] ?? '') === 'Both' ? 'selected' : '' ?>>Both Shifts</option>
                    <?php else: ?>
                        <?php foreach ($activeShifts as $shift): ?>
                            <option value="<?= e($shift['name']) ?>" <?= ($me['shift'] ?? '') === $shift['name'] ? 'selected' : '' ?>>
                                <?= e($shift['name']) ?> Shift (<?= date('h:i A', strtotime($shift['start_time'])) ?> - <?= date('h:i A', strtotime($shift['end_time'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:5px;">Payment Transaction / UTR Reference ID *</label>
                <input type="text" name="payment_id" placeholder="e.g. UPI / UTR 328109849201" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border-color); font-size:13px; text-transform:uppercase;">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeRenewModal()" class="btn" style="background:var(--bg-slate); color:var(--text-color);">Cancel</button>
                <button type="submit" class="btn" style="background:var(--primary);"><i class="fa-solid fa-paper-plane"></i> Submit Renewal Request</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Printable Official Payment Receipt -->
<div id="receiptModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center; padding:15px;">
    <div style="background:white; border-radius:12px; max-width:520px; width:100%; padding:30px; box-shadow:0 10px 25px rgba(0,0,0,0.3); position:relative; font-family:sans-serif;">
        <button onclick="closeReceiptModal()" style="position:absolute; top:15px; right:15px; background:none; border:none; font-size:20px; cursor:pointer; color:#666;">&times;</button>
        
        <div id="printableReceiptArea">
            <div style="text-align:center; border-bottom:2px solid #2563eb; padding-bottom:15px; margin-bottom:20px;">
                <h2 style="margin:0; font-size:18px; color:#1e293b; text-transform:uppercase; font-weight:800;">CBMDL Digital Library</h2>
                <div style="font-size:11px; color:#64748b; font-weight:600; margin-top:3px;">MEERUT CANTONMENT BOARD</div>
                <div style="font-size:13px; font-weight:700; color:#2563eb; margin-top:8px; letter-spacing:1px; text-transform:uppercase;">Official Fee Payment Receipt</div>
            </div>

            <table style="width:100%; border-collapse:collapse; font-size:13px; margin-bottom:20px;">
                <tr><td style="padding:6px 0; color:#64748b; width:40%;">Member Name:</td><td style="padding:6px 0; font-weight:700; color:#0f172a;" id="rcptMemberName">-</td></tr>
                <tr><td style="padding:6px 0; color:#64748b;">Membership ID:</td><td style="padding:6px 0; font-weight:700; color:#0f172a;" id="rcptMembershipId">-</td></tr>
                <tr><td style="padding:6px 0; color:#64748b;">Action Type:</td><td style="padding:6px 0; font-weight:700; color:#2563eb;" id="rcptActionType">-</td></tr>
                <tr><td style="padding:6px 0; color:#64748b;">Plan & Duration:</td><td style="padding:6px 0; font-weight:700; color:#0f172a;" id="rcptPlanName">-</td></tr>
                <tr><td style="padding:6px 0; color:#64748b;">Shift Allocated:</td><td style="padding:6px 0; font-weight:700; color:#0f172a;" id="rcptShift">-</td></tr>
                <tr><td style="padding:6px 0; color:#64748b;">Validity Term:</td><td style="padding:6px 0; font-weight:700; color:#0f172a;" id="rcptValidity">-</td></tr>
                <tr><td style="padding:6px 0; color:#64748b;">Amount Paid:</td><td style="padding:6px 0; font-weight:800; font-size:16px; color:#16a34a;" id="rcptFee">-</td></tr>
                <tr><td style="padding:6px 0; color:#64748b;">Transaction UTR / ID:</td><td style="padding:6px 0; font-family:monospace; font-weight:700; color:#0f172a;" id="rcptPaymentId">-</td></tr>
                <tr><td style="padding:6px 0; color:#64748b;">Date & Time:</td><td style="padding:6px 0; color:#64748b;" id="rcptDate">-</td></tr>
            </table>

            <div style="text-align:center; border-top:1px dashed #cbd5e1; padding-top:12px; font-size:11px; color:#94a3b8;">
                This is a computer-generated official payment receipt issued by CBMDL e-Library.
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;" class="no-print">
            <button type="button" onclick="closeReceiptModal()" class="btn" style="background:#f1f5f9; color:#334155;">Close</button>
            <button type="button" onclick="window.print()" class="btn" style="background:#2563eb; color:white;"><i class="fa-solid fa-print"></i> Print Receipt</button>
        </div>
    </div>
</div>

<script>
function openRenewModal() {
    document.getElementById('renewModal').style.display = 'flex';
}
function closeRenewModal() {
    document.getElementById('renewModal').style.display = 'none';
}
if (window.location.hash === '#renew_modal') {
    openRenewModal();
}

function printReceipt(data) {
    document.getElementById('rcptMemberName').innerText = data.memberName;
    document.getElementById('rcptMembershipId').innerText = data.membershipId;
    document.getElementById('rcptActionType').innerText = data.actionType;
    document.getElementById('rcptPlanName').innerText = data.planName + ' (' + data.duration + ')';
    document.getElementById('rcptShift').innerText = data.shift + ' Shift';
    document.getElementById('rcptValidity').innerText = data.startDate + ' to ' + data.endDate;
    document.getElementById('rcptFee').innerText = '₹' + data.fee;
    document.getElementById('rcptPaymentId').innerText = data.paymentId;
    document.getElementById('rcptDate').innerText = data.date;
    document.getElementById('receiptModal').style.display = 'flex';
}
function closeReceiptModal() {
    document.getElementById('receiptModal').style.display = 'none';
}
</script>

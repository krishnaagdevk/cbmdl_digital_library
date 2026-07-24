<?php
// views/admin/view_members.php
if (!defined('BASE_URL')) exit;

$viewId = (int)($_GET['view'] ?? 0);
$selected = null;
if ($viewId) {
    $stmt = $db->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->bind_param("i", $viewId);
    $stmt->execute();
    $selected = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<?php if ($selected): ?>
    <div class="card">
        <h3><i class="fa-solid fa-user-pen"></i> Update Member Profile & Scope — <?= e($selected['membership_id']) ?></h3>
        <div class="grid" style="grid-template-columns: 1.2fr 1fr; gap: 30px;">
            <div>
                <h4><i class="fa-solid fa-address-card"></i> Personal details</h4>
                <form method="post" action="?action=update_member&tab=<?= $tab ?>">
                    <?= csrf_input() ?>
                    <input type="hidden" name="id" value="<?= $selected['id'] ?>">
                    
                    <label for="upd_name">Full Name *</label>
                    <input id="upd_name" name="name" value="<?= e($selected['name']) ?>" required>
                    
                    <label for="upd_gender">Gender *</label>
                    <select id="upd_gender" name="gender" required>
                        <option value="Male" <?= ($selected['gender'] ?? 'Male') === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= ($selected['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= ($selected['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Others</option>
                    </select>
                    
                    <label for="upd_guardian">Father / Husband Name *</label>
                    <input id="upd_guardian" name="guardian_name" value="<?= e($selected['guardian_name']) ?>" required>
                    
                    <label for="upd_mobile">Mobile Number *</label>
                    <input id="upd_mobile" name="mobile" value="<?= e($selected['mobile']) ?>" required>
                    
                    <label for="upd_email">Email Address</label>
                    <input id="upd_email" type="email" name="email" value="<?= e($selected['email']) ?>">
                    
                    <label for="upd_address">Residential Address *</label>
                    <textarea id="upd_address" name="address" required style="width:100%; min-height:80px; font-family:inherit; resize:vertical;"><?= e($selected['address']) ?></textarea>
                    
                    <label for="upd_aadhar">Aadhar ID No. *</label>
                    <input id="upd_aadhar" name="aadhar_no" value="<?= e($selected['aadhar_no']) ?>" required>
                    
                    <label for="upd_shift">Library Work Shift *</label>
                    <select id="upd_shift" name="shift" required>
                        <?php 
                        $shiftsRes = $db->query("SELECT * FROM work_shifts ORDER BY id ASC");
                        while ($s = $shiftsRes->fetch_assoc()) {
                            $sName = $s['name'];
                            $sel = (strcasecmp($selected['shift'] ?? '', $sName) === 0) ? 'selected' : '';
                            $sStart = date('h:i A', strtotime($s['start_time']));
                            $sEnd = date('h:i A', strtotime($s['end_time']));
                            echo '<option value="' . e($sName) . '" ' . $sel . '>' . e($sName) . ' Shift (' . $sStart . ' - ' . $sEnd . ')</option>';
                        }
                        ?>
                    </select>

                    <label for="upd_status">Account Status</label>
                    <select id="upd_status" name="is_active">
                        <option value="1" <?= $selected['is_active'] == 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= $selected['is_active'] == 0 ? 'selected' : '' ?>>Inactive</option>
                    </select>

                    <label for="upd_pwd">New Password (leave blank to keep current)</label>
                    <input id="upd_pwd" type="password" name="password" placeholder="Enter new password to override" maxlength="15">
                    
                    <button style="margin-top:10px;"><i class="fa-solid fa-floppy-disk"></i> Commit Profile Updates</button>
                </form>
            </div>
            <div>
                <div style="background:var(--bg-slate); padding:20px; border-radius:12px; border:1px solid var(--border-color); margin-bottom: 25px;">
                    <h4 style="margin-top:0;"><i class="fa-solid fa-calendar-days"></i> Membership Scope</h4>
                    <p><strong>Tier duration:</strong> <span class="badge badge-blue"><?= e($selected['duration']) ?></span></p>
                    <p><strong>Start Date:</strong> <?= date('d-m-Y', strtotime($selected['start_date'])) ?></p>
                    <p><strong>Expiry Date:</strong> <?= date('d-m-Y', strtotime($selected['end_date'])) ?></p>
                    <p><strong>Payment ID:</strong> <?= e($selected['payment_id']) ?></p>
                    <?php 
                        $isExpired = $selected['end_date'] < date('Y-m-d');
                        if ($selected['is_active'] == 0) {
                            echo '<span class="badge badge-red" style="display:block; text-align:center; font-size:13px; padding:6px;"><i class="fa-solid fa-circle-xmark"></i> Membership Suspended/Inactive</span>';
                        } elseif ($isExpired) {
                            echo '<span class="badge badge-red" style="display:block; text-align:center; font-size:13px; padding:6px;"><i class="fa-solid fa-circle-exclamation"></i> Membership Expired</span>';
                        } else {
                            echo '<span class="badge badge-green" style="display:block; text-align:center; font-size:13px; padding:6px;"><i class="fa-solid fa-circle-check"></i> Membership Active</span>';
                        }
                    ?>
                </div>

                <!-- DOUBLE SIDED PRINT BLOCK FOR ADMIN -->
                <div style="background:var(--bg-slate); padding:20px; border-radius:12px; border:1px solid var(--border-color); margin-bottom: 25px; text-align:center;">
                    <h4 style="margin-top:0; text-align:left;"><i class="fa-solid fa-print"></i> Issue Library ID Card</h4>
                    <p style="font-size:12px; color:var(--text-muted); text-align:left; margin-bottom:15px;">Generate a printable double-sided card aligned to CR80 specifications.</p>
                    
                    <!-- HIDDEN PRINT CARD ELEMENTS -->
                    <div id="m-card-print-area" style="display:none;">
                        <style>
                            @media print {
                                body * { visibility: hidden !important; }
                                #m-card-print-area, #m-card-print-area * { visibility: visible !important; }
                                #m-card-print-area {
                                    position: absolute;
                                    left: 0;
                                    top: 0;
                                    width: 100%;
                                    display: flex !important;
                                    flex-direction: row !important;
                                    justify-content: center !important;
                                    gap: 25px !important;
                                }
                            }
                        </style>
                        <!-- FRONT CARD -->
                        <div style="width: 340px; height: 215px; border: 1px solid #222; border-radius: 12px; background: white; overflow:hidden; font-family:\'Inter\', sans-serif; display:flex; flex-direction:column; justify-content:space-between; box-sizing:border-box;">
                            <div style="background: #0f172a; color: white; padding: 10px; text-align: center; border-bottom: 2px solid #2563eb; display:flex; align-items:center; gap:8px; justify-content:center;">
                                <div style="font-size:16px;">🏛️</div>
                                <div>
                                    <h4 style="margin: 0; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight:700;">Meerut Cantonment Board</h4>
                                    <p style="margin: 1px 0 0 0; font-size: 8px; opacity: 0.9;">P.L.C.L. Digital e-Library</p>
                                </div>
                            </div>
                            <div style="padding: 12px; display: flex; gap: 12px; align-items: center; flex-grow:1;">
                                <div style="width: 55px; height: 54px; border-radius: 50%; background: #eff6ff; display:flex; align-items:center; justify-content:center; border: 1px solid #cbd5e1; font-size: 24px;">👤</div>
                                <div style="overflow:hidden; text-align:left;">
                                    <h4 style="margin:0; font-size: 13px; color: #1e293b; white-space:nowrap; text-overflow:ellipsis; overflow:hidden; font-weight:700;"><?= e($selected['name']) ?></h4>
                                    <p style="margin:2px 0 0 0; font-size: 11px; font-weight: 700; color: #2563eb;"><?= e($selected['membership_id']) ?></p>
                                    <p style="margin:1px 0 0 0; font-size: 9px; color: #64748b;">Mob: <?= e($selected['mobile']) ?></p>
                                    <p style="margin:1px 0 0 0; font-size: 9px; color: #64748b;">UID: <?= e(substr($selected['aadhar_no'], 0, 4) . ' ' . substr($selected['aadhar_no'], 4, 4) . ' ' . substr($selected['aadhar_no'], 8)) ?></p>
                                </div>
                            </div>
                            <div style="background: #f8fafc; padding: 6px 12px; border-top: 1px dashed #cbd5e1; font-size: 9px; color: #334155; display: flex; justify-content: space-between;">
                                <div>
                                    <span style="color: #64748b; font-size: 7px; text-transform: uppercase; display:block;">Shift</span>
                                    <strong style="color:var(--primary);"><?= e($selected['shift'] ?? 'Both') ?></strong>
                                </div>
                                <div>
                                    <span style="color: #64748b; font-size: 7px; text-transform: uppercase; display:block;">Issued</span>
                                    <strong><?= date('d-m-Y', strtotime($selected['start_date'])) ?></strong>
                                </div>
                                <div style="text-align: right;">
                                    <span style="color: #64748b; font-size: 7px; text-transform: uppercase; display:block;">Expires</span>
                                    <strong style="color: #ef4444;"><?= date('d-m-Y', strtotime($selected['end_date'])) ?></strong>
                                </div>
                            </div>
                            <div style="background: #0f172a; color: white; padding: 4px; text-align: center; font-size: 8px; font-weight:600;">100% SECURE OFFLINE DIGITAL PASS</div>
                        </div>

                        <!-- BACK CARD -->
                        <div style="width: 340px; height: 215px; border: 1px solid #222; border-radius: 12px; background: white; overflow:hidden; font-family:\'Inter\', sans-serif; display:flex; flex-direction:column; justify-content:space-between; padding: 12px 15px; box-sizing:border-box;">
                            <div>
                                <h5 style="margin: 0 0 6px 0; font-size: 10px; text-transform: uppercase; color: #0f172a; font-weight: 700; border-bottom:1px solid #e2e8f0; padding-bottom:3px; text-align:center;">Library Instructions & Terms</h5>
                                <ul style="margin: 0; padding-left: 12px; text-align: left; font-size: 8px; color: #334155; line-height: 1.3; display:flex; flex-direction:column; gap:2px;">
                                    <li>This card is non-transferable and remains property of MCB.</li>
                                    <li>Show this card at the check-out desk for physical book lending.</li>
                                    <li>Overdue physical volumes must be returned promptly to the library desk.</li>
                                    <li>Loss of membership card must be reported to librarian instantly.</li>
                                    <li>Digital portal access is active till subscription expiry.</li>
                                </ul>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-top:5px;">
                                <div style="text-align:left;">
                                    <div style="display:flex; align-items:flex-end; height:20px; background:white; padding:2px; border-radius:2px; border:1px solid #cbd5e1;">
                                        <div style="width:2px; height:100%; background:black; margin-right:1px;"></div>
                                        <div style="width:1px; height:100%; background:black; margin-right:1px;"></div>
                                        <div style="width:3px; height:100%; background:black; margin-right:1px;"></div>
                                        <div style="width:1px; height:100%; background:black; margin-right:2px;"></div>
                                        <div style="width:2px; height:100%; background:black; margin-right:1px;"></div>
                                        <div style="width:4px; height:100%; background:black; margin-right:1px;"></div>
                                        <div style="width:1px; height:100%; background:black; margin-right:1px;"></div>
                                        <div style="width:2px; height:100%; background:black; margin-right:2px;"></div>
                                        <div style="width:3px; height:100%; background:black; margin-right:1px;"></div>
                                    </div>
                                    <span style="font-family:\'Courier New\', monospace; font-size: 8px; font-weight:700; color:#334155; display:block; margin-top:2px; text-align:left;"><?= e($selected['membership_id']) ?></span>
                                </div>
                                <div style="text-align:right; border-top:1px solid #475569; width:90px; padding-top:2px;">
                                    <span style="font-size:7px; color:#0f172a; font-weight:700; text-transform:uppercase; display:block;">Issued By Authority</span>
                                    <span style="font-size:6px; color:#64748b; display:block;">CBMDL</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TRIGGER BUTTON -->
                    <button class="btn" style="background:var(--primary); width:100%;" onclick="window.print()"><i class="fa-solid fa-print"></i> Print Member ID Card</button>
                </div>

                <div style="background:var(--bg-slate); padding:20px; border-radius:12px; border:1px solid var(--border-color); margin-bottom: 25px;">
                    <h4 style="margin-top:0;"><i class="fa-solid fa-rotate-right"></i> Renew Membership</h4>
                    <form method="post" action="?action=renew_member&tab=<?= $tab ?>">
                        <?= csrf_input() ?>
                        <input type="hidden" name="id" value="<?= $selected['id'] ?>">
                        <label for="ren_plan_vm">Choose Renewal Plan *</label>
                        <select id="ren_plan_vm" name="plan_id" required>
                            <option value="">Select Plan Class...</option>
                            <?php
                            $plans = $db->query("SELECT * FROM membership_plans ORDER BY amount ASC");
                            while ($p = $plans->fetch_assoc()) {
                                $pSel = (($selected['membership_plan_id'] ?? 0) == $p['id']) ? 'selected' : '';
                                echo '<option value="' . $p['id'] . '" ' . $pSel . '>' . e($p['name']) . ' (' . e($p['duration']) . ' - ₹' . e($p['amount']) . ')</option>';
                            }
                            ?>
                        </select>

                        <label for="ren_shift_vm">Library Work Shift / Timing *</label>
                        <select id="ren_shift_vm" name="shift" required>
                            <?php 
                            $shiftsRes = $db->query("SELECT * FROM work_shifts ORDER BY id ASC");
                            while ($s = $shiftsRes->fetch_assoc()) {
                                $sName = $s['name'];
                                $sel = (strcasecmp($selected['shift'] ?? '', $sName) === 0) ? 'selected' : '';
                                $sStart = date('h:i A', strtotime($s['start_time']));
                                $sEnd = date('h:i A', strtotime($s['end_time']));
                                echo '<option value="' . e($sName) . '" ' . $sel . '>' . e($sName) . ' Shift (' . $sStart . ' - ' . $sEnd . ')</option>';
                            }
                            ?>
                        </select>
                        <label for="ren_pay">Reference Transaction / Payment ID</label>
                        <input id="ren_pay" name="payment_id" placeholder="Transaction Reference" required>
                        <button style="width:100%;"><i class="fa-solid fa-circle-check"></i> Process Renewal</button>
                    </form>
                </div>

                <!-- <div style="background:#fef2f2; padding:20px; border-radius:12px; border:1px solid #fca5a5;">
                    <h4 style="margin-top:0; color:#b91c1c;"><i class="fa-solid fa-triangle-exclamation"></i> Danger Zone</h4>
                    <p style="font-size:13px; color:#7f1d1d;">Remove this member profile permanently from the e-Library catalog.</p>
                    <form method="post" action="?action=delete_member" onsubmit="return confirm('Are you absolutely certain you want to delete this member account permanently? This action is irreversible.')">
                        <?= csrf_input() ?>
                        <input type="hidden" name="id" value="<?= $selected['id'] ?>">
                        <button class="danger btn btn-danger" style="width:100%;"><i class="fa-solid fa-trash-can"></i> Delete Member Account</button>
                    </form>
                </div> -->
            </div>
        </div>

        <!-- Membership History & Renewal Timeline -->
        <?php
        $mHistStmt = $db->prepare("SELECT * FROM membership_history WHERE member_id = ? ORDER BY id DESC");
        $mHistStmt->bind_param("i", $selected['id']);
        $mHistStmt->execute();
        $mHistRes = $mHistStmt->get_result();
        ?>
        <div style="margin-top: 30px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.02);">
            <h4 style="margin-top:0; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-clock-rotate-left" style="color:var(--primary);"></i> Membership Renewal & History Timeline
            </h4>
            <p style="font-size:12px; color:var(--text-muted); margin-bottom:15px;">Chronological audit log of all renewals, plan changes, and fee transactions for <?= e($selected['name']) ?>.</p>
            
            <div style="overflow-x:auto;">
                <table class="table" style="width:100%; border-collapse:collapse; font-size:12px;">
                    <thead>
                        <tr style="background:var(--bg-slate); text-align:left; color:var(--text-muted);">
                            <th style="padding:10px;">#</th>
                            <th style="padding:10px;">Action Type</th>
                            <th style="padding:10px;">Plan & Term</th>
                            <th style="padding:10px;">Validity Period</th>
                            <th style="padding:10px;">Shift</th>
                            <th style="padding:10px;">Fee Paid</th>
                            <th style="padding:10px;">Payment Ref ID</th>
                            <th style="padding:10px;">Logged On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($mHistRes->num_rows === 0): ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding:20px; color:var(--text-muted);">
                                    No historical records logged yet for this member.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $hCount = 1; while ($hRow = $mHistRes->fetch_assoc()): ?>
                                <tr style="border-bottom:1px solid var(--border-color);">
                                    <td style="padding:10px; color:var(--text-muted);"><?= $hCount++ ?></td>
                                    <td style="padding:10px;">
                                        <?php if ($hRow['action_type'] === 'Initial Joining'): ?>
                                            <span class="badge badge-blue" style="font-size:10px; padding:3px 8px; font-weight:600;"><i class="fa-solid fa-user-plus"></i> Initial Joining</span>
                                        <?php elseif ($hRow['action_type'] === 'Renewal'): ?>
                                            <span class="badge" style="font-size:10px; padding:3px 8px; font-weight:600; background:#dcfce7; color:#15803d; border:1px solid #bbf7d0;"><i class="fa-solid fa-rotate-right"></i> Renewal</span>
                                        <?php else: ?>
                                            <span class="badge badge-orange" style="font-size:10px; padding:3px 8px; font-weight:600;"><?= e($hRow['action_type']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:10px;">
                                        <strong><?= e(!empty($hRow['duration']) ? $hRow['duration'] : $hRow['plan_name']) ?></strong>
                                    </td>
                                    <td style="padding:10px;">
                                        <?= date('d-m-Y', strtotime($hRow['start_date'])) ?> &rarr; <?= date('d-m-Y', strtotime($hRow['end_date'])) ?>
                                    </td>
                                    <td style="padding:10px;"><?= e($hRow['shift']) ?></td>
                                    <td style="padding:10px; font-weight:700; color:#16a34a;">₹<?= number_format($hRow['membership_fee'], 2) ?></td>
                                    <td style="padding:10px; font-family:monospace;"><?= e($hRow['payment_id'] ?? 'N/A') ?></td>
                                    <td style="padding:10px; color:var(--text-muted);"><?= date('d M Y, H:i', strtotime($hRow['created_at'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <p style="margin-top:20px;"><a class="btn" href="?action=admin&tab=<?= $tab ?>" style="background:var(--navy-light);"><i class="fa-solid fa-arrow-left"></i> Back to Directory</a></p>
    </div>
<?php else: ?>
    <?php
    // Fetch stats
    $total_active = (int)$db->query("SELECT COUNT(*) c FROM members WHERE approved = 1 AND is_active = 1 AND start_date <= CURDATE() AND end_date >= CURDATE()")->fetch_assoc()['c'];
    $total_inactive = (int)$db->query("SELECT COUNT(*) c FROM members WHERE approved = 1 AND is_active = 0")->fetch_assoc()['c'];
    $total_expired = (int)$db->query("SELECT COUNT(*) c FROM members WHERE approved = 1 AND is_active = 1 AND (end_date < CURDATE() OR start_date > CURDATE())")->fetch_assoc()['c'];
    
    // Status Filter logic
    $status_filter = $_GET['status_filter'] ?? 'all';
    $whereClause = "approved = 1";
    if ($status_filter === 'active') {
        $whereClause .= " AND is_active = 1 AND start_date <= CURDATE() AND end_date >= CURDATE()";
    } elseif ($status_filter === 'inactive') {
        $whereClause .= " AND is_active = 0";
    } elseif ($status_filter === 'expired') {
        $whereClause .= " AND is_active = 1 AND (end_date < CURDATE() OR start_date > CURDATE())";
    }

    // Fetch shift names directly from work_shifts database table
    $availableShifts = [];
    $wsRes = $db->query("SELECT name FROM work_shifts ORDER BY id ASC");
    if ($wsRes) {
        while ($wsRow = $wsRes->fetch_assoc()) {
            if (!empty($wsRow['name']) && !in_array($wsRow['name'], $availableShifts)) {
                $availableShifts[] = $wsRow['name'];
            }
        }
    }

    // Shift Filter logic (defaults to 'all' / All Shifts)
    $shift_filter = $_GET['shift_filter'] ?? 'all';
    if ($shift_filter !== 'all' && in_array($shift_filter, $availableShifts)) {
        $whereClause .= " AND shift = '" . $db->real_escape_string($shift_filter) . "'";
    } else {
        $shift_filter = 'all';
    }

    // Default order
    $orderBy = 'id DESC';
    ?>
    <div class="card">
        <h3>
            <i class="fa-solid fa-address-book"></i> Membership Details 
            <?php if ($status_filter !== 'all' || $shift_filter !== 'all'): ?>
                <span style="font-size:13px; font-weight:normal; margin-left:10px;">
                    (Filtered: 
                    <?php 
                    $labels = [];
                    if ($status_filter !== 'all') $labels[] = '<strong>' . ucfirst($status_filter) . '</strong>';
                    if ($shift_filter !== 'all') $labels[] = '<strong>' . e($shift_filter) . ' Shift</strong>';
                    echo implode(' | ', $labels);
                    ?>) 
                    <a href="?action=admin&tab=view_members" class="btn" style="padding:4px 8px; font-size:11px; background:var(--bg-slate); color:var(--text-color); border:1px solid var(--border-color);"><i class="fa-solid fa-filter-circle-xmark"></i> Clear Filter</a>
                </span>
            <?php endif; ?>
        </h3>
        
        <!-- Member Stats Summary Cards -->
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 20px;">
            <a href="?action=admin&tab=view_members&status_filter=active<?= $shift_filter !== 'all' ? '&shift_filter='.urlencode($shift_filter) : '' ?>" style="text-decoration:none; color:inherit; display:block;">
                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:15px; display:flex; align-items:center; gap:12px; transition: transform 0.2s, box-shadow 0.2s; <?= $status_filter === 'active' ? 'box-shadow: 0 0 0 3px #16a34a;' : '' ?>" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="width:40px; height:40px; border-radius:50%; background:rgba(16, 185, 129, 0.1); color:var(--accent-green); display:flex; align-items:center; justify-content:center; font-size:18px;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <span style="font-size:11px; font-weight:600; color:#15803d; text-transform:uppercase; display:block;">Active Memberships</span>
                        <h3 style="margin:2px 0 0 0; font-size:20px; font-weight:700; color:#166534;"><?= $total_active ?></h3>
                    </div>
                </div>
            </a>

            <a href="?action=admin&tab=view_members&status_filter=inactive<?= $shift_filter !== 'all' ? '&shift_filter='.urlencode($shift_filter) : '' ?>" style="text-decoration:none; color:inherit; display:block;">
                <div style="background:#fff1f2; border:1px solid #fecdd3; border-radius:12px; padding:15px; display:flex; align-items:center; gap:12px; transition: transform 0.2s, box-shadow 0.2s; <?= $status_filter === 'inactive' ? 'box-shadow: 0 0 0 3px #dc2626;' : '' ?>" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="width:40px; height:40px; border-radius:50%; background:rgba(239, 68, 68, 0.1); color:var(--accent-red); display:flex; align-items:center; justify-content:center; font-size:18px;">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </div>
                    <div>
                        <span style="font-size:11px; font-weight:600; color:#b91c1c; text-transform:uppercase; display:block;">Inactive Membersships</span>
                        <h3 style="margin:2px 0 0 0; font-size:20px; font-weight:700; color:#991b1b;"><?= $total_inactive ?></h3>
                    </div>
                </div>
            </a>

            <a href="?action=admin&tab=view_members&status_filter=expired<?= $shift_filter !== 'all' ? '&shift_filter='.urlencode($shift_filter) : '' ?>" style="text-decoration:none; color:inherit; display:block;">
                <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:12px; padding:15px; display:flex; align-items:center; gap:12px; transition: transform 0.2s, box-shadow 0.2s; <?= $status_filter === 'expired' ? 'box-shadow: 0 0 0 3px #d97706;' : '' ?>" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="width:40px; height:40px; border-radius:50%; background:rgba(245, 158, 11, 0.1); color:var(--accent-orange); display:flex; align-items:center; justify-content:center; font-size:18px;">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </div>
                    <div>
                        <span style="font-size:11px; font-weight:600; color:#b45309; text-transform:uppercase; display:block;">Expired Memberships</span>
                        <h3 style="margin:2px 0 0 0; font-size:20px; font-weight:700; color:#92400e;"><?= $total_expired ?></h3>
                    </div>
                </div>
            </a>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; gap:15px; margin-bottom:15px; flex-wrap:wrap;">
            <input type="text" id="viewMembersFilter" placeholder="Instant Search Directory..." style="margin-bottom:0; flex:1; min-width:200px;">
            <div style="display:flex; align-items:center; gap:8px;">
                <label for="memberShiftFilter" style="margin:0; font-size:13px; font-weight:600; color:var(--text-muted); white-space:nowrap;"><i class="fa-solid fa-filter"></i> Filter Shift:</label>
                <select id="memberShiftFilter" onchange="location.href=this.value;" style="margin:0; padding:8px 12px; border-radius:8px; border:1px solid var(--border-color); background:var(--bg-card); font-size:13px; font-weight:600; color:var(--navy-dark); cursor:pointer;">
                    <option value="?action=admin&tab=view_members&shift_filter=all<?= $status_filter !== 'all' ? '&status_filter='.$status_filter : '' ?>" <?= $shift_filter === 'all' ? 'selected' : '' ?>>All Shifts</option>
                    <?php foreach ($availableShifts as $sName): ?>
                        <option value="?action=admin&tab=view_members&shift_filter=<?= urlencode($sName) ?><?= $status_filter !== 'all' ? '&status_filter='.$status_filter : '' ?>" <?= $shift_filter === $sName ? 'selected' : '' ?>><?= e($sName) ?> Shift</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table id="viewMembersTable">
                <thead>
                    <tr>
                        <th>Member Code</th>
                        <th>Full Name</th>
                        <th>Gender</th>
                        <th>Mobile Phone</th>
                        <th>Current Shift</th>
                        <th>Membership Period</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $p_limit = 10;
                    $p_page = max(1, (int)($_GET['p_page'] ?? 1));

                    $cnt_res = $db->query("SELECT COUNT(*) c FROM members WHERE $whereClause");
                    $total_items = (int)($cnt_res ? $cnt_res->fetch_assoc()['c'] : 0);
                    $total_pages = ceil($total_items / $p_limit);
                    $p_offset = ($p_page - 1) * $p_limit;

                    $x = $db->query("SELECT * FROM members WHERE $whereClause ORDER BY $orderBy LIMIT $p_limit OFFSET $p_offset");
                    $mCount = 0;
                    while($r = $x->fetch_assoc()) {
                        $mCount++;
                        $memShift = trim($r['shift'] ?? '');
                        if ($memShift === '' || $memShift === 'Both' || $memShift === 'both') {
                            $memShift = 'Full Day';
                        }
                        if (strcasecmp($memShift, 'Morning') === 0) {
                            $shiftBadge = '<span class="badge badge-orange" style="font-size:11px;"><i class="fa-solid fa-sun"></i> ' . e($memShift) . '</span>';
                        } elseif (strcasecmp($memShift, 'Evening') === 0) {
                            $shiftBadge = '<span class="badge badge-purple" style="font-size:11px; background:#f3e8ff; color:#7e22ce;"><i class="fa-solid fa-moon"></i> ' . e($memShift) . '</span>';
                        } elseif (strcasecmp($memShift, 'Full Day') === 0) {
                            $shiftBadge = '<span class="badge badge-blue" style="font-size:11px;"><i class="fa-solid fa-clock"></i> ' . e($memShift) . '</span>';
                        } else {
                            $shiftBadge = '<span class="badge badge-blue" style="font-size:11px; background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd;"><i class="fa-solid fa-clock"></i> ' . e($memShift) . '</span>';
                        }
                        $gText = e($r['gender'] ?? 'Male');
                        if ($gText === 'Other') $gText = 'Others';
                        
                        $isExpired = $r['end_date'] < date('Y-m-d');
                        $isUpcoming = !empty($r['start_date']) && $r['start_date'] > date('Y-m-d');
                        if ($r['is_active'] == 0) {
                            $statusBadge = '<span class="badge badge-red" style="font-size:11px;"><i class="fa-solid fa-circle-xmark"></i> Inactive</span>';
                        } elseif ($isExpired) {
                            $statusBadge = '<span class="badge badge-red" style="font-size:11px;"><i class="fa-solid fa-circle-exclamation"></i> Expired</span>';
                        } elseif ($isUpcoming) {
                            $statusBadge = '<span class="badge badge-blue" style="font-size:11px; background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd;"><i class="fa-solid fa-calendar-check"></i> Upcoming</span>';
                        } else {
                            $statusBadge = '<span class="badge badge-green" style="font-size:11px;"><i class="fa-solid fa-circle-check"></i> Active</span>';
                        }
                        
                        echo '
                        <tr>
                            <td><strong>' . $r['membership_id'] . '</strong></td>
                            <td>' . e($r['name']) . '</td>
                            <td><span class="badge badge-blue" style="font-size:11px;">' . $gText . '</span></td>
                            <td>' . e($r['mobile']) . '</td>
                            <td>' . $shiftBadge . '</td>
                            <td>' . date('d-m-Y', strtotime($r['start_date'])) . ' to ' . date('d-m-Y', strtotime($r['end_date'])) . '</td>
                            <td>' . $statusBadge . '</td>
                            <td><a class="btn" href="?action=admin&tab=view_members&view=' . $r['id'] . '"><i class="fa-solid fa-user-gear"></i>Edit</a></td>
                        </tr>';
                    }
                    if ($mCount === 0) {
                        echo '<tr><td colspan="8" style="text-align:center; padding:30px; color:var(--text-muted);">No member accounts registered yet.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Premium Pagination Component -->
        <?php if ($total_pages > 1): ?>
            <?php
            $qs = $_GET;
            unset($qs['p_page']);
            $qs_str = http_build_query($qs);
            $qs_str = $qs_str ? '&' . $qs_str : '';
            ?>
            <div class="pagination-container" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; flex-wrap:wrap; gap:15px; border-top:1px solid var(--border-color); padding-top:15px;">
                <div style="font-size:13px; color:var(--text-muted);">
                    Showing <strong><?= $p_offset + 1 ?></strong> to <strong><?= min($p_offset + $p_limit, $total_items) ?></strong> of <strong><?= $total_items ?></strong> members
                </div>
                <div class="pagination" style="display:flex; align-items:center; gap:6px;">
                    <?php if ($p_page > 1): ?>
                        <a href="?p_page=1<?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="First Page"><i class="fa-solid fa-angles-left"></i></a>
                        <a href="?p_page=<?= $p_page - 1 ?><?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Previous Page"><i class="fa-solid fa-angle-left"></i> Prev</a>
                    <?php else: ?>
                        <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-left"></i></span>
                        <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angle-left"></i> Prev</span>
                    <?php endif; ?>

                    <?php 
                    $start_p = max(1, $p_page - 2);
                    $end_p = min($total_pages, $p_page + 2);
                    for($i = $start_p; $i <= $end_p; $i++): 
                    ?>
                        <?php if ($i == $p_page): ?>
                            <span class="btn" style="padding:6px 12px; background:var(--primary); color:white; font-size:12px; font-weight:700; border-radius:6px;"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?p_page=<?= $i ?><?= $qs_str ?>" class="btn" style="padding:6px 12px; background:var(--bg-slate); color:var(--text-color); font-size:12px; border-radius:6px;"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($p_page < $total_pages): ?>
                        <a href="?p_page=<?= $p_page + 1 ?><?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center; gap:4px;" title="Next Page">Next <i class="fa-solid fa-angle-right"></i></a>
                        <a href="?p_page=<?= $total_pages ?><?= $qs_str ?>" class="btn" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-color); font-size:12px; display:inline-flex; align-items:center;" title="Last Page"><i class="fa-solid fa-angles-right"></i></a>
                    <?php else: ?>
                        <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; gap:4px; cursor:not-allowed; opacity:0.6;">Next <i class="fa-solid fa-angle-right"></i></span>
                        <span class="btn disabled" style="padding:6px 10px; background:var(--bg-slate); color:var(--text-muted); font-size:12px; display:inline-flex; align-items:center; cursor:not-allowed; opacity:0.6;"><i class="fa-solid fa-angles-right"></i></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <script class="dynamic-script">
        (function() {
            function initViewMembersScript() {
                const filterInput = document.getElementById('viewMembersFilter');
                const membersTable = document.getElementById('viewMembersTable');
                if (filterInput && membersTable) {
                    filterInput.addEventListener('keyup', function() {
                        const val = this.value.toLowerCase().trim();
                        const rows = membersTable.querySelectorAll('tbody tr');
                        rows.forEach(row => {
                            const text = row.textContent.toLowerCase();
                            row.style.display = text.includes(val) ? '' : 'none';
                        });
                    });
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initViewMembersScript);
            } else {
                initViewMembersScript();
            }
        })();
        </script>
    </div>
<?php endif; ?>


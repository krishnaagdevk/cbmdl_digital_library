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
                    
                    <label for="upd_status">Account Status</label>
                    <select id="upd_status" name="is_active">
                        <option value="1" <?= $selected['is_active'] == 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= $selected['is_active'] == 0 ? 'selected' : '' ?>>Suspended / Inactive</option>
                    </select>

                    <label for="upd_pwd">New Password (leave blank to keep current)</label>
                    <input id="upd_pwd" type="password" name="password" placeholder="Enter new password to override">
                    
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
                        $isExpired = strtotime($selected['end_date']) < time();
                        echo $isExpired 
                            ? '<span class="badge badge-red" style="display:block; text-align:center; font-size:13px; padding:6px;"><i class="fa-solid fa-circle-exclamation"></i> Membership Expired</span>' 
                            : '<span class="badge badge-green" style="display:block; text-align:center; font-size:13px; padding:6px;"><i class="fa-solid fa-circle-check"></i> Membership Active</span>';
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
                                    <span style="font-size:6px; color:#64748b; display:block;">CBMDL Librarian</span>
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
                        <label for="ren_plan">Choose Renewal Plan</label>
                        <select id="ren_plan" name="plan_id" required onchange="updateRenewPlanDetails()">
                            <option value="">Select Plan Class...</option>
                            <?php
                            $plans = $db->query("SELECT * FROM membership_plans ORDER BY amount ASC");
                            while ($p = $plans->fetch_assoc()) {
                                echo '<option value="' . $p['id'] . '" data-amount="' . e($p['amount']) . '" data-duration="' . e($p['duration']) . '">' . e($p['name']) . ' (' . e($p['duration']) . ' - ₹' . e($p['amount']) . ')</option>';
                            }
                            ?>
                        </select>

                        <label for="ren_duration">Renewal Term Duration (Auto-set)</label>
                        <input id="ren_duration" name="duration" readonly style="background:var(--bg-slate); font-weight:600;" placeholder="Term will auto-fill" required>

                        <label for="ren_fee">Plan Due Amount (INR) (Auto-set)</label>
                        <input id="ren_fee" readonly style="background:var(--bg-slate); font-weight:600;" placeholder="Amount will auto-fill">

                        <script>
                        function updateRenewPlanDetails() {
                            const planSelect = document.getElementById('ren_plan');
                            const selectedOption = planSelect.options[planSelect.selectedIndex];
                            const durationInput = document.getElementById('ren_duration');
                            const feeInput = document.getElementById('ren_fee');
                            
                            if (selectedOption && selectedOption.value !== '') {
                                const amount = selectedOption.getAttribute('data-amount');
                                const duration = selectedOption.getAttribute('data-duration');
                                durationInput.value = duration;
                                feeInput.value = amount;
                            } else {
                                durationInput.value = '';
                                feeInput.value = '';
                            }
                        }
                        </script>
                        <label for="ren_pay">Reference Transaction / Payment ID</label>
                        <input id="ren_pay" name="payment_id" placeholder="Transaction Reference" required>
                        <button style="width:100%;"><i class="fa-solid fa-circle-check"></i> Process Renewal</button>
                    </form>
                </div>

                <div style="background:#fef2f2; padding:20px; border-radius:12px; border:1px solid #fca5a5;">
                    <h4 style="margin-top:0; color:#b91c1c;"><i class="fa-solid fa-triangle-exclamation"></i> Danger Zone</h4>
                    <p style="font-size:13px; color:#7f1d1d;">Remove this member profile permanently from the e-Library catalog.</p>
                    <form method="post" action="?action=delete_member" onsubmit="return confirm('Are you absolutely certain you want to delete this member account permanently? This action is irreversible.')">
                        <?= csrf_input() ?>
                        <input type="hidden" name="id" value="<?= $selected['id'] ?>">
                        <button class="danger btn btn-danger" style="width:100%;"><i class="fa-solid fa-trash-can"></i> Delete Member Account</button>
                    </form>
                </div>
            </div>
        </div>
        <p style="margin-top:20px;"><a class="btn" href="?action=admin&tab=<?= $tab ?>" style="background:var(--navy-light);"><i class="fa-solid fa-arrow-left"></i> Back to Directory</a></p>
    </div>
<?php else: ?>
    <div class="card">
        <h3><i class="fa-solid fa-address-book"></i> Member Accounts Registry</h3>
        <input type="text" id="viewMembersFilter" placeholder="Instant Search Directory..." style="margin-bottom:12px;">
        <div class="table-responsive">
            <table id="viewMembersTable">
                <thead>
                    <tr>
                        <th>Member Code</th>
                        <th>Full Name</th>
                        <th>Mobile Phone</th>
                        <th>Term Validity Span</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $p_limit = 10;
                    $p_page = max(1, (int)($_GET['p_page'] ?? 1));

                    $cnt_res = $db->query("SELECT COUNT(*) c FROM members WHERE approved = 1");
                    $total_items = (int)($cnt_res ? $cnt_res->fetch_assoc()['c'] : 0);
                    $total_pages = ceil($total_items / $p_limit);
                    $p_offset = ($p_page - 1) * $p_limit;

                    $x = $db->query("SELECT * FROM members WHERE approved = 1 ORDER BY id DESC LIMIT $p_limit OFFSET $p_offset");
                    $mCount = 0;
                    while($r = $x->fetch_assoc()) {
                        $mCount++;
                        echo '
                        <tr>
                            <td><strong>' . $r['membership_id'] . '</strong></td>
                            <td>' . e($r['name']) . '</td>
                            <td>' . e($r['mobile']) . '</td>
                            <td>' . date('d-m-Y', strtotime($r['start_date'])) . ' to ' . date('d-m-Y', strtotime($r['end_date'])) . '</td>
                            <td><a class="btn" href="?action=admin&tab=view_members&view=' . $r['id'] . '"><i class="fa-solid fa-user-gear"></i> Inspect / Edit</a></td>
                        </tr>';
                    }
                    if ($mCount === 0) {
                        echo '<tr><td colspan="5" style="text-align:center; padding:30px; color:var(--text-muted);">No member accounts registered yet.</td></tr>';
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
        <script>
        document.addEventListener('DOMContentLoaded', function() {
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
        });
        </script>
    </div>
<?php endif; ?>

<?php
// views/admin/members.php
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

$pendingCountRes = $db->query("SELECT COUNT(*) c FROM members WHERE approved = 0");
$pendingCount = $pendingCountRes ? $pendingCountRes->fetch_assoc()['c'] : 0;
?>
<?php if ($selected): ?>
    <div class="card">
        <?php if ($selected['approved'] == 0): ?>
            <h3><i class="fa-solid fa-file-signature"></i> Review & Approve LAN Membership Application</h3>
            <div class="grid" style="grid-template-columns: 1.5fr 1fr; gap: 30px;">
                <div>
                    <h4><i class="fa-solid fa-address-card"></i> Personal Details (Provided by Applicant)</h4>
                    <form method="post" action="?action=approve_member">
                        <?= csrf_input() ?>
                        <input type="hidden" name="id" value="<?= $selected['id'] ?>">
                        
                        <label for="upd_name">Full Name</label>
                        <input id="upd_name" name="name" value="<?= e($selected['name']) ?>" required style="background:var(--bg-slate);" readonly>
                        
                        <label for="upd_guardian">Father / Husband Name</label>
                        <input id="upd_guardian" name="guardian_name" value="<?= e($selected['guardian_name']) ?>" required style="background:var(--bg-slate);" readonly>
                        
                        <label for="upd_mobile">Mobile Number</label>
                        <input id="upd_mobile" name="mobile" value="<?= e($selected['mobile']) ?>" required style="background:var(--bg-slate);" readonly>
                        
                        <label for="upd_email">Email Address</label>
                        <input id="upd_email" type="email" name="email" value="<?= e($selected['email']) ?>" style="background:var(--bg-slate);" readonly>
                        
                        <label for="upd_address">Residential Address</label>
                        <textarea id="upd_address" name="address" required style="width:100%; min-height:80px; background:var(--bg-slate); font-family:inherit; resize:vertical;" readonly><?= e($selected['address']) ?></textarea>
                        
                        <label for="upd_aadhar">Aadhar ID No.</label>
                        <input id="upd_aadhar" name="aadhar_no" value="<?= e($selected['aadhar_no']) ?>" required style="background:var(--bg-slate);" readonly>
                </div>
                <div>
                    <div style="background:var(--bg-slate); padding:20px; border-radius:12px; border:1px solid var(--border-color); margin-bottom: 25px;">
                        <h4 style="margin-top:0;"><i class="fa-solid fa-receipt"></i> Settle Plan & Payment</h4>
                        <label for="m_plan_approve">Membership Plan *</label>
                        <select id="m_plan_approve" name="plan_id" required>
                            <option value="">Choose Membership Plan Class</option>
                            <?php 
                            $plans = $db->query('SELECT * FROM membership_plans ORDER BY amount ASC');
                            while($p = $plans->fetch_assoc()) {
                                $pSel = (($selected['membership_plan_id'] ?? 0) == $p['id']) ? 'selected' : '';
                                echo '<option value="' . $p['id'] . '" ' . $pSel . '>' . e($p['name']) . ' (' . e($p['duration']) . ' - ₹' . e($p['amount']) . ')</option>';
                            }
                            ?>
                        </select>

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
                        
                        <label for="m_pay">Reference Transaction / Payment ID *</label>
                        <input id="m_pay" name="payment_id" value="<?= e($selected['payment_id'] ?? '') ?>" placeholder="Challan / UPI / Cash" required>
                        
                        <button style="width:100%; margin-top:15px; background:var(--primary);"><i class="fa-solid fa-circle-check"></i> Approve & Issue Membership ID</button>
                    </form>
                    </div>

                    <div style="background:#fef2f2; padding:20px; border-radius:12px; border:1px solid #fca5a5;">
                        <h4 style="margin-top:0; color:#b91c1c;"><i class="fa-solid fa-ban"></i> Reject Application</h4>
                        <p style="font-size:13px; color:#7f1d1d;">Discard this pending LAN membership application permanently. This action is irreversible.</p>
                        <form method="post" action="?action=delete_member" onsubmit="return confirm('Are you absolutely certain you want to discard this application permanently? This action is irreversible.')">
                            <?= csrf_input() ?>
                            <input type="hidden" name="id" value="<?= $selected['id'] ?>">
                            <button class="danger btn btn-danger" style="width:100%;"><i class="fa-solid fa-trash"></i> Discard Application</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <h3><i class="fa-solid fa-user-pen"></i> Update Member Profile & Scope — <?= e($selected['membership_id']) ?></h3>
            <div class="grid" style="grid-template-columns: 1.5fr 1fr; gap: 30px;">
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
                        <textarea id="upd_address" name="address" required style="width:100%; min-height:80px;"><?= e($selected['address']) ?></textarea>
                        
                        <label for="upd_aadhar">Aadhar ID No. *</label>
                        <input id="upd_aadhar" name="aadhar_no" value="<?= e($selected['aadhar_no']) ?>" required maxlength="12" pattern="\d{12}" inputmode="numeric" title="Aadhar ID must be exactly 12 digits (numbers only)" placeholder="12 Digit ID">
                        
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
                            $isExpired = $selected['end_date'] < date('Y-m-d');
                            echo $isExpired 
                                ? '<span class="badge badge-red" style="display:block; text-align:center; font-size:13px; padding:6px;"><i class="fa-solid fa-circle-exclamation"></i> Membership Expired</span>' 
                                : '<span class="badge badge-green" style="display:block; text-align:center; font-size:13px; padding:6px;"><i class="fa-solid fa-circle-check"></i> Membership Active</span>';
                        ?>
                    </div>

                    <div style="background:var(--bg-slate); padding:20px; border-radius:12px; border:1px solid var(--border-color); margin-bottom: 25px;">
                        <h4 style="margin-top:0;"><i class="fa-solid fa-rotate-right"></i> Renew Membership</h4>
                        <form method="post" action="?action=renew_member&tab=<?= $tab ?>">
                            <?= csrf_input() ?>
                            <input type="hidden" name="id" value="<?= $selected['id'] ?>">
                            <label for="ren_plan">Choose Renewal Plan *</label>
                            <select id="ren_plan" name="plan_id" required>
                                <option value="">Select Plan Class...</option>
                                <?php
                                $plans = $db->query("SELECT * FROM membership_plans ORDER BY amount ASC");
                                while ($p = $plans->fetch_assoc()) {
                                    $pSel = (($selected['membership_plan_id'] ?? 0) == $p['id']) ? 'selected' : '';
                                    echo '<option value="' . $p['id'] . '" ' . $pSel . '>' . e($p['name']) . ' (' . e($p['duration']) . ' - ₹' . e($p['amount']) . ')</option>';
                                }
                                ?>
                            </select>

                            <label for="ren_shift">Library Work Shift / Timing *</label>
                            <select id="ren_shift" name="shift" required>
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

                    <div style="background:#fef2f2; padding:20px; border-radius:12px; border:1px solid #fca5a5;">
                        <h4 style="margin-top:0; color:#b91c1c;"><i class="fa-solid fa-triangle-exclamation"></i> Danger Zone</h4>
                        <p style="font-size:13px; color:#7f1d1d;">Remove this member profile and all associated logs permanently from the e-Library catalog.</p>
                        <form method="post" action="?action=delete_member" onsubmit="return confirm('Are you absolutely certain you want to delete this member account permanently? This action is irreversible.')">
                            <?= csrf_input() ?>
                            <input type="hidden" name="id" value="<?= $selected['id'] ?>">
                            <button class="danger btn btn-danger" style="width:100%;"><i class="fa-solid fa-trash-can"></i> Delete Member Account</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <p style="margin-top:20px;"><a class="btn" href="?action=admin&tab=<?= $tab ?>" style="background:var(--navy-light);"><i class="fa-solid fa-arrow-left"></i> Back to Members Dashboard</a></p>
    </div>
<?php else: ?>

    <div class="grid">
        <div class="card">
            <h3><i class="fa-solid fa-address-card"></i> Register New Member Account</h3>
            <?php 
            $draft = $_SESSION['reg_member_draft'] ?? [];
            ?>
            <form method="post" action="?action=add_member">
                <?= csrf_input() ?>
                <label for="m_name">Full Name *</label>
                <input id="m_name" name="name" value="<?= e($draft['name'] ?? '') ?>" placeholder="Member Name" required>
                
                <label for="m_gender">Gender *</label>
                <select id="m_gender" name="gender" required>
                    <option value="Male" <?= ($draft['gender'] ?? 'Male') === 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= ($draft['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                    <option value="Other" <?= ($draft['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Others</option>
                </select>
                
                <label for="m_guardian">Father / Husband Name *</label>
                <input id="m_guardian" name="guardian_name" value="<?= e($draft['guardian_name'] ?? '') ?>" placeholder="Guardian Name" required>
                
                <label for="m_mobile">Mobile Number *</label>
                <input id="m_mobile" name="mobile" value="<?= e($draft['mobile'] ?? '') ?>" placeholder="10 Digit Mobile Number" required>
                
                <label for="m_email">Email Address</label>
                <input id="m_email" type="email" name="email" value="<?= e($draft['email'] ?? '') ?>" placeholder="email@domain.com">
                
                <label for="m_pass">Account Password *</label>
                <input id="m_pass" type="password" name="password" placeholder="Passcode for member portal login" required>
                
                <label for="m_address">Residential Address *</label>
                <textarea id="m_address" name="address" placeholder="Full Postal Address" required style="width:100%; min-height:80px;"><?= e($draft['address'] ?? '') ?></textarea>
                
                <label for="m_aadhar">Aadhar ID No. *</label>
                <input id="m_aadhar" name="aadhar_no" value="<?= e($draft['aadhar_no'] ?? '') ?>" placeholder="12 Digit Unique Aadhar" required maxlength="12" pattern="\d{12}" inputmode="numeric" title="Aadhar ID must be exactly 12 digits (numbers only)">
                
                <label for="m_plan">Membership Plan *</label>
                <select id="m_plan" name="plan_id" required>
                    <option value="">Choose Membership Tier...</option>
                    <?php 
                    $plans = $db->query('SELECT * FROM membership_plans ORDER BY amount ASC');
                    while($p = $plans->fetch_assoc()) {
                        $pSel = (($draft['plan_id'] ?? 0) == $p['id']) ? 'selected' : '';
                        echo '<option value="' . $p['id'] . '" ' . $pSel . '>' . e($p['name']) . ' (' . e($p['duration']) . ' - ₹' . e($p['amount']) . ')</option>';
                    }
                    ?>
                </select>

                <label for="m_shift">Library Work Shift / Timing *</label>
                <select id="m_shift" name="shift" required>
                    <?php 
                    $shiftsRes = $db->query("SELECT * FROM work_shifts ORDER BY id ASC");
                    while ($s = $shiftsRes->fetch_assoc()) {
                        $sName = $s['name'];
                        $sel = (strcasecmp($draft['shift'] ?? '', $sName) === 0) ? 'selected' : '';
                        $sStart = date('h:i A', strtotime($s['start_time']));
                        $sEnd = date('h:i A', strtotime($s['end_time']));
                        echo '<option value="' . e($sName) . '" ' . $sel . '>' . e($sName) . ' Shift (' . $sStart . ' - ' . $sEnd . ')</option>';
                    }
                    ?>
                </select>
                
                <label for="m_pay">Reference Transaction / Payment ID *</label>
                <input id="m_pay" name="payment_id" value="<?= e($draft['payment_id'] ?? '') ?>" placeholder="Challan / UPI / Cash Ref" required>
                
                <button><i class="fa-solid fa-user-plus"></i> Save Member Profile</button>
            </form>
        </div><?php unset($_SESSION['reg_member_draft']); ?>

        <div>
            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <h3 style="margin:0;"><i class="fa-solid fa-users-viewfinder"></i> Member Catalog (Latest 5)</h3>
                    <a href="?action=admin&tab=view_members" class="btn" style="padding:4px 10px; font-size:12px; background:var(--navy-light);"><i class="fa-solid fa-list-check"></i> View All</a>
                </div>
                <div class="table-responsive">
                    <table id="membersTable">
                        <thead>
                            <tr>
                                <th>Membership ID</th>
                                <th>Full Name</th>
                                <th>Mobile</th>
                                <th>Validity</th>
                                <th>Status</th>
                                <th>Profile</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $x = $db->query('SELECT * FROM members WHERE approved = 1 ORDER BY id DESC LIMIT 5');
                            while($r = $x->fetch_assoc()) {
                                $isExpired = $r['end_date'] < date('Y-m-d');
                                $isUpcoming = !empty($r['start_date']) && $r['start_date'] > date('Y-m-d');
                                if ($r['is_active'] == 0) {
                                    $statusBadge = '<span class="badge badge-red" style="font-size:11px;"><i class="fa-solid fa-circle-xmark"></i> Suspended</span>';
                                } elseif ($isExpired) {
                                    $statusBadge = '<span class="badge badge-red" style="font-size:11px;"><i class="fa-solid fa-circle-exclamation"></i> Expired</span>';
                                } elseif ($isUpcoming) {
                                    $statusBadge = '<span class="badge badge-blue" style="font-size:11px; background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd;"><i class="fa-solid fa-calendar-check"></i> Upcoming</span>';
                                } else {
                                    $statusBadge = '<span class="badge badge-green" style="font-size:11px;"><i class="fa-solid fa-circle-check"></i> Active</span>';
                                }
                                echo '
                                <tr>
                                    <td><strong>' . e($r['membership_id']) . '</strong></td>
                                    <td>' . e($r['name']) . '</td>
                                    <td>' . e($r['mobile']) . '</td>
                                    <td><span style="font-size:12px; font-weight:500;">' . date('d-m-Y', strtotime($r['start_date'])) . ' to ' . date('d-m-Y', strtotime($r['end_date'])) . '</span></td>
                                    <td>' . $statusBadge . '</td>
                                    <td><a class="btn" href="?action=admin&tab=members&view=' . $r['id'] . '" style="padding:6px 12px;"><i class="fa-solid fa-user-pen"></i> Edit</a></td>
                                </tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script class="dynamic-script">
(function() {
    function initMembersScript() {
        // Restrict Aadhar ID input fields to numbers only, maximum of 12 digits
        const aadharInputs = document.querySelectorAll('#upd_aadhar, #m_aadhar');
        aadharInputs.forEach(input => {
            input.addEventListener('input', function() {
                let cleanVal = this.value.replace(/\D/g, '');
                if (cleanVal.length > 12) {
                    cleanVal = cleanVal.substring(0, 12);
                }
                this.value = cleanVal;
            });
            
            input.addEventListener('keypress', function(e) {
                if (e.key < '0' || e.key > '9') {
                    e.preventDefault();
                }
            });
        });

        const table = document.getElementById('membersTable');
        if (!table) return;

        const tbody = table.querySelector('tbody');
        const originalRows = Array.from(tbody.querySelectorAll('tr'));
        const filterInput = document.getElementById('memberFilterInput');
        
        let filteredRows = [...originalRows];
        let currentPage = 1;
        const rowsPerPage = 10;

        const tableResponsive = table.parentElement;
        if (!tableResponsive) return;
        
        // Remove existing pagination container if re-initializing
        const oldPag = tableResponsive.parentNode.querySelector('#memberPaginationContainer');
        if (oldPag) oldPag.remove();

        const paginationContainer = document.createElement('div');
        paginationContainer.id = 'memberPaginationContainer';
        paginationContainer.style.cssText = `
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid var(--border-color, #e2e8f0);
        `;
        
        paginationContainer.innerHTML = `
            <span id="memberPageInfo" style="font-size:12px; color:var(--text-muted, #64748b); font-weight:500;">Showing 1-10 of 10</span>
            <div style="display:flex; gap:8px;">
                <button id="memberPrevBtn" class="btn btn-secondary" style="padding:6px 12px; font-size:12px; background:var(--bg-slate, #f8fafc); border:1px solid var(--border-color, #e2e8f0); color:var(--text-color, #1e293b); cursor:pointer;"><i class="fa-solid fa-chevron-left"></i> Previous</button>
                <button id="memberNextBtn" class="btn btn-secondary" style="padding:6px 12px; font-size:12px; background:var(--bg-slate, #f8fafc); border:1px solid var(--border-color, #e2e8f0); color:var(--text-color, #1e293b); cursor:pointer;">Next <i class="fa-solid fa-chevron-right"></i></button>
            </div>
        `;
        tableResponsive.parentNode.appendChild(paginationContainer);

        const prevBtn = document.getElementById('memberPrevBtn');
        const nextBtn = document.getElementById('memberNextBtn');
        const pageInfo = document.getElementById('memberPageInfo');

        function renderPage(page) {
            currentPage = page;
            const totalRows = filteredRows.length;
            const totalPages = Math.ceil(totalRows / rowsPerPage) || 1;

            if (currentPage < 1) currentPage = 1;
            if (currentPage > totalPages) currentPage = totalPages;

            const startIdx = (currentPage - 1) * rowsPerPage;
            const endIdx = Math.min(startIdx + rowsPerPage, totalRows);

            originalRows.forEach(row => row.style.display = 'none');

            for (let i = startIdx; i < endIdx; i++) {
                if (filteredRows[i]) {
                    filteredRows[i].style.display = '';
                }
            }

            if (totalRows === 0) {
                pageInfo.textContent = 'No records found';
            } else {
                pageInfo.textContent = `Showing ${startIdx + 1}-${endIdx} of ${totalRows}`;
            }

            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === totalPages || totalPages === 0;

            prevBtn.style.opacity = prevBtn.disabled ? '0.5' : '1';
            prevBtn.style.cursor = prevBtn.disabled ? 'not-allowed' : 'pointer';
            nextBtn.style.opacity = nextBtn.disabled ? '0.5' : '1';
            nextBtn.style.cursor = nextBtn.disabled ? 'not-allowed' : 'pointer';
        }

        if (filterInput) {
            filterInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                filteredRows = originalRows.filter(row => {
                    return row.textContent.toLowerCase().includes(query);
                });
                renderPage(1);
            });
        }

        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentPage > 1) renderPage(currentPage - 1);
        });

        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const totalPages = Math.ceil(filteredRows.length / rowsPerPage) || 1;
            if (currentPage < totalPages) renderPage(currentPage + 1);
        });

        renderPage(1);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMembersScript);
    } else {
        initMembersScript();
    }
})();
</script>

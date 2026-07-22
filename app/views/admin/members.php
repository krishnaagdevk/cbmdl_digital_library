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
                        <label for="m_plan">Membership Plan *</label>
                        <select id="m_plan" name="plan_id" required onchange="updatePlanFee(this)">
                            <option value="">Choose Membership Plan Class</option>
                            <?php 
                            $plans = $db->query('SELECT * FROM membership_plans ORDER BY amount ASC');
                            while($p = $plans->fetch_assoc()) {
                                echo '<option value="' . $p['id'] . '" data-amount="' . e($p['amount']) . '" data-duration="' . e($p['duration']) . '">' . e($p['name']) . ' (' . e($p['duration']) . ' - ₹' . e($p['amount']) . ')</option>';
                            }
                            ?>
                        </select>

                        <label for="m_duration">Membership Term Duration (Auto-set)</label>
                        <input id="m_duration" name="duration" readonly style="background:var(--bg-slate); font-weight:600;" placeholder="Term will auto-fill" required>
                        
                        <label for="m_fee">Collected Membership Fee (INR) (Auto-set)</label>
                        <input id="m_fee" name="membership_fee" readonly style="background:var(--bg-slate); font-weight:600;" placeholder="Collected fee will auto-fill" required>

                        <script>
                        function updatePlanFee(selectEl) {
                            const selectedOption = selectEl.options[selectEl.selectedIndex];
                            const container = selectEl.closest('div');
                            const feeInput = container ? container.querySelector('#m_fee') : null;
                            const durationInput = container ? container.querySelector('#m_duration') : null;
                            
                            if (selectedOption && selectedOption.value !== '') {
                                const amount = selectedOption.getAttribute('data-amount');
                                const duration = selectedOption.getAttribute('data-duration');
                                if (feeInput) feeInput.value = amount;
                                if (durationInput) durationInput.value = duration;
                            } else {
                                if (feeInput) feeInput.value = '';
                                if (durationInput) durationInput.value = '';
                            }
                        }
                        </script>
                        
                        <label for="m_pay">Reference Transaction / Payment ID *</label>
                        <input id="m_pay" name="payment_id" placeholder="Challan / UPI / Cash" required>
                        
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

                    <div style="background:var(--bg-slate); padding:20px; border-radius:12px; border:1px solid var(--border-color); margin-bottom: 25px;">
                        <h4 style="margin-top:0;"><i class="fa-solid fa-rotate-right"></i> Renew Membership</h4>
                        <form method="post" action="?action=renew_member&tab=<?= $tab ?>">
                            <?= csrf_input() ?>
                            <input type="hidden" name="id" value="<?= $selected['id'] ?>">
                            <label for="ren_plan">Choose Renewal Plan</label>
                            <select id="ren_plan" name="plan_id" required onchange="updateRenewPlanDetails(this)">
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
                            function updateRenewPlanDetails(selectEl) {
                                const selectedOption = selectEl.options[selectEl.selectedIndex];
                                const container = selectEl.closest('form');
                                const durationInput = container ? container.querySelector('#ren_duration') : null;
                                const feeInput = container ? container.querySelector('#ren_fee') : null;
                                
                                if (selectedOption && selectedOption.value !== '') {
                                    const amount = selectedOption.getAttribute('data-amount');
                                    const duration = selectedOption.getAttribute('data-duration');
                                    if (durationInput) durationInput.value = duration;
                                    if (feeInput) feeInput.value = amount;
                                } else {
                                    if (durationInput) durationInput.value = '';
                                    if (feeInput) feeInput.value = '';
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
            <form method="post" action="?action=add_member">
                <?= csrf_input() ?>
                <label for="m_name">Full Name *</label>
                <input id="m_name" name="name" placeholder="Member Name" required>
                
                <label for="m_guardian">Father / Husband Name *</label>
                <input id="m_guardian" name="guardian_name" placeholder="Guardian Name" required>
                
                <label for="m_mobile">Mobile Number *</label>
                <input id="m_mobile" name="mobile" placeholder="Contact Mobile No" required>
                
                <label for="m_password">Secure Login Password *</label>
                <input id="m_password" type="password" name="password" placeholder="Create password" required>
                
                <label for="m_email">Email ID</label>
                <input id="m_email" name="email" type="email" placeholder="e.g. member@meerut.com">
                
                <label for="m_address">Residential Address *</label>
                <textarea id="m_address" name="address" placeholder="Address information" required style="width:100%; min-height:80px;"></textarea>
                
                <label for="m_aadhar">Aadhar ID No. *</label>
                <input id="m_aadhar" name="aadhar_no" placeholder="12 Digit ID" required maxlength="12" pattern="\d{12}" inputmode="numeric" title="Aadhar ID must be exactly 12 digits (numbers only)">
                
                <label for="m_plan">Membership Plan *</label>
                 <select id="m_plan" name="plan_id" required onchange="updatePlanFeeMain()">
                     <option value="">Choose Membership Plan Class</option>
                     <?php 
                     $plans = $db->query('SELECT * FROM membership_plans ORDER BY amount ASC');
                     while($p = $plans->fetch_assoc()) {
                         echo '<option value="' . $p['id'] . '" data-amount="' . e($p['amount']) . '" data-duration="' . e($p['duration']) . '">' . e($p['name']) . ' (' . e($p['duration']) . ' - ₹' . e($p['amount']) . ')</option>';
                     }
                     ?>
                 </select>

                 <label for="m_duration_main">Membership Term Duration (Auto-set)</label>
                 <input id="m_duration_main" name="duration" readonly style="background:var(--bg-slate); font-weight:600;" placeholder="Term will auto-fill" required>
                 
                 <label for="m_fee_main">Collected Membership Fee (INR) (Auto-set)</label>
                 <input id="m_fee_main" name="membership_fee" readonly style="background:var(--bg-slate); font-weight:600;" placeholder="Collected fee will auto-fill" required>

                 <script>
                 function updatePlanFeeMain(selectEl) {
                     const selectedOption = selectEl.options[selectEl.selectedIndex];
                     const container = selectEl.closest('form');
                     const feeInput = container ? container.querySelector('#m_fee_main') : null;
                     const durationInput = container ? container.querySelector('#m_duration_main') : null;
                     
                     if (selectedOption && selectedOption.value !== '') {
                         const amount = selectedOption.getAttribute('data-amount');
                         const duration = selectedOption.getAttribute('data-duration');
                         if (feeInput) feeInput.value = amount;
                         if (durationInput) durationInput.value = duration;
                     } else {
                         if (feeInput) feeInput.value = '';
                         if (durationInput) durationInput.value = '';
                     }
                 }
                 </script>
                
                <label for="m_pay">Reference Transaction / Payment ID *</label>
                <input id="m_pay" name="payment_id" placeholder="Challan / UPI / Cache Ref" required>
                
                <button><i class="fa-solid fa-user-plus"></i> Save Member Profile</button>
            </form>
        </div>

        <div>


            <div class="card">
                <h3><i class="fa-solid fa-users-viewfinder"></i> Active Registered Members</h3>
                <input type="text" id="memberFilterInput" placeholder="Type to filter active members..." style="margin-bottom:12px;">
                <div class="table-responsive">
                    <table id="membersTable">
                        <thead>
                            <tr>
                                <th>Code ID</th>
                                <th>Full Name</th>
                                <th>Mobile</th>
                                <th>Term Validity</th>
                                <th>Profile</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $x = $db->query('SELECT * FROM members WHERE approved = 1 ORDER BY id DESC');
                            while($r = $x->fetch_assoc()) {
                                echo '
                                <tr>
                                    <td><strong>' . $r['membership_id'] . '</strong></td>
                                    <td>' . e($r['name']) . '</td>
                                    <td>' . e($r['mobile']) . '</td>
                                    <td><span style="font-size:12px; font-weight:500;">' . date('d-m-Y', strtotime($r['start_date'])) . ' to ' . date('d-m-Y', strtotime($r['end_date'])) . '</span></td>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Restrict Aadhar ID input fields to numbers only, maximum of 12 digits
    const aadharInputs = document.querySelectorAll('#upd_aadhar, #m_aadhar');
    aadharInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Strip any character that is not a number
            let cleanVal = this.value.replace(/\D/g, '');
            // Bound to maximum length of 12 digits
            if (cleanVal.length > 12) {
                cleanVal = cleanVal.substring(0, 12);
            }
            this.value = cleanVal;
        });
        
        // Block non-numeric characters on keypress before they render
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

    // Create pagination container below table-responsive
    const tableResponsive = table.parentElement;
    const paginationContainer = document.createElement('div');
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

    function renderTable() {
        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage) || 1;
        
        // Boundaries
        if (currentPage < 1) currentPage = 1;
        if (currentPage > totalPages) currentPage = totalPages;

        const startIdx = (currentPage - 1) * rowsPerPage;
        const endIdx = startIdx + rowsPerPage;

        // Clear and append
        tbody.innerHTML = '';
        const pageRows = filteredRows.slice(startIdx, endIdx);
        
        if (pageRows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:20px; color:var(--text-muted);"><i class="fa-solid fa-face-frown" style="font-size:18px; margin-bottom:5px; display:block;"></i> No matching members found</td></tr>`;
        } else {
            pageRows.forEach(row => tbody.appendChild(row));
        }

        // Update button states
        prevBtn.disabled = (currentPage === 1);
        nextBtn.disabled = (currentPage === totalPages);
        
        // CSS tweaks for disabled state
        prevBtn.style.opacity = (currentPage === 1) ? '0.5' : '1';
        prevBtn.style.cursor = (currentPage === 1) ? 'not-allowed' : 'pointer';
        nextBtn.style.opacity = (currentPage === totalPages) ? '0.5' : '1';
        nextBtn.style.cursor = (currentPage === totalPages) ? 'not-allowed' : 'pointer';

        // Update text
        const showingStart = totalRows === 0 ? 0 : startIdx + 1;
        const showingEnd = Math.min(endIdx, totalRows);
        pageInfo.textContent = `Showing ${showingStart}-${showingEnd} of ${totalRows}`;
    }

    // Button actions
    prevBtn.addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    });

    nextBtn.addEventListener('click', function() {
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderTable();
        }
    });

    // Custom reactive search filter
    if (filterInput) {
        filterInput.addEventListener('input', function() {
            const query = filterInput.value.toLowerCase().trim();
            
            filteredRows = originalRows.filter(row => {
                const text = row.textContent.toLowerCase();
                return text.includes(query);
            });
            
            currentPage = 1;
            renderTable();
        });
    }

    // Init
    renderTable();
});
</script>

<?php
// views/user/profile.php
if (!defined('BASE_URL')) exit;
?>
<div class="card">
    <h3><i class="fa-solid fa-user-tag"></i> Profile & Membership Credentials Summary</h3>
    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 15px;">
        <div style="background:var(--bg-slate); padding:16px; border-radius:12px; border:1px solid var(--border-color);">
            <p style="margin: 0 0 6px 0; font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:600;"><i class="fa-solid fa-user"></i> Full Name</p>
            <p style="margin:0; font-size:15px; font-weight:700; color:var(--navy-dark);"><?= e($me['name']) ?></p>
        </div>
        
        <div style="background:var(--bg-slate); padding:16px; border-radius:12px; border:1px solid var(--border-color);">
            <p style="margin: 0 0 6px 0; font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:600;"><i class="fa-solid fa-user-tie"></i> Father / Husband Name</p>
            <p style="margin:0; font-size:15px; font-weight:700; color:var(--navy-dark);"><?= e($me['guardian_name'] ?? 'N/A') ?></p>
        </div>

        <div style="background:var(--bg-slate); padding:16px; border-radius:12px; border:1px solid var(--border-color);">
            <p style="margin: 0 0 6px 0; font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:600;"><i class="fa-solid fa-id-badge"></i> Membership Id</p>
            <p style="margin:0; font-size:15px; font-weight:700; color:var(--primary);"><?= e($me['membership_id']) ?></p>
        </div>

        <div style="background:var(--bg-slate); padding:16px; border-radius:12px; border:1px solid var(--border-color);">
            <p style="margin: 0 0 6px 0; font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:600;"><i class="fa-solid fa-phone"></i> Registered Mobile</p>
            <p style="margin:0; font-size:15px; font-weight:700; color:var(--navy-dark);"><?= e($me['mobile']) ?></p>
        </div>

        <div style="background:var(--bg-slate); padding:16px; border-radius:12px; border:1px solid var(--border-color);">
            <p style="margin: 0 0 6px 0; font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:600;"><i class="fa-solid fa-envelope"></i> Primary Email</p>
            <p style="margin:0; font-size:15px; font-weight:700; color:var(--navy-dark);"><?= e($me['email'] ?: 'Not specified') ?></p>
        </div>

        <div style="background:var(--bg-slate); padding:16px; border-radius:12px; border:1px solid var(--border-color);">
            <p style="margin: 0 0 6px 0; font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:600;"><i class="fa-solid fa-fingerprint"></i> Aadhar Card Number</p>
            <p style="margin:0; font-size:15px; font-weight:700; color:var(--navy-dark);"><?= e($me['aadhar_no']) ?></p>
        </div>

        <div style="background:var(--bg-slate); padding:16px; border-radius:12px; border:1px solid var(--border-color);">
            <p style="margin: 0 0 6px 0; font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:600;"><i class="fa-solid fa-indian-rupee-sign"></i> Membership Fees Paid</p>
            <p style="margin:0; font-size:15px; font-weight:700; color:var(--accent-green);">₹<?= number_format((float)($me['membership_fee'] ?? 0), 2) ?></p>
        </div>

        <div style="background:var(--bg-slate); padding:16px; border-radius:12px; border:1px solid var(--border-color);">
            <p style="margin: 0 0 6px 0; font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:600;"><i class="fa-solid fa-receipt"></i> Payment Transaction ID</p>
            <p style="margin:0; font-size:15px; font-weight:700; color:var(--primary);"><?= e($me['payment_id'] ?: 'N/A') ?></p>
        </div>

        <div style="background:var(--bg-slate); padding:16px; border-radius:12px; border:1px solid var(--border-color);">
            <p style="margin: 0 0 6px 0; font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:600;"><i class="fa-solid fa-calendar-plus"></i> Membership Start Date</p>
            <p style="margin:0; font-size:15px; font-weight:700; color:var(--navy-dark);"><?= date('d-m-Y', strtotime($me['start_date'])) ?></p>
        </div>

        <div style="background:var(--bg-slate); padding:16px; border-radius:12px; border:1px solid var(--border-color);">
            <p style="margin: 0 0 6px 0; font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:600;"><i class="fa-solid fa-clock"></i> Assigned Library Shift</p>
            <p style="margin:0; font-size:15px; font-weight:700; color:var(--primary);">
                <?= e($me['shift'] ?? 'Both') ?>
                <?php 
                $shift_time_win = get_shift_time_window($me['shift'] ?? 'Both', $db);
                if ($shift_time_win) {
                    echo '<span style="font-size:12px; font-weight:600; color:var(--text-muted); display:block; margin-top:4px;"><i class="fa-regular fa-clock" style="font-size:11px; margin-right:4px;"></i>' . date('h:i A', strtotime($shift_time_win['start_time'])) . ' - ' . date('h:i A', strtotime($shift_time_win['end_time'])) . '</span>';
                }
                ?>
            </p>
        </div>

        <div style="background:var(--bg-slate); padding:16px; border-radius:12px; border:1px solid var(--border-color);">
            <p style="margin: 0 0 6px 0; font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:600;"><i class="fa-solid fa-calendar-check"></i> Membership End Date</p>
            <p style="margin:0; font-size:15px; font-weight:700; color:var(--accent-green);"><?= date('d-m-Y', strtotime($me['end_date'])) ?></p>
        </div>

        <div style="background:var(--bg-slate); padding:16px; border-radius:12px; border:1px solid var(--border-color);">
            <p style="margin: 0 0 6px 0; font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:600;"><i class="fa-solid fa-circle-info"></i> Account Status</p>
            <p style="margin:0; font-size:15px; font-weight:700;">
                <?php 
                $isExpired = $me['end_date'] < date('Y-m-d');
                if ($me['is_active'] == 0) {
                    echo '<span class="badge badge-red" style="font-size:12px; padding:4px 8px;"><i class="fa-solid fa-circle-xmark"></i> Suspended / Inactive</span>';
                } elseif ($isExpired) {
                    echo '<span class="badge badge-red" style="font-size:12px; padding:4px 8px;"><i class="fa-solid fa-circle-exclamation"></i> Expired</span>';
                } else {
                    echo '<span class="badge badge-green" style="font-size:12px; padding:4px 8px;"><i class="fa-solid fa-circle-check"></i> Active</span>';
                }
                ?>
            </p>
        </div>

        <div style="background:var(--bg-slate); padding:16px; border-radius:12px; border:1px solid var(--border-color); grid-column: 1 / -1;">
            <p style="margin: 0 0 6px 0; font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:600;"><i class="fa-solid fa-location-dot"></i> Residential Address</p>
            <p style="margin:0; font-size:15px; font-weight:600; color:var(--navy-dark); line-height:1.4;"><?= nl2br(e($me['address'])) ?></p>
        </div>
    </div>
</div>

<div class="card" style="max-width:550px; margin: 20px auto 0 auto;">
    <h3><i class="fa-solid fa-user-shield"></i> Update Portal Access Credentials</h3>
    <form method="post" action="?action=update_profile">
        <?= csrf_input() ?>
        <label for="m_prof_email">Primary Email Address</label>
        <input id="m_prof_email" type="email" name="email" value="<?= e($me['email']) ?>" required>
        
        <label for="m_prof_pwd">New Password (leave blank to keep current)</label>
        <input id="m_prof_pwd" type="password" name="password" placeholder="Input complex secure password" maxlength="15">
        
        <button style="width:100%;"><i class="fa-solid fa-square-check"></i> Commit Account Password Update</button>
    </form>
</div>

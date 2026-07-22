<?php
// views/user/profile.php
if (!defined('BASE_URL')) exit;
?>
<div class="card">
    <h3><i class="fa-solid fa-user-tag"></i> Profile Credentials Summary</h3>
    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
        <div style="background:var(--bg-slate); padding:16px; border-radius:12px; border:1px solid var(--border-color);">
            <p style="margin: 0 0 8px 0; font-size:13px; color:var(--text-muted);">Legal Name</p>
            <p style="margin:0; font-size:16px; font-weight:700; color:var(--navy-dark);"><?= e($me['name']) ?></p>
        </div>
        <div style="background:var(--bg-slate); padding:16px; border-radius:12px; border:1px solid var(--border-color);">
            <p style="margin: 0 0 8px 0; font-size:13px; color:var(--text-muted);">Membership ID</p>
            <p style="margin:0; font-size:16px; font-weight:700; color:var(--primary);"><?= e($me['membership_id']) ?></p>
        </div>
        <div style="background:var(--bg-slate); padding:16px; border-radius:12px; border:1px solid var(--border-color);">
            <p style="margin: 0 0 8px 0; font-size:13px; color:var(--text-muted);">Registered Contact</p>
            <p style="margin:0; font-size:16px; font-weight:700; color:var(--navy-dark);"><?= e($me['mobile']) ?></p>
        </div>
        <div style="background:var(--bg-slate); padding:16px; border-radius:12px; border:1px solid var(--border-color);">
            <p style="margin: 0 0 8px 0; font-size:13px; color:var(--text-muted);">Term Expiry Date</p>
            <p style="margin:0; font-size:16px; font-weight:700; color:var(--accent-green);"><?= date('d-m-Y', strtotime($me['end_date'])) ?></p>
        </div>
    </div>
</div>

<div class="card" style="max-width:550px; margin: 0 auto;">
    <h3><i class="fa-solid fa-user-shield"></i> Update Portal Access Password</h3>
    <form method="post" action="?action=update_profile">
        <?= csrf_input() ?>
        <label for="m_prof_email">Primary Email Address</label>
        <input id="m_prof_email" type="email" name="email" value="<?= e($me['email']) ?>" required>
        
        <label for="m_prof_pwd">New Password (leave blank to keep current)</label>
        <input id="m_prof_pwd" type="password" name="password" placeholder="Input complex secure password">
        
        <button style="width:100%;"><i class="fa-solid fa-square-check"></i> Commit Account Password Update</button>
    </form>
</div>

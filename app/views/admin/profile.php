<?php
// views/admin/profile.php
if (!defined('BASE_URL')) exit;
$admin_id = (int)$_SESSION['admin'];

$stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$adminData = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<div class="card" style="max-width:620px; margin:0 auto;">
    <h3><i class="fa-solid fa-user-gear"></i> Librarian Account Profile</h3>
    <p class="muted">Update librarian account login credentials.</p>
    
    <form method="post" action="?action=update_admin_profile">
        <?= csrf_input() ?>
        
        <div style="margin-bottom:15px;">
            <label for="prof_username"><i class="fa-solid fa-user"></i> Username ID</label>
            <input id="prof_username" name="username" value="<?= e($adminData['username']) ?>" required style="margin:0;">
        </div>

        <div style="margin-bottom:18px;">
            <label for="prof_password"><i class="fa-solid fa-lock"></i> Change Password</label>
            <input id="prof_password" type="password" name="password" placeholder="Input new password (leave blank to keep unchanged)" maxlength="30" style="margin:0;">
            <span style="font-size:11.5px; color:var(--text-muted); display:block; margin-top:3px;">Leave blank to preserve current password.</span>
        </div>

        <button style="width:100%; padding:12px; font-weight:600; font-size:14px;"><i class="fa-solid fa-check-double"></i> Save Profile Settings</button>
    </form>
</div>

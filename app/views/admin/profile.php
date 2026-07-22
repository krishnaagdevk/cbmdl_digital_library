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
<div class="card" style="max-width:550px; margin:0 auto;">
    <h3><i class="fa-solid fa-user-gear"></i> Librarian Account Profile</h3>
    <p class="muted">Update login username ID or database access password. Leave password field blank to preserve the current password.</p>
    <form method="post" action="?action=update_admin_profile">
        <?= csrf_input() ?>
        <label for="prof_username">Username ID</label>
        <input id="prof_username" name="username" value="<?= e($adminData['username']) ?>" required>
        
        <label for="prof_password">New Password</label>
        <input id="prof_password" type="password" name="password" placeholder="Input new password (blank = keep unchanged)">
        
        <button><i class="fa-solid fa-check-double"></i> Save Profile Details</button>
    </form>
</div>

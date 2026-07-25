<?php
// views/admin_forgot_password.php
if (!defined('BASE_URL')) exit;
$sysPin = get_admin_master_pin();
?>

<h2 class="header-title-sub" style="margin-bottom:12px;">Cantonment Digital Library Administration</h2>

<div class="forgot-container" style="max-width:540px; margin:20px auto; padding:0 15px;">
    <div class="login-section" style="background:#fff; padding:30px 32px; border-radius:14px; box-shadow:0 8px 25px rgba(15,23,42,0.06); border:1px solid var(--border-color);">
        
        <div style="text-align:center; margin-bottom:22px;">
            <div style="width:54px; height:54px; background:rgba(30,58,138,0.1); color:var(--primary); border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:24px; margin-bottom:10px;">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <h3 style="margin:0 0 6px 0; font-size:20px; color:var(--navy-dark); border:none; padding:0;">Librarian Password Recovery</h3>
            <p style="font-size:13px; color:var(--text-muted); margin:0;">Verify identity using your Master Security Recovery PIN.</p>
        </div>

        <div style="background:#f0f9ff; border:1px solid #bae6fd; border-radius:8px; padding:12px 16px; margin-bottom:20px; font-size:12.5px; color:#0369a1; display:flex; gap:10px; align-items:start;">
            <i class="fa-solid fa-circle-info" style="font-size:16px; margin-top:2px; color:#0284c7;"></i>
            <div>
                <strong>Identity Verification Required:</strong><br>
                Enter your registered <strong>Master Recovery PIN</strong> (Configured PIN: <code><?= e($sysPin) ?></code>) to set a new password.
            </div>
        </div>

        <form method="post" action="?action=process_admin_forgot_password" autocomplete="off">
            <?= csrf_input() ?>
            
            <label for="recovery_username" style="font-size:12.5px; font-weight:600; margin-bottom:5px; display:block;">
                <i class="fa-solid fa-user"></i> Librarian User ID
            </label>
            <input id="recovery_username" name="username" required value="admin" placeholder="Enter Librarian Username" style="padding:11px 14px; margin-bottom:16px; font-size:13.5px; width:100%;">

            <!-- Master PIN Field -->
            <div style="margin-bottom:16px;">
                <label for="recovery_pin" style="font-size:12.5px; font-weight:600; margin-bottom:5px; display:block;">
                    <i class="fa-solid fa-key" style="color:var(--primary);"></i> Master Security Recovery PIN
                </label>
                <input id="recovery_pin" type="password" name="recovery_pin" required placeholder="Enter Master Recovery PIN" style="padding:11px 14px; font-size:13.5px; width:100%;">
            </div>

            <hr style="border:none; border-top:1px dashed var(--border-color); margin:20px 0;">

            <!-- New Password -->
            <div style="margin-bottom:14px;">
                <label for="new_password" style="font-size:12.5px; font-weight:600; margin-bottom:5px; display:block;">
                    <i class="fa-solid fa-lock"></i> New Admin Password
                </label>
                <input id="new_password" type="password" name="new_password" required placeholder="Enter new password (min 4 chars)" minlength="4" maxlength="30" style="padding:11px 14px; font-size:13.5px; width:100%;">
            </div>

            <!-- Confirm New Password -->
            <div style="margin-bottom:20px;">
                <label for="confirm_password" style="font-size:12.5px; font-weight:600; margin-bottom:5px; display:block;">
                    <i class="fa-solid fa-circle-check"></i> Confirm New Password
                </label>
                <input id="confirm_password" type="password" name="confirm_password" required placeholder="Re-enter new password" minlength="4" maxlength="30" style="padding:11px 14px; font-size:13.5px; width:100%;">
            </div>

            <button type="submit" style="width:100%; padding:13px; font-weight:600; font-size:14px; background:var(--primary);">
                <i class="fa-solid fa-rotate-left"></i> Verify PIN & Reset Admin Password
            </button>
        </form>

        <div style="text-align:center; margin-top:18px; border-top:1px solid var(--border-color); padding-top:14px;">
            <a href="admin-login" style="font-size:13px; color:var(--text-muted); font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                <i class="fa-solid fa-arrow-left"></i> Back to Librarian Login
            </a>
        </div>
    </div>
</div>

<?php
namespace App\Controllers;
use App\Models\Admin;
use App\Models\Member;
use mysqli;
final class AuthController {
    private $db;
    public function __construct(mysqli $db) { $this->db = $db; }
    public function adminLogin() {
        if (check_login_lockout()) {
            session_write_close();
            go('admin-login');
        }
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $admin = (new Admin($this->db))->authenticate($username, $password);
        if ($admin) {
            log_admin_login($this->db, $username, 'Success');
            clear_failed_attempts();
            session_regenerate_id(true);
            $_SESSION['admin'] = $admin['id'];
            flash('Welcome back! Logged in as Librarian.');
            session_write_close();
            go('?action=admin');
        }
        log_admin_login($this->db, $username, 'Failed Credentials');
        register_failed_attempt();
        flash('⚠️ Invalid admin credentials.');
        session_write_close();
        go('admin-login');
    }

    public function adminForgotPassword() {
        if (check_login_lockout()) {
            session_write_close();
            go('?action=admin_forgot_password');
        }

        $username = trim($_POST['username'] ?? '');
        $recoveryPin = trim($_POST['recovery_pin'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($username)) {
            flash('⚠️ Please enter your Librarian Username ID.');
            session_write_close();
            go('?action=admin_forgot_password');
        }

        if (empty($recoveryPin)) {
            flash('⚠️ Please enter your Master Security Recovery PIN.');
            session_write_close();
            go('?action=admin_forgot_password');
        }

        if (empty($newPassword) || strlen($newPassword) < 4) {
            flash('⚠️ New password must be at least 4 characters long.');
            session_write_close();
            go('?action=admin_forgot_password');
        }

        if ($newPassword !== $confirmPassword) {
            flash('⚠️ New password and confirm password do not match.');
            session_write_close();
            go('?action=admin_forgot_password');
        }

        $stmt = $this->db->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        if (!$stmt) {
            flash('⚠️ Database error looking up admin details.');
            session_write_close();
            go('?action=admin_forgot_password');
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$admin) {
            log_admin_login($this->db, $username, 'Password Reset Failed (Invalid User)');
            register_failed_attempt();
            flash('⚠️ Invalid Librarian Username ID.');
            session_write_close();
            go('?action=admin_forgot_password');
        }

        $masterPin = get_admin_master_pin();
        $dbPin = !empty($admin['recovery_pin']) ? $admin['recovery_pin'] : $masterPin;
        $isVerified = (password_verify($recoveryPin, $dbPin) || $recoveryPin === $dbPin || $recoveryPin === $masterPin);

        if ($isVerified) {
            $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
            $updateStmt = $this->db->prepare("UPDATE admins SET password = ? WHERE id = ?");
            if ($updateStmt) {
                $updateStmt->bind_param("si", $hashed, $admin['id']);
                $updateStmt->execute();
                $updateStmt->close();
            }

            log_admin_login($this->db, $username, 'Password Reset Success');
            clear_failed_attempts();
            flash('✅ Admin password updated successfully! Please log in with your new password.');
            session_write_close();
            go('admin-login');
        } else {
            log_admin_login($this->db, $username, 'Password Reset Failed (Verification Mismatch)');
            register_failed_attempt();
            flash('⚠️ Verification Failed: Incorrect Master Recovery PIN');
            session_write_close();
            go('?action=admin_forgot_password');
        }
    }

    public function memberLogin() {
        if (check_login_lockout()) {
            session_write_close();
            go('member-login');
        }
        
        $mobile = trim($_POST['mobile'] ?? '');
        $password = $_POST['password'] ?? '';
        
        $stmt = $this->db->prepare("SELECT * FROM members WHERE mobile = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $mobile);
            $stmt->execute();
            $member = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($member) {
                $mShift = $member['shift'] ?? 'Full Day';
                if ($mShift === 'Both' || $mShift === 'both' || $mShift === '') {
                    $mShift = 'Full Day';
                }

                // Verify password
                $pwdCorrect = false;
                if (password_verify($password, $member['password'])) {
                    $pwdCorrect = true;
                } elseif ($password === $member['password']) {
                    // Auto-upgrade raw database password to hashed password
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $updateStmt = $this->db->prepare("UPDATE members SET password = ? WHERE id = ?");
                    if ($updateStmt) {
                        $updateStmt->bind_param("si", $hashed, $member['id']);
                        $updateStmt->execute();
                        $updateStmt->close();
                    }
                    $member['password'] = $hashed;
                    $pwdCorrect = true;
                }
                
                if ($pwdCorrect) {
                    // Check approval / active status / expiry
                    if ($member['approved'] == 0) {
                        log_member_login($this->db, $mobile, $member['id'], $member['name'], $mShift, 'Pending Approval');
                        flash('⚠️ Your membership registration is pending approval from the librarian.');
                        session_write_close();
                        go('member-login');
                    }
                    if ($member['is_active'] == 0) {
                        log_member_login($this->db, $mobile, $member['id'], $member['name'], $mShift, 'Inactive Account');
                        flash('⚠️ Your membership is inactive. Kindly contact the librarian.');
                        session_write_close();
                        go('member-login');
                    }
                    $today = date('Y-m-d');
                    if (!active_member($member, $this->db, $today)) {
                        if (!empty($member['start_date']) && $member['start_date'] > $today) {
                            log_member_login($this->db, $mobile, $member['id'], $member['name'], $mShift, 'Upcoming Membership');
                            flash('⚠️ Your membership is not active today. Your upcoming pass starts on ' . date('d-m-Y', strtotime($member['start_date'])) . '.');
                        } else {
                            log_member_login($this->db, $mobile, $member['id'], $member['name'], $mShift, 'Membership Expired');
                            flash('⚠️ Your membership has expired. Kindly contact the librarian.');
                        }
                        session_write_close();
                        go('member-login');
                    }
                    
                    // Check shift
                    if (!is_member_within_shift_time($mShift, $this->db)) {
                        log_member_login($this->db, $mobile, $member['id'], $member['name'], $mShift, 'Shift Restricted');
                        $time_win = get_shift_time_window($mShift, $this->db);
                        $fmt_start = date('h:i A', strtotime($time_win['start_time']));
                        $fmt_end = date('h:i A', strtotime($time_win['end_time']));
                        flash("🔒 Shift Access Restricted: Your account is assigned to the " . $mShift . " shift (" . $fmt_start . " - " . $fmt_end . "). You cannot log in outside your assigned shift timings.");
                        session_write_close();
                        go('member-login');
                    }
                    
                    log_member_login($this->db, $mobile, $member['id'], $member['name'], $mShift, 'Success');
                    clear_failed_attempts();
                    session_regenerate_id(true);
                    $_SESSION['member'] = $member['id'];
                    expire_member_reading_requests($member['id'], $this->db);
                    flash('Welcome back! Logged in successfully.');
                    session_write_close();
                    go('?action=user');
                } else {
                    // Password incorrect for registered member
                    log_member_login($this->db, $mobile, $member['id'], $member['name'], $mShift, 'Failed Credentials');
                    register_failed_attempt();
                    flash('⚠️ Invalid login credentials.');
                    session_write_close();
                    go('member-login');
                }
            }
        }
        
        // Mobile number NOT registered in DB
        log_member_login($this->db, $mobile, null, null, null, 'Failed Credentials');
        register_failed_attempt();
        flash('⚠️ Invalid login credentials.');
        session_write_close();
        go('member-login');
    }
}
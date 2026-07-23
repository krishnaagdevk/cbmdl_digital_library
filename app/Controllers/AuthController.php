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
            clear_failed_attempts();
            session_regenerate_id(true);
            $_SESSION['admin'] = $admin['id'];
            flash('Welcome back! Logged in as Admin.');
            session_write_close();
            go('?action=admin');
        }
        register_failed_attempt();
        flash('⚠️ Invalid admin credentials.');
        session_write_close();
        go('admin-login');
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
                        flash('⚠️ Your membership registration is pending approval from the librarian.');
                        session_write_close();
                        go('member-login');
                    }
                    if ($member['is_active'] == 0) {
                        flash('⚠️ Your membership is inactive. Kindly contact the librarian.');
                        session_write_close();
                        go('member-login');
                    }
                    if ($member['end_date'] < date('Y-m-d')) {
                        flash('⚠️ Your membership has expired. Kindly contact the librarian.');
                        session_write_close();
                        go('member-login');
                    }
                    
                    // Check shift
                    $shift = $member['shift'] ?? 'Both';
                    if (!is_member_within_shift_time($shift, $this->db)) {
                        $time_win = get_shift_time_window($shift, $this->db);
                        $fmt_start = date('h:i A', strtotime($time_win['start_time']));
                        $fmt_end = date('h:i A', strtotime($time_win['end_time']));
                        flash("🔒 Shift Access Restricted: Your account is assigned to the '" . $shift . "' shift (" . $fmt_start . " - " . $fmt_end . "). You cannot log in outside your assigned shift timings.");
                        session_write_close();
                        go('member-login');
                    }
                    
                    clear_failed_attempts();
                    session_regenerate_id(true);
                    $_SESSION['member'] = $member['id'];
                    expire_member_reading_requests($member['id'], $this->db);
                    flash('Welcome back! Logged in successfully.');
                    session_write_close();
                    go('?action=user');
                }
            }
        }
        
        register_failed_attempt();
        flash('⚠️ Invalid login credentials.');
        session_write_close();
        go('member-login');
    }
}
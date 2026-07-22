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
            header('Location: admin-login');
            exit;
        }
        $admin = (new Admin($this->db))->authenticate($_POST['username']??'', $_POST['password']??'');
        if ($admin) {
            clear_failed_attempts();
            session_regenerate_id(true);
            $_SESSION['admin'] = $admin['id'];
            header('Location: index.php?action=admin');
            exit;
        }
        register_failed_attempt();
        $_SESSION['flash'] = 'Invalid admin credentials.';
        header('Location: admin-login');
        exit;
    }
    public function memberLogin() {
        if (check_login_lockout()) {
            header('Location: member-login');
            exit;
        }
        $member = (new Member($this->db))->authenticate($_POST['mobile']??'', $_POST['password']??'');
        if ($member) {
            clear_failed_attempts();
            session_regenerate_id(true);
            $_SESSION['member'] = $member['id'];
            header('Location: index.php?action=user');
            exit;
        }
        register_failed_attempt();
        $_SESSION['flash'] = 'Invalid login credentials or membership has expired.';
        header('Location: member-login');
        exit;
    }
}
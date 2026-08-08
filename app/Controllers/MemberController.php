<?php
namespace App\Controllers;

use mysqli;

final class MemberController {
    private $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    public function updateProfile() {
        if (!member()) go('member-login');
        $mid = (int)$_SESSION['member'];
        $email = trim($_POST['email'] ?? '');
        $pass = trim($_POST['password'] ?? '');
        
        if ($pass !== '') {
            $hashed = password_hash($pass, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("UPDATE members SET email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssi", $email, $hashed, $mid);
        } else {
            $stmt = $this->db->prepare("UPDATE members SET email = ? WHERE id = ?");
            $stmt->bind_param("si", $email, $mid);
        }
        $stmt->execute();
        $stmt->close();
        flash('Profile updated successfully.');
        go('?action=user&tab=profile');
    }

    public function requestRead() {
        if (!member()) go('member-login');
        $mid = (int)$_SESSION['member'];
        $id = (int)($_GET['id'] ?? 0);
        
        // Check current active/pending request count (limit 5)
        $cntStmt = $this->db->prepare("SELECT COUNT(*) c FROM reading_requests WHERE member_id = ? AND (status = 'Pending' OR (status = 'Approved' AND (started_reading_at IS NULL OR expires_at > NOW())))");
        $cntStmt->bind_param("i", $mid);
        $cntStmt->execute();
        $active_count = (int)($cntStmt->get_result()->fetch_assoc()['c'] ?? 0);
        $cntStmt->close();

        if ($active_count >= 5) {
            flash('⚠️ Request Limit Reached: You can have a maximum of 5 active or pending e-book reading requests at a time.');
            go('?action=user&tab=books');
        }
        
        $oldStmt = $this->db->prepare("SELECT id FROM reading_requests WHERE member_id = ? AND ebook_id = ? AND status = 'Pending'");
        $oldStmt->bind_param("ii", $mid, $id);
        $oldStmt->execute();
        $old = $oldStmt->get_result()->fetch_assoc();
        $oldStmt->close();
        
        if (!$old) {
            $stmt = $this->db->prepare("INSERT INTO reading_requests (member_id, ebook_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $mid, $id);
            $stmt->execute();
            $stmt->close();
            flash('Reading request submitted successfully to the librarian.');
        } else {
            flash('⚠️ Reading request already submitted and pending approval.');
        }
        go('?action=user&tab=books');
    }

    public function requestHold() {
        if (!member()) go('member-login');
        go('?action=user&tab=physical_books');
    }

    public function checkRequestUpdates() {
        header('Content-Type: application/json');
        if (!member()) {
            echo json_encode([]);
            exit;
        }
        $mid = (int)$_SESSION['member'];
        $query = $this->db->query("SELECT r.id, r.ebook_id, r.status, r.started_reading_at, r.expires_at, e.title FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE r.member_id = $mid AND r.id = (SELECT MAX(r2.id) FROM reading_requests r2 WHERE r2.member_id = $mid AND r2.ebook_id = r.ebook_id)");
        $updates = [];
        while ($row = $query->fetch_assoc()) {
            $updates[] = [
                'request_id' => (int)$row['id'],
                'ebook_id' => (int)$row['ebook_id'],
                'status' => $row['status'],
                'title' => $row['title'],
                'active' => ($row['status'] === 'Approved' && (empty($row['started_reading_at']) || strtotime($row['expires_at']) > time()))
            ];
        }
        echo json_encode($updates);
        exit;
    }

    public function pollNotifications() {
        session_write_close();
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        if (!member()) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        $mid = (int)$_SESSION['member'];
        
        $read_query = $this->db->query("
            SELECT r.id, r.status, e.title, r.expires_at 
            FROM reading_requests r 
            JOIN ebooks e ON e.id = r.ebook_id 
            WHERE r.member_id = $mid AND r.requested_at > NOW() - INTERVAL 12 HOUR
            ORDER BY r.id DESC
        ");
        $reading_reqs = [];
        while ($row = $read_query->fetch_assoc()) {
            $reading_reqs[] = [
                'id' => (int)$row['id'],
                'type' => 'reading',
                'title' => $row['title'],
                'status' => $row['status'],
                'expires_at' => $row['expires_at']
            ];
        }
        
        $print_query = $this->db->query("
            SELECT p.id, p.status, e.title, p.pages 
            FROM print_requests p 
            JOIN ebooks e ON e.id = p.ebook_id 
            WHERE p.member_id = $mid AND p.requested_at > NOW() - INTERVAL 12 HOUR
            ORDER BY p.id DESC
        ");
        $print_reqs = [];
        while ($row = $print_query->fetch_assoc()) {
            $print_reqs[] = [
                'id' => (int)$row['id'],
                'type' => 'print',
                'title' => $row['title'],
                'status' => $row['status'],
                'pages' => $row['pages']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'reading' => $reading_reqs,
            'print' => $print_reqs
        ]);
        exit;
    }

    public function requestPrint() {
        if (!member()) go('member-login');
        $mid = (int)$_SESSION['member'];
        $id = (int)($_POST['ebook_id'] ?? 0);
        $p = trim($_POST['pages'] ?? '');
        
        if ($p === '') {
            flash('⚠️ Please specify valid page numbers for printing.');
            go('?action=user&tab=books');
        }

        // Check if there is already a pending print request for this e-book or if a request was submitted in the last 10 seconds
        $checkStmt = $this->db->prepare("SELECT id FROM print_requests WHERE member_id = ? AND ebook_id = ? AND (status = 'Pending' OR requested_at > NOW() - INTERVAL 10 SECOND)");
        $checkStmt->bind_param("ii", $mid, $id);
        $checkStmt->execute();
        $existing = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();

        if ($existing) {
            flash('⚠️ A print request for this e-book is already pending approval.');
            go('?action=user&tab=books');
            return;
        }
        
        $stmt = $this->db->prepare("INSERT INTO print_requests (member_id, ebook_id, pages) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $mid, $id, $p);
        $stmt->execute();
        $stmt->close();
        
        flash('Print request submitted successfully to the librarian.');
        go('?action=user&tab=books');
    }
}

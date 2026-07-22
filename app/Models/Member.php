<?php
namespace App\Models;
use mysqli;

final class Member {
    private mysqli $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    public function authenticate(string $mobile, string $password): ?array {
        $stmt = $this->db->prepare("SELECT * FROM members WHERE mobile = ? LIMIT 1");
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param("s", $mobile);
        $stmt->execute();
        $result = $stmt->get_result();
        $member = $result->fetch_assoc();
        $stmt->close();

        if (!$member || $member['is_active'] == 0 || $member['end_date'] < date('Y-m-d')) {
            return null;
        }

        // Verify using password_verify, with plaintext fallback
        if (password_verify($password, $member['password'])) {
            return $member;
        } elseif ($password === $member['password']) {
            // Auto-upgrade raw database password to hashed password on successful authentication!
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $updateStmt = $this->db->prepare("UPDATE members SET password = ? WHERE id = ?");
            if ($updateStmt) {
                $updateStmt->bind_param("si", $hashed, $member['id']);
                $updateStmt->execute();
                $updateStmt->close();
            }
            $member['password'] = $hashed;
            return $member;
        }

        return null;
    }
}
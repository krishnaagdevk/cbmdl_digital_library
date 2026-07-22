<?php
namespace App\Models;
use mysqli;

final class Admin {
    private mysqli $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    public function authenticate(string $username, string $password): ?array {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();

        if ($admin) {
            // Verify using password_verify, with plaintext fallback
            if (password_verify($password, $admin['password'])) {
                return $admin;
            } elseif ($password === $admin['password']) {
                // Auto-upgrade raw database password to hashed password on successful authentication!
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $updateStmt = $this->db->prepare("UPDATE admins SET password = ? WHERE id = ?");
                if ($updateStmt) {
                    $updateStmt->bind_param("si", $hashed, $admin['id']);
                    $updateStmt->execute();
                    $updateStmt->close();
                }
                $admin['password'] = $hashed;
                return $admin;
            }
        }
        return null;
    }
}
<?php
namespace App\Controllers;

use mysqli;

final class PdfController {
    private $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    public function downloadPdf() {
        if (!admin()) exit('Unauthorized');
        $id = (int)($_GET['id'] ?? 0);
        
        $stmt = $this->db->prepare("SELECT * FROM ebooks WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $b = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($b) {
            $file = dirname(__DIR__, 2) . '/uploads/' . basename($b['pdf_file']);
            if (is_file($file)) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . basename($b['title']) . '.pdf"');
                header('X-Content-Type-Options: nosniff');
                readfile($file);
                exit;
            }
        }
        exit('File not found.');
    }

    public function readPdf() {
        if (!member()) exit('Unauthorized');
        $id = (int)($_GET['id'] ?? 0);
        $mid = (int)$_SESSION['member'];
        
        $stmt = $this->db->prepare(
            "SELECT e.pdf_file, e.title
               FROM ebooks e
          LEFT JOIN reading_requests r ON r.ebook_id = e.id
              WHERE (e.id = ? OR r.id = ?)
              LIMIT 1"
        );
        $stmt->bind_param("ii", $id, $id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$r || empty($r['pdf_file'])) exit('No active permission for this book.');
        $file = dirname(__DIR__, 2) . '/uploads/' . basename($r['pdf_file']);
        if (is_file($file)) {
            stream_file_ranged($file, 'application/pdf', true, 300);
        }
        exit('File not found.');
    }

    public function securePdfViewer() {
        $id           = (int)($_GET['id'] ?? 0);
        $source       = $_GET['source']   ?? '';
        $sessionToken = $_GET['session_token'] ?? $_GET['token'] ?? '';

        $pdfTitle      = 'Secure Interactive Reader';
        $streamUrl     = '';
        $expiresAtUnix = 0;

        if ($source === 'admin' || admin()) {
            if (!admin()) exit('Unauthorized');
            go(BASE_URL . '?action=view_pdf_content&id=' . $id);

        } elseif ($source === 'member') {
            if (!member()) exit('Unauthorized');
            $mid = (int)$_SESSION['member'];

            // 1. Fetch eBook details & approved reading request for this member
            $stmt = $this->db->prepare(
                "SELECT e.id AS ebook_id, e.title, e.pdf_file,
                        r.id AS req_id, r.duration_minutes, r.started_reading_at, r.expires_at, r.status
                   FROM ebooks e
              LEFT JOIN reading_requests r ON r.ebook_id = e.id AND r.member_id = ?
                  WHERE (e.id = ? OR r.id = ?)
                  ORDER BY r.id DESC
                  LIMIT 1"
            );
            $stmt->bind_param("iii", $mid, $id, $id);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$r || empty($r['pdf_file'])) {
                exit('<div style="font-family:system-ui,sans-serif;text-align:center;padding:60px 20px;color:#ef4444;background:#0b0f19;height:100vh;box-sizing:border-box;"><h2 style="font-size:24px;margin-bottom:12px;">⚠️ Permission Expired or Book Not Found</h2><p style="color:#9ca3af;font-size:15px;max-width:500px;margin:0 auto 20px;">Your e-reading request for this book is either not approved or your active reading session has expired.</p><a href="' . BASE_URL . '?action=user&tab=books" style="display:inline-block;padding:10px 20px;background:#3b82f6;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Return to Dashboard</a></div>');
            }

            $durationMinutes = !empty($r['duration_minutes']) ? (int)$r['duration_minutes'] : 15;
            $ebookId         = (int)$r['ebook_id'];
            $activeKey       = 'm_' . $mid . '_b_' . $ebookId;

            $sess = null;

            // A. Check if per-client session instance exists in PHP session memory
            if (!empty($sessionToken) && isset($_SESSION['pdf_sessions'][$sessionToken])) {
                $candidate = $_SESSION['pdf_sessions'][$sessionToken];
                if ((int)$candidate['member_id'] === $mid && (int)$candidate['ebook_id'] === $ebookId) {
                    $sess = $candidate;
                }
            }
            if (!$sess && isset($_SESSION['pdf_sessions'][$activeKey])) {
                $candidate = $_SESSION['pdf_sessions'][$activeKey];
                if ((int)$candidate['member_id'] === $mid && (int)$candidate['ebook_id'] === $ebookId) {
                    $sess = $candidate;
                }
            }

            // B. Reconnect to active DB session if started previously
            if (!$sess && !empty($r['started_reading_at']) && !empty($r['expires_at'])) {
                $sess = [
                    'id'               => !empty($r['req_id']) ? (int)$r['req_id'] : $ebookId,
                    'member_id'        => $mid,
                    'ebook_id'         => $ebookId,
                    'duration_minutes' => $durationMinutes,
                    'started_at'       => strtotime($r['started_reading_at']),
                    'expires_at'       => strtotime($r['expires_at']),
                    'pdf_file'         => $r['pdf_file'],
                    'title'            => $r['title']
                ];
            }

            // C. Validate Session Expiry
            if ($sess) {
                if (time() >= $sess['expires_at']) {
                    // Session HAS EXPIRED. Block access!
                    exit('<div style="font-family:system-ui,sans-serif;text-align:center;padding:60px 20px;color:#ef4444;background:#0b0f19;height:100vh;box-sizing:border-box;"><h2 style="font-size:24px;margin-bottom:12px;">⚠️ e-Reading Permission Expired</h2><p style="color:#9ca3af;font-size:15px;max-width:500px;margin:0 auto 20px;">Your allocated reading session time for this book has expired. Please request a new reading pass from your portal.</p><a href="' . BASE_URL . '?action=user&tab=books" style="display:inline-block;padding:10px 20px;background:#3b82f6;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Return to Dashboard</a></div>');
                }
                $sessionExpiresAt = (int)$sess['expires_at'];
            } else {
                // D. First-time session initialization
                $sessionExpiresAt = time() + ($durationMinutes * 60);

                if (!empty($r['req_id']) && empty($r['started_reading_at'])) {
                    $upStmt = $this->db->prepare(
                        "UPDATE reading_requests
                            SET started_reading_at = NOW(),
                                expires_at         = DATE_ADD(NOW(), INTERVAL ? MINUTE)
                          WHERE id                 = ?
                            AND started_reading_at IS NULL"
                    );
                    $upStmt->bind_param("ii", $durationMinutes, $r['req_id']);
                    $upStmt->execute();
                    $upStmt->close();
                }

                $sess = [
                    'id'               => !empty($r['req_id']) ? (int)$r['req_id'] : $ebookId,
                    'member_id'        => $mid,
                    'ebook_id'         => $ebookId,
                    'duration_minutes' => $durationMinutes,
                    'started_at'       => time(),
                    'expires_at'       => $sessionExpiresAt,
                    'pdf_file'         => $r['pdf_file'],
                    'title'            => $r['title']
                ];
            }

            if (empty($sessionToken)) {
                $sessionToken = bin2hex(random_bytes(16));
            }

            if (!isset($_SESSION['pdf_sessions']) || !is_array($_SESSION['pdf_sessions'])) {
                $_SESSION['pdf_sessions'] = [];
            }
            $_SESSION['pdf_sessions'][$sessionToken] = $sess;
            $_SESSION['pdf_sessions'][$activeKey]     = $sess;

            $pdfTitle      = $sess['title'];
            $streamUrl     = BASE_URL . '?action=read_member_pdf_content&id=' . (int)$sess['ebook_id'] . '&session_token=' . urlencode($sessionToken);
            $expiresAtUnix = (int)$sess['expires_at'];

        } else {
            exit('Invalid source specifier.');
        }

        require dirname(__DIR__) . '/views/pdf_viewer.php';
        exit;
    }

    public function viewEbookPdf() {
        if (!admin()) exit('Unauthorized');
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $this->db->prepare("SELECT * FROM ebooks WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $b = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$b || !$b['pdf_file']) exit('Book not found.');
        
        $file = dirname(__DIR__, 2) . '/uploads/' . basename($b['pdf_file']);
        if (!is_file($file)) exit('PDF file does not exist on server.');
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($b['pdf_file']) . '"');
        header('Content-Length: ' . filesize($file));
        header('Accept-Ranges: bytes');
        readfile($file);
        exit;
    }

    public function viewPdfContent() {
        if (!admin()) {
            http_response_code(403);
            exit('Unauthorized');
        }
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $this->db->prepare("SELECT * FROM ebooks WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $b = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($b && !empty($b['pdf_file'])) {
            $file = dirname(__DIR__, 2) . '/uploads/' . basename($b['pdf_file']);
            if (is_file($file)) {
                stream_file_ranged($file, 'application/pdf', true, 300);
            }
        }
        http_response_code(404);
        exit('File not found.');
    }

    public function readMemberPdf() {
        if (!member()) exit('Unauthorized');
        $mid = (int)$_SESSION['member'];
        $id = (int)($_GET['id'] ?? 0);

        $stmt = $this->db->prepare(
            "SELECT e.id AS ebook_id, e.title, e.pdf_file,
                    r.id AS req_id, r.duration_minutes, r.started_reading_at, r.expires_at
               FROM ebooks e
          LEFT JOIN reading_requests r ON r.ebook_id = e.id AND r.member_id = ?
              WHERE (e.id = ? OR r.id = ?)
              ORDER BY r.id DESC
              LIMIT 1"
        );
        $stmt->bind_param("iii", $mid, $id, $id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$r || empty($r['pdf_file'])) {
            exit('<div style="font-family:system-ui,sans-serif;text-align:center;padding:60px 20px;color:#ef4444;background:#0b0f19;height:100vh;box-sizing:border-box;"><h2 style="font-size:24px;margin-bottom:12px;">⚠️ Permission Expired or Book Not Found</h2><p style="color:#9ca3af;font-size:15px;max-width:500px;margin:0 auto 20px;">Your e-reading request for this book is either not approved or your active reading session has expired.</p><a href="' . BASE_URL . '?action=user&tab=books" style="display:inline-block;padding:10px 20px;background:#3b82f6;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Return to Dashboard</a></div>');
        }

        $durationMinutes = !empty($r['duration_minutes']) ? (int)$r['duration_minutes'] : 15;
        $ebookId         = (int)$r['ebook_id'];
        $activeKey       = 'm_' . $mid . '_b_' . $ebookId;

        $sess = null;
        if (isset($_SESSION['pdf_sessions'][$activeKey])) {
            $candidate = $_SESSION['pdf_sessions'][$activeKey];
            if ((int)$candidate['member_id'] === $mid && (int)$candidate['ebook_id'] === $ebookId) {
                $sess = $candidate;
            }
        }

        if (!$sess && !empty($r['started_reading_at']) && !empty($r['expires_at'])) {
            $sess = [
                'id'               => !empty($r['req_id']) ? (int)$r['req_id'] : $ebookId,
                'member_id'        => $mid,
                'ebook_id'         => $ebookId,
                'duration_minutes' => $durationMinutes,
                'started_at'       => strtotime($r['started_reading_at']),
                'expires_at'       => strtotime($r['expires_at']),
                'pdf_file'         => $r['pdf_file'],
                'title'            => $r['title']
            ];
        }

        if ($sess) {
            if (time() >= $sess['expires_at']) {
                exit('<div style="font-family:system-ui,sans-serif;text-align:center;padding:60px 20px;color:#ef4444;background:#0b0f19;height:100vh;box-sizing:border-box;"><h2 style="font-size:24px;margin-bottom:12px;">⚠️ e-Reading Permission Expired</h2><p style="color:#9ca3af;font-size:15px;max-width:500px;margin:0 auto 20px;">Your allocated reading session time for this book has expired. Please request a new reading pass from your portal.</p><a href="' . BASE_URL . '?action=user&tab=books" style="display:inline-block;padding:10px 20px;background:#3b82f6;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Return to Dashboard</a></div>');
            }
            $sessionToken = bin2hex(random_bytes(16));
        } else {
            $sessionExpiresAt = time() + ($durationMinutes * 60);
            if (!empty($r['req_id']) && empty($r['started_reading_at'])) {
                $upStmt = $this->db->prepare(
                    "UPDATE reading_requests
                        SET started_reading_at = NOW(),
                            expires_at         = DATE_ADD(NOW(), INTERVAL ? MINUTE)
                      WHERE id                 = ?
                        AND started_reading_at IS NULL"
                );
                $upStmt->bind_param("ii", $durationMinutes, $r['req_id']);
                $upStmt->execute();
                $upStmt->close();
            }

            $sessionToken = bin2hex(random_bytes(16));
            $sess = [
                'id'               => !empty($r['req_id']) ? (int)$r['req_id'] : $ebookId,
                'member_id'        => $mid,
                'ebook_id'         => $ebookId,
                'duration_minutes' => $durationMinutes,
                'started_at'       => time(),
                'expires_at'       => $sessionExpiresAt,
                'pdf_file'         => $r['pdf_file'],
                'title'            => $r['title']
            ];
        }

        if (!isset($_SESSION['pdf_sessions']) || !is_array($_SESSION['pdf_sessions'])) {
            $_SESSION['pdf_sessions'] = [];
        }
        $_SESSION['pdf_sessions'][$sessionToken] = $sess;
        $_SESSION['pdf_sessions'][$activeKey]     = $sess;

        session_write_close();
        go(BASE_URL . '?action=secure_pdf_viewer&source=member&id=' . urlencode($ebookId) . '&session_token=' . urlencode($sessionToken));
    }

    public function readMemberPdfContent() {
        if (!member()) exit('Unauthorized');
        $mid = (int)$_SESSION['member'];
        $id = (int)($_GET['id'] ?? 0);
        $sessionToken = $_GET['session_token'] ?? $_GET['token'] ?? '';

        $fileToStream = null;

        // 1. Check isolated per-client session instance token first
        if (!empty($sessionToken) && isset($_SESSION['pdf_sessions'][$sessionToken])) {
            $sess = $_SESSION['pdf_sessions'][$sessionToken];
            if ((int)$sess['member_id'] === $mid && time() < $sess['expires_at']) {
                $fileToStream = dirname(__DIR__, 2) . '/uploads/' . basename($sess['pdf_file']);
            }
        }

        // 2. Check activeKey fallback in $_SESSION
        if (!$fileToStream && isset($_SESSION['pdf_sessions']['m_' . $mid . '_b_' . $id])) {
            $sess = $_SESSION['pdf_sessions']['m_' . $mid . '_b_' . $id];
            if ((int)$sess['member_id'] === $mid && time() < $sess['expires_at']) {
                $fileToStream = dirname(__DIR__, 2) . '/uploads/' . basename($sess['pdf_file']);
            }
        }

        // 3. Fallback DB lookup for direct stream connections
        if (!$fileToStream) {
            $stmt = $this->db->prepare(
                "SELECT e.pdf_file, r.expires_at
                   FROM ebooks e
              LEFT JOIN reading_requests r ON r.ebook_id = e.id
                  WHERE (e.id = ? OR r.id = ?)
                  LIMIT 1"
            );
            $stmt->bind_param("ii", $id, $id);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($r && !empty($r['pdf_file'])) {
                if (empty($r['expires_at']) || strtotime($r['expires_at']) > time()) {
                    $fileToStream = dirname(__DIR__, 2) . '/uploads/' . basename($r['pdf_file']);
                }
            }
        }

        if ($fileToStream && is_file($fileToStream)) {
            stream_file_ranged($fileToStream, 'application/pdf', true, 300);
        }

        http_response_code(403);
        exit('File not found or e-reading permission has expired.');
    }
}

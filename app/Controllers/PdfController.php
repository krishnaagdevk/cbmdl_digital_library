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
        
        $stmt = $this->db->prepare("SELECT r.*, e.pdf_file, e.title FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE r.member_id = ? AND r.ebook_id = ? AND r.status = 'Approved' AND r.expires_at > NOW() ORDER BY r.id DESC LIMIT 1");
        $stmt->bind_param("ii", $mid, $id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$r) exit('No active permission for this book.');
        $file = dirname(__DIR__, 2) . '/uploads/' . basename($r['pdf_file']);
        if (is_file($file)) {
            stream_file_ranged($file, 'application/pdf', true, 300);
        }
        exit('File not found.');
    }

    public function securePdfViewer() {
        $id = (int)($_GET['id'] ?? 0);
        $source = $_GET['source'] ?? '';
        
        $pdfTitle = 'Secure Interactive Reader';
        $streamUrl = '';
        $expiresAtUnix = 0;
        
        if ($source === 'admin' || admin()) {
            if (!admin()) exit('Unauthorized');
            go(BASE_URL . '?action=view_pdf_content&id=' . $id);
        } elseif ($source === 'member') {
            if (!member()) exit('Unauthorized');
            $mid = (int)$_SESSION['member'];
            $stmt = $this->db->prepare("SELECT r.id, r.duration_minutes, r.started_reading_at, r.expires_at, e.title, e.pdf_file FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE r.id = ? AND r.member_id = ? AND r.status = 'Approved' AND (r.started_reading_at IS NULL OR r.expires_at > DATE_SUB(NOW(), INTERVAL 10 SECOND)) LIMIT 1");
            $stmt->bind_param("ii", $id, $mid);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$r) {
                $stmt = $this->db->prepare("SELECT r.id, r.duration_minutes, r.started_reading_at, r.expires_at, e.title, e.pdf_file FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE r.ebook_id = ? AND r.member_id = ? AND r.status = 'Approved' AND (r.started_reading_at IS NULL OR r.expires_at > DATE_SUB(NOW(), INTERVAL 10 SECOND)) ORDER BY r.id DESC LIMIT 1");
                $stmt->bind_param("ii", $id, $mid);
                $stmt->execute();
                $r = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            }
            
            if (!$r || empty($r['pdf_file'])) {
                exit('<div style="font-family:system-ui, sans-serif; text-align:center; padding:60px 20px; color:#ef4444; background:#0b0f19; height:100vh; box-sizing:border-box;"><h2 style="font-size:24px; margin-bottom:12px;">⚠️ Permission Expired or Book Not Found</h2><p style="color:#9ca3af; font-size:15px; max-width:500px; margin:0 auto 20px;">Your e-reading request for this book is either not approved or your active reading session has expired.</p><a href="' . BASE_URL . '?action=user&tab=books" style="display:inline-block; padding:10px 20px; background:#3b82f6; color:#fff; text-decoration:none; border-radius:8px; font-weight:600;">Return to Dashboard</a></div>');
            }

            // Start session timer on first read click if not already started
            if (empty($r['started_reading_at'])) {
                $duration = !empty($r['duration_minutes']) ? (int)$r['duration_minutes'] : 15;
                $upStmt = $this->db->prepare("UPDATE reading_requests SET started_reading_at = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?");
                $upStmt->bind_param("ii", $duration, $r['id']);
                $upStmt->execute();
                $upStmt->close();
                $r['expires_at'] = date('Y-m-d H:i:s', time() + ($duration * 60));
            }

            $pdfTitle = $r['title'];
            $streamUrl = BASE_URL . '?action=read_member_pdf_content&id=' . (int)$r['id'];
            $expiresAtUnix = !empty($r['expires_at']) ? strtotime($r['expires_at']) : 0;
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
        $rid = (int)($_GET['id'] ?? 0);
        
        $stmt = $this->db->prepare("SELECT r.*, e.pdf_file, e.title FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE r.id = ? AND r.member_id = ? AND r.status = 'Approved' AND (r.started_reading_at IS NULL OR r.expires_at > DATE_SUB(NOW(), INTERVAL 10 SECOND)) LIMIT 1");
        $stmt->bind_param("ii", $rid, $mid);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$r) {
            $stmt = $this->db->prepare("SELECT r.*, e.pdf_file, e.title FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE r.ebook_id = ? AND r.member_id = ? AND r.status = 'Approved' AND (r.started_reading_at IS NULL OR r.expires_at > DATE_SUB(NOW(), INTERVAL 10 SECOND)) ORDER BY r.id DESC LIMIT 1");
            $stmt->bind_param("ii", $rid, $mid);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
        
        if (!$r || empty($r['pdf_file'])) {
            exit('<div style="font-family:system-ui, sans-serif; text-align:center; padding:60px 20px; color:#ef4444; background:#0b0f19; height:100vh; box-sizing:border-box;"><h2 style="font-size:24px; margin-bottom:12px;">⚠️ Permission Expired or Book Not Found</h2><p style="color:#9ca3af; font-size:15px; max-width:500px; margin:0 auto 20px;">Your e-reading request for this book is either not approved or your active reading session has expired.</p><a href="' . BASE_URL . '?action=user&tab=books" style="display:inline-block; padding:10px 20px; background:#3b82f6; color:#fff; text-decoration:none; border-radius:8px; font-weight:600;">Return to Dashboard</a></div>');
        }

        if (empty($r['started_reading_at'])) {
            $duration = !empty($r['duration_minutes']) ? (int)$r['duration_minutes'] : 15;
            $upStmt = $this->db->prepare("UPDATE reading_requests SET started_reading_at = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?");
            $upStmt->bind_param("ii", $duration, $r['id']);
            $upStmt->execute();
            $upStmt->close();
        }
        
        go(BASE_URL . '?action=secure_pdf_viewer&source=member&id=' . urlencode($r['id']));
    }

    public function readMemberPdfContent() {
        if (!member()) exit('Unauthorized');
        $mid = (int)$_SESSION['member'];
        $rid = (int)($_GET['id'] ?? 0);
        
        $stmt = $this->db->prepare("SELECT r.id, r.duration_minutes, r.started_reading_at, r.expires_at, e.pdf_file FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE r.id = ? AND r.member_id = ? AND r.status = 'Approved' AND (r.started_reading_at IS NULL OR r.expires_at > DATE_SUB(NOW(), INTERVAL 10 SECOND)) LIMIT 1");
        $stmt->bind_param("ii", $rid, $mid);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$r) {
            $stmt = $this->db->prepare("SELECT r.id, r.duration_minutes, r.started_reading_at, r.expires_at, e.pdf_file FROM reading_requests r JOIN ebooks e ON e.id = r.ebook_id WHERE r.ebook_id = ? AND r.member_id = ? AND r.status = 'Approved' AND (r.started_reading_at IS NULL OR r.expires_at > DATE_SUB(NOW(), INTERVAL 10 SECOND)) ORDER BY r.id DESC LIMIT 1");
            $stmt->bind_param("ii", $rid, $mid);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
        
        if ($r && !empty($r['pdf_file'])) {
            if (empty($r['started_reading_at'])) {
                $duration = !empty($r['duration_minutes']) ? (int)$r['duration_minutes'] : 15;
                $upStmt = $this->db->prepare("UPDATE reading_requests SET started_reading_at = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?");
                $upStmt->bind_param("ii", $duration, $r['id']);
                $upStmt->execute();
                $upStmt->close();
            }
            $file = dirname(__DIR__, 2) . '/uploads/' . basename($r['pdf_file']);
            if (is_file($file)) {
                stream_file_ranged($file, 'application/pdf', true, 300);
            }
        }
        http_response_code(403);
        exit('File not found or e-reading permission has expired.');
    }
}

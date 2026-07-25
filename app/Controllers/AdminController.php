<?php
namespace App\Controllers;

use mysqli;

final class AdminController {
    private $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    private function checkAdmin() {
        if (!admin()) {
            http_response_code(403);
            exit('Unauthorized');
        }
    }

    public function pollNotifications() {
        session_write_close();
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        if (!admin()) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        
        $req_count = (int)$this->db->query("SELECT COUNT(*) c FROM reading_requests WHERE status='Pending'")->fetch_assoc()['c'];
        $prt_count = (int)$this->db->query("SELECT COUNT(*) c FROM print_requests WHERE status='Pending'")->fetch_assoc()['c'];
        
        $read_query = $this->db->query("
            SELECT r.id, e.title, m.name as member_name, r.requested_at,
                   TIMESTAMPDIFF(SECOND, r.requested_at, NOW()) as age_secs
            FROM reading_requests r 
            JOIN ebooks e ON e.id = r.ebook_id 
            JOIN members m ON m.id = r.member_id 
            WHERE r.status = 'Pending' 
            ORDER BY r.id DESC LIMIT 15
        ");
        $recent_reading = [];
        while ($row = $read_query->fetch_assoc()) {
            $recent_reading[] = [
                'id' => (int)$row['id'],
                'type' => 'reading',
                'title' => $row['title'],
                'member' => $row['member_name'],
                'time' => $row['requested_at'],
                'age_secs' => (int)$row['age_secs']
            ];
        }
        
        $print_query = $this->db->query("
            SELECT p.id, e.title, m.name as member_name, p.pages, p.requested_at,
                   TIMESTAMPDIFF(SECOND, p.requested_at, NOW()) as age_secs
            FROM print_requests p 
            JOIN ebooks e ON e.id = p.ebook_id 
            JOIN members m ON m.id = p.member_id 
            WHERE p.status = 'Pending' 
            ORDER BY p.id DESC LIMIT 15
        ");
        $recent_print = [];
        while ($row = $print_query->fetch_assoc()) {
            $recent_print[] = [
                'id' => (int)$row['id'],
                'type' => 'print',
                'title' => $row['title'],
                'member' => $row['member_name'],
                'pages' => $row['pages'],
                'time' => $row['requested_at'],
                'age_secs' => (int)$row['age_secs']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'reading_pending_count' => $req_count,
            'print_pending_count' => $prt_count,
            'recent_reading' => $recent_reading,
            'recent_print' => $recent_print
        ]);
        exit;
    }

    public function updateProfile() {
        $this->checkAdmin();
        $admin_id = (int)$_SESSION['admin'];
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        if ($username === '') {
            flash('⚠️ User ID is required.');
            go('?action=admin&tab=profile');
        }
        
        if ($password !== '') {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("UPDATE admins SET username = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssi", $username, $hashed, $admin_id);
        } else {
            $stmt = $this->db->prepare("UPDATE admins SET username = ? WHERE id = ?");
            $stmt->bind_param("si", $username, $admin_id);
        }
        
        try {
            $ok = $stmt->execute();
            $stmt->close();
            flash($ok ? 'Librarian profile updated successfully.' : '⚠️ User ID already exists.');
        } catch (\mysqli_sql_exception $e) {
            flash('⚠️ User ID already exists in system.');
        }
        go('?action=admin&tab=profile');
    }

    public function generateDbBackup() {
        $this->checkAdmin();
        try {
            $backupService = new \App\Services\BackupService($this->db);
            $res = $backupService->createDatabaseBackup();
            flash("✅ Database backup created successfully: {$res['filename']} ({$res['size_formatted']})");
        } catch (\Exception $e) {
            flash("⚠️ Backup failed: " . $e->getMessage());
        }
        go('?action=admin&tab=backups');
    }

    public function generateFullBackup() {
        $this->checkAdmin();
        try {
            $backupService = new \App\Services\BackupService($this->db);
            $res = $backupService->createFullSystemBackup();
            flash("✅ Complete system backup (.zip) created successfully: {$res['filename']} ({$res['size_formatted']})");
        } catch (\Exception $e) {
            flash("⚠️ Full system backup failed: " . $e->getMessage());
        }
        go('?action=admin&tab=backups');
    }

    public function downloadBackup() {
        $this->checkAdmin();
        $file = $_GET['file'] ?? '';
        if ($file === '') {
            flash('⚠️ No backup file specified.');
            go('?action=admin&tab=backups');
        }
        $backupService = new \App\Services\BackupService($this->db);
        $backupService->downloadBackup($file);
        exit;
    }

    public function deleteBackup() {
        $this->checkAdmin();
        $file = $_POST['file'] ?? '';
        if ($file !== '') {
            $backupService = new \App\Services\BackupService($this->db);
            if ($backupService->deleteBackup($file)) {
                flash("✅ Backup file '{$file}' was deleted successfully.");
            } else {
                flash("⚠️ Failed to delete backup file or file not found.");
            }
        }
        go('?action=admin&tab=backups');
    }

    public function restoreBackup() {
        $this->checkAdmin();
        $file = $_POST['file'] ?? '';
        
        if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
            $uploadedTmp = $_FILES['backup_file']['tmp_name'];
            $uploadedName = $_FILES['backup_file']['name'];
            $ext = strtolower(pathinfo($uploadedName, PATHINFO_EXTENSION));
            if ($ext !== 'sql') {
                flash("⚠️ Only .sql backup files can be uploaded for database restoration.");
                go('?action=admin&tab=backups');
            }
            $safeName = 'uploaded_restore_' . date('Y-m-d_H-i-s') . '.sql';
            $backupService = new \App\Services\BackupService($this->db);
            $targetPath = $backupService->getBackupDir() . '/' . $safeName;
            if (move_uploaded_file($uploadedTmp, $targetPath)) {
                $file = $safeName;
            }
        }

        if ($file === '') {
            flash('⚠️ Please select or upload a valid .sql backup file to restore.');
            go('?action=admin&tab=backups');
        }

        try {
            $backupService = new \App\Services\BackupService($this->db);
            $res = $backupService->restoreDatabaseBackup($file);
            flash("✅ " . $res['message']);
        } catch (\Exception $e) {
            flash("⚠️ Database restoration failed: " . $e->getMessage());
        }
        go('?action=admin&tab=backups');
    }

    public function addCategory() {
        $this->checkAdmin();
        $n = trim($_POST['name'] ?? '');
        if ($n !== '') {
            $stmt = $this->db->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $n);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            if ($affected > 0) {
                flash('Category "' . e($n) . '" created successfully.');
            } else {
                flash('⚠️ Category name "' . e($n) . '" already exists.');
            }
        } else {
            flash('⚠️ Category name cannot be empty.');
        }
        go('?action=admin&tab=categories');
    }

    public function deleteCategory() {
        $this->checkAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $catName = 'Category';
            $cStmt = $this->db->prepare("SELECT name FROM categories WHERE id = ? LIMIT 1");
            if ($cStmt) {
                $cStmt->bind_param("i", $id);
                $cStmt->execute();
                $cRes = $cStmt->get_result()->fetch_assoc();
                if ($cRes) $catName = '"' . e($cRes['name']) . '"';
                $cStmt->close();
            }

            $ebsStmt = $this->db->prepare("SELECT pdf_file FROM ebooks WHERE category_id = ?");
            if ($ebsStmt) {
                $ebsStmt->bind_param("i", $id);
                $ebsStmt->execute();
                $res = $ebsStmt->get_result();
                while ($eb = $res->fetch_assoc()) {
                    $f_file = 'uploads/' . basename($eb['pdf_file']);
                    if (is_file($f_file)) {
                        @unlink($f_file);
                    }
                }
                $ebsStmt->close();
            }

            try {
                $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $affected = $stmt->affected_rows;
                $stmt->close();
                if ($affected > 0) {
                    flash($catName . ' and all associated e-books deleted successfully.');
                } else {
                    flash('⚠️ Category not found or already deleted.');
                }
            } catch (\mysqli_sql_exception $e) {
                flash('⚠️ Failed to delete category due to a database constraint.');
            }
        } else {
            flash('⚠️ Invalid category ID specified.');
        }
        go('?action=admin&tab=categories');
    }

    public function uploadChunk() {
        header('Content-Type: application/json');
        if (!admin()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
            exit;
        }
        $csrf_header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($csrf_header) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrf_header)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'CSRF verification failed']);
            exit;
        }
        
        if (isset($_FILES['chunk']['error']) && $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
            $err_code = $_FILES['chunk']['error'];
            $err_msg = "PHP Upload Error Code: " . $err_code;
            if ($err_code === 1 || $err_code === 2) {
                $err_msg = "The uploaded chunk exceeds the upload_max_filesize directive in php.ini.";
            } elseif ($err_code === 3) {
                $err_msg = "The file was only partially uploaded.";
            } elseif ($err_code === 4) {
                $err_msg = "No file chunk was uploaded.";
            }
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $err_msg]);
            exit;
        }
        
        $upload_id = $_POST['upload_id'] ?? '';
        if (!preg_match('/^[a-f0-9\-]{36}$/i', $upload_id)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid upload UUID format']);
            exit;
        }
        
        $chunk_index = isset($_POST['chunk_index']) ? (int)$_POST['chunk_index'] : -1;
        $total_chunks = isset($_POST['total_chunks']) ? (int)$_POST['total_chunks'] : 0;
        
        if ($chunk_index < 0 || $total_chunks <= 0 || empty($_FILES['chunk']['tmp_name'])) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Malformed chunk upload request parameters or empty temporary chunk file.']);
            exit;
        }
        
        $chunk_dir = 'uploads/chunks/' . $upload_id;
        if (!is_dir($chunk_dir)) {
            mkdir($chunk_dir, 0755, true);
        }
        
        $padded_index = str_pad($chunk_index, 5, '0', STR_PAD_LEFT);
        $dest_file = $chunk_dir . '/' . $padded_index;
        
        if (move_uploaded_file($_FILES['chunk']['tmp_name'], $dest_file)) {
            echo json_encode(['ok' => true, 'received' => $chunk_index]);
        } else {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Failed to persist temporary chunk slice to disk']);
        }
        exit;
    }

    public function assembleUpload() {
        header('Content-Type: application/json');
        if (!admin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'CSRF verification failed']);
            exit;
        }
        
        $upload_id = $_POST['upload_id'] ?? '';
        if (!preg_match('/^[a-f0-9\-]{36}$/i', $upload_id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid upload UUID format']);
            exit;
        }
        
        $total_chunks = isset($_POST['total_chunks']) ? (int)$_POST['total_chunks'] : 0;
        $chunk_dir = 'uploads/chunks/' . $upload_id;
        
        if (!is_dir($chunk_dir) || $total_chunks <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No active chunk directory found for this session']);
            exit;
        }
        
        $chunks = glob($chunk_dir . '/*');
        if (count($chunks) !== $total_chunks) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Chunk count mismatch. Expected ' . $total_chunks . ', found ' . count($chunks)]);
            exit;
        }
        
        sort($chunks);
        if (!is_dir('uploads')) {
            mkdir('uploads', 0755, true);
        }
        
        $out_name = uniqid('book_') . '.pdf';
        $out_path = 'uploads/' . $out_name;
        
        $out = fopen($out_path, 'wb');
        if (!$out) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to initialize output destination file stream']);
            exit;
        }
        
        foreach ($chunks as $c_file) {
            $in = fopen($c_file, 'rb');
            if ($in) {
                while (!feof($in)) {
                    $buf = fread($in, 8192);
                    fwrite($out, $buf);
                }
                fclose($in);
            }
        }
        fclose($out);
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $out_path);
        finfo_close($finfo);
        
        if ($mime !== 'application/pdf') {
            unlink($out_path);
            array_map('unlink', glob($chunk_dir . '/*'));
            rmdir($chunk_dir);
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Validation error: Assembled file content-type is not a valid PDF.']);
            exit;
        }
        
        array_map('unlink', glob($chunk_dir . '/*'));
        rmdir($chunk_dir);
        
        $c = (int)($_POST['category_id'] ?? 0);
        $t = trim($_POST['title'] ?? '');
        $k = trim($_POST['keywords'] ?? '');
        $edit_id = (int)($_POST['ebook_id'] ?? 0);
        
        try {
            if ($edit_id > 0) {
                $oldStmt = $this->db->prepare("SELECT pdf_file FROM ebooks WHERE id = ?");
                $oldStmt->bind_param("i", $edit_id);
                $oldStmt->execute();
                $old_row = $oldStmt->get_result()->fetch_assoc();
                $old_pdf = $old_row['pdf_file'] ?? '';
                $oldStmt->close();
                
                if ($old_pdf !== '') {
                    $old_file = 'uploads/' . basename($old_pdf);
                    if (is_file($old_file)) {
                        unlink($old_file);
                    }
                }
                
                $stmt = $this->db->prepare("UPDATE ebooks SET category_id = ?, title = ?, keywords = ?, pdf_file = ? WHERE id = ?");
                $stmt->bind_param("isssi", $c, $t, $k, $out_name, $edit_id);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $this->db->prepare("INSERT INTO ebooks (category_id, title, keywords, pdf_file) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $c, $t, $k, $out_name);
                $stmt->execute();
                $stmt->close();
            }
            echo json_encode(['success' => true, 'filename' => $out_name]);
        } catch (\mysqli_sql_exception $e) {
            if (is_file($out_path)) @unlink($out_path);
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Duplicate E-Book Title: An E-book with title "' . $t . '" already exists in this category.']);
        }
        exit;
    }

    public function cancelUpload() {
        header('Content-Type: application/json');
        if (!admin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'CSRF verification failed']);
            exit;
        }
        
        $upload_id = $_POST['upload_id'] ?? '';
        if (preg_match('/^[a-f0-9\-]{36}$/i', $upload_id)) {
            $chunk_dir = 'uploads/chunks/' . $upload_id;
            if (is_dir($chunk_dir)) {
                array_map('unlink', glob($chunk_dir . '/*'));
                rmdir($chunk_dir);
            }
        }
        echo json_encode(['success' => true]);
        exit;
    }

    public function exportEbooksCsv() {
        $this->checkAdmin();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=ebooks_backup_' . date('Ymd_His') . '.csv');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
        fputcsv($output, ['Category', 'Title', 'Keywords']);
        
        $query = $this->db->query("SELECT e.*, c.name as category_name FROM ebooks e LEFT JOIN categories c ON c.id = e.category_id ORDER BY e.id ASC");
        while ($row = $query->fetch_assoc()) {
            fputcsv($output, [
                $row['category_name'] ?? 'General',
                $row['title'],
                $row['keywords']
            ]);
        }
        fclose($output);
        exit;
    }

    public function exportPhysicalCsv() {
        $this->checkAdmin();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=physical_books_backup_' . date('Ymd_His') . '.csv');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
        fputcsv($output, ['Category', 'Book Code', 'Title', 'Author', 'Publisher', 'Price', 'Rack Location', 'Shelf Number']);
        
        $query = $this->db->query("SELECT * FROM physical_books ORDER BY id ASC");
        while ($row = $query->fetch_assoc()) {
            fputcsv($output, [
                'Physical Books',
                $row['book_code'],
                $row['title'],
                $row['author'],
                $row['publisher'] ?? '',
                $row['price'] ?? 0.00,
                $row['shelf_number'] ?? 'Shelf A1',
                $row['shelf_number'] ?? ''
            ]);
        }
        fclose($output);
        exit;
    }

    public function importBooksCsv() {
        $this->checkAdmin();
        $type = $_POST['import_type'] ?? 'physical';
        
        if (!empty($_FILES['csv_file']['name']) && $_FILES['csv_file']['error'] === 0) {
            $filename = $_FILES['csv_file']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if ($ext === 'pdf') {
                flash("⚠️ Error: You selected a PDF file instead of a CSV metadata file. Please select a valid CSV spreadsheet.");
                go('?action=admin&tab=' . $type);
            }
            
            if ($ext === 'xlsx' || $ext === 'xls') {
                flash("⚠️ Error: Direct Excel (.xlsx/.xls) imports are not supported. Please click 'Save As' in Excel and choose 'CSV (Comma delimited) (*.csv)' format, then upload the CSV file.");
                go('?action=admin&tab=' . $type);
            }
            
            if ($ext !== 'csv' && $ext !== 'txt') {
                flash("⚠️ Error: Invalid file format selected. Please upload a valid CSV (.csv) file.");
                go('?action=admin&tab=' . $type);
            }

            $tmp = $_FILES['csv_file']['tmp_name'];
            if (($handle = fopen($tmp, "r")) !== FALSE) {
                $firstLine = fgets($handle);
                rewind($handle);
                
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }
                
                $delimiter = ',';
                if ($firstLine !== false) {
                    $numCommas = substr_count($firstLine, ',');
                    $numSemicolons = substr_count($firstLine, ';');
                    $numTabs = substr_count($firstLine, "\t");
                    
                    if ($numSemicolons > $numCommas && $numSemicolons > $numTabs) {
                        $delimiter = ';';
                    } elseif ($numTabs > $numCommas && $numTabs > $numSemicolons) {
                        $delimiter = "\t";
                    }
                }
                
                $headers = fgetcsv($handle, 1000, $delimiter);
                $imported = 0;
                $skipped = 0;
                
                while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                    if (count($data) < 3) {
                        $skipped++;
                        continue;
                    }
                    
                    $cat_name = trim($data[0]);
                    $cat_id = 1;
                    if ($cat_name !== '') {
                        $cStmt = $this->db->prepare("SELECT id FROM categories WHERE name = ? LIMIT 1");
                        $cStmt->bind_param("s", $cat_name);
                        $cStmt->execute();
                        $cRow = $cStmt->get_result()->fetch_assoc();
                        $cStmt->close();
                        
                        if ($cRow) {
                            $cat_id = $cRow['id'];
                        } else {
                            $insCat = $this->db->prepare("INSERT INTO categories (name) VALUES (?)");
                            $insCat->bind_param("s", $cat_name);
                            if ($insCat->execute()) {
                                $cat_id = $this->db->insert_id;
                            }
                            $insCat->close();
                        }
                    }
                    
                    if ($type === 'physical') {
                        $code = trim($data[1]);
                        $title = trim($data[2]);
                        $author = trim($data[3] ?? 'Unknown');
                        $publisher = trim($data[4] ?? '');
                        $price = (float)($data[5] ?? 0.00);
                        $rack = trim($data[6] ?? '');
                        $shelf_num = trim($data[7] ?? $rack);
                        
                        if ($code === '' || $title === '') {
                            $skipped++;
                            continue;
                        }
                        
                        $chkCode = $this->db->prepare("SELECT id FROM physical_books WHERE book_code = ? LIMIT 1");
                        $chkCode->bind_param("s", $code);
                        $chkCode->execute();
                        $codeExists = $chkCode->get_result()->fetch_assoc();
                        $chkCode->close();
                        
                        if ($codeExists) {
                            $skipped++;
                            continue;
                        }
                        
                        $insStmt = $this->db->prepare("INSERT INTO physical_books (book_code, title, author, publisher, price, shelf_number) VALUES (?, ?, ?, ?, ?, ?)");
                        $insStmt->bind_param("ssssds", $code, $title, $author, $publisher, $price, $shelf_num);
                        if ($insStmt->execute()) {
                            $imported++;
                        } else {
                            $skipped++;
                        }
                        $insStmt->close();
                    } else {
                        $title = trim($data[1]);
                        $keywords = trim($data[2] ?? '');
                        $pdf_placeholder = 'pending_upload.pdf';
                        
                        if ($title === '') {
                            $skipped++;
                            continue;
                        }
                        
                        $insStmt = $this->db->prepare("INSERT INTO ebooks (category_id, title, keywords, pdf_file) VALUES (?, ?, ?, ?)");
                        $insStmt->bind_param("isss", $cat_id, $title, $keywords, $pdf_placeholder);
                        if ($insStmt->execute()) {
                            $imported++;
                        } else {
                            $skipped++;
                        }
                        $insStmt->close();
                    }
                }
                fclose($handle);
                flash("🎉 Catalog Ingestion Complete: Imported {$imported} items. (Skipped/Duplicate: {$skipped})");
            } else {
                flash("⚠️ Error opening uploaded CSV file.");
            }
        } else {
            flash("⚠️ Error uploading file. Please ensure it is a valid CSV.");
        }
        go('?action=admin&tab=' . $type);
    }

    public function addEbook() {
        $this->checkAdmin();
        if (!is_dir('uploads')) mkdir('uploads', 0755, true);
        $f = $_FILES['pdf']['name'] ?? '';
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        
        if ($ext === 'pdf' && $_FILES['pdf']['error'] === 0) {
            $tmp = $_FILES['pdf']['tmp_name'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmp);
            finfo_close($finfo);
            
            if ($mime === 'application/pdf') {
                $name = uniqid('book_') . '.pdf';
                move_uploaded_file($tmp, 'uploads/' . $name);
                $c = (int)$_POST['category_id'];
                $t = trim($_POST['title'] ?? '');
                $k = trim($_POST['keywords'] ?? '');
                
                try {
                    $stmt = $this->db->prepare("INSERT INTO ebooks (category_id, title, keywords, pdf_file) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("isss", $c, $t, $k, $name);
                    $stmt->execute();
                    $stmt->close();
                    flash('E-book "' . e($t) . '" uploaded successfully.');
                } catch (\mysqli_sql_exception $e) {
                    flash('⚠️ Duplicate E-Book Title: An E-book titled "' . e($t) . '" already exists in this category.');
                }
            } else {
                flash('⚠️ Uploaded file is not a valid PDF MIME-type.');
            }
        } else {
            flash('⚠️ Only PDF files are allowed.');
        }
        go('?action=admin&tab=ebooks');
    }

    public function deleteEbook() {
        $this->checkAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $this->db->prepare("SELECT pdf_file FROM ebooks WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $x = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($x && is_file('uploads/' . $x['pdf_file'])) {
            unlink('uploads/' . $x['pdf_file']);
        }
        
        $stmt = $this->db->prepare("DELETE FROM ebooks WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        flash('E-book deleted successfully.');
        go('?action=admin&tab=ebooks');
    }

    public function updateEbook() {
        $this->checkAdmin();
        $id = (int)$_POST['id'];
        $category_id = (int)$_POST['category_id'];
        $title = trim($_POST['title'] ?? '');
        $keywords = trim($_POST['keywords'] ?? '');
        
        try {
            if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === 0) {
                $f = $_FILES['pdf']['name'] ?? '';
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    $oldStmt = $this->db->prepare("SELECT pdf_file FROM ebooks WHERE id = ?");
                    $oldStmt->bind_param("i", $id);
                    $oldStmt->execute();
                    $old = $oldStmt->get_result()->fetch_assoc();
                    $oldStmt->close();
                    if ($old && is_file('uploads/' . $old['pdf_file'])) {
                        unlink('uploads/' . $old['pdf_file']);
                    }
                    
                    $name = uniqid('book_') . '.pdf';
                    move_uploaded_file($_FILES['pdf']['tmp_name'], 'uploads/' . $name);
                    
                    $stmt = $this->db->prepare("UPDATE ebooks SET category_id = ?, title = ?, keywords = ?, pdf_file = ? WHERE id = ?");
                    $stmt->bind_param("isssi", $category_id, $title, $keywords, $name, $id);
                } else {
                    flash('Only PDF files are allowed.');
                    go('?action=admin&tab=ebooks');
                }
            } else {
                $stmt = $this->db->prepare("UPDATE ebooks SET category_id = ?, title = ?, keywords = ? WHERE id = ?");
                $stmt->bind_param("issi", $category_id, $title, $keywords, $id);
            }
            
            $stmt->execute();
            $stmt->close();
            flash('E-book updated successfully.');
        } catch (\mysqli_sql_exception $e) {
            flash('⚠️ Duplicate E-Book Title: An E-book titled "' . e($title) . '" already exists in this category.');
        }
        go('?action=admin&tab=ebooks');
    }

    public function addMember() {
        $this->checkAdmin();
        $d = trim($_POST['duration'] ?? '');
        $shift = valid_shift($this->db, $_POST['shift'] ?? '', 'Morning');
        $plan_id = isset($_POST['plan_id']) && $_POST['plan_id'] !== '' ? (int)$_POST['plan_id'] : null;
        $start = date('Y-m-d');
        
        $v = ['name', 'gender', 'guardian_name', 'mobile', 'password', 'email', 'address', 'aadhar_no', 'membership_fee', 'payment_id'];
        $data = [];
        foreach ($v as $k) {
            $data[$k] = trim($_POST[$k] ?? '');
        }
        
        if ($data['payment_id'] === '') {
            flash('⚠️ Error: Payment / Transaction ID is required.');
            go('?action=admin&tab=members');
        }
        
        $gender = in_array($data['gender'], ['Male', 'Female', 'Other']) ? $data['gender'] : 'Male';
        
        if ($data['name'] === '' || $data['mobile'] === '' || $data['aadhar_no'] === '') {
            flash('⚠️ Error: Name, Mobile, and Aadhar Number are required.');
            go('?action=admin&tab=members');
        }
        
        if (!preg_match('/^\d{12}$/', $data['aadhar_no'])) {
            flash('⚠️ Error: Aadhar ID must be exactly 12 digits (numbers only).');
            go('?action=admin&tab=members');
        }
        
        $fee = (float)$data['membership_fee'];
        $feeStr = '';
        
        if ($plan_id) {
            $pStmt = $this->db->prepare("SELECT duration, amount FROM membership_plans WHERE id = ?");
            $pStmt->bind_param("i", $plan_id);
            $pStmt->execute();
            $pRes = $pStmt->get_result()->fetch_assoc();
            if ($pRes) {
                $fee = (float)$pRes['amount'];
                $d = $pRes['duration'];
                $data['membership_fee'] = $fee;
            }
            $pStmt->close();
        }
        $validDurations = ['Yearly', 'Half Yearly', 'Quarterly', 'Monthly', 'Daily'];
        if (!in_array($d, $validDurations)) $d = 'Yearly';
        $feeStr = (string)$fee;
        $end = membership_end($d);
        
        $temp_id = 'TEMP_M_' . uniqid('', true);
        $hashed = password_hash($data['password'], PASSWORD_BCRYPT);
        
        $dup_errors = [];
        $dup_fields = [];
        
        $chkAadhar = $this->db->prepare("SELECT id FROM members WHERE aadhar_no = ? LIMIT 1");
        $chkAadhar->bind_param("s", $data['aadhar_no']);
        $chkAadhar->execute();
        if ($chkAadhar->get_result()->num_rows > 0) {
            $dup_errors[] = "Aadhar Number ('" . e($data['aadhar_no']) . "') is already registered with an existing member.";
            $dup_fields[] = 'aadhar_no';
        }
        $chkAadhar->close();
        
        if ($data['payment_id'] !== '') {
            $chkPay = $this->db->prepare("SELECT id FROM members WHERE LOWER(payment_id) = LOWER(?) LIMIT 1");
            $chkPay->bind_param("s", $data['payment_id']);
            $chkPay->execute();
            $memPayExists = ($chkPay->get_result()->num_rows > 0);
            $chkPay->close();

            $chkHist = $this->db->prepare("SELECT id FROM membership_history WHERE LOWER(payment_id) = LOWER(?) LIMIT 1");
            $chkHist->bind_param("s", $data['payment_id']);
            $chkHist->execute();
            $histPayExists = ($chkHist->get_result()->num_rows > 0);
            $chkHist->close();

            if ($memPayExists || $histPayExists) {
                $dup_errors[] = "Transaction / Payment ID ('" . e($data['payment_id']) . "') has already been recorded in the database.";
                $dup_fields[] = 'payment_id';
            }
        }

        $chkMobile = $this->db->prepare("SELECT id FROM members WHERE mobile = ? LIMIT 1");
        $chkMobile->bind_param("s", $data['mobile']);
        $chkMobile->execute();
        if ($chkMobile->get_result()->num_rows > 0) {
            $dup_errors[] = "Mobile Number ('" . e($data['mobile']) . "') is already registered with another member account.";
            $dup_fields[] = 'mobile';
        }
        $chkMobile->close();

        if (count($dup_errors) > 0) {
            $draft = $data;
            $draft['shift'] = $shift;
            $draft['plan_id'] = $plan_id;
            $draft['gender'] = $gender;
            
            foreach ($dup_fields as $fKey) {
                $draft[$fKey] = '';
            }
            $_SESSION['reg_member_draft'] = $draft;
            
            $alertMsg = "⚠️ Membership Registration Blocked (Duplicate Records Found):\n\n" . implode("\n", $dup_errors) . "\n\nNote: Rejected duplicate fields have been emptied. Non-duplicate details have been preserved.";
            flash($alertMsg);
            go('?action=admin&tab=members');
        }
        
        try {
            $stmt = $this->db->prepare("INSERT INTO members (membership_id, name, gender, guardian_name, mobile, password, email, address, aadhar_no, duration, shift, start_date, end_date, payment_id, membership_plan_id, membership_fee) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                flash('⚠️ Error: ' . $this->db->error);
                go('?action=admin&tab=members');
            }
            $stmt->bind_param("ssssssssssssssis",
                $temp_id, $data['name'], $gender, $data['guardian_name'],
                $data['mobile'], $hashed, $data['email'], $data['address'],
                $data['aadhar_no'], $d, $shift, $start, $end,
                $data['payment_id'], $plan_id, $feeStr
            );
            $ok = $stmt->execute();
            if (!$ok) {
                flash('⚠️ Error saving member: ' . $stmt->error);
                $stmt->close();
                go('?action=admin&tab=members');
            }
            $stmt->close();
            
            if ($ok) {
                unset($_SESSION['reg_member_draft']);
                $new_id = $this->db->insert_id;
                $mid_code = 'CBMDLM' . $new_id;
                $upStmt = $this->db->prepare("UPDATE members SET membership_id = ? WHERE id = ?");
                $upStmt->bind_param("si", $mid_code, $new_id);
                $upStmt->execute();
                $upStmt->close();
                log_membership_history($this->db, $new_id, 'Initial Joining');
                flash('✅ Member created successfully. Issued Membership ID: ' . $mid_code);
            }
        } catch (\mysqli_sql_exception $e) {
            flash('⚠️ Duplicate Entry Error: A member with these details already exists. ' . $e->getMessage());
        }
        go('?action=admin&tab=members');
    }

    public function approveMember() {
        $this->checkAdmin();
        $id = (int)$_POST['id'];
        $plan_id = isset($_POST['plan_id']) && $_POST['plan_id'] !== '' ? (int)$_POST['plan_id'] : null;
        $d = trim($_POST['duration'] ?? '');
        $shift = valid_shift($this->db, $_POST['shift'] ?? '', 'Morning');
        $fee = (float)($_POST['membership_fee'] ?? 0.00);
        $payment_id = trim($_POST['payment_id'] ?? '');

        if ($payment_id === '') {
            flash('⚠️ Error: Transaction / Payment Reference ID is required to approve membership.');
            go('?action=admin&tab=members&view=' . $id);
        }

        if ($plan_id) {
            $pStmt = $this->db->prepare("SELECT duration, amount FROM membership_plans WHERE id = ?");
            $pStmt->bind_param("i", $plan_id);
            $pStmt->execute();
            $pRes = $pStmt->get_result()->fetch_assoc();
            if ($pRes) {
                $fee = (float)$pRes['amount'];
                $d = $pRes['duration'];
            }
            $pStmt->close();
        }
        if ($d === '') $d = 'Yearly';

        $chkHist = $this->db->prepare("SELECT id FROM membership_history WHERE LOWER(payment_id) = LOWER(?) LIMIT 1");
        $chkHist->bind_param("s", $payment_id);
        $chkHist->execute();
        $histDup = ($chkHist->get_result()->num_rows > 0);
        $chkHist->close();

        $chkMem = $this->db->prepare("SELECT id FROM members WHERE LOWER(payment_id) = LOWER(?) AND id != ? LIMIT 1");
        $chkMem->bind_param("si", $payment_id, $id);
        $chkMem->execute();
        $memDup = ($chkMem->get_result()->num_rows > 0);
        $chkMem->close();

        if ($histDup || $memDup) {
            flash("⚠️ Duplicate Transaction Error: Payment ID ('" . e($payment_id) . "') has already been recorded for another transaction or membership. Approval rejected.");
            go('?action=admin&tab=members&view=' . $id);
        }

        $start = date('Y-m-d');
        $end = membership_end($d);
        $mid_code = 'CBMDLM' . $id;

        try {
            $stmt = $this->db->prepare("UPDATE members SET membership_id = ?, membership_plan_id = ?, membership_fee = ?, payment_id = ?, duration = ?, shift = ?, start_date = ?, end_date = ?, is_active = 1, approved = 1 WHERE id = ?");
            $stmt->bind_param("sissssssi", $mid_code, $plan_id, $fee, $payment_id, $d, $shift, $start, $end, $id);
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok) {
                log_membership_history($this->db, $id, 'Initial Joining');
                flash('✅ Member application approved successfully! Issued Membership ID: ' . $mid_code);
            } else {
                flash('⚠️ Error approving member application.');
            }
        } catch (\mysqli_sql_exception $e) {
            flash('⚠️ Duplicate Entry Error: Could not approve membership due to a duplicate transaction reference or database conflict.');
        }

        go('?action=admin&tab=members&view=' . $id);
    }

    public function updateMember() {
        $this->checkAdmin();
        $id = (int)$_POST['id'];
        
        $curMem = null;
        $mStmt = $this->db->prepare("SELECT * FROM members WHERE id = ?");
        if ($mStmt) {
            $mStmt->bind_param("i", $id);
            $mStmt->execute();
            $curMem = $mStmt->get_result()->fetch_assoc();
            $mStmt->close();
        }

        $name = trim($_POST['name'] ?? ($curMem['name'] ?? ''));
        $gender = isset($_POST['gender']) && in_array($_POST['gender'], ['Male', 'Female', 'Other']) ? $_POST['gender'] : ($curMem['gender'] ?? 'Male');
        $g_name = trim($_POST['guardian_name'] ?? ($curMem['guardian_name'] ?? ''));
        $mobile = trim($_POST['mobile'] ?? ($curMem['mobile'] ?? ''));
        $email = trim($_POST['email'] ?? ($curMem['email'] ?? ''));
        $address = trim($_POST['address'] ?? ($curMem['address'] ?? ''));
        $aadhar = trim($_POST['aadhar_no'] ?? ($curMem['aadhar_no'] ?? ''));
        
        $refTab = $_GET['tab'] ?? 'members';
        if ($aadhar !== '' && !preg_match('/^\d{12}$/', $aadhar)) {
            flash('⚠️ Error: Aadhar ID must be exactly 12 digits (numbers only).');
            go('?action=admin&tab=' . $refTab . '&view=' . $id);
        }
        
        if (isset($_POST['shift']) && $_POST['shift'] !== '') {
            $shift = valid_shift($this->db, $_POST['shift'], $curMem['shift'] ?? 'Both');
        } else {
            $shift = $curMem['shift'] ?? 'Both';
        }

        $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : ($curMem['is_active'] ?? 1);
        $pass = trim($_POST['password'] ?? '');
        
        try {
            if ($pass !== '') {
                $hashed = password_hash($pass, PASSWORD_BCRYPT);
                $stmt = $this->db->prepare("UPDATE members SET name = ?, gender = ?, guardian_name = ?, mobile = ?, email = ?, address = ?, aadhar_no = ?, shift = ?, is_active = ?, password = ? WHERE id = ?");
                $stmt->bind_param("ssssssssisi", $name, $gender, $g_name, $mobile, $email, $address, $aadhar, $shift, $is_active, $hashed, $id);
            } else {
                $stmt = $this->db->prepare("UPDATE members SET name = ?, gender = ?, guardian_name = ?, mobile = ?, email = ?, address = ?, aadhar_no = ?, shift = ?, is_active = ? WHERE id = ?");
                $stmt->bind_param("ssssssssii", $name, $gender, $g_name, $mobile, $email, $address, $aadhar, $shift, $is_active, $id);
            }
            $stmt->execute();
            $stmt->close();
            flash('Member profile updated successfully.');
        } catch (\mysqli_sql_exception $e) {
            flash('⚠️ Duplicate Entry Error: A member with this Mobile number or Aadhar number already exists.');
        }
        $refTab = $_GET['tab'] ?? 'members';
        go('?action=admin&tab=' . $refTab . '&view=' . $id);
    }

    public function renewMember() {
        $this->checkAdmin();
        $id = (int)$_POST['id'];
        $plan_id = isset($_POST['plan_id']) && $_POST['plan_id'] !== '' ? (int)$_POST['plan_id'] : null;
        $payment_id = trim($_POST['payment_id'] ?? '');
        $fee = (float)($_POST['membership_fee'] ?? 0.00);
        $d = trim($_POST['duration'] ?? '');
        $refTab = $_GET['tab'] ?? 'members';

        if ($payment_id === '') {
            flash('⚠️ Error: Transaction / Payment Reference ID is required to process renewal.');
            go('?action=admin&tab=' . $refTab . '&view=' . $id);
        }

        if ($plan_id) {
            $pStmt = $this->db->prepare("SELECT duration, amount FROM membership_plans WHERE id = ?");
            $pStmt->bind_param("i", $plan_id);
            $pStmt->execute();
            $pRes = $pStmt->get_result()->fetch_assoc();
            if ($pRes) {
                $d = $pRes['duration'];
                $fee = (float)$pRes['amount'];
            }
            $pStmt->close();
        }

        $validDurations = ['Yearly', 'Half Yearly', 'Quarterly', 'Monthly', 'Daily'];
        if (!in_array($d, $validDurations)) $d = 'Yearly';

        $mStmt = $this->db->prepare("SELECT name, start_date, end_date, is_active, approved FROM members WHERE id = ?");
        $mStmt->bind_param("i", $id);
        $mStmt->execute();
        $mRes = $mStmt->get_result()->fetch_assoc();
        $mName = $mRes['name'] ?? 'Member';
        $mStmt->close();

        $today = date('Y-m-d');
        $isCurrentlyActive = ($mRes && !empty($mRes['end_date']) && $mRes['end_date'] >= $today && ($mRes['is_active'] ?? 1) == 1 && ($mRes['approved'] ?? 1) == 1);

        if ($isCurrentlyActive) {
            $start = date('Y-m-d', strtotime($mRes['end_date'] . ' +1 day'));
        } else {
            $start = $today;
        }

        $end = membership_end($d, $start);

        $chkHist = $this->db->prepare("SELECT id FROM membership_history WHERE LOWER(payment_id) = LOWER(?) LIMIT 1");
        $chkHist->bind_param("s", $payment_id);
        $chkHist->execute();
        $histDup = ($chkHist->get_result()->num_rows > 0);
        $chkHist->close();

        $chkMem = $this->db->prepare("SELECT id FROM members WHERE LOWER(payment_id) = LOWER(?) AND id != ? LIMIT 1");
        $chkMem->bind_param("si", $payment_id, $id);
        $chkMem->execute();
        $memDup = ($chkMem->get_result()->num_rows > 0);
        $chkMem->close();

        if ($histDup || $memDup) {
            flash("⚠️ Duplicate Transaction Error: Transaction / Payment ID ('" . e($payment_id) . "') has already been recorded in the database. Renewal failed.");
            go('?action=admin&tab=' . $refTab . '&view=' . $id);
        }

        $shift = trim($_POST['shift'] ?? '');
        if ($shift !== '') $shift = valid_shift($this->db, $shift);

        $this->db->begin_transaction();
        try {
            if ($shift !== '') {
                $stmt = $this->db->prepare("UPDATE members SET duration = ?, start_date = ?, end_date = ?, payment_id = ?, membership_plan_id = ?, membership_fee = ?, shift = ?, is_active = 1 WHERE id = ?");
                $stmt->bind_param("ssssidsi", $d, $start, $end, $payment_id, $plan_id, $fee, $shift, $id);
            } else {
                $stmt = $this->db->prepare("UPDATE members SET duration = ?, start_date = ?, end_date = ?, payment_id = ?, membership_plan_id = ?, membership_fee = ?, is_active = 1 WHERE id = ?");
                $stmt->bind_param("ssssidi", $d, $start, $end, $payment_id, $plan_id, $fee, $id);
            }
            $stmt->execute();
            $stmt->close();

            log_membership_history($this->db, $id, 'Renewal');
            $this->db->commit();

            if ($isCurrentlyActive) {
                $msg = '✅ Early Renewal Applied for ' . e($mName) . '! Active pass extended: New term starts ' . date('d-m-Y', strtotime($start)) . ' and valid until ' . date('d-m-Y', strtotime($end)) . ' (' . e($d) . ', Fee: ₹' . number_format($fee, 2) . ').';
            } else {
                $msg = '✅ Membership Renewed Successfully for ' . e($mName) . '! New active term: ' . date('d-m-Y', strtotime($start)) . ' to ' . date('d-m-Y', strtotime($end)) . ' (' . e($d) . ', Fee: ₹' . number_format($fee, 2) . ').';
            }
            flash($msg);
        } catch (\Throwable $e) {
            $this->db->rollback();
            flash('⚠️ Duplicate Entry Error: Could not process renewal due to a duplicate transaction reference or database constraint.');
        }

        go('?action=admin&tab=' . $refTab . '&view=' . $id);
    }

    public function deleteMember() {
        $this->checkAdmin();
        $id = (int)$_POST['id'];
        try {
            $stmt = $this->db->prepare("DELETE FROM members WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            flash('Member deleted successfully.');
        } catch (\mysqli_sql_exception $e) {
            flash('⚠️ Integrity Warning: This member account cannot be deleted because they have associated physical book lending logs. Please preserve circulation history.');
        }
        go('?action=admin&tab=members');
    }

    public function addPlan() {
        $this->checkAdmin();
        $name = trim($_POST['name'] ?? '');
        $duration = trim($_POST['duration'] ?? 'Yearly');
        $amount = (float)($_POST['amount'] ?? 0.00);
        
        if ($name !== '') {
            try {
                $stmt = $this->db->prepare("INSERT INTO membership_plans (name, duration, amount) VALUES (?, ?, ?)");
                $stmt->bind_param("ssd", $name, $duration, $amount);
                $stmt->execute();
                $stmt->close();
                flash('Membership plan "' . e($name) . '" created successfully.');
            } catch (\mysqli_sql_exception $e) {
                flash('⚠️ Duplicate Plan Name: A membership plan named "' . e($name) . '" already exists.');
            }
        } else {
            flash('⚠️ Plan name is required.');
        }
        go('?action=admin&tab=active_plans');
    }

    public function updatePlan() {
        $this->checkAdmin();
        $id = (int)$_POST['id'];
        $name = trim($_POST['name'] ?? '');
        $duration = trim($_POST['duration'] ?? 'Yearly');
        $amount = (float)($_POST['amount'] ?? 0.00);
        
        if ($name !== '') {
            try {
                $stmt = $this->db->prepare("UPDATE membership_plans SET name = ?, duration = ?, amount = ? WHERE id = ?");
                $stmt->bind_param("ssdi", $name, $duration, $amount, $id);
                $stmt->execute();
                $stmt->close();
                flash('Membership plan "' . e($name) . '" updated successfully.');
            } catch (\mysqli_sql_exception $e) {
                flash('⚠️ Duplicate Plan Name: A membership plan named "' . e($name) . '" already exists.');
            }
        } else {
            flash('⚠️ Plan name is required.');
        }
        go('?action=admin&tab=active_plans');
    }

    public function deletePlan() {
        $this->checkAdmin();
        $id = (int)$_POST['id'];
        try {
            $stmt = $this->db->prepare("DELETE FROM membership_plans WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            flash('Membership plan deleted successfully.');
        } catch (\mysqli_sql_exception $e) {
            flash('⚠️ Integrity Warning: Cannot delete a membership plan that is assigned to existing members.');
        }
        go('?action=admin&tab=active_plans');
    }

    public function deleteShift() {
        $this->checkAdmin();
        $id = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));
        if ($id > 0) {
            $stmtS = $this->db->prepare("SELECT name FROM work_shifts WHERE id = ?");
            $stmtS->bind_param("i", $id);
            $stmtS->execute();
            $shiftRow = $stmtS->get_result()->fetch_assoc();
            $stmtS->close();

            if ($shiftRow) {
                $sName = $shiftRow['name'];
                $stmt = $this->db->prepare("DELETE FROM work_shifts WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();

                $stmtM = $this->db->prepare("UPDATE members SET shift = 'Both' WHERE shift = ?");
                $stmtM->bind_param("s", $sName);
                $stmtM->execute();
                $stmtM->close();
                
                flash("Shift '" . $sName . "' deleted successfully. Any members assigned to this shift were defaulted to 'Both'.");
            }
        }
        go('?action=admin&tab=shift_timings');
    }

    public function saveShiftTimes() {
        $this->checkAdmin();
        $shift_ids = $_POST['shift_id'] ?? [];
        $shift_names = $_POST['shift_name'] ?? [];
        $start_times = $_POST['start_time'] ?? [];
        $end_times = $_POST['end_time'] ?? [];
        
        for ($i = 0; $i < count($shift_names); $i++) {
            $sid = (int)($shift_ids[$i] ?? 0);
            $sname = trim($shift_names[$i]);
            $stime = trim($start_times[$i]);
            $etime = trim($end_times[$i]);
            
            if ($sname !== '' && $stime !== '' && $etime !== '') {
                if (strlen($stime) == 5) $stime .= ':00';
                if (strlen($etime) == 5) $etime .= ':00';
                
                if ($sid > 0) {
                    $stmtOld = $this->db->prepare("SELECT name FROM work_shifts WHERE id = ?");
                    $stmtOld->bind_param("i", $sid);
                    $stmtOld->execute();
                    $oldRow = $stmtOld->get_result()->fetch_assoc();
                    $stmtOld->close();

                    $stmt = $this->db->prepare("UPDATE work_shifts SET name = ?, start_time = ?, end_time = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $sname, $stime, $etime, $sid);
                    $stmt->execute();
                    $stmt->close();

                    if ($oldRow && $oldRow['name'] !== $sname) {
                        $stmtM = $this->db->prepare("UPDATE members SET shift = ? WHERE shift = ?");
                        $stmtM->bind_param("ss", $sname, $oldRow['name']);
                        $stmtM->execute();
                        $stmtM->close();
                    }
                } else {
                    $stmt = $this->db->prepare("INSERT INTO work_shifts (name, start_time, end_time) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE start_time = VALUES(start_time), end_time = VALUES(end_time)");
                    $stmt->bind_param("sss", $sname, $stime, $etime);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
        
        $custom_name = trim($_POST['custom_shift_name'] ?? '');
        $custom_start = trim($_POST['custom_start_time'] ?? '');
        $custom_end = trim($_POST['custom_end_time'] ?? '');
        
        if ($custom_name !== '' && $custom_start !== '' && $custom_end !== '') {
            if (strlen($custom_start) == 5) $custom_start .= ':00';
            if (strlen($custom_end) == 5) $custom_end .= ':00';
            
            $stmt = $this->db->prepare("INSERT INTO work_shifts (name, start_time, end_time) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE start_time = VALUES(start_time), end_time = VALUES(end_time)");
            $stmt->bind_param("sss", $custom_name, $custom_start, $custom_end);
            $stmt->execute();
            $stmt->close();
            flash("Work Shift configuration and custom shift '" . $custom_name . "' saved.");
        } else {
            flash('Work Shift timing configurations updated successfully.');
        }
        go('?action=admin&tab=shift_timings');
    }

    public function settleFine() {
        $this->checkAdmin();
        $id = (int)$_POST['id'];
        $status = $_POST['fine_status'] ?? 'Paid';
        if (!in_array($status, ['Paid', 'Waived'])) {
            $status = 'Paid';
        }
        $pay_id = trim($_POST['fine_payment_id'] ?? '');
        $amount = max(0.00, (float)($_POST['fine_amount'] ?? 0.00));
        
        $stmt = $this->db->prepare("UPDATE lendings SET fine_amount = ?, fine_status = ?, fine_payment_id = ? WHERE id = ?");
        $stmt->bind_param("dssi", $amount, $status, $pay_id, $id);
        $stmt->execute();
        $stmt->close();
        
        flash('Fine settled successfully.');
        $ref = $_POST['tab'] ?? 'view_lending';
        if (!in_array($ref, ['lending', 'view_lending', 'dashboard'])) {
            $ref = 'view_lending';
        }
        go('?action=admin&tab=' . $ref);
    }

    public function updatePhysical() {
        $this->checkAdmin();
        $id = (int)$_POST['id'];
        $shelf_number = trim($_POST['shelf_number'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $book_code = trim($_POST['book_code'] ?? '');
        $price = (float)($_POST['price'] ?? 0.00);
        $author = trim($_POST['author'] ?? '');
        $publisher = trim($_POST['publisher'] ?? '');
        
        try {
            $stmt = $this->db->prepare("UPDATE physical_books SET shelf_number = ?, title = ?, book_code = ?, price = ?, author = ?, publisher = ? WHERE id = ?");
            $stmt->bind_param("sssdssi", $shelf_number, $title, $book_code, $price, $author, $publisher, $id);
            $stmt->execute();
            $stmt->close();
            flash('Physical book updated successfully.');
        } catch (\mysqli_sql_exception $e) {
            flash('⚠️ Duplicate Book Code / Bar Code: A physical book with ID "' . e($book_code) . '" already exists in the catalog database.');
        }
        go('?action=admin&tab=physical');
    }

    public function addPhysical() {
        $this->checkAdmin();
        $shelf_number = trim($_POST['shelf_number'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $book_code = trim($_POST['book_code'] ?? '');
        $price = (float)($_POST['price'] ?? 0.00);
        $author = trim($_POST['author'] ?? '');
        $publisher = trim($_POST['publisher'] ?? '');
        
        try {
            $stmt = $this->db->prepare("INSERT INTO physical_books (shelf_number, title, book_code, price, author, publisher) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdss", $shelf_number, $title, $book_code, $price, $author, $publisher);
            $stmt->execute();
            $stmt->close();
            flash('Physical book added successfully.');
        } catch (\mysqli_sql_exception $e) {
            flash('⚠️ Duplicate Book Code / Bar Code: A physical book with ID "' . e($book_code) . '" already exists in the catalog database.');
        }
        go('?action=admin&tab=physical');
    }

    public function deletePhysical() {
        $this->checkAdmin();
        $id = (int)($_POST['id'] ?? 0);
        try {
            $stmt = $this->db->prepare("DELETE FROM physical_books WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            flash('Physical book deleted.');
        } catch (\mysqli_sql_exception $e) {
            flash('⚠️ Integrity Warning: This book volume cannot be deleted because it has active/past lending transactions logged.');
        }
        go('?action=admin&tab=physical');
    }

    public function approveRequest() {
        $this->checkAdmin();
        $id = (int)$_POST['request_id'];
        $mins = min(240, max(1, (int)$_POST['minutes']));
        
        $stmt = $this->db->prepare("UPDATE reading_requests SET status = 'Approved', approved_at = NOW(), duration_minutes = ?, started_reading_at = NULL, expires_at = NULL WHERE id = ?");
        $stmt->bind_param("ii", $mins, $id);
        $stmt->execute();
        $stmt->close();
        
        flash('Reading permission approved successfully. Session timer will start when member clicks Read Now.');
        go('?action=admin&tab=requests');
    }

    public function rejectRequest() {
        $this->checkAdmin();
        $id = (int)($_POST['request_id'] ?? $_POST['id'] ?? 0);
        $stmt = $this->db->prepare("UPDATE reading_requests SET status = 'Rejected' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        flash('Request rejected successfully.');
        go('?action=admin&tab=requests');
    }

    public function lendBook() {
        $this->checkAdmin();
        $find = trim($_POST['member'] ?? '');
        $b = trim($_POST['book_code'] ?? '');
        $date = date('Y-m-d H:i:s');
        $due = $_POST['due_date'] ?? '';
        $tx = trim($_POST['transaction_id'] ?? '');
        
        if (!$tx) {
            flash('⚠️ Payment / Transaction ID is mandatory.');
            go('?action=admin&tab=lending');
        }
        
        $mStmt = $this->db->prepare("SELECT id, is_active, end_date FROM members WHERE membership_id = ? OR mobile = ? LIMIT 1");
        $mStmt->bind_param("ss", $find, $find);
        $mStmt->execute();
        $m = $mStmt->get_result()->fetch_assoc();
        $mStmt->close();
        
        if ($m) {
            if ($m['is_active'] == 0) {
                flash('⚠️ Issue Blocked: This member account is currently suspended/inactive.');
                go('?action=admin&tab=lending');
            }
            if (strtotime($m['end_date']) < time()) {
                flash('⚠️ Issue Blocked: This member account has expired.');
                go('?action=admin&tab=lending');
            }
        }
        
        $bStmt = $this->db->prepare("SELECT p.id FROM physical_books p WHERE p.book_code = ? AND NOT EXISTS (SELECT 1 FROM lendings l WHERE l.physical_book_id = p.id AND l.returned_at IS NULL) LIMIT 1");
        $bStmt->bind_param("s", $b);
        $bStmt->execute();
        $book = $bStmt->get_result()->fetch_assoc();
        $bStmt->close();
        
        if ($m && $book) {
            $lStmt = $this->db->prepare("INSERT INTO lendings (member_id, physical_book_id, lent_at, due_date, transaction_id) VALUES (?, ?, ?, ?, ?)");
            $lStmt->bind_param("iisss", $m['id'], $book['id'], $date, $due, $tx);
            $lStmt->execute();
            $lStmt->close();
            
            $this->db->query("UPDATE hold_requests SET status = 'Completed' WHERE physical_book_id = " . (int)$book['id'] . " AND member_id = " . (int)$m['id'] . " AND status IN ('Active', 'Awaiting Collection')");
            
            flash('Book issued successfully.');
        } else {
            flash('⚠️ Member or available book copy was not found.');
        }
        go('?action=admin&tab=lending');
    }

    public function returnBook() {
        $this->checkAdmin();
        $id = (int)$_POST['id'];
        
        $pbStmt = $this->db->prepare("SELECT physical_book_id FROM lendings WHERE id = ? LIMIT 1");
        $pbStmt->bind_param("i", $id);
        $pbStmt->execute();
        $pbRow = $pbStmt->get_result()->fetch_assoc();
        $pbStmt->close();
        
        $stmt = $this->db->prepare("UPDATE lendings SET returned_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        if ($pbRow) {
            $book_id = $pbRow['physical_book_id'];
            
            $holdStmt = $this->db->prepare("SELECT h.id, m.name FROM hold_requests h JOIN members m ON m.id = h.member_id WHERE h.physical_book_id = ? AND h.status = 'Active' ORDER BY h.id ASC LIMIT 1");
            $holdStmt->bind_param("i", $book_id);
            $holdStmt->execute();
            $holdRow = $holdStmt->get_result()->fetch_assoc();
            $holdStmt->close();
            
            if ($holdRow) {
                $this->db->query("UPDATE hold_requests SET status = 'Awaiting Collection' WHERE id = " . (int)$holdRow['id']);
                flash("🎉 Book returned successfully! NOTE: This book has an active hold reservation by member '" . e($holdRow['name']) . "'. It has been placed in 'Awaiting Collection' status.");
            } else {
                flash('Book returned successfully and marked as available.');
            }
        } else {
            flash('Book returned successfully.');
        }
        $ref = $_POST['tab'] ?? 'lending';
        go('?action=admin&tab=' . $ref);
    }

    public function completePrint() {
        $this->checkAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $this->db->prepare("UPDATE print_requests SET status = 'Completed' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        flash('Print request marked as completed successfully.');
        go('?action=admin&tab=prints');
    }

    public function rejectPrint() {
        $this->checkAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $this->db->prepare("UPDATE print_requests SET status = 'Rejected' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        flash('Print request rejected successfully.');
        go('?action=admin&tab=prints');
    }
}

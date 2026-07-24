<?php
namespace App\Services;

use mysqli;
use ZipArchive;
use Exception;

/**
 * Class BackupService
 * Market-standard, secure database & full system backup engine for MCB Digital Library.
 */
class BackupService {
    private mysqli $db;
    private string $backupDir;

    public function __construct(mysqli $db) {
        $this->db = $db;
        $this->backupDir = __DIR__ . '/../../backups';
        $this->ensureDirectoryAndSecurity();
    }

    /**
     * Ensure backup directory exists and is secured with .htaccess and index.html
     */
    private function ensureDirectoryAndSecurity(): void {
        if (!is_dir($this->backupDir)) {
            @mkdir($this->backupDir, 0755, true);
        }

        // Secure directory from direct URL access via Apache
        $htaccessPath = $this->backupDir . '/.htaccess';
        if (!file_exists($htaccessPath)) {
            $htaccessContent = "# Prevent direct public web access to backup files\nDeny from all\n";
            @file_put_contents($htaccessPath, $htaccessContent);
        }

        $indexPath = $this->backupDir . '/index.html';
        if (!file_exists($indexPath)) {
            @file_put_contents($indexPath, '<!-- Access Denied -->');
        }
    }

    /**
     * Get target backup directory path
     */
    public function getBackupDir(): string {
        return $this->backupDir;
    }

    /**
     * Generate a complete standalone SQL Database Dump
     */
    public function createDatabaseBackup(string $prefix = 'cbmdl_db_backup'): array {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "{$prefix}_{$timestamp}.sql";
        $filepath = $this->backupDir . '/' . $filename;

        // Fetch all tables
        $tables = [];
        $result = $this->db->query("SHOW TABLES");
        if ($result) {
            while ($row = $result->fetch_row()) {
                $tables[] = $row[0];
            }
        }

        if (empty($tables)) {
            throw new Exception("No tables found in the database to backup.");
        }

        $sql = "-- ========================================================\n";
        $sql .= "-- Cantonment Digital Library (MCB) Database Backup\n";
        $sql .= "-- Generated On: " . date('Y-m-d H:i:s T') . "\n";
        $sql .= "-- Database Host: " . $this->db->host_info . "\n";
        $sql .= "-- PHP Version: " . PHP_VERSION . "\n";
        $sql .= "-- ========================================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql .= "SET AUTOCOMMIT = 0;\n";
        $sql .= "START TRANSACTION;\n\n";

        foreach ($tables as $table) {
            // Drop table statement
            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "-- Table structure for table `{$table}`\n";
            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";

            // Create table statement
            $createRes = $this->db->query("SHOW CREATE TABLE `{$table}`");
            if ($createRes && $createRow = $createRes->fetch_row()) {
                $sql .= $createRow[1] . ";\n\n";
            }

            // Dump data
            $dataRes = $this->db->query("SELECT * FROM `{$table}`");
            if ($dataRes && $dataRes->num_rows > 0) {
                $sql .= "-- Dumping data for table `{$table}`\n";
                $numFields = $dataRes->field_count;

                while ($row = $dataRes->fetch_row()) {
                    $sql .= "INSERT INTO `{$table}` VALUES(";
                    for ($i = 0; $i < $numFields; $i++) {
                        if (isset($row[$i])) {
                            // Properly escape string values
                            $escaped = $this->db->real_escape_string($row[$i]);
                            // Escape line breaks for clean multi-line SQL parsing
                            $escaped = str_replace(["\r\n", "\r", "\n"], ["\\r\\n", "\\r", "\\n"], $escaped);
                            $sql .= '"' . $escaped . '"';
                        } else {
                            $sql .= "NULL";
                        }
                        if ($i < ($numFields - 1)) {
                            $sql .= ", ";
                        }
                    }
                    $sql .= ");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $sql .= "COMMIT;\n";
        $sql .= "-- End of Backup\n";

        if (file_put_contents($filepath, $sql) === false) {
            throw new Exception("Failed to write SQL backup file to {$filepath}");
        }

        $filesize = filesize($filepath);
        $checksum = hash_file('sha256', $filepath);

        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'type' => 'Database Dump (.sql)',
            'size_bytes' => $filesize,
            'size_formatted' => $this->formatBytes($filesize),
            'checksum' => $checksum,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Generate a Full System Backup (.zip) containing DB SQL dump + Uploaded Files
     */
    public function createFullSystemBackup(): array {
        if (!class_exists('ZipArchive')) {
            throw new Exception("ZipArchive PHP extension is not enabled on this server.");
        }

        // Step 1: Create fresh database dump
        $dbBackup = $this->createDatabaseBackup('cbmdl_db_snapshot');

        // Step 2: Create zip file
        $timestamp = date('Y-m-d_H-i-s');
        $zipFilename = "cbmdl_full_backup_{$timestamp}.zip";
        $zipFilepath = $this->backupDir . '/' . $zipFilename;

        $zip = new ZipArchive();
        if ($zip->open($zipFilepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($dbBackup['filepath']);
            throw new Exception("Could not create ZIP archive at {$zipFilepath}");
        }

        // Add SQL dump into Zip
        $zip->addFile($dbBackup['filepath'], 'database_dump.sql');

        // Add Manifest JSON
        $manifest = [
            'system' => 'Cantonment Digital Library (MCB)',
            'created_at' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'db_checksum' => $dbBackup['checksum'],
            'contents' => [
                'database_dump.sql',
                'uploads/'
            ]
        ];
        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

        // Add uploads directory recursively
        $uploadsDir = __DIR__ . '/../../uploads';
        if (is_dir($uploadsDir)) {
            $this->addDirectoryToZip($zip, $uploadsDir, 'uploads');
        }

        $zip->close();

        // Clean up temp DB snapshot file
        @unlink($dbBackup['filepath']);

        if (!file_exists($zipFilepath)) {
            throw new Exception("ZIP archive creation failed.");
        }

        $filesize = filesize($zipFilepath);
        $checksum = hash_file('sha256', $zipFilepath);

        return [
            'success' => true,
            'filename' => $zipFilename,
            'filepath' => $zipFilepath,
            'type' => 'Full System Archive (.zip)',
            'size_bytes' => $filesize,
            'size_formatted' => $this->formatBytes($filesize),
            'checksum' => $checksum,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Helper to recursively add directory contents into ZipArchive
     */
    private function addDirectoryToZip(ZipArchive $zip, string $sourceDir, string $localDirName): void {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen(realpath($sourceDir)) + 1);
            $relativePath = str_replace('\\', '/', $relativePath);
            $zipPath = $localDirName . '/' . $relativePath;

            if ($file->isDir()) {
                $zip->addEmptyDir($zipPath);
            } else {
                $zip->addFile($filePath, $zipPath);
            }
        }
    }

    /**
     * Get list of all available backups in order of creation (newest first)
     */
    public function getBackupList(): array {
        $backups = [];
        if (!is_dir($this->backupDir)) {
            return $backups;
        }

        $files = scandir($this->backupDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === '.htaccess' || $file === 'index.html') {
                continue;
            }

            $filepath = $this->backupDir . '/' . $file;
            if (!is_file($filepath)) {
                continue;
            }

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ($ext !== 'sql') {
                continue;
            }

            $filesize = filesize($filepath);
            $mtime = filemtime($filepath);

            $backups[] = [
                'filename' => $file,
                'filepath' => $filepath,
                'extension' => $ext,
                'type' => ($ext === 'sql') ? 'Database Dump (.sql)' : 'Full System Archive (.zip)',
                'size_bytes' => $filesize,
                'size_formatted' => $this->formatBytes($filesize),
                'created_at' => date('Y-m-d H:i:s', $mtime),
                'timestamp' => $mtime,
                'checksum' => hash_file('sha256', $filepath)
            ];
        }

        // Sort newest first
        usort($backups, function ($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        return $backups;
    }

    /**
     * Securely stream file download with path traversal prevention
     */
    public function downloadBackup(string $filename): void {
        // Strict path traversal prevention
        $safeFilename = basename($filename);
        $filepath = $this->backupDir . '/' . $safeFilename;

        if (!file_exists($filepath) || !is_file($filepath)) {
            header("HTTP/1.1 404 Not Found");
            echo "Error: Requested backup file not found.";
            exit;
        }

        // Clean buffer to prevent corrupted file output
        if (ob_get_level()) {
            ob_end_clean();
        }

        $mimeType = (pathinfo($safeFilename, PATHINFO_EXTENSION) === 'zip') ? 'application/zip' : 'text/plain';

        header('Content-Description: Secure File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));

        readfile($filepath);
        exit;
    }

    /**
     * Restore database from an existing .sql backup file with auto pre-restore safety snapshot
     */
    public function restoreDatabaseBackup(string $filename): array {
        $safeFilename = basename($filename);
        $filepath = $this->backupDir . '/' . $safeFilename;

        if (!file_exists($filepath) || pathinfo($safeFilename, PATHINFO_EXTENSION) !== 'sql') {
            throw new Exception("Invalid or missing SQL backup file for restore.");
        }

        // 1. Create Pre-Restore Safety Snapshot first
        $safetySnapshot = $this->createDatabaseBackup('cbmdl_prerestore_safety');

        // 2. Read SQL statements
        $sqlContent = file_get_contents($filepath);
        if (empty($sqlContent)) {
            throw new Exception("SQL backup file is empty.");
        }

        // Execute SQL script
        $this->db->query("SET FOREIGN_KEY_CHECKS=0");
        $this->db->query("SET AUTOCOMMIT=0");

        if ($this->db->multi_query($sqlContent)) {
            do {
                if ($result = $this->db->store_result()) {
                    $result->free();
                }
            } while ($this->db->more_results() && $this->db->next_result());
        }

        if ($this->db->errno) {
            $err = $this->db->error;
            $this->db->query("ROLLBACK");
            $this->db->query("SET FOREIGN_KEY_CHECKS=1");
            throw new Exception("Database restore failed: " . $err);
        }

        $this->db->query("COMMIT");
        $this->db->query("SET FOREIGN_KEY_CHECKS=1");

        return [
            'success' => true,
            'message' => 'Database restored successfully! A safety snapshot (' . $safetySnapshot['filename'] . ') was automatically created before restoration.',
            'safety_snapshot' => $safetySnapshot['filename']
        ];
    }

    /**
     * Delete a backup file securely
     */
    public function deleteBackup(string $filename): bool {
        $safeFilename = basename($filename);
        $filepath = $this->backupDir . '/' . $safeFilename;

        if (file_exists($filepath) && is_file($filepath)) {
            return @unlink($filepath);
        }

        return false;
    }

    /**
     * Clean backups older than specified retention days (default 30 days)
     */
    public function autoCleanOldBackups(int $daysToKeep = 30): int {
        $cleanedCount = 0;
        $cutoffTimestamp = time() - ($daysToKeep * 86400);

        $backups = $this->getBackupList();
        foreach ($backups as $b) {
            // Keep safety snapshots or check date
            if ($b['timestamp'] < $cutoffTimestamp && strpos($b['filename'], 'cbmdl_prerestore_safety') === false) {
                if ($this->deleteBackup($b['filename'])) {
                    $cleanedCount++;
                }
            }
        }

        return $cleanedCount;
    }

    /**
     * Format bytes into human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

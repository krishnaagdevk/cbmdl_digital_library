<?php
/**
 * CLI / Cron Backup Script for Cantonment Digital Library (MCB)
 * Usage: php cron/backup.php [db|full]
 */

if (php_sapi_name() !== 'cli' && empty($_GET['cron_key'])) {
    die("Access Denied: This script must be run from the Command Line (CLI) or with a valid cron key.");
}

require_once __DIR__ . '/../config.php';

use App\Services\BackupService;

try {
    $backupService = new BackupService($db);

    $type = 'db';

    echo "[" . date('Y-m-d H:i:s') . "] Starting MCB Digital Library Database SQL Backup...\n";
    $result = $backupService->createDatabaseBackup();

    echo "[" . date('Y-m-d H:i:s') . "] SUCCESS: Created " . $result['filename'] . " (" . $result['size_formatted'] . ")\n";
    echo "SHA-256 Checksum: " . $result['checksum'] . "\n";

    // Auto-clean backups older than 30 days
    $cleaned = $backupService->autoCleanOldBackups(30);
    if ($cleaned > 0) {
        echo "[" . date('Y-m-d H:i:s') . "] Auto-cleaned {$cleaned} outdated backup(s) older than 30 days.\n";
    }

} catch (\Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

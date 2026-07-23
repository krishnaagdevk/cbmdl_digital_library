<?php
/**
 * System Backups & Restore Control Center
 * Cantonment Digital Library (MCB)
 */

use App\Services\BackupService;

$backupService = new BackupService($db);

// Handle manual trigger or fetch list
$backups = $backupService->getBackupList();
$totalStorageBytes = array_sum(array_column($backups, 'size_bytes'));
$formattedStorage = $totalStorageBytes > 0 ? round($totalStorageBytes / (1024 * 1024), 2) . ' MB' : '0 MB';
$latestBackup = !empty($backups) ? $backups[0]['created_at'] : 'None';
?>

<div style="background:#fff; border-radius:14px; border:1px solid var(--border-color); padding:24px; box-shadow:0 6px 20px rgba(15,23,42,0.03);">
    
    <!-- Top Title & Security Header -->
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:15px; margin-bottom:24px; padding-bottom:18px; border-bottom:2px solid #f1f5f9;">
        <div>
            <h2 style="margin:0; font-size:22px; font-weight:800; color:var(--navy-dark); display:flex; align-items:center; gap:10px;">
                <i class="fa-solid fa-box-archive" style="color:var(--primary);"></i> Market-Standard System Backups & Restore
            </h2>
            <p style="margin:4px 0 0 0; font-size:13px; color:var(--text-muted);">
                Generate, download, schedule, and safely restore database dumps & complete system archives.
            </p>
        </div>
        
        
    </div>

    <!-- Quick Metrics Row -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:28px;">
        
        <div style="background:linear-gradient(135deg, #eff6ff, #dbeafe); border:1px solid #bfdbfe; border-radius:12px; padding:16px; display:flex; align-items:center; gap:14px;">
            <div style="width:46px; height:46px; border-radius:12px; background:#2563eb; color:white; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0;">
                <i class="fa-solid fa-database"></i>
            </div>
            <div>
                <span style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#1e40af; display:block;">Total Backups</span>
                <strong style="font-size:20px; color:#1e3a8a;"><?= count($backups) ?> Files</strong>
            </div>
        </div>

        <div style="background:linear-gradient(135deg, #f0fdf4, #dcfce7); border:1px solid #bbf7d0; border-radius:12px; padding:16px; display:flex; align-items:center; gap:14px;">
            <div style="width:46px; height:46px; border-radius:12px; background:#16a34a; color:white; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0;">
                <i class="fa-solid fa-hard-drive"></i>
            </div>
            <div>
                <span style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#166534; display:block;">Storage Used</span>
                <strong style="font-size:20px; color:#14532d;"><?= $formattedStorage ?></strong>
            </div>
        </div>

        <div style="background:linear-gradient(135deg, #fef3c7, #fef08a); border:1px solid #fde047; border-radius:12px; padding:16px; display:flex; align-items:center; gap:14px;">
            <div style="width:46px; height:46px; border-radius:12px; background:#d97706; color:white; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0;">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <span style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#92400e; display:block;">Latest Backup</span>
                <strong style="font-size:13px; color:#78350f; font-family:monospace;"><?= $latestBackup ?></strong>
            </div>
        </div>

        <div style="background:linear-gradient(135deg, #faf5ff, #f3e8ff); border:1px solid #e9d5ff; border-radius:12px; padding:16px; display:flex; align-items:center; gap:14px;">
            <div style="width:46px; height:46px; border-radius:12px; background:#9333ea; color:white; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0;">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <span style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#6b21a8; display:block;">Auto-Retention</span>
                <strong style="font-size:13.5px; color:#581c87;">30 Days Auto-Purge</strong>
            </div>
        </div>

    </div>

    <!-- Action Toolbar Bar -->
    <div style="background:#f8fafc; border:1px solid var(--border-color); border-radius:12px; padding:18px; margin-bottom:28px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px;">
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            
            <!-- Form 1: Generate Database Backup -->
            <form method="post" action="?action=generate_db_backup" style="margin:0;" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=\"fa-solid fa-spinner fa-spin\"></i> Generating...';">
                <?= csrf_input() ?>
                <button class="btn" style="background:var(--primary); color:white; padding:10px 18px; font-weight:600; font-size:13.5px; border-radius:8px; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-database"></i> Create DB Backup (.sql)
                </button>
            </form>

            <!-- Form 2: Generate Full System Backup -->
            <form method="post" action="?action=generate_full_backup" style="margin:0;" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=\"fa-solid fa-spinner fa-spin\"></i> Compressing...';">
                <?= csrf_input() ?>
                <button class="btn" style="background:#059669; color:white; padding:10px 18px; font-weight:600; font-size:13.5px; border-radius:8px; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-file-zipper"></i> Create Full System Backup (.zip)
                </button>
            </form>

        </div>

        <div>
            <button class="btn" onclick="document.getElementById('uploadRestoreModal').style.display='flex';" style="background:var(--navy-dark); color:white; padding:10px 18px; font-weight:600; font-size:13.5px; border-radius:8px; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-upload"></i> Restore DB from External SQL File
            </button>
        </div>
    </div>

    <!-- Backups History Table -->
    <h3 style="margin:0 0 14px 0; font-size:16px; color:var(--navy-dark); font-weight:700; display:flex; align-items:center; gap:8px;">
        <i class="fa-solid fa-list-check" style="color:var(--primary);"></i> Generated Backups Archive
    </h3>

    <?php if (empty($backups)): ?>
        <div style="text-align:center; padding:40px 20px; background:#f8fafc; border-radius:12px; border:2px dashed #cbd5e1; color:var(--text-muted);">
            <i class="fa-solid fa-box-open" style="font-size:38px; color:#cbd5e1; margin-bottom:10px; display:block;"></i>
            <strong style="font-size:15px; color:var(--navy-dark); display:block; margin-bottom:4px;">No Backup Files Found</strong>
            <p style="margin:0; font-size:13px;">Click the buttons above to generate a fresh database dump or complete system backup archive.</p>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                <thead>
                    <tr style="background:#f1f5f9; color:var(--navy-dark); text-transform:uppercase; font-size:11px; letter-spacing:0.5px;">
                        <th style="padding:12px 14px; border-bottom:2px solid var(--border-color);">Backup Filename</th>
                        <th style="padding:12px 14px; border-bottom:2px solid var(--border-color);">Type</th>
                        <th style="padding:12px 14px; border-bottom:2px solid var(--border-color);">File Size</th>
                        <th style="padding:12px 14px; border-bottom:2px solid var(--border-color);">Date Created</th>
                        <th style="padding:12px 14px; border-bottom:2px solid var(--border-color);">SHA-256 Checksum</th>
                        <th style="padding:12px 14px; border-bottom:2px solid var(--border-color); text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $b): ?>
                        <tr style="border-bottom:1px solid #f1f5f9; transition:background 0.15s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='transparent';">
                            
                            <!-- Filename & Icon -->
                            <td style="padding:12px 14px; font-weight:600; color:var(--navy-dark);">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <?php if ($b['extension'] === 'sql'): ?>
                                        <i class="fa-solid fa-file-code" style="font-size:18px; color:var(--primary);"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-file-zipper" style="font-size:18px; color:#059669;"></i>
                                    <?php endif; ?>
                                    <span style="font-family:monospace; font-size:12.5px;"><?= htmlspecialchars($b['filename']) ?></span>
                                </div>
                            </td>

                            <!-- Type Badge -->
                            <td style="padding:12px 14px;">
                                <?php if ($b['extension'] === 'sql'): ?>
                                    <span style="background:#eff6ff; color:#1d4ed8; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700; border:1px solid #bfdbfe;">
                                        Database SQL
                                    </span>
                                <?php else: ?>
                                    <span style="background:#ecfdf5; color:#047857; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700; border:1px solid #a7f3d0;">
                                        Full System ZIP
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Size -->
                            <td style="padding:12px 14px; font-weight:600; color:#334155;">
                                <?= $b['size_formatted'] ?>
                            </td>

                            <!-- Date -->
                            <td style="padding:12px 14px; color:#64748b; font-family:monospace; font-size:12px;">
                                <?= $b['created_at'] ?>
                            </td>

                            <!-- SHA-256 Checksum -->
                            <td style="padding:12px 14px;">
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <code style="background:#f1f5f9; padding:3px 6px; border-radius:4px; font-size:10.5px; color:#475569; max-width:110px; overflow:hidden; text-overflow:ellipsis; display:inline-block;" title="<?= $b['checksum'] ?>">
                                        <?= substr($b['checksum'], 0, 12) ?>...
                                    </code>
                                    <button type="button" onclick="navigator.clipboard.writeText('<?= $b['checksum'] ?>'); alert('SHA-256 Hash copied to clipboard!');" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:12px; padding:2px 4px;" title="Copy full SHA-256 Checksum">
                                        <i class="fa-solid fa-copy"></i>
                                    </button>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td style="padding:12px 14px; text-align:right;">
                                <div style="display:flex; align-items:center; justify-content:flex-end; gap:6px;">
                                    
                                    <!-- Download Button -->
                                    <a href="?action=download_backup&file=<?= urlencode($b['filename']) ?>" class="btn" style="background:#eff6ff; color:var(--primary); border:1px solid #bfdbfe; padding:6px 10px; font-size:12px; border-radius:6px; font-weight:600;" title="Secure Download">
                                        <i class="fa-solid fa-download"></i> Download
                                    </a>

                                    <!-- Restore Button (SQL only) -->
                                    <?php if ($b['extension'] === 'sql'): ?>
                                        <form method="post" action="?action=restore_backup" style="margin:0; display:inline;" onsubmit="return confirm('⚠️ WARNING: Restoring this database backup will overwrite existing database records!\n\nAn automatic pre-restore safety snapshot will be created before applying this restore.\n\nAre you sure you want to proceed?');">
                                            <?= csrf_input() ?>
                                            <input type="hidden" name="file" value="<?= htmlspecialchars($b['filename']) ?>">
                                            <button type="submit" class="btn" style="background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; padding:6px 10px; font-size:12px; border-radius:6px; font-weight:600;" title="Restore Database">
                                                <i class="fa-solid fa-rotate-left"></i> Restore
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <!-- Delete Button -->
                                    <form method="post" action="?action=delete_backup" style="margin:0; display:inline;" onsubmit="return confirm('Delete backup file \'<?= htmlspecialchars($b['filename']) ?>\'? This action cannot be undone.');">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="file" value="<?= htmlspecialchars($b['filename']) ?>">
                                        <button type="submit" class="btn" style="background:#fef2f2; color:#dc2626; border:1px solid #fecaca; padding:6px 10px; font-size:12px; border-radius:6px; font-weight:600;" title="Delete File">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- CLI / Automated Cron Instructions Info Box -->
    <div style="margin-top:30px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:18px;">
        <h4 style="margin:0 0 8px 0; font-size:14px; color:var(--navy-dark); font-weight:700; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-terminal" style="color:var(--primary);"></i> Automated CLI & Windows Task Scheduler Cron
        </h4>
        <p style="margin:0 0 10px 0; font-size:12.5px; color:var(--text-muted);">
            To schedule automatic daily backups at midnight without manually clicking in the dashboard, configure a scheduled task running the following command:
        </p>
        <div style="background:#0f172a; color:#f8fafc; padding:10px 14px; border-radius:8px; font-family:monospace; font-size:12.5px; overflow-x:auto;">
            C:\xampp\php\php.exe C:\xampp\htdocs\cbmdl\cron\backup.php full
        </div>
    </div>

</div>

<!-- Upload & Restore External File Modal -->
<div id="uploadRestoreModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(3px); z-index:99999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:white; border-radius:16px; max-width:520px; width:100%; padding:24px; box-shadow:0 20px 40px rgba(0,0,0,0.2); position:relative; border:1px solid var(--border-color);">
        <button type="button" onclick="document.getElementById('uploadRestoreModal').style.display='none';" style="position:absolute; top:16px; right:16px; background:none; border:none; font-size:22px; cursor:pointer; color:var(--text-muted);">&times;</button>

        <h3 style="margin:0 0 8px 0; font-size:18px; color:var(--navy-dark); font-weight:800; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-upload" style="color:var(--primary);"></i> Restore Database from SQL File
        </h3>
        <p style="margin:0 0 18px 0; font-size:13px; color:var(--text-muted);">
            Upload a valid <code>.sql</code> backup dump file to restore the system database.
        </p>

        <form method="post" action="?action=restore_backup" enctype="multipart/form-data" onsubmit="return confirm('⚠️ Are you sure you want to restore the database from this uploaded file?\n\nAn automatic safety snapshot will be created before restoration.');">
            <?= csrf_input() ?>
            
            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:12.5px; font-weight:700; color:var(--navy-dark); margin-bottom:6px;">Select .sql Backup File</label>
                <input type="file" name="backup_file" accept=".sql" required style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; background:#f8fafc;">
            </div>

            <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:10px; padding:12px; margin-bottom:20px; font-size:12px; color:#9a3412;">
                <strong style="display:block; margin-bottom:2px;"><i class="fa-solid fa-circle-exclamation"></i> Safety First Assurance:</strong>
                System will automatically take a pre-restore safety snapshot before applying the file. You can rollback anytime.
            </div>

            <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="document.getElementById('uploadRestoreModal').style.display='none';" class="btn" style="background:#e2e8f0; color:#334155; padding:10px 16px; border-radius:8px; font-weight:600;">Cancel</button>
                <button type="submit" class="btn" style="background:var(--accent-red); color:white; padding:10px 20px; border-radius:8px; font-weight:600;"><i class="fa-solid fa-rotate-left"></i> Start Restoration</button>
            </div>
        </form>
    </div>
</div>

<?php
/**
 * System Backups Control Center
 * Cantonment Digital Library (MCB)
 */

use App\Services\BackupService;

$backupService = new BackupService($db);

// Handle manual trigger or fetch list
$backups = $backupService->getBackupList();
$totalStorageBytes = array_sum(array_column($backups, 'size_bytes'));
$formattedStorage = $totalStorageBytes > 0 ? round($totalStorageBytes / (1024 * 1024), 2) . ' MB' : '0 MB';
$latestBackupRaw = !empty($backups) ? $backups[0]['created_at'] : null;
$latestBackup = $latestBackupRaw ? date('d-m-Y H:i:s', strtotime($latestBackupRaw)) : 'None';
?>

<div style="background:#fff; border-radius:14px; border:1px solid var(--border-color); padding:24px; box-shadow:0 6px 20px rgba(15,23,42,0.03);">
    
    <!-- Top Title & Security Header -->
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:15px; margin-bottom:24px; padding-bottom:18px; border-bottom:2px solid #f1f5f9;">
        <div>
            <h2 style="margin:0; font-size:22px; font-weight:800; color:var(--navy-dark); display:flex; align-items:center; gap:10px;">
                <i class="fa-solid fa-database" style="color:var(--primary);"></i> Market-Standard Database SQL Backups
            </h2>
            <p style="margin:4px 0 0 0; font-size:13px; color:var(--text-muted);">
                Generate, download, and schedule database SQL dump backups.
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
                <span style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#1e40af; display:block;">Total SQL Backups</span>
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
            <p style="margin:0; font-size:13px;">Click the button above to generate a fresh database SQL backup dump.</p>
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
                        <th style="padding:12px 14px; border-bottom:2px solid var(--border-color); text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $b): ?>
                        <tr style="border-bottom:1px solid #f1f5f9; transition:background 0.15s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='transparent';">
                            
                            <!-- Filename & Icon -->
                            <td style="padding:12px 14px; font-weight:600; color:var(--navy-dark);">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <i class="fa-solid fa-file-code" style="font-size:18px; color:var(--primary);"></i>
                                    <span style="font-family:monospace; font-size:12.5px;"><?= htmlspecialchars($b['filename']) ?></span>
                                </div>
                            </td>

                            <!-- Type Badge -->
                            <td style="padding:12px 14px;">
                                <span style="background:#eff6ff; color:#1d4ed8; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700; border:1px solid #bfdbfe;">
                                    Database SQL
                                </span>
                            </td>

                            <!-- Size -->
                            <td style="padding:12px 14px; font-weight:600; color:#334155;">
                                <?= $b['size_formatted'] ?>
                            </td>

                            <!-- Date -->
                            <td style="padding:12px 14px; color:#64748b; font-family:monospace; font-size:12px;">
                                <?= date('d-m-Y H:i:s', strtotime($b['created_at'])) ?>
                            </td>

                            <!-- Actions -->
                            <td style="padding:12px 14px; text-align:right;">
                                <div style="display:flex; align-items:center; justify-content:flex-end; gap:6px;">
                                    
                                    <!-- Download Button -->
                                    <a href="?action=download_backup&file=<?= urlencode($b['filename']) ?>" class="btn" style="background:#eff6ff; color:var(--primary); border:1px solid #bfdbfe; padding:6px 10px; font-size:12px; border-radius:6px; font-weight:600;" title="Secure Download">
                                        <i class="fa-solid fa-download"></i> Download
                                    </a>

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


</div>

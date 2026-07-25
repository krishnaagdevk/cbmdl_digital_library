<?php
// views/admin/login_window.php
if (!defined('BASE_URL')) exit;

$now_time = date('H:i:s');
$now_fmt = date('h:i A');
?>

<div class="card" style="border-top: 4px solid var(--primary); margin-bottom:20px;">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:12px;">
        <div>
            <h3 style="margin:0; color:var(--navy-dark); display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-sliders" style="color:var(--primary);"></i> Shift Timing Master Control
            </h3>
            <p style="font-size:13px; color:var(--text-muted); margin:4px 0 0 0;">
                Live shift window access restrictions & member login window enforcement status.
            </p>
        </div>
        <div style="background:var(--bg-slate); border:1px solid var(--border-color); padding:8px 14px; border-radius:8px; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-clock" style="color:var(--primary); font-size:16px;"></i>
            <div>
                <span style="font-size:11px; color:var(--text-muted); display:block; line-height:1;">Server Time (Kolkata)</span>
                <strong style="font-size:13px; color:var(--navy-dark);"><?= $now_fmt ?> (<?= date('d-m-Y') ?>)</strong>
            </div>
        </div>
    </div>

    <!-- Active Shift Status Overview Cards -->
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:15px; margin-bottom:25px;">
        <?php 
        $shiftsRes = $db->query("SELECT * FROM work_shifts ORDER BY id ASC");
        if ($shiftsRes && $shiftsRes->num_rows > 0):
            while ($sf = $shiftsRes->fetch_assoc()):
                $sName = $sf['name'];
                $sStart = date('h:i A', strtotime($sf['start_time']));
                $sEnd = date('h:i A', strtotime($sf['end_time']));
                
                // Count valid and expired members assigned to this shift
                $mCountStmt = $db->prepare("SELECT 
                    SUM(CASE WHEN start_date <= CURDATE() AND end_date >= CURDATE() THEN 1 ELSE 0 END) as valid_cnt,
                    SUM(CASE WHEN end_date < CURDATE() OR start_date > CURDATE() THEN 1 ELSE 0 END) as expired_cnt
                    FROM members WHERE shift = ? AND approved = 1 AND is_active = 1");
                $validCount = 0;
                $expiredCount = 0;
                if ($mCountStmt) {
                    $mCountStmt->bind_param("s", $sName);
                    $mCountStmt->execute();
                    $rowCounts = $mCountStmt->get_result()->fetch_assoc();
                    $validCount = (int)($rowCounts['valid_cnt'] ?? 0);
                    $expiredCount = (int)($rowCounts['expired_cnt'] ?? 0);
                    $mCountStmt->close();
                }

                // Check if shift is active right now
                $is_active_now = is_member_within_shift_time($sName, $db);

                $shift_emojis = [
                    'Morning'  => '🌅',
                    'Evening'  => '🌆',
                    'Full Day' => '☀️',
                    'Night'    => '🌙',
                    'Both'     => '⏱️'
                ];
                $sEmoji = $shift_emojis[$sName] ?? '🕒';
        ?>
            <div style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:12px; padding:16px; box-shadow:0 4px 12px rgba(0,0,0,0.02); position:relative; overflow:hidden;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                    <span style="font-weight:700; font-size:15px; color:var(--navy-dark); display:flex; align-items:center; gap:6px;">
                        <span style="font-size:18px;"><?= $sEmoji ?></span> <?= e($sName) ?> Shift
                    </span>
                    <?php if ($is_active_now): ?>
                        <span class="badge badge-green" style="font-size:10px; padding:3px 8px;"><i class="fa-solid fa-circle-play"></i> Active Window Now</span>
                    <?php else: ?>
                        <span class="badge" style="background:#e2e8f0; color:#475569; font-size:10px; padding:3px 8px;"><i class="fa-solid fa-circle-pause"></i> Restricted Now</span>
                    <?php endif; ?>
                </div>

                <div style="margin-bottom:12px; font-size:12.5px; color:var(--text-color);">
                    <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                        <span style="color:var(--text-muted);">Window Start:</span>
                        <strong><?= $sStart ?></strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                        <span style="color:var(--text-muted);">Window End:</span>
                        <strong><?= $sEnd ?></strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="color:var(--text-muted);">Assigned Members:</span>
                        <div style="text-align:right;">
                            <a href="?action=admin&tab=view_members&shift_filter=<?= urlencode($sName) ?>&status_filter=active" style="text-decoration:none;">
                                <strong style="color:var(--accent-green);"><?= $validCount ?> Valid Members</strong>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php 
            endwhile;
        endif; 
        ?>
    </div>

    <!-- Quick Navigation to Edit Shift Timings -->
    <div style="background:rgba(37, 99, 235, 0.05); border:1px solid rgba(37, 99, 235, 0.2); border-radius:10px; padding:15px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:40px; height:40px; border-radius:50%; background:var(--primary); color:white; display:flex; align-items:center; justify-content:center; font-size:18px;">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <strong style="font-size:14px; color:var(--navy-dark); display:block;">Need to adjust shift start or end times?</strong>
                <span style="font-size:12px; color:var(--text-muted);">You can configure custom shift windows or update existing schedules in the Shift Timings module.</span>
            </div>
        </div>
        <a href="?action=admin&tab=shift_timings" class="btn" style="padding:8px 16px; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:6px; font-weight:600;">
            <i class="fa-solid fa-clock"></i> Add / Edit New Shift Timings
        </a>
    </div>
</div>

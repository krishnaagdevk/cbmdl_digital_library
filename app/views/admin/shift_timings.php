<?php
// views/admin/shift_timings.php
if (!defined('BASE_URL')) exit;
?>

<div class="card" style="border-top: 4px solid var(--primary);">
    <div style="margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:15px;">
        <h3 style="margin:0; color:var(--navy-dark); display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-clock" style="color:var(--primary);"></i> Library Shift Timings Configuration
        </h3>
        <p style="font-size:13px; color:var(--text-muted); margin:4px 0 0 0;">
            Define login time windows for assigned member shifts. Members assigned to a shift will be restricted from logging in before or after their specified shift window.
        </p>
    </div>

    <form method="post" action="?action=save_shift_times">
        <?= csrf_input() ?>
        <div class="table-responsive" style="margin-bottom:20px;">
            <table>
                <thead>
                    <tr>
                        <th>Shift Name</th>
                        <th>Login Allowed From (Start Time)</th>
                        <th>Login Allowed Until (End Time)</th>
                        <th>Access Window Preview</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $shiftsList = $db->query("SELECT * FROM work_shifts ORDER BY id ASC");
                    if ($shiftsList && $shiftsList->num_rows > 0):
                        while ($sf = $shiftsList->fetch_assoc()):
                            $sStart = date('h:i A', strtotime($sf['start_time']));
                            $sEnd = date('h:i A', strtotime($sf['end_time']));
                    ?>
                        <tr>
                            <td style="font-weight:700; color:var(--navy-dark);">
                                <input type="hidden" name="shift_name[]" value="<?= e($sf['name']) ?>">
                                <i class="fa-solid fa-sun" style="color:var(--accent-orange); margin-right:5px;"></i> <?= e($sf['name']) ?> Shift
                            </td>
                            <td>
                                <input type="time" name="start_time[]" value="<?= e($sf['start_time']) ?>" required style="margin:0; padding:6px 10px; font-size:13px;">
                            </td>
                            <td>
                                <input type="time" name="end_time[]" value="<?= e($sf['end_time']) ?>" required style="margin:0; padding:6px 10px; font-size:13px;">
                            </td>
                            <td>
                                <span class="badge badge-blue" style="font-size:11.5px; padding:4px 10px;">
                                    <i class="fa-solid fa-business-time"></i> <?= $sStart ?> &rarr; <?= $sEnd ?>
                                </span>
                            </td>
                        </tr>
                    <?php 
                        endwhile;
                    endif;
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Add Custom Shift Creation Row -->
        <div style="background:var(--bg-slate); padding:16px; border-radius:10px; border:1px solid var(--border-color); margin-bottom:20px;">
            <h4 style="margin:0 0 10px 0; font-size:13.5px; color:var(--navy-dark); display:flex; align-items:center; gap:6px;">
                <i class="fa-solid fa-plus-circle" style="color:var(--primary);"></i> Add Custom Shift (Market Standard)
            </h4>
            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:12px;">
                <div>
                    <label style="font-size:11.5px; font-weight:600; margin-bottom:4px; display:block;">Custom Shift Name</label>
                    <input name="custom_shift_name" placeholder="e.g. Night Shift, Weekend" style="margin:0;">
                </div>
                <div>
                    <label style="font-size:11.5px; font-weight:600; margin-bottom:4px; display:block;">Start Time</label>
                    <input type="time" name="custom_start_time" style="margin:0;">
                </div>
                <div>
                    <label style="font-size:11.5px; font-weight:600; margin-bottom:4px; display:block;">End Time</label>
                    <input type="time" name="custom_end_time" style="margin:0;">
                </div>
            </div>
        </div>

        <button type="submit" class="btn" style="padding:10px 24px; font-weight:600;"><i class="fa-solid fa-floppy-disk"></i> Save Shift Timing Configurations</button>
    </form>
</div>

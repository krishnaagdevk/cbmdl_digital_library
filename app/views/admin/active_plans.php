<?php
// views/admin/active_plans.php
if (!defined('BASE_URL')) exit;
?>

<div class="card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div>
            <h3 style="margin:0; display:flex; align-items:center; gap:8px; color:var(--navy-dark);">
                <i class="fa-solid fa-list-check" style="color:var(--primary);"></i> Active Membership Plans
            </h3>
            <p style="margin:4px 0 0 0; font-size:13px; color:var(--text-muted);">
                View and manage available membership duration classes and access fees.
            </p>
        </div>
        <a href="?action=admin&tab=create_plan" class="btn" style="padding:8px 16px; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:6px; font-weight:600;">
            <i class="fa-solid fa-circle-plus"></i> Create New Plan
        </a>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width:70px;">ID</th>
                    <th>Plan Class Name</th>
                    <th>Duration Term</th>
                    <th>Amount Fee (INR)</th>
                    <th style="width:180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $plansQuery = $db->query('SELECT * FROM membership_plans ORDER BY amount ASC');
                if ($plansQuery && $plansQuery->num_rows > 0):
                    while($p = $plansQuery->fetch_assoc()):
                ?>
                    <tr>
                        <td><strong>#<?= $p['id'] ?></strong></td>
                        <td style="font-weight:600; color:var(--navy-dark);"><?= e($p['name']) ?></td>
                        <td><span class="badge badge-blue"><i class="fa-solid fa-calendar-days"></i> <?= e($p['duration']) ?></span></td>
                        <td><strong style="color:var(--accent-green); font-size:14px;">₹<?= number_style_format($p['amount']) ?></strong></td>
                        <td>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <a class="btn" href="?action=admin&tab=create_plan&edit=<?= $p['id'] ?>" style="padding:6px 12px; font-size:12px; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>
                                <form method="post" action="?action=delete_plan" style="display:inline; margin:0;" onsubmit="return confirm('Are you sure you want to delete this plan permanently?')">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <button class="btn btn-danger" style="padding:6px 12px; font-size:12px; background:var(--accent-red); border-color:var(--accent-red); display:inline-flex; align-items:center; gap:4px;">
                                        <i class="fa-solid fa-trash-can"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php 
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:30px; color:var(--text-muted);">
                            <i class="fa-solid fa-folder-open" style="font-size:32px; display:block; margin-bottom:10px; opacity:0.5;"></i>
                            No active membership plans found. Click "Create New Plan" to add one.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

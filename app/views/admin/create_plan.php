<?php
// views/admin/create_plan.php
if (!defined('BASE_URL')) exit;

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editPlan = null;
if ($editId) {
    $stmt = $db->prepare("SELECT * FROM membership_plans WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $editId);
        $stmt->execute();
        $editPlan = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}
?>

<div style="max-width: 650px; margin: 0 auto;">
    <div class="card" style="border-top: 4px solid var(--primary);">
        <?php if ($editPlan): ?>
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:15px;">
                <h3 style="margin:0; color:var(--navy-dark); display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-pen-to-square" style="color:var(--primary);"></i> Edit Membership Plan #<?= $editPlan['id'] ?>
                </h3>
                <a href="?action=admin&tab=active_plans" class="btn btn-secondary" style="padding:6px 12px; font-size:12px; text-decoration:none; display:inline-flex; align-items:center; gap:5px; background:var(--bg-slate); border:1px solid var(--border-color); color:var(--text-dark);">
                    <i class="fa-solid fa-arrow-left"></i> Back to Active Plans
                </a>
            </div>

            <form method="post" action="?action=update_plan">
                <?= csrf_input() ?>
                <input type="hidden" name="id" value="<?= $editPlan['id'] ?>">

                <label for="p_name" style="font-weight:600; display:block; margin-bottom:5px;">Membership Plan Name *</label>
                <input id="p_name" name="name" value="<?= e($editPlan['name']) ?>" required placeholder="e.g. Platinum Premium Pass" style="margin-bottom:15px;">

                <label for="p_duration" style="font-weight:600; display:block; margin-bottom:5px;">Duration Term *</label>
                <?php 
                $standard_terms = ['Yearly', 'Half Yearly', 'Quarterly', 'Monthly', 'Daily'];
                $is_custom = $editPlan && !in_array($editPlan['duration'], $standard_terms);
                ?>
                <select id="p_duration" <?= $is_custom ? '' : 'name="duration"' ?> required style="margin-bottom:12px;">
                    <?php 
                    foreach ($standard_terms as $term) {
                        $sel = (!$is_custom && $editPlan['duration'] === $term) ? 'selected' : '';
                        echo '<option ' . $sel . ' value="' . $term . '">' . $term . '</option>';
                    }
                    ?>
                </select>

                <input id="p_custom_duration" type="text" <?= $is_custom ? 'name="duration" required' : '' ?> value="<?= $is_custom ? e($editPlan['duration']) : '' ?>" placeholder="e.g. 3 Years, 2 Weeks, etc." style="display: <?= $is_custom ? 'block' : 'none' ?>; margin-bottom:15px; border-color: var(--primary);">

                <label for="p_amount" style="font-weight:600; display:block; margin-bottom:5px;">Amount Fee (INR) *</label>
                <input id="p_amount" type="number" step="0.01" name="amount" value="<?= e($editPlan['amount']) ?>" required placeholder="0.00" style="margin-bottom:20px;">

                <div style="display:flex; gap:10px; align-items:center;">
                    <button type="submit" class="btn" style="padding:10px 20px; font-weight:600; flex:1;"><i class="fa-solid fa-circle-check"></i> Save Changes</button>
                    <a href="?action=admin&tab=active_plans" class="btn btn-secondary" style="padding:10px 20px; text-decoration:none; background:var(--bg-slate); border:1px solid var(--border-color); color:var(--text-dark); text-align:center;">Cancel</a>
                </div>
            </form>
        <?php else: ?>
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:15px;">
                <h3 style="margin:0; color:var(--navy-dark); display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-circle-plus" style="color:var(--primary);"></i> Create New Membership Plan
                </h3>
                <a href="?action=admin&tab=active_plans" class="btn btn-secondary" style="padding:6px 12px; font-size:12px; text-decoration:none; display:inline-flex; align-items:center; gap:5px; background:var(--bg-slate); border:1px solid var(--border-color); color:var(--text-dark);">
                    <i class="fa-solid fa-list-check"></i> View Active Plans
                </a>
            </div>

            <form method="post" action="?action=add_plan">
                <?= csrf_input() ?>

                <label for="p_name" style="font-weight:600; display:block; margin-bottom:5px;">Membership Plan Name *</label>
                <input id="p_name" name="name" required placeholder="e.g. Premium Annual Pass" style="margin-bottom:15px;">

                <label for="p_duration" style="font-weight:600; display:block; margin-bottom:5px;">Duration Term *</label>
                <select id="p_duration" name="duration" required style="margin-bottom:12px;">
                    <option value="Yearly">Yearly</option>
                    <option value="Half Yearly">Half Yearly</option>
                    <option value="Quarterly">Quarterly</option>
                    <option value="Monthly">Monthly</option>
                    <option value="Daily">Daily</option>
                </select>

                <input id="p_custom_duration" type="text" placeholder="e.g. 3 Years, 2 Weeks, etc." style="display: none; margin-bottom:15px; border-color: var(--primary);">

                <label for="p_amount" style="font-weight:600; display:block; margin-bottom:5px;">Amount Fee (INR) *</label>
                <input id="p_amount" type="number" step="0.01" name="amount" required placeholder="e.g. 500.00" style="margin-bottom:20px;">

                <button type="submit" class="btn" style="width:100%; padding:10px 20px; font-weight:600; font-size:14px;"><i class="fa-solid fa-circle-plus"></i> Add Membership Plan Class</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script class="dynamic-script">
(function() {
    function initCreatePlanScript() {
        const durationSelect = document.getElementById('p_duration');
        const customDurationInput = document.getElementById('p_custom_duration');
        
        if (durationSelect && customDurationInput) {
            function handleDurationChange() {
                if (durationSelect.value === 'Custom') {
                    durationSelect.removeAttribute('name');
                    customDurationInput.setAttribute('name', 'duration');
                    customDurationInput.setAttribute('required', 'required');
                    customDurationInput.style.display = 'block';
                    customDurationInput.focus();
                } else {
                    durationSelect.setAttribute('name', 'duration');
                    customDurationInput.removeAttribute('name');
                    customDurationInput.removeAttribute('required');
                    customDurationInput.style.display = 'none';
                }
            }

            durationSelect.addEventListener('change', handleDurationChange);
            handleDurationChange();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCreatePlanScript);
    } else {
        initCreatePlanScript();
    }
})();
</script>

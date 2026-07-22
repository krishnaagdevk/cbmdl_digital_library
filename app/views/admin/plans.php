<?php
// views/admin/plans.php
if (!defined('BASE_URL')) exit;

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editPlan = null;
if ($editId) {
    $stmt = $db->prepare("SELECT * FROM membership_plans WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editPlan = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<div class="grid" style="grid-template-columns: 1.5fr 1fr; gap: 30px;">
    <!-- Plans Table -->
    <div class="card">
        <h3><i class="fa-solid fa-address-book"></i> Active Membership Plans</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Plan Class Name</th>
                        <th>Duration Term</th>
                        <th>Amount Fee (INR)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $plansQuery = $db->query('SELECT * FROM membership_plans ORDER BY amount ASC');
                    while($p = $plansQuery->fetch_assoc()) {
                        echo '
                        <tr>
                            <td><strong>' . $p['id'] . '</strong></td>
                            <td>' . e($p['name']) . '</td>
                            <td><span class="badge badge-blue">' . e($p['duration']) . '</span></td>
                            <td><strong>₹' . number_style_format($p['amount']) . '</strong></td>
                            <td>
                                <div style="display:flex; gap:8px;">
                                    <a class="btn" href="?action=admin&tab=plans&edit=' . $p['id'] . '" style="padding:6px 12px; font-size:12px;"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                    <form method="post" action="?action=delete_plan" style="display:inline; margin:0;" onsubmit="return confirm(\'Are you sure you want to delete this plan permanently?\')">
                                        ' . csrf_input() . '
                                        <input type="hidden" name="id" value="' . $p['id'] . '">
                                        <button class="btn btn-danger" style="padding:6px 12px; font-size:12px; background:var(--accent-red); border-color:var(--accent-red);"><i class="fa-solid fa-trash-can"></i> Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit/Add Card -->
    <div class="card">
        <?php if ($editPlan): ?>
            <h3><i class="fa-solid fa-pen-to-square"></i> Edit Membership Plan</h3>
            <form method="post" action="?action=update_plan">
                <?= csrf_input() ?>
                <input type="hidden" name="id" value="<?= $editPlan['id'] ?>">

                <label for="p_name">Plan Class Name *</label>
                <input id="p_name" name="name" value="<?= e($editPlan['name']) ?>" required placeholder="e.g. Platinum Premium">

                <label for="p_duration">Duration Term *</label>
                <?php 
                $standard_terms = ['Yearly', 'Half Yearly', 'Quarterly', 'Monthly', 'Daily'];
                $is_custom = $editPlan && !in_array($editPlan['duration'], $standard_terms);
                ?>
                <select id="p_duration" <?= $is_custom ? '' : 'name="duration"' ?> required style="margin-bottom:10px;">
                    <?php 
                    foreach ($standard_terms as $term) {
                        $sel = (!$is_custom && $editPlan['duration'] === $term) ? 'selected' : '';
                        echo '<option ' . $sel . ' value="' . $term . '">' . $term . '</option>';
                    }
                    ?>
                    <option value="Custom" <?= $is_custom ? 'selected' : '' ?>>Custom (Enter below)</option>
                </select>

                <input id="p_custom_duration" type="text" <?= $is_custom ? 'name="duration" required' : '' ?> value="<?= $is_custom ? e($editPlan['duration']) : '' ?>" placeholder="e.g. 3 Years, 2 Weeks, etc." style="display: <?= $is_custom ? 'block' : 'none' ?>; margin-bottom:15px; border-color: var(--primary);">

                <label for="p_amount">Amount Fee (INR) *</label>
                <input id="p_amount" type="number" step="0.01" name="amount" value="<?= e($editPlan['amount']) ?>" required placeholder="0.00">

                <button style="width:100%; margin-top:10px;"><i class="fa-solid fa-circle-check"></i> Save Changes</button>
                <p style="text-align:center; margin-top:15px;"><a href="?action=admin&tab=plans" style="color:var(--primary); text-decoration:none;"><i class="fa-solid fa-arrow-left"></i> Cancel Edit</a></p>
            </form>
        <?php else: ?>
            <h3><i class="fa-solid fa-circle-plus"></i> Create New Plan</h3>
            <form method="post" action="?action=add_plan">
                <?= csrf_input() ?>

                <label for="p_name">Plan Class Name *</label>
                <input id="p_name" name="name" required placeholder="e.g. Premium Access Pass">

                <label for="p_duration">Duration Term *</label>
                <select id="p_duration" name="duration" required style="margin-bottom:10px;">
                    <option value="Yearly">Yearly</option>
                    <option value="Half Yearly">Half Yearly</option>
                    <option value="Quarterly">Quarterly</option>
                    <option value="Monthly">Monthly</option>
                    <option value="Daily">Daily</option>
                    <option value="Custom">Custom (Enter below)</option>
                </select>

                <input id="p_custom_duration" type="text" placeholder="e.g. 3 Years, 2 Weeks, etc." style="display: none; margin-bottom:15px; border-color: var(--primary);">

                <label for="p_amount">Amount Fee (INR) *</label>
                <input id="p_amount" type="number" step="0.01" name="amount" required placeholder="e.g. 500.00">

                <button style="width:100%; margin-top:10px;"><i class="fa-solid fa-circle-plus"></i> Add Plan Class</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
    }
});
</script>

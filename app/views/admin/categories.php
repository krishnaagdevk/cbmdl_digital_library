<?php
// views/admin/categories.php
if (!defined('BASE_URL')) exit;
?>
<div class="grid">
    <div class="card">
        <h3><i class="fa-solid fa-circle-plus"></i> Add New e-books Category</h3>
        <form method="post" action="?action=add_category">
            <?= csrf_input() ?>
            <label for="cat_input">Category Name</label>
            <input id="cat_input" name="name" placeholder="e.g. UPSC, Engineering, Novel" required>
            <button><i class="fa-solid fa-floppy-disk"></i>Save Category</button>
        </form>
    </div>

    <div class="card">
        <h3><i class="fa-solid fa-list-ul"></i> Saved Categories</h3>
        <input type="text" id="catFilterInput" placeholder="Instant Category Search..." style="margin-bottom:12px;">
        <div class="table-responsive">
            <table id="categoriesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category Name</th>
                        <th>Operations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $x = $db->query("SELECT * FROM categories ORDER BY name");
                    $sr = 1; 
                    while($r = $x->fetch_assoc()) {
                        echo "
                        <tr>
                            <td>{$sr}</td>
                            <td class='cat-name-cell'>" . e($r['name']) . "</td>
                            <td>
                                <form method='post' action='?action=delete_category' style='display:inline; margin:0;'>
                                    " . csrf_input() . "
                                    <input type='hidden' name='id' value='{$r['id']}'>
                                    <button class='danger btn btn-danger' type='submit' style='padding:6px 12px;'><i class='fa-solid fa-trash-can'></i> Delete</button>
                                </form>
                            </td>
                        </tr>";
                        $sr++; 
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
if (!current_user_can('edit_posts')) exit;

global $wpdb;
$table = $wpdb->prefix . 'srm_classes';

/* ---------- HANDLE DELETE ---------- */
if (isset($_GET['delete']) && intval($_GET['delete'])) {
    $wpdb->delete($table, ['id' => intval($_GET['delete'])]);
    echo "<div class='notice notice-success is-dismissible'><p>Class deleted successfully.</p></div>";
}

/* ---------- HANDLE SAVE ---------- */
if (isset($_POST['srm_save_class'])) {
    check_admin_referer('srm_save_class');

    $name = sanitize_text_field($_POST['name']);
    $class_no = intval($_POST['class_no']);
    $status = isset($_POST['status']) ? 1 : 0;

    $data = [
        'name' => $name,
        'class_no' => $class_no,
        'status' => $status
    ];

    if (!empty($_POST['class_id'])) {
        $wpdb->update($table, $data, ['id' => intval($_POST['class_id'])]);
        echo "<div class='notice notice-success is-dismissible'><p>Class updated successfully.</p></div>";
    } else {
        $wpdb->insert($table, $data);
        echo "<div class='notice notice-success is-dismissible'><p>Class added successfully.</p></div>";
    }
}

/* ---------- LOAD EDIT DATA ---------- */
$edit_name = '';
$edit_class_no = '';
$edit_status = 1;
$edit_id = 0;

if (isset($_GET['edit']) && intval($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM $table WHERE id=%d", $edit_id));
    $edit_class_no = $wpdb->get_var($wpdb->prepare("SELECT class_no FROM $table WHERE id=%d", $edit_id));
    $edit_status = $wpdb->get_var($wpdb->prepare("SELECT status FROM $table WHERE id=%d", $edit_id));
}

/* ---------- FETCH ALL CLASSES ---------- */
$classes = $wpdb->get_results("SELECT * FROM $table ORDER BY class_no ASC");
?>

<div class="wrap">
<h1>Classes</h1>

<!-- ADD / EDIT FORM -->
<form method="post" style="margin-bottom:20px;">
    <?php wp_nonce_field('srm_save_class'); ?>
    <?php if ($edit_id): ?>
        <input type="hidden" name="class_id" value="<?= esc_attr($edit_id) ?>">
        <h2>Edit Class</h2>
    <?php else: ?>
        <h2>Add New Class</h2>
    <?php endif; ?>

    <table class="form-table">
    <tr>
        <th>Name</th>
        <td><input type="text" name="name" value="<?= esc_attr($edit_name) ?>" required></td>
    </tr>
    <tr>
        <th>Class No</th>
        <td><input type="number" name="class_no" value="<?= esc_attr($edit_class_no) ?>" required></td>
    </tr>
    <tr>
        <th>Status</th>
        <td><input type="checkbox" name="status" value="1" <?= $edit_status ? 'checked' : '' ?>> Active</td>
    </tr>
    </table>

    <input type="submit" name="srm_save_class" class="button button-primary" value="<?= $edit_id ? 'Update Class' : 'Add Class' ?>">
</form>

<hr>

<!-- CLASSES LIST -->
<h2>All Classes</h2>
<table class="wp-list-table widefat fixed striped">
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Class No</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php if ($classes): foreach ($classes as $c): ?>
<tr>
    <td><?= esc_html($c->id) ?></td>
    <td><?= esc_html($c->name) ?></td>
    <td><?= esc_html($c->class_no) ?></td>
    <td><?= $c->status ? 'Active' : 'Inactive' ?></td>
    <td>
        <a href="?page=srm_classes&edit=<?= $c->id ?>">Edit</a> |
        <a href="?page=srm_classes&delete=<?= $c->id ?>" onclick="return confirm('Delete this class?')">Delete</a>
    </td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="5" style="text-align:center;">No classes found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

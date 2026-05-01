<?php
if (!current_user_can('edit_posts')) exit;

global $wpdb;
$table = $wpdb->prefix . 'srm_sections';
$class_table = $wpdb->prefix . 'srm_classes';

/* ---------- FETCH CLASSES ---------- */
$classes = $wpdb->get_results("SELECT * FROM $class_table WHERE status=1 ORDER BY class_no");

/* ---------- HANDLE SAVE ---------- */
if (isset($_POST['srm_save_section'])) {
    check_admin_referer('srm_save_section');

    $data = [
        'class_no' => intval($_POST['class_no']),
        'name'     => sanitize_text_field($_POST['name']),
        'status'   => isset($_POST['status']) ? 1 : 0,
    ];

    if (!empty($_POST['section_id'])) {
        $wpdb->update($table, $data, ['id' => intval($_POST['section_id'])]);
        echo '<div class="notice notice-success"><p>Section updated successfully.</p></div>';
    } else {
        $wpdb->insert($table, $data);
        echo '<div class="notice notice-success"><p>Section added successfully.</p></div>';
    }
}

/* ---------- DELETE ---------- */
if (isset($_GET['delete']) && intval($_GET['delete'])) {
    $wpdb->delete($table, ['id' => intval($_GET['delete'])]);
    echo '<div class="notice notice-success"><p>Section deleted successfully.</p></div>';
}

/* ---------- FETCH SECTIONS ---------- */
$sections = $wpdb->get_results("SELECT s.*, c.name AS class_name FROM $table s LEFT JOIN $class_table c ON s.class_no=c.class_no ORDER BY c.class_no, s.name");
?>

<div class="wrap">
<h1>Sections</h1>

<!-- ADD / EDIT FORM -->
<form method="post">
<?php wp_nonce_field('srm_save_section'); ?>
<input type="hidden" name="section_id" value="<?= esc_attr($_GET['edit'] ?? '') ?>">

<table class="form-table">
<tr>
<th>Class</th>
<td>
<select name="class_no" required>
<option value="">Select Class</option>
<?php foreach ($classes as $cl): ?>
<option value="<?= $cl->class_no ?>" <?= (isset($_GET['edit']) && $wpdb->get_var($wpdb->prepare("SELECT class_no FROM $table WHERE id=%d", intval($_GET['edit'])))==$cl->id)?'selected':'' ?>>
<?= esc_html($cl->name) ?>
</option>
<?php endforeach; ?>
</select>
</td>
</tr>

<tr>
<th>Section Name</th>
<td><input type="text" name="name" value="<?= isset($_GET['edit'])?$wpdb->get_var($wpdb->prepare("SELECT name FROM $table WHERE id=%d", intval($_GET['edit']))):'' ?>" required></td>
</tr>

<tr>
<th>Status</th>
<td><input type="checkbox" name="status" value="1" <?= (!isset($_GET['edit']) || $wpdb->get_var($wpdb->prepare("SELECT status FROM $table WHERE id=%d", intval($_GET['edit'])))==1)?'checked':'' ?>> Active</td>
</tr>
</table>

<input type="submit" name="srm_save_section" class="button button-primary" value="<?= isset($_GET['edit'])?'Update Section':'Add Section' ?>">
</form>

<hr>

<!-- SECTION LIST -->
<table class="wp-list-table widefat fixed striped">
<thead>
<tr>
<th>ID</th>
<th>Class</th>
<th>Section</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php if ($sections): foreach ($sections as $s): ?>
<tr>
<td><?= $s->id ?></td>
<td><?= esc_html($s->class_name) ?></td>
<td><?= esc_html($s->name) ?></td>
<td><?= $s->status ? 'Active' : 'Inactive' ?></td>
<td>
<a href="?page=srm_sections&edit=<?= $s->id ?>">Edit</a> |
<a href="?page=srm_sections&delete=<?= $s->id ?>" onclick="return confirm('Delete this section?')">Delete</a>
</td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="5" style="text-align:center;">No sections found</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

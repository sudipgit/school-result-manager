<?php
if (!current_user_can('edit_posts')) exit;

global $wpdb;

$table_exams   = $wpdb->prefix . 'srm_exams';
$table_classes = $wpdb->prefix . 'srm_classes';

/* ---------- FETCH CLASSES FROM DB ---------- */
$classes = $wpdb->get_results(
    "SELECT class_no, name FROM $table_classes WHERE status=1 ORDER BY class_no ASC"
);

/* ---------- EDIT LOAD ---------- */
$edit_exam = null;
if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    $edit_exam = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_exams WHERE id=%d", intval($_GET['edit']))
    );
}

/* ---------- SAVE EXAM ---------- */
if (isset($_POST['srm_save_exam'])) {

    check_admin_referer('srm_save_exam');

    $class_values = array_map('intval', $_POST['class'] ?? []);
    $class_csv    = implode(',', $class_values);

    $data = [
        'name'    => sanitize_text_field($_POST['name']),
        'class'   => $class_csv,
        'session' => sanitize_text_field($_POST['session']),
    ];

    if (!empty($_POST['exam_id'])) {
        $wpdb->update($table_exams, $data, ['id' => intval($_POST['exam_id'])]);
        echo '<div class="notice notice-success"><p>Exam updated successfully.</p></div>';
    } else {
        $wpdb->insert($table_exams, $data);
        echo '<div class="notice notice-success"><p>Exam added successfully.</p></div>';
    }
}

/* ---------- DELETE ---------- */
if (isset($_GET['delete']) && intval($_GET['delete'])) {
    $wpdb->delete($table_exams, ['id' => intval($_GET['delete'])]);
    echo '<div class="notice notice-success"><p>Exam deleted.</p></div>';
}

/* ---------- FETCH EXAMS ---------- */
$exams = $wpdb->get_results("SELECT * FROM $table_exams ORDER BY id DESC");

/* ---------- HELPERS ---------- */
function srm_class_names($csv, $classes) {
    $map = [];
    foreach ($classes as $c) {
        $map[$c->class_no] = $c->name;
    }

    $names = [];
    foreach (explode(',', $csv) as $c) {
        if (isset($map[$c])) {
            $names[] = $map[$c];
        }
    }
    return implode(', ', $names);
}

$selected_classes = $edit_exam ? explode(',', $edit_exam->class) : [];
?>

<div class="wrap">
<h1><?= $edit_exam ? 'Edit Exam' : 'Add Exam' ?></h1>

<!-- ADD / EDIT FORM -->
<form method="post">
<?php wp_nonce_field('srm_save_exam'); ?>
<input type="hidden" name="exam_id" value="<?= esc_attr($edit_exam->id ?? '') ?>">

<table class="form-table">

<tr>
<th>Exam Name</th>
<td>
<input type="text" name="name" value="<?= esc_attr($edit_exam->name ?? '') ?>" required>
</td>
</tr>

<tr>
<th>Class</th>
<td>
<select name="class[]" multiple required style="height:140px;">
<?php foreach ($classes as $c): ?>
<option value="<?= $c->class_no ?>"
    <?= in_array($c->class_no, $selected_classes) ? 'selected' : '' ?>>
    <?= esc_html($c->name) ?>
</option>
<?php endforeach; ?>
</select>
<p class="description">Hold CTRL / CMD to select multiple classes</p>
</td>
</tr>

<tr>
<th>Session</th>
<td>
<input type="text" name="session" value="<?= esc_attr($edit_exam->session ?? '') ?>" placeholder="2026" required>
</td>
</tr>

</table>

<button type="submit" name="srm_save_exam" class="button button-primary">
<?= $edit_exam ? 'Update Exam' : 'Save Exam' ?>
</button>

<?php if ($edit_exam): ?>
<a href="<?= admin_url('admin.php?page=srm_exams') ?>" class="button">Cancel</a>
<?php endif; ?>

</form>

<hr>

<!-- EXAM LIST -->
<h2>All Exams</h2>

<table class="wp-list-table widefat fixed striped">
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Classes</th>
<th>Session</th>
<th>Actions</th>
</tr>
</thead>

<tbody>
<?php if ($exams): foreach ($exams as $e): ?>
<tr>
<td><?= esc_html($e->id) ?></td>
<td><?= esc_html($e->name) ?></td>
<td><?= esc_html(srm_class_names($e->class, $classes)) ?></td>
<td><?= esc_html($e->session) ?></td>
<td>
<a href="<?= admin_url('admin.php?page=srm_exams&edit=' . $e->id) ?>">Edit</a> |
<a href="<?= admin_url('admin.php?page=srm_exams&delete=' . $e->id) ?>"
   onclick="return confirm('Delete this exam?')">Delete</a>
</td>
</tr>
<?php endforeach; else: ?>
<tr>
<td colspan="5" style="text-align:center;">No exams found</td>
</tr>
<?php endif; ?>
</tbody>
</table>

</div>

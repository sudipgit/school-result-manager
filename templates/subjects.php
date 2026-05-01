<?php
if (!current_user_can('edit_posts')) exit;

global $wpdb;
$table = $wpdb->prefix . 'srm_subjects';

/* ---------- EDIT LOAD ---------- */
$is_edit = false;
$subject = null;

if (isset($_GET['edit']) && intval($_GET['edit'])) {
    $is_edit = true;
    $subject_id = intval($_GET['edit']);

    $subject = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $subject_id)
    );

    if (!$subject) {
        echo '<div class="notice notice-error"><p>Subject not found.</p></div>';
        $is_edit = false;
    }
}

/* ---------- SAVE SUBJECT ---------- */
if (isset($_POST['srm_save_subject'])) {

    $class_values = $_POST['class'] ?? [];
    $class_csv = implode(',', array_map('intval', $class_values));

    $mcq = intval($_POST['mcq_marks']);
    $cq  = intval($_POST['cq_marks']);
    $pr  = intval($_POST['practical_marks']);

    $data = [
        'name'            => sanitize_text_field($_POST['name']),
        'class'           => $class_csv,
        'group'           => sanitize_text_field($_POST['group']),
        'mcq_marks'       => $mcq,
        'cq_marks'        => $cq,
        'practical_marks' => $pr,
        'full_marks'      => $mcq + $cq + $pr,
        'pass_marks'      => intval($_POST['pass_marks']),
    ];

    if (!empty($_POST['subject_id'])) {
        $wpdb->update($table, $data, ['id' => intval($_POST['subject_id'])]);
        echo '<div class="notice notice-success"><p>Subject updated successfully.</p></div>';
        $is_edit = false;
        $subject = null;
    } else {
        $wpdb->insert($table, $data);
        echo '<div class="notice notice-success"><p>Subject added successfully.</p></div>';
    }
}

/* ---------- DELETE ---------- */
if (isset($_GET['delete']) && intval($_GET['delete'])) {
    $wpdb->delete($table, ['id' => intval($_GET['delete'])]);
    echo '<div class="notice notice-success"><p>Subject deleted.</p></div>';
}

/* ---------- FILTER ---------- */
$filter_class = $_GET['filter_class'] ?? '';

$where = '';
if ($filter_class !== '') {
    $where = $wpdb->prepare("WHERE FIND_IN_SET(%d, class)", $filter_class);
}

/* ---------- FETCH ---------- */
$subjects = $wpdb->get_results("SELECT * FROM $table $where ORDER BY id DESC");

/* ---------- OPTIONS ---------- */
$classes = [
    6 => 'Six',
    7 => 'Seven',
    8 => 'Eight',
    9 => 'Nine',
    10 => 'Ten'
];

$groups = ['Science','Commerce','Humanities'];

$selected_classes = $subject ? explode(',', $subject->class) : [];
?>

<div class="wrap">
<h1><?= $is_edit ? 'Edit Subject' : 'Add Subject' ?></h1>

<!-- ADD / EDIT FORM -->
<form method="post">
<input type="hidden" name="subject_id" value="<?= esc_attr($subject->id ?? '') ?>">

<table class="form-table">

<tr>
<th>Subject Name</th>
<td><input type="text" name="name" value="<?= esc_attr($subject->name ?? '') ?>" required></td>
</tr>

<tr>
<th>Class</th>
<td>
<select name="class[]" multiple required style="height:120px;">
<?php foreach ($classes as $v => $l): ?>
<option value="<?= $v ?>" <?= in_array($v, $selected_classes) ? 'selected' : '' ?>>
    <?= $l ?>
</option>
<?php endforeach; ?>
</select>
<p class="description">Hold CTRL to select multiple classes</p>
</td>
</tr>

<tr>
<th>Group</th>
<td>
<select name="group">
<option value="">— All Groups —</option>
<?php foreach ($groups as $g): ?>
<option value="<?= esc_attr($g) ?>" <?= ($subject->group ?? '') === $g ? 'selected' : '' ?>>
    <?= esc_html($g) ?>
</option>
<?php endforeach; ?>
</select>
</td>
</tr>

<tr><th colspan="2"><strong>Marks Distribution</strong></th></tr>

<tr><th>MCQ</th><td><input type="number" name="mcq_marks" value="<?= esc_attr($subject->mcq_marks ?? '') ?>" required></td></tr>
<tr><th>CQ</th><td><input type="number" name="cq_marks" value="<?= esc_attr($subject->cq_marks ?? '') ?>" required></td></tr>
<tr><th>Practical</th><td><input type="number" name="practical_marks" value="<?= esc_attr($subject->practical_marks ?? 0) ?>"></td></tr>

<tr>
<th>Pass Mark</th>
<td><input type="number" name="pass_marks" value="<?= esc_attr($subject->pass_marks ?? '') ?>" required></td>
</tr>

</table>

<input type="submit" name="srm_save_subject" class="button button-primary"
       value="<?= $is_edit ? 'Update Subject' : 'Save Subject' ?>">

<?php if ($is_edit): ?>
<a href="?page=srm_subjects" class="button">Cancel</a>
<?php endif; ?>

</form>

<hr>

<!-- FILTER FORM -->
<form method="get" style="margin-bottom:15px;">
<input type="hidden" name="page" value="srm_subjects">

<select name="filter_class">
<option value="">All Classes</option>
<?php foreach ($classes as $v => $l): ?>
<option value="<?= $v ?>" <?= $filter_class==$v?'selected':'' ?>><?= $l ?></option>
<?php endforeach; ?>
</select>

<button class="button">Filter</button>
<a href="?page=srm_subjects" class="button">Reset</a>
</form>

<!-- SUBJECT LIST -->
<table class="wp-list-table widefat fixed striped">
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Class</th>
<th>Group</th>
<th>MCQ</th>
<th>CQ</th>
<th>Practical</th>
<th>Full</th>
<th>Pass</th>
<th>Action</th>
</tr>
</thead>

<tbody>
<?php if ($subjects): foreach ($subjects as $s): ?>
<tr>
<td><?= $s->id ?></td>
<td><?= esc_html($s->name) ?></td>
<td><?= esc_html($s->class) ?></td>
<td><?= esc_html($s->group ?: 'All') ?></td>
<td><?= $s->mcq_marks ?></td>
<td><?= $s->cq_marks ?></td>
<td><?= $s->practical_marks ?></td>
<td><strong><?= $s->full_marks ?></strong></td>
<td><strong><?= $s->pass_marks ?></strong></td>
<td>
<a href="?page=srm_subjects&edit=<?= $s->id ?>">Edit</a> | 
<a href="?page=srm_subjects&delete=<?= $s->id ?>" onclick="return confirm('Delete this subject?')">Delete</a>
</td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="10" style="text-align:center;">No subjects found</td></tr>
<?php endif; ?>
</tbody>
</table>

</div>

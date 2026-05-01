<?php
if (!current_user_can('edit_posts')) exit;

global $wpdb;

/* ---------- TABLES ---------- */
$subjects_table = $wpdb->prefix . 'srm_subjects';
$assign_table   = $wpdb->prefix . 'srm_subject_assignments';
$classes_table  = $wpdb->prefix . 'srm_classes';
$sections_table = $wpdb->prefix . 'srm_sections';
$exams_table    = $wpdb->prefix . 'srm_exams';

/* ---------- CURRENT SETTINGS ---------- */
$current_session = get_option('srm_active_session'); // e.g. 2026
$current_exam_id = intval(get_option('srm_ACTIVE_exam')); // exam_id

if (!$current_session || !$current_exam_id) {
    echo '<div class="notice notice-error"><p>Please set Current Session and Exam in Settings.</p></div>';
    return;
}

/* ---------- CLASSES ---------- */
$classes = $wpdb->get_results("SELECT * FROM $classes_table ORDER BY class_no ASC");

/* ---------- TEACHERS ---------- */
$teachers = get_users([
    'role'    => 'teacher',
    'orderby' => 'display_name',
    'order'   => 'ASC'
]);

/* ---------- FORM SUBMIT ---------- */
if (isset($_POST['srm_assign_subjects'])) {

    check_admin_referer('srm_assign_subjects');

    $class_no   = intval($_POST['class']);
    $section_id = intval($_POST['section']);
    $subjects   = $_POST['subjects'] ?? [];

    foreach ($subjects as $subject_id => $teacher_id) {

        $teacher_id = intval($teacher_id);
        if (!$teacher_id) continue;

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $assign_table 
             WHERE class_no=%d AND section_id=%d AND subject_id=%d 
             AND session=%s AND exam_id=%d",
            $class_no,
            $section_id,
            $subject_id,
            $current_session,
            $current_exam_id
        ));

        $data = [
            'class_no'   => $class_no,
            'section_id' => $section_id,
            'subject_id' => $subject_id,
            'teacher_id' => $teacher_id,
            'session'    => $current_session,
            'exam_id'    => $current_exam_id
        ];

        if ($exists) {
            $wpdb->update($assign_table, $data, ['id' => $exists]);
        } else {
            $wpdb->insert($assign_table, $data);
        }
    }

    echo '<div class="notice notice-success"><p>Subjects assigned successfully.</p></div>';
}

/* ---------- SELECTED ---------- */
$selected_class   = intval($_POST['class'] ?? 0);
$selected_section = intval($_POST['section'] ?? 0);

/* ---------- CLASS WISE SECTIONS ---------- */
$sections = [];
if ($selected_class) {
    $sections = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $sections_table WHERE class_no=%d", $selected_class)
    );
}

/* ---------- SUBJECTS ---------- */
$subjects = [];
if ($selected_class) {
    $subjects = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $subjects_table WHERE FIND_IN_SET(%d, class)",
            $selected_class
        )
    );
}

/* ---------- EXISTING ASSIGNMENTS ---------- */
$existing = [];
if ($selected_class && $selected_section) {

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT subject_id, teacher_id FROM $assign_table
         WHERE class_no=%d AND section_id=%d AND session=%s AND exam_id=%d",
        $selected_class,
        $selected_section,
        $current_session,
        $current_exam_id
    ));

    foreach ($rows as $r) {
        $existing[$r->subject_id] = $r->teacher_id;
    }
}
?>

<div class="wrap">
<h1>Assign Subjects to Teachers</h1>

<p>
<strong>Session:</strong> <?= esc_html($current_session) ?> |
<strong>Exam:</strong>
<?php
echo esc_html(
    $wpdb->get_var(
        $wpdb->prepare("SELECT name FROM $exams_table WHERE id=%d", $current_exam_id)
    )
);
?>
</p>

<form method="post">
<?php wp_nonce_field('srm_assign_subjects'); ?>

<select name="class" required onchange="this.form.submit()">
    <option value="">Class</option>
    <?php foreach ($classes as $c): ?>
        <option value="<?= $c->class_no ?>" <?= $selected_class==$c->class_no?'selected':'' ?>>
            <?= esc_html($c->name) ?>
        </option>
    <?php endforeach; ?>
</select>

<select name="section" required>
    <option value="">Section</option>
    <?php foreach ($sections as $s): ?>
        <option value="<?= $s->id ?>" <?= $selected_section==$s->id?'selected':'' ?>>
            <?= esc_html($s->name) ?>
        </option>
    <?php endforeach; ?>
</select>

<button class="button button-primary">Load Subjects</button>
</form>

<?php if ($subjects && $selected_section): ?>
<hr>

<form method="post">
<?php wp_nonce_field('srm_assign_subjects'); ?>
<input type="hidden" name="class" value="<?= $selected_class ?>">
<input type="hidden" name="section" value="<?= $selected_section ?>">

<table class="wp-list-table widefat striped">
<thead>
<tr>
    <th>Subject</th>
    <th>Teacher</th>
</tr>
</thead>
<tbody>
<?php foreach ($subjects as $sub): ?>
<tr>
<td><?= esc_html($sub->name) ?></td>
<td>
<select name="subjects[<?= $sub->id ?>]">
<option value="">Select</option>
<?php foreach ($teachers as $t): ?>
<option value="<?= $t->ID ?>" <?= ($existing[$sub->id] ?? 0)==$t->ID?'selected':'' ?>>
<?= esc_html($t->display_name) ?>
</option>
<?php endforeach; ?>
</select>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<br>
<button class="button button-primary" name="srm_assign_subjects">Save Assignments</button>
</form>
<?php endif; ?>
</div>

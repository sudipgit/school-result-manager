<?php
if (!current_user_can('edit_posts')) exit;

global $wpdb;
$table_students = $wpdb->prefix . 'srm_students';
$table_classes  = $wpdb->prefix . 'srm_classes';
$table_sections = $wpdb->prefix . 'srm_sections';

/* ================= PROMOTION HANDLER ================= */
if (isset($_POST['srm_promote'])) {

    check_admin_referer('srm_promote_students');

    $student_ids = array_map('intval', $_POST['student_ids'] ?? []);
    $new_class   = intval($_POST['new_class']);
    $new_session = sanitize_text_field($_POST['new_session']);
    $new_section = sanitize_text_field($_POST['new_section']);
    $rolls       = $_POST['new_roll'] ?? [];

    if (!$student_ids || !$new_class || !$new_session || !$new_section) {
        echo '<div class="notice notice-error"><p>Missing required data.</p></div>';
    } else {

        foreach ($student_ids as $student_id) {

            $new_roll = sanitize_text_field($rolls[$student_id] ?? '');
            if ($new_roll === '') continue;

            // Fetch student as ARRAY
            $student = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table_students WHERE id=%d", $student_id),
                ARRAY_A
            );

            if (!$student) continue;

            // Remove system fields
            unset($student['id'], $student['created_at']);

            // Update only required fields
            $student['roll']    = $new_roll;
            $student['class']   = $new_class;
            $student['section'] = $new_section;
            $student['session'] = $new_session;

            // Insert promoted student
            $wpdb->insert($table_students, $student);
        }

        echo '<div class="notice notice-success is-dismissible">
                <p>Students promoted successfully.</p>
              </div>';
    }
}

/* ================= LOAD FILTER ================= */
$current_class   = intval($_GET['class'] ?? 0);
$current_section = sanitize_text_field($_GET['section'] ?? '');
$current_session = sanitize_text_field($_GET['session'] ?? '');

/* ================= FETCH CLASSES ================= */
$classes = $wpdb->get_results(
    "SELECT * FROM $table_classes WHERE status=1 ORDER BY class_no ASC"
);

/* ================= FETCH SECTIONS ================= */
$sections = [];
if ($current_class) {
    $sections = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_sections WHERE class_no=%d AND status=1 ORDER BY name ASC",
            $current_class
        )
    );
}

/* ================= FETCH STUDENTS ================= */
$where = "WHERE 1=1";
$params = [];

if ($current_class) {
    $where .= " AND class=%d";
    $params[] = $current_class;
}
if ($current_section) {
    $where .= " AND section=%s";
    $params[] = $current_section;
}
if ($current_session) {
    $where .= " AND session=%s";
    $params[] = $current_session;
}

$sql = "SELECT * FROM $table_students $where ORDER BY roll ASC";
$students = $params ? $wpdb->get_results($wpdb->prepare($sql, $params)) : [];

/* ================= NEXT CLASS MAP ================= */
$next_class_map = [
    6 => 7,
    7 => 8,
    8 => 9,
    9 => 10
];
$next_class = $next_class_map[$current_class] ?? 0;

/* ================= SESSIONS ================= */
$sessions = ['2024','2025','2026','2027'];
?>

<div class="wrap">
<h1>Promote Students</h1>

<!-- LOAD STUDENTS -->
<form method="get" style="margin-bottom:20px;">
    <input type="hidden" name="page" value="srm_promote_students">

    <select name="class" required onchange="this.form.submit()">
        <option value="">Select Class</option>
        <?php foreach ($classes as $c): ?>
            <option value="<?= $c->class_no ?>" <?= $current_class==$c->class_no?'selected':'' ?>>
                <?= esc_html($c->name) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="section" required onchange="this.form.submit()">
        <option value="">Select Section</option>
        <?php foreach ($sections as $s): ?>
            <option value="<?= esc_attr($s->name) ?>" <?= $current_section==$s->name?'selected':'' ?>>
                <?= esc_html($s->name) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="session" required onchange="this.form.submit()">
        <option value="">Select Session</option>
        <?php foreach ($sessions as $sess): ?>
            <option value="<?= $sess ?>" <?= $current_session==$sess?'selected':'' ?>>
                <?= $sess ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php if ($students && $next_class): ?>

<hr>

<form method="post">
<?php wp_nonce_field('srm_promote_students'); ?>

<input type="hidden" name="new_class" value="<?= $next_class ?>">

<p><strong>Promoting To Class:</strong> <?= esc_html($next_class) ?></p>

<select name="new_section" required>
    <option value="">Select New Section</option>
    <?php foreach ($sections as $s): ?>
        <option value="<?= esc_attr($s->name) ?>"><?= esc_html($s->name) ?></option>
    <?php endforeach; ?>
</select>

<select name="new_session" required>
    <option value="">Select New Session</option>
    <?php foreach ($sessions as $sess): ?>
        <option value="<?= $sess ?>"><?= $sess ?></option>
    <?php endforeach; ?>
</select>

<table class="wp-list-table widefat striped" style="margin-top:15px;">
<thead>
<tr>
    <th><input type="checkbox" onclick="jQuery('.chk').prop('checked', this.checked)"></th>
    <th>Name</th>
    <th>Old Roll</th>
    <th>New Roll</th>
</tr>
</thead>
<tbody>

<?php foreach ($students as $st): ?>
<tr>
    <td><input type="checkbox" class="chk" name="student_ids[]" value="<?= $st->id ?>"></td>
    <td><?= esc_html($st->name) ?></td>
    <td><?= esc_html($st->roll) ?></td>
    <td>
        <input type="number" name="new_roll[<?= $st->id ?>]" min="1" style="width:80px;">
    </td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

<br>
<button type="submit" name="srm_promote" class="button button-primary">
    Promote Selected Students
</button>

</form>

<?php elseif ($current_class == 10): ?>
<p><strong>Class 10 students cannot be promoted.</strong></p>

<?php elseif ($current_class): ?>
<p>No students found.</p>
<?php endif; ?>

</div>

<?php
if (!current_user_can('edit_posts')) exit;

global $wpdb;

/* ===============================
   TABLES
================================ */
$table_marks    = $wpdb->prefix . 'srm_marks';
$table_students = $wpdb->prefix . 'srm_students';
$table_subjects = $wpdb->prefix . 'srm_subjects';
$table_classes  = $wpdb->prefix . 'srm_classes';
$table_sections = $wpdb->prefix . 'srm_sections';
$table_exams    = $wpdb->prefix . 'srm_exams';

/* ===============================
   ACTIVE SESSION & EXAM
================================ */
$active_session = get_option('srm_active_session');
$active_exam_id = (int) get_option('srm_active_exam');
$exam_name = $wpdb->get_var(
    $wpdb->prepare("SELECT name FROM $table_exams WHERE id=%d", $active_exam_id)
);

/* ===============================
   FILTER INPUTS
================================ */
$class_id   = isset($_GET['class'])   ? (int) $_GET['class']   : 0;
$section_id = isset($_GET['section']) ? (int) $_GET['section'] : 0;
$subject_id = isset($_GET['subject']) ? (int) $_GET['subject'] : 0;

/* ===============================
   DROPDOWN DATA
================================ */

// Classes
$classes = $wpdb->get_results(
    "SELECT class_no,name FROM $table_classes ORDER BY class_no ASC"
);

// Sections by selected class
$sections = [];
if ($class_id) {
    $sections = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_sections WHERE class_no=%d ORDER BY name ASC",
            $class_id
        )
    );
}

// Subjects by selected class
$subjects = [];
if ($class_id) {
    $subjects = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_subjects
             WHERE FIND_IN_SET(%d, class)
             ORDER BY name ASC",
            $class_id
        )
    );
}

/* ===============================
   DELETE MARK
================================ */
if (isset($_GET['delete']) && current_user_can('manage_options')) {
    $wpdb->delete($table_marks, ['id' => (int) $_GET['delete']]);
    echo '<div class="notice notice-success"><p>Mark deleted.</p></div>';
}

/* ===============================
   MARKS QUERY
   Only run if "Filter" button clicked
================================ */
$marks = [];
if (isset($_GET['filter'])) {

    $where  = "WHERE m.session=%s AND m.exam_id=%d";
    $params = [$active_session, $active_exam_id];

    if ($class_id) {
        $where .= " AND m.class_no=%d";
        $params[] = $class_id;
    }

    // Section filter via student table
    if ($section_id) {
        $where .= " AND m.roll IN (
            SELECT id FROM $table_students WHERE section_id=%d
        )";
        $params[] = $section_id;
    }

    if ($subject_id) {
        $where .= " AND m.subject_id=%d";
        $params[] = $subject_id;
    }

    $sql = "
    SELECT
        m.*,
        st.name AS student_name,
        st.roll,
        sub.name AS subject_name,
        (m.mcq + m.cq + m.practical) AS total
    FROM $table_marks m
    LEFT JOIN $table_students st ON st.id = m.roll
    LEFT JOIN $table_subjects sub ON sub.id = m.subject_id
    $where
    ORDER BY st.roll ASC
    ";

    $marks = $wpdb->get_results(
        $wpdb->prepare($sql, $params)
    );
}

?>

<div class="wrap">
<h1>Marks — <?= esc_html($exam_name) ?> (<?= esc_html($active_session) ?>)</h1>

<!-- ================= FILTER FORM ================= -->
<form method="get" style="margin-bottom:20px;" id="marks-filter-form">
<input type="hidden" name="page" value="srm_marks">

<select name="class" id="filter-class">
    <option value="">Class</option>
    <?php foreach ($classes as $c): ?>
        <option value="<?= $c->class_no ?>" <?= selected($class_id,$c->class_no,false) ?>>
            <?= esc_html($c->name) ?>
        </option>
    <?php endforeach; ?>
</select>

<select name="section" id="filter-section">
    <option value="">Section</option>
    <?php foreach ($sections as $s): ?>
        <option value="<?= $s->id ?>" <?= selected($section_id,$s->id,false) ?>>
            <?= esc_html($s->name) ?>
        </option>
    <?php endforeach; ?>
</select>

<select name="subject" id="filter-subject">
    <option value="">Subject</option>
    <?php foreach ($subjects as $sub): ?>
        <option value="<?= $sub->id ?>" <?= selected($subject_id,$sub->id,false) ?>>
            <?= esc_html($sub->name) ?>
        </option>
    <?php endforeach; ?>
</select>

<button class="button button-primary" name="filter" value="1">Filter</button>
</form>

<!-- ================= MARKS TABLE ================= -->
<table class="wp-list-table widefat striped" id="marks-table">
<thead>
<tr>
    <th class="sortable" data-sort="int">Roll</th>
    <th>Student</th>
    <th>Subject</th>
    <th>MCQ</th>
    <th>CQ</th>
    <th>Practical</th>
    <th class="sortable" data-sort="int">Total</th>
    <th>Action</th>
</tr>
</thead>
<tbody>
<?php if ($marks): foreach ($marks as $m): ?>
<tr>
    <td><?= esc_html($m->roll) ?></td>
    <td><?= esc_html($m->student_name) ?></td>
    <td><?= esc_html($m->subject_name) ?></td>
    <td><?= esc_html($m->mcq) ?></td>
    <td><?= esc_html($m->cq) ?></td>
    <td><?= esc_html($m->practical) ?></td>
    <td><strong><?= esc_html($m->total) ?></strong></td>
    <td>
        <a href="<?= admin_url('admin.php?page=srm_edit_mark&id='.$m->id) ?>">Edit</a> |
        <a href="<?= wp_nonce_url(
            admin_url('admin.php?page=srm_marks&delete='.$m->id),
            'delete_mark_'.$m->id
        ) ?>" onclick="return confirm('Delete this mark?')">Delete</a>
    </td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="8" style="text-align:center;">No marks found</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('filter-class');

    classSelect.addEventListener('change', function() {
        // Auto-submit form to update sections & subjects
        document.getElementById('marks-filter-form').submit();
    });
});
</script>


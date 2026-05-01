<?php
if (!current_user_can('edit_posts')) exit;

global $wpdb;

/* ---------- TABLES ---------- */
$assign_table   = $wpdb->prefix . 'srm_subject_assignments';
$subjects_table = $wpdb->prefix . 'srm_subjects';
$classes_table  = $wpdb->prefix . 'srm_classes';
$sections_table = $wpdb->prefix . 'srm_sections';
$exams_table    = $wpdb->prefix . 'srm_exams';

/* ---------- CURRENT SETTINGS ---------- */
$current_session = get_option('srm_active_session'); // eg: 2026
$current_exam_id = intval(get_option('srm_active_exam')); // exam_id

if (!$current_session || !$current_exam_id) {
    echo '<div class="notice notice-error"><p>
        Please set <strong>Current Session</strong> and <strong>Current Exam</strong> in settings.
    </p></div>';
    return;
}

/* ---------- FETCH CLASSES ---------- */
$classes = $wpdb->get_results("SELECT * FROM $classes_table ORDER BY class_no ASC");

/* ---------- FILTERS (ONLY CLASS & SECTION) ---------- */
$filter_class   = intval($_GET['class'] ?? 0);
$filter_section = intval($_GET['section'] ?? 0);

/* ---------- FETCH SECTIONS ---------- */
$sections = [];
if ($filter_class) {
    $sections = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $sections_table 
         WHERE id IN (
             SELECT DISTINCT section_id 
             FROM $assign_table 
             WHERE class_no=%d 
               AND session=%s 
               AND exam_id=%d
         )",
        $filter_class,
        $current_session,
        $current_exam_id
    ));
}

/* ---------- WHERE CLAUSE ---------- */
$where  = "WHERE a.session=%s AND a.exam_id=%d";
$params = [$current_session, $current_exam_id];

if ($filter_class) {
    $where .= " AND a.class_no=%d";
    $params[] = $filter_class;
}

if ($filter_section) {
    $where .= " AND a.section_id=%d";
    $params[] = $filter_section;
}

/* ---------- FETCH ASSIGNMENTS ---------- */
$sql = "
SELECT 
    a.*,
    s.name   AS subject_name,
    c.name   AS class_name,
    sec.name AS section_name,
    u.display_name AS teacher_name,
    e.name AS exam_name
FROM $assign_table a
LEFT JOIN $subjects_table s ON s.id = a.subject_id
LEFT JOIN $classes_table c ON c.class_no = a.class_no
LEFT JOIN $sections_table sec ON sec.id = a.section_id
LEFT JOIN {$wpdb->users} u ON u.ID = a.teacher_id
LEFT JOIN $exams_table e ON e.id = a.exam_id
$where
ORDER BY c.class_no ASC, sec.name ASC, s.name ASC
";

$assignments = $wpdb->get_results($wpdb->prepare($sql, $params));
?>

<div class="wrap">
    <h1>Subject Assignments</h1>

    <p>
        <strong>Session:</strong> <?= esc_html($current_session) ?> &nbsp; | &nbsp;
        <strong>Exam:</strong> <?= esc_html($wpdb->get_var(
            $wpdb->prepare("SELECT name FROM $exams_table WHERE id=%d", $current_exam_id)
        )) ?>
    </p>

    <!-- FILTER FORM -->
    <form method="get" style="margin-bottom:15px;">
        <input type="hidden" name="page" value="srm_subject_assignments">

        <select name="class" id="filter_class">
            <option value="">Select Class</option>
            <?php foreach ($classes as $c): ?>
                <option value="<?= $c->class_no ?>" <?= $filter_class==$c->class_no?'selected':'' ?>>
                    <?= esc_html($c->name) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="section" id="filter_section">
            <option value="">Select Section</option>
            <?php foreach ($sections as $s): ?>
                <option value="<?= $s->id ?>" <?= $filter_section==$s->id?'selected':'' ?>>
                    <?= esc_html($s->name) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button class="button">Filter</button>
        <a href="?page=srm_subject_assignments" class="button">Reset</a>
    </form>

    <!-- ASSIGNMENT TABLE -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Class</th>
                <th>Section</th>
                <th>Session</th>
                <th>Exam</th>
                <th>Subject</th>
                <th>Teacher</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($assignments): foreach ($assignments as $a): ?>
            <tr>
                <td><?= esc_html($a->class_name) ?></td>
                <td><?= esc_html($a->section_name) ?></td>
                <td><?= esc_html($a->session) ?></td>
                <td><?= esc_html($a->exam_name) ?></td>
                <td><?= esc_html($a->subject_name) ?></td>
                <td><?= esc_html($a->teacher_name ?: '— Not Assigned —') ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr>
                <td colspan="6" style="text-align:center;">No assignments found</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
jQuery(function($){
    $('#filter_class').on('change', function(){
        const url = new URL(window.location.href);
        url.searchParams.set('class', this.value);
        url.searchParams.delete('section');
        window.location.href = url.href;
    });
});
</script>

<?php
if (!is_user_logged_in()) {
    echo '<p>Please log in to view your profile.</p>';
    return;
}

$current_user = wp_get_current_user();

// Only allow teachers
if (!in_array('teacher', $current_user->roles)) {
    echo '<p>You do not have permission to view this page.</p>';
    return;
}

global $wpdb;

$assign_table   = $wpdb->prefix . 'srm_subject_assignments';
$subjects_table = $wpdb->prefix . 'srm_subjects';
$classes_table  = $wpdb->prefix . 'srm_classes';
$sections_table = $wpdb->prefix . 'srm_sections';

// Get active session and exam from admin settings
$active_session = get_option('srm_active_session');
$active_exam    = intval(get_option('srm_active_exam'));

// Fetch assigned subjects for current session and exam only
$assignments = $wpdb->get_results($wpdb->prepare(
    "SELECT a.subject_id, a.class_no, a.section_id, s.name as subject_name, s.group
     FROM $assign_table a
     INNER JOIN $subjects_table s ON a.subject_id = s.id
     WHERE a.teacher_id = %d
       AND a.session = %s
       AND a.exam_id = %d
     ORDER BY a.class_no, a.section_id",
     $current_user->ID,
     $active_session,
     $active_exam
));

// Fetch class and section names for each assignment
foreach ($assignments as $key => $a) {
    $class = $wpdb->get_row($wpdb->prepare("SELECT name, class_no FROM $classes_table WHERE class_no=%d", $a->class_no));
    $section = $wpdb->get_row($wpdb->prepare("SELECT name FROM $sections_table WHERE id=%d", $a->section_id));

    $assignments[$key]->class_name   = $class ? $class->name : '';
    $assignments[$key]->class_no     = $class ? $class->class_no : '';
    $assignments[$key]->section_name = $section ? $section->name : '';
}
?>

<div class="teacher-profile-wrap">
    <h1>Teacher Profile</h1>

    <h2><?= esc_html($current_user->display_name) ?></h2>
    <p><strong>Username:</strong> <?= esc_html($current_user->user_login) ?></p>
    <p><strong>Email:</strong> <?= esc_html($current_user->user_email) ?></p>

    <p><strong>Current Session:</strong> <?= esc_html($active_session) ?><br>
    <strong>Current Exam:</strong> 
    <?php
        $exam_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}srm_exams WHERE id=%d", $active_exam));
        echo esc_html($exam_name);
    ?>
    </p>

    <h3>Assigned Subjects</h3>

    <?php if ($assignments): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Subject</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($assignments as $a): ?>
                <tr>
                    <td><?= esc_html($a->class_name) ?></td>
                    <td><?= esc_html($a->section_name) ?></td>
                    <td><?= esc_html($a->subject_name) ?></td>
                    <td>
                        <a href="<?= site_url("/add-marks/?class_no={$a->class_no}&section_id={$a->section_id}&subject_id={$a->subject_id}") ?>">
                            Add Mark
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No subjects assigned for the current session and exam.</p>
    <?php endif; ?>
</div>

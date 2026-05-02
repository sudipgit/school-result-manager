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

$exam_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}srm_exams WHERE id=%d", $active_exam));
$photos = srm_get_teacher_photos();              


// Fetch class and section names for each assignment
foreach ($assignments as $key => $a) {
    $class = $wpdb->get_row($wpdb->prepare("SELECT name, class_no FROM $classes_table WHERE class_no=%d", $a->class_no));
    $section = $wpdb->get_row($wpdb->prepare("SELECT name FROM $sections_table WHERE id=%d", $a->section_id));

    $assignments[$key]->class_name   = $class ? $class->name : '';
    $assignments[$key]->class_no     = $class ? $class->class_no : '';
    $assignments[$key]->section_name = $section ? $section->name : '';
}
?>
<div class="dashboard-layout">
    <h2> <?= esc_html($exam_name)?> - <?= esc_html($active_session) ?></h2>
    <div class="d-sidebar">
        <div><img src="<?php echo $photos[$current_user->ID];?>" width="150"/></div> 
        <h2><?= esc_html($current_user->display_name) ?></h2>
        <p style="color:#fff;">Email: <?= esc_html($current_user->user_email) ?></p>
        <ul>
            <li><a href="https://rtnb.edu.bd/management/dashboard/">Dashboard</a></li>
            <li><a href="https://rtnb.edu.bd/management/dashboard/">Add Marks</a></li>
            <li><a href="https://rtnb.edu.bd/management/dashboard/">Download Marksheet</a></li>
        </ul>
        
    </div>
    <div class="d-content">
        <div class="teacher-profile-wrap">

            <h3>Assigned Subjects</h3>

            <?php if ($assignments): ?>
                <div class="marks-grid">
                 <?php foreach ($assignments as $a): ?>
                    <div class="mark-card">
                        <div class="card-header" style="text-align:center;">
                            <h3><?= esc_html($a->class_name) ?> - <?= esc_html($a->section_name) ?></h3>
                            <span><?= esc_html($a->subject_name) ?></span>
                        </div>

                        <div class="card-actions">
                            <a href="<?= site_url("/add-marks/?class={$a->class_no}&section={$a->section_id}&subject={$a->subject_id}") ?>" class="btn add">
                                Add Mark
                            </a>

                            <a href="<?= site_url("/view-marks/?class={$a->class_no}&section={$a->section_id}&subject={$a->subject_id}") ?>" class="btn view">
                                View Mark
                            </a>

                            <a href="<?= site_url("/management/teacher-mark-sheet/?class={$a->class_no}&section={$a->section_id}&subject={$a->subject_id}&teacher={$current_user->ID}") ?>" class="btn download">
                                Download MarkSheet
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>

                </div>

            <?php else: ?>
                <p>No subjects assigned for the current session and exam.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

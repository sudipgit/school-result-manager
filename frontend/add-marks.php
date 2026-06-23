<?php
if (!is_user_logged_in()) {
    return '<p>Please log in to add marks.</p>';
}

$current_user = wp_get_current_user();
if (!in_array('teacher', $current_user->roles)) {
    return '<p>You do not have permission to access this page.</p>';
}

global $wpdb;

$students_table   = $wpdb->prefix . 'srm_students';
$subjects_table   = $wpdb->prefix . 'srm_subjects';
$marks_table      = $wpdb->prefix . 'srm_marks';
$assign_table     = $wpdb->prefix . 'srm_subject_assignments';

// Get current active session and exam from admin settings
$active_session = get_option('srm_active_session'); // e.g., "2025-2026"
$active_exam_id = intval(get_option('srm_active_exam')); // e.g., 3

// Get data from URL
$class_no   = intval($_GET['class'] ?? 0);
$section_id = intval($_GET['section'] ?? 0);
$subject_id = intval($_GET['subject'] ?? 0);

if (!$class_no || !$section_id || !$subject_id) {
    return '<p>Invalid class, section, or subject.</p>';
}

// Fetch subject info
$subject = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $subjects_table WHERE id=%d",
    $subject_id
));
if (!$subject) return '<p>Subject not found.</p>';

// Fetch assignment for current session and exam only
$assignment = $wpdb->get_row($wpdb->prepare(
    "SELECT session, exam_id FROM $assign_table 
     WHERE class_no=%d AND section_id=%d AND subject_id=%d AND teacher_id=%d
       AND session=%s AND exam_id=%d
     LIMIT 1",
    $class_no, $section_id, $subject_id, $current_user->ID, $active_session, $active_exam_id
));


if (!$assignment) {
    return '<p>Marks can only be added for the current active session and exam.</p>';
}

$session = $assignment->session;
$exam_id = $assignment->exam_id;

// Fetch section name
$section_name = $wpdb->get_var($wpdb->prepare(
    "SELECT name FROM {$wpdb->prefix}srm_sections WHERE id=%d",
    $section_id
));

// Fetch students in this class/section

    
if($subject_id==8){
    $students = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $students_table WHERE class_no=%d AND section=%s AND session=%s AND religion='Islam' ORDER BY CAST(roll AS UNSIGNED) ASC",
        $class_no, $section_name,$active_session
    ));
}else if($subject_id==9){
    $students = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $students_table WHERE class_no=%d AND section=%s AND session=%s AND religion='Hindu' ORDER BY CAST(roll AS UNSIGNED) ASC",
    $class_no, $section_name,$active_session
    ));
}else{
    $students = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $students_table WHERE class_no=%d AND section=%s AND session=%s ORDER BY CAST(roll AS UNSIGNED) ASC",
        $class_no, $section_name,$active_session
    ));
}


if (!$students) return '<p>No students found for this subject.</p>';
$msg = '';
// Handle form submission
if (isset($_POST['srm_save_marks'])) {
    check_admin_referer('srm_save_marks');

    foreach ($_POST['marks'] as $roll => $marks) {
        $mcq = intval($marks['mcq'] ?? 0);
        $cq  = intval($marks['cq'] ?? 0);
        $pr  = intval($marks['practical'] ?? 0);
        $total = $cq + $mcq + $pr;
        // Check if mark row exists
        $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $marks_table 
        WHERE roll=%d AND class_no=%d AND subject_id=%d AND session=%s AND exam_id=%d",
        $roll, $class_no, $subject_id, $session, $exam_id
        ));


       $data = [
        'roll' => $roll,
        'class_no'   => $class_no, // add this
        'section_id' => $section_id, 
        'subject_id' => $subject_id,
        'teacher_id' => $current_user->ID,
        'mcq'        => $mcq,
        'cq'         => $cq,
        'practical'  => $pr,
        'total'      => $total,
        'session'    => $session,
        'exam_id'    => $exam_id,
        ];                                          


        if ($exists) {
            $wpdb->update($marks_table, $data, ['id' => $exists]);
        } else {
            $wpdb->insert($marks_table, $data);
        }
    }

    $msg =  '<div class="notice notice-success"><p>Marks saved successfully.</p></div>';
}

$exam_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}srm_exams WHERE id=%d", $active_exam_id));
$photos = srm_get_teacher_photos();   

// Display form
ob_start();
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
        <?php echo $msg;?>
        <h2><?= esc_html($subject->name) ?></h2>
        <form method="post">
            <?php wp_nonce_field('srm_save_marks'); ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Roll</th>
                        <th>Name</th>
                        <?php if ($subject->mcq_marks > 0): ?><th>MCQ (Max: <?= $subject->mcq_marks ?>)</th><?php endif; ?>
                        <?php if ($subject->cq_marks > 0): ?><th>CQ (Max: <?= $subject->cq_marks ?>)</th><?php endif; ?>
                        <?php if ($subject->practical_marks > 0): ?><th>Practical (Max: <?= $subject->practical_marks ?>)</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                        <?php
                        $existing = $wpdb->get_row($wpdb->prepare(
                            "SELECT * FROM $marks_table 
                            WHERE roll=%d AND subject_id=%d AND session=%s AND exam_id=%d",
                            $s->roll, $subject_id, $session, $exam_id
                        ));
                        ?>
                        <tr>
                            <td><?= esc_html($s->roll) ?></td>
                            <td><?= esc_html($s->name) ?></td>
                            <?php if ($subject->mcq_marks > 0): ?>
                                <td><input type="number" name="marks[<?= $s->roll ?>][mcq]" min="0" max="<?= $subject->mcq_marks ?>" value="<?= $existing->mcq ?? '' ?>"></td>
                            <?php endif; ?>
                            <?php if ($subject->cq_marks > 0): ?>
                                <td><input type="number" name="marks[<?= $s->roll ?>][cq]" min="0" max="<?= $subject->cq_marks ?>" value="<?= $existing->cq ?? '' ?>"></td>
                            <?php endif; ?>
                            <?php if ($subject->practical_marks > 0): ?>
                                <td><input type="number" name="marks[<?= $s->roll ?>][practical]" min="0" max="<?= $subject->practical_marks ?>" value="<?= $existing->practical ?? '' ?>"></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <br>
            <button type="submit" name="srm_save_marks" class="button button-primary">Save All Marks</button>
        </form>
    </div>
 </div>        

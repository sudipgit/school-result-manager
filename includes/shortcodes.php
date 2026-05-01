<?php

function srm_academic_settings_shortcode() {
    srm_check_permission();
    include SRM_PLUGIN_PATH . 'templates/academic-settings.php';
}
add_shortcode('srm_academic_settings','srm_academic_settings_shortcode');

function srm_add_student_shortcode() {
   srm_check_permission();
   include SRM_PLUGIN_PATH . 'templates/add-student.php';
}
add_shortcode('srm_add_student','srm_add_student_shortcode');


add_shortcode('teacher_profile', function() {
     ob_start();
    include SRM_PLUGIN_PATH . 'frontend/teacher-profile.php';
     return ob_get_clean();
});

add_shortcode('srm_add_marks', function() {
    ob_start();
    include SRM_PLUGIN_PATH . 'frontend/add-marks.php';
    return ob_get_clean();
});
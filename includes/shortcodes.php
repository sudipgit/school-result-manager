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


add_shortcode('srm_dashboard', function() {
    ob_start();
    if (current_user_can('administrator')) {
        include SRM_PLUGIN_PATH . 'frontend/dashboard.php';
    }else{
        include SRM_PLUGIN_PATH . 'frontend/teacher-profile.php';
    }
    
    return ob_get_clean();
});

add_shortcode('srm_add_marks', function() {
    ob_start();
    include SRM_PLUGIN_PATH . 'frontend/add-marks.php';
    return ob_get_clean();
});

add_shortcode('srm_view_marks', function() {
    ob_start();
    include SRM_PLUGIN_PATH . 'frontend/view-marks.php';
    return ob_get_clean();
});
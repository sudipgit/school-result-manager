<?php

function srm_academic_settings_shortcode() {
    srm_check_permission();
    ob_start();
    include SRM_PLUGIN_PATH . 'templates/academic-settings.php';
    return ob_get_clean();
}
add_shortcode('srm_academic_settings','srm_academic_settings_shortcode');

function srm_add_student_shortcode() {
     
    srm_check_permission();
    ob_start();
    include SRM_PLUGIN_PATH . 'templates/add-student.php';
    return ob_get_clean();
}
add_shortcode('srm_add_student','srm_add_student_shortcode');

function srm_update_student_csv_shortcode() {
     
    srm_check_permission();
    ob_start();
    include SRM_PLUGIN_PATH . 'templates/update-students-csv.php';
    return ob_get_clean();
}
add_shortcode('srm_update_student_csv','srm_update_student_csv_shortcode');

function srm_student_list_shortcode() {
     
    srm_check_permission();
    ob_start();
    include SRM_PLUGIN_PATH . 'templates/students.php';
    return ob_get_clean();
}
add_shortcode('srm_student_list','srm_student_list_shortcode');

add_shortcode('srm_dashboard', function() {
    ob_start();
    if (current_user_can('administrator') || current_user_can('management')) {
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

add_shortcode('srm_download_marksheet', function() {
    ob_start();
    include SRM_PLUGIN_PATH . 'frontend/download-teacher-marksheet.php';
    return ob_get_clean();
});

add_shortcode('srm_download_seat_plan', function() {
    ob_start();
    include SRM_PLUGIN_PATH . 'frontend/download-seat-plan.php';
    return ob_get_clean();
});



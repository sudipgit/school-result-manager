<?php
function srm_get_all_exams(){
    global $wpdb;
    $exams_table = $wpdb->prefix . 'srm_exams';
    $exams = $wpdb->get_results(
        "SELECT * FROM $exams_table ORDER BY session DESC, name ASC"
    );
    return $exams;
}

function srm_save_current_session($post=null){
    if(empty($post)){
        return;
    }
    update_option('srm_active_session', sanitize_text_field($post['session']));
    update_option('srm_active_exam', intval($post['exam_id']));
}


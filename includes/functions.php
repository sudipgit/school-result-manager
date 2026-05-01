<?php
function srm_get_sessions(){
    $sessions=['2024','2025','2026'];
    return $sessions;
}

function srm_check_permission(){
    if (!is_user_logged_in()) {
        return 'Please login to access this page.';
    }
    $user = wp_get_current_user();
    if (!in_array('school_manager', (array) $user->roles) && !current_user_can('manage_options')) {
        return 'You do not have permission to access this page.';
    }
}

?>
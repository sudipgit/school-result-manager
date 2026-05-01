<?php
function srm_seat_plan_shortcode() {
    if (!is_user_logged_in()) {
        return 'Please login to view this page.';
    }

    $user = wp_get_current_user();
    if (!in_array('school_manager', (array) $user->roles) && !current_user_can('manage_options')) {
       return 'You do not have permission to view this page.';
    }

    ob_start();
    ?>
    <div class="srm-seat-plan">
        <h2>Seat Plan</h2>
        <ul>
            <li><a target="_blank" href="https://rtnb.edu.bd/download-seat-plan/?class=6">Six All</a></li>
            <li><a target="_blank" href="https://rtnb.edu.bd/download-seat-plan/?class=6&gender=Male">Six Male</a></li>
            <li><a target="_blank" href="https://rtnb.edu.bd/download-seat-plan/?class=6&gender=Female">Six Female</a></li>
        </ul>
   </div>
    <?php

    return ob_get_clean();
}

add_shortcode('srm_seat_plan', 'srm_seat_plan_shortcode');
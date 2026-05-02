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
function srm_get_teacher_photos(){
    $list=array(
        1  => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        2  => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        3  => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        4  => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        5  => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        6  => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        7  => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        8  => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        9  => 'https://rtnb.edu.bd/wp-content/uploads/2024/11/arafat-sir-e1732155546808-1-1024x1024.jpeg',
        10 => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        11 => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        12 => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        13 => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        14 => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/FB_IMG_1693288822977-e1693459835484.jpg',
        15 => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        16 => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        17 => 'https://rtnb.edu.bd/wp-content/uploads/2026/04/19.-সুদীপ-কুমার-সরকার.jpg',
        18 => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        19 => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/achintya-sir-e1693460947285-768x768.png',
        20 => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        21 => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        22 => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        23 => 'https://rtnb.edu.bd/wp-content/uploads/2023/08/azhar-sir-1.jpg',
        
    );

    return $list;
}





?>
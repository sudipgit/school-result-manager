<?php
add_shortcode('srm_seat_plan', 'srm_seat_plan_shortcode');
add_shortcode('srm_teacher_list', 'srm_teacher_list_shortcode');
add_shortcode('srm_teacher_info', 'srm_teacher_info_shortcode');


function srm_seat_plan_shortcode() {
    if (!is_user_logged_in()) {
        return 'Please login to view this page.';
    }

    $user = wp_get_current_user();
    if (!in_array('school_manager', (array) $user->roles) && !current_user_can('manage_options')) {
       return 'You do not have permission to view this page.';
    }
    $classes = srm_get_all_classes();
    ob_start();
    ?>
    <div class="srm-seat-plan">
        <div class="blocks">
        <?php foreach($classes as $cl) { ?>
            <div class="block>">
                <h3><?php echo $cl->name;?></h3>
                <ul>
                    <li><a target="_blank" href="https://rtnb.edu.bd/download-seat-plan/?class=<?php echo $cl->class_no;?>"><?php echo $cl->name;?> All</a></li>
                    <li><a target="_blank" href="https://rtnb.edu.bd/download-seat-plan/?class=<?php echo $cl->class_no;?>&gender=Male"><?php echo $cl->name;?> Male</a></li>
                    <li><a target="_blank" href="https://rtnb.edu.bd/download-seat-plan/?class=<?php echo $cl->class_no;?>&gender=Female"><?php echo $cl->name;?> Female</a></li>
                </ul>
            </div>
        <?php } ?>
        </div>
   </div>
    <?php

    return ob_get_clean();
}

function srm_teacher_list_shortcode() {
    if (!is_user_logged_in()) {
        return 'Please login to view this page.';
    }

    $user = wp_get_current_user();
    if (!in_array('school_manager', (array) $user->roles) && !current_user_can('manage_options')) {
       return 'You do not have permission to view this page.';
    }
   
    $teachers = get_users([
        'role'    => 'teacher',
        'orderby' => 'display_name',
        'order'   => 'ASC'
    ]);


    $photos = srm_get_teacher_photos(); 
    ob_start();
    ?>
    <div class="srm-seat-plan">
         <ul class="grid-a">
       <?php foreach ($teachers as $teacher) { ?>
       
            
        <li>
            <a target="_blank" href="https://rtnb.edu.bd/management/teacher-info/?teacher=<?php echo $teacher->ID;?>">
                <div class="menu-card-body">
                <div class="menu-card-icon">
                   <img src="<?php echo $photos[$teacher->ID];?>"/>
                </div>
                <div class="menu-card-name"><?php echo $teacher->display_name;?></div>
               
                </div>
                <div class="menu-card-footer">Open →</div>
            </a>
        </li>
     
        <?php } ?>
        </ul>
   </div>
    <?php

    return ob_get_clean();
}



function srm_teacher_info_shortcode() {
    if (!is_user_logged_in()) {
        return 'Please login to view this page.';
    }

    $user = wp_get_current_user();
    if (!in_array('school_manager', (array) $user->roles) && !current_user_can('manage_options')) {
       return 'You do not have permission to view this page.';
    }
   
    $teacher_id = intval($_GET['teacher'] ?? 0);
    $teacher = get_user_by('ID', $teacher_id);
    $classes = srm_get_all_classes();
    $sections = srm_get_all_sections();
    $subjects = srm_get_all_subjects();
    $active_session = get_option('srm_active_session');
    $active_exam    = intval(get_option('srm_active_exam'));
    $assigned_classes  = srm_get_assigned_classes($teacher_id,$active_session,$active_exam);

    ob_start();
    ?>
    <div class="teacher-info">
         <!-- ── Teacher header banner ── -->
    <div class="teacher-info-header">
      <div class="teacher-avatar">S</div><!-- First letter of teacher name -->
      <div>
        <div class="teacher-meta-name">Sudip Kumar Sarkar</div>
        <div class="teacher-meta-role">Teacher</div>
      </div>
      <div class="teacher-subject-count">
        <strong>2</strong><!-- Update this number dynamically in PHP -->
        <span>Subjects</span>
      </div>
    </div>

    <!-- ── Section label ── -->
    <div class="subjects-divider-label">Assigned Subjects</div>
        <ul class="grid-li">
            <?php foreach($assigned_classes as $cl) { ?>
            
          <li>
           <h3> <?php echo $subjects[$cl->subject_id]->name;?></h3>
            <p> <?php echo $classes[$cl->class_no]->name;?> - <?php echo 'Section '.$sections[$cl->section_id]->name;?></p>
            <p><a target="_blank" href="https://rtnb.edu.bd/management/view-marks/?class=<?php echo $cl->class_no;?>&section=<?php echo $cl->section_id;?>&subject=<?php echo $cl->subject_id;?>&teacher=<?php echo $teacher_id;?>">View Marks</a>
            <a target="_blank" href="https://rtnb.edu.bd/management/download-teacher-marksheet/?class=<?php echo $cl->class_no;?>&section=<?php echo $cl->section_id;?>&subject=<?php echo $cl->subject_id;?>&teacher=<?php echo $teacher_id;?>"> Marksheet</a></p>
</li>
          <?php } ?>
</ul>
   </div>
    <?php

    return ob_get_clean();
}


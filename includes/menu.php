<?php
function srm_admin_menu() {
    add_menu_page('School Results', 'School Results', 'edit_posts', 'srm_dashboard', 'srm_academic_settings', 'dashicons-welcome-learn-more', 25);
    add_submenu_page('srm_dashboard', 'Add Student', 'Add Student', 'edit_posts', 'srm_add_student', 'srm_add_student_page');
    add_submenu_page('srm_dashboard', 'Students', 'Students', 'edit_posts', 'srm_student_list', 'srm_student_list_page');

    add_submenu_page(
        'srm_dashboard',          // Parent slug
        'Teachers',               // Page title
        'Teachers',               // Menu title
        'edit_posts',         // Capability (or 'teacher' if you want teachers to see it)
        'srm_teachers',           // Menu slug
        'srm_teachers_page'       // Callback function
    );
    add_submenu_page('srm_dashboard', 'Subjects', 'Subjects', 'edit_posts', 'srm_subjects', 'srm_subjects_page');
    add_submenu_page('srm_dashboard', 'Exams', 'Exams', 'edit_posts', 'srm_exams', 'srm_exams_page');
    add_submenu_page('srm_dashboard', 'Marks', 'Marks', 'edit_posts', 'srm_marks', 'srm_marks_page');
    add_submenu_page(   
        'srm_dashboard',
        'Promote Students',
        'Promote Students',
        'edit_posts',
        'srm_promote_students',
        'srm_promote_students_page'
    );
    add_submenu_page(
        'srm_dashboard',
        'Assign Subjects',
        'Assign Subjects',
        'edit_posts',
        'srm_teacher_subjects',
        'srm_teacher_subjects_page'
    );

      add_submenu_page(
        'srm_dashboard',
        'Assign Subjects List',
        'Assign Subjects List',
        'edit_posts',
        'srm_subject_assignments',
        'srm_assign_subjects_page'
    );

    add_submenu_page(
    'srm_dashboard',
    'Classes',
    'Classes',
    'edit_posts',
    'srm_classes',
    'srm_classes_page'
);

add_submenu_page(
    'srm_dashboard',
    'Sections',
    'Sections',
    'edit_posts',
    'srm_sections',
    'srm_sections_page'
);

add_submenu_page(
    'srm_dashboard',
    'Academic Settings',
    'Academic Settings',
    'edit_posts',
    'srm_academic_settings',
    'srm_academic_settings'
    
);

add_submenu_page(
    'srm_dashboard',
    'Tabulation Sheet',
    'Tabulation Sheet',
    'edit_posts',
    'srm_tabulation',
    'srm_tabulation_page'
    
);
add_submenu_page(
    'srm_dashboard',
    'Merit List',
    'Merit List',
    'edit_posts',
    'srm_merit',
    'srm_merit_page'
    
);



}

function srm_tabulation_page() {
    include SRM_PLUGIN_PATH . 'templates/tabulation-sheet.php';
}
function srm_merit_page() {
    include SRM_PLUGIN_PATH . 'templates/merit-list.php';
}



function srm_academic_settings() {
        include SRM_PLUGIN_PATH . 'templates/academic-settings.php';
    }

// Teachers page callback
function srm_teachers_page() {
    include SRM_PLUGIN_PATH . 'templates/teachers.php';

}

function srm_add_student_page() {
    include SRM_PLUGIN_PATH . 'templates/add-student.php';
}

function srm_student_list_page() {
    include SRM_PLUGIN_PATH . 'templates/students.php';
}

function srm_subjects_page() {
    include SRM_PLUGIN_PATH . 'templates/subjects.php';
}

function srm_exams_page() {
    include SRM_PLUGIN_PATH . 'templates/exams.php';
}

function srm_classes_page() {
    include SRM_PLUGIN_PATH . 'templates/classes.php';
}

function srm_sections_page() {
    include SRM_PLUGIN_PATH . 'templates/sections.php';
}

function srm_marks_page() {
    include SRM_PLUGIN_PATH . 'templates/marks.php';
}

function srm_teacher_subjects_page() {
    include SRM_PLUGIN_PATH . 'templates/teacher-subject-assign.php';
}

function srm_promote_students_page() {
    include SRM_PLUGIN_PATH . 'templates/promote-students.php';
}
function srm_assign_subjects_page() {
    include SRM_PLUGIN_PATH . 'templates/subject-assignments.php';
}
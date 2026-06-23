<?php

    global $wpdb;
    $table = $wpdb->prefix . 'srm_marks';
    $table2 = $wpdb->prefix . 'srm_students'; 
    $active_session = get_option('srm_active_session'); // e.g., "2025-2026"
    $active_exam_id = intval(get_option('srm_active_exam')); // e.g., 3
    

    // Handle form submission (filters)
    $class   = $_GET['class'] ?? '';
    $section_id = $_GET['section'] ?? '';
    $subject_id  = $_GET['subject'] ?? '';
    $teacher_id  = $_GET['teacher'] ?? '';
    $col  = $_GET['col'] ?? '';
    $section_title = srm_get_section_title($section_id);
    $subject_title = srm_get_subject_title($subject_id);
    $teacher = $user = get_user_by('ID', $teacher_id);
//var_dump($subject_title);exit;
    $classes = array(
        6=>'Six',
        7=>'Seven',
        8=>'Eight',
        9=>'Nine',
        10=>'Ten'
    );


    // Build query
    $where = [];
    if ($class) $where[] = $wpdb->prepare("m.class_no = %d", $class);
    if ($section_id) $where[] = $wpdb->prepare("m.section_id = %d", $section_id);
    if ($subject_id) $where[] = $wpdb->prepare("m.subject_id = %d", $subject_id);
    $where[] = $wpdb->prepare("m.session = %s", $active_session);
    $where[] = $wpdb->prepare("m.exam_id = %d", $active_exam_id);

  /*  $sql = "SELECT M.*, S.name  FROM $table as M LEFT JOIN  $table2 as S on M.roll=S.roll";
    if (!empty($where)) $sql .= " WHERE " . implode(' AND ', $where);
    $sql .= " ORDER BY M.roll ASC";
    var_dump($sql);exit;*/

   $sql ="SELECT 
    m.id,
    m.roll,
    s.name AS student_name,
    s.name_en AS student_name_en,
    m.subject_id,
    m.mcq,
    m.cq,
    m.practical,
    m.total,
    m.class_no,
    m.section_id,
    m.session,
    m.exam_id
FROM $table m
JOIN $table2 s 
    ON m.roll = s.roll 
    AND m.class_no = s.class_no 
    AND m.section_id = s.section_id 
    AND m.session = s.session";
     if (!empty($where)) $sql .= " WHERE " . implode(' AND ', $where);
    $sql .= " ORDER BY m.roll ASC";
    $marks = $wpdb->get_results($sql);
   // var_dump($sql)
?>

 <?php
$total = count($marks);
$half = ceil($total / 2);

$left_column  = array_slice($marks, 0, $half);
$right_column = array_slice($marks, $half);
?>

<div class="marksheet">

    <div class="header">
        <h1>Ratanpur Taraknath Bidyapith (High School)</h1>
        <p>Class: <?= esc_html($classes[$class] ?? '') ?> | Section: <?= esc_html($section_title) ?>| Teacher: <?= esc_html($teacher->display_name) ?></p>
        <h2>Subject: <?= esc_html($subject_title) ?> </h2>
    </div>

    <div class="marks-columns">
        <?php if($col == 2){ ?>
        <div class="marks-col">
            <?php srm_render_marks_table($left_column); ?>
        </div>
        <div class="marks-col">
            <?php srm_render_marks_table($right_column); ?>
        </div>
        <?php } else{  ?>

          <?php srm_render_marks_table($marks); ?>

      <?php  }?>
    </div>

    <div class="footer">
        <p>Generated on: <?= date('d M Y') ?></p>
        <div class="sign">
            <span>____________________</span>
            <p>Teacher Signature</p>
        </div>
    </div>

</div>
<style>
body {
    font-family: Arial, sans-serif;
}

/* Container */
.marksheet {
    width: 96%;
    padding: 20px;
}

/* Header */
.header {
    text-align: center;
    margin-bottom: 20px;
}

.header h1 {
    margin: 0;
    font-size: 22px;
}

.header h2 {
    margin: 5px 0;
    font-size: 18px;
}

/* Table */
.marks-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.marks-table th,
.marks-table td {
    border: 1px solid #333;
    padding: 2px 5px;
    text-align: center;
}

.marks-table th {
    background: #f2f2f2;
    font-weight: bold;
}

/* Zebra rows */
.marks-table tr:nth-child(even) {
    background: #fafafa;
}

/* Footer */
.footer {
    margin-top: 40px;
    display: flex;
    justify-content: space-between;
}

.sign {
    text-align: center;
}

.sign span {
    display: block;
    margin-bottom: 5px;
}

.marks-columns {
    width: 100%;
    overflow: hidden; /* clearfix for floated children */
}

.marks-col {
    float: left;
    width: 48%;
}

.marks-col:first-child {
    margin-right: 4%;
}

.marks-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    font-size: 14px; /* slightly smaller so two fit cleanly per page */
}

.marks-table th,
.marks-table td {
    border: 1px solid #333;
    padding: 2px 5px;
    text-align: center;
}

.marks-table th {
    background: #f2f2f2;
    font-weight: bold;
}

.marks-table tr:nth-child(even) {
    background: #fafafa;
}

</style>
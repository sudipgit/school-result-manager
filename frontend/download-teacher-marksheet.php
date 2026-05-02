<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


    global $wpdb;
    $table = $wpdb->prefix . 'srm_marks';
    $table2 = $wpdb->prefix . 'srm_students'; 
    

    // Handle form submission (filters)
    $class   = $_GET['class'] ?? '';
    $section_id = $_GET['section'] ?? '';
    $subject_id  = $_GET['subject'] ?? '';
    $teacher_id  = $_GET['teacher'] ?? '';

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
    if ($class) $where[] = $wpdb->prepare("M.class_no = %s", $class);
    if ($section_id) $where[] = $wpdb->prepare("M.section_id = %s", $section_id);
    if ($subject_id) $where[] = $wpdb->prepare("M.subject_id = %s", $subject_id);

    $sql = "SELECT M.*, S.name_en  FROM $table as M LEFT JOIN  $table2 as S on M.roll=S.roll";
    if (!empty($where)) $sql .= " WHERE " . implode(' AND ', $where);
    $sql .= " ORDER BY M.roll ASC";

    $marks = $wpdb->get_results($sql);


   ob_start();
?>

<div class="marksheet">

    <div class="header">
        <h1>Ratanpur Taraknath Bidyapith (High School)</h1>
        <p>Class: <?= esc_html($classes[$class] ?? '') ?> | Section: <?= esc_html($section_title) ?>| Teacher: <?= esc_html($teacher->display_name) ?></p>
        <h2>Subject: <?= esc_html($subject_title) ?> </h2>
    </div>

    <table class="marks-table">
        <thead>
            <tr>
                <th>Roll</th>
                <th>Student Name</th>
                <th>CQ</th>
                <th>MCQ</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach($marks as $mark): ?>
            <tr>
                <td><?= esc_html($mark->roll) ?></td>
                <td><?= esc_html($mark->name_en) ?></td>
                <td><?= esc_html($mark->cq) ?></td>
                <td><?= esc_html($mark->mcq) ?></td>
                <td><strong><?= esc_html($mark->total) ?></strong></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

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
    width: 100%;
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
</style>

<?php
$html = ob_get_clean();

    $filename = "Mark_Sheet_{$class}_{$section_title}_{$subject_title}.pdf";
   
    srm_generate_pdf($html,$filename);
    exit;
   //echo $html;
    

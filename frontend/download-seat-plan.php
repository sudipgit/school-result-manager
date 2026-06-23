<?php

    global $wpdb;
    $table = $wpdb->prefix . 'srm_students'; // your students table

    // Handle form submission (filters)
    $class   = $_GET['class'] ?? '';
    $section = $_GET['section'] ?? '';
    $gender  = $_GET['gender'] ?? '';

    $classes = array(
        6=>'Six',
        7=>'Seven',
        8=>'Eight',
        9=>'Nine',
        10=>'Ten'
    );


    // Build query
    $where = [];
    if ($class) $where[] = $wpdb->prepare("class_no = %s", $class);
    if ($section) $where[] = $wpdb->prepare("section = %s", $section);
    if ($gender) $where[] = $wpdb->prepare("gender = %s", $gender);

    $sql = "SELECT * FROM $table";
    if (!empty($where)) $sql .= " WHERE " . implode(' AND ', $where);
    $sql .= " ORDER BY roll ASC";

    $students = $wpdb->get_results($sql);

    ?>

<div class="marksheet">
    <div class="seat-grid">
        <?php foreach($students as $stu): ?>
            <div class="seat">
                <div class="top"><img src="https://rtnb.edu.bd/wp-content/uploads/2023/08/logo-77x77.jpg" width="50"/><h2>Ratanpur Taraknath Bidyapith(H.S.)</h2></div>
                <p class="sp">Seat Plan</p>
                <p class="name"><strong>Name: </strong> <?= esc_html($stu->name) ?></p>
                <div><ul><li><strong>Class:</strong></li><li><?= esc_html($classes[$stu->class_no]) ?></li></ul><ul><li><strong>Section:</strong></li><li><?= esc_html($stu->section) ?></li></ul></div>
                <div><ul><li><strong>Roll:</strong></li><li><?= esc_html($stu->roll) ?></li></ul><ul><li><strong>Gender:</strong></li><li><?= esc_html($stu->gender) ?></li></ul></div>
 
            </div>
        <?php endforeach; ?>
    </div>

    <style>
    .seat-grid { display: inline-block; }
    .seat .top *{display:inline-block;}
    .seat .top img{width:15%;float:left;}
    .seat {display: inline-block; width:31%; height:160px; border:1px solid #333; text-align:center; padding:5px; margin:5px; vertical-align:top; font-size:12px;}
    .seat .sp{text-align:center;fort-size:11px;}
    .seat .name{text-align:center;fort-size:14px;}
    .seat table{width:80%;margin:0 auto;}
    .seat table td{}
     .seat h2{font-size:14px;width:84%;float:left;}
     .seat ul{margin:0;padding:0;list-style:none;display:inline-block;width:40%}
    .seat ul li{display:inline-block;margin:5px 5px 5px 0;}
    </style>



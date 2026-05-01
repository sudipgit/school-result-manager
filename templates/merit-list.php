<?php
if (!current_user_can('edit_posts')) exit;

global $wpdb;

$classes_table  = $wpdb->prefix . 'srm_classes';

$active_session = get_option('srm_active_session');
$active_exam_id = (int) get_option('srm_active_exam');

/* ================= FETCH CLASSES ================= */
$classes  = $wpdb->get_results("SELECT * FROM $classes_table ORDER BY class_no");
$class_no = intval($_GET['class'] ?? 0);



/* ==============  === FETCH DATA ================= */
if ($class_no) {
 
    $students = srm_get_students($active_session, $class_no);
    $marks_raw    = srm_get_marks($active_exam_id, $active_session, $class_no);
    $marks =  srm_get_formated_marks($marks_raw); // all formated mark with no sorting current roll
    $merit = srm_calculate_merit_position($marks); // array with all data sorted by merit position
    $position = srm_merit_possition_rolls($merit);
   
}   

?>

<div class="wrap">
<h1>Tabulation Sheet</h1>

<form method="get">
    <input type="hidden" name="page" value="srm_merit">
    <select name="class" required>
        <option value="">Select Class</option>
        <?php foreach ($classes as $c): ?>
            <option value="<?= $c->class_no ?>" <?= $class_no==$c->class_no?'selected':'' ?>>
                <?= esc_html($c->name) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button class="button button-primary">Generate</button>
</form>

<?php if ($class_no && $merit): ?>


<hr>
<table class="wp-list-table widefat striped">
<thead>
<tr>
    <th rowspan="2">SL</th>
    <th rowspan="2">Roll</th>
    <th rowspan="2">Name</th>
    <th colspan="3">Bangla 1</th>
    <th colspan="3">Bangla 2</th>
    <th colspan="2">Bangla </th>
    <th colspan="0">Eng 1</th> 
    <th colspan="0">Engl 2</th> 
    <th colspan="2">English</th> 
    <th colspan="4">Math</th>
    <th colspan="4">Science</th>
    <th colspan="4">BGS</th>
    <th colspan="4">Religion</th>
    <th colspan="4">ICT</th>
    </tr>

<tr><th>CQ</th><th>MCQ</th><th>Total</th><!--Bangla 1 -->
<th>CQ</th><th>MCQ</th><th>Total</th><!--Bangla 2-->
<th>Total</th><th>GP</th><!--Bangla -->
<th>Total</th><!--English 1-->
<th>Total</th><!--EN 2 -->
<th>Total</th><th>GP</th><!--EN-->
<<th>CQ</th><th>MCQ</th><th>Total</th><th>GP</th><!--math-->
<th>CQ</th><th>MCQ</th><th>Total</th><th>GP</th><!--science-->
<th>CQ</th><th>MCQ</th><th>Total</th><th>GP</th><!--/BGS-->
<th>CQ</th><th>MCQ</th><th>Total</th><th>GP</th><!--Religion-->
<th>CQ</th><th>PRA.</th><th>Total</th><th>GP</th><tr><!--ICT-->

</thead>

<tbody>
<?php foreach ($merit as $i => $marks): 
   
    $roll = $marks['roll'];
    $s=$students[$roll];

    $ban1 = $marks[1]?? null;
    $ban2 = $marks[2]?? null;
    $eng1 = $marks[3]?? null;
    $eng2 = $marks[4]?? null;
    $mat  = $marks[5]?? null;
    $sci  = $marks[6]?? null;
    $bgs  = $marks[7]?? null;
    $rel  = $marks[8]?? null;
    if($s->religion=='Hindu'){
        $rel = $marks[9]?? null;
    }
   
    $ict = $marks[10]?? null;
    
    ?>
    <tr>
        <td><?= esc_html($i+1) ?></td>
        <td><?= esc_html($roll) ?></td>
        <td><?= esc_html($s->name) ?></td>
        <td><?= $ban1['cq']?? 0; ?></td><td><?= $ban1['mcq']?? 0 ;?></td><td><?= $ban1['total']?? 0 ; ?></td> <!-- Ban1  -->
        <td><?= $ban2['cq']?? 0; ?></td><td><?= $ban2['mcq']?? 0 ;?></td><td ><?= $ban2['total']?? 0 ; ?></td> <!-- Ban2  -->
        <td <?php if(isset($ban2['fail']) && $ban2['fail']=='yes'){echo 'style="color:red"';};?>><?= $ban2['pair_total']?? 0; ?></td><td><?= number_format($ban1['pair_gp']?? 0,2) ;?></td> <!-- Ban  -->
        <td><?= $eng1['total']?? 0 ; ?></td> <!-- Eng1  -->
        <td><?= $eng2['total']?? 0 ; ?></td> <!-- Eng2  -->
        <td <?php if(isset($eng2['fail']) &&  $eng2['fail']=='yes'){echo 'style="color:red"';};?>><?= $eng2['pair_total']?? 0; ?></td><td><?= number_format($eng2['pair_gp']?? 0,2) ;?></td> <!-- Eng  -->
        <td><?= $mat['cq']?? 0; ?></td><td><?= $mat['mcq']?? 0 ;?></td><td <?php if(isset($mat['fail']) && $mat['fail']=='yes'){echo 'style="color:red"';};?>><?= $mat['total']?? 0 ; ?></td> <td><?= number_format($mat['gp']?? 0,2) ; ?></td><!-- Math  -->
        <td><?= $sci['cq']?? 0; ?></td><td><?= $sci['mcq']?? 0 ;?></td><td <?php if(isset($sci['fail']) && $sci['fail']=='yes'){echo 'style="color:red"';};?>><?= $sci['total']?? 0 ; ?></td> <td><?= number_format($sci['gp']?? 0,2) ; ?></td><!-- Science  -->
        <td><?= $bgs['cq']?? 0; ?></td><td><?= $bgs['mcq']?? 0 ;?></td><td <?php if(isset($bgs['fail']) && $bgs['fail']=='yes'){echo 'style="color:red"';};?>><?= $bgs['total']?? 0 ; ?></td> <td><?= number_format($bgs['gp']?? 0,2) ; ?></td><!-- BSG  -->
        <td><?= $rel['cq']?? 0; ?></td><td><?= $rel['mcq']?? 0 ;?></td><td <?php if(isset($rel['fail']) && $rel['fail']=='yes'){echo 'style="color:red"';};?>><?= $rel['total']?? 0 ; ?></td> <td><?= number_format($rel['gp']?? 0,2) ; ?></td><!-- Religion  -->
        <td><?= $ict['cq']?? 0; ?></td><td><?= $ict['practical']?? 0 ;?></td><td <?php if(isset($ict['fail']) && $ict['fail']=='yes'){echo 'style="color:red"';};?>><?= $ict['total']?? 0 ; ?></td> <td><?= number_format($ict['gp']?? 0,2) ; ?></td><!-- ICT  -->
        <td>
            Total = <?= $marks['grand_total']?? 0; ?>
            GPA = <?= number_format($marks['gpa']?? 0,2); ?>
            F=<?= $marks['fail_count']?? 0; ?>
        </td>
        
    
    





  
    </tr>
<?php endforeach; ?>
</tbody>
</table>

<?php endif; ?>
<style>
    th,td{font-size:9px !important;padding:3px !important;border:1px solid #ccc !important}
</style>
</div>

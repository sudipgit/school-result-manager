<?php
function srm_grade_point($marks, $full) {
    if (!$marks || $marks==0 || $full==0) return 0;
 
    $percent = ($marks / $full) * 100;

    return match (true) {
        $percent >= 80 => 5.00,
        $percent >= 70 => 4.00,
        $percent >= 60 => 3.50,
        $percent >= 50 => 3.00,
        $percent >= 40 => 2.00,
        $percent >= 33 => 1.00,
        default => 0.00
    };
}


function srm_get_marks($exam_id, $session, $class_no){
    global $wpdb;
    $mark_table    = $wpdb->prefix . 'srm_marks';
    $sub_table    = $wpdb->prefix . 'srm_subjects';
    $sql = "SELECT A.*,B.full_marks,B.pass_marks FROM $mark_table A LEFT JOIN $sub_table B ON A.subject_id=B.id  WHERE A.exam_id =". $exam_id." AND A.session = ". $session. " AND A.class_no =".$class_no ." ORDER BY A.roll ASC";
    $results = $wpdb->get_results($sql);
   
    $marks=array();
    if(empty($results)){
        return $marks;
    }
    

    foreach($results as $result){    
        $marks[$result->roll][$result->subject_id]=$result;
    }
   
    return $marks;
}

/*

  Output Array
  result[roll]{
  [subject_id]array(cq,mcq,practical) 
  [total]
  [cgpa]
  [total_fail]
  
*/

function srm_get_formated_marks($marks){
    $pairs = array(1,2,3,4);

    foreach($marks as $roll=>$subjects){
        $fail_count  = 0;
        $pair_fail1  = 0;
        $pair_fail2  = 0;
        $grand_total = 0;
        $grand_gp    = 0;
        foreach($subjects as $subject_id => $result){

            $fail          = 'no';
            $pair_total   = 0; // For Pair Subject
            $pair_gp      = 0;  // For Pair
            $is_pair       = false;
            $gp            = 0;
            $grand_total = $grand_total + $result->total;
            $without_practical = $result->cq + $result->mcq;
            $subject_id = $result->subject_id;

            if(in_array($subject_id,$pairs)){
                if($subject_id == 1 || $subject_id == 2){
                    $ban1 = $subjects[1];
                    $ban2 = $subjects[2];
                    $pair_total = $ban1->total + $ban2->total;
                    $full        = $ban1->full_marks + $ban2->full_marks;
                    $full_pass   = $ban1->pass_marks + $ban2->pass_marks;
                    $is_pair     = true;
                    $pair_gp    = srm_grade_point($pair_total,$full);

                    if($full_pass > $pair_total){
                        $fail = 'yes';
                        $pair_fail1 = 1;
                    }
                   
                }

                if($subject_id == 3 || $subject_id == 4){
                    $sub1 = $subjects[3];
                    $sub2 = $subjects[4];
                    $pair_total = $sub1->total + $sub2->total;
                    $full        = $sub1->full_marks + $sub2->full_marks;
                    $full_pass   = $sub1->pass_marks + $sub2->pass_marks;
                    $is_pair     = true;
                    $pair_gp    = srm_grade_point($pair_total,$full);

                    if($full_pass > $pair_total){
                        $fail = 'yes';
                        $pair_fail2 = 1;
                    }
                   
                }

                if($subject_id == 2 || $subject_id == 4){
                   $grand_gp = $grand_gp + $pair_gp;
                }
            

            }else{
                if($result->pass_marks > $without_practical ){
                    $fail = 'yes';
                    $fail_count++;
                   
                }else{
                    $gp = srm_grade_point($result->total,$result->full_marks);
                    $grand_gp = $grand_gp + $gp;
                }

            }
        
        
            $m = array(
                'cq'          => $result->cq,
                'mcq'         => $result->mcq,
                'practical'   => $result->practical,
                'total'       => $result->total,
                'fail'        => $fail,
                'gp'          => $gp,
                'pair_total' => $pair_total,
                'pair_gp'    => $pair_gp,
                'is_pair'     => $is_pair

            );
                
            $marks[$result->roll][$result->subject_id]=$m;
        }

        

        $fail_count = $fail_count + $pair_fail1 + $pair_fail2;
        $gpa = $grand_gp / 7;
        if($fail_count>0){
            $gpa = 0.00;
        }

        $marks[$result->roll]['fail_count'] = $fail_count;
        $marks[$result->roll]['grand_total'] = $grand_total;
        $marks[$result->roll]['gpa'] = $gpa;
       
    }

    return $marks;
}


function srm_get_students($session, $class_no){
    global $wpdb;
    $table    = $wpdb->prefix . 'srm_students';
    $sql = "SELECT * FROM $table WHERE session = ". $session. " AND class_no =".$class_no ." ORDER BY roll ASC";
    $results = $wpdb->get_results($sql);
    foreach($results as $result){
        $all[$result->roll]=$result;
    }
    return $all;
}

function srm_get_subjects(){
    global $wpdb;
    $table    = $wpdb->prefix . 'srm_subjects';
    $sql = "SELECT * FROM $table";
    $results = $wpdb->get_results($sql);
    
    $subjects = array();
    foreach($results as $result){
        $subjects[$result->id] = $result;
    }

    return $subjects;
}

/*
  Input formated marks

  index 0 pass, 1 one subject fail, 2 two subjects fail ...

*/

function srm_calculate_merit_position($marks){
    $results = array();
    $merit=array();
    foreach($marks as $roll=>$mark){
       $mark['roll'] = $roll; 
       $results[$mark['fail_count']][]=$mark;
    }
    ksort($results);
   foreach($results as $result){
      usort($result, function ($a, $b) {
        return $b['grand_total'] <=> $a['grand_total'];
     });

     foreach($result as $r){
       $merit[]=$r;
     }
     
   }
   return $merit;
}

function srm_merit_possition_rolls($merit){
    $result = array();
    foreach($merit as $index=>$m){
        $result[$m['roll']]=$index;
     }
     return $result;
}

function srm_get_section_title($id){
    global $wpdb;
    $table    = $wpdb->prefix . 'srm_sections';

    $row = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id)
    );

    if ($row) {
        return  $row->name;
        
    }

   
}
function srm_get_subject_title($id){
    global $wpdb;
    $table    = $wpdb->prefix . 'srm_subjects';
    $row = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id)
    );

    if ($row) {
        return  $row->name;
        
    }
}


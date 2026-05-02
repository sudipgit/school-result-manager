<?php
function srm_get_all_classes(){
    global $wpdb;
    $table = $wpdb->prefix . 'srm_classes';
    $results = $wpdb->get_results(
        "SELECT * FROM $table"
    );
   $list=array();
    if($results){
        foreach($results as $result){
           $list[$result->class_no]=$result;
        }
    }
    return $list;
}

function srm_get_all_sections(){
    global $wpdb;
    $table = $wpdb->prefix . 'srm_sections';
    $results = $wpdb->get_results(
        "SELECT * FROM $table"
    );
   $list=array();
    if($results){
        foreach($results as $result){
           $list[$result->id]=$result;
        }
    }
    return $list;
}

function srm_get_all_subjects(){
    global $wpdb;
    $table = $wpdb->prefix . 'srm_subjects';
    $results = $wpdb->get_results(
        "SELECT * FROM $table"
    );
    $list=array();
    if($results){
        foreach($results as $result){
           $list[$result->id]=$result;
        }
    }
    return $list;
}

function srm_get_assigned_classes($teacher_id,$active_session,$active_exam){
    global $wpdb;
    $table = $wpdb->prefix . 'srm_subject_assignments';
    //$sql = "SELECT * FROM $table WHERE teacher_id=".$teacher_id." AND session='".$active_session."' AND exam_id=".$active_exam." ORDER BY class_no ASC";
   // var_dump($sql);exit;
    $results = $wpdb->get_results(
        "SELECT * FROM $table WHERE teacher_id=".$teacher_id." AND session='".$active_session."' AND exam_id=".$active_exam." ORDER BY class_no ASC"
    );
   
    return $results;
}

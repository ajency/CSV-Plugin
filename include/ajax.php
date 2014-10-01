<?php
/*
 * Custom ajax functions of the plugin
 * 
 */

/*
 * function ro check progress of a csv file being processed
 */
function ajax_csv_check_progress(){
    global $aj_csvimport;
    
    $csv_id = $_POST['csv_id'];
    
    $csv_progress = $aj_csvimport->csv_check_progress($csv_id);
    
    $totalparts = count($csv_progress['notstarted']) + count($csv_progress['processing']) + count($csv_progress['completed']);
    $totalcompleted = count($csv_progress['completed']);
    
    if($totalparts == 0){
        wp_die( json_encode( array( 'code'=>'ERROR','totalparts' => $totalparts, 'totalcompleted' => $totalcompleted) ) );
    }
    
    wp_die( json_encode( array( 'code'=>'OK','totalparts' => $totalparts, 'totalcompleted' => $totalcompleted,'log_paths' => $csv_progress['log_paths']) ) );
    
}
add_action('wp_ajax_ajci_csv_check_progress','ajax_csv_check_progress');
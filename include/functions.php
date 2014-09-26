<?php
/*
 * Custom general functions of plugin
 * 
 */

 /* 
 * function to display preview of a valid csv file
 * @param string $component_name 
 * @param array $validated_response of the csv file
 * 
 * @return string $output; 
 */
function ajci_display_csv_preview($component_name = '',$validated_response = array()){
    global $ajci_components;
    $output ='';
    $output .='<p>Total Records In CSV:'.$validated_response['row_count'].'</p>';
    
    $output .='<p>CSV data Preview:</p>';
    $output .= '<form method="post">';
    $output .= '<table border="1">';
    $output .= '<tr><td></td>';
    foreach($ajci_components[$component_name]['headers'] as $label){
        $output .= '<th>'.$label.'</th>';
    }
    
    $flag = 0;
    foreach($validated_response['preview_rows'] as $row){
        $output .= '<tr>'; 
        if($flag == 0){
            $output .= '<td><input type="checkbox" name="csv_header" id="csv_header" /></td>';
            $flag=1;
        }else{
           $output .= '<td></td>';  
        }
        foreach ($row as $col){
             $output .= '<td>'.$col.'</td>';
        }
         $output .= '</tr>';
    }
    
    $output .= '</tr>';
    $output .= '</table>';
    
    $output .= '<br/>';
    $output .= '<p>
                Check The Check Box at the first row of the Preview if it is the Header Row.
                </p>
                <input type="hidden" name="uniquename" id="uniquename" value="'.$validated_response['files']['uniquename'].'" />
                <input type="hidden" name="realname" id="realname" value="'.$validated_response['files']['realname'].'" />
                <input type="hidden" name="import_step" id="import_step" value="2" />
                <input type="hidden" name="csv_component" id="csv_component" value="users" />
                <input type="submit"
                name="submit"
                value="Confirm Import" /> 
                </form>';
    return $output;
}

/*
 * function to setup the split csv async tasks
 */
function setup_ajci_async_task(){
     new AJCI_Splitcsv_Async_Task(1);
}
add_action('wp_loaded', 'setup_ajci_async_task',10);

/*
 * funtion to split the csv file using the global obj method async
 * @param int $id csv file master id
 * 
 */
function async_ajci_split_csv($csv_id){
    global $aj_csvimport;
    $aj_csvimport->create_csvfile_parts($csv_id);
}
//add_action('wp_async_nopriv_ajci_split_csv', 'async_ajci_split_csv', 100,1);
add_action('wp_async_ajci_split_csv', 'async_ajci_split_csv', 100,1);

/*
 * funtion to process a part file 
 * @param int $csv_id csv file master record id
 * @param int $part_id csv part file id using the global obj method async
 * 
 */
function async_ajci_csv_process_part($csv_id,$part_id){
    global $aj_csvimport;
    $aj_csvimport->csv_async_process_part($csv_id,$part_id);
}
add_action('wp_async_ajci_csv_process_part', 'async_ajci_csv_process_part', 100,2);

/*
 * funtion to cleanup csv files created during import process using the global obj method async
 * @param int $csv_id csv file master record id
 * 
 */
function async_ajci_temp_csvs_cleanup($csv_id){
    global $aj_csvimport;
    $aj_csvimport->cleanup_temp_csv_files($csv_id);
}
add_action('wp_async_ajci_temp_csvs_cleanup', 'async_ajci_temp_csvs_cleanup', 100,1);
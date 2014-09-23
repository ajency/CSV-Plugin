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
    $output .='<p>Total Records:'.$validated_response['row_count'].'</p>';
    
    $output .= '<table border="1">';
    $output .= '<tr>';
    foreach($ajci_components[$component_name]['headers'] as $label){
        $output .= '<th>'.$label.'</th>';
    }
    
    foreach($validated_response['preview_rows'] as $row){
        $output .= '<tr>';        
        foreach ($row as $col){
             $output .= '<td>'.$col.'</td>';
        }
         $output .= '</tr>';
    }
    
    $output .= '</tr>';
    $output .= '</table>';
    
    $output .= '<br/><form method="post">';
    $output .= '<input type="hidden" name>';
    $output .= '<form method="post">
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


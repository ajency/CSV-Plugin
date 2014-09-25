<?php
/**
 * Represents the view for the administration tools menu upload interface step-2
 *
 * The User Interface to the end user.
 *
 * @package   csv-import
 * @author    Team Ajency <talktous@ajency.in>
 * @license   GPL-2.0+
 * @link      http://ajency.in
 * @copyright 9-22-2014 Ajency.in
 */
?>
<div class="wrap">
 	<?php screen_icon(); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
        <p>CSV import interface</p>
        <?php 
         global $aj_csvimport;
         
         if (isset($_POST['submit'])){   
             $uniquefilename= $_POST['uniquename'];
             $realfilename= $_POST['realname'];
             $component = $_POST['csv_component'];
             $header_row = (isset($_POST['csv_header']))?true:false;
             
             $csv_insert_record = $aj_csvimport->init_csv_data($uniquefilename,$realfilename,$component,$header_row);
             if($csv_insert_record && !is_wp_error($csv_insert_record)){
                 /*$process_csv_data = $aj_csvimport->csv_process_files($csv_insert_record);
                 $aj_csvimport->mark_csv_processed($csv_insert_record,$component);
                 
                 $logview ='<table>';
                 foreach($process_csv_data as $key => $value){
                     if($key == 'success')
                         $logview .='<tr><td>Success Log</td><td><a href="'.$value.'" target="_blank">View Records</a></td></tr>';
                     if($key == 'error')
                         $logview .='<tr><td>Error Log</td><td><a href="'.$value.'" target="_blank">View Records</a></td></tr>';
                 }
                 $logview .='</table>';
                 
                 echo $logview;*/
                 
            ?>    
        
            <div id="import_csv_data">
                <p>Import process initialized click on the "Import Start" button to start import</p>
                <input type='hidden' name='csv-master-id' id='csv-master-id' value='<?php echo $csv_insert_record?>' />
                <input type='hidden' name='component' id='component' value='<?php echo $component?>' />
                <input type='button' name='import-start' id='import-start' value='Import Start' />
                <div class='processing-status'>
                    
                </div>
            </div>
            
            <?php }
             else{
                echo $csv_insert_record->get_error_message(); 
             }
         }else{
             wp_die('Invalid Request');
         }
        ?>
        
        
        <div id="log_view">
            
        </div>        
</div>
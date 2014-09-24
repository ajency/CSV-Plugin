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
             
             $csv_insert_record = $aj_csvimport->init_csv_data($uniquefilename,$realfilename,$component);
             if($csv_insert_record && !is_wp_error($csv_insert_record)){
                 $process_csv_data = $aj_csvimport->csv_process_files($csv_insert_record);
                 $aj_csvimport->mark_csv_processed($csv_insert_record,$component);
                 
                 $logview ='<table>';
                 foreach($process_csv_data as $key => $value){
                     if($key == 'success')
                         $logview .='<tr><td>Success Log</td><td>'.$value.'</td></tr>';
                     if($key == 'error')
                         $logview .='<tr><td>Error Log</td><td>'.$value.'</td></tr>';
                 }
                 $logview .='</table>';
                 
                 echo $logview;
             }
             else{
                echo $csv_insert_record->get_error_message(); 
             }
         }else{
             wp_die('Invalid Request');
         }
        ?>
        
        
        
</div>
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
             ?>    
        
            <div id="import_csv_data">
                <p>To Start import click on the "Import Start" button</p>
                <input type='hidden' name='csv-master-id' id='csv-master-id' value='<?php echo $csv_insert_record?>' />
                <input type='button' name='import-csv-start' id='import-csv-start' value='Import Start' />
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
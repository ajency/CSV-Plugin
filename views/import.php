<?php
/**
 * Represents the view for the administration tools menu upload interface
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
             if($_POST['import_step'] == 1){
                 $validate_response = $aj_csvimport->csv_validate();
                 //var_dump($validate_response);
                 if($validate_response['success']){
                     echo $aj_csvimport->display_messages($validate_response['msg'],'success');
                     echo ajci_display_csv_preview($_POST['csv_component'],$validate_response);
                 }
                 else{
                    echo $aj_csvimport->display_messages($validate_response['msg'],'error');
                    $aj_csvimport->display_interface(1); 
                 }
             }   
         }  
         else{
             $aj_csvimport->display_interface(1);
         }

        ?>
</div>

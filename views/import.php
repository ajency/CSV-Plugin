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
        <p>CSV upload interface</p>
        <?php 
         global $aj_csvimport;
     
         if (isset($_POST['submit'])){
             if($_POST['import_step'] == 1){
                 $validate_response = $aj_csvimport->csv_validate();
                 var_dump($validate_response);
             }
         }  
         else{
             $aj_csvimport->display_interface(1);
         }

        ?>
</div>

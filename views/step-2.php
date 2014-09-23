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
<?php
//print_r($_POST);
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
             $aj_csvimport->init_csv_data($uniquefilename,$realfilename,$component);
         }
        ?>
</div>
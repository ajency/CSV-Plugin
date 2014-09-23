<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
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
        <p>Plugin Settings</p>
        <?php 
         global $ajci_components;
          //var_dump($ajci_components);      
         if (isset($_POST['submit'])){
             $optionsarray= array();

             $optionsarray['ajci_lines_per_csv'] = $_POST['ajci_lines_per_csv'];
             
             update_option('ajci_plugin_options', $optionsarray);
         }  
         
          $ajcm_plugin_options= get_option('ajci_plugin_options');
        ?>
        
        <form method="post">
            <table>
            <tr>
            <td>
            <label>Lines Per Csv File: 
            </label></td>	
            <td>
            <input type="text"
            name="ajci_lines_per_csv"
            size="30"
            value="<?php echo $ajcm_plugin_options['ajci_lines_per_csv'] ?>" />
            </td>
            </tr>
            </table>
            <input type="submit"
            name="submit"
            value="Save Changes" /> 
        </form>
</div>

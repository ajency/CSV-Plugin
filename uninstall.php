<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   csv-import
 * @author    Team Ajency <talktous@ajency.in>
 * @license   GPL-2.0+
 * @link      http://ajency.in
 * @copyright 9-22-2014 Ajency.in
 */

// If uninstall, not called from WordPress, then exit
if (!defined("WP_UNINSTALL_PLUGIN")) {
	exit;
}

//Define uninstall functionality here

 /**
 * Delete database tables
 */
function delete_plugin_data() {
    global $wpdb;  
        
	$sql = "DROP TABLE ". $wpdb->prefix."ajci_csv";
	$wpdb->query($sql);
        
	$sql = "DROP TABLE ". $wpdb->prefix."ajci_csv_parts";
	$wpdb->query($sql);

}
delete_plugin_data();
<?php
/**
 * Csv Import Plugin
 *
 * A simple Csv Import Plugin for wordpress
 *
 * @package   csv-import
 * @author    Team Ajency <talktous@ajency.in>
 * @license   GPL-2.0+
 * @link      http://ajency.in
 * @copyright 9-22-2014 Ajency.in
 *
 * @wordpress-plugin
 * Plugin Name: CSV Import
 * Plugin URI:  http://ajency.in
 * Description: A simple CSV Import Plugin for wordpress
 * Version:     0.1.0
 * Author:      Team Ajency
 * Author URI:  http://ajency.in
 * Text Domain: csv-import-locale
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if (!defined("WPINC")) {
	die;
}

// include the register components functions file
require_once( plugin_dir_path( __FILE__ ) . '/include/register_components.php');

// include the custom plugin functions file
require_once( plugin_dir_path( __FILE__ ) . '/include/functions.php');

// include the Coseva CSV parser file
require_once( plugin_dir_path( __FILE__ ) . '/src/Coseva/CSV.php');

require_once(plugin_dir_path(__FILE__) . "CsvImport.php");

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook(__FILE__, array("CsvImport", "activate"));
register_deactivation_hook(__FILE__, array("CsvImport", "deactivate"));

function aj_csvimport() {
	return CsvImport::get_instance();
}

// add the communication module instance to globals
$GLOBALS['aj_csvimport'] = aj_csvimport();

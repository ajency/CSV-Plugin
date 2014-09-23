<?php
/**
 * Csv Import Plugin
 *
 * @package   csv-import
 * @author    Team Ajency <wordpress@ajency.in>
 * @license   GPL-2.0+
 * @link      http://ajency.in
 * @copyright 9-22-2014 Ajency.in
 */

/**
 * Csv Import class.
 *
 * @package CsvImport
 * @author  Team Ajency <wordpress@ajency.in>
 */
class CsvImport{
	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   0.1.0
	 *
	 * @var     string
	 */
	protected $version = "0.1.0";

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    0.1.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = "csv-import";

	/**
	 * Instance of this class.
	 *
	 * @since    0.1.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    0.1.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = '';

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     0.1.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action("init", array($this, "load_plugin_textdomain"));

		// add plugin tables to $wpdb inorder to access tables in format ie $wpdb->tablename
		add_action("after_setup_theme", array($this, "add_plugin_tables_to_wpdb"));
                
		// Add the options page and menu item.
		add_action("admin_menu", array($this, "add_plugin_admin_menu"));
                
		// Add the csv import interface in settings.
		add_action("admin_menu", array($this, "add_import_interface_menu"));                

		// Load admin style sheet and JavaScript.
		add_action("admin_enqueue_scripts", array($this, "enqueue_admin_styles"));
		add_action("admin_enqueue_scripts", array($this, "enqueue_admin_scripts"));

		// Load public-facing style sheet and JavaScript.
		add_action("wp_enqueue_scripts", array($this, "enqueue_styles"));
		add_action("wp_enqueue_scripts", array($this, "enqueue_scripts"));

                // hook function to register plugin defined and theme defined CSV components
                add_action("init", array($this, "register_components"));
                
		// Define custom functionality. Read more about actions and filters: http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		add_action("TODO", array($this, "action_method_name"));
		add_filter("TODO", array($this, "filter_method_name"));
                
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.1.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn"t been set, set it now.
		if (null == self::$instance) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    0.1.0
	 *
	 * @param    boolean $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate($network_wide) {
        
                global $wpdb;
            
                //create tables logic on plugin activation
                $csv_tbl=$wpdb->prefix."ajci_csv";
                $csv_tbl_sql="CREATE TABLE `{$csv_tbl}` (
                               `id` int(11) NOT NULL primary key AUTO_INCREMENT,           
                               `component` varchar(75) NOT NULL,
                               `attachment_id` int(11) DEFAULT '0',
                               `status` varchar(25) NOT NULL,
                               `uploaded_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
                                );";

                $csv_parts_tbl=$wpdb->prefix."ajci_csv_parts";            
                $csv_parts_tbl_sql="CREATE TABLE `{$csv_parts_tbl}` (
                                `id` int(11) NOT NULL primary key AUTO_INCREMENT,
                                `csv_id` int(11) DEFAULT NULL,
                                `attachment_id` int(11) DEFAULT '0',
                                `status` varchar(25) NOT NULL
                                 );";   

                //reference to upgrade.php file
                //uses WP dbDelta function inorder to handle addition of new table columns 
                require_once(ABSPATH.'wp-admin/includes/upgrade.php');
                dbDelta($csv_tbl_sql);
                dbDelta($csv_parts_tbl_sql);
                
             $optionsarray= array();
             $optionsarray['ajci_lines_per_csv'] = 10;
             
             update_option('ajci_plugin_options', $optionsarray);

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    0.1.0
	 *
	 * @param    boolean $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate($network_wide) {
		// TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.1.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters("plugin_locale", get_locale(), $domain);

		load_textdomain($domain, WP_LANG_DIR . "/" . $domain . "/" . $domain . "-" . $locale . ".mo");
		load_plugin_textdomain($domain, false, dirname(plugin_basename(__FILE__)) . "/lang/");
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     0.1.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if (!isset($this->plugin_screen_hook_suffix)) {
			return;
		}

		$screen = get_current_screen();
		if ($screen->id == $this->plugin_screen_hook_suffix) {
			wp_enqueue_style($this->plugin_slug . "-admin-styles", plugins_url("css/admin.css", __FILE__), array(),
				$this->version);
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     0.1.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if (!isset($this->plugin_screen_hook_suffix)) {
			return;
		}

		$screen = get_current_screen();
		if ($screen->id == $this->plugin_screen_hook_suffix) {
			wp_enqueue_script($this->plugin_slug . "-admin-script", plugins_url("js/csv-import-admin.js", __FILE__),
				array("jquery"), $this->version);
		}

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style($this->plugin_slug . "-plugin-styles", plugins_url("css/public.css", __FILE__), array(),
			$this->version);
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script($this->plugin_slug . "-plugin-script", plugins_url("js/public.js", __FILE__), array("jquery"),
			$this->version);
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    0.1.0
	 */
	public function add_plugin_admin_menu() {
		$this->plugin_screen_hook_suffix = add_plugins_page(__("CSV Import - Administration", $this->plugin_slug),
			__("CSV Import", $this->plugin_slug), "read", $this->plugin_slug, array($this, "display_plugin_admin_page"));
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    0.1.0
	 */
	public function display_plugin_admin_page() {
		include_once("views/admin.php");
	}

	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        WordPress Actions: http://codex.wordpress.org/Plugin_API#Actions
	 *        Action Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    0.1.0
	 */
	public function action_method_name() {
		// TODO: Define your action hook callback here
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        WordPress Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Filter Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    0.1.0
	 */
	public function filter_method_name() {
		// TODO: Define your filter hook callback here
	}
        
        
	/*
	 * function to add plugin table names to global $wpdb
	 */
	public function add_plugin_tables_to_wpdb(){
		global $wpdb;
		
		if (!isset($wpdb->ajci_csv)) {
			$wpdb->ajci_csv = $wpdb->prefix . 'ajci_csv';
		}
		if (!isset($wpdb->ajci_csv_parts)) {
			$wpdb->ajci_csv_parts = $wpdb->prefix . 'ajci_csv_parts';
		}    
		
	}

        public function add_import_interface_menu(){
            	add_management_page(
                        'CSV Import Data', // Page <title>
                        'CSV Import Data', // Menu title
                        7, // What level of user
                        __FILE__, //File to open
                        array($this, "display_upload_interface_page") //Function to call
                        );
        }
        
        public function display_upload_interface_page(){
            include_once("views/import.php");
        }
        
        public function display_interface($step = 1){
            // switch case as to select the import step page
            switch ($step) {
                case 1:
                    include_once("views/step-1.php");
                 break;
                    include_once("views/step-2.php");
                case 2:
                 break;
                default:
                 break;
            }
        }
        
        public function csv_validate(){
            global $ajci_components;
            
            if(! $this->is_registered_component_type($_POST['csv_component'])){
                $validate_status = array('success'=>false,'msg'=>'csv component not registered');
                return $validate_status;              
            }
            
            $csv_json = $this->parseCSV($_FILES['csv_file']['tmp_name']);
            
            $csvData = json_decode($csv_json);
            
            if($student_csv_headers !== $csvData[0] ){
                $validate_status = array('success'=>false,'msg'=>'Headers Labels incorrect.');
                return $validate_status;
            }
            
            $i=1;
            while ($i <= count($csvData)-1 ) {
                if( count($csvData[$i]) !== count($ajci_components[$_POST['csv_component']]['headers'])){
                    $validate_status = array('success'=>false,'msg'=>'Rows columns incoorect count.');
                    return $validate_status;
                }
            }
            
            $validate_status = array('success'=>true,'row_count'=>count($csvData)-1);
            
            return $validate_status;
        }
        
        function parseCSV($filepath) {
            // read the csv file
            $csv = new Coseva\CSV($filepath);
            // parse the csv
            $csv->parse();
            //Convert parsed csv data to a json string
            $csvJson = $csv->toJSON();

            return $csvJson;
        }
        
        /*
         * function to register the csv components and their headers
         */       
        public function register_components(){
            $component_name = 'users';
            $component_headers = array('USERNAME',
                                       'FIRST_NAME',
                                       'LAST_NAME',
                                       'ROLL_NO',
                                       'BLOG_ID',
                                       'EMAIL_ID',
                                       'DIVISION',
                                       'DIVISION_ID',
                                       'PARENT_EMAIL_ID_1',
                                       'PARENT_MOBILE_1',
                                       'PARENT_EMAIL_ID_2',
                                       'PARENT_MOBILE_2');
            register_csv_component($component_name,$component_headers,$call_back);
        }
        
         /*
         * Check if a CSV component is registered in theme/plugin code
         * @param string $component
         * 
         * return bool true if component is registerd 
         */
        public function is_registered_component_type($component){
            global $ajci_components;
            
            if(is_null($ajci_components)){
                    return false;
            }
          
            if(!array_key_exists($component, $ajci_components))
                    return false;
            
            return true;
        }
        
}
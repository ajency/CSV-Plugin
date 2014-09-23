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
                // custom added
		add_action("after_setup_theme", array($this, "add_plugin_tables_to_wpdb"));
                
		// Add the options page and menu item.
                // custom added
		add_action("admin_menu", array($this, "add_plugin_admin_menu"));
                
		// Add the csv import interface in settings.
                // custom added
		add_action("admin_menu", array($this, "add_import_interface_menu"));                

		// Load admin style sheet and JavaScript.
		add_action("admin_enqueue_scripts", array($this, "enqueue_admin_styles"));
		add_action("admin_enqueue_scripts", array($this, "enqueue_admin_scripts"));

		// Load public-facing style sheet and JavaScript.
		add_action("wp_enqueue_scripts", array($this, "enqueue_styles"));
		add_action("wp_enqueue_scripts", array($this, "enqueue_scripts"));

                // hook function to register plugin defined and theme defined CSV components
                // custom added
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
         * custom code logic for table creation on plugin activation
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
                               `real_filename` varchar(255) NOT NULL,
                               `filename` varchar(255) NOT NULL,
                               `status` varchar(25) NOT NULL,
                               `uploaded_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
                                );";

                $csv_parts_tbl=$wpdb->prefix."ajci_csv_parts";            
                $csv_parts_tbl_sql="CREATE TABLE `{$csv_parts_tbl}` (
                                `id` int(11) NOT NULL primary key AUTO_INCREMENT,
                                `csv_id` int(11) DEFAULT NULL,
                                `filename` varchar(255) NOT NULL,
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
         * custom added function
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

        /*
         * function to add import interface menu in admin dashboard under Tools menu
         * custom added function
         * 
         * @since    0.1.0
         */
        public function add_import_interface_menu(){
            	add_management_page(
                        'CSV Import Data', // Page <title>
                        'CSV Import Data', // Menu title
                        7, // What level of user
                        __FILE__, //File to open
                        array($this, "display_upload_interface_page") //Function to call
                        );
        }
        
        /*
         * function to display the csv import interface page
         * custom added function
         * 
         * @since    0.1.0
         */
        public function display_upload_interface_page(){
            if($_POST['import_step'] == 2){
                include_once("views/step-2.php"); 
            }
            else{
                include_once("views/import.php");
            }
        }
        
  
        /*
         * function to display csv import interface screen based on the step
         * custom added function
         * 
         * @param int $step
         * 
         * @since    0.1.0
         */
        public function display_interface($step = 1){
            // switch case as to select the import step page
            switch ($step) {
                case 1:
                    include_once("views/step-1.php");
                 break;
                case 2:
                    include_once("views/step-2.php");
                 break;
                default:
                 break;
            }
        }

        /*
         * function to validate the CSV file to be imported
         * custom added function
         * 
         * @since    0.1.0
         */        
        public function csv_validate(){
            global $ajci_components;
            
            if(! $this->is_registered_component_type($_POST['csv_component'])){
                $validate_status = array('success'=>false,'msg'=>'csv component not registered');
                return $validate_status;              
            }
            
            if(! $this->is_valid_file($_FILES['csv_file'])){
                $validate_status = array('success'=>false,'msg'=>'uploaded file is invalid');
                return $validate_status;                  
            }
            
            $csv_json = $this->parseCSV($_FILES['csv_file']['tmp_name']);
            
            $csvData = json_decode($csv_json);
            
            if($ajci_components[$_POST['csv_component']]['headers'] !== $csvData[0] ){
                $validate_status = array('success'=>false,'msg'=>'Headers Labels incorrect.');
                return $validate_status;
            }
            
            $i=1;
            $preview_rows = array();
            while ($i <= count($csvData)-1 ) {
                if( count($csvData[$i]) !== count($ajci_components[$_POST['csv_component']]['headers'])){
                    $validate_status = array('success'=>false,'msg'=>'Rows columns incorrect count.');
                    return $validate_status;
                }
                if($i <= 20){
                   $preview_rows[] = $csvData[$i]; 
                }
                $i++;
            }
            
            //TODO move upload functionality to separate function
            $uploads_dir = wp_upload_dir();
            $upload_directory = $uploads_dir['basedir'];
            
            if(!file_exists($upload_directory.'/ajci_tmp/'))
                mkdir($upload_directory.'/ajci_tmp',0755);
            
            $csvFileUniqueName = time().'_'.$_FILES['csv_file']['name'];
            $csvFile = $upload_directory.'/ajci_tmp/'.$csvFileUniqueName; // csv to save filepath
            move_uploaded_file($_FILES['csv_file']['tmp_name'], $csvFile);
            
            $file_info = array('realname'=>$_FILES['csv_file']['name'],'uniquename'=>$csvFileUniqueName);

            $validate_status = array('success'=>true,
                                     'msg'=>'File validated',
                                     'row_count'=>count($csvData)-1,
                                     'preview_rows'=>$preview_rows,
                                     'files' => $file_info);
            
            return $validate_status;
        }
 
        /*
         * function to validate an uploaded file type
         * custom added function
         * 
         * @param array $file
         * 
         * @return bool true|false
         * 
         * @since    0.1.0
         */    
        public function is_valid_file($file){
            $allowedExts = array("csv");
            $temp = explode(".", $file["name"]);
            $extension = end($temp);
            
            if($file["error"] > 0 || (($file["type"] != "text/comma-separated-values" || $file["type"] != "text/csv" || $file["type"] != "application/vnd.ms-excel") 
                    && !in_array($extension, $allowedExts))){
                return false;
            }

            return true;
        }
        
        /*
         * function to parse a csv file
         * custom added function
         * 
         * @param string $filepath
         * 
         * @return string $csvJson json encoded string
         * 
         * @since    0.1.0
         */        
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
         * custom added function
         * 
         * @since    0.1.0
         * 
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
            register_csv_component($component_name,$component_headers);
        }
        
        /*
        * Check if a CSV component is registered in theme/plugin code
        * custom added function
        * 
        * @param string $component
        * 
        * return bool true if component is registerd 
        * 
        * @since    0.1.0
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
        
        /*
         * function to display status message on the csv import iterface
         * custom added function
         */
        public function display_messages($msg,$type){
            $msg = '<p class="'.$type.'">'.$msg.'</p>';
            return $msg;
        }

         /*
          * function to add csv file record to the ajci_csv table
          * custom added function
          * 
          * @param array $args {
          *     An array of arguments.
          *     @type int $id.
          *     @type string $component(csv registered component) 
          *     @type string $real_filename actual filename at upload
          *     @type string $filename unique filename after upload
          *     @type string $status status label(initalized|completed)
          *     @type datetime $uploaded_on
          *     }
          * 
          * @return int $csv_id record id
          * 
          */       
       public function add_csvfile_master($args = ''){
           global $wpdb;
           
           $defaults = array(
                    'id'                  => false,
                    'component'           => '',    
                    'real_filename'       => '',                  
                    'filename'            => '',    
                    'status'              => 'initialized',
                    'uploaded_on'         => current_time( 'mysql', true )
            );
            $params = wp_parse_args( $args, $defaults );
            extract( $params, EXTR_SKIP );
            
            // add a new csv record in master when $id is false.
            if(!$id){
                $q = $wpdb->insert( $wpdb->ajci_csv, array(
                                                                    'component'     => $component,
                                                                    'real_filename' => $real_filename,
                                                                    'filename'      => $filename,
                                                                    'status'        => $status,
                                                                    'uploaded_on'   =>$uploaded_on
                                                                     ));

                        if ( false === $q )
                            return new WP_Error('csv_master_insert_failed', __('Insert CSV master record Failed.') );
                        
                $csv_id = $wpdb->insert_id;
                    
                return $csv_id;
            }
            else{
                //TODO handle update code logic
            }
       } 

        /*
         * function to display status message on the csv import iterface
         * custom added function
         * 
         * @param array $args {
         *     An array of arguments.
         *     @type int $id.
         *     @type int $csv_id master record id
         *     @type string $filename part filename
         *     @type string $status status label(initalized|completed)
         *     }
         * 
         * @return int $csv_parts_id
         */       
        public function add_csvfile_parts($args = ''){
           global $wpdb;
           
           $defaults = array(
                    'id'                  => false,
                    'csv_id'              => 0,                   
                    'filename'            => '',    
                    'status'              => 'initialized'
            );
            $params = wp_parse_args( $args, $defaults );
            extract( $params, EXTR_SKIP );
            
            // add a new csv record in csv parts when $id is false.
            if(!$id){
                $q = $wpdb->insert( $wpdb->ajci_csv_parts, array(
                                                                    'csv_id'     => $csv_id,
                                                                    'filename'      => $filename,
                                                                    'status'        => $status
                                                                     ));

                        if ( false === $q )
                            return new WP_Error('csv_parts_insert_failed', __('Insert CSV parts record Failed.') );
                        
                $csv_parts_id = $wpdb->insert_id;
                    
                return $csv_parts_id;
            }
            else{
                //TODO handle update code logic
            }
       }            
        
        
        /*
         * function to import the csv data
         * 
         * @param string $uniquefilename saved file name 
         * @param string $realfilename actual file name 
         * @param string $component csv component name
         *  
         */
        public function init_csv_data($uniquefilename,$realfilename,$component){
            $uploads_dir = wp_upload_dir();
            $upload_directory = $uploads_dir['basedir'];
            $filename = $upload_directory.'/ajci_tmp/'.$uniquefilename;
            
            if(file_exists($filename)){
                $args = array('component'     => $component,
                              'real_filename' => $realfilename,
                              'filename'      => $uniquefilename
                             );
                $id = $this->add_csvfile_master($args);
                $sub_files = $this->create_csvfile_parts($id,$uniquefilename);
                foreach($sub_files as $part){
                    $args = array(
                                'csv_id'   => $id,
                                'filename' => $part
                                );
                    $this->add_csvfile_parts($args);
                }
                
            }
            
            return $id;
        }  
       
        /*
         * function to break the master csv file into parts of smaller files
         * custom added function
         * 
         * @param int $id of the master record
         * @param string $uniquefilename filename master csv
         * 
         * @return array $fileparts created smaller files
         */      
       public function create_csvfile_parts($id,$uniquefilename){
           global $ajci_components;
           $fileparts = array();
           $ajci_plugin_options = get_option('ajci_plugin_options');
           
           $uploads_dir = wp_upload_dir();
           $upload_directory = $uploads_dir['basedir'];
           $filename = $upload_directory.'/ajci_tmp/'.$uniquefilename;
           
           $csv_json = $this->parseCSV($filename);
           $csvData = json_decode($csv_json);
           
           $lines_per_part = $ajci_plugin_options['ajci_lines_per_csv'];
           
           $mod = (count($csvData)-1)%$lines_per_part;
           $file_parts_count = ((count($csvData)-1)- $mod)/$lines_per_part;
           
           if($mod > 0)
            $file_parts_count = $file_parts_count+1;
           
           for($filecount=1;$filecount<=$file_parts_count;$filecount++){
               $offset = ($filecount-1)*$lines_per_part;
               $fileparts[] = 'part'.$filecount.'_'.$uniquefilename;
               $file_part_name =  $upload_directory.'/ajci_tmp/part'.$filecount.'_'.$uniquefilename;
               $file = fopen($file_part_name,"w");
               fputcsv($file,$csvData[0]);
               $i = $offset + 1;
               $limit = $offset + $lines_per_part;
               while($i <= $limit){
                   if(isset($csvData[$i]))
                    fputcsv($file,$csvData[$i]);
                   
                   $i++;
               }
               fclose($file);
           }
           
           return $fileparts;
       }
}
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
         * Slug of the plug tools menu screen.
         * 
	 * @since    0.1.0
	 *
	 * @var      string
         */
        protected $plugin_tools_screen_hook_suffix = '';
        
        /**
         * Configurable number of lines per part.
         * 
         */
        protected static $plugin_num_lines_part = 500;

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
                $csv_tbl_sql="CREATE TABLE IF NOT EXISTS `{$csv_tbl}` (
                               `id` int(11) NOT NULL primary key AUTO_INCREMENT,           
                               `component` varchar(75) NOT NULL,
                               `real_filename` varchar(255) NOT NULL,
                               `filename` varchar(255) NOT NULL,
                               `status` varchar(25) NOT NULL,
                               `uploaded_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                               `meta` longtext
                                );";

                $csv_parts_tbl=$wpdb->prefix."ajci_csv_parts";            
                $csv_parts_tbl_sql="CREATE TABLE IF NOT EXISTS `{$csv_parts_tbl}` (
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
             $optionsarray['ajci_lines_per_csv'] = self::$plugin_num_lines_part;
             
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
		if ($screen->id == $this->plugin_screen_hook_suffix || $screen->id == $this->plugin_tools_screen_hook_suffix ) {
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
		wp_enqueue_script($this->plugin_slug . "-plugin-script", plugins_url("js/csv-import.js", __FILE__), array("jquery"),
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
         * 
         * @since    0.1.0
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
            	$this->plugin_tools_screen_hook_suffix =add_management_page(
                        'CSV Import Data', // Page <title>
                        'CSV Import Data', // Menu title
                        'manage_options', // What level of user
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
            if(isset($_POST['import_step'])){
                if($_POST['import_step'] == 2){
                    include_once("views/step-2.php"); 
                }
                else{
                    include_once("views/import.php");
                }
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
            
            if(! $this->is_registered_component($_POST['csv_component'])){
                $validate_status = array('success'=>false,'msg'=>'csv component not registered');
                return $validate_status;              
            }
            
            if(! $this->is_valid_file($_FILES['csv_file'])){
                $validate_status = array('success'=>false,'msg'=>'uploaded file is invalid');
                return $validate_status;                  
            }
            
            $csv_json = $this->parseCSV($_FILES['csv_file']['tmp_name']);
            
            $csvData = json_decode($csv_json);
            
            $i=0;
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
            
            if(!file_exists($upload_directory.'/ajci_tmp/'.$_POST['csv_component'].'/'))
                mkdir($upload_directory.'/ajci_tmp/'.$_POST['csv_component'].'/',0755);
            
            $csvFileUniqueName = time().'_'.$_FILES['csv_file']['name'];
            $csvFile = $upload_directory.'/ajci_tmp/'.$_POST['csv_component'].'/'.$csvFileUniqueName; // csv to save filepath
            move_uploaded_file($_FILES['csv_file']['tmp_name'], $csvFile);
            
            $file_info = array('realname'=>$_FILES['csv_file']['name'],'uniquename'=>$csvFileUniqueName);

            $validate_status = array('success'=>true,
                                     'msg'=>'File validated Successfully.',
                                     'row_count'=>count($csvData),
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
         * @param string $parse_to
         * 
         * @return string $csvJson json encoded string
         * 
         * @since    0.1.0
         */        
        function parseCSV($filepath,$parse_to = 'json') {
            // read the csv file
            $csv = new Coseva\CSV($filepath);
            // parse the csv
            $csv->parse();
            
            if($parse_to == 'table'){
                $csvParseTo = $csv->toTable();
            }
            else{
               //Convert parsed csv data to a json string
                $csvParseTo = $csv->toJSON();             
            }


            return $csvParseTo;
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
        public function is_registered_component($component){
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
         * 
         * @since    0.1.0
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
          * @param array $metadata array of metadata key value pairs
          * 
          * @return int $csv_id record id
          * 
          * @since    0.1.0
          * 
          */       
       public function add_csvfile_master($args = '',$metadata = array()){
           global $wpdb;

           $defaults = array(
                    'id'                  => false,
                    'component'           => '',    
                    'real_filename'       => '',                  
                    'filename'            => '',    
                    'status'              => '',
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
                                                                    'uploaded_on'   => $uploaded_on,
                                                                    'meta'          => maybe_serialize($metadata)
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
         * function to add csv parts files to db
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
         * 
         * @since    0.1.0
         */       
        public function add_csvfile_parts_db($args = ''){
           global $wpdb;
           
           $defaults = array(
                    'id'                  => false,
                    'csv_id'              => 0,                   
                    'filename'            => '',    
                    'status'              => ''
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
        * function to mark a csv master table record as completed 
        * 
        * @param int $csv_id 
        * 
        * @since    0.1.0
        */
       public function mark_csv_processed($csv_id){
           global $wpdb;

           // update the status field to mark the csv part completed
           $q = $wpdb->update($wpdb->ajci_csv,array('status'=>'completed'),
                                           array('id'=>$csv_id));  
              
       } 
       
        /*
         * function to add a csv file record and split the file into subparts
         * 
         * @param string $uniquefilename saved file name 
         * @param string $realfilename actual file name 
         * @param string $component csv component name
         * @param bool $header true if first row of csv is table header
         * 
         * @return int $id  | WP_Error 
         *  
         * @since    0.1.0
         */
        public function init_csv_data($uniquefilename,$realfilename,$component,$header = false){
            $uploads_dir = wp_upload_dir();
            $upload_directory = $uploads_dir['basedir'];
            $filename = $upload_directory.'/ajci_tmp/'.$component.'/'.$uniquefilename;

            if(file_exists($filename)){
                
                $args = array('component'     => $component,
                              'real_filename' => $realfilename,
                              'filename'      => $uniquefilename
                             );
                
                $metadata = array('header'=>$header);
                $id = $this->add_csvfile_master($args,$metadata);
                
                //Hook to call the async split csv upload using wp_sync
                do_action('ajci_split_csv',$id);
            }
            else{
                return new WP_Error('csv_file_not_found', __('CSV file for import not found') ); 
            }
            
            return $id;
        }  
       
        /*
         * function to break the master csv file into parts of smaller files
         * custom added function
         * 
         * @param int $csv_id of the master record
         * 
         * @return array $fileparts created smaller files
         * 
         * @since    0.1.0
         * 
         */      
       public function async_create_csvfile_parts($csv_id){
           global $wpdb,$ajci_components;

           $csv_master_info = $this->get_row_data_csv($csv_id);
           $uniquefilename = $csv_master_info->filename;
           $component = $csv_master_info->component;
           $meta_data = maybe_unserialize($csv_master_info->meta);
           
           $fileparts = array();
           $ajci_plugin_options = get_option('ajci_plugin_options');
           
           $uploads_dir = wp_upload_dir();
           $upload_directory = $uploads_dir['basedir'];
           $filename = $upload_directory.'/ajci_tmp/'.$component.'/'.$uniquefilename;
           
           $csv_json = $this->parseCSV($filename);
           $csvData = json_decode($csv_json);
           
           $lines_per_part = $ajci_plugin_options['ajci_lines_per_csv'];
           
           if(intval($lines_per_part) == 0){
               $lines_per_part = $this->plugin_num_lines_part;
           }
           
           if($meta_data['header'] == true){
              $count_csvData =  count($csvData) - 1;
           }else{
              $count_csvData =  count($csvData);
           }
           
           $mod = $count_csvData%$lines_per_part;
           $file_parts_count = ($count_csvData - $mod)/$lines_per_part;
           
           if($mod > 0)
            $file_parts_count = $file_parts_count+1;
           
           for($filecount=1; $filecount <= $file_parts_count; $filecount++){
               $offset = ($filecount-1)*$lines_per_part;
               
               // skip header on first file if csv file has header present
               if($filecount == 1 && $meta_data['header'] == true) {
                   $offset++;
               }
               
               $fileparts[] = 'part'.$filecount.'_'.$uniquefilename;
               $file_part_name =  $upload_directory.'/ajci_tmp/'.$component.'/part'.$filecount.'_'.$uniquefilename;
               $file = fopen($file_part_name,"w");
               $i = $offset;
               $limit = $offset + $lines_per_part;
               while($i < $limit){
                   if(isset($csvData[$i]) && !empty($csvData[$i]))
                    fputcsv($file,$csvData[$i]);
                   
                   $i++;
               }
               fclose($file);
           }
           
           // save the entries of created part file of a csv
           foreach($fileparts as $part){
                    $args = array(
                                'csv_id'   => $csv_id,
                                'filename' => $part
                                );
                    $this->add_csvfile_parts_db($args);
           }
           
           return $fileparts;
       }
 
       /*
        * function to process a csv part file 
        * invokes the componeent import filter to be hooked in the theme code
        * 
        * @param int $csv_id
        * @param int $part_id
        * 
        * @since    0.1.0
        */       
       public function csv_process_part($csv_id, $part_id){
           
           global $wpdb,$ajci_components;
           $import_reponse =array();
           $import_reponse['success'] = array();
           $import_reponse['error'] = array();
           
           //get the csv_id and part_id data
           $csv_master_info = $this->get_row_data_csv($csv_id);    
           $csv_parts_info = $this->get_row_data_csv_parts($part_id);  
           $component = $csv_master_info->component;
           $file_part_name = $csv_parts_info->filename;
           
           $uploads_dir = wp_upload_dir();
           $upload_directory = $uploads_dir['basedir'];
           $filename = $upload_directory.'/ajci_tmp/'.$component.'/'.$file_part_name;
           
           $csv_json = $this->parseCSV($filename);
           $csvData = json_decode($csv_json);
           
           //mark part status as processing
           $q = $wpdb->update($wpdb->ajci_csv_parts,array('status'=>'processing'),
                                           array('id'=>$part_id));            
           //While there is an entry in the CSV data   
           $i = 0;
           while ($i < count($csvData) ) {  
                $record_response = array();
                
                if(count($csvData[$i]) == count($ajci_components[$component]['headers'])){
                    $record_response = apply_filters($ajci_components[$component]['callback'],$record_response,$csvData[$i]);
                }
                
                if(!empty($record_response) && count($csvData[$i]) == count($ajci_components[$component]['headers'])){
                    
                    if($record_response['imported'] == true){
                       $import_reponse['success'][] =  $csvData[$i];
                    }
                    else{
                        $error_record = $csvData[$i];
                        $error_record[] = $record_response['reason'];
                        $import_reponse['error'][] = $error_record;
                    }
                }
                
                $i++;
           } 
           
           //mark part status as completed
           $q = $wpdb->update($wpdb->ajci_csv_parts,array('status'=>'completed'),
                                           array('id'=>$part_id));                
           
           $this->build_logs_csv_import($import_reponse,$component,$csv_id);
       }
       
       /*
        * function to check progress of a csv import process
        * @param int $csv_id master csv record being processed
        * 
        * @return array $csv_processing_status
        * 
        * @since    0.1.0
        */
       public function csv_check_progress($csv_id){
           
           $csv_processing_status = $this->get_csv_processing_status($csv_id);
           $csv_processing_status['log_paths'] = array();
           $parts_count = count($csv_processing_status['notstarted']) + count($csv_processing_status['processing']) 
                          + count($csv_processing_status['completed']);
           
           if($parts_count == 0){
               return $csv_processing_status;
           }
           
           // if all parts are completed call the the async temp files cleanup method
           if($parts_count == count($csv_processing_status['completed'])){
               //do_action('ajci_temp_csvs_cleanup',$csv_id);
               $csv_processing_status['log_paths'] = $this->get_import_log_paths($csv_id);
               $this->cleanup_temp_csv_files($csv_id);
               $this->mark_csv_processed($csv_id);
               return $csv_processing_status;
           }
           
           //if no part is being processed call the async csv process part for a not started part
           if(count($csv_processing_status['processing']) == 0 && !empty($csv_processing_status['notstarted'])){        
               $csv_processing_status['temp_debug'] = $csv_id.'--'.$csv_processing_status['notstarted'][0];
               //do_action('ajci_csv_process_part',$csv_id,$csv_processing_status['notstarted'][0]);
               $this->csv_process_part($csv_id,$csv_processing_status['notstarted'][0]);
           }
           
           return $csv_processing_status;
       }
       
       /*
        * function to get the status response of all parts of the CSV
        * @param int $csv_id 
        * 
        * @return array $response array of statuses and their corressponding part ids
        * 
        * @since    0.1.0
        */
       public function get_csv_processing_status($csv_id){
           global $wpdb;
        
           $response = array();
           $response['notstarted'] = array();
           $response['processing'] = array();
           $response['completed'] = array();
           
           $ajci_csv_qry = $wpdb->prepare(
                "SELECT id,status FROM $wpdb->ajci_csv_parts
                        WHERE csv_id = %d",
                array($csv_id)
            );
           $csv_parts_results = $wpdb->get_results($ajci_csv_qry);    
           
           if(!empty($csv_parts_results)){
            foreach($csv_parts_results as $csv_part){
                if($csv_part->status == 'processing'){
                   $response['processing'][] = $csv_part->id; 
                }
                elseif($csv_part->status == 'completed'){
                   $response['completed'][] = $csv_part->id; 
                }
                else{
                   $response['notstarted'][] = $csv_part->id;
                }
            }
           }
           
           return $response;
       }
       
       /*
        * function to get the record fields in table ajci_csv
        * @param int $id
        * 
        * @since    0.1.0
        */
       public function get_row_data_csv($id){
           global $wpdb;
           
           $ajci_csv_qry = $wpdb->prepare(
                "SELECT * FROM $wpdb->ajci_csv
                        WHERE id = %d",
                array($id)
            );
           $rowfields = $wpdb->get_row($ajci_csv_qry);         
           return $rowfields;
       }
       
       /*
        * function to get the record fields in table ajci_csv_parts
        * @param int $id id of the part
        * 
        * @since    0.1.0
        */
       public function get_row_data_csv_parts($id){
           global $wpdb; 
           
           $ajci_csv_parts_qry = $wpdb->prepare(
                "SELECT * FROM $wpdb->ajci_csv_parts
                        WHERE id = %d",
                array($id)
            );
           $rowfields = $wpdb->get_row($ajci_csv_parts_qry);         
           return $rowfields;
       }     
       /*
        * function to build csv logs after parsing csv part file
        * 
        * @param array $records_array  array of records with keys success and error
        * @param string $component component name 
        * @param array $log_paths array location to hold the log path urls
        * 
        * @return array $log_paths
        * 
        * @since    0.1.0
        */
       public function build_logs_csv_import($records_array,$component,$csv_id){
           
           if(count($records_array['success']) > 0){
               $this->write_to_log($records_array['success'],'success',$component,$csv_id);
           }
           
           if(count($records_array['error']) > 0){
               $this->write_to_log($records_array['error'],'error',$component,$csv_id);
           }
           
       }
       
       /*
        * function to write records to a log file 
        * 
        * @param array $records 
        * @param string $log_type success|error
        * @param string $component
        * 
        * @return string $url url to the log file link
        * 
        * @since    0.1.0
        * 
        */
       public function write_to_log($records,$log_type,$component,$csv_id){
           global $ajci_components;
           
           $uploads_dir = wp_upload_dir();
           $upload_directory = $uploads_dir['basedir'];
           $upload_url = $uploads_dir['baseurl'];
           
           if(!file_exists($upload_directory.'/ajci_tmp/import_logs/'))
              mkdir($upload_directory.'/ajci_tmp/import_logs/',0755);
           
           $log_file_name = $component.'_'.$log_type.'_'.$csv_id.'.csv';

           $log_file_path = $upload_directory.'/ajci_tmp/import_logs/'.$log_file_name;
           $log_url_path = $upload_url.'/ajci_tmp/import_logs/'.$log_file_name;
           
           if(file_exists($log_file_path)){
                $csv_handler = fopen($log_file_path, 'a');
            
           }
           else{
               $csv_handler = fopen($log_file_path, 'w');
               $csv_headers = $ajci_components[$component]['headers'];
               
               if($log_type == 'error'){
                $csv_headers[] = 'REASON';
               }
               
               fputcsv($csv_handler, $csv_headers);
           }
           
           
            foreach ($records as $record) 
            {
                if(isset($record) && !empty($record))
                    fputcsv($csv_handler, $record);
            }

            fclose($csv_handler);            
           
       }
       
       /*
        * function to delete csv files created during import process and parts records from db
        * @param id $csv_id
        * 
        * @since    0.1.0
        */
       public function cleanup_temp_csv_files($csv_id){ 
           $import_filenames = $this->get_filenames($csv_id);
           
           $csv_master_info = $this->get_row_data_csv($csv_id);
           $component = $csv_master_info->component;
                   
           $uploads_dir = wp_upload_dir();
           $upload_directory = $uploads_dir['basedir'];
           
           if($import_filenames['main'] != ''){
            $main_file_path = $upload_directory.'/ajci_tmp/'.$component.'/'.$import_filenames['main'];
                if(file_exists($main_file_path)){
                    unlink($main_file_path);
                }
           }
           
           foreach($import_filenames['parts'] as $partfile){
             if($partfile != ''){
                $part_file_path = $upload_directory.'/ajci_tmp/'.$component.'/'.$partfile;
                    if(file_exists($part_file_path)){
                        unlink($part_file_path);
                    }
                }             
           }
           
           //Delete the csv parts records
           $this->csv_delete_completed_csv_parts($csv_id);
       }
       
       /*
        * function to delete parts records for a csv on completion
        * @param int $csv_id
        * 
        * @since    0.1.0
        */
       public function csv_delete_completed_csv_parts($csv_id){
          global $wpdb;
          $wpdb->delete( $wpdb->ajci_csv_parts, array('csv_id' =>$csv_id ));       
       }
       
       /*
        * function to cleanup import logs created of a csv component
        * 
        * @since    0.1.0
        */
       public function cleanup_import_logs($component){
           $log_types = array('success','error');
           $uploads_dir = wp_upload_dir();
           $upload_directory = $uploads_dir['basedir'];
           
           $logs_file_dir = $upload_directory.'/ajci_tmp/import_logs';
           
           foreach ($log_types as $log_type){
               $log_filepath = $logs_file_dir.'/'.$component.'_'.$log_type.'.csv';
               if(file_exists($log_filepath))
                   unlink ($log_filepath);
           }        
           
       }
       
       /*
        * function to get the import log urls for a csv imported
        * @param int $csv_id
        * 
        * @since    0.1.0
        * 
        */
       public function get_import_log_paths($csv_id){
           $log_urls =array('success'=>'',
                           'error'=>''); 
 
           $uploads_dir = wp_upload_dir();
           $upload_directory = $uploads_dir['basedir'];
           $upload_url = $uploads_dir['baseurl'];
           
           $logs_file_dir = $upload_directory.'/ajci_tmp/import_logs';
           
           $csv_master_info = $this->get_row_data_csv($csv_id);
           
           foreach($log_urls as $key=>$value){
               $filename = $logs_file_dir.'/'.$csv_master_info->component.'_'.$key.'_'.$csv_id.'.csv';
               if(file_exists($filename)){
                    $fileurl = $upload_url.'/ajci_tmp/import_logs/'.$csv_master_info->component.'_'.$key.'_'.$csv_id.'.csv';
                    $log_urls[$key] = '<a href="'.$fileurl.'" target="_blank">View Log</a>';
               }
           }
           
           return $log_urls;
       }
       
       /*
        * function to get csv and csv parts filenames
        * @param int $id
        * 
        * @param array $filenames filenames array with keys main,parts
        * 
        * @since    0.1.0
        */
       public function get_filenames($id){
           global $wpdb;
           $filenames = array();
           
           $ajci_csv_filename = $wpdb->prepare(
                "SELECT filename FROM $wpdb->ajci_csv
                        WHERE id = %d",
                array($id)
            );
          $filenames['main']=$wpdb->get_var($ajci_csv_filename);     
          
          $ajci_csv_parts_filenames = $wpdb->prepare(
                "SELECT filename FROM $wpdb->ajci_csv_parts
                        WHERE csv_id = %d",
                array($id)
            );
         $csv_parts_filenames=$wpdb->get_results($ajci_csv_parts_filenames);   
         
         foreach($csv_parts_filenames as $part_file){
            $filenames['parts'][] = $part_file->filename; 
         }
           
          return $filenames;
       }
}
<?php
/*
 * Api configuration and methods of the plugin of the plugin
 * 
 */

/*
 * plugin api functionality
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
if(is_plugin_active('json-rest-api/plugin.php')){
    /**
     * Change the json rest api plugin prefix from wp-json to api
     *
     * @since CSV-plugin (0.1)
     *
     * @uses json rest api plugin filter hook json_url_prefix
     */
    function ajci_change_json_rest_api_prefix($prefix){
        $prefix = 'api';
        return $prefix;

    }
    add_filter( 'json_url_prefix', 'ajci_change_json_rest_api_prefix',10,1);
    
    /*
     * function to configure the plugin api routes
     */
    function csvimport_plugin_api_init($server) {
        global $csvimport_plugin_api;

        $csvimport_plugin_api = new CsvImportAPI($server);
        add_filter( 'json_endpoints', array( $csvimport_plugin_api, 'register_routes' ) );
    }
    add_action( 'wp_json_server_before_serve', 'csvimport_plugin_api_init',10,1 );

    class CsvImportAPI {

        /**
         * Server object
         *
         * @var WP_JSON_ResponseHandler
         */
        protected $server;

        /**
         * Constructor
         *
         * @param WP_JSON_ResponseHandler $server Server object
         */
        public function __construct(WP_JSON_ResponseHandler $server) {
                $this->server = $server;
        }

        /*Register Routes*/
        public function register_routes( $routes ) {
             $routes['/csvimport/componentheaders'] = array(
                array( array( $this, 'get_component_headers'), WP_JSON_Server::READABLE ),
                );
             $routes['/csvimport/getcsvpreview'] = array(
                array( array( $this, 'get_csv_preview'), WP_JSON_Server::CREATABLE ),
                );
             $routes['/csvimport/splitcsv/(?P<csv_id>\d+)'] = array(
                array( array( $this, 'split_csv'), WP_JSON_Server::READABLE | WP_JSON_Server::EDITABLE),
                );
             $routes['/csvimport/processcsv/(?P<csv_id>\d+)'] = array(
                array( array( $this, 'process_csv'), WP_JSON_Server::READABLE ), 
                );
            return $routes;
        }
        
        /*
         * function to get component response headers
         * uses function ajci_get_component_headers
         */
        public function get_component_headers(){
            if(isset($_GET['component'])){
                $component = $_GET['component'];
                $headers = ajci_get_component_headers($component);
                $response = json_encode($headers);
             }
             else{
                 $response =json_encode(array('Invalid Request'));
             }
            header( "Content-Type: application/json" );
            echo $response;
            exit;
        }
        
        /*
         * function to get a csv file preview response
         * uses function ajci_csv_get_preview
         */
        public function get_csv_preview(){
            $component = $_POST['component'];
            $csv_path = $_POST['filepath'];
            $preview_type = isset($_POST['preview_type'])? $_POST['preview_type'] : '';
            $response = ajci_csv_get_preview($component ,$csv_path, $preview_type);
            header( "Content-Type: application/json" );
            echo json_encode($response);
            exit;
        }
        
        /*
         * function to split a master csv file into smaller parts
         * @param int $csv_id
         * uses function ajci_split_csv
         * 
         * generates the response array which containes names of part file
         */
        public function split_csv($csv_id){
            $csv_id = intval($csv_id);
            $header = boolval($_POST['header']);
            
            $meta = array(
                     'header' =>$header,
                    );
            ajci_csv_update_meta($csv_id,$meta);
            $response = ajci_split_csv($csv_id);
            header( "Content-Type: application/json" );
            echo json_encode($response);
            exit;
        }
        
        /*
         * function to process a csv record
         * @param int $csv_id
         * 
         * generates the response with status of csv record being processed
         */
        public function process_csv($csv_id){
            $csv_id = intval($csv_id);
            $response = ajci_process_csv($csv_id);
            header( "Content-Type: application/json" );
            echo json_encode($response);
            exit;            
        }
    }

}

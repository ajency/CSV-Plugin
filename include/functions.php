<?php
/*
 * Custom general functions of plugin
 * 
 */

 /* 
 * function to display preview of a valid csv file
 * @param string $component_name 
 * @param array $validated_response of the csv file
 * 
 * @return string $output; 
 */
function ajci_display_csv_preview($component_name = '',$validated_response = array()){
    global $ajci_components;
    $output ='';
    $output .='<p>Total Records In CSV:'.$validated_response['row_count'].'</p>';
    
    $output .='<p>CSV data Preview:</p>';
    $output .= '<form method="post">';
    $output .= '<table border="1">';
    $output .= '<tr><th></th><th>Sr No.</th>';
    foreach($ajci_components[$component_name]['headers'] as $label){
        $output .= '<th>'.$label.'</th>';
    }
    
    $flag = 0;
    $sr_count = 1;
    foreach($validated_response['preview_rows'] as $row){
        $output .= '<tr>'; 
        if($flag == 0){
            $output .= '<td><input type="checkbox" name="csv_header" id="csv_header" /></td>';
            $flag=1;
        }else{
           $output .= '<td></td>';  
        }
        $output .= '<td>'.$sr_count.'</td>';
        
        foreach ($row as $col){
             $output .= '<td>'.$col.'</td>';
        }
         $output .= '</tr>';
         $sr_count++;
    }
    
    $output .= '</tr>';
    $output .= '</table>';
    
    $output .= '<br/>';
    $output .= '<p>
                Check The Check Box at the first row of the Preview if it is the Header Row.
                </p>
                <input type="hidden" name="uniquename" id="uniquename" value="'.$validated_response['files']['uniquename'].'" />
                <input type="hidden" name="realname" id="realname" value="'.$validated_response['files']['realname'].'" />
                <input type="hidden" name="import_step" id="import_step" value="2" />
                <input type="hidden" name="csv_component" id="csv_component" value="users" />
                <input type="submit"
                name="submit"
                value="Confirm Import" /> 
                </form>';
    return $output;
}

/*
 * function to setup the split csv async tasks
 */
function setup_ajci_async_task(){
     new AJCI_Splitcsv_Async_Task(1);
}
add_action('wp_loaded', 'setup_ajci_async_task',10);

/*
 * function to split the csv file with async request 
 * uses the global obj method create_csvfile_parts
 * @param int $id csv file master id
 * 
 */
function async_ajci_split_csv($csv_id){
    global $aj_csvimport;
    $aj_csvimport->create_csvfile_parts($csv_id);
}
//add_action('wp_async_nopriv_ajci_split_csv', 'async_ajci_split_csv', 100,1);
add_action('wp_async_ajci_split_csv', 'async_ajci_split_csv', 100,1);

/*
 * function to get the CSV headers of a registered component
 * @param string $component
 * 
 * @return array $headers headers of a registered csv component
 */
function ajci_get_component_headers($component){
    global $ajci_components;
    $headers =array();   
    
    if(array_key_exists($component, $ajci_components)){
        $headers = $ajci_components[$component]['headers'];
    }
    
    return $headers;
}

/*
 * function to get the preview rows of a csv
 * @param string $component
 * @param string $csv_path 
 * @param string $response_type
 * 
 * @param array $response
 * 
 */
function ajci_csv_get_preview($component ,$csv_path, $response_type = ''){
    global $aj_csvimport,$ajci_components;

    //check if component is registered 
    if(! $aj_csvimport->is_registered_component($component)){
       $response = array('success'=>false,'msg'=>'csv component not registered');
       return $response;              
    }  
    
    //check if input file is a valid file 
    $allowedExts = array("csv");
    $temp = explode(".", $csv_path);
    $extension = end($temp);
    if(!in_array($extension, $allowedExts)){
        $response = array('success'=>false,'msg'=>'file type is invalid');
        return $response;                  
    }
    
    
    if(file_exists($csv_path)){

        $preview_rows = '';
        $csv_json = $aj_csvimport->parseCSV($csv_path);
        $csvData = json_decode($csv_json);
        $row_count = count($csvData);
        
        //check if csv file records are valid
        $i=0;
        while ($i < count($csvData) ) {
            if( count($csvData[$i]) !== count($ajci_components[$component]['headers'])){
                $response = array('success'=>false,'msg'=>'Rows columns incorrect count.');
                return $response;
            }
            $i++;
        }
        
        
        if($row_count < 20){
            $preview_count = $row_count;
        }
        else{
            $preview_count = 20;
        }
        
        
        //get preview response based on response type if type not blank
        if($response_type != ''){
            $preview_rows = ajci_get_csv_preview_formated($csvData,$preview_count,$response_type);
        }
        
        //create csv master record
        $file_path_parts = pathinfo($csv_path);
        $args = array('component'     => $component,
                      'real_filename' => $file_path_parts['basename'],
                      'filename'      => $csv_path
                     );

        $id = $aj_csvimport->add_csvfile_master($args);

        //Hook to call the async split csv upload using wp_sync 
        //to be changed if does not get triggered
        //do_action('ajci_split_csv',$id);
        
        $response = array('success'=>true,
                          'csv_id'=>$id,
                          'preview_rows' => $preview_rows,
                          'row_count' => $row_count,
                         );
    }
    else{
        $response = array('success'=>false,'msg'=>'file does not exits');
    }
  
    return $response;
}

/*
 * function to format the csv preview response based on response type
 * @param array $csvData
 * @param int $preview_count
 * @param string $response_type
 * 
 */
function ajci_get_csv_preview_formated($csvData,$preview_count,$response_type){
    
    if($response_type == 'JSON'){
       $formated_response = array();
       for($i=0;$i<$preview_count;$i++){
           $formated_response[] = $csvData[$i];
       }
    }
    elseif($response_type == 'HTML'){
        $formated_response = '<table border="1">';
        for($i=0;$i<$preview_count;$i++){
            $formated_response .= '<tr>';
            foreach ($csvData[$i] as $col){
                  $formated_response .= '<td>'.$col.'</td>';
            }
            $formated_response .= '</tr>';
        }
        $formated_response .= '</table>';
    }
    else{
        $formated_response = '';
    }
    
    return $formated_response;
}

function ajci_split_csv($csv_id){
   global $aj_csvimport;
   $response = $aj_csvimport->create_csvfile_parts($csv_id,true);
   return $response;
}

/*
 * update csv master record meta data
 * 
 */
function ajci_csv_update_meta($csv_id,$metadata = array()){
    global $wpdb;
    $ajci_csv_meta = $wpdb->prepare(
     "SELECT meta FROM $wpdb->ajci_csv
             WHERE id = %d",
     array($csv_id)
     );
    $meta=$wpdb->get_var($ajci_csv_meta);  
    
    $meta = maybe_unserialize($meta);
    
    foreach ($metadata as $key => $value){
        $meta[$key] = $value;
    }
    
    $meta = maybe_serialize($meta);
    
    //update meta 
    $q = $wpdb->update($wpdb->ajci_csv,array('meta'=>$meta),
                                    array('id'=>$csv_id));       
}

/*
 * plugin api functionality
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
if(is_plugin_active('json-rest-api/plugin.php')){
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
         */
        public function split_csv($csv_id){
            $csv_id = intval($csv_id);
            $header = $_POST['header'];
            
            $meta = array(
                     'header' =>$header,
                    );
            ajci_csv_update_meta($id,$meta);
            $response = ajci_split_csv($csv_id);
            header( "Content-Type: application/json" );
            echo json_encode($response);
            exit;
        }
    }

}

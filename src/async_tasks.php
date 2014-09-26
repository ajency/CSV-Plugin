<?php
/*
 * Custom WP_Async tasks
 * 
 */

// include the WP_Async class file
require_once( plugin_dir_path( __FILE__ ) . '/WP_Async/wp-async-task.php');

class AJCI_Splitcsv_Async_Task extends WP_Async_Task {

    protected $action = 'ajci_split_csv';

    /**
     * Prepare data for the asynchronous request
     *
     * @throws Exception If for any reason the request should not happen
     *
     * @param array $data An array of data sent to the hook
     *
     * @return array
     */
    protected function prepare_data( $data ) {
        $csv_id = $data[0];
        return array('csv_id' =>$csv_id);
    }

    /**
     * Run the async task action
     */
    protected function run_action( ) {
        $csv_id = $_POST['csv_id'];
        do_action( "wp_async_$this->action",$csv_id);
    }
}

class AJCI_Csvprocesspart_Async_Task extends WP_Async_Task {

    protected $action = 'ajci_csv_process_part';

    /**
     * Prepare data for the asynchronous request
     *
     * @throws Exception If for any reason the request should not happen
     *
     * @param array $data An array of data sent to the hook
     *
     * @return array
     */
    protected function prepare_data( $data ) {
        $csv_id = $data[0];
        $part_id = $data[1];
        return array('csv_id' =>$csv_id,'part_id' =>$part_id);
    }

    /**
     * Run the async task action
     */
    protected function run_action( ) {
        $csv_id = $_POST['csv_id'];
        $part_id = $_POST['part_id'];
        do_action( "wp_async_$this->action",$csv_id,$part_id);
    }
}

class AJCI_Tempcsvscleanup_Async_Task extends WP_Async_Task {

    protected $action = 'ajci_temp_csvs_cleanup';

    /**
     * Prepare data for the asynchronous request
     *
     * @throws Exception If for any reason the request should not happen
     *
     * @param array $data An array of data sent to the hook
     *
     * @return array
     */
    protected function prepare_data( $data ) {
        $csv_id = $data[0];
        return array('csv_id' =>$csv_id);
    }

    /**
     * Run the async task action
     */
    protected function run_action( ) {
        $csv_id = $_POST['csv_id'];
        do_action( "wp_async_$this->action",$csv_id);
    }
}

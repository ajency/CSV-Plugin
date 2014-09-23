<?php
/*
 * Custom functions of registering CSV components
 * 
 * function to register Csv component and its headers
 * @param string component name 
 * @param array headers of the CSV file to be uploaded
 * 
 */
function register_csv_component($component_name = '',$headers = array()){
    global $ajci_components;
    
    $ajci_comp = array();
    //get the hooked CSV components/headers and assign to global variable
    $ajci_components = apply_filters('ajci_csv_component_filter',$ajci_comp);
    if($component_name != '' && !empty($headers)){
        if(empty($ajci_components)){
            $ajci_components[$component_name] = array();
        }else{
            if(!array_key_exists($component_name, $ajci_components))
                    $ajci_components[$component_name] = array();
        }

        foreach($headers as $value){
                    $ajci_components[$component_name]['headers'][]=$value;
                    $ajci_components[$component_name]['headers'] = array_unique($ajci_components[$component_name]['headers']);
        }
        
        $ajci_components[$component_name]['callback'] = 'ajci_parse_record_'.$component_name;
    }
}

/*
 * hook function to get the theme defined csv components
 */
function theme_defined_csv_components($ajci_comp){
    $defined_csv_components = array();  // theme defined user components array  ie format array('component_name'=>array('comm_type1','comm_type1'))
    $defined_csv_components = apply_filters('add_csv_commponents_filter',$defined_csv_components);
    
    foreach($defined_csv_components as $component => $comm_types){
            if(!array_key_exists($component, $comm_types))
                $ajci_comp[$component] = array();
            
                foreach($comm_types as $value){
                $ajci_comp[$component][]=$value;
                $ajci_comp[$component] = array_unique($ajci_comp[$component]);
                }
    }

    return $ajci_comp;
    
}
add_filter('ajci_csv_component_filter','theme_defined_csv_components',10,1);

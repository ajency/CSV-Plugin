/**
 * This is the main javascript file for the CSV Import plugin's main administration view.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end administrator.
 *
 * @package   csv-import
 * @author    Team Ajency <talktous@ajency.in>
 * @license   GPL-2.0+
 * @link      http://ajency.in
 * @copyright 7-24-2014 Ajency.in
 */

(function ($) {
	"use strict";
	$(function () {
		// Place your administration-specific JavaScript here
            jQuery("#import-csv-start").on('click',function(){
                 jQuery(this).prop('disabled', true); 
                 jQuery("#log_view").html('Please Wait Import in progress..'); 
                 var csv_id = jQuery('#csv-master-id').val();
                 var _this = jQuery(this);
                 var check_csv_import_progress = setInterval(function()
                              {
                                jQuery.post( ajaxurl,
                                {
                                  action    : 'ajci_csv_check_progress',
                                  csv_id    : csv_id
                                },
                                function(data) { 
                                  console.log(data);
                                  if(data.code ==='ERROR'){
                                      jQuery(_this).prop('disabled', false);
                                      jQuery("#log_view").html('Error CSV file already imported!!');                                      
                                      clearInterval(check_csv_import_progress);
                                  }else{
                                        if(data.totalparts == data.totalcompleted){ 
                                            jQuery(_this).prop('disabled', false);
                                            var logstable = '<table>';
                                            logstable = logstable+'<tr><td>Successfull Import</td><td>'+data.log_paths.success+'</td></tr>';
                                            logstable = logstable+'<tr><td>Failed Import</td><td>'+data.log_paths.error+'</td></tr>';
                                            logstable = logstable+'</table>';
                                            jQuery("#log_view").html(data.totalcompleted+' parts out of '+data.totalparts+' completed'+logstable); 
                                            clearInterval(check_csv_import_progress);
                                        }else{
                                            jQuery("#log_view").html(data.totalcompleted+' parts out of '+data.totalparts+' completed'); 
                                        } 
                                    }
                                },'json');  

                              }, 5000);                         

            });
                

	});
}(jQuery));
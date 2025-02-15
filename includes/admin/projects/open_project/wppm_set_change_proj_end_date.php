<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
global $wpdb,$wppmfunction,$current_user;
if ( check_ajax_referer( 'wppm_set_change_proj_end_date', '_ajax_nonce', false ) != 1 ) {
  wp_send_json_error( 'Unauthorised request!', 401 );
}
$proj_id  = isset($_POST['proj_id']) ? intval(sanitize_text_field($_POST['proj_id'])) : '' ;
$proj_data = $wppmfunction->get_project($proj_id);
$wppm_current_user_capability = get_user_meta( $current_user->ID, 'wppm_capability', true );
if (!(($current_user->ID && $current_user->has_cap('manage_options')) || $wppmfunction->has_project_permission('change_project_details',$proj_id) ||($wppm_current_user_capability =='wppm_manager' && $proj_data['created_by']==$current_user->ID ) || $wppm_current_user_capability =='wppm_admin')) {exit;}
$date =  isset($_POST['date']) ? (sanitize_text_field($_POST['date'])) : '' ;
$edate = strtotime($date); 
$end_date = date('Y-m-d H:i:s', $edate); 
if($end_date && $end_date != $proj_data['end_date'] ){
  $wppmfunction->change_project_end_date( $proj_id, $end_date);
}
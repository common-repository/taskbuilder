<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $current_user,$wppmfunction,$wpdb;
if ( check_ajax_referer( 'wppm_set_change_project_status', '_ajax_nonce', false ) != 1 ) {
    wp_send_json_error( 'Unauthorised request!', 401 );
}
$project_id    = isset($_POST['project_id'])  ? sanitize_text_field($_POST['project_id']) : '';
$project_data = $wppmfunction->get_project($project_id);
$wppm_current_user_capability = get_user_meta( $current_user->ID, 'wppm_capability', true );
if (!(($current_user->ID && $current_user->has_cap('manage_options')) || $wppmfunction->has_project_permission('change_project_status',$project_id) || ($project_data['created_by']==$current_user->ID && $wppm_current_user_capability=='wppm_manager') || $wppm_current_user_capability=='wppm_admin')) {exit;}

$status_id   = isset($_POST['wppm_project_status']) ? intval(sanitize_text_field($_POST['wppm_project_status'])) : 0 ;
if( !$status_id ){
  die();
}
$old_status_id   	= $project_data['status'];

if($status_id && $status_id!=$old_status_id){
	$wppmfunction->change_project_status( $project_id, $status_id);
}
do_action('wppm_after_set_change_project_status',$project_id, $status_id, $old_status_id);
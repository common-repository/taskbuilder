<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $current_user,$wppmfunction,$wpdb;

if ( check_ajax_referer( 'wppm_set_change_project_raised_by', '_ajax_nonce', false ) != 1 ) {
    wp_send_json_error( 'Unauthorised request!', 401 );
}
$project_id = isset($_POST['project_id'])  ? intval(sanitize_text_field($_POST['project_id'])) : '';
$project_data = $wppmfunction->get_project($project_id);
$wppm_current_user_capability = get_user_meta( $current_user->ID, 'wppm_capability', true );

if (!(($current_user->ID && $current_user->has_cap('manage_options')) || $wppmfunction->has_project_permission('change_project_raised_by',$project_id) || ($project_data['created_by']==$current_user->ID && $wppm_current_user_capability=='wppm_manager') || $wppm_current_user_capability=='wppm_admin')) {exit;}

$user_id = isset($_POST['wppm_user_id']) ? intval(sanitize_text_field($_POST['wppm_user_id'])) : 0 ;
$old_user_id  = esc_sql($project_data['created_by']);
if ( $user_id != $old_user_id ){
	$wppmfunction->change_project_raised_by($project_id, $user_id);
	$change_creator_value = array('prev_user'=>"$old_user_id",'new_user'=>"$user_id");
	$change_creator_obj = serialize($change_creator_value);
	$log_values = array('proj_id'=>$project_id,'body'=>$change_creator_obj,'attachment_ids'=>"",'create_time'=>date("Y-m-d h:i:sa"),'created_by'=>$current_user->ID );
	$wpdb->insert($wpdb->prefix . 'wppm_project_comment',$log_values);
	$log_id = esc_sql($wpdb->insert_id);
	$proj_log_values = array('proj_id'=>$project_id,'comment_id'=>$log_id,'comment_type'=>'change_proj_creator');
	$wpdb->insert($wpdb->prefix . 'wppm_project_comment_meta',$proj_log_values);
}

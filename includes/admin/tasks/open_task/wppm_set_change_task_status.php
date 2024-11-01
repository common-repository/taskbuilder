<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $current_user,$wppmfunction,$wpdb;

$task_id    = isset($_POST['task_id'])  ? sanitize_text_field($_POST['task_id']) : '';
if (!(($current_user->ID && $current_user->has_cap('manage_options')) || $wppmfunction->has_permission('change_status',$task_id))) {exit;}
if ( check_ajax_referer( 'wppm_set_change_task_status', '_ajax_nonce', false ) != 1 ) {
	wp_send_json_error( 'Unauthorised request!', 401 );
}
$status_id   = isset($_POST['wppm_status']) ? intval(sanitize_text_field($_POST['wppm_status'])) : 0 ;
if( !$status_id ){
  die();
}
$task_data = $wppmfunction->get_task($task_id);
$old_status_id   	= $task_data['status'];
if($status_id && $status_id!=$old_status_id){
	$wppmfunction->change_status( $task_id, $status_id);
	$change_task_value = array('prev_status'=>"$task_data[status]",'new_status'=>"$status_id");
	$change_task_obj = serialize($change_task_value);
	$log_values = array('task_id'=>$task_id,'body'=>$change_task_obj,'attachment_ids'=>"",'create_time'=>date("Y-m-d h:i:sa"),'created_by'=>$current_user->ID );
	$wpdb->insert($wpdb->prefix . 'wppm_task_comment',$log_values);
	$log_id = $wpdb->insert_id;
	$task_log_values = array('task_id'=>$task_id,'comment_id'=>$log_id,'comment_type'=>'change_task_status');
	$wpdb->insert($wpdb->prefix . 'wppm_task_comment_meta',$task_log_values);
}
do_action('wppm_after_set_change_task_status',$task_id,$status_id,$old_status_id);

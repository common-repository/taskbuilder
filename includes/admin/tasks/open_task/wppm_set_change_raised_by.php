<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $current_user,$wppmfunction,$wpdb;
$task_id = isset($_POST['task_id'])  ? intval(sanitize_text_field($_POST['task_id'])) : '';

if (!(($current_user->ID && $current_user->has_cap('manage_options')) || $wppmfunction->has_permission('change_raised_by',$task_id))) {exit;}
if ( check_ajax_referer( 'wppm_set_change_raised_by', '_ajax_nonce', false ) != 1 ) {
	wp_send_json_error( 'Unauthorised request!', 401 );
}
$user_id = isset($_POST['user_id']) ? sanitize_text_field($_POST['user_id']) : 0 ;
$task_data = $wppmfunction->get_task($task_id);
$old_user_id  = $task_data['created_by'];

if ( $user_id != $old_user_id ){
	$wppmfunction->change_raised_by($task_id, $user_id);
	$change_creator_value = array('prev_user'=>"$old_user_id",'new_user'=>"$user_id");
	$change_creator_obj = serialize($change_creator_value);
	$log_values = array('task_id'=>$task_id,'body'=>$change_creator_obj,'attachment_ids'=>"",'create_time'=>date("Y-m-d h:i:sa"),'created_by'=>$current_user->ID, );
	$wpdb->insert($wpdb->prefix . 'wppm_task_comment',$log_values);
	$log_id = $wpdb->insert_id;
	$task_log_values = array('task_id'=>$task_id,'comment_id'=>$log_id,'comment_type'=>'change_task_creator');
	$wpdb->insert($wpdb->prefix . 'wppm_task_comment_meta',$task_log_values);
}
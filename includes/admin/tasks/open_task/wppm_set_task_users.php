<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
global $wpdb,$wppmfunction,$current_user;
$task_id  = isset($_POST['task_id']) ? intval(sanitize_text_field($_POST['task_id'])) : '' ;
if (!(($current_user->ID && $current_user->has_cap('manage_options')) || $wppmfunction->has_permission('assign_task_users',$task_id))) {exit;}
if ( check_ajax_referer( 'wppm_set_task_users', '_ajax_nonce', false ) != 1 ) {
	wp_send_json_error( 'Unauthorised request!', 401 );
}
$task_data = $wppmfunction->get_task($task_id);
$users = "";
if(!empty($_POST['user_names'])){
  $users = $wppmfunction->sanitize_array($_POST['user_names']);
  $users = array_unique($users);
  $users = implode(",",$users);
}
$prev_assign_users = $task_data['users'];
if(isset($prev_assign_users)){
  $prev_assign_users = explode(",",$prev_assign_users);
}
$prev_assign_user_meta = $wppmfunction->get_task_meta($task_id,'prev_assigned_task_users');
$wppmfunction->delete_task_meta($task_id,'prev_assigned_task_users');

if(!empty($prev_assign_users)){
  foreach( $prev_assign_users as $ass_user){
    $wppmfunction->add_task_meta($task_id,'prev_assigned_task_users',$ass_user);
  }
}
if(isset($users)){
  $values=array(
    'users'=> $users
  );
}else{
  $values=array(
    'users'=> ''
  );
}
if(($users != $task_data['users'])){
  $wpdb->update($wpdb->prefix.'wppm_task', $values, array('id'=>$task_id));
  $change_assign_user_value = array('prev_assign_user'=>"$task_data[users]",'new_assign_user'=>"$users");
  $change_assign_user_obj = serialize($change_assign_user_value);
  $log_values = array('task_id'=>$task_id,'body'=>$change_assign_user_obj,'attachment_ids'=>"",'create_time'=>date("Y-m-d h:i:sa"),'created_by'=>$current_user->ID );
  $wpdb->insert($wpdb->prefix . 'wppm_task_comment',$log_values);
  $log_id = $wpdb->insert_id;
  $task_log_values = array('task_id'=>$task_id,'comment_id'=>$log_id,'comment_type'=>'change_assign_user');
	$wpdb->insert($wpdb->prefix . 'wppm_task_comment_meta',$task_log_values);
  do_action('wppm_set_task_users', $task_id);
}
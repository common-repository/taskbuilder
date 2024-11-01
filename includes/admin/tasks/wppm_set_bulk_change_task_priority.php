<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
global $wpdb,$wppmfunction,$current_user;
if ( check_ajax_referer( 'wppm_set_bulk_change_task_priority', '_ajax_nonce', false ) != 1 ) {
    wp_send_json_error( 'Unauthorised request!', 401 );
}

$task_ids = isset( $_POST['task_ids'] ) ? array_filter( array_map( 'intval', explode( ',', sanitize_text_field( wp_unslash( $_POST['task_ids'] ) ) ) ) ) : array();
if ( ! $task_ids ) {
    wp_send_json_error( 'Missing task ids', 400 );
}
$task_priority   = isset($_POST['wppm_task_priority']) ? intval(sanitize_text_field($_POST['wppm_task_priority'])) : 0 ;
if(!empty($task_ids)){
    foreach($task_ids as $task_id){
        $task_data = $wppmfunction->get_task($task_id);
        $wppm_current_user_capability = get_user_meta( $current_user->ID, 'wppm_capability', true );
        if ((($current_user->ID && $current_user->has_cap('manage_options')) || $wppmfunction->has_permission('change_task_details',$task_id))) {
            if( !$task_priority ){
                die();
            }
            $old_priority_id   	= $task_data['priority'];

            if( $task_priority && $task_priority != $old_priority_id){
                $wppmfunction->change_priority( $task_id, $task_priority);
                $change_priority_value = array('prev_prio'=>"$task_data[priority]",'new_prio'=>"$task_priority");
                $change_priority_obj = serialize($change_priority_value);
                $log_values = array('task_id'=>$task_id,'body'=>$change_priority_obj,'attachment_ids'=>"",'create_time'=>date("Y-m-d h:i:sa"),'created_by'=>$current_user->ID );
                $wpdb->insert($wpdb->prefix . 'wppm_task_comment',$log_values);
                $log_id = $wpdb->insert_id;
                $task_log_values = array('task_id'=>$task_id,'comment_id'=>$log_id,'comment_type'=>'change_task_priority');
                $wpdb->insert($wpdb->prefix . 'wppm_task_comment_meta',$task_log_values);
            }
        }
    }
}
do_action('wppm_after_set_bulk_change_task_priority',$task_ids, $task_priority);
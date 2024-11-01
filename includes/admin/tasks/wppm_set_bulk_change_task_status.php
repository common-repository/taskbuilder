<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
global $wpdb,$wppmfunction,$current_user;
if ( check_ajax_referer( 'wppm_set_bulk_change_task_status', '_ajax_nonce', false ) != 1 ) {
    wp_send_json_error( 'Unauthorised request!', 401 );
}

$task_ids = isset( $_POST['task_ids'] ) ? array_filter( array_map( 'intval', explode( ',', sanitize_text_field( wp_unslash( $_POST['task_ids'] ) ) ) ) ) : array();
if ( ! $task_ids ) {
    wp_send_json_error( 'Missing task ids', 400 );
}

$task_status = isset( $_POST['wppm_task_status']) ? intval(sanitize_text_field($_POST['wppm_task_status'])) : ''; 
if(!empty($task_ids)){
    foreach($task_ids as $task_id){
        $task_data = $wppmfunction->get_task($task_id);
        $wppm_current_user_capability = get_user_meta( $current_user->ID, 'wppm_capability', true );
        if ((($current_user->ID && $current_user->has_cap('manage_options')) || $wppmfunction->has_permission('change_status',$task_id))) {
            $status_id   = isset($_POST['wppm_task_status']) ? intval(sanitize_text_field($_POST['wppm_task_status'])) : 0 ;
            if( !$status_id ){
                die();
            }
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
        }
    }
}
do_action('wppm_after_set_bulk_change_task_status',$task_ids, $task_status);
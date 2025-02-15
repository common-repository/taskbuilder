<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wppmfunction ,$wpdb;
$task_id  = isset($_POST['task_id']) ? intval(sanitize_text_field($_POST['task_id'])) : 0 ;
$thread_id  = isset($_POST['comment_id']) ? intval(sanitize_text_field($_POST['comment_id'])) : 0 ;
$task_comment = $wppmfunction->get_task_comment($thread_id);
if (!(($current_user->ID && $current_user->has_cap('manage_options')) || $wppmfunction->has_comment_permission('delete_task_thread',$task_id,$thread_id))) {exit;}
if ( check_ajax_referer( 'wppm_set_delete_thread', '_ajax_nonce', false ) != 1 ) {
	wp_send_json_error( 'Unauthorised request!', 401 );
}
$sql="SELECT attachment_ids FROM {$wpdb->prefix}wppm_task_comment WHERE id ='$thread_id'";
$thread_attachment_ids = $wpdb->get_results( $sql );
if(!empty($thread_attachment_ids) ){
	foreach ($thread_attachment_ids as $thread_attachment_id){
		$attachment_ids_temp=array();
		if($thread_attachment_id->attachment_ids){
			$attachment_ids_temp =  explode(',', $thread_attachment_id->attachment_ids);
		}
		$attachment_ids=$attachment_ids_temp;
		if(!empty($attachment_ids_temp)){
			foreach ($attachment_ids_temp as $attachment_id){
				$sql="SELECT * FROM {$wpdb->prefix}wppm_attachments WHERE id = '$attachment_id'";
				$result=$wpdb->get_row($sql);
				if(!empty($result)){
					$sql_query="SELECT file_path FROM {$wpdb->prefix}wppm_attachments WHERE file_path ='".$result->file_path."'";
					$attach_result=$wpdb->get_results($sql_query);
					$result_count = count($attach_result);
					if(file_exists($result->file_path) && $result_count < 2)
					{
						unlink($result->file_path);
					}
					$wpdb->delete($wpdb->prefix.'wppm_attachments', array( 'id' => $attachment_id));
				}
			}
		}
	}
}
$cur_user = get_userdata($current_user->ID);
$thread_id = esc_sql($thread_id);
$log_values = array('task_id'=>$task_id,'body'=>'This comment was deleted by '.$cur_user->display_name,'attachment_ids'=>"" );
$wpdb->update($wpdb->prefix.'wppm_task_comment', $log_values, array('id'=>$thread_id));
$task_log_values = array('task_id'=>$task_id,'comment_id'=>$thread_id,'comment_type'=>'delete_task_comment');
$comment_meta = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wppm_task_comment_meta WHERE comment_id='$thread_id' ");
if(!empty($comment_meta)){
	$wpdb->update($wpdb->prefix . 'wppm_task_comment_meta',$task_log_values,array('id'=>$comment_meta->id));
}else{
	$wpdb->insert($wpdb->prefix . 'wppm_task_comment_meta',$task_log_values);

}
?>
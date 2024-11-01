<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wppmfunction ,$wpdb;
if ( check_ajax_referer( 'wppm_set_delete_proj_thread', '_ajax_nonce', false ) != 1 ) {
    wp_send_json_error( 'Unauthorised request!', 401 );
}
$proj_id  = isset($_POST['proj_id']) ? intval(sanitize_text_field($_POST['proj_id'])) : 0 ;
$thread_id  = isset($_POST['comment_id']) ? intval(sanitize_text_field($_POST['comment_id'])) : 0 ;
$project_comment = $wppmfunction->get_proj_comment($thread_id);
if (!(($current_user->ID && $current_user->has_cap('manage_options')) || $wppmfunction->has_proj_comment_permission('delete_proj_thread',$proj_id,$thread_id))) {exit;}
$sql="SELECT attachment_ids FROM {$wpdb->prefix}wppm_project_comment WHERE id = '$thread_id'";
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
				$attachment_id = esc_sql($attachment_id);
				$sql="SELECT * FROM {$wpdb->prefix}wppm_attachments WHERE id = '$attachment_id'";
				$result=$wpdb->get_row($sql);
				if(!empty($result)){
					$res_file_path = esc_sql($result->file_path);
					$sql_query="SELECT file_path FROM {$wpdb->prefix}wppm_attachments WHERE file_path ='".$res_file_path."'";
					$attach_result=$wpdb->get_results($sql_query);
					$result_count = count($attach_result);
					if(file_exists($result->file_path) && $result_count < 2)
					{
						unlink($result->file_path);
					}
					$wpdb->delete($wpdb->prefix.'wppm_attachments', array( 'id' => "$attachment_id"));
				}
			}
		}
	}
}

$cur_user = get_userdata($current_user->ID);
$log_values = array('proj_id'=>$proj_id,'body'=>'This comment was deleted by '.$cur_user->display_name,'attachment_ids'=>"");
$wpdb->update($wpdb->prefix.'wppm_project_comment', $log_values, array('id'=>$thread_id));
$comment_meta = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wppm_project_comment_meta WHERE comment_id='$thread_id' ");
$proj_log_values = array('proj_id'=>$proj_id,'comment_id'=>$thread_id,'comment_type'=>'delete_proj_comment');
if(!empty($comment_meta)){
	$wpdb->update($wpdb->prefix . 'wppm_project_comment_meta',$proj_log_values,array('id'=>$comment_meta->id));
} else{
	$wpdb->insert($wpdb->prefix . 'wppm_project_comment_meta',$proj_log_values);
}
do_action('wppm_after_delete_project_thread',$thread_id,$proj_id);

?>
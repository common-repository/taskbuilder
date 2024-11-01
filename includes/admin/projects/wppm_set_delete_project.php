<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wppmfunction ,$wpdb;
$project_id  = isset($_POST['project_id']) ? intval(sanitize_text_field($_POST['project_id'])) : 0 ;
$project = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wppm_project where id = '$project_id'" );
$wppm_current_user_capability = get_user_meta( $current_user->ID, 'wppm_capability', true );
if (!(($current_user->ID && $current_user->has_cap('manage_options')) || $wppmfunction->has_project_permission('delete_project',$project_id) || ($project->created_by==$current_user->ID && $wppm_current_user_capability == 'wppm_manager') || ($current_user->ID && $current_user->has_cap('wppm_admin')))) {exit;}

if ( check_ajax_referer( 'wppm_set_delete_project', '_ajax_nonce', false ) != 1 ) {
    wp_send_json_error( 'Unauthorised request!', 401 );
}

$tasks = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wppm_task where project = '$project_id'");
/*************delete project********/
    $wpdb->delete($wpdb->prefix.'wppm_project',array('id'=>"$project_id"));

/*************delete project comment********/
$sql="SELECT attachment_ids FROM {$wpdb->prefix}wppm_project_comment WHERE proj_id =".$project_id;
$proj_comm_attachment_ids= $wpdb->get_results( $sql );
if(!empty($proj_comm_attachment_ids)){
    foreach ($proj_comm_attachment_ids as $proj_attachment_id){
        $proj_attachment_ids_temp=array();
        if($proj_attachment_id->attachment_ids){
            $proj_attachment_ids_temp=  explode(',', $proj_attachment_id->attachment_ids);
        }
        foreach ($proj_attachment_ids_temp as $proj_attachment_id){
            $sql="SELECT * FROM {$wpdb->prefix}wppm_attachments WHERE id =".$proj_attachment_id;
            $att_result=$wpdb->get_row($sql);
            if(!empty($att_result)){
                $sql_query="SELECT file_path FROM {$wpdb->prefix}wppm_attachments WHERE file_path ='".$att_result->file_path."'";
                $proj_attach_result=$wpdb->get_results($sql_query);
                $proj_result_count = count($proj_attach_result);
                if(file_exists($att_result->file_path) && $proj_result_count < 2)
                {
                    unlink($att_result->file_path);
                }
                $wpdb->delete($wpdb->prefix.'wppm_attachments', array( 'id' => $proj_attachment_id));
            }
        }
    }
}
$wpdb->delete($wpdb->prefix.'wppm_project_comment',array('proj_id'=>$project_id));
$wpdb->delete($wpdb->prefix.'wppm_project_meta',array('project_id'=>$project_id));
$wpdb->delete($wpdb->prefix.'wppm_project_comment_meta',array('proj_id'=>$project_id));

/*************delete project's tasks and comments********/
foreach($tasks as $task){
    $sql="SELECT attachment_ids FROM {$wpdb->prefix}wppm_task_comment WHERE task_id =".esc_sql($task->id);
    $thread_attachment_ids= $wpdb->get_results( $sql );
    $tid = esc_sql($task->id);
    /***************************Code for deleting attachment files****************************************************/
    if(!empty($thread_attachment_ids)){
        foreach ($thread_attachment_ids as $thread_attachment_id){
            $attachment_ids_temp=array();
            if($thread_attachment_id->attachment_ids){
                $attachment_ids_temp=  explode(',', $thread_attachment_id->attachment_ids);
            }
            //$attachment_ids=$attachment_ids_temp;
            foreach ($attachment_ids_temp as $attachment_id){
                $attachment_id = esc_sql($attachment_id);
                $sql="SELECT * FROM {$wpdb->prefix}wppm_attachments WHERE id = '$attachment_id'";
                $result=$wpdb->get_row($sql);
                if(!empty($result)){
                    $sql_query="SELECT file_path FROM {$wpdb->prefix}wppm_attachments WHERE file_path ='".esc_sql($result->file_path)."'";
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
    /***************************Code for deleting checklists****************************************************/
    $sql="SELECT id FROM {$wpdb->prefix}wppm_checklist WHERE task_id = '$tid'";
    $checklists = $wpdb->get_results( $sql );
    if(!empty($checklists)){
        foreach($checklists as $checklist){
            $chk_array = (array) $checklist;
            $chk_id = esc_sql($chk_array['id']);
            $sql="SELECT id FROM {$wpdb->prefix}wppm_checklist_items WHERE checklist_id ='$chk_id '";
            $checklist_items = $wpdb->get_results( $sql );
            if(!empty($checklist_items)){
                foreach($checklist_items as $ch_item){
                    $chk_items_array = (array) $ch_item;
                    $chk_item_id = esc_sql($chk_items_array['id']);
                    $wpdb->delete($wpdb->prefix.'wppm_checklist_items',array('id'=>"$chk_item_id"));
                }
            }
            $wpdb->delete($wpdb->prefix.'wppm_checklist',array('id'=> "$chk_id"));
        }
    }
    $wpdb->delete($wpdb->prefix.'wppm_task',array('project'=>$project_id));
    $wpdb->delete($wpdb->prefix.'wppm_task_meta',array('task_id'=>$tid));
    $wpdb->delete($wpdb->prefix.'wppm_task_comment',array('task_id'=>$tid));
    $wpdb->delete($wpdb->prefix.'wppm_task_comment_meta',array('task_id'=>$tid));
}

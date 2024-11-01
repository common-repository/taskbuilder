<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
global $wpdb,$wppmfunction,$current_user;
if ( check_ajax_referer( 'wppm_set_project_users', '_ajax_nonce', false ) != 1 ) {
  wp_send_json_error( 'Unauthorised request!', 401 );
}
$proj_id  = isset($_POST['proj_id']) ? intval(sanitize_text_field($_POST['proj_id'])) : '' ;
$project_data = $wppmfunction->get_project($proj_id);
$wppm_current_user_capability = get_user_meta( $current_user->ID, 'wppm_capability', true );
if (!(($current_user->ID && $current_user->has_cap('manage_options')) || $wppmfunction->has_project_permission('assign_project_users',$proj_id) || ($project_data['created_by']==$current_user->ID && $wppm_current_user_capability == 'wppm_manager') || ($current_user->ID && $current_user->has_cap('wppm_admin')))) {exit;}
$wppm_users_role = get_option('wppm_user_role');
$prev_assign_users = $project_data['users'];
if(!empty($prev_assign_users )){
  $prev_assign_users = explode(",",$prev_assign_users);
}
$users = (!empty($_POST['user_names']))? $wppmfunction->sanitize_array($_POST['user_names']):"";
$prev_assgn_user_meta = $wppmfunction->get_project_meta($proj_id,'prev_assigned_users');
$wppmfunction->delete_project_meta($proj_id,'prev_assigned_users');
$task_users = $wpdb->get_results("SELECT id,users FROM {$wpdb->prefix}wppm_task WHERE project = '$proj_id'");
if(!empty($prev_assign_users)) {
  foreach($prev_assign_users as $puser){
    $wppmfunction->add_project_meta($proj_id,'prev_assigned_users',$puser);
    if(!empty($users) && !in_array($puser,$users)){
      if(!empty($task_users)){
        foreach($task_users as $tuser){
          if(!empty($tuser)){
            $wppm_tuser = explode(',',$tuser->users);
            $tuser_id = esc_sql($tuser->id);
            if (($key = array_search($puser, $wppm_tuser)) !== false) {
                unset($wppm_tuser[$key]);
                if(!empty($wppm_tuser)){
                  $wppmtuser = implode(',',$wppm_tuser);
                  $wppmtuser = esc_sql($wppmtuser);
                  $value=array(
                    'users'=>  $wppmtuser
                  );
                } elseif(empty($wppm_tuser)){
                  $value=array(
                    'users'=> ''
                  );
                }
                $wpdb->update($wpdb->prefix.'wppm_task', $value, array('id'=>"$tuser_id"));
            }
          }
        }
      }
    }
    elseif(empty($users) && !empty($task_users)){
      foreach($task_users as $tuser){
        if(!empty($tuser)){
          $wppm_tuser = explode(',',$tuser->users);
          $tuser_id = esc_sql($tuser->id);
          if (($key = array_search($puser, $wppm_tuser)) !== false) {
              unset($wppm_tuser[$key]);
              if(!empty($wppm_tuser)){
                $wppmtuser = implode(',',$wppm_tuser);
                $wppmtuser = esc_sql($wppmtuser); 
                $value=array(
                  'users'=>  $wppmtuser
                );
              } elseif(empty($wppm_tuser)){
                $value=array(
                  'users'=> ''
                );
              }
              $wpdb->update($wpdb->prefix.'wppm_task', $value, array('id'=>$tuser_id));
          }
        }
      }
    }
  }
}
if(!empty($users)){
  $users = array_unique($users);
  foreach($users as $user){
    $user_role = sanitize_text_field($_POST['wppm_select_user_role_'.$user]);
    $user = esc_sql($user);
    $project_user = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wppm_project_users WHERE proj_id = '$proj_id' AND user_id = '$user'");
    if(!empty($project_user)){
      $values=array(
        'role_id'=> $user_role,
        'assigned_by'=> $current_user->ID
      );
      $proj_user_id = esc_sql($project_user->id);
      $wpdb->update($wpdb->prefix.'wppm_project_users', $values, array('id'=>"$proj_user_id"));
    }else{
      $wpdb->insert( 
        $wpdb->prefix . 'wppm_project_users', 
        array(
          'proj_id' => $proj_id,
          'user_id' => $user,
          'role_id' => $user_role,
          'assigned_by' => $current_user->ID
      ) );
    }
  }
  $pusers = implode(",",$users);
  if($project_data['users'] != $pusers){
    $pvalues=array(
      'users'=> esc_sql($pusers)
    );
    $wpdb->update($wpdb->prefix.'wppm_project', $pvalues, array('id'=>"$proj_id"));
  }
} else{
    $pvalues = array(
      'users'=>''
    );
    $wpdb->update($wpdb->prefix.'wppm_project', $pvalues, array('id'=>$proj_id));
}
$change_assign_user_value = array('prev_assign_user'=>"$project_data[users]",'new_assign_user'=>"$pusers");
$change_assign_user_obj = serialize($change_assign_user_value);
$log_values = array('proj_id'=>$proj_id,'body'=>$change_assign_user_obj,'attachment_ids'=>"",'create_time'=>date("Y-m-d h:i:sa"),'created_by'=>$current_user->ID );
$wpdb->insert($wpdb->prefix . 'wppm_project_comment',$log_values);
$log_id = $wpdb->insert_id;
$proj_log_values = array('proj_id'=>$proj_id,'comment_id'=>$log_id,'comment_type'=>'change_assign_user');
$wpdb->insert($wpdb->prefix . 'wppm_project_comment_meta',$proj_log_values);
do_action('wppm_set_project_users', $proj_id);
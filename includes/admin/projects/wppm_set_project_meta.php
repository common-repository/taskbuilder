<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
global $wpdb,$wppmfunction,$current_user;
$project_id = intval(sanitize_text_field($project_id));
$project_data = $wppmfunction->get_project($project_id);
$users = explode(',',$project_data['users']);
$users = array_unique($users);
if(!empty($users)){
  foreach($users as $user){
    if(!empty($user)){
      $user_role = sanitize_text_field($_POST['wppm_select_user_role_'.$user]);
      $user = esc_sql($user);
      $project_user = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wppm_project_users WHERE proj_id = '$project_id' AND user_id = '$user'");
      if(!empty($project_user)){
        $project_user_id = esc_sql($project_user->id);
        $user_role = esc_sql($user_role);
        $values=array(
          'role_id'=> $user_role,
          'assigned_by'=> esc_sql($current_user->ID)
        );
        $wpdb->update($wpdb->prefix.'wppm_project_users', $values, array('id'=>$project_user_id));
      }else{
        $wpdb->insert( 
          $wpdb->prefix . 'wppm_project_users', 
          array(
            'proj_id' => $project_id,
            'user_id' => $user,
            'role_id' => $user_role,
            'assigned_by' => esc_sql($current_user->ID)
        ) );
      }
    }
  }
}
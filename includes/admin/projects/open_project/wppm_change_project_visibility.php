<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
} 
global $current_user,$wpdb,$wppmfunction;
if ( check_ajax_referer( 'wppm_change_project_visibility', '_ajax_nonce', false ) != 1 ) {
    wp_send_json_error( 'Unauthorised request!', 401 );
}
$project_id  = isset($_POST['project_id']) ? sanitize_text_field($_POST['project_id']) : '' ;
$project_id = esc_sql($project_id);
$project = $wppmfunction->get_project($project_id);
$wppm_current_user_capability = get_user_meta( $current_user->ID, 'wppm_capability', true );
if (!(($current_user->ID && $current_user->has_cap('manage_options')) || ($current_user->ID && $current_user->has_cap('wppm_admin')) || ($project['created_by']==$current_user->ID && $wppm_current_user_capability == 'wppm_manager'))) {exit;}
$wppm_project_visibility = isset($_POST['project_visibility']) ? sanitize_text_field($_POST['project_visibility']):"0";
$id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}wppm_project_meta WHERE project_id = '$project_id' AND meta_key ='public_project'");
if(empty($id )){
    $wppmfunction->add_project_meta($project_id,'public_project',$wppm_project_visibility);
}elseif(!empty($id)){
    $values = array(
        'meta_value'=>$wppm_project_visibility
    );
    $wpdb->update($wpdb->prefix.'wppm_project_meta', $values,array('id'=>intval($id)));
}
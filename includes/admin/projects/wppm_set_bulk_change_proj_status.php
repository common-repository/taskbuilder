<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
global $wpdb,$wppmfunction,$current_user;
if ( check_ajax_referer( 'wppm_set_bulk_change_project_status', '_ajax_nonce', false ) != 1 ) {
    wp_send_json_error( 'Unauthorised request!', 401 );
}

$proj_ids = isset( $_POST['proj_ids'] ) ? array_filter( array_map( 'intval', explode( ',', sanitize_text_field( wp_unslash( $_POST['proj_ids'] ) ) ) ) ) : array();
if ( ! $proj_ids ) {
    wp_send_json_error( 'Missing project ids', 400 );
}

$proj_status = isset( $_POST['wppm_project_status']) ? intval(sanitize_text_field($_POST['wppm_project_status'])) : ''; 
if(!empty($proj_ids)){
    foreach($proj_ids as $proj_id){
        $project_data = $wppmfunction->get_project($proj_id);
        $wppm_current_user_capability = get_user_meta( $current_user->ID, 'wppm_capability', true );
        if ((($current_user->ID && $current_user->has_cap('manage_options')) || $wppmfunction->has_project_permission('change_project_status',$proj_id) || ($project_data['created_by']==$current_user->ID && $wppm_current_user_capability=='wppm_manager') || $wppm_current_user_capability=='wppm_admin')) {

            $status_id   = isset($_POST['wppm_project_status']) ? intval(sanitize_text_field($_POST['wppm_project_status'])) : 0 ;
            if( !$status_id ){
            die();
            }
            $old_status_id   	= $project_data['status'];

            if($status_id && $status_id!=$old_status_id){
                $wppmfunction->change_project_status( $proj_id, $status_id);
            }
        }
    }
}
do_action('wppm_after_set_bulk_change_project_status',$proj_ids, $proj_status);
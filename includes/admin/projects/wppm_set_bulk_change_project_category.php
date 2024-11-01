<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
global $wpdb,$wppmfunction,$current_user;
if ( check_ajax_referer( 'wppm_set_bulk_change_project_category', '_ajax_nonce', false ) != 1 ) {
    wp_send_json_error( 'Unauthorised request!', 401 );
}

$proj_ids = isset( $_POST['proj_ids'] ) ? array_filter( array_map( 'intval', explode( ',', sanitize_text_field( wp_unslash( $_POST['proj_ids'] ) ) ) ) ) : array();
if ( ! $proj_ids ) {
    wp_send_json_error( 'Missing project ids', 400 );
}

$proj_category = isset($_POST['wppm_project_category']) ? intval(sanitize_text_field($_POST['wppm_project_category'])) : 0 ;

if(!empty($proj_ids)){
    foreach($proj_ids as $proj_id){
        $project_data = $wppmfunction->get_project($proj_id);
        $wppm_current_user_capability = get_user_meta( $current_user->ID, 'wppm_capability', true );
        if ((($current_user->ID && $current_user->has_cap('manage_options')) || $wppmfunction->has_project_permission('change_project_details',$proj_id) || ($project_data['created_by']==$current_user->ID && $wppm_current_user_capability=='wppm_manager') || $wppm_current_user_capability=='wppm_admin')) {
            if( !$proj_category ){
                die();
            }
            if( $proj_category && $proj_category != $project_data['cat_id']){
                $wppmfunction->change_category( $proj_id, $proj_category);
                $change_category_value = array('prev_cat'=>"$project_data[cat_id]",'new_cat'=>"$proj_category");
                $change_category_obj = serialize($change_category_value);
                $log_values = array('proj_id'=>$proj_id,'body'=>$change_category_obj,'attachment_ids'=>"",'create_time'=>date("Y-m-d h:i:sa"),'created_by'=>$current_user->ID );
                $wpdb->insert($wpdb->prefix . 'wppm_project_comment',$log_values);
                $log_id = $wpdb->insert_id;
                $proj_log_values = array('proj_id'=>$proj_id,'comment_id'=>$log_id,'comment_type'=>'change_proj_cat');
                $wpdb->insert($wpdb->prefix . 'wppm_project_comment_meta',$proj_log_values);
            }
        }
    }
}
do_action('wppm_after_set_bulk_change_project_status',$proj_ids, $proj_category);
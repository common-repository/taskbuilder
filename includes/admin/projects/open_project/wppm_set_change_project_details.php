<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
global $wpdb,$wppmfunction,$current_user;
if ( check_ajax_referer( 'wppm_set_change_project_details', '_ajax_nonce', false ) != 1 ) {
    wp_send_json_error( 'Unauthorised request!', 401 );
}
$project_id  = isset($_POST['project_id']) ? intval(sanitize_text_field($_POST['project_id'])) : '' ;
$project_data = $wppmfunction->get_project($project_id);
$wppm_current_user_capability = get_user_meta( $current_user->ID, 'wppm_capability', true );
if (!(($current_user->ID && $current_user->has_cap('manage_options')) || $wppmfunction->has_project_permission('change_project_details',$project_id) || ($project_data['created_by']==$current_user->ID && $wppm_current_user_capability=='wppm_manager') || $wppm_current_user_capability=='wppm_admin')) {exit;}
$project_label = isset($_POST['wppm_edit_project_label']) ? sanitize_text_field($_POST['wppm_edit_project_label']) : "" ;
$project_start_date = isset($_POST['wppm_edit_project_start_date']) ? sanitize_text_field($_POST['wppm_edit_project_start_date']) : "" ;
$project_end_date = isset($_POST['wppm_edit_project_end_date']) ? sanitize_text_field($_POST['wppm_edit_project_end_date']) : "" ;
$project_category = isset($_POST['wppm_edit_project_category']) ? intval(sanitize_text_field($_POST['wppm_edit_project_category'])) : "" ;
$allowed_tags = array( 'br' => array(), 'abbr' => array('title' => array(),), 'p' => array(), 'strong' => array(), 'a' => array('href' => array(), 'title' => array(),'target'=> array(), 'rel'=>array()),'em' =>array(),'span' =>array(), 'blockquote'=>array('cite'  => array(),),'div' => array('class' => array(),'title' => array(),'style' => array(),),'ul'=>array(),'li'=>array(),'ol'=>array(),'img' => array( 'alt'=> array(),'class' => array(),'height' => array(),'src'=> array(),'width'=> array(),));
$project_description = isset($_POST['wppm_edit_project_description']) ? wp_kses(htmlspecialchars_decode($_POST['wppm_edit_project_description'], ENT_QUOTES),$allowed_tags) : "" ;

if($project_label && $project_label != $project_data['project_name'] ){
    $wppmfunction->change_project_label( $project_id, $project_label);
}

if($project_start_date && $project_start_date != $project_data['start_date'] ){
    $wppmfunction->change_project_start_date( $project_id, $project_start_date);
}

if($project_end_date && $project_end_date != $project_data['end_date'] ){
    $wppmfunction->change_project_end_date( $project_id, $project_end_date);
}
$pcid = esc_sql($project_data['cat_id']);
$project_category = esc_sql($project_category);
if( $project_category && $project_category != $project_data['cat_id']){
	$wppmfunction->change_category( $project_id, $project_category);
    $change_category_value = array('prev_cat'=>"$pcid",'new_cat'=>"$project_category");
	$change_category_obj = serialize($change_category_value);
	$log_values = array('proj_id'=>$project_id,'body'=>$change_category_obj,'attachment_ids'=>"",'create_time'=>date("Y-m-d h:i:sa"),'created_by'=>$current_user->ID );
	$wpdb->insert($wpdb->prefix . 'wppm_project_comment',$log_values);
    $log_id = esc_sql($wpdb->insert_id);
	$proj_log_values = array('proj_id'=>"$project_id",'comment_id'=>"$log_id",'comment_type'=>'change_proj_cat');
	$wpdb->insert($wpdb->prefix . 'wppm_project_comment_meta',$proj_log_values);
}

if( $project_description && $project_description != $project_data['description']){
	$wppmfunction->change_project_description( $project_id, $project_description);
}
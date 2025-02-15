<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user,$wppmfunction;
if (!($current_user->ID && $current_user->has_cap('manage_options') || ($current_user->ID && $current_user->has_cap('wppm_admin')))) {
	exit;
}
if ( check_ajax_referer( 'wppm_set_general_settings', '_ajax_nonce', false ) != 1 ) {
	wp_send_json_error( 'Unauthorised request!', 401 );
}
$wppm_task_list_view = isset($_POST) && isset(($_POST['wppm_task_list_view'])) ? sanitize_text_field($_POST['wppm_task_list_view']) : '';
update_option('wppm_default_task_list_view',$wppm_task_list_view);
$wppm_project_time = isset($_POST) && isset(($_POST['wppm_project_time'])) ? sanitize_text_field($_POST['wppm_project_time']) : '1';
update_option('wppm_project_time',$wppm_project_time);
$wppm_default_project_date = isset($_POST) && isset(($_POST['wppm_default_project_date'])) ? sanitize_text_field($_POST['wppm_default_project_date']) : '1';
update_option('wppm_default_project_date',$wppm_default_project_date);
$wppm_default_task_date = isset($_POST) && isset(($_POST['wppm_default_task_date'])) ? sanitize_text_field($_POST['wppm_default_task_date']) : '1';
update_option('wppm_default_task_date',$wppm_default_task_date);
$wppm_edit_tasks_permission = isset($_POST) && isset(($_POST['wppm_edit_tasks_permission'])) ? sanitize_text_field($_POST['wppm_edit_tasks_permission']) : '0';
update_option('wppm_default_edit_tasks_permission',$wppm_edit_tasks_permission);
$wppm_task_time = isset($_POST) && isset(($_POST['wppm_task_time'])) ? sanitize_text_field($_POST['wppm_task_time']) : '1';
update_option('wppm_task_time',$wppm_task_time);
$wppm_proj_status = isset($_POST) && isset(($_POST['wppm_default_proj_status'])) ? sanitize_text_field($_POST['wppm_default_proj_status']) : '1';
update_option('wppm_default_project_status',$wppm_proj_status);
$wppm_task_status = isset($_POST) && isset(($_POST['wppm_default_task_status'])) ? sanitize_text_field($_POST['wppm_default_task_status']) : '1';
update_option('wppm_default_task_status',$wppm_task_status);
$wppm_tinymce_visibility_open_project = isset($_POST) && isset(($_POST['wppm_tinymce_visibility_open_project'])) ? sanitize_text_field($_POST['wppm_tinymce_visibility_open_project']) : '1';
update_option('wppm_tinymce_visibility_open_project',$wppm_tinymce_visibility_open_project);
$wppm_tinymce_visibility_open_task = isset($_POST) && isset(($_POST['wppm_tinymce_visibility_open_task'])) ? sanitize_text_field($_POST['wppm_tinymce_visibility_open_task']) : '1';
update_option('wppm_tinymce_visibility_open_task',$wppm_tinymce_visibility_open_task);
do_action('wppm_set_general_settings');

echo '{ "sucess_status":"1","messege":"'.__('Settings saved.','taskbuilder').'" }';
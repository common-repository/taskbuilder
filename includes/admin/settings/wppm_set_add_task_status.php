<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wpdb;
if (!($current_user->ID && $current_user->has_cap('manage_options') || ($current_user->ID && $current_user->has_cap('wppm_admin')))) {exit;}

if ( check_ajax_referer( 'wppm_set_add_task_status', '_ajax_nonce', false ) != 1 ) {
  wp_send_json_error( 'Unauthorised request!', 401 );
}

$status_name = isset($_POST) && isset($_POST['status_name']) ? sanitize_text_field($_POST['status_name']) : '';
if (!$status_name) {exit;}

$status_color = isset($_POST) && isset($_POST['status_color']) ? sanitize_text_field($_POST['status_color']) : '';
if (!$status_color) {exit;}

$status_bg_color = isset($_POST) && isset($_POST['status_bg_color']) ? sanitize_text_field($_POST['status_bg_color']) : '';
if (!$status_bg_color) {exit;}

if ($status_color==$status_bg_color) {
  echo '{ "sucess_status":"0","messege":"'.__('Status color and background color should not be same.','taskbuilder').'" }';
  die();
}
$load_order = $wpdb->get_var("select max(load_order) from {$wpdb->prefix}wppm_task_statuses");
$values=array(
  'name'=>esc_sql($status_name),
  'color'=>esc_sql($status_color),
  'bg_color'=>esc_sql($status_bg_color),
  'load_order'=> esc_sql(++$load_order)
);
$wpdb->insert($wpdb->prefix.'wppm_task_statuses',$values);
echo '{ "sucess_status":"1","messege":"'.__('Status added successfully.','taskbuilder').'" }';


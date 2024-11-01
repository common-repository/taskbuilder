<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

global $current_user, $wpdb;

if (!($current_user->ID && $current_user->has_cap('manage_options') || ($current_user->ID && $current_user->has_cap('wppm_admin')))) {exit;}

if ( check_ajax_referer( 'wppm_set_edit_status', '_ajax_nonce', false ) != 1 ) {
  wp_send_json_error( 'Unauthorised request!', 401 );
}
$status_id = isset($_POST) && isset($_POST['status_id']) ? intval(sanitize_text_field($_POST['status_id'])) : '';
if (!$status_id) {exit;}
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
$values= array(
  'name'=>esc_sql($status_name),
  'color'=>esc_sql($status_color),
  'bg_color'=>esc_sql($status_bg_color)
);
$wpdb->update($wpdb->prefix.'wppm_project_statuses',$values,array('id'=>intval("$status_id"))); 
echo '{ "sucess_status":"1","messege":"Success" }';
?>
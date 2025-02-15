<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

global $current_user,$wpdb,$wppmfunction;

if (!($current_user->ID && $current_user->has_cap('manage_options') || ($current_user->ID && $current_user->has_cap('wppm_admin')))) {
	exit;
}
if ( check_ajax_referer( 'wppm_reset_ap_proj_list', '_ajax_nonce', false ) != 1 ) {
  wp_send_json_error( 'Unauthorised request!', 401 );
}
update_option(
    'wppm-ap-project-list',
    array(
        'list-header-background-color'     => '#304FFE',
        'list-header-text-color'           => '#fff',
        'list-item-odd-background-color'   => '#fff',
        'list-item-odd-text-color'         => '#2C3E50',
        'list-item-even-background-color'  => '#F2F2F2',
        'list-item-even-text-color'        => '#2C3E50',
        'list-item-hover-background-color' => '#F5F5F5',
        'list-item-hover-text-color'       => '#2C3E50'
    )
);
wp_die();
?>
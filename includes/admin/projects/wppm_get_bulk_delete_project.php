<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
global $wpdb,$wppmfunction,$current_user;
$proj_ids  = isset($_POST['proj_ids']) ? $wppmfunction->sanitize_array($_POST['proj_ids']) : '' ;
$wppm_current_user_capability = get_user_meta( $current_user->ID, 'wppm_capability', true );
ob_start();
?>
<form id="frm_delete_bulk_project" method="post">
    <div class="form-group">
        <p><?php echo esc_html_e('Are you sure to delete these projects?','taskbuilder');?></p>
    </div>
    <input type="hidden" name="action" value="wppm_set_delete_bulk_projects" />
    <input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wppm_set_delete_bulk_projects' ) ); ?>">
    <input type="hidden" name="proj_ids" value="<?php echo esc_attr( implode( ',', $proj_ids ) ); ?>" />
</form>
<?php
$body = ob_get_clean();
ob_start();
?>
<button type="button" class="btn wppm_popup_close"  onclick="wppm_modal_close();"><?php echo esc_html_e('Cancel','taskbuilder');?></button>
<button type="button" class="btn wppm_popup_action" type="submit" onclick="wppm_set_delete_bulk_projects();"><?php echo esc_html_e('Confirm','taskbuilder');?></button>
<?php
$footer = ob_get_clean();
$response = array(
    'body'      => $body,
    'footer'    => $footer
);
echo json_encode($response);

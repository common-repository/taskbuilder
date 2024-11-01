<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $current_user,$wppmfunction,$wpdb;
$id = isset($_POST['id']) ? intval(sanitize_text_field($_POST['id'])) : '0';
$project = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wppm_project where id = '$id'" );
$wppm_current_user_capability = get_user_meta( $current_user->ID, 'wppm_capability', true );
if (!(($current_user->ID && $current_user->has_cap('manage_options')) || $wppmfunction->has_project_permission('delete_project',$id) || ($project->created_by==$current_user->ID && $wppm_current_user_capability == 'wppm_manager') || ($current_user->ID && $current_user->has_cap('wppm_admin')))) {exit;}
ob_start();
?>
<form id="frm_delete_project" method="post">
    <div class="form-group">
        <p><?php echo esc_html_e('Are you sure to delete this project?','taskbuilder');?></p>
    </div>
    <input type="hidden" name="action" value="wppm_set_delete_project" />
    <input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wppm_set_delete_project' ) ); ?>">
    <input type="hidden" name="project_id" value="<?php echo htmlentities(esc_attr($id))?>" />
</form>
<?php
$body = ob_get_clean();
ob_start();
?>
<button type="button" class="btn wppm_popup_close"  onclick="wppm_modal_close();"><?php echo esc_html_e('Cancel','taskbuilder');?></button>
<button type="button" class="btn wppm_popup_action" type="submit" onclick="wppm_set_delete_project();"><?php echo esc_html_e('Confirm','taskbuilder');?></button>
<?php
$footer = ob_get_clean();
$response = array(
    'body'      => $body,
    'footer'    => $footer
);
echo json_encode($response);

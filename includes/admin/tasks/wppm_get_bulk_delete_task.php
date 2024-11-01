<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
global $wpdb,$wppmfunction,$current_user;
$task_ids  = isset($_POST['task_ids']) ? $wppmfunction->sanitize_array($_POST['task_ids']) : '' ;
$wppm_users_role = get_option('wppm_user_role');
ob_start();
?>
<form id="frm_delete_bulk_tasks">
    <div class="form-group">
        <p><?php echo esc_html_e('Are you sure to delete these tasks?','taskbuilder');?></p>
    </div>
    <input type="hidden" name="action" value="wppm_set_delete_bulk_tasks" />
    <input type="hidden" name="_ajax_nonce" value="<?php echo wp_create_nonce('wppm_set_delete_bulk_tasks')?>">
    <input type="hidden" id="wppm_task_ids" name="task_ids" value="<?php echo esc_attr( implode( ',', $task_ids ) ); ?>" />
</form>
<?php
$body = ob_get_clean();
ob_start();
?>
<button type="button" class="btn wppm_popup_close"  onclick="wppm_modal_close();"><?php echo esc_html_e('Cancel','taskbuilder');?></button>
<button type="button" class="btn wppm_popup_action" onclick="wppm_set_delete_bulk_tasks();"><?php echo esc_html_e('Confirm','taskbuilder');?></button>
<?php
$footer = ob_get_clean();
$response = array(
    'body'      => $body,
    'footer'    => $footer
);
echo json_encode($response);
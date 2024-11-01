<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
global $wpdb,$wppmfunction,$current_user;
$task_ids  = isset($_POST['task_ids']) ? $wppmfunction->sanitize_array($_POST['task_ids']) : '' ;
$statuses = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wppm_task_statuses");
ob_start();
?>
<form id="frm_get_bulk_task_change_status" method="post">
	<div class="form-group">
		<label for="wppm_task_status" class="wppm_task_status"><?php echo esc_html_e('Task Status','taskbuilder');?></label>
		<select class="form-control" name="wppm_task_status">
			<?php
			if(!empty($statuses)){
				foreach ( $statuses as $status ) :
					echo '<option value="'.esc_attr($status->id).'">'.esc_html($status->name).'</option>';
				endforeach;
			}
			?>
		</select>
	</div><?php
        do_action('wppm_after_edit_bulk_change_task_status',$task_ids); ?>
        <input type="hidden" name="action" value="wppm_set_bulk_change_task_status" />
        <input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wppm_set_bulk_change_task_status' ) ); ?>">
        <input type="hidden" id="wppm_task_ids" name="task_ids" value="<?php echo esc_attr( implode( ',', $task_ids ) ); ?>" />
</form>
<?php
$body = ob_get_clean();

ob_start();
?>
<button type="button" class="btn wppm_popup_close" onclick="wppm_modal_close();"><?php echo esc_html_e('Close','taskbuilder');?></button>
<button type="button" class="btn wppm_popup_action" onclick="wppm_set_bulk_change_task_status();"><?php echo esc_html_e('Save','taskbuilder');?></button>
<?php
$footer = ob_get_clean();

$output = array(
  'body'   => $body,
  'footer' => $footer
);

echo json_encode($output);
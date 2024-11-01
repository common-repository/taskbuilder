<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
global $wpdb,$wppmfunction,$current_user;
$proj_ids  = isset($_POST['proj_ids']) ? $wppmfunction->sanitize_array($_POST['proj_ids']) : '' ;
$categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wppm_project_categories");
ob_start();
?>
<form id="frm_get_bulk_project_change_category" method="post">
	<div class="form-group">
		<label for="wppm_project_category" class="wppm_project_category"><?php echo esc_html_e('Project Category','taskbuilder');?></label>
		<select class="form-control" name="wppm_project_category">
			<?php
			if(!empty($categories)){
				foreach ( $categories as $cat ) :
					echo '<option value="'.esc_attr($cat->id).'">'.esc_html($cat->name).'</option>';
				endforeach;
			}
			?>
		</select>
	</div><?php
        do_action('wppm_after_edit_bulk_change_project_category',$proj_ids); ?>
        <input type="hidden" name="action" value="wppm_set_bulk_change_project_category" />
        <input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wppm_set_bulk_change_project_category' ) ); ?>">
        <input type="hidden" id="wppm_project_ids" name="proj_ids" value="<?php echo esc_attr( implode( ',', $proj_ids ) ); ?>" />
</form>
<?php
$body = ob_get_clean();

ob_start();
?>
<button type="button" class="btn wppm_popup_close" onclick="wppm_modal_close();"><?php echo esc_html_e('Close','taskbuilder');?></button>
<button type="button" class="btn wppm_popup_action" onclick="wppm_set_bulk_change_project_category();"><?php echo esc_html_e('Save','taskbuilder');?></button>
<?php
$footer = ob_get_clean();

$output = array(
  'body'   => $body,
  'footer' => $footer
);

echo json_encode($output);
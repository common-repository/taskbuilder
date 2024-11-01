<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
global $wpdb,$wppmfunction,$current_user;
$wppm_users_role = get_option('wppm_user_role');
$proj_ids  = isset($_POST['proj_ids']) ? $wppmfunction->sanitize_array($_POST['proj_ids']) : '' ;
$wppm_current_user_capability = get_user_meta( $current_user->ID, 'wppm_capability', true );
$settings = get_option("wppm-ap-modal");
ob_start();
?>
<form id="frm_get_bulk_project_users">
	<div id="wppm_get_users">
		<input type="text" id="wppm_user_name" class="wppm_user_name form-control regi_user_autocomplete ui-autocomplete-input" name="user_name" autocomplete="off" placeholder="Search User">
		<div class="wppm_filter_display_container" id="wppm_bulk_proj_users_display_container">
		<input type="hidden" name="action" value="wppm_set_bulk_project_users" />
		<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wppm_set_bulk_project_users' ) ); ?>">
		<input type="hidden" id="wppm_proj_id" name="proj_ids" value="<?php echo esc_attr( implode( ',', $proj_ids ) ); ?>" />
	</div>
</form>
<style>
    li {
        color:<?php echo esc_attr( $settings['body-text-color'])?>!important;
    }
</style>
<script>
	jQuery(document).ready(function(){
		jQuery("input[name='user_name']").keypress(function(e) {
			//Enter key
			if (e.which == 13) {
				return false;
			}
		});
		
		jQuery( ".wppm_user_name" ).autocomplete({
			minLength: 1,
			appendTo: jQuery('.wppm_user_name').parent(),
			source: function( request, response ) {
				var term = request.term;
				request = {
					action: 'wppm_filter_autocomplete',
					term : term,
					field : 'users_name',
				}
				jQuery.getJSON( wppm_admin.ajax_url, request, function( data, status, xhr ) {
					response(data);
				});
			},
			select: function (event, ui) {
				var html_str = '<div id="wppm_user_display_container_'+ui.item.user_id+'" class="row wppm_user_display_container">'
									+'<div class="flex-container col-sm-4">'
										+'<span class="wppm_filter_display_text">'
											+ui.item.label
											+'<input type="hidden" name="user_names[]" value="'+ui.item.user_id+'">'
										+'</span>'
									+'</div>'
									+'<div class="col-sm-4 wppm_user_role">'
											+'<select size="sm" class="form-control" id="wppm_select_user_role_'+ui.item.user_id+'" name="wppm_select_user_role_'+ui.item.user_id+'">'+
											<?php 
												if(!empty($wppm_users_role)){
													foreach($wppm_users_role as $key=>$role){
														if(!empty($role)){
															foreach($role as $k=>$val){
															?>'<option value="<?php echo esc_attr($key) ?>"><?php echo esc_html_e($role['label'],'taskbuilder') ?></option>'+<?php
															}
														}
													}
												}
											?>
											'</select>'
									+'</div>'
									+'<div class="col-sm-4 wppm_delete_user_icon">'
										+'<span onclick="wppm_remove_filter('+ui.item.user_id+');"><img src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/trash.svg'); ?>" alt="delete"></span>'
									+'</div>'	
								+'</div>';
				jQuery('.wppm_project_users_not_assign_label').hide();
				jQuery('#wppm_get_users #wppm_bulk_proj_users_display_container').append(html_str);
				jQuery(this).val(''); return false;
			}
		}).focus(function() {
			jQuery(this).autocomplete("search", "");
		});
	});
</script>
<?php
$body = ob_get_clean();
ob_start();
?>
<button type="button" class="btn wppm_popup_close" onclick="wppm_modal_close();"><?php echo esc_html_e('Close','taskbuilder');?></button>
<button type="button" class="btn wppm_popup_action" onclick="wppm_set_bulk_project_users();"><?php echo esc_html_e('Save','taskbuilder');?></button>
<?php
$footer = ob_get_clean();
$output = array(
    'body'      => $body,
    'footer'    => $footer
);
echo json_encode($output);
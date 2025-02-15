<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user,$wpdb,$wppmfunction;
if (!($current_user->ID && $current_user->has_cap('wppm_admin') || $current_user->has_cap('manage_options'))) {
	exit;
}
$wppm_default_email_notification_to_current_user = get_option('wppm_default_email_notification_to_current_user');
?>
<form id="wppm_en_frm_general_settings" method="post" action="javascript:wppm_set_en_general_settings();">
  <div class="form-group">
    <label for="wppm_en_from_name"><?php echo esc_html_e('From Name','taskbuilder');?></label>
    <p class="help-block"><?php echo esc_html_e('Emails to send by this name.','taskbuilder');?></p>
    <input type="text" class="form-control" name="wppm_en_from_name" id="wppm_en_from_name" value="<?php echo esc_attr(get_option('wppm_en_from_name',''));?>" />
  </div>
  <div class="form-group">
    <label for="wppm_en_from_email"><?php echo esc_html_e('From Email','taskbuilder');?></label>
    <p class="help-block"><?php echo esc_html_e('Emails to send from this email.','taskbuilder');?></p>
    <input type="text" class="form-control" name="wppm_en_from_email" id="wppm_en_from_email" value="<?php echo esc_attr(get_option('wppm_en_from_email',''));?>" />
  </div>
  <div class="form-group">
    <label for="wppm_en_ignore_emails"><?php echo esc_html_e('Block Emails','taskbuilder');?></label>
    <p class="help-block"><?php echo esc_html_e('Emails will not be sent to these email addresses. New email should begin on new line.','taskbuilder');?></p>
    <?php
    $ignore_emails = get_option('wppm_en_ignore_emails',array());
    $ignore_emails = $wppmfunction->sanitize_array($ignore_emails);
    ?>
    <textarea class="form-control" style="height:100px !important;" name="wppm_en_ignore_emails" id="wppm_en_ignore_emails"><?php echo stripcslashes(implode('\n', $ignore_emails))?></textarea>
  </div>
  <hr>
  <span>
    <label><?php echo esc_html_e('Enable current user email notifications','taskbuilder');?></label>
  </span><br>
  <p class="help-block"><?php echo esc_html_e('Default enable/disable setting to send email notifications to current user (who is making some action like create task, change status, change assign user, new comment.etc).','taskbuilder');?></p>
  <select class="form-control" name="wppm_default_email_notification_to_current_user" id="wppm_default_email_notification_to_current_user">
      <?php
      $selected = $wppm_default_email_notification_to_current_user == '1' ? 'selected="selected"' : '';
      echo '<option '.$selected.' value="1">'.__('Enable','taskbuilder').'</option>';
      $selected = $wppm_default_email_notification_to_current_user == '0' ? 'selected="selected"' : '';
      echo '<option '.$selected.' value="0">'.__('Disable','taskbuilder').'</option>';
      ?>
  </select>
  <hr>
  <?php do_action('wppm_get_en_gerneral_settings');?>
  <button type="submit" class="wppm-submit-btn"><?php echo esc_html_e('Save Changes','taskbuilder');?></button>
  <span class="wppm_submit_wait" style="display:none;"><img src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/loading_buffer.svg'); ?>" alt="loading_icon"></span>  
  <input type="hidden" name="action" value="wppm_set_en_general_settings" />
  <input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wppm_set_en_general_settings' ) ); ?>">
</form>

<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

global $current_user,$wpdb,$wppmfunction;
$wppm_default_task_list_view = get_option('wppm_default_task_list_view');
$wppm_default_project_date = get_option('wppm_default_project_date');
$wppm_default_task_date = get_option('wppm_default_task_date');
$wppm_project_time = get_option('wppm_project_time');
$wppm_task_time = get_option('wppm_task_time');
$wppm_ap_settings = get_option("wppm-ap-settings");
$wppm_edit_tasks_permission = get_option('wppm_default_edit_tasks_permission');
$project_statuses = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wppm_project_statuses");
$task_statuses = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wppm_task_statuses");
$default_proj_status = get_option('wppm_default_project_status');
$default_task_status = get_option('wppm_default_task_status');
$wppm_tinymce_visibility_open_task = get_option('wppm_tinymce_visibility_open_task');
$wppm_tinymce_visibility_open_project = get_option('wppm_tinymce_visibility_open_project');
?>
<form id="wppm_frm_general_settings" method="post" action="javascript:wppm_set_general_settings();">
    <div class="wppm-help-container">
      <a href="https://taskbuilder.net/docs/general-setting/" target="_blank"><?php echo esc_attr__( 'Click here', 'taskbuilder' )?></a> <?php echo esc_attr__( 'to see the documentation!', 'taskbuilder' )?>
    </div>
    <span>
      <label><?php echo esc_html_e('Task List View','taskbuilder');?></label>
    </span><br>
    <p class="help-block"><?php echo esc_html_e('This selected view get applied on task list table','taskbuilder');?></p>
    <input type="radio" name="wppm_task_list_view" style="margin-top: 0px;" value="1" <?php echo ((esc_attr($wppm_default_task_list_view))==1) ?'checked="checked"':'';?>>
    <span style="padding-left: 10px;"><?php echo esc_html_e('List View','taskbuilder');?></span>
    <br>
    <input type="radio" name="wppm_task_list_view" value="0" <?php echo ((esc_attr($wppm_default_task_list_view))==0)?'checked="checked"':'';?>>
    <span style="padding-left: 10px;"><?php echo esc_html_e('Card View','taskbuilder');?></span>
    <hr>
    <span>
      <label><?php echo esc_html_e('Time in project start date and end date','taskbuilder');?></label>
    </span><br>
    <p class="help-block"><?php echo esc_html_e('Default show/hide time in start and end date of project.','taskbuilder');?></p>
    <select class="form-control" name="wppm_project_time" id="wppm_project_time">
				<?php
				$selected = $wppm_project_time == '1' ? 'selected="selected"' : '';
				echo '<option '.$selected.' value="1">'.__('Show','taskbuilder').'</option>';
				$selected = $wppm_project_time == '0' ? 'selected="selected"' : '';
				echo '<option '.$selected.' value="0">'.__('Hide','taskbuilder').'</option>';
				?>
    </select>
    <hr>
    <span>
      <label><?php echo esc_html_e('Time in task start date and end date','taskbuilder');?></label>
    </span><br>
    <p class="help-block"><?php echo esc_html_e('Default show/hide time in start and end date of task.','taskbuilder');?></p>
    <select class="form-control" name="wppm_task_time" id="wppm_task_time">
				<?php
				$selected = $wppm_task_time == '1' ? 'selected="selected"' : '';
				echo '<option '.$selected.' value="1">'.__('Show','taskbuilder').'</option>';
				$selected = $wppm_task_time == '0' ? 'selected="selected"' : '';
				echo '<option '.$selected.' value="0">'.__('Hide','taskbuilder').'</option>';
				?>
    </select>
    <hr>
    <span>
      <label><?php echo esc_html_e('Project start date and end date','taskbuilder');?></label>
    </span><br>
    <p class="help-block"><?php echo esc_html_e('Default show/hide start date and end date of project.','taskbuilder');?></p>
    <input type="radio" name="wppm_default_project_date" style="margin-top: 0px;" value="1" <?php echo ((esc_attr($wppm_default_project_date))==1) ?'checked="checked"':'';?>>
    <span style="padding-left: 10px;"><?php echo esc_html_e('Show','taskbuilder');?></span>
    <br>
    <input type="radio" name="wppm_default_project_date" style="margin-top: 0px;" value="0" <?php echo ((esc_attr($wppm_default_project_date))==0) ?'checked="checked"':'';?>>
    <span style="padding-left: 10px;"><?php echo esc_html_e('Hide','taskbuilder');?></span>
    <br>
    <hr>
    <span>
      <label><?php echo esc_html_e('Task start date and end date','taskbuilder');?></label>
    </span><br>
    <p class="help-block"><?php echo esc_html_e('Default show/hide start date and end date of task.','taskbuilder');?></p>
    <input type="radio" name="wppm_default_task_date" style="margin-top: 0px;" value="1" <?php echo ((esc_attr($wppm_default_task_date))==1) ?'checked="checked"':'';?>>
    <span style="padding-left: 10px;"><?php echo esc_html_e('Show','taskbuilder');?></span>
    <br>
    <input type="radio" name="wppm_default_task_date" style="margin-top: 0px;" value="0" <?php echo ((esc_attr($wppm_default_task_date))==0) ?'checked="checked"':'';?>>
    <span style="padding-left: 10px;"><?php echo esc_html_e('Hide','taskbuilder');?></span>
    <br>
    <hr>
    <span>
      <label><?php echo esc_html_e('Allow co-workers to edit tasks','taskbuilder');?></label>
    </span><br>
    <p class="help-block"><?php echo esc_html_e('Default enable/disable permission for co-workers to edit tasks.','taskbuilder');?></p>
    <select class="form-control" name="wppm_edit_tasks_permission" id="wppm_edit_tasks_permission">
				<?php
				$selected = $wppm_edit_tasks_permission == '1' ? 'selected="selected"' : '';
				echo '<option '.$selected.' value="1">'.__('Enable','taskbuilder').'</option>';
				$selected = $wppm_edit_tasks_permission == '0' ? 'selected="selected"' : '';
				echo '<option '.$selected.' value="0">'.__('Disable','taskbuilder').'</option>';
				?>
    </select>
    <hr>
    <span>
      <label><?php echo esc_html_e('Default project status','taskbuilder');?></label>
    </span><br>
    <p class="help-block"><?php echo esc_html_e('Selected status will get applied to project after creating it.','taskbuilder');?></p>
    <select class="form-control" name="wppm_default_proj_status" id="wppm_default_proj_status">
      <?php foreach ($project_statuses as $status) :
        ?>
        <option <?php echo esc_attr($default_proj_status)==esc_attr($status->id) ?'selected="selected"':''?> value="<?php echo esc_attr($status->id)?>"><?php echo (esc_attr($status->name))?></option>
      <?php endforeach;?>
    </select>
    <br>
    <hr>
    <span>
      <label><?php echo esc_html_e('Default task status','taskbuilder');?></label>
    </span><br>
    <p class="help-block"><?php echo esc_html_e('Selected status will get applied to task after creating it.','taskbuilder');?></p>
    <select class="form-control" name="wppm_default_task_status" id="wppm_default_task_status">
      <?php foreach ($task_statuses as $status) :
        ?>
        <option <?php echo esc_attr($default_task_status)==esc_attr($status->id) ?'selected="selected"':''?> value="<?php echo esc_attr($status->id)?>"><?php echo (esc_attr($status->name))?></option>
      <?php endforeach;?>
    </select>
    <br>
    <hr>
    <span>
      <label><?php echo esc_html_e('Default Show TinyMCE editor in open project ','taskbuilder');?></label>
    </span><br>
    <p class="help-block"><?php echo esc_html_e('Default show/hide TinyMCE editor in open project.','taskbuilder');?></p>
    <input type="radio" name="wppm_tinymce_visibility_open_project" style="margin-top: 0px;" value="1" <?php echo ((esc_attr($wppm_tinymce_visibility_open_project))==1) ?'checked="checked"':'';?>>
    <span style="padding-left: 10px;"><?php echo esc_html_e('Show','taskbuilder');?></span>
    <br>
    <input type="radio" name="wppm_tinymce_visibility_open_project" style="margin-top: 0px;" value="0" <?php echo ((esc_attr($wppm_tinymce_visibility_open_project))==0) ?'checked="checked"':'';?>>
    <span style="padding-left: 10px;"><?php echo esc_html_e('Hide','taskbuilder');?></span>
    <br>
    <hr>
    <span>
      <label><?php echo esc_html_e('Default Show TinyMCE editor in open task ','taskbuilder');?></label>
    </span><br>
    <p class="help-block"><?php echo esc_html_e('Default show/hide TinyMCE editor in open task.','taskbuilder');?></p>
    <input type="radio" name="wppm_tinymce_visibility_open_task" style="margin-top: 0px;" value="1" <?php echo ((esc_attr($wppm_tinymce_visibility_open_task))==1) ?'checked="checked"':'';?>>
    <span style="padding-left: 10px;"><?php echo esc_html_e('Show','taskbuilder');?></span>
    <br>
    <input type="radio" name="wppm_tinymce_visibility_open_task" style="margin-top: 0px;" value="0" <?php echo ((esc_attr($wppm_tinymce_visibility_open_task))==0) ?'checked="checked"':'';?>>
    <span style="padding-left: 10px;"><?php echo esc_html_e('Hide','taskbuilder');?></span>
    <br>
    <hr>
    <button type="submit" class="wppm-submit-btn" style="background-color:<?php echo esc_attr($wppm_ap_settings['save-changes-button-bg-color'])?>!important;color:<?php echo esc_attr($wppm_ap_settings['save-changes-button-text-color'])?>!important;"><?php echo esc_html_e('Save Changes','taskbuilder');?></button>
    <span class="wppm_submit_wait" style="display:none;"><img src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/loading_buffer.svg'); ?>" alt="edit"></span>  
    <input type="hidden" name="action" value="wppm_set_general_settings" />
    <input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wppm_set_general_settings' ) ); ?>">
</form>
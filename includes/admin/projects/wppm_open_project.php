<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
global $wpdb,$current_user,$wppmfunction;
$wppm_project_time = get_option('wppm_project_time');
$wppm_default_project_date = get_option('wppm_default_project_date');
$wppm_public_projects_permission = get_option('wppm_public_projects_permission');
$appearance_settings = get_option("wppm-ap-individual-project");
$wppm_tinymce_visibility_open_project = get_option('wppm_tinymce_visibility_open_project');
$settings = get_option("wppm-ap-modal");
$wppm_current_user_capability = get_user_meta( $current_user->ID, 'wppm_capability', true );
$current_date = date('Y-m-d');
$id = isset($_POST) && isset($_POST['id']) ? intval(sanitize_text_field($_POST['id'])) : 0;
if (!isset($id)) {exit;}
$auth_id = $wppmfunction->get_project_meta($id,'project_auth_code',true);
$auth_id = sanitize_text_field($auth_id);
$project = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wppm_project where id = $id" );
$orderby_sql = sanitize_sql_orderby("create_time DESC");
$project_comment = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wppm_project_comment where proj_id="."$id"." ORDER BY ".$orderby_sql);
if(isset($project->users)){
  $users = explode(",",$project->users);
}else{
  $users = "";
}
$wppm_users_role = get_option('wppm_user_role');
if(isset($project->status)){
  $projstatus = esc_sql($project->status);
  $project_status = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wppm_project_statuses where id = $projstatus");
}
if(isset($project->cat_id)){
  $projcat = esc_sql($project->cat_id);
  $project_category = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wppm_project_categories where id = $projcat");
}
if(!empty($project->description)){
  $project_description = stripslashes((htmlspecialchars_decode($project->description, ENT_QUOTES)));
}
if($wppm_project_time == 1){
 $proj_start_date = isset($project->start_date)? $project->start_date:"";
 $proj_end_date = isset($project->end_date) ? $project->end_date: "" ;
} elseif($wppm_project_time == 0){
  $psDate = isset($project->start_date) ? new DateTime($project->start_date): "";
  $peDate = isset($project->end_date) ? new DateTime($project->end_date):"";
  $proj_start_date = !empty($psDate) ? $psDate->format('Y-m-d'):"";
  $proj_end_date =  !empty($peDate) ? $peDate->format('Y-m-d'):"";
}

?>
<form id="wppm_open_project" method="post">
  <div class="row">
    <div class="col-sm-12">
      <span class="wppm-heading-inline"><?php echo esc_html_e('Project','taskbuilder');?></span>
      <?php if($current_user->has_cap('manage_options') || $wppm_current_user_capability == 'wppm_manager'|| $wppm_current_user_capability == 'wppm_admin'){ ?>
              <span class="wppm-add-new-btn btn-primary" style="background-color:<?php echo esc_attr($appearance_settings['menu-button-bg-color'])?>;color:<?php echo esc_attr($appearance_settings['menu-button-text-color'])?>" id="wppm_add_new_project_btn" onclick="wppm_add_new_project()"><span style="margin-right:5px;"><img id="wppm_add_new_project_icon" src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/plus_icon.svg'); ?>" alt="plus_icon"></span><span><?php echo esc_html_e('Add New','taskbuilder');?></span></span>
      <?php } ?>
      <span class="wppm-add-new-btn btn-primary" id="wppm_project_list" style="background-color:<?php echo esc_attr($appearance_settings['menu-button-bg-color'])?>;color:<?php echo esc_attr($appearance_settings['menu-button-text-color'])?>" onclick="wppm_get_project_list()" ><span style="margin-right:5px;"><img id ="wppm_project_list_icon" src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/list-symbol.svg'); ?>" alt="list-symbol"></span><span><?php echo esc_html_e('Project List','taskbuilder');?></span></span>
      <span class="wppm-add-new-btn btn-primary" id="wppm_project_tasks" style="background-color:<?php echo esc_attr($appearance_settings['menu-button-bg-color'])?>;color:<?php echo esc_attr($appearance_settings['menu-button-text-color'])?>" onclick="wppm_get_project_tasks(<?php echo esc_attr($id) ?>)"><span style="margin-right:5px;"><img id ="wppm_task_list_icon" src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/task_list.svg'); ?>" alt="task_list"></span><span><?php echo esc_html_e('Tasks','taskbuilder');?></span></span>
      <?php $style = (($current_user->ID && $current_user->has_cap('manage_options')) || ($wppmfunction->has_project_permission('delete_project',$project->id))  || ($project->created_by==$current_user->ID && $wppm_current_user_capability == 'wppm_manager') || ($current_user->ID && $current_user->has_cap('wppm_admin')))? "display:inline":"display:none"; ?>
      <span class="wppm-add-new-btn btn-primary" id="wppm_delete_project_btn" style="background-color:<?php echo esc_attr($appearance_settings['menu-button-bg-color'])?>;color:<?php echo esc_attr($appearance_settings['menu-button-text-color'])?>;<?php echo esc_attr($style) ?>" onclick="wppm_delete_project(<?php echo esc_attr($id) ?>)"><span style="margin-right:5px;"><img id="wppm_delete_project_icon" src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/delete2.svg'); ?>" alt="delete"></span><span><?php echo esc_html_e('Delete','taskbuilder');?></span></span>
      <?php $visibility_style = (($current_user->ID && ($current_user->has_cap('manage_options') || $current_user->has_cap('wppm_admin') || ($project->created_by==$current_user->ID && $wppm_current_user_capability == 'wppm_manager'))) && ($wppm_public_projects_permission==1))? "display:inline":"display:none"; ?>
      <span class="wppm-add-new-btn btn-primary" id="wppm_project_visibility" style="background-color:<?php echo esc_attr($appearance_settings['menu-button-bg-color'])?>;color:<?php echo esc_attr($appearance_settings['menu-button-text-color'])?>;<?php echo esc_attr($visibility_style) ?>" onclick="wppm_get_project_visibility(<?php echo esc_attr($id) ?>)"><span style="margin-right:5px;"><img id ="wppm_task_list_icon" src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/wppm_visibility.svg'); ?>" alt="project_visibility"></span><span><?php echo esc_html_e('Project Visibility','taskbuilder');?></span></span>
      <?php echo do_action('wppm_after_open_project_buttons',$project->id);?>
    </div>
  </div>
  <div id="wppm_load_individual_project_container" class="row">
      <div class="col-sm-8 wppm_body col-md-9">
        <div class="row">
          <div class="col-sm-12" id="wppm_project_details_container">
            <div class="row">
              <div class="col-sm-12">
                <span class="wppm_project_label"> <?php echo isset($project->project_name) ? esc_html($project->project_name):"";
                      if (($wppmfunction->has_project_permission('change_project_details',$id)) || ($current_user->has_cap('manage_options') || $current_user->has_cap('wppm_admin') || ($project->created_by==$current_user->ID && $wppm_current_user_capability == 'wppm_manager'))) { ?>
                        <span id="wppm_individual_edit_project_subject" onclick="wppm_edit_proj_details(<?php echo esc_attr($id) ?>)" class="btn btn-sm wppm_action_btn" style="background-color:#FFFFFF !important;color:#000000 !important;"><img src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/edit_01.svg'); ?>" alt="edit"></span>
                <?php } ?>
                </span>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-3">
                <span class="wppm_project_details_label"><?php echo esc_html_e('Created On:','taskbuilder');?></span>
              </div>
              <div class="col-sm-9">
                <span class="wppm_project_details"><?php echo isset($project->date_created) ? esc_html($project->date_created):"" ?></span>
              </div>
            </div>
            <?php if($wppm_default_project_date==1) { ?>
                    <div class="row">
                      <div class="col-sm-3">
                        <span class="wppm_project_details_label"><?php echo esc_html_e('Start Date:','taskbuilder');?></span>
                      </div>
                      <div class="col-sm-9">
                        <span class="wppm_project_details"  id="wppm_edit_pstart_date"><?php echo (isset($proj_start_date))? esc_html($proj_start_date): "" ?></span>
                      </div>
                      <input type="hidden" name="wppm_edit_pstart_date_ajax_nonce" id="wppm_edit_pstart_date_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wppm_set_change_proj_start_date' ) ); ?>">
                    </div>
                    <div class="row">
                      <div class="col-sm-3">
                      <span class="wppm_project_details_label"><?php echo esc_html_e('End Date:','taskbuilder');?></span>
                      </div>
                      <div class="col-sm-9">
                      <?php 
                        $pstatus = isset($project->status) ? $project->status :"";
                        $style = ($pstatus!=4 && $proj_end_date < $current_date) ? "color:#FF0000":"color:#2C3E50"; ?>
                        <span class="wppm_project_details" id="wppm_edit_pend_date" style="<?php echo esc_attr($style); ?>"><?php echo (isset($proj_end_date))? esc_html($proj_end_date):"" ?></span>
                      </div>
                      <input type="hidden" name="wppm_edit_pend_date_ajax_nonce" id="wppm_edit_pend_date_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wppm_set_change_proj_end_date' ) ); ?>">
                    </div>
            <?php } ?>
            <div class="row">
              <div class="col-sm-3">
                <span class="wppm_project_details_label"><?php echo esc_html_e('Project Category:','taskbuilder');?></span>
              </div>
              <div class="col-sm-9">
                <span class="wppm_project_details"><?php echo (isset($project_category->name))? esc_html_e($project_category->name,'taskbuilder'):"" ?></span>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-3">
                <span class="wppm_project_details_label"><?php echo esc_html_e('Description:','taskbuilder');?></span>
              </div>
              <?php
              $allowedtags = array( 'br' => array(), 'abbr' => array('title' => array(),), 'p' => array(), 'strong' => array(), 'a' => array('href' => array(), 'title' => array(),'target'=> array(), 'rel'=>array()),'em' =>array(),'span' =>array(), 'blockquote'=>array('cite'  => array(),),'div' => array('class' => array(),'title' => array(),'style' => array(),),'ul'=>array(),'li'=>array(),'ol'=>array(),'img' => array( 'alt'=> array(),'class' => array(),'height' => array(),'src'=> array(),'width'=> array(),));
              ?>
              <div class="col-sm-9 wppm_project_description">
                <span><?php  echo (isset($project_description))?  wp_kses(wpautop($project_description),$allowedtags):"" ?></span>
              </div>
            </div>
          </div>
        </div>
        <div id="wppm_proj_discussion_container">
            <div class="row">
              <div class="col-sm-12" style="margin-top:5px;">
                <span class="wppm_proj_discussion"><?php echo esc_html_e('Comment','taskbuilder')?></span><br>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-12">
                <textarea id="wppm_proj_comment" placeholder="<?php echo esc_attr__('Write a comment...','taskbuilder');?>" onclick="wppm_add_proj_comment()" name="wppm_proj_comment"></textarea>
              </div>
            </div>
            <div class="row" id="wppm_proj_comment_btn">
              <div class="col-sm-6">
                <span id="wppm_proj_attach_file" onclick="wppm_upload_proj_comment_file()"><?php echo esc_html_e('Attach Files','taskbuilder')?></span>
              </div>
              <div class="col-sm-6">
                <button type="button" id="wppm_individual_cancel_proj_comment_btn" onclick="wppm_cancel_proj_comment();" class="wppm-btn-cancel">
                  <?php echo esc_html_e('Cancel','taskbuilder')?> 
                </button>
                <button type="button" id="wppm_individual_submit_proj_comment_btn" style="background-color:<?php echo esc_attr($appearance_settings['comment-send-btn-bg-color'])?>;color:<?php echo esc_attr($appearance_settings['comment-send-btn-color'])?>!important;" onclick="wppm_submit_proj_comment(<?php echo esc_attr($id); ?>);" class="wppm-btn">
                  <?php echo esc_html_e('SEND','taskbuilder')?> 
                </button>
              </div>
              <input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wppm_submit_proj_comment' ) ); ?>">
            </div>
            <div class="row">
              <div class="wppm_proj_attachment_container col-sm-12" style="display:none;">
              </div>
            </div>
          </div>
          <div class="wppm_thread_container">
          <?php 
            if(!empty($project_comment)){
              foreach($project_comment as $comment){
                $proj_comment_meta = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wppm_project_comment_meta where proj_id=".$id." AND comment_id=".esc_sql($comment->id)."");
                $user = get_userdata( $comment->created_by );
                $comment_body = stripslashes((htmlspecialchars_decode($comment->body, ENT_QUOTES)));
                $comment_body = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $comment_body);
                $date = date("Y-m-d H:i:s" ,strtotime($comment->create_time));
                $thread_date = date("d F Y, h:i:s A" ,strtotime($date));
                $attachment_ids = isset($comment->attachment_ids)? $comment->attachment_ids : '';
                if($attachment_ids!=""){
                  $attachments = explode(",",$attachment_ids);
                } else {
                  $attachments = array();
                }
                ?>
                <div class="wppm_proj_thread">
                  <div class="wppm_thread_avatar">
                    <?php echo (get_avatar($comment->created_by, 40));?>
                  </div>
                  <div class="wppm_thread_body">
                    <div class="wppm_thread_user_name">
                      <strong style="color:<?php echo esc_attr($appearance_settings['comment-primary-color']);?>"><?php echo esc_html($user->display_name) ?></strong> 
                      <span><?php if(!empty($comment->body) && (isset($proj_comment_meta->comment_type) && $proj_comment_meta->comment_type=='change_proj_status')){
                        $comment_log_body = unserialize($comment->body);
                        $prev_status = $comment_log_body['prev_status'];
                        $new_status = $comment_log_body['new_status'];
                        if(isset($prev_status)){
                          $prev_status = esc_sql($prev_status);
                          $project_prev_status = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wppm_project_statuses where id = '$prev_status'");
                        }
                        if(isset($new_status)){
                          $new_status = esc_sql($new_status);
                          $project_new_status = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wppm_project_statuses where id= '$new_status'");
                        }
                        esc_html_e("changed status from $project_prev_status->name to $project_new_status->name","taskbuilder");
                      }
                      if(!empty($comment->body) && (isset($proj_comment_meta->comment_type) && $proj_comment_meta->comment_type=='change_proj_creator')){
                        $comment_log_body = unserialize($comment->body);
                        $prev_user = $comment_log_body['prev_user'];
                        $new_user = $comment_log_body['new_user'];
                        $prev_user_data = get_userdata($prev_user);
                        $new_user_data = get_userdata($new_user);
                        esc_html_e("changed creator from $prev_user_data->display_name to $new_user_data->display_name","taskbuilder");
                      }if(!empty($comment->body) && (isset($proj_comment_meta->comment_type) && $proj_comment_meta->comment_type=='change_assign_user')){
                        esc_html_e("changed the Assignee","taskbuilder");
                     }if(!empty($comment->body) && (isset($proj_comment_meta->comment_type) && $proj_comment_meta->comment_type=='change_proj_cat')){
                        $comment_log_body = unserialize($comment->body);
                        $prev_cat = $comment_log_body['prev_cat'];
                        $new_cat = $comment_log_body['new_cat'];
                        if(!empty($prev_cat)){
                          $prev_cat = esc_sql($prev_cat);
                          $project_prev_cat = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wppm_project_categories where id= '$prev_cat'");
                        }
                        if(!empty($new_cat)){
                          $new_cat = esc_sql($new_cat);
                          $project_new_cat = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wppm_project_categories where id='$new_cat'");
                        }
                        $proj_prev_cat_name = !empty($project_prev_cat) ? $project_prev_cat->name: "None";
                        $proj_new_cat_name = !empty($project_new_cat) ? $project_new_cat->name: "None";
                        esc_html_e("changed category from $proj_prev_cat_name to $proj_new_cat_name","taskbuilder");
                    }
                      ?></span>
                      <small class="wppm_comment_date" style="color:<?php echo esc_attr($appearance_settings['comment-date-color']);?>"><i><?php echo esc_html($thread_date) ?></i></small>
                      <div class="wppm_thread_action">
                        <?php if(( (($current_user->has_cap('manage_options')) || ($wppmfunction->has_proj_comment_permission('edit_proj_comment',$id,$comment->id))) && ((empty($proj_comment_meta->comment_type))|| (isset($proj_comment_meta->comment_type) && $proj_comment_meta->comment_type=='comment')))){ ?>
                          <span title="Edit this thread" onclick="wppm_edit_proj_thread(<?php echo esc_attr($comment->id)?>,<?php echo esc_attr($id) ?>)"><img src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/edit1.svg'); ?>" alt="edit"></span>
                          <span title="Delete this thread" onclick="wppm_delete_proj_thread(<?php echo esc_attr($comment->id) ?>,<?php echo esc_attr($id) ?>)"><img src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/trash.svg'); ?>" alt="delete"></span>
                        <?php } ?>
                      </div>
                    </div>
                    <div class="wppm_thread_messege" style="color:<?php echo esc_attr($appearance_settings['comment-secondary-color']);?>">
                      <?php 
                      $allowedtags = array( 'br' => array(), 'abbr' => array('title' => array(),), 'p' => array(), 'strong' => array(), 'a' => array('href' => array(), 'title' => array(), 'target'=> array(), 'rel'=>array()),'em' =>array(),'span' =>array(), 'blockquote'=>array('cite'  => array(),),'div' => array('class' => array(),'title' => array(),'style' => array(),),'ul'=>array(),'li'=>array(),'ol'=>array(),'img' => array( 'alt'=> array(),'class' => array(),'height' => array(),'src'=> array(),'width'=> array(),));
                      if(!empty($comment_body) && ((empty($proj_comment_meta->comment_type)) || (isset($proj_comment_meta->comment_type) && $proj_comment_meta->comment_type=='comment') || $proj_comment_meta->comment_type=='delete_proj_comment')){
                        echo wp_kses(wpautop($comment_body),$allowedtags) ;
                      } elseif(!empty($comment_body) && (isset($proj_comment_meta->comment_type) && $proj_comment_meta->comment_type=='change_proj_status')){ ?>
                        <span class="wppm_admin_label" style="margin-right: 10px; background-color:<?php echo isset($project_prev_status->bg_color) ? esc_attr($project_prev_status->bg_color):""; ?>;color:<?php echo isset($project_prev_status->color) ? esc_attr($project_prev_status->color): "" ?>;"><?php echo isset($project_prev_status->name) ? esc_attr($project_prev_status->name) : "" ?></span> <img src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/arrow-right.svg'); ?>" alt="arrow_right"> <span class="wppm_admin_label" style="margin-left:10px; background-color:<?php echo isset($project_new_status->bg_color) ? esc_attr($project_new_status->bg_color):""; ?>;color:<?php echo isset($project_new_status->color) ? esc_attr($project_new_status->color): "" ?>;"><?php echo isset($project_new_status->name) ? esc_attr($project_new_status->name) : "" ?></span><?php
                      } elseif(!empty($comment_body) && (isset($proj_comment_meta->comment_type) && $proj_comment_meta->comment_type=='change_proj_creator')) { ?>
                        <span style="margin-right: 10px;"><?php echo isset($prev_user_data->display_name) ? esc_attr($prev_user_data->display_name) : "" ?></span> <img src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/arrow-right.svg'); ?>" alt="arrow_right"> <span style="margin-left:10px;"><?php echo isset($new_user_data->display_name) ? esc_attr($new_user_data->display_name) : "" ?></span><?php
                      } elseif(!empty($comment_body) && (isset($proj_comment_meta->comment_type) && $proj_comment_meta->comment_type=='change_assign_user')) { 
                        $comment_log_body = unserialize($comment->body);
                        $prev_assign_user = $comment_log_body['prev_assign_user'];
                        $new_assign_user = $comment_log_body['new_assign_user'];
                        $prev_assign_user_array = array();
                        $new_assign_user_array = array();
                        if(!empty($prev_assign_user)){
                          $prev_assign_user_array = explode(",",$prev_assign_user);
                        }
                        if(!empty($new_assign_user)){
                          $new_assign_user_array = explode(",",$new_assign_user);
                        }
                        $assign_user_name = array();
                        $assign_user_name_str = "";
                        if(!empty($prev_assign_user_array)){
                          foreach($prev_assign_user_array as $val){
                            $assign_user = get_userdata($val);
                            $assign_user_name[] = $assign_user->display_name;
                          }
                          $assign_user_name_str = implode(",",$assign_user_name);
                        }
                        $new_assign_user_name = array();
                        $new_assign_user_name_str = "";
                        if(!empty($new_assign_user_array)){
                          foreach($new_assign_user_array as $val){
                            $new_assign_user = get_userdata($val);
                            $new_assign_user_name[] = $new_assign_user->display_name;
                          }
                          $new_assign_user_name_str = implode(",",$new_assign_user_name);
                        }
                        ?>
                        <span style="margin-right: 10px;"><?php echo !empty($assign_user_name) ? esc_attr( $assign_user_name_str) : " None" ?></span> <img src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/arrow-right.svg'); ?>" alt="arrow_right"> <span style="margin-left:10px;"><?php echo !empty($new_assign_user_name_str) ? esc_attr($new_assign_user_name_str) : "None" ?></span><?php
                      }elseif(isset($proj_comment_meta->comment_type) && $proj_comment_meta->comment_type=='change_proj_cat'){ ?>
                        <span style="margin-right: 10px;"><?php echo !empty($project_prev_cat->name) ? esc_attr($project_prev_cat->name) : " None" ?></span> <img src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/arrow-right.svg'); ?>" alt="arrow_right"> <span style="margin-left:10px;"><?php echo !empty($project_new_cat->name) ? esc_attr($project_new_cat->name) : "None" ?></span><?php
                      } ?>

                    </div>
                    <?php 
                    if(!empty($attachments) && !(isset($proj_comment_meta->comment_type) && $proj_comment_meta->comment_type=='delete_proj_comment')):?> <br>
                      <strong class="wppm_attachment_title"><?php echo esc_html_e('Attachments','taskbuilder');?>:</strong><br>
                      <table class="wppm_attachment_tbl">
                        <tbody>
                        <?php
                        if(!empty($attachments)){
                          foreach( $attachments as $attachment ):
                            $attachment = esc_sql($attachment);
                            $attach = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wppm_attachments where id='$attachment'");
                            $download_url = site_url('/').'?wppm_attachment='.$attachment.'&pid='.$id.'&pac='.$auth_id;
                            $findStr = ".txt";
                            if(!empty( $attach)){
                              $attachment_name = preg_replace('/' . $findStr . '/', "", $attach->name, 1);
                              $attachment_name = sanitize_file_name($attachment_name);
                            }
                            if((!empty($attachment_name))){
                              ?>
                              <tr class="wppm_attachment_tr">
                                <td>
                                  <a class="wppm_attachment_link" href="<?php echo esc_url($download_url) ?>" target="_blank">
                                  <span class="wppm_attachment_file_name" style="padding: 7px;"><?php echo isset($attach->file_name) ? esc_html($attach->file_name):"";?></span></a>
                                  <?php if((($comment->created_by == $current_user->ID) || ($current_user->has_cap('manage_options')) || ($wppmfunction->has_proj_comment_permission('edit_proj_comment',$id,$comment->id)))){ ?>
                                    <span class="wppm_thread_action_btn" onclick="wppm_proj_thread_attachment_remove(this,<?php echo isset($attach->id)? esc_attr($attach->id):''; ?>,<?php echo isset($comment->id)? esc_attr($comment->id):''; ?>,<?php echo isset($comment->proj_id) ? esc_attr($comment->proj_id):'';?>)"><img src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/cancel.svg'); ?>" alt="cancel"></span>
                                  <?php } ?>
                                </td>
                              </tr>
                            <?php }
                          endforeach;
                        } ?>
                        </tbody>
                      </table>
                      <input type="hidden" name="wppm_proj_thread_attachment_remove_ajax_nonce" id="wppm_proj_thread_attachment_remove" value="<?php echo esc_attr( wp_create_nonce( 'wppm_remove_proj_thread_attachment' ) ); ?>">
                    <?php do_action('wppm_after_project_comment_attachment',$id,$comment->id);?>
                    <?php endif;?>
                  </div>
                </div>
            <?php } 
            } ?>
          </div>
        </div>
      <div class="wppm_individual_project_widget col-sm-4 wppm_sidebar col-md-3">
        <div class="row wppm_widget" id="wppm_project_status_widget">
            <div class="wppm-widget-header" style="background-color:<?php echo esc_attr($appearance_settings['widget-header-bg-color'])?>"> 
              <h4 class="widget_header" style="color:<?php echo esc_attr($appearance_settings['widget-header-text-color'])?>"><?php echo esc_html_e('Status','taskbuilder')?></h4>
                <?php $style = ($wppmfunction->has_project_permission('change_project_status',$id) || $current_user->has_cap('manage_options') || ($project->created_by==$current_user->ID && $wppm_current_user_capability == 'wppm_manager') ||  $wppm_current_user_capability == 'wppm_admin')? "display:flex":"display:none"; ?>
                <span class="wppm_edit_project_details_widget" style="<?php echo $style ?>" onclick="wppm_edit_project_status(<?php echo esc_attr($id) ?>)"><img src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/edit_01.svg'); ?>" alt="edit"></span>
            </div>
            <hr class="widget_divider">
            <div class="wppm-widget-body" style="background-color:<?php echo esc_attr($appearance_settings['widget-body-bg-color'])?>">
              <div class="wppm_sidebar_labels"><span class="wppm_label_info" style="color:<?php echo esc_attr($appearance_settings['widget-body-label-color'])?>"><?php echo esc_html_e('Status','taskbuilder')?>: <span class="wppm_admin_label" style="background-color:<?php echo isset($project_status->bg_color) ? esc_attr($project_status->bg_color):""; ?>;color:<?php echo isset($project_status->color) ? esc_attr($project_status->color): "" ?>;"><?php echo isset($project_status->name) ? esc_attr($project_status->name) : "" ?> </span></div>
            </div>
          </div>
        <div class="row wppm_widget" id="wppm_project_raisedby_widget">
          <div class="wppm-widget-header" style="background-color:<?php echo esc_attr($appearance_settings['widget-header-bg-color'])?>">
            <h4 class="widget_header" style="color:<?php echo esc_attr($appearance_settings['widget-header-text-color'])?>"><?php echo esc_html_e('Project Creator','taskbuilder')?></span></h4>
            <?php if ($current_user->has_cap('manage_options') || $wppm_current_user_capability == 'wppm_admin'|| ($project->created_by==$current_user->ID && $wppm_current_user_capability == 'wppm_manager')) { ?>
                    <span class="wppm_edit_project_details_widget" onclick="wppm_edit_project_creator(<?php echo esc_attr($id) ?>)"><img src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/edit_01.svg'); ?>" alt="edit"></span>
            <?php } ?>
          </div>
          <hr class="widget_divider"> <?php
          if(isset($project->created_by)){
            $user_info = get_userdata($project->created_by);
          }else{
            $user_info = "";
          }
          ?>
          <div class="wppm-widget-body" style="background-color:<?php echo esc_attr($appearance_settings['widget-body-bg-color'])?>">
            <div id="wppm_project_creator">
              <div style="padding:2px 0;">
                  <span class="wppm_project_user_avatar"><?php echo isset($project->created_by)? (get_avatar($project->created_by, 25, "mysteryman")):""; ?></span>
                  <span class="wppm_project_user_names" style="color:<?php echo esc_attr($appearance_settings['widget-body-text-color'])?>"><?php echo (!empty($user_info)) ? esc_html_e($user_info->display_name,'taskbuilder') : "";?></span>
              </div>
            </div>
          </div>
        </div>
        <div class="row wppm_widget" id="wppm_project_users_widget_container">
          <div class="wppm-widget-header" style="background-color:<?php echo esc_attr($appearance_settings['widget-header-bg-color'])?>">
            <h4 class="widget_header" style="color:<?php echo esc_attr($appearance_settings['widget-header-text-color'])?>"><?php echo esc_html_e('Users','taskbuilder')?></h4>
            <?php if ($wppmfunction->has_project_permission('assign_project_users',$id ) || $current_user->has_cap('manage_options') ||  ($project->created_by==$current_user->ID && $wppm_current_user_capability == 'wppm_manager') || $wppm_current_user_capability == 'wppm_admin') { ?>
                      <span class="wppm_edit_project_details_widget" onclick="wppm_get_users(<?php echo esc_attr($id) ?>)"><img src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/edit_01.svg'); ?>" alt="edit"></span>
            <?php } ?>
          </div>
          <hr class="widget_divider">
          <div class="wppm-widget-body" style="background-color:<?php echo esc_attr($appearance_settings['widget-body-bg-color'])?>">
            <div id="wppm_project_users">
              <?php 
              if(!(empty(array_filter($users)))){
                foreach($users as $user) { 
                  $user_data = get_userdata($user);
                  $user = esc_sql($user);
                  $project_user = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wppm_project_users WHERE proj_id = '$id' AND user_id = '$user'");
                  ?>
                  <div style="padding:2px 0;">
                    <span class="wppm_project_user_avatar"><?php echo (get_avatar($user, 25, "mysteryman")); ?></span>
                    <span class="wppm_project_user_names" style="color:<?php echo esc_attr($appearance_settings['widget-header-text-color'])?>"><?php echo esc_html_e($user_data->display_name,'taskbuilder'); ?></span>
                    <?php 
                    if(!empty($wppm_users_role)){ 
                      foreach($wppm_users_role as $key=>$role){
                        if(!empty($role)){
                          foreach($role as $k=>$val){
                            if( !empty($project_user) && $key == $project_user->role_id){ ?>
                              <span class="wppm_project_user_role" style="color:<?php echo esc_attr($appearance_settings['widget-body-text-color'])?>">(<?php echo esc_html_e($role['label'],'taskbuilder'); ?>)</span><?php 
                            }
                          }
                        }
                      }
                    }
                    ?>
                  </div>
                <?php 
                }
              } else {
                ?><span class="wppm_project_users_not_assign_label" style="color:<?php echo esc_attr($appearance_settings['widget-body-text-color'])?>"> <?php echo esc_html_e('None','taskbuilder'); ?></span><?php
              }
              ?>
            </div>
          </div>
        </div>
      </div>
      <input type="hidden" id="wppm_proj_nonce" value="<?php echo wp_create_nonce('wppm_upload_proj_attach_file')?>">
      <input type="file" id="wppm_proj_attachment_upload" class="hidden" onchange="">
  </div>
</form>
<style>
  .wppm_comment_date i:hover{
    color: <?php echo esc_attr($appearance_settings['comment-date-hover-color'])?>;
  }
  #wppm_open_project .wppm-add-new-btn:hover{
    background-color: <?php echo esc_attr($appearance_settings['menu-button-hover-color'])?>!important;
  }
</style>
<script>
  jQuery( document ).ready( function( jQuery ) { <?php
    if ((($current_user->ID && $current_user->has_cap('manage_options')) || $wppmfunction->has_project_permission('change_project_details',$id) || ($project->created_by==$current_user->ID && $wppm_current_user_capability == 'wppm_manager') || $wppm_current_user_capability == 'wppm_admin')) { ?>
      jQuery("#wppm_edit_pstart_date").flatpickr({
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        onChange: function(selectedDates, dateStr, instance) {
          var data = {
            action: 'wppm_set_change_proj_start_date',
            proj_id:<?php echo esc_attr($project->id);?>,
            date:dateStr,
            _ajax_nonce:jQuery('#wppm_edit_pstart_date_ajax_nonce').val()
          };
          jQuery.post(wppm_admin.ajax_url, data, function(response) {
            instance.close();
            wppm_open_project(<?php echo esc_attr($project->id);?>);
          }); 
        },
      });
      jQuery("#wppm_edit_pend_date").flatpickr({
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        onChange: function(selectedDates, dateStr, instance) {
          var data = {
            action: 'wppm_set_change_proj_end_date',
            proj_id:<?php echo esc_attr($project->id);?>,
            date:dateStr,
            _ajax_nonce:jQuery('#wppm_edit_pend_date_ajax_nonce').val()
          };
          jQuery.post(wppm_admin.ajax_url, data, function(response) {
            instance.close();
            wppm_open_project(<?php echo esc_attr($project->id);?>);
          }); 
        },
      });
    <?php } ?>
    tinymce.remove();
    tinymce.init({ 
      selector:'#wppm_proj_description',
      body_id: 'wppm_proj_description',
      directionality : '<?php //echo 'rtl'; ?>',
      menubar: false,
      statusbar: false,
      height : '200',
      plugins: [
          'lists link image directionality'
      ],
      image_advtab: true,
      toolbar: 'bold italic underline blockquote | alignleft aligncenter alignright | bullist numlist | rtl | link image',
      branding: false,
      autoresize_bottom_margin: 20,
      browser_spellcheck : true,
      relative_urls : false,
      remove_script_host : false,
      convert_urls : true,
      setup: function (editor) {
      }
    }); <?php
    if($wppm_tinymce_visibility_open_project==1){?> 
      tinymce.remove();
      tinymce.init({ 
        selector:'#wppm_proj_comment',
        body_id: 'wppm_proj_comment',
        directionality : '<?php //echo 'rtl'; ?>',
        menubar: false,
        statusbar: false,
        height : '200',
        width  : '100%',
        plugins: [
            'lists link image directionality paste'
        ],
        image_advtab: true,
        toolbar: 'bold italic underline blockquote | alignleft aligncenter alignright | bullist numlist | rtl | link image',
        branding: false,
        autoresize_bottom_margin: 20,
        browser_spellcheck : true,
        relative_urls : false,
        remove_script_host : false,
        convert_urls : true,
        paste_as_text: true,
        setup: function (editor) {
        }
      });
      jQuery('#wppm_proj_comment_btn').show();
  <?php } ?>
  });

function wppm_add_proj_comment(){
  tinymce.remove();
  tinymce.init({ 
    selector:'#wppm_proj_comment',
    body_id: 'wppm_proj_comment',
    directionality : '<?php //echo 'rtl'; ?>',
    menubar: false,
    statusbar: false,
    height : '200',
    width  : '100%',
    plugins: [
        'lists link image directionality paste'
    ],
    image_advtab: true,
    toolbar: 'bold italic underline blockquote | alignleft aligncenter alignright | bullist numlist | rtl | link image',
    branding: false,
    autoresize_bottom_margin: 20,
    browser_spellcheck : true,
    relative_urls : false,
    remove_script_host : false,
    convert_urls : true,
    paste_as_text: true,
    setup: function (editor) {
    }
  });
  jQuery('#wppm_proj_comment_btn').show();
}

function wppm_upload_proj_comment_file(){
    jQuery('#wppm_proj_attachment_upload').unbind('change');
    jQuery('#wppm_proj_attachment_upload').on('change', function() {
      var flag = false;
      var file = this.files[0];
      jQuery('#wppm_proj_attachment_upload').val('');
      var allowedExtension = ['exe', 'php','js'];
	    var file_name_split = file.name.split('.');
	    var file_extension = file_name_split[file_name_split.length-1];
      file_extension = file_extension.toLowerCase();	
      if( (jQuery.inArray(file_extension,allowedExtension)  > -1)){
        flag = true;
        alert("<?php _e('Attached file type not allowed!','taskbuilder')?>");
      };
      <?php
      $max_upload = (int)(ini_get('upload_max_filesize')); ?>
      var current_filesize=file.size/1000000;
      if(current_filesize > <?php echo esc_attr($max_upload); ?>){
				flag = true;
				alert("<?php _e('File size exceed allowed limit!','taskbuilder')?>");
			}
      if (!flag){
        var html_str = '<div class="wppm_attachment">'+
                          '<div class="progress" style="float: none !important; width: unset !important;">'+
                              '<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">'+
                                file.name+
                              '</div>'+
                              '<span class="wppm_attachment_cancel" onclick="wppm_attachment_cancel(this);" style="display:none;"><img src="<?php echo esc_url( WPPM_PLUGIN_URL . 'asset/images/cross_icon.svg'); ?>" alt="delete_icon"></span>'+
                          '</div>'+
                        '</div>';
          jQuery('.wppm_proj_attachment_container').show();
          jQuery('.wppm_proj_attachment_container').append(html_str);
          var attachment = jQuery('.wppm_proj_attachment_container').find('.wppm_attachment').last();
          var data = new FormData();
            data.append('file', file);
            data.append('arr_name', file.name);
            data.append('action', 'wppm_upload_proj_attach_file');
            data.append('nonce', jQuery('#wppm_proj_nonce').val().trim());
            jQuery.ajax({
              type: 'post',
              url: wppm_admin.ajax_url,
              data: data,
              xhr: function(){
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt){
                  if (evt.lengthComputable) {
                    var percentComplete = Math.floor((evt.loaded / evt.total) * 100);
                    jQuery(attachment).find('.progress-bar').css('width',percentComplete+'%');
                  }
                }, false);
                return xhr;
              },
              processData: false,
              contentType: false,
              success: function(response) {
                var return_obj=JSON.parse(response);
                jQuery(attachment).find('.wppm_attachment_cancel').show();
                if( parseInt(return_obj.id) != 0 ){
                  jQuery(attachment).append('<input type="hidden" class="wppm_proj_comment_attachment" name="wppm_proj_comment_attachment[]" value="'+return_obj.id+'">');
                  jQuery(attachment).find('.progress-bar').addClass('progress-bar-success');
                }else {
                  jQuery(attachment).find('.progress-bar').addClass('progress-bar-danger');
                }
              }
            });    
      }
    });
    jQuery('#wppm_proj_attachment_upload').trigger('click');
}
</script>
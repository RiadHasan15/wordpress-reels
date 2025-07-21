<?php
/**
 * Plugin Name: BuddyPress Reels
 * Description: Vertical video reels integrated with BuddyPress profiles.
 * Version: 2.4
 */

if (!defined('ABSPATH')) exit;

function bpr_enqueue_scripts() {
    wp_enqueue_style('bpr-style', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('bpr-script', plugin_dir_url(__FILE__) . 'js/scripts.js', ['jquery'], null, true);
    $opts = get_option('bpr_settings', [
        'video_height' => 'calc(100vh - 80px)',
        'video_width'  => '100%',
        'autoplay'     => '1',
        'default_muted'=> '1',
    ]);
    wp_localize_script('bpr-script', 'bprSettings', $opts);
}
add_action('wp_enqueue_scripts', 'bpr_enqueue_scripts');

function bpr_handle_upload() {
    if (!is_user_logged_in()) wp_die('You must be logged in.');
    check_admin_referer('bpr_upload_nonce');
    if (empty($_FILES['bpr_video']['name'])) {
        wp_redirect(add_query_arg('bpr_msg','error_nofile',wp_get_referer())); exit;
    }

    $post_id = wp_insert_post([
        'post_title'   => sanitize_text_field($_POST['bpr_title']),
        'post_content' => sanitize_textarea_field($_POST['bpr_description']),
        'post_type'    => 'bpr_reel',
        'post_status'  => 'publish',
        'post_author'  => get_current_user_id(),
    ]);
    if (is_wp_error($post_id)) {
        wp_redirect(add_query_arg('bpr_msg','error_post',wp_get_referer())); exit;
    }

    $att_id = media_handle_upload('bpr_video', $post_id);
    if (is_wp_error($att_id)) {
        wp_delete_post($post_id,true);
        wp_redirect(add_query_arg('bpr_msg','error_upload',wp_get_referer())); exit;
    }
    update_post_meta($post_id, 'bpr_video', $att_id);

    if (function_exists('bp_activity_add')) {
        bp_activity_add([
            'user_id' => get_current_user_id(),
            'component' => 'reels',
            'type' => 'bpr_reel_upload',
            'action' => sprintf(__('%s uploaded a reel','buddypress-reels'), bp_core_get_userlink(get_current_user_id())),
            'content' => '<a href="'.esc_url(get_permalink($post_id)).'">'.get_the_title($post_id).'</a>',
            'primary_link' => get_permalink($post_id),
            'item_id' => $post_id,
            'recorded_time' => bp_core_current_time(),
        ]);
    }

    wp_redirect(add_query_arg('bpr_msg','success',wp_get_referer())); exit;
}
add_action('admin_post_bpr_upload_reel', 'bpr_handle_upload');
add_action('admin_post_nopriv_bpr_upload_reel', 'bpr_handle_upload');

add_shortcode('bpr_upload_form', function() {
    if (!is_user_logged_in() || !function_exists('bp_loggedin_user_id')) {
        return '<p>You must be logged in to upload a reel.</p>';
    }
    ob_start();
    if (isset($_GET['bpr_msg'])) {
        $msg = sanitize_text_field($_GET['bpr_msg']);
        $type = strpos($msg, 'error') === 0 ? 'error' : 'success';
        $map = [
            'success'=>'Upload successful! Your reel is now live.',
            'error_post'=>'Error creating the reel post.',
            'error_upload'=>'Error uploading video.',
            'error_nofile'=>'No video file selected.',
        ];
        echo '<div class="bpr-message bpr-'.$type.'">'.esc_html($map[$msg]).'</div>';
    }
    include plugin_dir_path(__FILE__) . 'templates/upload-form.php';
    return ob_get_clean();
});

add_shortcode('bpr_reels_feed', function($atts) {
    $opts = get_option('bpr_settings', [
        'video_height' => 'calc(100vh - 80px)',
        'video_width'  => '100%',
        'autoplay'     => '1',
        'default_muted'=> '1',
    ]);
    $atts = shortcode_atts(['author'=>0], $atts);
    $args = [
        'post_type'=>'bpr_reel',
        'post_status'=>'publish',
        'posts_per_page'=>-1,
        'orderby'=>'date',
        'order'=>'DESC',
    ];
    if (!empty($atts['author']) && intval($atts['author'])>0) {
        $args['author'] = intval($atts['author']);
    }
    $q = new WP_Query($args);
    ob_start();
    echo '<div class="bpr-feed">';
    while($q->have_posts()): $q->the_post();
        $video_url = wp_get_attachment_url(get_post_meta(get_the_ID(),'bpr_video',true));
        if (!$video_url) continue;
        $author_id = get_the_author_meta('ID');
        $avatar = function_exists('bp_core_fetch_avatar') ?
            bp_core_fetch_avatar(['item_id'=>$author_id,'width'=>48,'height'=>48,'html'=>false]) :
            get_avatar_url($author_id,['size'=>48]);
        $profile_url = function_exists('bp_core_get_user_domain') ?
            bp_core_get_user_domain($author_id) :
            get_author_posts_url($author_id);
        $author_name = get_the_author_meta('display_name',$author_id);
        ?>
        <div class="bpr-video-wrapper">
            <video class="bpr-video" playsinline <?php echo $opts['default_muted']=='1'?'muted':''; ?>
                <?php echo $opts['autoplay']=='1'?'autoplay':''; ?> loop preload="metadata">
                <source src="<?php echo esc_url($video_url);?>" type="video/mp4">
            </video>
            <div class="bpr-mute-toggle" data-muted="<?php echo $opts['default_muted']=='1'?'true':'false'; ?>">
                <?php echo $opts['default_muted']=='1'?'🔇':'🔊'; ?>
            </div>
            <div class="bpr-overlay">
                <a href="<?php echo esc_url($profile_url);?>" class="bpr-author-avatar-link" target="_blank" rel="noopener">
                    <div class="bpr-author-avatar" style="background-image:url('<?php echo esc_url($avatar);?>');"></div>
                </a>
                <a href="<?php echo esc_url($profile_url);?>" class="bpr-author-name-link" target="_blank" rel="noopener">
                    <span class="bpr-author-name"><?php echo esc_html($author_name);?></span>
                </a>
                <div class="bpr-author-texts">
                    <h3><?php the_title();?></h3>
                    <p><?php the_content();?></p>
                </div>
            </div>
            <div class="bpr-pause-icon">⏸️</div>
            <div class="bpr-play-icon">▶️</div>
        </div>
    <?php endwhile;
    wp_reset_postdata();
    echo '</div>';
    return ob_get_clean();
});

add_action('bp_setup_nav', function() {
    if (!function_exists('bp_is_active') || !bp_is_active('members')) return;
    bp_core_new_nav_item([
        'name'=>'Reels',
        'slug'=>'reels',
        'position'=>50,
        'screen_function'=>'bpr_bp_reels_screen',
        'show_for_displayed_user'=>true,
        'default_subnav_slug'=>'reels',
    ]);
});
function bpr_bp_reels_screen(){ add_action('bp_template_content','bpr_bp_reels_screen_content'); bp_core_load_template('members/single/plugins'); }
function bpr_bp_reels_screen_content(){ echo do_shortcode('[bpr_reels_feed author="'.bp_displayed_user_id().'"]'); }

add_action('admin_menu', function(){
    add_options_page('Reels Settings','Reels Settings','manage_options','bpr-settings','bpr_settings_page');
});
function bpr_settings_page(){
    if (!current_user_can('manage_options')) return;
    if (isset($_POST['bpr_submit'])) {
        check_admin_referer('bpr_settings_nonce');
        update_option('bpr_settings',[
            'video_height'=>sanitize_text_field($_POST['bpr_video_height']),
            'video_width'=>sanitize_text_field($_POST['bpr_video_width']),
            'autoplay'=>isset($_POST['bpr_autoplay'])?'1':'0',
            'default_muted'=>isset($_POST['bpr_default_muted'])?'1':'0',
        ]);
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }
    $o = get_option('bpr_settings', ['video_height'=>'calc(100vh - 80px)','video_width'=>'100%','autoplay'=>'1','default_muted'=>'1']);
    ?>
    <div class="wrap"><h1>Reels Settings</h1><form method="post">
    <?php wp_nonce_field('bpr_settings_nonce'); ?>
    <table class="form-table">
        <tr><th><label for="bpr_video_height">Video Height</label></th>
            <td><input type="text" id="bpr_video_height" name="bpr_video_height" value="<?php echo esc_attr($o['video_height']);?>" class="regular-text" />
                <p class="description">CSS value e.g. <code>calc(100vh - 80px)</code></p>
            </td>
        </tr>
        <tr><th><label for="bpr_video_width">Video Width</label></th>
            <td><input type="text" id="bpr_video_width" name="bpr_video_width" value="<?php echo esc_attr($o['video_width']);?>" class="regular-text" />
                <p class="description">CSS value e.g. <code>100%</code></p>
            </td>
        </tr>
        <tr><th>Autoplay</th><td><label><input type="checkbox" name="bpr_autoplay" value="1" <?php checked($o['autoplay'],'1');?> /> Enable autoplay</label></td></tr>
        <tr><th>Default Mute</th><td><label><input type="checkbox" name="bpr_default_muted" value="1" <?php checked($o['default_muted'],'1');?> /> Mute by default</label></td></tr>
    </table>
    <?php submit_button('Save Settings','primary','bpr_submit'); ?></form></div>
    <?php
}

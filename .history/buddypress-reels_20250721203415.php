<?php
/**
 * Plugin Name: BuddyPress Reels
 * Description: BuddyPress-integrated Reels with profile grid and overlay playback.
 * Version: 2.5
 */

if (!defined('ABSPATH')) exit;

// Load media upload support
add_action('init', function() {
    if (!function_exists('media_handle_upload')) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
    }
});

// Register reels CPT
add_action('init', function() {
    register_post_type('bpr_reel', [
        'labels' => [
            'name' => 'Reels',
            'singular_name' => 'Reel',
        ],
        'public' => true,
        'supports' => ['title', 'editor', 'author'],
    ]);
});

// Front-end script & styles
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('bpr-style', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('bpr-script', plugin_dir_url(__FILE__) . 'js/scripts.js', ['jquery'], null, true);

    $opts = get_option('bpr_settings', [
        'video_height' => 'calc(100vh - 80px)',
        'video_width'  => '100%',
        'autoplay'     => '1',
        'default_muted'=> '1',
    ]);
    wp_localize_script('bpr-script', 'bprSettings', $opts);
});

// Handle uploads
add_action('admin_post_bpr_upload_reel', 'bpr_handle_upload');
add_action('admin_post_nopriv_bpr_upload_reel', 'bpr_handle_upload');
function bpr_handle_upload(){
    if (!is_user_logged_in()) wp_die('Logged-in users only.');
    check_admin_referer('bpr_upload_nonce');
    if (empty($_FILES['bpr_video']['name'])) {
        wp_redirect(add_query_arg('bpr_msg','error_nofile',wp_get_referer()));
        exit;
    }

    $post_id = wp_insert_post([
        'post_title'   => sanitize_text_field($_POST['bpr_title']),
        'post_content' => sanitize_textarea_field($_POST['bpr_description']),
        'post_type'    => 'bpr_reel',
        'post_status'  => 'publish',
        'post_author'  => get_current_user_id(),
    ]);
    if (is_wp_error($post_id)) {
        wp_redirect(add_query_arg('bpr_msg','error_post',wp_get_referer()));
        exit;
    }

    $att = media_handle_upload('bpr_video', $post_id);
    if (is_wp_error($att)) {
        wp_delete_post($post_id,true);
        wp_redirect(add_query_arg('bpr_msg','error_upload',wp_get_referer()));
        exit;
    }
    update_post_meta($post_id,'bpr_video',$att);

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

    wp_redirect(add_query_arg('bpr_msg','success',wp_get_referer()));
    exit;
}

// Upload form shortcode
add_shortcode('bpr_upload_form', function(){
    if (!is_user_logged_in() || !function_exists('bp_loggedin_user_id')) {
        return '<p>Please log in to upload.</p>';
    }
    ob_start();
    if (isset($_GET['bpr_msg'])) {
        $msgs = [
            'success'=>'Upload successful!',
            'error_post'=>'Post error.',
            'error_upload'=>'Upload error.',
            'error_nofile'=>'Please select a video.',
        ];
        $type = substr($_GET['bpr_msg'],0,5)==='error'?'error':'success';
        echo '<div class="bpr-message bpr-'.$type.'">'.esc_html($msgs[$_GET['bpr_msg']]).'</div>';
    }
    include plugin_dir_path(__FILE__).'templates/upload-form.php';
    return ob_get_clean();
});

// Reels feed shortcode (supports grid)
add_shortcode('bpr_reels_feed', function($atts) {
    $opts = get_option('bpr_settings', [
        'video_height' => 'calc(100vh - 80px)',
        'video_width'  => '100%',
        'autoplay'     => '1',
        'default_muted'=> '1',
    ]);
    $atts = shortcode_atts([
        'author'=>0,
        'grid'=>0,
    ], $atts);

    $q = new WP_Query([
        'post_type'=>'bpr_reel',
        'post_status'=>'publish',
        'author'=>intval($atts['author']),
        'posts_per_page'=>-1,
        'orderby'=>'date',
        'order'=>'DESC'
    ]);

    ob_start();
    if ($atts['grid']) {
        echo '<div class="bpr-grid-feed">';
        while($q->have_posts()): $q->the_post();
            $vid = wp_get_attachment_url(get_post_meta(get_the_ID(),'bpr_video',true));
            if (!$vid) continue;
            ?>
            <div class="bpr-grid-item" data-video="<?php echo esc_attr($vid); ?>" data-title="<?php echo esc_attr(get_the_title()); ?>">
                <video class="bpr-grid-video" muted loop preload="metadata"><source src="<?php echo esc_url($vid);?>" type="video/mp4" /></video>
                <div class="bpr-grid-title"><?php echo esc_html(get_the_title());?></div>
            </div>
            <?php
        endwhile;
        echo '</div>';
        echo '<div id="bpr-overlay" class="bpr-overlay-container"><div class="bpr-overlay-close">&#10005;</div><video id="bpr-overlay-video" controls></video><div id="bpr-overlay-title"></div></div>';
    } else {
        echo '<div class="bpr-feed">';
        while($q->have_posts()): $q->the_post();
            get_template_part('path/to/reel'); // your existing vertical markup
        endwhile;
        echo '</div>';
    }
    wp_reset_postdata();
    return ob_get_clean();
});

// Add BuddyPress Reels tab
add_action('bp_setup_nav', function(){
    if (!bp_is_active('members')) return;
    bp_core_new_nav_item([
      'name' => __('Reels'),
      'slug' => 'reels',
      'screen_function'=>'bpr_bp_reels_screen',
      'position'=>50,
      'show_for_displayed_user'=>true,
      'default_subnav_slug'=>'reels',
    ]);
});
function bpr_bp_reels_screen(){
    add_action('bp_template_content', 'bpr_bp_reels_screen_content');
    bp_core_load_template('members/single/plugins');
}
function bpr_bp_reels_screen_content(){
    echo do_shortcode('[bpr_reels_feed author="'.bp_displayed_user_id().'" grid="1"]');
}

// Settings page
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
    $o = get_option('bpr_settings', []);
    ?>
    <div class="wrap"><h1>Reels Settings</h1><form method="post"><?php wp_nonce_field('bpr_settings_nonce');?>
    <table class="form-table">
      <tr><th><label>Video Height</label></th>
      <td><input type="text" name="bpr_video_height" value="<?php echo esc_attr($o['video_height']);?>" class="regular-text"/><p>e.g., calc(100vh - 80px)</p></td></tr>
      <tr><th><label>Video Width</label></th>
      <td><input type="text" name="bpr_video_width" value="<?php echo esc_attr($o['video_width']);?>" class="regular-text"/><p>e.g., 100% or 480px</p></td></tr>
      <tr><th>Autoplay</th><td><label><input type="checkbox" name="bpr_autoplay" <?php checked($o['autoplay'],'1');?> /> Enable autoplay</label></td></tr>
      <tr><th>Default Mute</th><td><label><input type="checkbox" name="bpr_default_muted" <?php checked($o['default_muted'],'1');?> /> Start muted</label></td></tr>
    </table><p><input type="submit" name="bpr_submit" class="button-primary" value="Save Changes"/></p></form></div>
    <?php
}

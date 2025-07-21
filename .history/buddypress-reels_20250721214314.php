<?php
/**
 * Plugin Name: BuddyPress Reels
 * Description: Short vertical reels + TikTok-style grid playback on BuddyPress profiles.
 * Version: 2.5
 */

if (!defined('ABSPATH')) exit;

// Enqueue styles/scripts
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('bpr-style', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('bpr-script', plugin_dir_url(__FILE__) . 'js/scripts.js', ['jquery'], null, true);

    $opts = get_option('bpr_settings', [
        'video_height'  => 'calc(100vh - 80px)',
        'video_width'   => '100%',
        'autoplay'      => '1',
        'default_muted' => '1'
    ]);
    wp_localize_script('bpr-script', 'bprSettings', $opts);
});

// Handle reel uploads
add_action('admin_post_bpr_upload_reel', 'bpr_handle_upload');
add_action('admin_post_nopriv_bpr_upload_reel', 'bpr_handle_upload');

function bpr_handle_upload() {
    if (!is_user_logged_in()) wp_die('Login required');
    check_admin_referer('bpr_upload_reel');

    if (empty($_FILES['bpr_video']['name'])) {
        wp_redirect(add_query_arg('bpr_msg','error_nofile',wp_get_referer()));
        exit;
    }

    $post_id = wp_insert_post([
        'post_title'   => sanitize_text_field($_POST['bpr_title']),
        'post_content' => sanitize_textarea_field($_POST['bpr_description']),
        'post_type'    => 'bpr_reel',
        'post_status'  => 'publish',
        'post_author'  => get_current_user_id()
    ]);

    if (is_wp_error($post_id)) {
        wp_redirect(add_query_arg('bpr_msg','error_post',wp_get_referer()));
        exit;
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $att_id = media_handle_upload('bpr_video', $post_id);
    if (is_wp_error($att_id)) {
        wp_delete_post($post_id, true);
        wp_redirect(add_query_arg('bpr_msg','error_upload',wp_get_referer()));
        exit;
    }
    update_post_meta($post_id, 'bpr_video', $att_id);

    if (function_exists('bp_activity_add')) {
        bp_activity_add([
            'user_id'      => get_current_user_id(),
            'component'    => 'reels',
            'type'         => 'bpr_reel_upload',
            'action'       => sprintf(__('%s uploaded a Reel','buddypress-reels'), bp_core_get_userlink(get_current_user_id())),
            'content'      => '<a href="'.get_permalink($post_id).'">'.get_the_title($post_id).'</a>',
            'primary_link' => get_permalink($post_id),
            'item_id'      => $post_id,
            'recorded_time'=> bp_core_current_time()
        ]);
    }

    wp_redirect(add_query_arg('bpr_msg','success',wp_get_referer()));
    exit;
}

// Upload form shortcode
add_shortcode('bpr_upload_form', function() {
    if (!is_user_logged_in()) return '<p>Please log in to upload a reel.</p>';
    ob_start();
    if (isset($_GET['bpr_msg'])) {
        $m = $_GET['bpr_msg'];
        $map = [
            'success'      => 'Upload successful!',
            'error_post'   => 'Error creating post.',
            'error_upload' => 'Error uploading video.',
            'error_nofile' => 'No video selected.'
        ];
        $cls = strpos($m, 'error') === 0 ? 'error' : 'success';
        echo '<div class="bpr-message bpr-' . esc_attr($cls) . '">' . esc_html($map[$m] ?? '') . '</div>';
    }
    include plugin_dir_path(__FILE__) . 'templates/upload-form.php';
    return ob_get_clean();
});

// Vertical scroll feed shortcode
add_shortcode('bpr_reels_feed', function() {
    $opts = get_option('bpr_settings');
    $q = new WP_Query([
        'post_type'      => 'bpr_reel',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
    ob_start(); ?>
    <div class="bpr-feed">
    <?php while ($q->have_posts()): $q->the_post();
        $vid = wp_get_attachment_url(get_post_meta(get_the_ID(),'bpr_video',true));
        if (!$vid) continue;
        $aid = get_the_author_meta('ID');
        $avatar = function_exists('bp_core_fetch_avatar') ? bp_core_fetch_avatar(['item_id'=>$aid,'html'=>false,'width'=>48,'height'=>48]) : get_avatar_url($aid,['size'=>48]);
        $purl = function_exists('bp_core_get_user_domain') ? bp_core_get_user_domain($aid) : '';
        ?>
        <div class="bpr-video-wrapper">
            <video class="bpr-video" playsinline <?php echo $opts['default_muted']=='1'?'muted':''; ?> <?php echo $opts['autoplay']=='1'?'autoplay':''; ?> loop preload="metadata">
                <source src="<?php echo esc_url($vid); ?>" type="video/mp4">
            </video>
            <div class="bpr-mute-toggle">🔇</div>
            <div class="bpr-overlay">
                <a href="<?php echo esc_url($purl); ?>" class="bpr-author-avatar-link">
                    <div class="bpr-author-avatar" style="background-image:url('<?php echo esc_url($avatar); ?>')"></div>
                </a>
                <a href="<?php echo esc_url($purl); ?>" class="bpr-author-name-link">
                    <span class="bpr-author-name"><?php echo esc_html(get_the_author_meta('display_name',$aid)); ?></span>
                </a>
                <div class="bpr-author-texts">
                    <h3><?php the_title(); ?></h3>
                    <p><?php the_content(); ?></p>
                </div>
            </div>
            <div class="bpr-pause-icon">⏸️</div>
            <div class="bpr-play-icon">▶️</div>
        </div>
    <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php
    return ob_get_clean();
});

// Profile grid view shortcode
add_shortcode('bpr_reels_grid', function() {
    ob_start();
    $uid = bp_displayed_user_id();
    $q = new WP_Query([
        'post_type'      => 'bpr_reel',
        'author'         => $uid,
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
    ?>
    <div class="bpr-grid-wrapper">
        <?php while ($q->have_posts()): $q->the_post();
            $vid = wp_get_attachment_url(get_post_meta(get_the_ID(),'bpr_video',true));
            if (!$vid) continue; ?>
            <div class="bpr-grid-item" data-video="<?php echo esc_attr($vid); ?>">
                <video muted loop preload="metadata">
                    <source src="<?php echo esc_url($vid); ?>" type="video/mp4">
                </video>
            </div>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <div class="bpr-modal"><div class="bpr-modal-content">
        <span class="bpr-close">&#10005;</span>
        <video id="bpr-full-video" controls autoplay muted></video>
        <div class="bpr-modal-user-info">
            <?php echo bp_core_fetch_avatar(['item_id'=>$uid,'html'=>true,'width'=>32,'height'=>32]); ?>
            <a href="<?php echo bp_core_get_user_domain($uid); ?>"><?php echo bp_core_get_username($uid); ?></a>
        </div>
    </div></div>
    <?php
    return ob_get_clean();
});

// Add BuddyPress profile tab
add_action('bp_setup_nav', function() {
    if (!bp_is_active('members')) return;
    bp_core_new_nav_item([
        'name'                => 'Reels',
        'slug'                => 'reels',
        'position'            => 50,
        'screen_function'     => 'bpr_profile_reels_screen',
        'default_subnav_slug' => 'reels'
    ]);
});
function bpr_profile_reels_screen() {
    add_action('bp_template_content', function() {
        echo do_shortcode('[bpr_reels_grid]');
    });
    bp_core_load_template('members/single/plugins');
}

<?php
/**
 * Plugin Name: BuddyPress Reels
 * Description: Vertical video reels with frontend upload and BuddyPress integration.
 * Version: 2.3
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;

// Load media functions
add_action('init', function() {
    if (!function_exists('media_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
    }
});

// Register custom post type for reels
add_action('init', function() {
    register_post_type('bpr_reel', [
        'labels' => [
            'name' => 'Reels',
            'singular_name' => 'Reel',
        ],
        'public' => true,
        'supports' => ['title', 'editor', 'author'],
        'show_in_rest' => true,
        'has_archive' => false,
    ]);
});

// Enqueue scripts and styles
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('bpr-style', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('bpr-script', plugin_dir_url(__FILE__) . 'js/scripts.js', ['jquery'], null, true);

    $opts = get_option('bpr_settings', [
        'video_height' => 'calc(100vh - 80px)',
        'video_width' => '100%',
        'autoplay' => '1',
        'default_muted' => '1',
    ]);
    wp_localize_script('bpr-script', 'bprSettings', $opts);
});

// Frontend reel upload handling
add_action('admin_post_bpr_upload_reel', 'bpr_handle_upload');
add_action('admin_post_nopriv_bpr_upload_reel', 'bpr_handle_upload');

function bpr_handle_upload() {
    if (!is_user_logged_in()) {
        wp_die('You must be logged in to upload.');
    }

    check_admin_referer('bpr_upload_nonce');

    if (empty($_FILES['bpr_video']['name'])) {
        wp_redirect(add_query_arg('bpr_msg', 'error_nofile', wp_get_referer()));
        exit;
    }

    $title = sanitize_text_field($_POST['bpr_title']);
    $description = sanitize_textarea_field($_POST['bpr_description']);

    $post_id = wp_insert_post([
        'post_title' => $title,
        'post_content' => $description,
        'post_type' => 'bpr_reel',
        'post_status' => 'publish',
        'post_author' => get_current_user_id(),
    ]);

    if (is_wp_error($post_id)) {
        wp_redirect(add_query_arg('bpr_msg', 'error_post', wp_get_referer()));
        exit;
    }

    $attachment_id = media_handle_upload('bpr_video', $post_id);
    if (is_wp_error($attachment_id)) {
        wp_delete_post($post_id, true);
        wp_redirect(add_query_arg('bpr_msg', 'error_upload', wp_get_referer()));
        exit;
    }

    update_post_meta($post_id, 'bpr_video', $attachment_id);

    // BuddyPress Activity Stream integration
    if (function_exists('bp_activity_add')) {
        $user_id = get_current_user_id();
        $post_url = get_permalink($post_id);

        bp_activity_add([
            'user_id' => $user_id,
            'component' => 'reels',
            'type' => 'bpr_reel_upload',
            'action' => sprintf(
                __('%s uploaded a new reel', 'buddypress-reels'),
                bp_core_get_userlink($user_id)
            ),
            'content' => sprintf('<a href="%s">%s</a>', esc_url($post_url), esc_html($title)),
            'primary_link' => esc_url($post_url),
            'item_id' => $post_id,
            'secondary_item_id' => 0,
            'recorded_time' => bp_core_current_time(),
        ]);
    }

    wp_redirect(add_query_arg('bpr_msg', 'success', wp_get_referer()));
    exit;
}

// Shortcode for frontend upload form
add_shortcode('bpr_upload_form', function() {
    if (!is_user_logged_in() || !function_exists('bp_loggedin_user_id')) {
        return '<p>You must be logged in to upload a reel.</p>';
    }

    ob_start();

    if (isset($_GET['bpr_msg'])) {
        $msg = sanitize_text_field($_GET['bpr_msg']);
        if ($msg === 'success') {
            echo '<div class="bpr-message bpr-success">Upload successful! Your reel is now live.</div>';
        } elseif ($msg === 'error_post') {
            echo '<div class="bpr-message bpr-error">Error creating the reel post. Please try again.</div>';
        } elseif ($msg === 'error_upload') {
            echo '<div class="bpr-message bpr-error">Error uploading video. Please try again.</div>';
        } elseif ($msg === 'error_nofile') {
            echo '<div class="bpr-message bpr-error">No video file selected. Please choose a video.</div>';
        }
    }

    include plugin_dir_path(__FILE__) . 'templates/upload-form.php';

    return ob_get_clean();
});

// Reels feed shortcode, supports author filter by param (optional)
add_shortcode('bpr_reels_feed', function($atts) {
    $opts = get_option('bpr_settings', [
        'video_height' => 'calc(100vh - 80px)',
        'video_width' => '100%',
        'autoplay' => '1',
        'default_muted' => '1',
    ]);

    $atts = shortcode_atts([
        'author' => 0, // user ID filter, 0 = all users
    ], $atts);

    $args = [
        'post_type' => 'bpr_reel',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    ];

    if (!empty($atts['author']) && intval($atts['author']) > 0) {
        $args['author'] = intval($atts['author']);
    }

    $query = new WP_Query($args);

    ob_start();
    ?>
    <div class="bpr-feed">
        <?php
        while ($query->have_posts()) : $query->the_post();
            $video_id = get_post_meta(get_the_ID(), 'bpr_video', true);
            $video_url = wp_get_attachment_url($video_id);
            if (!$video_url) continue;

            $author_id = get_the_author_meta('ID');
            if (function_exists('bp_core_fetch_avatar')) {
                $avatar = bp_core_fetch_avatar([
                    'item_id' => $author_id,
                    'type' => 'full',
                    'width' => 48,
                    'height' => 48,
                    'html' => false,
                ]);
            } else {
                $avatar = get_avatar_url($author_id, ['size' => 48]);
            }

            $profile_url = function_exists('bp_core_get_user_domain') ? bp_core_get_user_domain($author_id) : get_author_posts_url($author_id);

            $author_display_name = get_the_author_meta('display_name', $author_id);
            ?>
            <div class="bpr-video-wrapper">
                <video 
                    class="bpr-video" 
                    playsinline 
                    <?php echo $opts['default_muted'] === '1' ? 'muted' : ''; ?>
                    <?php echo $opts['autoplay'] === '1' ? 'autoplay' : ''; ?>
                    loop 
                    preload="metadata"
                >
                    <source src="<?php echo esc_url($video_url); ?>" type="video/mp4" />
                    Your browser does not support the video tag.
                </video>
                <div class="bpr-mute-toggle" data-muted="<?php echo $opts['default_muted'] === '1' ? 'true' : 'false'; ?>" title="Toggle Sound"><?php echo $opts['default_muted'] === '1' ? '🔇' : '🔊'; ?></div>
                <div class="bpr-overlay">
                    <a href="<?php echo esc_url($profile_url); ?>" class="bpr-author-avatar-link" target="_blank" rel="noopener" title="View Profile">
                        <div class="bpr-author-avatar" style="background-image:url('<?php echo esc_url($avatar); ?>');"></div>
                    </a>
                    <a href="<?php echo esc_url($profile_url); ?>" class="bpr-author-name-link" target="_blank" rel="noopener" title="View Profile">
                        <span class="bpr-author-name"><?php echo esc_html($author_display_name); ?></span>
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

// Add BuddyPress profile nav tab for Reels
add_action('bp_setup_nav', function() {
    if (!function_exists('bp_is_active') || !bp_is_active('members')) {
        return;
    }

    global $bp;

    bp_core_new_nav_item([
        'name' => __('Reels', 'buddypress-reels'),
        'slug' => 'reels',
        'screen_function' => 'bpr_bp_reels_screen',
        'position' => 50,
        'show_for_displayed_user' => true,
        'default_subnav_slug' => 'reels',
    ]);
});

// Screen function to render the reels tab content
function bpr_bp_reels_screen() {
    add_action('bp_template_content', 'bpr_bp_reels_screen_content');
    bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function bpr_bp_reels_screen_content() {
    $displayed_user_id = bp_displayed_user_id();

    // Show reels only by the displayed user
    echo do_shortcode('[bpr_reels_feed author="' . intval($displayed_user_id) . '"]');
}

// Admin settings page (same as before)
add_action('admin_menu', function() {
    add_options_page('Reels Settings', 'Reels Settings', 'manage_options', 'bpr-settings', 'bpr_settings_page');
});

function bpr_settings_page() {
    if (!current_user_can('manage_options')) return;

    if (isset($_POST['bpr_submit'])) {
        check_admin_referer('bpr_settings_nonce');

        $height = sanitize_text_field($_POST['bpr_video_height']);
        $width = sanitize_text_field($_POST['bpr_video_width']);
        $autoplay = isset($_POST['bpr_autoplay']) ? '1' : '0';
        $muted = isset($_POST['bpr_default_muted']) ? '1' : '0';

        update_option('bpr_settings', [
            'video_height' => $height,
            'video_width' => $width,
            'autoplay' => $autoplay,
            'default_muted' => $muted,
        ]);

        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    $opts = get_option('bpr_settings', [
        'video_height' => 'calc(100vh - 80px)',
        'video_width' => '100%',
        'autoplay' => '1',
        'default_muted' => '1',
    ]);
    ?>
    <div class="wrap">
        <h1>Reels Settings</h1>
        <form method="post" action="">
            <?php wp_nonce_field('bpr_settings_nonce'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="bpr_video_height">Video Height</label></th>
                    <td><input type="text" id="bpr_video_height" name="bpr_video_height" value="<?php echo esc_attr($opts['video_height']); ?>" class="regular-text" />
                    <p class="description">CSS value, e.g., <code>calc(100vh - 80px)</code>, <code>600px</code>, <code>80%</code></p></td>
                </tr>
                <tr>
                    <th scope="row"><label for="bpr_video_width">Video Width</label></th>
                    <td><input type="text" id="bpr_video_width" name="bpr_video_width" value="<?php echo esc_attr($opts['video_width']); ?>" class="regular-text" />
                    <p class="description">CSS value, e.g., <code>100%</code>, <code>400px</code>, <code>80vw</code></p></td>
                </tr>
                <tr>
                    <th scope="row">Autoplay Videos</th>
                    <td><label><input type="checkbox" name="bpr_autoplay" <?php checked($opts['autoplay'], '1'); ?> /> Enable autoplay</label></td>
                </tr>
                <tr>
                    <th scope="row">Default Mute State</th>
                    <td><label><input type="checkbox" name="bpr_default_muted" <?php checked($opts['default_muted'], '1'); ?> /> Mute videos by default</label></td>
                </tr>
            </table>
            <p><input type="submit" name="bpr_submit" class="button button-primary" value="Save Changes" /></p>
        </form>
    </div>
    <?php
}

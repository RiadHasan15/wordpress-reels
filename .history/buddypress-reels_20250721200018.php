<?php
/**
 * Plugin Name: BuddyPress Reels
 * Description: Vertical video reels with frontend upload and BuddyPress integration.
 * Version: 2.1
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;

// Include media functions for uploads
add_action('init', function() {
    if (!function_exists('media_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
    }
});

// Register Custom Post Type
add_action('init', function() {
    register_post_type('bpr_reel', [
        'labels' => [
            'name' => 'Reels',
            'singular_name' => 'Reel',
        ],
        'public' => true,
        'supports' => ['title', 'editor', 'author'],
        'show_in_rest' => true,
    ]);
});

// Enqueue scripts and styles
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('bpr-style', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('bpr-script', plugin_dir_url(__FILE__) . 'js/scripts.js', ['jquery'], null, true);

    $opts = get_option('bpr_settings', [
        'video_height' => '100vh',
        'video_width' => '100%',
        'autoplay' => '1',
        'default_muted' => '1',
    ]);
    wp_localize_script('bpr-script', 'bprSettings', $opts);
});

// Handle frontend video upload form submission
add_action('admin_post_bpr_upload_reel', 'bpr_handle_upload');
add_action('admin_post_nopriv_bpr_upload_reel', 'bpr_handle_upload');

function bpr_handle_upload() {
    if (!is_user_logged_in()) {
        wp_die('You must be logged in to upload.');
    }

    check_admin_referer('bpr_upload_nonce');

    if (!empty($_FILES['bpr_video']['name'])) {
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

        wp_redirect(add_query_arg('bpr_msg', 'success', wp_get_referer()));
        exit;
    } else {
        wp_redirect(add_query_arg('bpr_msg', 'error_nofile', wp_get_referer()));
        exit;
    }
}

// Shortcode: Frontend Upload Form
add_shortcode('bpr_upload_form', function() {
    ob_start();

    if (!is_user_logged_in()) {
        echo '<p>You must be logged in to upload a reel.</p>';
        return ob_get_clean();
    }

    // Show messages if set in URL
    if (isset($_GET['bpr_msg'])) {
        $msg = $_GET['bpr_msg'];
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

// ... The rest remains unchanged (shortcode for feed, settings page, etc.)

// Add Admin Settings Page
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
        'video_height' => '100vh',
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
                    <p class="description">CSS value, e.g., <code>100vh</code>, <code>600px</code>, <code>80%</code></p></td>
                </tr>
                <tr>
                    <th scope="row"><label for="bpr_video_width">Video Width</label></th>
                    <td><input type="text" id="bpr_video_width" name="bpr_video_width" value="<?php echo esc_attr($opts['video_width']); ?>" class="regular-text" />
                    <p class="description">CSS value, e.g., <code>100%</code>, <code>400px</code>, <code>80vw</code></p></td>
                </tr>
                <tr>
                    <th scope="row">Autoplay Videos</th>
                    <td><label><input type="checkbox" name="bpr_autoplay" value="1" <?php checked($opts['autoplay'], '1'); ?> /> Enable autoplay</label></td>
                </tr>
                <tr>
                    <th scope="row">Default Mute State</th>
                    <td><label><input type="checkbox" name="bpr_default_muted" value="1" <?php checked($opts['default_muted'], '1'); ?> /> Videos start muted by default</label></td>
                </tr>
            </table>
            <?php submit_button('Save Settings', 'primary', 'bpr_submit'); ?>
        </form>
    </div>
    <?php
}

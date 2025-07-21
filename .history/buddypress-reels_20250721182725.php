<?php
/**
 * Plugin Name: BuddyPress Reels
 * Description: Adds Instagram-like vertical reels to BuddyPress with front-end upload.
 * Version: 1.0
 * Author: Your Name
 */

defined('ABSPATH') || exit;

// Enqueue Scripts & Styles
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('bp-reels-style', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('bp-reels-js', plugin_dir_url(__FILE__) . 'js/scripts.js', [], false, true);
});

// Register Custom Post Type
add_action('init', function () {
    register_post_type('bp_reel', [
        'labels' => [
            'name' => 'Reels',
            'singular_name' => 'Reel'
        ],
        'public' => true,
        'has_archive' => false,
        'supports' => ['title', 'editor', 'author', 'thumbnail'],
        'show_in_rest' => true,
    ]);
});

// Handle Frontend Upload
add_action('init', function () {
    if (isset($_POST['bp_reel_nonce']) && wp_verify_nonce($_POST['bp_reel_nonce'], 'bp_reel_upload')) {
        $title = sanitize_text_field($_POST['reel_title']);
        $desc = sanitize_textarea_field($_POST['reel_description']);
        $video = $_FILES['reel_video'];

        if (!empty($title) && $video['size'] > 0) {
            $post_id = wp_insert_post([
                'post_title' => $title,
                'post_content' => $desc,
                'post_type' => 'bp_reel',
                'post_status' => 'publish',
                'post_author' => get_current_user_id()
            ]);

            // Handle video upload
            require_once ABSPATH . 'wp-admin/includes/file.php';
            $uploaded = media_handle_upload('reel_video', $post_id);
            if (!is_wp_error($uploaded)) {
                update_post_meta($post_id, '_reel_video_url', wp_get_attachment_url($uploaded));
            }
        }
        wp_redirect(add_query_arg('reel_uploaded', '1', wp_get_referer()));
        exit;
    }
});

// Shortcode for Upload Form
add_shortcode('bp_reel_upload_form', function () {
    if (!is_user_logged_in()) {
        return '<p>You must be logged in to upload a reel.</p>';
    }
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/upload-form.php';
    return ob_get_clean();
});

// Display Reels (Shortcode)
add_shortcode('bp_reels_feed', function () {
    ob_start();
    $query = new WP_Query(['post_type' => 'bp_reel', 'posts_per_page' => -1]);
    echo '<div class="bp-reels-container">';
    while ($query->have_posts()) : $query->the_post();
        $video_url = get_post_meta(get_the_ID(), '_reel_video_url', true);
        ?>
        <div class="wpvr-video-wrapper">
            <video src="<?php echo esc_url($video_url); ?>" loop autoplay playsinline muted></video>
            <div class="wpvr-sound-toggle">🔇</div>
            <div class="wpvr-playpause-anim"></div>
            <div class="wpvr-meta">
                <h4><?php the_title(); ?></h4>
                <p><?php the_content(); ?></p>
            </div>
        </div>
        <?php
    endwhile;
    echo '</div>';
    wp_reset_postdata();
    return ob_get_clean();
});

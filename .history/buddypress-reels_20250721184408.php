<?php
/**
 * Plugin Name: BuddyPress Reels
 * Description: Frontend video upload and reels feed with BuddyPress integration.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;

function bpr_enqueue_assets() {
    wp_enqueue_style('bpr-style', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('bpr-scripts', plugin_dir_url(__FILE__) . 'js/scripts.js', ['jquery'], null, true);
}
add_action('wp_enqueue_scripts', 'bpr_enqueue_assets');

// Register Custom Post Type for Reels
function bpr_register_reels_cpt() {
    register_post_type('bpr_reel', [
        'label' => 'Reels',
        'public' => true,
        'supports' => ['title', 'editor', 'author'],
    ]);
}
add_action('init', 'bpr_register_reels_cpt');

// Handle Upload
function bpr_handle_upload() {
    if (isset($_POST['bpr_upload_nonce']) && wp_verify_nonce($_POST['bpr_upload_nonce'], 'bpr_upload')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $title = sanitize_text_field($_POST['bpr_title']);
        $description = sanitize_textarea_field($_POST['bpr_description']);

        $post_id = wp_insert_post([
            'post_title' => $title,
            'post_content' => $description,
            'post_type' => 'bpr_reel',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ]);

        if ($post_id && !empty($_FILES['bpr_video']['name'])) {
            $attachment_id = media_handle_upload('bpr_video', $post_id);
            if (!is_wp_error($attachment_id)) {
                update_post_meta($post_id, 'bpr_video_url', wp_get_attachment_url($attachment_id));
            }
        }
    }
}
add_action('init', 'bpr_handle_upload');

// Shortcode: Upload Form
function bpr_upload_form_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>You must be logged in to upload reels.</p>';
    }
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/upload-form.php';
    return ob_get_clean();
}
add_shortcode('bpr_upload_form', 'bpr_upload_form_shortcode');

// Shortcode: Reels Feed
function bpr_reels_feed_shortcode() {
    $args = ['post_type' => 'bpr_reel', 'posts_per_page' => -1];
    $query = new WP_Query($args);

    ob_start();
    if ($query->have_posts()):
        echo '<div class="bpr-reels-container">';
        echo '<div class="bpr-sound-toggle">🔇</div>';
        while ($query->have_posts()): $query->the_post();
            $video_url = get_post_meta(get_the_ID(), 'bpr_video_url', true);
            ?>
            <div class="bpr-reel-frame">
                <video class="bpr-video" playsinline autoplay loop muted>
                    <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                </video>
                <div class="bpr-overlay">
                    <div class="bpr-title"><?php the_title(); ?></div>
                    <div class="bpr-description"><?php the_content(); ?></div>
                </div>
                <div class="bpr-pause-icon">⏸</div>
                <div class="bpr-play-icon">▶</div>
            </div>
            <?php
        endwhile;
        echo '</div>';
        wp_reset_postdata();
    endif;
    return ob_get_clean();
}
add_shortcode('bpr_reels_feed', 'bpr_reels_feed_shortcode');

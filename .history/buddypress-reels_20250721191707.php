<?php
/**
 * Plugin Name: BuddyPress Reels
 * Description: Vertical video reels with frontend upload and BuddyPress integration.
 * Version: 1.7
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
});

// Handle frontend video upload form submission
add_action('admin_post_bpr_upload_reel', 'bpr_handle_upload');
add_action('admin_post_nopriv_bpr_upload_reel', 'bpr_handle_upload');

function bpr_handle_upload() {
    if (!is_user_logged_in()) {
        wp_die('You must be logged in to upload.');
    }

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
            wp_die('Error creating post.');
        }

        $attachment_id = media_handle_upload('bpr_video', $post_id);

        if (is_wp_error($attachment_id)) {
            wp_delete_post($post_id, true);
            wp_die('Error uploading video.');
        }

        update_post_meta($post_id, 'bpr_video', $attachment_id);

        wp_redirect(get_permalink($post_id));
        exit;
    } else {
        wp_die('No video file selected.');
    }
}

// Shortcode: Frontend Upload Form
add_shortcode('bpr_upload_form', function() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/upload-form.php';
    return ob_get_clean();
});

// Shortcode: Reels Feed
add_shortcode('bpr_reels_feed', function() {
    $query = new WP_Query([
        'post_type' => 'bpr_reel',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);

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
                    muted 
                    autoplay 
                    loop 
                    preload="metadata"
                >
                    <source src="<?php echo esc_url($video_url); ?>" type="video/mp4" />
                    Your browser does not support the video tag.
                </video>
                <div class="bpr-mute-toggle" data-muted="true" title="Toggle Sound">🔇</div>
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

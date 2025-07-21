<?php
/**
 * Plugin Name: BuddyPress Reels
 * Description: Instagram-style vertical video reels with frontend upload and BuddyPress support.
 * Version: 1.0
 * Author: Your Name
 */

defined('ABSPATH') || exit;

// Register CPT
add_action('init', function () {
    register_post_type('bp_reel', [
        'labels' => [
            'name' => 'Reels',
            'singular_name' => 'Reel'
        ],
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor', 'author'],
        'show_in_rest' => true,
    ]);
});

// Enqueue assets
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('bpr-style', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('bpr-script', plugin_dir_url(__FILE__) . 'js/scripts.js', [], false, true);
});

// Handle upload
add_action('init', function () {
    if (isset($_POST['bpr_nonce']) && wp_verify_nonce($_POST['bpr_nonce'], 'bpr_upload') && is_user_logged_in()) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $title = sanitize_text_field($_POST['reel_title']);
        $desc  = sanitize_textarea_field($_POST['reel_description']);
        $attachment_id = media_handle_upload('reel_video', 0);

        if (!is_wp_error($attachment_id)) {
            $post_id = wp_insert_post([
                'post_type'    => 'bp_reel',
                'post_title'   => $title,
                'post_content' => $desc,
                'post_status'  => 'publish',
                'post_author'  => get_current_user_id(),
            ]);
            if ($post_id) {
                update_post_meta($post_id, 'reel_video_id', $attachment_id);
            }
        }
        wp_redirect(add_query_arg('uploaded', '1', wp_get_referer()));
        exit;
    }
});

// Upload form shortcode
add_shortcode('bpr_upload_form', function () {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/upload-form.php';
    return ob_get_clean();
});

// Reels feed shortcode
add_shortcode('bpr_reels_feed', function () {
    $q = new WP_Query([
        'post_type' => 'bp_reel',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);

    ob_start();
    echo '<div class="bpr-reels-wrapper">';
    while ($q->have_posts()): $q->the_post();
        $vid = get_post_meta(get_the_ID(), 'reel_video_id', true);
        $url = wp_get_attachment_url($vid);
        ?>
        <div class="bpr-reel">
  <video muted loop playsinline preload="auto" src="..."></video>
  <div class="bpr-mute-toggle">🔇</div>
  <div class="bpr-pp-anim"></div>
  <div class="bpr-info">
    <h3>Title</h3>
    <p>Description</p>
  </div>
</div>

        <?php
    endwhile;
    echo '</div>';
    wp_reset_postdata();

    return ob_get_clean();
});

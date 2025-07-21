<?php
/**
 * Plugin Name: BuddyPress Reels
 * Description: A vertical reels feature for BuddyPress users.
 * Version: 1.0
 * Author: Your Name
 */

add_action('init', function() {
    if (!function_exists('media_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
    }
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('bpr-style', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('bpr-script', plugin_dir_url(__FILE__) . 'js/scripts.js', ['jquery'], null, true);
});

add_shortcode('bpr_reels_feed', function () {
    ob_start();
    $args = [
        'post_type' => 'bpr_reel',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    ];
    $query = new WP_Query($args);
    echo '<div class="bpr-feed">';
    echo '<div class="bpr-mute-toggle" data-muted="true">🔇</div>';
    while ($query->have_posts()) {
        $query->the_post();
        $video = get_attached_media('video', get_the_ID());
        $video_url = wp_get_attachment_url(array_keys($video)[0]);
        ?>
        <div class="bpr-video-wrapper">
            <video class="bpr-video" playsinline muted loop>
                <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
            </video>
            <div class="bpr-overlay">
                <h3><?php the_title(); ?></h3>
                <p><?php the_content(); ?></p>
            </div>
            <div class="bpr-pause-icon">⏸️</div>
        </div>
        <?php
    }
    echo '</div>';
    wp_reset_postdata();
    return ob_get_clean();
});

add_shortcode('bpr_upload_form', function () {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/upload-form.php';
    return ob_get_clean();
});

add_action('init', function () {
    register_post_type('bpr_reel', [
        'label' => 'Reels',
        'public' => true,
        'supports' => ['title', 'editor', 'author'],
        'show_in_rest' => true,
    ]);
});

add_action('admin_post_bpr_upload_reel', 'bpr_handle_upload');
add_action('admin_post_nopriv_bpr_upload_reel', 'bpr_handle_upload');

function bpr_handle_upload() {
    if (!empty($_FILES['bpr_video']['name'])) {
        $post_id = wp_insert_post([
            'post_title'   => sanitize_text_field($_POST['bpr_title']),
            'post_content' => sanitize_textarea_field($_POST['bpr_description']),
            'post_type'    => 'bpr_reel',
            'post_status'  => 'publish',
            'post_author'  => get_current_user_id()
        ]);

        $attachment_id = media_handle_upload('bpr_video', $post_id);
        if (is_wp_error($attachment_id)) {
            wp_die('Error uploading video.');
        }
    }
    wp_redirect(home_url());
    exit;
}

<?php
/**
 * Plugin Name: BuddyPress Reels
 * Description: Upload and display vertical video reels with BuddyPress.
 * Version: 1.0
 * Author: Your Name
 */

add_action('init', function () {
    if (is_user_logged_in() && isset($_POST['bpr_submit_reel'])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $title = sanitize_text_field($_POST['bpr_title']);
        $description = sanitize_textarea_field($_POST['bpr_description']);
        $video = $_FILES['bpr_video'];

        $attachment_id = media_handle_upload('bpr_video', 0);
        if (!is_wp_error($attachment_id)) {
            $post_id = wp_insert_post([
                'post_title' => $title,
                'post_content' => $description,
                'post_type' => 'bpr_reel',
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
                'meta_input' => [
                    'bpr_video' => $attachment_id,
                ]
            ]);
        }
    }
});

add_action('init', function () {
    register_post_type('bpr_reel', [
        'labels' => [
            'name' => 'Reels',
            'singular_name' => 'Reel',
        ],
        'public' => true,
        'has_archive' => false,
        'supports' => ['title', 'editor', 'author'],
        'show_in_rest' => true,
    ]);
});

add_shortcode('bpr_upload_form', function () {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/upload-form.php';
    return ob_get_clean();
});

add_shortcode('bpr_reels_feed', function () {
    $reels = new WP_Query([
        'post_type' => 'bpr_reel',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);

    ob_start();
    echo '<div class="bpr-reels-wrapper">';
    while ($reels->have_posts()) {
        $reels->the_post();
        $video_id = get_post_meta(get_the_ID(), 'bpr_video', true);
        $video_url = wp_get_attachment_url($video_id);
        ?>
        <div class="bpr-reel">
            <video 
                src="<?php echo esc_url($video_url); ?>" 
                muted 
                loop 
                playsinline 
                preload="metadata"
            ></video>
            <div class="bpr-overlay">
                <div class="bpr-meta">
                    <h3><?php the_title(); ?></h3>
                    <p><?php the_content(); ?></p>
                </div>
                <div class="bpr-sound-toggle">🔇</div>
                <div class="bpr-pause-icon">⏸</div>
                <div class="bpr-play-icon">▶️</div>
            </div>
        </div>
        <?php
    }
    wp_reset_postdata();
    echo '</div>';
    return ob_get_clean();
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('bpr-style', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('bpr-script', plugin_dir_url(__FILE__) . 'js/scripts.js', ['jquery'], null, true);
});

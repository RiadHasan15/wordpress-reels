<?php
/**
 * Plugin Name: WP Video Reels
 * Description: Users can upload vertical video reels with title, description, tags; reels show in vertical scroll feed.
 * Version: 1.2
 * Author: Your Name
 */

// Register custom post type 'reel' with tags enabled
function wpvr_register_reel_post_type() {
    register_post_type('reel', array(
        'labels' => array(
            'name' => 'Reels',
            'singular_name' => 'Reel',
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields', 'excerpt'),
        'taxonomies' => array('post_tag'),
        'menu_icon' => 'dashicons-video-alt3',
        'show_in_rest' => true, // Enable Gutenberg support if needed
    ));
}
add_action('init', 'wpvr_register_reel_post_type');

// Enqueue styles and scripts
function wpvr_enqueue_scripts() {
    wp_enqueue_style('wpvr-style', plugin_dir_url(__FILE__) . 'style.css');
    wp_enqueue_script('wpvr-script', plugin_dir_url(__FILE__) . 'script.js', array(), false, true);
}
add_action('wp_enqueue_scripts', 'wpvr_enqueue_scripts');

// Upload form shortcode
function wpvr_upload_form_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please <a href="' . wp_login_url() . '">log in</a> to upload a reel.</p>';
    }

    $message = '';
    if (isset($_POST['wpvr_upload_submit']) && !empty($_FILES['wpvr_video_file']['name'])) {
        $user_id = get_current_user_id();

        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $uploaded = media_handle_upload('wpvr_video_file', 0);

        if (!is_wp_error($uploaded)) {
            $post_id = wp_insert_post(array(
                'post_type'    => 'reel',
                'post_title'   => sanitize_text_field($_POST['wpvr_title']),
                'post_content' => sanitize_textarea_field($_POST['wpvr_description']),
                'post_status'  => 'publish',
                'post_author'  => $user_id,
            ));

            if ($post_id) {
                update_post_meta($post_id, 'video_url', wp_get_attachment_url($uploaded));

                if (!empty($_POST['wpvr_tags'])) {
                    $tags = sanitize_text_field($_POST['wpvr_tags']);
                    wp_set_post_tags($post_id, $tags, true);
                }

                $message = '<p class="success-message">✅ Upload successful!</p>';
            } else {
                $message = '<p class="error-message">❌ Failed to create reel post.</p>';
            }
        } else {
            $message = '<p class="error-message">❌ Upload failed. Please try again.</p>';
        }
    }

    ob_start();
    ?>
    <?php echo $message; ?>
    <form method="post" enctype="multipart/form-data" class="wpvr-upload-form">
        <label>Title:</label><br>
        <input type="text" name="wpvr_title" required><br><br>

        <label>Description:</label><br>
        <textarea name="wpvr_description" rows="3" required></textarea><br><br>

        <label>Tags (comma separated):</label><br>
        <input type="text" name="wpvr_tags" placeholder="tag1, tag2, tag3"><br><br>

        <label>Select MP4 Video:</label><br>
        <input type="file" name="wpvr_video_file" accept="video/mp4" required><br><br>

        <input type="submit" name="wpvr_upload_submit" value="Upload Reel">
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('video_reel_upload', 'wpvr_upload_form_shortcode');

// Feed shortcode to show reels
function wpvr_feed_shortcode() {
    $args = array(
        'post_type'      => 'reel',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );
    $query = new WP_Query($args);

    ob_start();
    echo '<div class="wpvr-reels-container">';
    while ($query->have_posts()) {
        $query->the_post();
        $video_url = get_post_meta(get_the_ID(), 'video_url', true);
        if ($video_url) {
            $tags = get_the_term_list(get_the_ID(), 'post_tag', '', ', ');
            ?>
            <div class="wpvr-reel">
                <div class="wpvr-video-frame">
                    <video
                        src="<?php echo esc_url($video_url); ?>"
                        autoplay muted loop playsinline
                        class="reel-video"
                    ></video>
                    <div class="wpvr-sound-toggle">🔇</div>
                    <div class="wpvr-reel-info">
                        <h3 class="wpvr-reel-title"><?php echo esc_html(get_the_title()); ?></h3>
                        <p class="wpvr-reel-description"><?php echo esc_html(get_the_excerpt()); ?></p>
                        <?php if ($tags): ?>
                            <div class="wpvr-tags">Tags: <?php echo $tags; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    echo '</div>';
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('video_reel_feed', 'wpvr_feed_shortcode');

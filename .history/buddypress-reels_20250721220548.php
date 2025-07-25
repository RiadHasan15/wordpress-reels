<?php
/**
 * Plugin Name: BuddyPress Reels Enhanced
 * Description: Short vertical reels + TikTok-style grid playback on BuddyPress profiles with enhanced features.
 * Version: 3.0
 * Author: R
 * 
 */

if (!defined('ABSPATH')) exit;

// Register custom post type
add_action('init', 'bpr_register_post_type');
function bpr_register_post_type() {
    register_post_type('bpr_reel', [
        'labels' => [
            'name' => 'Reels',
            'singular_name' => 'Reel',
            'menu_name' => 'Reels',
            'add_new' => 'Add New Reel',
            'add_new_item' => 'Add New Reel',
            'edit_item' => 'Edit Reel',
            'new_item' => 'New Reel',
            'view_item' => 'View Reel',
            'search_items' => 'Search Reels',
            'not_found' => 'No reels found',
            'not_found_in_trash' => 'No reels found in trash'
        ],
        'public' => true,
        'show_in_menu' => true,
        'supports' => ['title', 'editor', 'author', 'thumbnail'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'reels'],
        'menu_icon' => 'dashicons-video-alt3'
    ]);
}

// Plugin activation - create tables and set defaults
register_activation_hook(__FILE__, 'bpr_activate');
function bpr_activate() {
    bpr_register_post_type();
    flush_rewrite_rules();
    
    // Set default options
    $default_opts = [
        'video_height'  => 'calc(100vh - 80px)',
        'video_width'   => '100%',
        'autoplay'      => '1',
        'default_muted' => '1',
        'max_file_size' => '50', // MB
        'allowed_formats' => 'mp4,webm,mov',
        'enable_comments' => '1',
        'enable_likes' => '1'
    ];
    add_option('bpr_settings', $default_opts);
}

// Enqueue styles/scripts
add_action('wp_enqueue_scripts', 'bpr_enqueue_scripts');
function bpr_enqueue_scripts() {
    wp_enqueue_style('bpr-style', plugin_dir_url(__FILE__) . 'css/style.css', [], '3.0');
    wp_enqueue_script('bpr-script', plugin_dir_url(__FILE__) . 'js/scripts.js', ['jquery'], '3.0', true);

    $opts = get_option('bpr_settings', []);
    $opts['ajax_url'] = admin_url('admin-ajax.php');
    $opts['nonce'] = wp_create_nonce('bpr_nonce');
    wp_localize_script('bpr-script', 'bprSettings', $opts);
}

// Handle reel uploads
add_action('admin_post_bpr_upload_reel', 'bpr_handle_upload');
add_action('admin_post_nopriv_bpr_upload_reel', 'bpr_handle_upload');

function bpr_handle_upload() {
    if (!is_user_logged_in()) {
        wp_die(__('Login required to upload reels.', 'buddypress-reels'));
    }
    
    if (!wp_verify_nonce($_POST['bpr_nonce'], 'bpr_upload_reel')) {
        wp_die(__('Security check failed.', 'buddypress-reels'));
    }

    $errors = [];
    
    // Validate file upload
    if (empty($_FILES['bpr_video']['name'])) {
        $errors[] = __('Please select a video file.', 'buddypress-reels');
    } else {
        $opts = get_option('bpr_settings', []);
        $max_size = ($opts['max_file_size'] ?? 50) * 1024 * 1024; // Convert MB to bytes
        $allowed_formats = explode(',', $opts['allowed_formats'] ?? 'mp4,webm,mov');
        
        $file_info = pathinfo($_FILES['bpr_video']['name']);
        $file_ext = strtolower($file_info['extension'] ?? '');
        
        if (!in_array($file_ext, $allowed_formats)) {
            $errors[] = sprintf(__('Invalid file format. Allowed formats: %s', 'buddypress-reels'), implode(', ', $allowed_formats));
        }
        
        if ($_FILES['bpr_video']['size'] > $max_size) {
            $errors[] = sprintf(__('File too large. Maximum size: %dMB', 'buddypress-reels'), $opts['max_file_size'] ?? 50);
        }
    }
    
    // Validate title
    if (empty(trim($_POST['bpr_title'] ?? ''))) {
        $errors[] = __('Title is required.', 'buddypress-reels');
    }
    
    if (!empty($errors)) {
        $error_msg = implode('<br>', $errors);
        wp_redirect(add_query_arg([
            'bpr_msg' => 'error_validation',
            'bpr_details' => urlencode($error_msg)
        ], wp_get_referer()));
        exit;
    }

    // Create post
    $post_data = [
        'post_title'   => sanitize_text_field($_POST['bpr_title']),
        'post_content' => sanitize_textarea_field($_POST['bpr_description'] ?? ''),
        'post_type'    => 'bpr_reel',
        'post_status'  => 'publish',
        'post_author'  => get_current_user_id()
    ];
    
    $post_id = wp_insert_post($post_data);
    if (is_wp_error($post_id)) {
        wp_redirect(add_query_arg('bpr_msg', 'error_post', wp_get_referer()));
        exit;
    }

    // Handle file upload
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $att_id = media_handle_upload('bpr_video', $post_id);
    if (is_wp_error($att_id)) {
        wp_delete_post($post_id, true);
        wp_redirect(add_query_arg('bpr_msg', 'error_upload', wp_get_referer()));
        exit;
    }
    
    update_post_meta($post_id, 'bpr_video', $att_id);
    update_post_meta($post_id, 'bpr_views', 0);
    update_post_meta($post_id, 'bpr_likes', 0);

    // Add BuddyPress activity
    if (function_exists('bp_activity_add')) {
        $user_link = function_exists('bp_core_get_userlink') ? 
            bp_core_get_userlink(get_current_user_id()) : 
            get_the_author_meta('display_name', get_current_user_id());
            
        bp_activity_add([
            'user_id'      => get_current_user_id(),
            'component'    => 'reels',
            'type'         => 'bpr_reel_upload',
            'action'       => sprintf(__('%s uploaded a new Reel', 'buddypress-reels'), $user_link),
            'content'      => '<a href="'.get_permalink($post_id).'">'.get_the_title($post_id).'</a>',
            'primary_link' => get_permalink($post_id),
            'item_id'      => $post_id,
            'recorded_time'=> bp_core_current_time()
        ]);
    }

    wp_redirect(add_query_arg('bpr_msg', 'success', wp_get_referer()));
    exit;
}

// Upload form shortcode
add_shortcode('bpr_upload_form', 'bpr_upload_form_shortcode');
function bpr_upload_form_shortcode() {
    if (!is_user_logged_in()) {
        return '<p class="bpr-login-notice">' . __('Please log in to upload a reel.', 'buddypress-reels') . '</p>';
    }
    
    ob_start();
    
    // Display messages
    if (isset($_GET['bpr_msg'])) {
        $msg_type = sanitize_key($_GET['bpr_msg']);
        $messages = [
            'success'           => __('Upload successful!', 'buddypress-reels'),
            'error_post'        => __('Error creating post.', 'buddypress-reels'),
            'error_upload'      => __('Error uploading video.', 'buddypress-reels'),
            'error_nofile'      => __('No video selected.', 'buddypress-reels'),
            'error_validation'  => isset($_GET['bpr_details']) ? urldecode($_GET['bpr_details']) : __('Validation error.', 'buddypress-reels')
        ];
        
        $message = $messages[$msg_type] ?? '';
        $class = strpos($msg_type, 'error') === 0 ? 'error' : 'success';
        
        if ($message) {
            echo '<div class="bpr-message bpr-' . esc_attr($class) . '">' . wp_kses_post($message) . '</div>';
        }
    }
    
    $opts = get_option('bpr_settings', []);
    ?>
    <form class="bpr-upload-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
        <?php wp_nonce_field('bpr_upload_reel', 'bpr_nonce'); ?>
        <input type="hidden" name="action" value="bpr_upload_reel">
        
        <h3><?php _e('Upload New Reel', 'buddypress-reels'); ?></h3>
        
        <label for="bpr_title"><?php _e('Title *', 'buddypress-reels'); ?></label>
        <input type="text" id="bpr_title" name="bpr_title" required maxlength="200" 
               placeholder="<?php esc_attr_e('Enter reel title...', 'buddypress-reels'); ?>">
        
        <label for="bpr_description"><?php _e('Description', 'buddypress-reels'); ?></label>
        <textarea id="bpr_description" name="bpr_description" rows="4" maxlength="500"
                  placeholder="<?php esc_attr_e('Describe your reel...', 'buddypress-reels'); ?>"></textarea>
        
        <label for="bpr_video"><?php _e('Video File *', 'buddypress-reels'); ?></label>
        <input type="file" id="bpr_video" name="bpr_video" accept="video/*" required>
        
        <div class="bpr-upload-info">
            <p><small><?php printf(__('Max size: %dMB | Allowed formats: %s', 'buddypress-reels'), 
                $opts['max_file_size'] ?? 50, 
                $opts['allowed_formats'] ?? 'mp4, webm, mov'
            ); ?></small></p>
        </div>
        
        <button type="submit" class="button button-primary">
            <?php _e('Upload Reel', 'buddypress-reels'); ?>
        </button>
    </form>
    <?php
    return ob_get_clean();
}

// Vertical scroll feed shortcode
add_shortcode('bpr_reels_feed', 'bpr_reels_feed_shortcode');
function bpr_reels_feed_shortcode($atts) {
    $atts = shortcode_atts([
        'count' => 20,
        'user_id' => '',
        'orderby' => 'date'
    ], $atts);
    
    $args = [
        'post_type'      => 'bpr_reel',
        'post_status'    => 'publish',
        'posts_per_page' => intval($atts['count']),
        'orderby'        => sanitize_key($atts['orderby']),
        'order'          => 'DESC',
        'meta_query'     => [
            [
                'key' => 'bpr_video',
                'compare' => 'EXISTS'
            ]
        ]
    ];
    
    if (!empty($atts['user_id'])) {
        $args['author'] = intval($atts['user_id']);
    }
    
    $query = new WP_Query($args);
    
    if (!$query->have_posts()) {
        return '<div class="bpr-no-reels"><p>' . __('No reels found.', 'buddypress-reels') . '</p></div>';
    }
    
    ob_start(); 
    ?>
    <div class="bpr-feed" data-feed-type="vertical">
        <?php while ($query->have_posts()): $query->the_post();
            $post_id = get_the_ID();
            $video_id = get_post_meta($post_id, 'bpr_video', true);
            $video_url = wp_get_attachment_url($video_id);
            
            if (!$video_url) continue;
            
            $author_id = get_the_author_meta('ID');
            $author_name = get_the_author_meta('display_name');
            $views = get_post_meta($post_id, 'bpr_views', true) ?: 0;
            $likes = get_post_meta($post_id, 'bpr_likes', true) ?: 0;
            
            // Get avatar
            $avatar_url = function_exists('bp_core_fetch_avatar') ? 
                bp_core_fetch_avatar(['item_id' => $author_id, 'html' => false, 'width' => 48, 'height' => 48]) : 
                get_avatar_url($author_id, ['size' => 48]);
            
            // Get profile URL
            $profile_url = function_exists('bp_core_get_user_domain') ? 
                bp_core_get_user_domain($author_id) : 
                get_author_posts_url($author_id);
            ?>
            <div class="bpr-video-wrapper" data-post-id="<?php echo esc_attr($post_id); ?>">
                <video class="bpr-video" playsinline muted loop preload="metadata" data-views="<?php echo esc_attr($views); ?>">
                    <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                    <?php _e('Your browser does not support the video tag.', 'buddypress-reels'); ?>
                </video>
                
                <div class="bpr-controls">
                    <div class="bpr-mute-toggle" data-user-muted="false" title="<?php esc_attr_e('Toggle sound', 'buddypress-reels'); ?>">🔇</div>
                </div>
                
                <div class="bpr-overlay">
                    <div class="bpr-author-info">
                        <a href="<?php echo esc_url($profile_url); ?>" class="bpr-author-avatar">
                            <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($author_name); ?>">
                        </a>
                        <div class="bpr-author-details">
                            <a href="<?php echo esc_url($profile_url); ?>" class="bpr-author-name">
                                <?php echo esc_html($author_name); ?>
                            </a>
                            <div class="bpr-reel-meta">
                                <span class="bpr-views"><?php printf(__('%s views', 'buddypress-reels'), number_format($views)); ?></span>
                                <span class="bpr-separator">•</span>
                                <span class="bpr-time"><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')); ?> <?php _e('ago', 'buddypress-reels'); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bpr-content">
                        <h3 class="bpr-title"><?php the_title(); ?></h3>
                        <?php if (get_the_content()): ?>
                            <p class="bpr-description"><?php echo wp_trim_words(get_the_content(), 20); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="bpr-actions">
                    <button class="bpr-like-btn" data-post-id="<?php echo esc_attr($post_id); ?>" title="<?php esc_attr_e('Like', 'buddypress-reels'); ?>">
                        ❤️ <span class="bpr-like-count"><?php echo number_format($likes); ?></span>
                    </button>
                </div>
                
                <div class="bpr-pause-overlay">
                    <div class="bpr-pause-icon">⏸️</div>
                </div>
            </div>
        <?php endwhile; 
        wp_reset_postdata(); ?>
    </div>
    <?php
    return ob_get_clean();
}

// Profile grid view shortcode
add_shortcode('bpr_reels_grid', 'bpr_reels_grid_shortcode');
function bpr_reels_grid_shortcode($atts) {
    $atts = shortcode_atts([
        'user_id' => '',
        'columns' => 3
    ], $atts);
    
    $user_id = !empty($atts['user_id']) ? intval($atts['user_id']) : 
        (function_exists('bp_displayed_user_id') ? bp_displayed_user_id() : get_current_user_id());
    
    if (!$user_id) {
        return '<p>' . __('No user specified.', 'buddypress-reels') . '</p>';
    }
    
    $query = new WP_Query([
        'post_type'      => 'bpr_reel',
        'author'         => $user_id,
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => [
            [
                'key' => 'bpr_video',
                'compare' => 'EXISTS'
            ]
        ]
    ]);
    
    if (!$query->have_posts()) {
        return '<div class="bpr-no-reels"><p>' . __('No reels found.', 'buddypress-reels') . '</p></div>';
    }
    
    ob_start();
    ?>
    <div class="bpr-grid-wrapper" data-columns="<?php echo esc_attr($atts['columns']); ?>">
        <?php while ($query->have_posts()): $query->the_post();
            $post_id = get_the_ID();
            $video_id = get_post_meta($post_id, 'bpr_video', true);
            $video_url = wp_get_attachment_url($video_id);
            
            if (!$video_url) continue;
            
            $views = get_post_meta($post_id, 'bpr_views', true) ?: 0;
            ?>
            <div class="bpr-grid-item" 
                 data-post-id="<?php echo esc_attr($post_id); ?>"
                 data-video="<?php echo esc_attr($video_url); ?>"
                 data-title="<?php echo esc_attr(get_the_title()); ?>"
                 data-description="<?php echo esc_attr(get_the_content()); ?>">
                
                <video muted loop preload="metadata" poster="">
                    <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                </video>
                
                <div class="bpr-grid-overlay">
                    <div class="bpr-grid-stats">
                        <span class="bpr-views-count">👁️ <?php echo number_format($views); ?></span>
                    </div>
                </div>
            </div>
        <?php endwhile; 
        wp_reset_postdata(); ?>
    </div>
    
    <!-- Modal for fullscreen playback -->
    <div class="bpr-modal" id="bpr-modal">
        <div class="bpr-modal-content">
            <button class="bpr-close" aria-label="<?php esc_attr_e('Close', 'buddypress-reels'); ?>">&times;</button>
            <video id="bpr-full-video" controls preload="metadata">
                <?php _e('Your browser does not support the video tag.', 'buddypress-reels'); ?>
            </video>
            <div class="bpr-modal-info">
                <div class="bpr-modal-user-info">
                    <?php if (function_exists('bp_core_fetch_avatar')): ?>
                        <?php echo bp_core_fetch_avatar(['item_id' => $user_id, 'html' => true, 'width' => 32, 'height' => 32]); ?>
                    <?php else: ?>
                        <img src="<?php echo esc_url(get_avatar_url($user_id, ['size' => 32])); ?>" alt="" class="avatar">
                    <?php endif; ?>
                    
                    <a href="<?php echo esc_url(function_exists('bp_core_get_user_domain') ? bp_core_get_user_domain($user_id) : get_author_posts_url($user_id)); ?>">
                        <?php echo esc_html(get_the_author_meta('display_name', $user_id)); ?>
                    </a>
                </div>
                <div class="bpr-modal-content-info">
                    <h4 class="bpr-modal-title"></h4>
                    <p class="bpr-modal-description"></p>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// AJAX handlers for likes and views
add_action('wp_ajax_bpr_like_reel', 'bpr_handle_like');
add_action('wp_ajax_nopriv_bpr_like_reel', 'bpr_handle_like');

function bpr_handle_like() {
    check_ajax_referer('bpr_nonce', 'nonce');
    
    $post_id = intval($_POST['post_id'] ?? 0);
    $user_id = get_current_user_id();
    
    if (!$post_id || !$user_id) {
        wp_send_json_error(__('Invalid request.', 'buddypress-reels'));
    }
    
    $likes_key = 'bpr_likes_' . $post_id;
    $user_likes = get_user_meta($user_id, $likes_key, true);
    $current_likes = get_post_meta($post_id, 'bpr_likes', true) ?: 0;
    
    if ($user_likes) {
        // Unlike
        delete_user_meta($user_id, $likes_key);
        $new_likes = max(0, $current_likes - 1);
        $liked = false;
    } else {
        // Like
        add_user_meta($user_id, $likes_key, true, true);
        $new_likes = $current_likes + 1;
        $liked = true;
    }
    
    update_post_meta($post_id, 'bpr_likes', $new_likes);
    
    wp_send_json_success([
        'likes' => $new_likes,
        'liked' => $liked
    ]);
}

add_action('wp_ajax_bpr_track_view', 'bpr_handle_view');
add_action('wp_ajax_nopriv_bpr_track_view', 'bpr_handle_view');

function bpr_handle_view() {
    check_ajax_referer('bpr_nonce', 'nonce');
    
    $post_id = intval($_POST['post_id'] ?? 0);
    
    if (!$post_id) {
        wp_send_json_error(__('Invalid request.', 'buddypress-reels'));
    }
    
    $views = get_post_meta($post_id, 'bpr_views', true) ?: 0;
    $new_views = $views + 1;
    update_post_meta($post_id, 'bpr_views', $new_views);
    
    wp_send_json_success(['views' => $new_views]);
}

// Add BuddyPress profile tab
add_action('bp_setup_nav', 'bpr_setup_nav');
function bpr_setup_nav() {
    if (!bp_is_active('members')) return;
    
    bp_core_new_nav_item([
        'name'                => __('Reels', 'buddypress-reels'),
        'slug'                => 'reels',
        'position'            => 50,
        'screen_function'     => 'bpr_profile_reels_screen',
        'default_subnav_slug' => 'reels'
    ]);
}

function bpr_profile_reels_screen() {
    add_action('bp_template_content', function() {
        echo do_shortcode('[bpr_reels_grid]');
    });
    bp_core_load_template('members/single/plugins');
}

// Admin settings page
add_action('admin_menu', 'bpr_admin_menu');
function bpr_admin_menu() {
    add_options_page(
        __('BuddyPress Reels Settings', 'buddypress-reels'),
        __('BP Reels', 'buddypress-reels'),
        'manage_options',
        'bpr-settings',
        'bpr_settings_page'
    );
}

function bpr_settings_page() {
    if (isset($_POST['submit'])) {
        check_admin_referer('bpr_settings');
        
        $settings = [
            'video_height'    => sanitize_text_field($_POST['video_height'] ?? 'calc(100vh - 80px)'),
            'video_width'     => sanitize_text_field($_POST['video_width'] ?? '100%'),
            'autoplay'        => isset($_POST['autoplay']) ? '1' : '0',
            'default_muted'   => isset($_POST['default_muted']) ? '1' : '0',
            'max_file_size'   => intval($_POST['max_file_size'] ?? 50),
            'allowed_formats' => sanitize_text_field($_POST['allowed_formats'] ?? 'mp4,webm,mov'),
            'enable_comments' => isset($_POST['enable_comments']) ? '1' : '0',
            'enable_likes'    => isset($_POST['enable_likes']) ? '1' : '0'
        ];
        
        update_option('bpr_settings', $settings);
        echo '<div class="notice notice-success"><p>' . __('Settings saved!', 'buddypress-reels') . '</p></div>';
    }
    
    $opts = get_option('bpr_settings', []);
    ?>
    <div class="wrap">
        <h1><?php _e('BuddyPress Reels Settings', 'buddypress-reels'); ?></h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('bpr_settings'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Max File Size (MB)', 'buddypress-reels'); ?></th>
                    <td><input type="number" name="max_file_size" value="<?php echo esc_attr($opts['max_file_size'] ?? 50); ?>" min="1" max="500" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Allowed Formats', 'buddypress-reels'); ?></th>
                    <td><input type="text" name="allowed_formats" value="<?php echo esc_attr($opts['allowed_formats'] ?? 'mp4,webm,mov'); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Autoplay', 'buddypress-reels'); ?></th>
                    <td><input type="checkbox" name="autoplay" value="1" <?php checked($opts['autoplay'] ?? '1', '1'); ?> /></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Default Muted', 'buddypress-reels'); ?></th>
                    <td><input type="checkbox" name="default_muted" value="1" <?php checked($opts['default_muted'] ?? '1', '1'); ?> /></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Enable Likes', 'buddypress-reels'); ?></th>
                    <td><input type="checkbox" name="enable_likes" value="1" <?php checked($opts['enable_likes'] ?? '1', '1'); ?> /></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Enable Comments', 'buddypress-reels'); ?></th>
                    <td><input type="checkbox" name="enable_comments" value="1" <?php checked($opts['enable_comments'] ?? '1', '1'); ?> /></td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
        
        <h2><?php _e('Usage', 'buddypress-reels'); ?></h2>
        <p><?php _e('Use these shortcodes to display reels:', 'buddypress-reels'); ?></p>
        <ul>
            <li><code>[bpr_upload_form]</code> - <?php _e('Upload form for new reels', 'buddypress-reels'); ?></li>
            <li><code>[bpr_reels_feed]</code> - <?php _e('Vertical scrolling feed', 'buddypress-reels'); ?></li>
            <li><code>[bpr_reels_grid]</code> - <?php _e('Grid view for profile pages', 'buddypress-reels'); ?></li>
        </ul>
    </div>
    <?php
}

// Plugin deactivation cleanup
register_deactivation_hook(__FILE__, 'bpr_deactivate');
function bpr_deactivate() {
    flush_rewrite_rules();
}

// Single reel template
add_filter('single_template', 'bpr_single_template');
function bpr_single_template($template) {
    if (get_post_type() === 'bpr_reel') {
        $plugin_template = plugin_dir_path(__FILE__) . 'templates/single-reel.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}

// Add meta boxes for admin
add_action('add_meta_boxes', 'bpr_add_meta_boxes');
function bpr_add_meta_boxes() {
    add_meta_box(
        'bpr_video_meta',
        __('Reel Video', 'buddypress-reels'),
        'bpr_video_meta_box',
        'bpr_reel',
        'normal',
        'high'
    );
    
    add_meta_box(
        'bpr_stats_meta',
        __('Reel Statistics', 'buddypress-reels'),
        'bpr_stats_meta_box',
        'bpr_reel',
        'side',
        'default'
    );
}

function bpr_video_meta_box($post) {
    wp_nonce_field('bpr_meta_box', 'bpr_meta_nonce');
    
    $video_id = get_post_meta($post->ID, 'bpr_video', true);
    $video_url = $video_id ? wp_get_attachment_url($video_id) : '';
    
    ?>
    <table class="form-table">
        <tr>
            <th><label for="bpr_video_upload"><?php _e('Video File', 'buddypress-reels'); ?></label></th>
            <td>
                <input type="hidden" name="bpr_video_id" id="bpr_video_id" value="<?php echo esc_attr($video_id); ?>" />
                <input type="button" class="button" id="bpr_video_upload" value="<?php _e('Choose Video', 'buddypress-reels'); ?>" />
                <input type="button" class="button" id="bpr_video_remove" value="<?php _e('Remove Video', 'buddypress-reels'); ?>" style="<?php echo $video_url ? '' : 'display:none;'; ?>" />
                
                <div id="bpr_video_preview" style="margin-top: 10px; <?php echo $video_url ? '' : 'display:none;'; ?>">
                    <?php if ($video_url): ?>
                        <video width="300" height="200" controls>
                            <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                        </video>
                        <p><strong><?php _e('Current video:', 'buddypress-reels'); ?></strong> <?php echo esc_html(basename($video_url)); ?></p>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    </table>
    
    <script>
    jQuery(document).ready(function($) {
        $('#bpr_video_upload').click(function() {
            var media_uploader = wp.media({
                title: '<?php _e("Choose Video", "buddypress-reels"); ?>',
                button: { text: '<?php _e("Use this video", "buddypress-reels"); ?>' },
                multiple: false,
                library: { type: 'video' }
            });
            
            media_uploader.on('select', function() {
                var attachment = media_uploader.state().get('selection').first().toJSON();
                $('#bpr_video_id').val(attachment.id);
                $('#bpr_video_preview').html('<video width="300" height="200" controls><source src="' + attachment.url + '" type="video/mp4"></video><p><strong><?php _e("Current video:", "buddypress-reels"); ?></strong> ' + attachment.filename + '</p>').show();
                $('#bpr_video_remove').show();
            });
            
            media_uploader.open();
        });
        
        $('#bpr_video_remove').click(function() {
            $('#bpr_video_id').val('');
            $('#bpr_video_preview').hide();
            $(this).hide();
        });
    });
    </script>
    <?php
}

function bpr_stats_meta_box($post) {
    $views = get_post_meta($post->ID, 'bpr_views', true) ?: 0;
    $likes = get_post_meta($post->ID, 'bpr_likes', true) ?: 0;
    
    ?>
    <table class="form-table">
        <tr>
            <th><?php _e('Views', 'buddypress-reels'); ?></th>
            <td><?php echo number_format($views); ?></td>
        </tr>
        <tr>
            <th><?php _e('Likes', 'buddypress-reels'); ?></th>
            <td><?php echo number_format($likes); ?></td>
        </tr>
        <tr>
            <th><?php _e('Upload Date', 'buddypress-reels'); ?></th>
            <td><?php echo get_the_date(); ?></td>
        </tr>
    </table>
    <?php
}

// Save meta box data
add_action('save_post', 'bpr_save_meta_box');
function bpr_save_meta_box($post_id) {
    if (!isset($_POST['bpr_meta_nonce']) || !wp_verify_nonce($_POST['bpr_meta_nonce'], 'bpr_meta_box')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (isset($_POST['bpr_video_id'])) {
        $video_id = intval($_POST['bpr_video_id']);
        if ($video_id) {
            update_post_meta($post_id, 'bpr_video', $video_id);
        } else {
            delete_post_meta($post_id, 'bpr_video');
        }
    }
}

// Add admin columns
add_filter('manage_bpr_reel_posts_columns', 'bpr_admin_columns');
function bpr_admin_columns($columns) {
    $new_columns = [];
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['bpr_video'] = __('Video', 'buddypress-reels');
            $new_columns['bpr_views'] = __('Views', 'buddypress-reels');
            $new_columns['bpr_likes'] = __('Likes', 'buddypress-reels');
        }
    }
    return $new_columns;
}

add_action('manage_bpr_reel_posts_custom_column', 'bpr_admin_column_content', 10, 2);
function bpr_admin_column_content($column, $post_id) {
    switch ($column) {
        case 'bpr_video':
            $video_id = get_post_meta($post_id, 'bpr_video', true);
            if ($video_id) {
                $video_url = wp_get_attachment_url($video_id);
                echo '<video width="60" height="80" muted><source src="' . esc_url($video_url) . '" type="video/mp4"></video>';
            } else {
                echo '—';
            }
            break;
            
        case 'bpr_views':
            $views = get_post_meta($post_id, 'bpr_views', true) ?: 0;
            echo number_format($views);
            break;
            
        case 'bpr_likes':
            $likes = get_post_meta($post_id, 'bpr_likes', true) ?: 0;
            echo number_format($likes);
            break;
    }
}

// REST API endpoints
add_action('rest_api_init', 'bpr_register_rest_routes');
function bpr_register_rest_routes() {
    register_rest_route('buddypress-reels/v1', '/reels', [
        'methods' => 'GET',
        'callback' => 'bpr_get_reels_api',
        'permission_callback' => '__return_true'
    ]);
    
    register_rest_route('buddypress-reels/v1', '/reels/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => 'bpr_get_reel_api',
        'permission_callback' => '__return_true'
    ]);
}

function bpr_get_reels_api($request) {
    $params = $request->get_params();
    $per_page = min(100, intval($params['per_page'] ?? 20));
    $page = intval($params['page'] ?? 1);
    $user_id = intval($params['user_id'] ?? 0);
    
    $args = [
        'post_type' => 'bpr_reel',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'paged' => $page,
        'orderby' => 'date',
        'order' => 'DESC'
    ];
    
    if ($user_id) {
        $args['author'] = $user_id;
    }
    
    $query = new WP_Query($args);
    $reels = [];
    
    foreach ($query->posts as $post) {
        $video_id = get_post_meta($post->ID, 'bpr_video', true);
        $video_url = wp_get_attachment_url($video_id);
        
        if (!$video_url) continue;
        
        $reels[] = [
            'id' => $post->ID,
            'title' => $post->post_title,
            'description' => $post->post_content,
            'video_url' => $video_url,
            'author' => [
                'id' => $post->post_author,
                'name' => get_the_author_meta('display_name', $post->post_author),
                'avatar' => get_avatar_url($post->post_author)
            ],
            'stats' => [
                'views' => intval(get_post_meta($post->ID, 'bpr_views', true) ?: 0),
                'likes' => intval(get_post_meta($post->ID, 'bpr_likes', true) ?: 0)
            ],
            'date_created' => $post->post_date,
            'permalink' => get_permalink($post->ID)
        ];
    }
    
    return new WP_REST_Response([
        'reels' => $reels,
        'pagination' => [
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => $page,
            'per_page' => $per_page
        ]
    ]);
}

function bpr_get_reel_api($request) {
    $reel_id = intval($request['id']);
    $post = get_post($reel_id);
    
    if (!$post || $post->post_type !== 'bpr_reel') {
        return new WP_Error('reel_not_found', __('Reel not found', 'buddypress-reels'), ['status' => 404]);
    }
    
    $video_id = get_post_meta($post->ID, 'bpr_video', true);
    $video_url = wp_get_attachment_url($video_id);
    
    if (!$video_url) {
        return new WP_Error('video_not_found', __('Video not found', 'buddypress-reels'), ['status' => 404]);
    }
    
    return [
        'id' => $post->ID,
        'title' => $post->post_title,
        'description' => $post->post_content,
        'video_url' => $video_url,
        'author' => [
            'id' => $post->post_author,
            'name' => get_the_author_meta('display_name', $post->post_author),
            'avatar' => get_avatar_url($post->post_author)
        ],
        'stats' => [
            'views' => intval(get_post_meta($post->ID, 'bpr_views', true) ?: 0),
            'likes' => intval(get_post_meta($post->ID, 'bpr_likes', true) ?: 0)
        ],
        'date_created' => $post->post_date,
        'permalink' => get_permalink($post->ID)
    ];
}

// Enqueue media uploader for admin
add_action('admin_enqueue_scripts', 'bpr_admin_scripts');
function bpr_admin_scripts($hook) {
    global $post_type;
    
    if ($post_type === 'bpr_reel' && in_array($hook, ['post.php', 'post-new.php'])) {
        wp_enqueue_media();
    }
}
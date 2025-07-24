<?php
/**
 * Plugin Name: BuddyPress Reels Enhanced
 * Description: Vertical video reels shortcode system with Instagram-style interface and seamless BuddyPress integration.
 * Version: 3.1
 * Author: Riad Hasan
 * 
 */

if (!defined('ABSPATH')) exit;

// Plugin activation - set defaults only
register_activation_hook(__FILE__, 'bpr_activate');
function bpr_activate() {
    // Set default options
    $default_opts = [
        'video_height'  => 'calc(100vh - 80px)',
        'video_width'   => '100%',
        'autoplay'      => '1',
        'default_muted' => '1',
        'max_file_size' => '50', // MB
        'allowed_formats' => 'mp4,webm,mov',
        'enable_comments' => '1'
    ];
    add_option('bpr_settings', $default_opts);
}

// Enqueue styles/scripts
add_action('wp_enqueue_scripts', 'bpr_enqueue_scripts');
function bpr_enqueue_scripts() {
    wp_enqueue_style('bpr-style', plugin_dir_url(__FILE__) . 'css/style.css', [], '3.1');
    wp_enqueue_script('bpr-script', plugin_dir_url(__FILE__) . 'js/scripts.js', ['jquery'], '3.1', true);

    $opts = get_option('bpr_settings', []);
    $opts['ajax_url'] = admin_url('admin-ajax.php');
    $opts['nonce'] = wp_create_nonce('bpr_nonce');
    wp_localize_script('bpr-script', 'bprSettings', $opts);
}

// Handle reel uploads (now creates regular posts with video meta)
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

    // Create regular post with reel meta
    $post_data = [
        'post_title'   => sanitize_text_field($_POST['bpr_title']),
        'post_content' => sanitize_textarea_field($_POST['bpr_description'] ?? ''),
        'post_type'    => 'post',
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
    
    // Mark this post as a reel
    update_post_meta($post_id, 'bpr_video', $att_id);
    update_post_meta($post_id, 'bpr_is_reel', '1');

    // Add BuddyPress activity
    if (function_exists('bp_activity_add')) {
        $user_link = function_exists('bp_core_get_userlink') ? 
            bp_core_get_userlink(get_current_user_id()) : 
            get_the_author_meta('display_name', get_current_user_id());
            
        $activity_id = bp_activity_add([
            'user_id'      => get_current_user_id(),
            'component'    => 'reels',
            'type'         => 'bpr_reel_upload',
            'action'       => sprintf(__('%s uploaded a new Reel', 'buddypress-reels'), $user_link),
            'content'      => sprintf(__('"%s" - Check out this new reel!', 'buddypress-reels'), get_the_title($post_id)),
            'primary_link' => wp_get_referer() ?: home_url('/'),
            'item_id'      => $post_id,
            'recorded_time'=> bp_core_current_time()
        ]);
        
        if ($activity_id) {
            // Store the activity ID in post meta for future reference
            update_post_meta($post_id, 'bp_activity_id', $activity_id);
            
            // Add custom meta to activity
            bp_activity_update_meta($activity_id, 'reel_post_id', $post_id);
            bp_activity_update_meta($activity_id, 'reel_video_id', $att_id);
        }
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
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => intval($atts['count']),
        'orderby'        => sanitize_key($atts['orderby']),
        'order'          => 'DESC',
        'meta_query'     => [
            [
                'key' => 'bpr_is_reel',
                'value' => '1',
                'compare' => '='
            ],
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
                <video class="bpr-video" playsinline muted loop preload="metadata">
                    <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                    <?php _e('Your browser does not support the video tag.', 'buddypress-reels'); ?>
                </video>
                
                <div class="bpr-controls">
                    <button class="bpr-mute-toggle" type="button" aria-label="<?php esc_attr_e('Toggle mute', 'buddypress-reels'); ?>" data-muted="true">🔇</button>
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

// Profile feed shortcode (showing user's reels)
add_shortcode('bpr_profile_feed', 'bpr_profile_feed_shortcode');
function bpr_profile_feed_shortcode($atts) {
    $atts = shortcode_atts([
        'user_id' => '',
        'posts_per_page' => 10
    ], $atts);
    
    $user_id = !empty($atts['user_id']) ? intval($atts['user_id']) : 
        (function_exists('bp_displayed_user_id') ? bp_displayed_user_id() : get_current_user_id());
    
    if (!$user_id) {
        return '<p>' . __('No user specified.', 'buddypress-reels') . '</p>';
    }
    
    $query = new WP_Query([
        'post_type'      => 'post',
        'author'         => $user_id,
        'post_status'    => 'publish',
        'posts_per_page' => intval($atts['posts_per_page']),
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => [
            [
                'key' => 'bpr_is_reel',
                'value' => '1',
                'compare' => '='
            ],
            [
                'key' => 'bpr_video',
                'compare' => 'EXISTS'
            ]
        ]
    ]);
    
    if (!$query->have_posts()) {
        return '<div class="bpr-no-reels">
                    <div class="bpr-empty-state">
                        <div class="bpr-empty-icon">🎬</div>
                        <h3>' . __('No reels yet', 'buddypress-reels') . '</h3>
                        <p>' . __('This user hasn\'t created any reels yet. Check back later!', 'buddypress-reels') . '</p>
                    </div>
                </div>';
    }
    
    ob_start();
    ?>
    <div class="bpr-profile-feed">
        <div class="bpr-profile-header">
            <div class="bpr-profile-stats">
                <div class="bpr-stat">
                    <span class="bpr-stat-number"><?php echo number_format($query->found_posts); ?></span>
                    <span class="bpr-stat-label"><?php _e('Reels', 'buddypress-reels'); ?></span>
                </div>
                <div class="bpr-stat">
                    <span class="bpr-stat-number"><?php echo date_i18n('M Y', strtotime(get_userdata($user_id)->user_registered)); ?></span>
                    <span class="bpr-stat-label"><?php _e('Joined', 'buddypress-reels'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="bpr-feed-container">
            <?php while ($query->have_posts()): $query->the_post();
                $post_id = get_the_ID();
                $video_id = get_post_meta($post_id, 'bpr_video', true);
                $video_url = wp_get_attachment_url($video_id);
                
                if (!$video_url) continue;
                ?>
                <div class="bpr-profile-reel" data-post-id="<?php echo esc_attr($post_id); ?>">
                    <div class="bpr-video-wrapper">
                        <video class="bpr-video" 
                               muted 
                               loop 
                               preload="metadata" 
                               data-post-id="<?php echo esc_attr($post_id); ?>">
                            <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                            <?php _e('Your browser does not support the video tag.', 'buddypress-reels'); ?>
                        </video>
                        
                        <div class="bpr-video-overlay">
                            <div class="bpr-controls">
                                <button class="bpr-mute-toggle" type="button" aria-label="<?php esc_attr_e('Toggle mute', 'buddypress-reels'); ?>" data-muted="true">🔇</button>
                            </div>
                        </div>
                        
                        <div class="bpr-pause-overlay">
                            <div class="bpr-pause-icon">⏸️</div>
                        </div>
                    </div>
                    
                    <div class="bpr-reel-content">
                        <?php if (get_the_title()): ?>
                            <h4 class="bpr-reel-title"><?php echo esc_html(get_the_title()); ?></h4>
                        <?php endif; ?>
                        
                        <?php if (get_the_content()): ?>
                            <p class="bpr-reel-description"><?php echo esc_html(wp_trim_words(get_the_content(), 20)); ?></p>
                        <?php endif; ?>
                        
                        <div class="bpr-reel-stats">
                            <span class="bpr-stat-item">
                                <span class="bpr-icon">📅</span>
                                <span><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ' . __('ago', 'buddypress-reels'); ?></span>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endwhile; 
            wp_reset_postdata(); ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Grid view shortcode (3-column TikTok style)
add_shortcode('bpr_reels_grid', 'bpr_reels_grid_shortcode');
function bpr_reels_grid_shortcode($atts) {
    $atts = shortcode_atts([
        'user_id' => '',
        'posts_per_page' => 12
    ], $atts);
    
    $user_id = !empty($atts['user_id']) ? intval($atts['user_id']) : 
        (function_exists('bp_displayed_user_id') ? bp_displayed_user_id() : get_current_user_id());
    
    if (!$user_id) {
        return '<p>' . __('No user specified.', 'buddypress-reels') . '</p>';
    }
    
    $query = new WP_Query([
        'post_type'      => 'post',
        'author'         => $user_id,
        'post_status'    => 'publish',
        'posts_per_page' => intval($atts['posts_per_page']),
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => [
            [
                'key' => 'bpr_is_reel',
                'value' => '1',
                'compare' => '='
            ],
            [
                'key' => 'bpr_video',
                'compare' => 'EXISTS'
            ]
        ]
    ]);
    
    if (!$query->have_posts()) {
        return '<div class="bpr-no-reels">
                    <div class="bpr-empty-state">
                        <div class="bpr-empty-icon">🎬</div>
                        <h3>' . __('No reels yet', 'buddypress-reels') . '</h3>
                        <p>' . __('This user hasn\'t created any reels yet. Check back later!', 'buddypress-reels') . '</p>
                    </div>
                </div>';
    }
    
    ob_start();
    ?>
    <div class="bpr-grid-container">
        <div class="bpr-grid-wrapper">
            <?php while ($query->have_posts()): $query->the_post();
                $post_id = get_the_ID();
                $video_id = get_post_meta($post_id, 'bpr_video', true);
                $video_url = wp_get_attachment_url($video_id);
                
                if (!$video_url) continue;
                
                // Get video thumbnail/poster
                $thumbnail_id = get_post_thumbnail_id($post_id);
                $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : '';
                ?>
                <div class="bpr-grid-item" data-post-id="<?php echo esc_attr($post_id); ?>">
                    <div class="bpr-grid-video-wrapper">
                        <video class="bpr-grid-video" 
                               muted 
                               loop 
                               preload="metadata"
                               <?php if ($thumbnail_url): ?>poster="<?php echo esc_url($thumbnail_url); ?>"<?php endif; ?>
                               data-src="<?php echo esc_url($video_url); ?>">
                            <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                        </video>
                        
                        <div class="bpr-grid-overlay">
                            <?php if (get_the_title()): ?>
                                <div class="bpr-grid-title"><?php echo esc_html(wp_trim_words(get_the_title(), 3)); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; 
            wp_reset_postdata(); ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
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
            'enable_comments' => isset($_POST['enable_comments']) ? '1' : '0'
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
            <li><code>[bpr_reels_feed]</code> - <?php _e('Instagram-style vertical scrolling feed', 'buddypress-reels'); ?></li>
            <li><code>[bpr_profile_feed]</code> - <?php _e('Optimized profile feed with stats', 'buddypress-reels'); ?></li>
            <li><code>[bpr_reels_grid]</code> - <?php _e('TikTok-style 3-column grid', 'buddypress-reels'); ?></li>
        </ul>
        
        <div class="notice notice-info">
            <p><strong><?php _e('Shortcode-Based System:', 'buddypress-reels'); ?></strong> 
            <?php _e('This plugin uses regular WordPress posts with video metadata. Use the shortcodes above on any page or post to display your reels. No custom post types or archives are created.', 'buddypress-reels'); ?></p>
        </div>
    </div>
    <?php
}

// Plugin deactivation cleanup
register_deactivation_hook(__FILE__, 'bpr_deactivate');
function bpr_deactivate() {
    // Clean up options if needed
    // delete_option('bpr_settings');
}
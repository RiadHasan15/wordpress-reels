<?php
/**
 * Debug file for BuddyPress Reels Feed
 * 
 * This file helps debug the [bpr_reels_feed] shortcode system
 * Place this in your theme's root directory and access via: yoursite.com/wp-content/themes/yourtheme/debug-feed.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('You need administrator privileges to access this debug page.');
}

get_header(); ?>

<div style="max-width: 800px; margin: 2rem auto; padding: 1rem; background: #f8fafc; border-radius: 12px;">
    <h1>BuddyPress Reels Debug - Shortcode System</h1>
    
    <h2>1. Plugin Status</h2>
    <p><strong>Plugin Active:</strong> <?php echo is_plugin_active('buddypress-reels/buddypress-reels.php') ? '✅ Yes' : '❌ No'; ?></p>
    <p><strong>System Type:</strong> ✅ Shortcode-based (No custom post types)</p>
    <p><strong>Shortcode Registered:</strong> <?php echo shortcode_exists('bpr_reels_feed') ? '✅ Yes' : '❌ No'; ?></p>
    
    <h2>2. Reel Posts Count</h2>
    <?php
    // Query for posts with reel metadata
    $reel_query = new WP_Query([
        'post_type' => 'post',
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => 'bpr_is_reel',
                'value' => '1',
                'compare' => '='
            ]
        ],
        'posts_per_page' => -1
    ]);
    
    echo "<p><strong>Published Reels:</strong> " . $reel_query->found_posts . "</p>";
    wp_reset_postdata();
    ?>
    
    <h2>3. Sample Reels Query</h2>
    <?php
    $test_query = new WP_Query([
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => 5,
        'meta_query' => [
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
    
    echo "<p><strong>Reels Found:</strong> " . $test_query->found_posts . "</p>";
    
    if ($test_query->have_posts()) {
        echo "<ul>";
        while ($test_query->have_posts()) {
            $test_query->the_post();
            $video_id = get_post_meta(get_the_ID(), 'bpr_video', true);
            $video_url = $video_id ? wp_get_attachment_url($video_id) : 'No video';
            $is_reel = get_post_meta(get_the_ID(), 'bpr_is_reel', true);
            echo "<li>" . get_the_title() . " - Video: " . ($video_url ? '✅ Has video' : '❌ No video') . " - Reel Meta: " . ($is_reel ? '✅ Yes' : '❌ No') . "</li>";
        }
        echo "</ul>";
        wp_reset_postdata();
    }
    ?>
    
    <h2>4. Shortcode Tests</h2>
    
    <h3>Upload Form Shortcode</h3>
    <p>Testing [bpr_upload_form] shortcode:</p>
    <div style="border: 2px solid #10b981; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
        <?php echo do_shortcode('[bpr_upload_form]'); ?>
    </div>
    
    <h3>Reels Feed Shortcode</h3>
    <p>Testing [bpr_reels_feed count="3"] shortcode:</p>
    <div style="border: 2px solid #2563eb; padding: 1rem; border-radius: 8px; max-height: 400px; overflow: auto;">
        <?php echo do_shortcode('[bpr_reels_feed count="3"]'); ?>
    </div>
    
    <h2>5. Available Shortcodes</h2>
    <ul>
        <li><code>[bpr_upload_form]</code> - <?php echo shortcode_exists('bpr_upload_form') ? '✅ Available' : '❌ Missing'; ?></li>
        <li><code>[bpr_reels_feed]</code> - <?php echo shortcode_exists('bpr_reels_feed') ? '✅ Available' : '❌ Missing'; ?></li>
        <li><code>[bpr_profile_feed]</code> - <?php echo shortcode_exists('bpr_profile_feed') ? '✅ Available' : '❌ Missing'; ?></li>
        <li><code>[bpr_reels_grid]</code> - <?php echo shortcode_exists('bpr_reels_grid') ? '✅ Available' : '❌ Missing'; ?></li>
    </ul>
    
    <h2>6. CSS/JS Assets</h2>
    <p><strong>Plugin CSS:</strong> 
        <?php 
        $css_file = plugin_dir_path(__FILE__) . 'css/style.css';
        echo file_exists($css_file) ? '✅ Found' : '❌ Missing'; 
        ?>
    </p>
    <p><strong>Plugin JS:</strong> 
        <?php 
        $js_file = plugin_dir_path(__FILE__) . 'js/scripts.js';
        echo file_exists($js_file) ? '✅ Found' : '❌ Missing'; 
        ?>
    </p>
    
    <h2>7. System Configuration</h2>
    <?php 
    $options = get_option('bpr_settings', []);
    if (!empty($options)) {
        echo "<ul>";
        foreach ($options as $key => $value) {
            echo "<li><strong>" . esc_html($key) . ":</strong> " . esc_html($value) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>❌ No plugin settings found</p>";
    }
    ?>
    
    <h2>8. Browser Console</h2>
    <p>Check your browser's developer console for any JavaScript errors.</p>
    
    <script>
    console.log('BuddyPress Reels Debug - Shortcode System');
    
    // Check if jQuery is available
    if (typeof jQuery !== 'undefined') {
        console.log('✅ jQuery is loaded');
    } else {
        console.log('❌ jQuery is not loaded');
    }
    
    // Check if bprSettings is available
    if (typeof bprSettings !== 'undefined') {
        console.log('✅ bprSettings is loaded:', bprSettings);
    } else {
        console.log('❌ bprSettings is not loaded');
    }
    
    // Check for reel elements
    document.addEventListener('DOMContentLoaded', function() {
        const feedElements = document.querySelectorAll('.bpr-feed');
        const videoElements = document.querySelectorAll('.bpr-video');
        const uploadForms = document.querySelectorAll('.bpr-upload-form');
        
        console.log('Feed elements found:', feedElements.length);
        console.log('Video elements found:', videoElements.length);
        console.log('Upload forms found:', uploadForms.length);
        
        if (feedElements.length === 0 && videoElements.length === 0) {
            console.log('ℹ️ No reel content found - this is normal if no reels have been uploaded yet');
        }
    });
    </script>
</div>

<?php get_footer(); ?>
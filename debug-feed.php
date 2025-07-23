<?php
/**
 * Debug file for BuddyPress Reels Feed
 * 
 * This file helps debug the [bpr_reels_feed] shortcode
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
    <h1>BuddyPress Reels Feed Debug</h1>
    
    <h2>1. Plugin Status</h2>
    <p><strong>Plugin Active:</strong> <?php echo is_plugin_active('buddypress-reels/buddypress-reels.php') ? '✅ Yes' : '❌ No'; ?></p>
    <p><strong>Post Type Registered:</strong> <?php echo post_type_exists('bpr_reel') ? '✅ Yes' : '❌ No'; ?></p>
    <p><strong>Shortcode Registered:</strong> <?php echo shortcode_exists('bpr_reels_feed') ? '✅ Yes' : '❌ No'; ?></p>
    
    <h2>2. Reel Posts Count</h2>
    <?php
    $reel_count = wp_count_posts('bpr_reel');
    echo "<p><strong>Published Reels:</strong> " . ($reel_count->publish ?? 0) . "</p>";
    echo "<p><strong>Draft Reels:</strong> " . ($reel_count->draft ?? 0) . "</p>";
    ?>
    
    <h2>3. Sample Reels Query</h2>
    <?php
    $test_query = new WP_Query([
        'post_type' => 'bpr_reel',
        'post_status' => 'publish',
        'posts_per_page' => 5,
        'meta_query' => [
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
            echo "<li>" . get_the_title() . " - Video: " . ($video_url ? '✅ Has video' : '❌ No video') . "</li>";
        }
        echo "</ul>";
        wp_reset_postdata();
    }
    ?>
    
    <h2>4. Shortcode Test</h2>
    <p>Testing [bpr_reels_feed] shortcode output:</p>
    <div style="border: 2px solid #2563eb; padding: 1rem; border-radius: 8px; max-height: 400px; overflow: auto;">
        <?php echo do_shortcode('[bpr_reels_feed count="5"]'); ?>
    </div>
    
    <h2>5. CSS/JS Assets</h2>
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
    
    <h2>6. Browser Console</h2>
    <p>Check your browser's developer console for any JavaScript errors.</p>
    
    <script>
    console.log('BuddyPress Reels Debug - Page loaded');
    
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
        
        console.log('Feed elements found:', feedElements.length);
        console.log('Video elements found:', videoElements.length);
        
        if (feedElements.length === 0) {
            console.log('❌ No .bpr-feed elements found - shortcode may not be rendering');
        }
        
        if (videoElements.length === 0) {
            console.log('❌ No .bpr-video elements found - videos may not be loading');
        }
    });
    </script>
</div>

<?php get_footer(); ?>
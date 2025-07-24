<?php
/**
 * Quick Test File for BuddyPress Reels Shortcodes
 * 
 * Place this in your WordPress root and access via: yoursite.com/test-shortcodes.php
 * This will quickly verify all shortcodes are working
 */

// Load WordPress
require_once('wp-config.php');
require_once('wp-load.php');

// Simple authentication check
if (!current_user_can('read')) {
    wp_die('Please log in to test the shortcodes.');
}

get_header(); ?>

<div style="max-width: 1200px; margin: 2rem auto; padding: 1rem;">
    <h1>🎬 BuddyPress Reels - Shortcode Test Page</h1>
    
    <div style="background: #f0f9ff; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
        <h2>✅ System Status</h2>
        <ul style="list-style: none; padding: 0;">
            <li>🔌 Plugin Active: <?php echo is_plugin_active('buddypress-reels/buddypress-reels.php') ? '<strong style="color: green;">Yes</strong>' : '<strong style="color: red;">No</strong>'; ?></li>
            <li>📝 Upload Form: <?php echo shortcode_exists('bpr_upload_form') ? '<strong style="color: green;">Available</strong>' : '<strong style="color: red;">Missing</strong>'; ?></li>
            <li>📱 Feed: <?php echo shortcode_exists('bpr_reels_feed') ? '<strong style="color: green;">Available</strong>' : '<strong style="color: red;">Missing</strong>'; ?></li>
            <li>👤 Profile: <?php echo shortcode_exists('bpr_profile_feed') ? '<strong style="color: green;">Available</strong>' : '<strong style="color: red;">Missing</strong>'; ?></li>
            <li>🎯 Grid: <?php echo shortcode_exists('bpr_reels_grid') ? '<strong style="color: green;">Available</strong>' : '<strong style="color: red;">Missing</strong>'; ?></li>
        </ul>
    </div>

    <!-- Upload Form Test -->
    <div style="background: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2>📤 Upload Form Test</h2>
        <p>Testing: <code>[bpr_upload_form]</code></p>
        <div style="border: 2px dashed #e5e7eb; padding: 1rem; border-radius: 8px;">
            <?php echo do_shortcode('[bpr_upload_form]'); ?>
        </div>
    </div>

    <!-- Feed Test -->
    <div style="background: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2>📱 Vertical Feed Test</h2>
        <p>Testing: <code>[bpr_reels_feed count="3"]</code></p>
        <div style="border: 2px dashed #e5e7eb; padding: 1rem; border-radius: 8px; max-height: 500px; overflow-y: auto;">
            <?php echo do_shortcode('[bpr_reels_feed count="3"]'); ?>
        </div>
    </div>

    <!-- Profile Feed Test -->
    <div style="background: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2>👤 Profile Feed Test</h2>
        <p>Testing: <code>[bpr_profile_feed posts_per_page="3"]</code></p>
        <div style="border: 2px dashed #e5e7eb; padding: 1rem; border-radius: 8px;">
            <?php echo do_shortcode('[bpr_profile_feed posts_per_page="3"]'); ?>
        </div>
    </div>

    <!-- Grid Test -->
    <div style="background: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2>🎯 Grid Layout Test</h2>
        <p>Testing: <code>[bpr_reels_grid posts_per_page="6"]</code></p>
        <div style="border: 2px dashed #e5e7eb; padding: 1rem; border-radius: 8px;">
            <?php echo do_shortcode('[bpr_reels_grid posts_per_page="6"]'); ?>
        </div>
    </div>

    <div style="background: #fef3c7; padding: 1rem; border-radius: 8px; text-align: center;">
        <p><strong>🎉 Test Complete!</strong></p>
        <p>If you see content above (or "No reels found" messages), the shortcodes are working correctly!</p>
        <p><small>Delete this file after testing: <code>test-shortcodes.php</code></small></p>
    </div>
</div>

<style>
/* Quick styling for the test page */
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; }
</style>

<?php get_footer(); ?>
<?php
/**
 * Test file for BuddyPress Reels
 * Visit this file to test the functionality
 * URL: http://localhost/wordpress/wp-content/plugins/buddypress-reels/test-reels.php
 */

// Include WordPress
require_once '../../../wp-config.php';

// Check if the reels post type exists
if (!post_type_exists('bpr_reel')) {
    echo '<h1>BuddyPress Reels Plugin</h1>';
    echo '<p style="color: red;">ERROR: bpr_reel post type is not registered. Make sure the plugin is activated.</p>';
    exit;
}

// Check for existing reels
$reels_query = new WP_Query([
    'post_type' => 'bpr_reel',
    'post_status' => 'publish',
    'posts_per_page' => 5
]);

echo '<h1>BuddyPress Reels - Test Page</h1>';
echo '<h2>Plugin Status: ✅ Active</h2>';

echo '<h3>Important URLs:</h3>';
echo '<ul>';
echo '<li><strong>Archive Page (Main Feed):</strong> <a href="' . home_url('/reels/') . '" target="_blank">' . home_url('/reels/') . '</a></li>';
echo '<li><strong>Upload Form:</strong> Use shortcode [bpr_upload_form] on any page</li>';
echo '<li><strong>Vertical Feed:</strong> Use shortcode [bpr_reels_feed] on any page</li>';
echo '<li><strong>Profile Grid:</strong> Use shortcode [bpr_reels_grid] on any page</li>';
echo '</ul>';

echo '<h3>Current Reels (' . $reels_query->found_posts . ' total):</h3>';

if ($reels_query->have_posts()) {
    echo '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">';
    echo '<tr><th>ID</th><th>Title</th><th>Author</th><th>Date</th><th>Video</th><th>Actions</th></tr>';
    
    while ($reels_query->have_posts()) {
        $reels_query->the_post();
        $video_id = get_post_meta(get_the_ID(), 'bpr_video', true);
        $video_url = $video_id ? wp_get_attachment_url($video_id) : '';
        
        echo '<tr>';
        echo '<td>' . get_the_ID() . '</td>';
        echo '<td>' . get_the_title() . '</td>';
        echo '<td>' . get_the_author() . '</td>';
        echo '<td>' . get_the_date() . '</td>';
        echo '<td>' . ($video_url ? '✅ Has Video' : '❌ No Video') . '</td>';
        echo '<td><a href="' . get_permalink() . '" target="_blank">View</a></td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo '<p style="color: orange;">No reels found. Create some reels first!</p>';
    echo '<p><strong>To create a test reel:</strong></p>';
    echo '<ol>';
    echo '<li>Go to WordPress Admin → Reels → Add New Reel</li>';
    echo '<li>Or use the [bpr_upload_form] shortcode on any page</li>';
    echo '</ol>';
}

wp_reset_postdata();

echo '<h3>Test Archive Page:</h3>';


echo '<h3>Shortcode Tests:</h3>';
echo '<h4>Upload Form:</h4>';
if (is_user_logged_in()) {
    echo do_shortcode('[bpr_upload_form]');
} else {
    echo '<p>Please log in to see the upload form.</p>';
}

echo '<h4>Sample Feed (if reels exist):</h4>';
if ($reels_query->found_posts > 0) {
    echo '<div style="max-width: 400px; height: 600px; border: 1px solid #ccc; margin: 20px 0;">';
    echo do_shortcode('[bpr_reels_feed count="3"]');
    echo '</div>';
} else {
    echo '<p>No reels to display in feed.</p>';
}

echo '<h3>Troubleshooting:</h3>';
echo '<ul>';
echo '<li>If the /reels/ URL shows 404, go to WordPress Admin → Settings → Permalinks and click "Save Changes" to flush rewrite rules.</li>';
echo '<li>Make sure the plugin is activated in WordPress Admin → Plugins</li>';
echo '<li>Check that you have at least one published reel</li>';
echo '</ul>';

echo '<hr>';
echo '<p><small>Generated at: ' . date('Y-m-d H:i:s') . '</small></p>';
?>
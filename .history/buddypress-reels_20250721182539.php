<?php
/**
 * Plugin Name: BuddyPress Reels
 * Description: A simple BuddyPress-integrated short video feed like Instagram Reels.
 * Version: 1.0
 * Author: Riad Hasan
 */

defined('ABSPATH') || exit;

// Register Post Type
function bpr_register_reels_post_type() {
    register_post_type('bp_reel', [
        'labels' => [
            'name' => 'Reels',
            'singular_name' => 'Reel'
        ],
        'public' => true,
        'supports' => ['title', 'editor', 'author'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'reels'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'bpr_register_reels_post_type');

// Enqueue Scripts and Styles
function bpr_enqueue_assets() {
    wp_enqueue_style('bpr-style', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('bpr-script', plugin_dir_url(__FILE__) . 'js/scripts.js', [], false, true);
}
add_action('wp_enqueue_scripts', 'bpr_enqueue_assets');

// Shortcode to show reels
function bpr_display_reels() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/reels-loop.php';
    return ob_get_clean();
}
add_shortcode('buddypress_reels', 'bpr_display_reels');

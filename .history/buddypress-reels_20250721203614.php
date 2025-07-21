<?php
/**
 * Plugin Name: BuddyPress Reels
 */

define('BPR_PLUGIN_DIR', plugin_dir_path(__FILE__));

function bpr_enqueue_scripts() {
    wp_enqueue_style('bpr-style', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('bpr-script', plugin_dir_url(__FILE__) . 'js/scripts.js', array('jquery'), null, true);

    wp_localize_script('bpr-script', 'bpr_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}
add_action('wp_enqueue_scripts', 'bpr_enqueue_scripts');

function bpr_reels_feed_shortcode() {
    ob_start();
    include BPR_PLUGIN_DIR . 'templates/reels-grid.php';
    include BPR_PLUGIN_DIR . 'templates/modal-player.php';
    return ob_get_clean();
}
add_shortcode('bpr_reels_feed', 'bpr_reels_feed_shortcode');

function bpr_upload_form_shortcode() {
    ob_start();
    include BPR_PLUGIN_DIR . 'templates/upload-form.php';
    return ob_get_clean();
}
add_shortcode('bpr_upload_form', 'bpr_upload_form_shortcode');

// Add Reels tab to BuddyPress profile
function bpr_add_reels_tab() {
    bp_core_new_nav_item(array(
        'name' => 'Reels',
        'slug' => 'reels',
        'position' => 90,
        'screen_function' => 'bpr_reels_tab_content',
        'default_subnav_slug' => 'reels',
    ));
}
add_action('bp_setup_nav', 'bpr_add_reels_tab', 99);

function bpr_reels_tab_content() {
    add_action('bp_template_content', function () {
        echo do_shortcode('[bpr_reels_feed]');
    });
    bp_core_load_template('members/single/plugins');
}

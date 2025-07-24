<?php
/**
 * Archive Template for BuddyPress Reels
 * 
 * This template displays the Instagram-style vertical feed
 * when users visit /reels/ or the reels archive page
 */

if (!defined('ABSPATH')) exit;

get_header(); ?>

<div class="bpr-archive-container">
    <header class="bpr-archive-header">
        <h1 class="bpr-archive-title"><?php _e('Reels', 'buddypress-reels'); ?></h1>
        <p class="bpr-archive-description"><?php _e('Discover and watch vertical video reels from our community', 'buddypress-reels'); ?></p>
    </header>
    
    <?php
    // Display the Instagram-style vertical feed using the shortcode
    echo do_shortcode('[bpr_reels_feed count="20"]');
    ?>
</div>

<style>
.bpr-archive-container {
    max-width: 100%;
    margin: 0 auto;
    padding: 0;
}

.bpr-archive-header {
    text-align: center;
    padding: 2rem 1rem;
    background: var(--bpr-light, #ffffff);
    border-bottom: 1px solid var(--bpr-glass-border, rgba(0,0,0,0.1));
    margin-bottom: 1rem;
}

.bpr-archive-title {
    font-size: 2.5rem;
    font-weight: var(--bpr-font-weight-bold, 700);
    color: var(--bpr-text-primary, #0f172a);
    margin: 0 0 0.5rem 0;
}

.bpr-archive-description {
    font-size: 1.1rem;
    color: var(--bpr-text-secondary, #475569);
    margin: 0;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

/* Hide default WordPress archive elements */
.post, .hentry, .entry {
    display: none;
}

/* Ensure the feed takes full width */
.bpr-feed {
    margin: 0 auto;
}

@media (max-width: 768px) {
    .bpr-archive-header {
        padding: 1rem;
    }
    
    .bpr-archive-title {
        font-size: 2rem;
    }
    
    .bpr-archive-description {
        font-size: 1rem;
    }
}
</style>

<?php get_footer(); ?>
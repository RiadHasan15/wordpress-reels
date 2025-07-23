<?php
/**
 * Single Reel Template
 * 
 * This template displays individual reel posts
 */

if (!defined('ABSPATH')) exit;

get_header(); ?>

<div class="bpr-single-reel-container">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('bpr-single-reel'); ?>>
                
                <header class="bpr-reel-header">
                    <h1 class="bpr-reel-title"><?php the_title(); ?></h1>
                    
                    <div class="bpr-reel-meta">
                        <div class="bpr-author-info">
                            <?php 
                            $author_id = get_the_author_meta('ID');
                            $avatar_url = function_exists('bp_core_fetch_avatar') ? 
                                bp_core_fetch_avatar(['item_id' => $author_id, 'html' => false, 'width' => 48, 'height' => 48]) : 
                                get_avatar_url($author_id, ['size' => 48]);
                            $profile_url = function_exists('bp_core_get_user_domain') ? 
                                bp_core_get_user_domain($author_id) : 
                                get_author_posts_url($author_id);
                            ?>
                            <a href="<?php echo esc_url($profile_url); ?>" class="bpr-author-link">
                                <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr(get_the_author_meta('display_name')); ?>" class="bpr-author-avatar">
                                <span class="bpr-author-name"><?php echo esc_html(get_the_author_meta('display_name')); ?></span>
                            </a>
                        </div>
                        
                        <div class="bpr-reel-date">
                            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                <?php echo human_time_diff(get_the_time('U'), current_time('timestamp')); ?> <?php _e('ago', 'buddypress-reels'); ?>
                            </time>
                        </div>
                    </div>
                </header>

                <div class="bpr-reel-content">
                    <?php 
                    $video_id = get_post_meta(get_the_ID(), 'bpr_video', true);
                    if ($video_id) :
                        $video_url = wp_get_attachment_url($video_id);
                        if ($video_url) : ?>
                            <div class="bpr-single-video-wrapper">
                                <video class="bpr-single-video" controls preload="metadata">
                                    <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                                    <?php _e('Your browser does not support the video tag.', 'buddypress-reels'); ?>
                                </video>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (get_the_content()) : ?>
                        <div class="bpr-reel-description">
                            <?php the_content(); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php 
                // Display comments if enabled
                $opts = get_option('bpr_settings', []);
                if ($opts['enable_comments'] === '1') {
                    comments_template();
                }
                ?>

            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <div class="bpr-no-reel">
            <h1><?php _e('Reel not found', 'buddypress-reels'); ?></h1>
            <p><?php _e('The requested reel could not be found.', 'buddypress-reels'); ?></p>
        </div>
    <?php endif; ?>
</div>

<style>
.bpr-single-reel-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.bpr-single-reel {
    background: var(--bpr-glass, rgba(255, 255, 255, 0.08));
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(20px);
}

.bpr-reel-header {
    padding: 2rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.bpr-reel-title {
    margin: 0 0 1rem 0;
    font-size: 2rem;
    font-weight: 700;
    color: var(--bpr-text-primary, #ffffff);
}

.bpr-reel-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.bpr-author-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    color: var(--bpr-text-primary, #ffffff);
    transition: all 0.3s ease;
}

.bpr-author-link:hover {
    opacity: 0.8;
}

.bpr-author-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}

.bpr-author-name {
    font-weight: 600;
    font-size: 1.1rem;
}

.bpr-reel-date time {
    color: var(--bpr-text-secondary, rgba(255, 255, 255, 0.8));
    font-size: 0.9rem;
}

.bpr-reel-content {
    padding: 2rem;
}

.bpr-single-video-wrapper {
    margin-bottom: 2rem;
    border-radius: 8px;
    overflow: hidden;
    background: #000;
}

.bpr-single-video {
    width: 100%;
    height: auto;
    max-height: 70vh;
    object-fit: contain;
}

.bpr-reel-description {
    font-size: 1.1rem;
    line-height: 1.6;
    color: var(--bpr-text-primary, #ffffff);
}

.bpr-no-reel {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--bpr-text-primary, #ffffff);
}

@media (max-width: 768px) {
    .bpr-single-reel-container {
        margin: 1rem auto;
        padding: 0 0.5rem;
    }
    
    .bpr-reel-header,
    .bpr-reel-content {
        padding: 1.5rem;
    }
    
    .bpr-reel-title {
        font-size: 1.5rem;
    }
    
    .bpr-reel-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>

<?php get_footer(); ?>
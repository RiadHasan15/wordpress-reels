<?php
/**
 * Archive template for BuddyPress Reels
 * Displays vertical scroll feed with theme header and page title
 */

get_header(); ?>

<div class="bpr-archive-page">
    <div class="bpr-page-header">
        <div class="container">
            <h1 class="bpr-page-title"><?php _e('Reels', 'buddypress-reels'); ?></h1>
            <p class="bpr-page-description"><?php _e('Discover amazing vertical videos from our community', 'buddypress-reels'); ?></p>
            
            <?php if (is_user_logged_in()): ?>
                <div class="bpr-upload-actions">
                    <a href="<?php echo esc_url(home_url('/upload-reel/')); ?>" class="bpr-btn-upload">
                        <?php _e('Upload Your Reel', 'buddypress-reels'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="bpr-feed-section">
        <?php 
        // Display the vertical scroll feed
        echo do_shortcode('[bpr_reels_feed count="50"]'); 
        ?>
    </div>
</div>

<?php get_footer(); ?>

<style>
/* Archive page with theme integration */
.bpr-archive-page {
    max-width: 100%;
    margin: 0 auto;
}

/* Page header section */
.bpr-page-header {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
    color: white;
    padding: 3rem 0;
    text-align: center;
    margin-bottom: 2rem;
}

.bpr-page-header .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.bpr-page-title {
    font-size: 3rem;
    font-weight: 700;
    margin: 0 0 1rem 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    line-height: 1.2;
}

.bpr-page-description {
    font-size: 1.2rem;
    margin: 0 0 2rem 0;
    opacity: 0.9;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.bpr-upload-actions {
    margin-top: 1.5rem;
}

.bpr-btn-upload {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 1rem 2rem;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.bpr-btn-upload:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    color: white;
    text-decoration: none;
}

/* Feed section */
.bpr-feed-section {
    display: flex;
    justify-content: center;
    padding: 0 20px;
    margin-bottom: 3rem;
}

/* Override feed styles to work with theme layout */
.bpr-feed-section .bpr-feed {
    max-width: 480px !important;
    width: 100% !important;
    height: 70vh !important;
    margin: 0 !important;
    border-radius: 12px !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.24) !important;
    border: 1px solid rgba(255, 255, 255, 0.18) !important;
}

.bpr-feed-section .bpr-video-wrapper {
    height: 70vh !important;
    border-radius: 12px !important;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .bpr-page-header {
        padding: 2rem 0;
        margin-bottom: 1rem;
    }
    
    .bpr-page-title {
        font-size: 2.5rem;
    }
    
    .bpr-page-description {
        font-size: 1.1rem;
        margin-bottom: 1.5rem;
    }
    
    .bpr-btn-upload {
        padding: 0.8rem 1.5rem;
        font-size: 1rem;
    }
    
    .bpr-feed-section {
        padding: 0 10px;
    }
    
    .bpr-feed-section .bpr-feed {
        max-width: 100% !important;
        height: 60vh !important;
        border-radius: 8px !important;
    }
    
    .bpr-feed-section .bpr-video-wrapper {
        height: 60vh !important;
        border-radius: 8px !important;
    }
}

/* Extra mobile optimization */
@media (max-width: 480px) {
    .bpr-page-header {
        padding: 1.5rem 0;
    }
    
    .bpr-page-title {
        font-size: 2rem;
    }
    
    .bpr-page-description {
        font-size: 1rem;
    }
    
    .bpr-feed-section .bpr-feed {
        height: 55vh !important;
    }
    
    .bpr-feed-section .bpr-video-wrapper {
        height: 55vh !important;
    }
}

/* Ensure proper spacing with theme */
.bpr-archive-page .bpr-feed {
    scroll-behavior: smooth;
}
</style>
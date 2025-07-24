<?php
/**
 * Archive template for BuddyPress Reels
 * Displays vertical scroll feed instead of default post cards
 */

// Don't load header/footer for full TikTok experience
// get_header(); 
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title><?php _e('Reels', 'buddypress-reels'); ?> - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
    <style>
        /* Remove theme interference */
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            height: 100% !important;
            background: #0a0a0b !important;
            overflow: hidden !important;
        }
        #page, #main, .site-main, .content-area {
            margin: 0 !important;
            padding: 0 !important;
        }
        /* Hide admin bar on this page */
        #wpadminbar {
            display: none !important;
        }
        html {
            margin-top: 0 !important;
        }
    </style>
</head>
<body <?php body_class('bpr-fullscreen-archive'); ?>>

<div class="bpr-archive-container">
    <!-- Floating header with back button -->
    <div class="bpr-floating-header">
        <button class="bpr-back-btn" onclick="window.history.back()">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.42-1.41L7.83 13H20v-2z"/>
            </svg>
        </button>
        <h1 class="bpr-floating-title"><?php _e('Reels', 'buddypress-reels'); ?></h1>
        <?php if (is_user_logged_in()): ?>
            <a href="<?php echo esc_url(home_url('/upload-reel/')); ?>" class="bpr-floating-upload">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 7v2.99s-1.99.01-2 0V7h-3s.01-1.99 0-2h3V2h2v3h3v2h-3zm-3 4V8h-3V5H5c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2v-8h-3zM5 19l3-4 2 3 3-4 4 5H5z"/>
                </svg>
            </a>
        <?php endif; ?>
    </div>
    
    <div class="bpr-archive-content">
        <?php 
        // Display the vertical scroll feed with more reels
        echo do_shortcode('[bpr_reels_feed count="100"]'); 
        ?>
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>

<style>
/* Full-screen TikTok-style archive */
.bpr-archive-container {
    width: 100vw;
    height: 100vh;
    position: relative;
    background: #0a0a0b;
    overflow: hidden;
}

/* Floating header like TikTok */
.bpr-floating-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 60px;
    background: linear-gradient(180deg, rgba(0,0,0,0.7) 0%, transparent 100%);
    backdrop-filter: blur(20px);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    z-index: 1000;
    color: white;
}

.bpr-back-btn, .bpr-floating-upload {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    text-decoration: none;
}

.bpr-back-btn:hover, .bpr-floating-upload:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.1);
    color: white;
}

.bpr-floating-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin: 0;
    text-shadow: 0 2px 8px rgba(0,0,0,0.5);
}

.bpr-archive-content {
    width: 100%;
    height: 100vh;
    padding: 0;
    margin: 0;
}

/* Override feed styles for full-screen */
.bpr-archive-content .bpr-feed {
    max-width: 100vw !important;
    width: 100vw !important;
    height: 100vh !important;
    margin: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    border: none !important;
}

.bpr-archive-content .bpr-video-wrapper {
    width: 100vw !important;
    height: 100vh !important;
    border-radius: 0 !important;
    margin: 0 !important;
}

/* Mobile-first responsive */
@media (max-width: 768px) {
    .bpr-floating-header {
        height: 50px;
        padding: 0 15px;
    }
    
    .bpr-back-btn, .bpr-floating-upload {
        width: 40px;
        height: 40px;
    }
    
    .bpr-floating-title {
        font-size: 1.1rem;
    }
    
    .bpr-back-btn svg, .bpr-floating-upload svg {
        width: 20px;
        height: 20px;
    }
}

/* Hide any theme elements that might interfere */
.bpr-fullscreen-archive .site-header,
.bpr-fullscreen-archive .site-footer,
.bpr-fullscreen-archive #wpadminbar {
    display: none !important;
}

/* Smooth transitions */
* {
    box-sizing: border-box;
}
</style>
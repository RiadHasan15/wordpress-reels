<?php
$args = array(
    'post_type' => 'attachment',
    'post_mime_type' => 'video',
    'posts_per_page' => -1,
    'author' => bp_displayed_user_id(),
    'post_status' => 'inherit',
);
$videos = get_posts($args);
?>

<div class="bpr-grid-wrapper">
    <?php foreach ($videos as $video): ?>
        <div class="bpr-grid-item" data-video-url="<?php echo wp_get_attachment_url($video->ID); ?>">
            <video muted preload="metadata">
                <source src="<?php echo wp_get_attachment_url($video->ID); ?>" type="video/mp4">
            </video>
        </div>
    <?php endforeach; ?>
</div>

<?php
$args = [
    'post_type' => 'bp_reel',
    'posts_per_page' => 10,
];

$reels = new WP_Query($args);

if ($reels->have_posts()) :
    while ($reels->have_posts()) : $reels->the_post();
        $video_url = get_the_content();
        $author = get_the_author_meta('display_name', get_post_field('post_author', get_the_ID()));
        ?>
        <div class="wpvr-video-wrapper">
            <video src="<?php echo esc_url($video_url); ?>" loop autoplay playsinline muted></video>
            <div class="wpvr-sound-toggle">🔇</div>
            <div class="wpvr-playpause-anim"></div>
            <div class="wpvr-meta">
                <h4><?php the_title(); ?></h4>
                <p>by <?php echo esc_html($author); ?></p>
            </div>
        </div>
        <?php
    endwhile;
    wp_reset_postdata();
else :
    echo '<p>No reels found.</p>';
endif;

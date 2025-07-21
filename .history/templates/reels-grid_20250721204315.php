<?php
$args=['post_type'=>'bpr_reel','author'=>bp_displayed_user_id(),'posts_per_page'=>-1,'orderby'=>'date','order'=>'DESC'];
$q=new WP_Query($args);
?>
<div class="bpr-grid-wrapper">
<?php while($q->have_posts()): $q->the_post();
    $vid=wp_get_attachment_url(get_post_meta(get_the_ID(),'bpr_video',true));
    if (!$vid) continue; ?>
    <div class="bpr-grid-item" data-video="<?php echo esc_attr($vid); ?>">
        <video muted loop preload="metadata"><source src="<?php echo esc_url($vid);?>" type="video/mp4"></video>
    </div>
<?php endwhile; wp_reset_postdata();?>
</div>

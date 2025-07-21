<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="bpr-upload-form" novalidate>
    <input type="hidden" name="action" value="bpr_upload_reel" />
    <?php wp_nonce_field('bpr_upload_nonce'); ?>

    <p>
        <label for="bpr_title">Title</label><br/>
        <input type="text" name="bpr_title" id="bpr_title" required maxlength="100" style="width:100%;" />
    </p>

    <p>
        <label for="bpr_description">Description</label><br/>
        <textarea name="bpr_description" id="bpr_description" rows="4" maxlength="300" style="width:100%;"></textarea>
    </p>

    <p>
        <label for="bpr_video">Upload Video (MP4/WebM)</label><br/>
        <input type="file" name="bpr_video" id="bpr_video" accept="video/mp4,video/webm" required />
    </p>

    <p>
        <button type="submit" class="button button-primary">Upload Reel</button>
    </p>
</form>

<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="bpr-upload-form" novalidate>
    <input type="hidden" name="action" value="bpr_upload_reel" />
    <?php wp_nonce_field('bpr_upload_nonce'); ?>

    <p>
        <label for="bpr_title">Title</label><br />
        <input type="text" id="bpr_title" name="bpr_title" required maxlength="100" />
    </p>
    <p>
        <label for="bpr_description">Description</label><br />
        <textarea id="bpr_description" name="bpr_description" rows="4" required maxlength="300"></textarea>
    </p>
    <p>
        <label for="bpr_video">Upload Video (mp4 only)</label><br />
        <input type="file" id="bpr_video" name="bpr_video" accept="video/mp4" required />
    </p>
    <p>
        <button type="submit" class="button button-primary">Upload Reel</button>
    </p>
</form>

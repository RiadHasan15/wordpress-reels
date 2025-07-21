<?php if ( isset($_GET['uploaded']) ): ?>
    <p class="bpr-success">✅ Uploaded successfully!</p>
<?php endif; ?>

<?php if ( is_user_logged_in() ): ?>
<form method="post" enctype="multipart/form-data" class="bpr-upload-form">
    <input type="text" name="reel_title" placeholder="Title" required>
    <textarea name="reel_description" placeholder="Description" required></textarea>
    <input type="file" name="reel_video" accept="video/*" required>
    <?php wp_nonce_field('bpr_upload', 'bpr_nonce'); ?>
    <button type="submit">Upload Reel</button>
</form>
<?php else: ?>
    <p>You need to <a href="<?php echo wp_login_url(); ?>">log in</a> to upload.</p>
<?php endif; ?>

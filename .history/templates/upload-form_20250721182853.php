<?php if (isset($_GET['reel_uploaded'])): ?>
    <p style="color:green;">✅ Reel uploaded successfully!</p>
<?php endif; ?>
<form method="post" enctype="multipart/form-data" class="bp-reel-upload-form">
    <?php wp_nonce_field('bp_reel_upload', 'bp_reel_nonce'); ?>
    <label>Reel Title</label>
    <input type="text" name="reel_title" required>
    
    <label>Description</label>
    <textarea name="reel_description" required></textarea>
    
    <label>Upload Video</label>
    <input type="file" name="reel_video" accept="video/mp4" required>

    <button type="submit">Upload Reel</button>
</form>

<div id="bpr-modal" class="bpr-modal">
    <div class="bpr-modal-content">
        <span class="bpr-close">&times;</span>
        <video id="bpr-full-video" controls autoplay></video>
        <div class="bpr-user-info">
            <?php
            $user_id = bp_displayed_user_id();
            echo get_avatar($user_id, 32);
            echo '<a href="' . bp_core_get_user_domain($user_id) . '">' . bp_core_get_username($user_id) . '</a>';
            ?>
        </div>
    </div>
</div>

jQuery(document).ready(function($) {
    const modal = $('#bpr-modal');
    const video = $('#bpr-full-video')[0];

    $('.bpr-grid-item').on('click', function () {
        const videoUrl = $(this).data('video-url');
        video.src = videoUrl;
        video.play();
        modal.fadeIn();
    });

    $('.bpr-close').on('click', function () {
        video.pause();
        modal.fadeOut();
    });
});

jQuery(document).ready(function ($) {
    // Variables
    let muteAll = bprSettings.default_muted === '1';
    let videos = $('.bpr-video');
    let muteToggles = $('.bpr-mute-toggle');

    // Apply default mute/unmute to all videos
    function applyMuteState() {
        videos.each(function () {
            this.muted = muteAll;
            $(this).siblings('.bpr-mute-toggle').text(muteAll ? '🔇' : '🔊').attr('data-muted', muteAll ? 'true' : 'false');
        });
    }
    applyMuteState();

    // Mute toggle button click handler (toggle all videos)
    muteToggles.on('click', function (e) {
        e.stopPropagation();
        muteAll = !muteAll;
        applyMuteState();
    });

    // Video click to toggle pause/resume with animation
    videos.on('click', function () {
        const video = this;
        const $wrapper = $(video).closest('.bpr-video-wrapper');
        const $pauseIcon = $wrapper.find('.bpr-pause-icon');
        const $playIcon = $wrapper.find('.bpr-play-icon');

        if (video.paused) {
            video.play();
            $pauseIcon.removeClass('show');
            $playIcon.addClass('show');
            setTimeout(() => {
                $playIcon.removeClass('show');
            }, 700);
        } else {
            video.pause();
            $playIcon.removeClass('show');
            $pauseIcon.addClass('show');
            setTimeout(() => {
                $pauseIcon.removeClass('show');
            }, 700);
        }
    });

    // Pause all videos except the one in viewport (optional advanced feature)
    // To keep performance good, you can add intersection observer here if needed.

});



jQuery(document).ready(function ($) {
    // Play video on hover for grid view, pause on mouse leave
    $('.bpr-grid-item').hover(function () {
        var video = $(this).find('video')[0];
        if (video) {
            video.play();
        }
    }, function () {
        var video = $(this).find('video')[0];
        if (video) {
            video.pause();
            video.currentTime = 0;
        }
    });
});

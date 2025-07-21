jQuery(document).ready(function ($) {
    let muteAll = bprSettings.default_muted === '1';
    let videos = $('.bpr-video');
    let toggles = $('.bpr-mute-toggle');

    function applyMute() {
        videos.each(function () {
            this.muted = muteAll;
            $(this).siblings('.bpr-mute-toggle').text(muteAll ? '🔇' : '🔊').attr('data-muted', muteAll ? 'true' : 'false');
        });
    }
    applyMute();

    toggles.on('click', function (e) {
        e.stopPropagation();
        muteAll = !muteAll;
        applyMute();
    });

    videos.on('click', function () {
        const video = this;
        const wrapper = $(video).closest('.bpr-video-wrapper');
        const $pause = wrapper.find('.bpr-pause-icon');
        const $play = wrapper.find('.bpr-play-icon');

        if (video.paused) {
            video.play();
            $pause.removeClass('show');
            $play.addClass('show');
            setTimeout(() => { $play.removeClass('show'); }, 700);
        } else {
            video.pause();
            $play.removeClass('show');
            $pause.addClass('show');
            setTimeout(() => { $pause.removeClass('show'); }, 700);
        }
    });
});






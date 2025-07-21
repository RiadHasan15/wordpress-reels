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





jQuery(function ($) {
    // grid hover play
    $('.bpr-grid-wrapper').on('mouseenter', '.bpr-grid-item', function () {
        this.querySelector('video').play();
    }).on('mouseleave', '.bpr-grid-item', function () {
        let v = this.querySelector('video');
        v.pause(); v.currentTime = 0;
    });

    // click grid
    $('body').append('<div class="bpr-modal"><div class="bpr-modal-content"><span class="bpr-close">&#10005;</span><video id="bpr-full-video" controls autoplay></video></div></div>');
    $(document).on('click', '.bpr-grid-item', function () {
        let src = $(this).data('video');
        $('#bpr-full-video').attr('src', src);
        $('.bpr-modal').addClass('active');
    });
    $(document).on('click', '.bpr-close, .bpr-modal', function (e) {
        if (e.target !== this) return;
        $('#bpr-full-video').attr('src', '').get(0).pause();
        $('.bpr-modal').removeClass('active');
    });
    $(document).on('click', '#bpr-full-video', function (e) {
        e.stopPropagation();
    });
});

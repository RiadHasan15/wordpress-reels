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





.bpr - grid - wrapper {
    display: grid;
    grid - template - columns: repeat(3, 1fr);
    gap: 8px;
    padding: 10px;
}
.bpr - grid - item { position: relative; padding - top: 177.78 %; overflow: hidden; border - radius: 8px; background:#000; cursor: pointer; }
.bpr - grid - item video { position: absolute; top: 0; left: 0; width: 100 %; height: 100 %; object - fit: cover; }
.bpr - modal { display: none; position: fixed; top: 0; left: 0; width: 100 %; height: 100vh; background: rgba(0, 0, 0, 0.9); align - items: center; justify - content: center; z - index: 9999; }
.bpr - modal.active{ display: flex; }
.bpr - modal - content{ position: relative; width: 90 %; max - width: 500px; }
#bpr - full - video{ width: 100 %; max - height: 85vh; }
.bpr - close{ position: absolute; top: 10px; right: 15px; font - size: 28px; color: #fff; cursor: pointer; }
@media(max - width: 768px) {.bpr - grid - wrapper{ grid - template - columns: repeat(2, 1fr); } }
@media(max - width: 480px) {.bpr - grid - wrapper{ grid - template - columns: 1fr; } }

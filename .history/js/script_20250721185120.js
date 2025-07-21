jQuery(document).ready(function ($) {
    const videos = $('.bpr-video');
    const muteToggle = $('.bpr-mute-toggle');

    let globalMuted = true;

    muteToggle.on('click', function () {
        globalMuted = !globalMuted;
        $(this).text(globalMuted ? '🔇' : '🔊');
        videos.each(function () {
            this.muted = globalMuted;
        });
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const video = entry.target;
            if (entry.isIntersecting) {
                video.play();
            } else {
                video.pause();
            }
        });
    }, { threshold: 0.8 });

    videos.each(function () {
        observer.observe(this);

        // pause/resume toggle on click
        $(this).on('click', function () {
            const icon = $(this).siblings('.bpr-pause-icon');
            if (this.paused) {
                this.play();
                icon.fadeIn(150).text('▶️').delay(500).fadeOut(300);
            } else {
                this.pause();
                icon.fadeIn(150).text('⏸️').delay(500).fadeOut(300);
            }
        });
    });
});

jQuery(document).ready(function ($) {
    const videos = $('.bpr-video');
    const muteToggle = $('.bpr-mute-toggle');

    let globalMuted = true;

    // Initialize videos muted and autoplay
    videos.each(function () {
        this.muted = true;
        this.play().catch(() => { });
    });

    // Mute toggle button logic
    muteToggle.on('click', function () {
        globalMuted = !globalMuted;
        muteToggle.text(globalMuted ? '🔇' : '🔊');
        videos.each(function () {
            this.muted = globalMuted;
        });
    });

    // Intersection Observer to play only visible video
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            const video = entry.target;
            if (entry.isIntersecting) {
                video.play().catch(() => { });
            } else {
                video.pause();
            }
        });
    }, { threshold: 0.85 });

    videos.each(function () {
        observer.observe(this);

        // Pause/Play toggle on video click with animation
        $(this).on('click', function () {
            const $wrapper = $(this).closest('.bpr-video-wrapper');
            const pauseIcon = $wrapper.find('.bpr-pause-icon');
            const playIcon = $wrapper.find('.bpr-play-icon');

            if (this.paused) {
                this.play();
                pauseIcon.hide();
                playIcon.fadeIn(150).delay(600).fadeOut(300);
            } else {
                this.pause();
                playIcon.hide();
                pauseIcon.fadeIn(150).delay(600).fadeOut(300);
            }
        });
    });
});

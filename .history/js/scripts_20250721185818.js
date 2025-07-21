jQuery(document).ready(function ($) {
    const videos = $('.bpr-video');
    const muteToggle = $('.bpr-mute-toggle');

    let globalMuted = true;

    // Initialize: videos start muted for autoplay compliance
    videos.each(function () {
        this.muted = true;
        this.play().catch(() => { });
    });

    // Global mute/unmute toggle button
    muteToggle.on('click', function () {
        globalMuted = !globalMuted;
        muteToggle.text(globalMuted ? '🔇' : '🔊');
        videos.each(function () {
            this.muted = globalMuted;
            // If unmuting, play video again if paused by autoplay policy
            if (!globalMuted && this.paused) {
                this.play().catch(() => { });
            }
        });
    });

    // Intersection Observer: play only visible videos
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

        // Pause/resume toggle on video click with animation
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

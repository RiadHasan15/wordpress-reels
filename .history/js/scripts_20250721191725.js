jQuery(document).ready(function ($) {
    const videos = $('.bpr-video');
    const muteToggles = $('.bpr-mute-toggle');

    // Global mute state
    let globalMuted = true;

    // Initialize videos muted for autoplay compliance
    videos.each(function () {
        this.muted = true;
        this.play().catch(() => { });
    });

    // Update all mute toggle icons
    function updateMuteIcons() {
        muteToggles.text(globalMuted ? '🔇' : '🔊');
    }

    // Toggle mute for all videos globally
    muteToggles.on('click', function (e) {
        e.stopPropagation(); // Prevent triggering video pause/play

        globalMuted = !globalMuted;
        videos.each(function () {
            this.muted = globalMuted;
            if (!globalMuted && this.paused) {
                this.play().catch(() => { });
            }
        });
        updateMuteIcons();
    });

    updateMuteIcons();

    // Intersection Observer to autoplay only visible videos
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

        const $wrapper = $(this).closest('.bpr-video-wrapper');
        const pauseIcon = $wrapper.find('.bpr-pause-icon');
        const playIcon = $wrapper.find('.bpr-play-icon');

        // Pause/resume toggle on video click with Instagram-like animation
        $(this).on('click', function () {
            if (this.paused) {
                this.play();
                pauseIcon.removeClass('show');
                playIcon.addClass('show');
                setTimeout(() => playIcon.removeClass('show'), 700);
            } else {
                this.pause();
                playIcon.removeClass('show');
                pauseIcon.addClass('show');
                setTimeout(() => pauseIcon.removeClass('show'), 700);
            }
        });
    });
});

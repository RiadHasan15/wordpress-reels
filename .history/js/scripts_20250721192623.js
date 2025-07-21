jQuery(document).ready(function ($) {
    const videos = $('.bpr-video');
    const muteToggles = $('.bpr-mute-toggle');

    // Global mute state
    let globalMuted = true;

    // Store currently visible video
    let currentVisibleVideo = null;

    // Initialize all videos muted for autoplay compliance
    videos.each(function () {
        this.muted = true;
        this.play().catch(() => { });
    });

    // Update all mute toggle icons globally
    function updateMuteIcons() {
        muteToggles.text(globalMuted ? '🔇' : '🔊');
    }

    // When global mute toggled
    muteToggles.on('click', function (e) {
        e.stopPropagation();

        globalMuted = !globalMuted;

        if (currentVisibleVideo) {
            // Mute/unmute only visible video based on globalMuted
            currentVisibleVideo.muted = globalMuted;
            if (!globalMuted && currentVisibleVideo.paused) {
                currentVisibleVideo.play().catch(() => { });
            }
        }

        // Mute all other videos
        videos.each(function () {
            if (this !== currentVisibleVideo) {
                this.muted = true;
            }
        });

        updateMuteIcons();
    });

    updateMuteIcons();

    // Intersection Observer to autoplay only visible videos and handle sound
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            const video = entry.target;
            if (entry.isIntersecting) {
                currentVisibleVideo = video;
                video.play().catch(() => { });
                // Apply sound only if global unmuted
                video.muted = globalMuted;
            } else {
                video.pause();
                // Mute videos not in view
                video.muted = true;
                // Clear currentVisibleVideo if it was this video
                if (currentVisibleVideo === video) {
                    currentVisibleVideo = null;
                }
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

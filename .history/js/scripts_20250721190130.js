jQuery(document).ready(function ($) {
    const videos = $('.bpr-video');

    // For each video wrapper get its mute toggle
    $('.bpr-video-wrapper').each(function () {
        const $wrapper = $(this);
        const video = $wrapper.find('.bpr-video').get(0);
        const muteToggle = $wrapper.find('.bpr-mute-toggle');
        const pauseIcon = $wrapper.find('.bpr-pause-icon');
        const playIcon = $wrapper.find('.bpr-play-icon');

        // Start muted for autoplay
        video.muted = true;
        video.play().catch(() => { });

        // Mute toggle button inside each video
        muteToggle.on('click', function (e) {
            e.stopPropagation(); // prevent pause/play toggle when clicking mute
            video.muted = !video.muted;
            muteToggle.text(video.muted ? '🔇' : '🔊');
        });

        // Pause/resume toggle on video click with Instagram-like animation
        $(video).on('click', function () {
            if (video.paused) {
                video.play();
                pauseIcon.removeClass('show');
                playIcon.addClass('show');
                setTimeout(() => playIcon.removeClass('show'), 700);
            } else {
                video.pause();
                playIcon.removeClass('show');
                pauseIcon.addClass('show');
                setTimeout(() => pauseIcon.removeClass('show'), 700);
            }
        });
    });

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
    });
});

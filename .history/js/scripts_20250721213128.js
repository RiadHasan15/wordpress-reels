document.addEventListener('DOMContentLoaded', function () {
    const videoWrappers = document.querySelectorAll('.bpr-video-wrapper');
    let currentlyUnmutedVideo = null;

    function muteAllExcept(videoToKeep) {
        videoWrappers.forEach(wrapper => {
            const video = wrapper.querySelector('video');
            const btn = wrapper.querySelector('.bpr-mute-toggle');
            if (!video || !btn) return;

            if (video === videoToKeep) {
                video.muted = false;
                btn.textContent = '🔊';
                currentlyUnmutedVideo = video;
                video.play().catch(() => { });
            } else {
                video.muted = true;
                btn.textContent = '🔇';
            }
        });
    }

    // Autoplay & unmute the first visible video
    let unmutedFirst = false;
    videoWrappers.forEach(wrapper => {
        const video = wrapper.querySelector('video');
        const btn = wrapper.querySelector('.bpr-mute-toggle');
        if (!video || !btn) return;

        if (!unmutedFirst && video.getBoundingClientRect().top < window.innerHeight) {
            video.muted = false;
            video.play().catch(() => { });
            btn.textContent = '🔊';
            currentlyUnmutedVideo = video;
            unmutedFirst = true;
        } else {
            video.muted = true;
            btn.textContent = '🔇';
        }
    });

    // Mute/unmute button click
    document.querySelectorAll('.bpr-mute-toggle').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const wrapper = btn.closest('.bpr-video-wrapper');
            const video = wrapper.querySelector('video');
            if (!video) return;

            if (video.muted) {
                muteAllExcept(video);
            } else {
                video.muted = true;
                btn.textContent = '🔇';
            }
        });
    });

    // Pause/resume on video click
    videoWrappers.forEach(wrapper => {
        const video = wrapper.querySelector('video');
        const pauseIcon = wrapper.querySelector('.bpr-pause-icon');
        const playIcon = wrapper.querySelector('.bpr-play-icon');

        wrapper.addEventListener('click', function () {
            if (video.paused) {
                video.play();
                if (playIcon) {
                    playIcon.classList.add('show');
                    setTimeout(() => playIcon.classList.remove('show'), 500);
                }
            } else {
                video.pause();
                if (pauseIcon) {
                    pauseIcon.classList.add('show');
                    setTimeout(() => pauseIcon.classList.remove('show'), 500);
                }
            }
        });
    });
});

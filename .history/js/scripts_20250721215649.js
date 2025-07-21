document.addEventListener('DOMContentLoaded', function () {
    const videos = document.querySelectorAll('.bpr-video');
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.8
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const video = entry.target;
            const wrapper = video.closest('.bpr-video-wrapper');
            const muteBtn = wrapper.querySelector('.bpr-mute-toggle');

            if (entry.isIntersecting) {
                // Pause and mute all other videos
                videos.forEach(v => {
                    if (v !== video) {
                        v.pause();
                        v.muted = true;

                        const otherWrapper = v.closest('.bpr-video-wrapper');
                        const otherMuteBtn = otherWrapper?.querySelector('.bpr-mute-toggle');
                        if (otherMuteBtn) {
                            otherMuteBtn.textContent = '🔇';
                            otherMuteBtn.dataset.userMuted = 'false'; // Reset other buttons
                        }
                    }
                });

                // Play this video
                video.play().catch(() => { });

                // Only unmute if user hasn't manually muted
                if (muteBtn?.dataset.userMuted !== 'true') {
                    video.muted = false;
                    muteBtn.textContent = '🔊';
                }

            } else {
                // Pause and mute if out of view
                video.pause();
                video.muted = true;
                if (muteBtn) {
                    muteBtn.textContent = '🔇';
                }
            }
        });
    }, observerOptions);

    videos.forEach(video => {
        observer.observe(video);

        // Pause/Play on click
        video.addEventListener('click', function () {
            const icon = this.parentElement.querySelector('.bpr-play-icon, .bpr-pause-icon');
            if (video.paused) {
                video.play();
                icon?.classList.remove('show');
            } else {
                video.pause();
                icon?.classList.add('show');
                setTimeout(() => {
                    icon?.classList.remove('show');
                }, 800);
            }
        });
    });

    // Mute toggle button
    document.querySelectorAll('.bpr-mute-toggle').forEach(button => {
        button.dataset.userMuted = 'false'; // Set default
        button.addEventListener('click', function () {
            const video = this.closest('.bpr-video-wrapper').querySelector('video');
            if (video) {
                video.muted = !video.muted;
                this.textContent = video.muted ? '🔇' : '🔊';
                this.dataset.userMuted = video.muted ? 'true' : 'false';
            }
        });
    });

    // Modal close
    const closeBtn = document.querySelector('.bpr-close');
    const modal = document.querySelector('.bpr-modal');
    const fullVideo = document.getElementById('bpr-full-video');
    if (closeBtn && modal && fullVideo) {
        closeBtn.addEventListener('click', function () {
            modal.classList.remove('active');
            fullVideo.pause();
        });
    }
});

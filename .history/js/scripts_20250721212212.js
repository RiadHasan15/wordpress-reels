document.addEventListener('DOMContentLoaded', function () {

    const videoWrappers = document.querySelectorAll('.bpr-video-wrapper');

    // Mute toggle buttons
    const muteToggles = document.querySelectorAll('.bpr-mute-toggle');

    // Mute all videos helper
    function muteAllVideos() {
        videoWrappers.forEach(wrapper => {
            const video = wrapper.querySelector('video');
            const btn = wrapper.querySelector('.bpr-mute-toggle');
            if (!video || !btn) return;
            video.muted = true;
            btn.textContent = '🔇';
        });
    }

    // Initialize all muted
    muteAllVideos();

    // Intersection Observer callback - detect which video is mostly visible
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.6 // 60% visible triggers unmute
    };

    let currentlyUnmutedVideo = null;

    const observerCallback = (entries) => {
        entries.forEach(entry => {
            const wrapper = entry.target;
            const video = wrapper.querySelector('video');
            const btn = wrapper.querySelector('.bpr-mute-toggle');
            if (!video || !btn) return;

            if (entry.isIntersecting && entry.intersectionRatio >= 0.6) {
                // This video is mostly in view → unmute and update button icon
                if (currentlyUnmutedVideo && currentlyUnmutedVideo !== video) {
                    // Mute previously unmuted video
                    currentlyUnmutedVideo.muted = true;
                    const prevBtn = currentlyUnmutedVideo.closest('.bpr-video-wrapper').querySelector('.bpr-mute-toggle');
                    if (prevBtn) prevBtn.textContent = '🔇';
                }
                video.muted = false;
                btn.textContent = '🔊';
                currentlyUnmutedVideo = video;
            } else {
                // Out of view or less than 60%, mute it
                if (video !== currentlyUnmutedVideo) {
                    video.muted = true;
                    btn.textContent = '🔇';
                }
            }
        });
    };

    const observer = new IntersectionObserver(observerCallback, observerOptions);

    videoWrappers.forEach(wrapper => observer.observe(wrapper));

    // Mute toggle button click handler per video
    muteToggles.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const wrapper = btn.closest('.bpr-video-wrapper');
            if (!wrapper) return;
            const video = wrapper.querySelector('video');
            if (!video) return;

            if (video.muted) {
                // Unmute this video and mute others
                video.muted = false;
                btn.textContent = '🔊';

                // Mute all other videos and update buttons
                videoWrappers.forEach(wrap => {
                    if (wrap !== wrapper) {
                        const vid = wrap.querySelector('video');
                        const b = wrap.querySelector('.bpr-mute-toggle');
                        if (vid && b) {
                            vid.muted = true;
                            b.textContent = '🔇';
                        }
                    }
                });

                currentlyUnmutedVideo = video;
            } else {
                // Mute this video
                video.muted = true;
                btn.textContent = '🔇';
                currentlyUnmutedVideo = null;
            }
        });
    });

    // Pause/resume on video click with animation
    videoWrappers.forEach(wrapper => {
        const video = wrapper.querySelector('video');
        const pauseIcon = wrapper.querySelector('.bpr-pause-icon');
        const playIcon = wrapper.querySelector('.bpr-play-icon');
        if (!video) return;

        wrapper.addEventListener('click', function () {
            if (video.paused) {
                video.play();
                if (playIcon) {
                    playIcon.classList.add('show');
                    setTimeout(() => playIcon.classList.remove('show'), 600);
                }
            } else {
                video.pause();
                if (pauseIcon) {
                    pauseIcon.classList.add('show');
                    setTimeout(() => pauseIcon.classList.remove('show'), 600);
                }
            }
        });
    });

    // Modal reel open/close (modal videos always muted by default)
    const modal = document.getElementById('bpr-reel-modal');
    const modalContent = document.getElementById('bpr-reel-modal-content');
    const closeModal = document.getElementById('bpr-close-modal');

    document.querySelectorAll('.bpr-grid-item').forEach(item => {
        item.addEventListener('click', function () {
            const videoSrc = this.getAttribute('data-video');
            modalContent.innerHTML = `
        <video src="${videoSrc}" autoplay loop playsinline muted></video>
      `;
            if (modal) modal.style.display = 'flex';
        });
    });

    if (closeModal) {
        closeModal.addEventListener('click', function () {
            if (modal) {
                modal.style.display = 'none';
                modalContent.innerHTML = '';
            }
        });
    }

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.style.display = 'none';
                modalContent.innerHTML = '';
            }
        });
    }

});

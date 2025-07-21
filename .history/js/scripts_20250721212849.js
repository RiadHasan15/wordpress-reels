document.addEventListener('DOMContentLoaded', function () {

    const videoWrappers = document.querySelectorAll('.bpr-video-wrapper');
    const muteToggles = document.querySelectorAll('.bpr-mute-toggle');

    let currentlyUnmutedVideo = null;

    // Helper to mute all videos except one (or all if none passed)
    function muteAllExcept(exceptVideo) {
        videoWrappers.forEach(wrapper => {
            const video = wrapper.querySelector('video');
            const btn = wrapper.querySelector('.bpr-mute-toggle');
            if (!video || !btn) return;

            if (video === exceptVideo) {
                video.muted = false;
                btn.textContent = '🔊';
                currentlyUnmutedVideo = video;
                // Autoplay the unmuted video
                video.play().catch(() => { /* ignore autoplay errors */ });
            } else {
                video.muted = true;
                btn.textContent = '🔇';
                // Optionally pause other videos
                // video.pause();
            }
        });
    }

    // Initialize: unmute and autoplay first visible video, mute others
    // We'll try to find the first video at least partially visible on load
    let foundUnmuted = false;
    videoWrappers.forEach(wrapper => {
        const video = wrapper.querySelector('video');
        const btn = wrapper.querySelector('.bpr-mute-toggle');
        if (!video || !btn) return;

        // Check if video is at least partially visible in viewport
        const rect = wrapper.getBoundingClientRect();
        const visible = (rect.top < window.innerHeight) && (rect.bottom > 0);

        if (visible && !foundUnmuted) {
            video.muted = false;
            btn.textContent = '🔊';
            currentlyUnmutedVideo = video;
            video.play().catch(() => { });
            foundUnmuted = true;
        } else {
            video.muted = true;
            btn.textContent = '🔇';
        }
    });

    // If no video found visible (edge case), mute all
    if (!foundUnmuted) {
        videoWrappers.forEach(wrapper => {
            const video = wrapper.querySelector('video');
            const btn = wrapper.querySelector('.bpr-mute-toggle');
            if (!video || !btn) return;
            video.muted = true;
            btn.textContent = '🔇';
        });
    }

    // Intersection Observer setup
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.6 // 60% visibility triggers unmute
    };

    const observerCallback = (entries) => {
        entries.forEach(entry => {
            const wrapper = entry.target;
            const video = wrapper.querySelector('video');
            const btn = wrapper.querySelector('.bpr-mute-toggle');
            if (!video || !btn) return;

            if (entry.isIntersecting && entry.intersectionRatio >= 0.6) {
                // Unmute this video, mute others
                if (currentlyUnmutedVideo !== video) {
                    muteAllExcept(video);
                }
            } else {
                // Mute video if it's not the currently unmuted one
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
                muteAllExcept(video);
            } else {
                // Mute this video
                video.muted = true;
                btn.textContent = '🔇';
                if (currentlyUnmutedVideo === video) {
                    currentlyUnmutedVideo = null;
                }
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

    // Modal reel open/close (modal videos always muted)
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

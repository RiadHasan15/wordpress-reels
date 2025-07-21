document.addEventListener('DOMContentLoaded', function () {

    const videoWrappers = document.querySelectorAll('.bpr-video-wrapper');

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

    // Initialize: mute all videos on page load
    muteAllVideos();

    // Mute toggle button click handler per video
    document.querySelectorAll('.bpr-mute-toggle').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();

            const wrapper = btn.closest('.bpr-video-wrapper');
            if (!wrapper) return;
            const video = wrapper.querySelector('video');
            if (!video) return;

            if (video.muted) {
                // Unmute this video, mute all others
                muteAllVideos();
                video.muted = false;
                btn.textContent = '🔊';
            } else {
                // Mute this video
                video.muted = true;
                btn.textContent = '🔇';
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

    // Modal reel open/close (keep modal videos muted by default)
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

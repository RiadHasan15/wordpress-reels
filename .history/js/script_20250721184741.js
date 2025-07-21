document.addEventListener('DOMContentLoaded', function () {
    const reels = document.querySelectorAll('.bpr-reel video');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const video = entry.target;
            if (entry.isIntersecting) {
                video.play();
            } else {
                video.pause();
            }
        });
    }, {
        threshold: 0.9
    });

    reels.forEach(video => {
        observer.observe(video);

        const wrapper = video.closest('.bpr-reel');
        const muteBtn = wrapper.querySelector('.bpr-sound-toggle');
        const pauseIcon = wrapper.querySelector('.bpr-pause-icon');
        const playIcon = wrapper.querySelector('.bpr-play-icon');

        // Mute toggle
        muteBtn.addEventListener('click', () => {
            const isMuted = video.muted;
            document.querySelectorAll('.bpr-reel video').forEach(v => v.muted = isMuted ? false : true);
            document.querySelectorAll('.bpr-sound-toggle').forEach(btn => btn.textContent = isMuted ? '🔊' : '🔇');
        });

        // Pause/Play on click
        video.addEventListener('click', () => {
            if (video.paused) {
                video.play();
                pauseIcon.style.display = 'none';
                playIcon.style.display = 'block';
                setTimeout(() => playIcon.style.display = 'none', 500);
            } else {
                video.pause();
                playIcon.style.display = 'none';
                pauseIcon.style.display = 'block';
                setTimeout(() => pauseIcon.style.display = 'none', 500);
            }
        });
    });
});

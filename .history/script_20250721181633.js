document.addEventListener('DOMContentLoaded', function () {
    const videoWrappers = document.querySelectorAll('.wpvr-video-wrapper');

    // Mute buttons per video
    videoWrappers.forEach(wrapper => {
        const video = wrapper.querySelector('video');
        const muteBtn = wrapper.querySelector('.wpvr-sound-toggle');

        // Initial state: muted
        video.muted = true;

        muteBtn.addEventListener('click', function () {
            video.muted = !video.muted;
            muteBtn.textContent = video.muted ? '🔇' : '🔊';
        });

        // Pause/Play on click with animation
        let animIcon = wrapper.querySelector('.wpvr-playpause-anim');

        video.addEventListener('click', () => {
            if (video.paused) {
                video.play();
                animIcon.textContent = '▶️';
            } else {
                video.pause();
                animIcon.textContent = '⏸️';
            }
            animIcon.classList.add('show');
            setTimeout(() => animIcon.classList.remove('show'), 500);
        });
    });

    // Scroll-based autoplay
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const video = entry.target.querySelector('video');
            if (entry.isIntersecting) {
                video.play();
            } else {
                video.pause();
            }
        });
    }, {
        threshold: 0.7
    });

    videoWrappers.forEach(wrapper => observer.observe(wrapper));
});

document.addEventListener('DOMContentLoaded', function () {
    const videoWrappers = document.querySelectorAll('.wpvr-video-wrapper');

    videoWrappers.forEach(wrapper => {
        const video = wrapper.querySelector('video');
        const muteBtn = wrapper.querySelector('.wpvr-sound-toggle');
        const animIcon = wrapper.querySelector('.wpvr-playpause-anim');

        video.muted = true;

        muteBtn.addEventListener('click', function () {
            video.muted = !video.muted;
            muteBtn.textContent = video.muted ? '🔇' : '🔊';
        });

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

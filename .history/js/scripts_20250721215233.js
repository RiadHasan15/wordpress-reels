document.addEventListener('DOMContentLoaded', function () {
    const videos = document.querySelectorAll('.bpr-video');
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.8 // trigger when 80% is visible
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const video = entry.target;

            if (entry.isIntersecting) {
                // Pause all other videos first
                videos.forEach(v => {
                    if (v !== video) {
                        v.pause();
                        v.muted = true;
                    }
                });

                // Play and unmute the visible video
                video.play().catch(() => { });
                video.muted = false;
            } else {
                // Pause and mute the non-visible video
                video.pause();
                video.muted = true;
            }
        });
    }, observerOptions);

    videos.forEach(video => {
        observer.observe(video);

        // Pause/Play on click
        video.addEventListener('click', function () {
            if (video.paused) {
                video.play();
                video.nextElementSibling?.classList.remove('show'); // Hide play icon
            } else {
                video.pause();
                video.nextElementSibling?.classList.add('show'); // Show pause icon
                setTimeout(() => {
                    video.nextElementSibling?.classList.remove('show');
                }, 800);
            }
        });
    });

    // Mute toggle button
    document.querySelectorAll('.bpr-mute-toggle').forEach(button => {
        button.addEventListener('click', function () {
            const video = this.closest('.bpr-video-wrapper').querySelector('video');

            // Toggle mute
            video.muted = !video.muted;

            // Optional: toggle icon or styling
            this.textContent = video.muted ? '🔇' : '🔊';
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

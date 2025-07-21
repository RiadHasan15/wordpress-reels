document.addEventListener('DOMContentLoaded', function () {
    const videos = document.querySelectorAll('.wpvr-reel video');
    let isMuted = true;
    let currentActive = null;

    // Set autoplay/muted/loop on all videos
    videos.forEach(video => {
        video.muted = isMuted;
        video.autoplay = true;
        video.playsInline = true;
        video.loop = true;
        video.play().catch(() => { });
    });

    // Create global mute/unmute toggle button
    const toggleButton = document.createElement('button');
    toggleButton.innerText = '🔇';
    toggleButton.className = 'global-mute-btn';
    toggleButton.addEventListener('click', function () {
        isMuted = !isMuted;
        toggleButton.innerText = isMuted ? '🔇' : '🔊';
        videos.forEach(video => {
            video.muted = isMuted;
        });
    });

    // Attach button to current visible video
    function attachButtonTo(video) {
        const parent = video.closest('.wpvr-reel');
        if (parent && !parent.contains(toggleButton)) {
            parent.appendChild(toggleButton);
        }
    }

    // Intersection Observer to detect visible video
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const video = entry.target;
            if (entry.isIntersecting) {
                currentActive = video;
                video.play().catch(() => { });
                attachButtonTo(video);
            } else {
                video.pause();
            }
        });
    }, {
        threshold: 0.75 // 75% of video must be visible
    });

    videos.forEach(video => {
        observer.observe(video);
    });
});

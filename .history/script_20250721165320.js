document.addEventListener('DOMContentLoaded', function () {
    const videos = document.querySelectorAll('.wpvr-reel video');
    let isMuted = true;
    let currentActive = null;

    // Create the floating mute/unmute button
    const toggleDiv = document.createElement('div');
    toggleDiv.className = 'wpvr-sound-toggle';
    toggleDiv.innerText = '🔇';

    toggleDiv.addEventListener('click', function () {
        isMuted = !isMuted;
        toggleDiv.innerText = isMuted ? '🔇' : '🔊';
        videos.forEach(video => {
            video.muted = isMuted;
        });
    });

    // Attach button to visible reel
    function attachButtonTo(video) {
        const wrapper = video.closest('.wpvr-reel');
        if (wrapper && !wrapper.contains(toggleDiv)) {
            wrapper.appendChild(toggleDiv);
        }
    }

    // Setup all videos
    videos.forEach(video => {
        video.muted = isMuted;
        video.autoplay = true;
        video.playsInline = true;
        video.loop = true;
        video.play().catch(() => { });
    });

    // Scroll detection
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
    }, { threshold: 0.75 });

    videos.forEach(video => observer.observe(video));
});

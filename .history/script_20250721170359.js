document.addEventListener('DOMContentLoaded', function () {
    const videos = document.querySelectorAll('.wpvr-reel video');
    let isMuted = true;

    // Initialize videos muted & autoplay
    videos.forEach(video => {
        video.muted = isMuted;
        video.autoplay = true;
        video.playsInline = true;
        video.loop = true;
        video.play().catch(() => { });
    });

    // Sound toggle button (single element reused)
    const toggleDiv = document.querySelector('.wpvr-sound-toggle');

    function toggleMute() {
        isMuted = !isMuted;
        toggleDiv.innerText = isMuted ? '🔇' : '🔊';
        videos.forEach(video => {
            video.muted = isMuted;
        });
    }

    toggleDiv.addEventListener('click', toggleMute);

    // Attach toggle button to currently visible video container
    function attachButtonTo(video) {
        const wrapper = video.closest('.wpvr-reel');
        if (wrapper && !wrapper.contains(toggleDiv)) {
            wrapper.appendChild(toggleDiv);
        }
    }

    // Intersection observer to handle play/pause & button attach
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const video = entry.target;
            if (entry.isIntersecting) {
                video.play().catch(() => { });
                attachButtonTo(video);
            } else {
                video.pause();
            }
        });
    }, { threshold: 0.75 });

    videos.forEach(video => observer.observe(video));
});

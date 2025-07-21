document.addEventListener('DOMContentLoaded', function () {
    const videos = document.querySelectorAll('.wpvr-reel video');
    let isMuted = true;
    let currentActive = null;

    // Setup videos initial state
    videos.forEach(video => {
        video.muted = isMuted;
        video.autoplay = true;
        video.playsInline = true;
        video.loop = true;
        video.play().catch(() => { });
    });

    // Get the single sound toggle button div (only one)
    const toggleDiv = document.querySelector('.wpvr-sound-toggle');

    // Mute toggle function
    function toggleMute() {
        isMuted = !isMuted;
        toggleDiv.innerText = isMuted ? '🔇' : '🔊';
        videos.forEach(video => {
            video.muted = isMuted;
        });
    }

    // Attach button click listener
    toggleDiv.addEventListener('click', toggleMute);

    // Attach button to currently visible reel
    function attachButtonTo(video) {
        const wrapper = video.closest('.wpvr-reel');
        if (wrapper && !wrapper.contains(toggleDiv)) {
            wrapper.appendChild(toggleDiv);
        }
    }

    // Intersection Observer for scroll autoplay & button attach
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

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

        // Create play/pause overlay element for each video
        const overlay = document.createElement('div');
        overlay.className = 'wpvr-play-pause-overlay';
        video.parentElement.appendChild(overlay);

        // Click video to toggle play/pause with animation
        video.addEventListener('click', () => {
            if (video.paused) {
                video.play().catch(() => { });
                showOverlay(overlay, '►'); // Play icon
            } else {
                video.pause();
                showOverlay(overlay, '❚❚'); // Pause icon
            }
        });
    });

    // Single mute toggle button
    const toggleDiv = document.querySelector('.wpvr-sound-toggle');

    // Update toggle button icon to reflect mute state
    function updateToggleIcon() {
        toggleDiv.innerText = isMuted ? '🔇' : '🔊';
    }

    function toggleMute() {
        isMuted = !isMuted;
        updateToggleIcon();
        // Mute/unmute all videos
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
            updateToggleIcon(); // Update icon when moved to new video
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

    // Show play/pause overlay icon briefly with animation
    function showOverlay(overlay, icon) {
        overlay.textContent = icon;
        overlay.classList.add('show');
        clearTimeout(overlay._timeout);
        overlay._timeout = setTimeout(() => {
            overlay.classList.remove('show');
        }, 700);
    }

    // Initial icon setup
    updateToggleIcon();
});

document.addEventListener('DOMContentLoaded', function () {
    const videos = document.querySelectorAll('.wpvr-reel video');
    let isMuted = true; // Default state

    // Create global mute/unmute button
    const toggleButton = document.createElement('button');
    toggleButton.innerText = '🔇';
    toggleButton.style.position = 'fixed';
    toggleButton.style.top = '20px';
    toggleButton.style.right = '20px';
    toggleButton.style.zIndex = '9999';
    toggleButton.style.background = 'rgba(0,0,0,0.5)';
    toggleButton.style.color = 'white';
    toggleButton.style.border = 'none';
    toggleButton.style.borderRadius = '50%';
    toggleButton.style.width = '50px';
    toggleButton.style.height = '50px';
    toggleButton.style.fontSize = '20px';
    toggleButton.style.cursor = 'pointer';
    document.body.appendChild(toggleButton);

    toggleButton.addEventListener('click', function () {
        isMuted = !isMuted;
        toggleButton.innerText = isMuted ? '🔇' : '🔊';
        // Apply mute state to all videos, except visible one (controlled separately)
        updateVideoAudioStates();
    });

    function updateVideoAudioStates() {
        videos.forEach(video => {
            if (video.classList.contains('active')) {
                video.muted = isMuted ? true : false;
                video.play().catch(() => { });
            } else {
                video.muted = true;
            }
        });
    }

    // Setup Intersection Observer
    const observer = new IntersectionObserver(
        entries => {
            entries.forEach(entry => {
                const video = entry.target;
                if (entry.isIntersecting) {
                    videos.forEach(v => v.classList.remove('active'));
                    video.classList.add('active');
                    updateVideoAudioStates();
                }
            });
        },
        {
            threshold: 0.75, // 75% of video must be visible to be considered "in frame"
        }
    );

    // Initialize videos
    videos.forEach(video => {
        video.autoplay = true;
        video.playsInline = true;
        video.loop = true;
        video.muted = true;
        video.play().catch(() => { });
        observer.observe(video);
    });
});

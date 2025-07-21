document.addEventListener("DOMContentLoaded", () => {
    const reels = document.querySelectorAll(".bpr-reel");

    // Intersection Observer: auto-play/pause based on visibility
    const obs = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            const vid = entry.target.querySelector("video");
            if (entry.isIntersecting) {
                vid.play().catch(() => { });
            } else {
                vid.pause();
            }
        });
    }, { threshold: 0.75 });

    reels.forEach(reel => {
        obs.observe(reel);

        const video = reel.querySelector("video");
        const muteBtn = reel.querySelector(".bpr-mute-toggle");
        const animIcon = reel.querySelector(".bpr-pp-anim");

        // Initialize: mute video
        video.muted = true;
        if (muteBtn) muteBtn.textContent = "🔇";

        // Mute/unmute toggle
        muteBtn.addEventListener("click", e => {
            e.stopPropagation();
            video.muted = !video.muted;
            muteBtn.textContent = video.muted ? "🔇" : "🔊";
        });

        // Pause/resume on video click
        video.addEventListener("click", e => {
            if (video.paused) {
                video.play().catch(() => { });
                showAnim(animIcon, "▶️");
            } else {
                video.pause();
                showAnim(animIcon, "⏸️‖");
            }
        });
    });

    // Show pause/play icon
    function showAnim(el, symbol) {
        el.textContent = symbol;
        el.style.display = "block";
        setTimeout(() => el.style.display = "none", 500);
    }
});

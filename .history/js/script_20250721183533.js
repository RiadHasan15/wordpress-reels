document.addEventListener("DOMContentLoaded", () => {
    const reels = document.querySelectorAll(".bpr-reel");

    reels.forEach(reel => {
        const video = reel.querySelector("video");
        const muteBtn = reel.querySelector(".bpr-mute-toggle");
        const anim = reel.querySelector(".bpr-pp-anim");

        // Mute toggle
        video.muted = true;
        muteBtn.addEventListener("click", () => {
            video.muted = !video.muted;
            muteBtn.textContent = video.muted ? "🔇" : "🔊";
        });

        // Pause/play on click
        reel.querySelector("video").addEventListener("click", () => {
            if (video.paused) {
                video.play();
                showAnim(anim, "▶️");
            } else {
                video.pause();
                showAnim(anim, "⏸️");
            }
        });
    });

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            const vid = entry.target.querySelector("video");
            if (entry.isIntersecting) vid.play();
            else vid.pause();
        });
    }, { threshold: 0.75 });

    reels.forEach(reel => observer.observe(reel));

    function showAnim(el, symbol) {
        el.textContent = symbol;
        el.style.display = "block";
        setTimeout(() => el.style.display = "none", 600);
    }
});

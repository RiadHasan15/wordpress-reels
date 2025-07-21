document.addEventListener('DOMContentLoaded', function () {

  // === Profile grid reel fullscreen modal ===
  const gridItems = document.querySelectorAll('.bpr-grid-item');
  const modal = document.getElementById('bpr-reel-modal');
  const modalContent = document.getElementById('bpr-reel-modal-content');
  const closeModal = document.getElementById('bpr-close-modal');

  gridItems.forEach(item => {
    item.addEventListener('click', function () {
      const videoSrc = this.getAttribute('data-video');
      modalContent.innerHTML = `
        <video src="${videoSrc}" autoplay loop playsinline muted></video>
      `;
      modal.style.display = 'flex';
    });
  });

  if (closeModal) {
    closeModal.addEventListener('click', function () {
      modal.style.display = 'none';
      modalContent.innerHTML = '';
    });
  }

  modal.addEventListener('click', function (e) {
    if (e.target === modal) {
      modal.style.display = 'none';
      modalContent.innerHTML = '';
    }
  });

  // === Vertical feed mute toggle button inside video frame ===
  const muteToggles = document.querySelectorAll('.bpr-mute-toggle');
  muteToggles.forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      const wrapper = btn.closest('.bpr-video-wrapper');
      if (!wrapper) return;

      const video = wrapper.querySelector('video');
      if (!video) return;

      if (video.muted) {
        video.muted = false;
        btn.textContent = '🔊';
      } else {
        video.muted = true;
        btn.textContent = '🔇';
      }
    });
  });

  // === Vertical feed pause/resume on video click with animation ===
  const videoWrappers = document.querySelectorAll('.bpr-video-wrapper');

  videoWrappers.forEach(wrapper => {
    const video = wrapper.querySelector('video');
    const pauseIcon = wrapper.querySelector('.bpr-pause-icon');
    const playIcon = wrapper.querySelector('.bpr-play-icon');

    if (!video) return;

    wrapper.addEventListener('click', function () {
      if (video.paused) {
        video.play();
        // Show play icon briefly
        playIcon.classList.add('show');
        setTimeout(() => playIcon.classList.remove('show'), 600);
      } else {
        video.pause();
        // Show pause icon briefly
        pauseIcon.classList.add('show');
        setTimeout(() => pauseIcon.classList.remove('show'), 600);
      }
    });
  });

  // === Optional: Global mute/unmute all videos (extend if you want) ===
  // You can implement this by adding a global toggle button and then toggling
  // mute state on all videos in feed and modal accordingly.

});

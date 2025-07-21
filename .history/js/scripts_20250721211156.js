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
      // Sync modal video mute state with global mute
      setModalVideoMuteState();
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

  // === Global mute state ===
  let isGloballyMuted = true;

  // === Vertical feed videos and mute toggles ===
  const videoWrappers = document.querySelectorAll('.bpr-video-wrapper');

  // Set mute state for all feed videos and their toggle buttons
  function setAllVideosMuteState(muted) {
    videoWrappers.forEach(wrapper => {
      const video = wrapper.querySelector('video');
      const btn = wrapper.querySelector('.bpr-mute-toggle');
      if (!video || !btn) return;
      video.muted = muted;
      btn.textContent = muted ? '🔇' : '🔊';
    });
    setModalVideoMuteState();
  }

  // Set modal video mute state according to global mute
  function setModalVideoMuteState() {
    if (!modalContent) return;
    const modalVideo = modalContent.querySelector('video');
    if (!modalVideo) return;
    modalVideo.muted = isGloballyMuted;
  }

  // Initialize all videos mute on page load
  setAllVideosMuteState(isGloballyMuted);

  // Mute toggle button click handler per video
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
        isGloballyMuted = false; // User unmuted one video → global mute off
      } else {
        video.muted = true;
        btn.textContent = '🔇';

        // Check if all videos are muted → set global mute true
        const allMuted = Array.from(videoWrappers).every(w => {
          const v = w.querySelector('video');
          return v && v.muted;
        });
        isGloballyMuted = allMuted;
      }
      setModalVideoMuteState();
      updateGlobalMuteButton();
    });
  });

  // === Pause/resume on video click with animation ===
  videoWrappers.forEach(wrapper => {
    const video = wrapper.querySelector('video');
    const pauseIcon = wrapper.querySelector('.bpr-pause-icon');
    const playIcon = wrapper.querySelector('.bpr-play-icon');
    if (!video) return;

    wrapper.addEventListener('click', function () {
      if (video.paused) {
        video.play();
        playIcon.classList.add('show');
        setTimeout(() => playIcon.classList.remove('show'), 600);
      } else {
        video.pause();
        pauseIcon.classList.add('show');
        setTimeout(() => pauseIcon.classList.remove('show'), 600);
      }
    });
  });

  // === Global mute/unmute all toggle button ===
  const feed = document.querySelector('.bpr-feed');
  let globalMuteBtn;

  function createGlobalMuteButton() {
    if (!feed) return;

    globalMuteBtn = document.createElement('div');
    globalMuteBtn.className = 'bpr-global-mute-btn';
    globalMuteBtn.textContent = isGloballyMuted ? '🔇' : '🔊';
    globalMuteBtn.title = 'Mute/Unmute All';

    // Style it inline or you can move to CSS file
    globalMuteBtn.style.position = 'fixed';
    globalMuteBtn.style.top = '80px';
    globalMuteBtn.style.right = '20px';
    globalMuteBtn.style.zIndex = '9999';
    globalMuteBtn.style.cursor = 'pointer';
    globalMuteBtn.style.fontSize = '28px';
    globalMuteBtn.style.background = 'rgba(0,0,0,0.4)';
    globalMuteBtn.style.color = '#fff';
    globalMuteBtn.style.padding = '6px 10px';
    globalMuteBtn.style.borderRadius = '20px';
    globalMuteBtn.style.userSelect = 'none';

    document.body.appendChild(globalMuteBtn);

    globalMuteBtn.addEventListener('click', () => {
      isGloballyMuted = !isGloballyMuted;
      globalMuteBtn.textContent = isGloballyMuted ? '🔇' : '🔊';
      setAllVideosMuteState(isGloballyMuted);
    });
  }

  function updateGlobalMuteButton() {
    if (!globalMuteBtn) return;
    globalMuteBtn.textContent = isGloballyMuted ? '🔇' : '🔊';
  }

  createGlobalMuteButton();

});

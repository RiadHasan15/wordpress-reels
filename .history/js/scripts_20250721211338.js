document.addEventListener('DOMContentLoaded', function () {

  const feed = document.querySelector('.bpr-feed');
  const modal = document.getElementById('bpr-reel-modal');
  const modalContent = document.getElementById('bpr-reel-modal-content');
  const closeModal = document.getElementById('bpr-close-modal');
  let isGloballyMuted = true;
  let globalMuteBtn;

  // Helper: set mute state and icon for a single video and button
  function setVideoMute(video, btn, muted) {
    if (!video || !btn) return;
    video.muted = muted;
    btn.textContent = muted ? '🔇' : '🔊';
  }

  // Set mute for all feed videos and buttons
  function setAllVideosMuteState(muted) {
    const videoWrappers = document.querySelectorAll('.bpr-video-wrapper');
    videoWrappers.forEach(wrapper => {
      const video = wrapper.querySelector('video');
      const btn = wrapper.querySelector('.bpr-mute-toggle');
      setVideoMute(video, btn, muted);
    });
    setModalVideoMuteState();
  }

  // Set modal video mute according to global mute
  function setModalVideoMuteState() {
    if (!modalContent) return;
    const modalVideo = modalContent.querySelector('video');
    if (!modalVideo) return;
    modalVideo.muted = isGloballyMuted;
  }

  // Create and add global mute/unmute button
  function createGlobalMuteButton() {
    if (!feed) return;

    globalMuteBtn = document.createElement('div');
    globalMuteBtn.className = 'bpr-global-mute-btn';
    globalMuteBtn.textContent = isGloballyMuted ? '🔇' : '🔊';
    globalMuteBtn.title = 'Mute/Unmute All';

    Object.assign(globalMuteBtn.style, {
      position: 'fixed',
      top: '80px',
      right: '20px',
      zIndex: '9999',
      cursor: 'pointer',
      fontSize: '28px',
      background: 'rgba(0,0,0,0.4)',
      color: '#fff',
      padding: '6px 10px',
      borderRadius: '20px',
      userSelect: 'none',
    });

    document.body.appendChild(globalMuteBtn);

    globalMuteBtn.addEventListener('click', () => {
      isGloballyMuted = !isGloballyMuted;
      globalMuteBtn.textContent = isGloballyMuted ? '🔇' : '🔊';
      setAllVideosMuteState(isGloballyMuted);
    });
  }

  // Event delegation for feed container for mute toggle buttons and pause/resume video click
  if (feed) {
    feed.addEventListener('click', function (e) {
      const muteBtn = e.target.closest('.bpr-mute-toggle');
      if (muteBtn) {
        e.stopPropagation();
        const wrapper = muteBtn.closest('.bpr-video-wrapper');
        if (!wrapper) return;
        const video = wrapper.querySelector('video');
        if (!video) return;

        // Toggle mute on that video
        if (video.muted) {
          video.muted = false;
          muteBtn.textContent = '🔊';
          isGloballyMuted = false; // user unmuted one video
        } else {
          video.muted = true;
          muteBtn.textContent = '🔇';

          // Check if all videos muted to set global mute state
          const allMuted = Array.from(feed.querySelectorAll('video')).every(v => v.muted);
          isGloballyMuted = allMuted;
        }
        setModalVideoMuteState();
        if (globalMuteBtn) {
          globalMuteBtn.textContent = isGloballyMuted ? '🔇' : '🔊';
        }
        return;
      }

      // If clicked on video wrapper (excluding mute button), toggle pause/resume with animation
      const videoWrapper = e.target.closest('.bpr-video-wrapper');
      if (videoWrapper) {
        const video = videoWrapper.querySelector('video');
        const pauseIcon = videoWrapper.querySelector('.bpr-pause-icon');
        const playIcon = videoWrapper.querySelector('.bpr-play-icon');
        if (!video) return;

        if (video.paused) {
          video.play();
          if (playIcon) {
            playIcon.classList.add('show');
            setTimeout(() => playIcon.classList.remove('show'), 600);
          }
        } else {
          video.pause();
          if (pauseIcon) {
            pauseIcon.classList.add('show');
            setTimeout(() => pauseIcon.classList.remove('show'), 600);
          }
        }
      }
    });
  }

  // Modal reel open/close logic
  if (modal && modalContent && gridItems) {
    gridItems.forEach(item => {
      item.addEventListener('click', function () {
        const videoSrc = this.getAttribute('data-video');
        modalContent.innerHTML = `
          <video src="${videoSrc}" autoplay loop playsinline muted></video>
        `;
        modal.style.display = 'flex';
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
  }

  // Initialize global mute button and mute state on page load
  createGlobalMuteButton();
  setAllVideosMuteState(isGloballyMuted);

});

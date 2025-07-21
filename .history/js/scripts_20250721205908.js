jQuery(function ($) {
    // Mute toggle and pause/play
    let muteAll = bprSettings.default_muted === '1';
    function applyMute() {
        $('.bpr-video').each(function () {
            this.muted = muteAll;
            $(this).siblings('.bpr-mute-toggle').text(muteAll ? '🔇' : '🔊');
        });
    }
    applyMute();
    $('.bpr-mute-toggle').on('click', function () {
        muteAll = !muteAll; applyMute();
    });
    $('.bpr-video').on('click', function () {
        const video = this;
        const paused = video.paused;
        video[paused ? 'play' : 'pause']();
        $(this).closest('.bpr-video-wrapper').find(paused ? '.bpr-play-icon' : '.bpr-pause-icon')
            .addClass('show').delay(700).queue(function (next) { $(this).removeClass('show'); next(); });
    });

    // Grid hover to preview
    $('.bpr-grid-wrapper').on('mouseenter', '.bpr-grid-item', function () {
        this.querySelector('video').play();
    }).on('mouseleave', '.bpr-grid-item', function () {
        let v = this.querySelector('video');
        v.pause(); v.currentTime = 0;
    });

    // Grid click for modal playback
    $('.bpr-grid-item').on('click', function () {
        const src = $(this).data('video');
        const modal = $('.bpr-modal').addClass('active');
        $('#bpr-full-video').attr('src', src).prop('muted', false).get(0).play();
    });

    $('.bpr-close, .bpr-modal').on('click', function (e) {
        if (e.target !== this) return;
        $('#bpr-full-video').get(0).pause().removeAttribute('src');
        $('.bpr-modal').removeClass('active');
    });

    $('#bpr-full-video').on('click', function (e) { e.stopPropagation(); });
});





document.addEventListener('DOMContentLoaded', function () {
  const gridItems = document.querySelectorAll('.bpr-grid-item');
  const modal = document.querySelector('#bpr-reel-modal');
  const modalContent = document.querySelector('#bpr-reel-modal-content');
  const closeModal = document.querySelector('#bpr-close-modal');

  gridItems.forEach(item => {
    item.addEventListener('click', function () {
      const videoSrc = this.getAttribute('data-video');
      modalContent.innerHTML = `
        <video src="${videoSrc}" autoplay loop playsinline></video>
      `;
      modal.style.display = 'flex';
    });
  });

  // ✅ Fix: Close modal on clicking close icon
  if (closeModal) {
    closeModal.addEventListener('click', function () {
      modal.style.display = 'none';
      modalContent.innerHTML = '';
    });
  }
});

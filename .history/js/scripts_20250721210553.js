document.addEventListener('DOMContentLoaded', function () {
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

  // Optional: Pause/Play animation and mute toggle can be added here per your other features
});

document.addEventListener('DOMContentLoaded', function () {
    const videos = document.querySelectorAll('.bpr-video');
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.8
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const video = entry.target;
            const wrapper = video.closest('.bpr-video-wrapper');
            const muteBtn = wrapper.querySelector('.bpr-mute-toggle');

            if (entry.isIntersecting) {
                // Pause and mute all other videos
                videos.forEach(v => {
                    if (v !== video) {
                        v.pause();
                        v.muted = true;

                        const otherWrapper = v.closest('.bpr-video-wrapper');
                        const otherMuteBtn = otherWrapper?.querySelector('.bpr-mute-toggle');
                        if (otherMuteBtn) {
                            otherMuteBtn.textContent = '🔇';
                            otherMuteBtn.dataset.userMuted = 'false'; // Reset other buttons
                        }
                    }
                });

                // Play this video
                video.play().catch(() => { });

                // Only unmute if user hasn't manually muted
                if (muteBtn?.dataset.userMuted !== 'true') {
                    video.muted = false;
                    muteBtn.textContent = '🔊';
                }

            } else {
                // Pause and mute if out of view
                video.pause();
                video.muted = true;
                if (muteBtn) {
                    muteBtn.textContent = '🔇';
                }
            }
        });
    }, observerOptions);

    videos.forEach(video => {
        observer.observe(video);
        video.setAttribute('data-observer-added', 'true');

        // Pause/Play on click
        video.addEventListener('click', function () {
            const icon = this.parentElement.querySelector('.bpr-play-icon, .bpr-pause-icon');
            if (video.paused) {
                video.play();
                icon?.classList.remove('show');
            } else {
                video.pause();
                icon?.classList.add('show');
                setTimeout(() => {
                    icon?.classList.remove('show');
                }, 800);
            }
        });
    });

    // Mute toggle button
    document.querySelectorAll('.bpr-mute-toggle').forEach(button => {
        button.dataset.userMuted = 'false'; // Set default
        button.dataset.listenerAdded = 'true'; // Mark as processed
        button.addEventListener('click', function () {
            const video = this.closest('.bpr-video-wrapper').querySelector('video');
            if (video) {
                video.muted = !video.muted;
                this.textContent = video.muted ? '🔇' : '🔊';
                this.dataset.userMuted = video.muted ? 'true' : 'false';
            }
        });
    });

    // Grid modal functionality
    const modal = document.getElementById('bpr-reel-modal');
    const modalVideo = document.getElementById('bpr-modal-player');
    const modalClose = document.querySelector('.bpr-modal-close');
    const modalOverlay = document.querySelector('.bpr-modal-overlay');
    const modalTitle = document.querySelector('.bpr-modal-title');
    const modalDescription = document.querySelector('.bpr-modal-description');
    const activityStream = document.getElementById('bpr-activity-stream');

    // Grid item click handlers
    document.querySelectorAll('.bpr-grid-item').forEach(item => {
        // Preview on hover
        item.addEventListener('mouseenter', function() {
            const video = this.querySelector('video');
            if (video) {
                video.play().catch(() => {});
            }
        });

        item.addEventListener('mouseleave', function() {
            const video = this.querySelector('video');
            if (video) {
                video.pause();
                video.currentTime = 0;
            }
        });

        // Open modal on click
        item.addEventListener('click', function() {
            const videoUrl = this.dataset.video;
            const title = this.dataset.title;
            const description = this.dataset.description;
            const postId = this.dataset.postId;

            if (modal && modalVideo) {
                modalVideo.src = videoUrl;
                modalTitle.textContent = title || '';
                modalDescription.textContent = description || '';
                
                modal.classList.add('active');
                modalVideo.play().catch(() => {});
                
                // Load BuddyPress activity
                loadReelActivity(postId);
            }
        });
    });

    // Close modal handlers
    if (modalClose) {
        modalClose.addEventListener('click', closeModal);
    }
    
    if (modalOverlay) {
        modalOverlay.addEventListener('click', closeModal);
    }

    // ESC key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal?.classList.contains('active')) {
            closeModal();
        }
    });

    function closeModal() {
        if (modal && modalVideo) {
            modal.classList.remove('active');
            modalVideo.pause();
            modalVideo.src = '';
        }
    }

    function loadReelActivity(postId) {
        if (!activityStream) return;
        
        activityStream.innerHTML = '<div class="loading">Loading activity...</div>';
        
        const formData = new FormData();
        formData.append('action', 'bpr_get_reel_activity');
        formData.append('post_id', postId);
        formData.append('nonce', bpr_ajax.nonce);
        
        fetch(bpr_ajax.url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                activityStream.innerHTML = data.data.html;
            } else {
                activityStream.innerHTML = '<div class="error">Failed to load activity</div>';
            }
        })
        .catch(error => {
            console.error('Error loading activity:', error);
            activityStream.innerHTML = '<div class="error">Error loading activity</div>';
        });
    }
});

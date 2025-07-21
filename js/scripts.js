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
        const video = item.querySelector('video');
        const loadingOverlay = item.querySelector('.bpr-loading-overlay');
        
        // Handle video loading states
        if (video) {
            video.addEventListener('loadstart', function() {
                if (loadingOverlay) loadingOverlay.style.display = 'flex';
            });
            
            video.addEventListener('loadeddata', function() {
                if (loadingOverlay) loadingOverlay.style.display = 'none';
            });
            
            video.addEventListener('error', function() {
                if (loadingOverlay) {
                    loadingOverlay.innerHTML = '<div style="color: white; text-align: center;">⚠️<br>Failed to load</div>';
                }
                console.error('Video failed to load:', this.src);
            });
        }
        
        // Preview on hover
        item.addEventListener('mouseenter', function() {
            if (video && video.readyState >= 2) { // HAVE_CURRENT_DATA
                video.play().catch(() => {});
            }
        });

        item.addEventListener('mouseleave', function() {
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
            const authorName = this.dataset.authorName;
            const authorUrl = this.dataset.authorUrl;
            const authorId = this.dataset.authorId;

            if (modal && modalVideo) {
                // Reset modal state
                modalVideo.pause();
                modalVideo.currentTime = 0;
                modalVideo.src = '';
                
                // Update modal content
                modalVideo.src = videoUrl;
                modalTitle.textContent = title || '';
                modalDescription.textContent = description || '';
                
                // Update author info
                const authorLink = document.querySelector('.bpr-author-name a');
                const authorAvatar = document.querySelector('.bpr-author-avatar');
                
                if (authorLink) {
                    authorLink.textContent = authorName || 'Unknown User';
                    authorLink.href = authorUrl || '#';
                }
                
                // Load avatar if BuddyPress is available
                if (authorAvatar && authorId) {
                    authorAvatar.innerHTML = `<img src="${getGravatarUrl(authorId)}" alt="${authorName}" class="avatar" width="40" height="40" style="border-radius: 50%; border: 2px solid var(--bpr-accent);">`;
                }
                
                modal.classList.add('active');
                
                // Handle modal video loading
                modalVideo.addEventListener('loadeddata', function() {
                    this.play().catch(error => {
                        console.error('Modal video play failed:', error);
                    });
                }, { once: true });
                
                modalVideo.addEventListener('error', function() {
                    console.error('Modal video failed to load:', this.src);
                    if (modalDescription) {
                        modalDescription.innerHTML = '<div class="error">Video failed to load</div>';
                    }
                }, { once: true });
                
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

    function getGravatarUrl(userId, size = 40) {
        // Simple gravatar URL generation - in real implementation, you'd get the email
        return `https://www.gravatar.com/avatar/${userId}?s=${size}&d=mp&r=g`;
    }

    function loadReelActivity(postId) {
        if (!activityStream) return;
        
        activityStream.innerHTML = '<div class="loading">Loading activity...</div>';
        
        // Check if bprSettings is available
        if (typeof bprSettings === 'undefined') {
            activityStream.innerHTML = '<div class="error">Configuration error</div>';
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'bpr_get_reel_activity');
        formData.append('post_id', postId);
        formData.append('nonce', bprSettings.nonce);
        
        fetch(bprSettings.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                activityStream.innerHTML = data.data.html;
            } else {
                activityStream.innerHTML = '<div class="error">Failed to load activity: ' + (data.data || 'Unknown error') + '</div>';
            }
        })
        .catch(error => {
            console.error('Error loading activity:', error);
            activityStream.innerHTML = '<div class="error">Error loading activity. Please try again.</div>';
        });
    }
});

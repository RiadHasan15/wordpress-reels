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
            const muteBtn = wrapper?.querySelector('.bpr-mute-toggle');

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
                video.play().catch(e => {
                    console.log('Autoplay prevented:', e);
                });

                // Only unmute if user hasn't manually muted
                if (muteBtn && muteBtn.dataset.userMuted !== 'true') {
                    video.muted = false;
                    muteBtn.textContent = '🔊';
                } else if (muteBtn) {
                    video.muted = true;
                    muteBtn.textContent = '🔇';
                }
                
                // Add playing class for styling
                wrapper?.classList.add('bpr-playing');

            } else {
                // Pause and mute if out of view
                video.pause();
                video.muted = true;
                if (muteBtn) {
                    muteBtn.textContent = '🔇';
                }
                
                // Remove playing class
                wrapper?.classList.remove('bpr-playing');
            }
        });
    }, observerOptions);

    videos.forEach(video => {
        observer.observe(video);

        // Error handling for video loading issues
        video.addEventListener('error', function () {
            console.error('Video loading error:', this.src);
            const wrapper = this.closest('.bpr-video-wrapper');
            if (wrapper) {
                wrapper.innerHTML = '<div class="bpr-video-error"><p>Video could not be loaded</p></div>';
            }
        });

        // Pause/Play on click
        video.addEventListener('click', function () {
            const wrapper = this.closest('.bpr-video-wrapper');
            const playIcon = wrapper?.querySelector('.bpr-play-icon');
            const pauseIcon = wrapper?.querySelector('.bpr-pause-icon');
            
            if (video.paused) {
                video.play().catch(e => {
                    console.log('Play prevented:', e);
                });
                playIcon?.classList.remove('show');
                pauseIcon?.classList.remove('show');
            } else {
                video.pause();
                pauseIcon?.classList.add('show');
                setTimeout(() => {
                    pauseIcon?.classList.remove('show');
                }, 800);
            }
        });

        // Add click handler for opening modal
        video.addEventListener('dblclick', function () {
            openModal(this);
        });

        // Add keyboard accessibility
        video.addEventListener('keydown', function (e) {
            if (e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                this.click();
            }
        });
    });

    // Mute toggle button functionality (both grid and modal)
    function setupMuteToggle(button) {
        // Ensure default state is set
        if (!button.dataset.userMuted) {
            button.dataset.userMuted = 'false';
        }
        
        button.addEventListener('click', function (e) {
            e.stopPropagation(); // Prevent video click events
            
            let video;
            if (this.classList.contains('bpr-modal-mute-toggle')) {
                // Modal mute toggle
                video = document.getElementById('bpr-full-video');
            } else {
                // Grid mute toggle
                const wrapper = this.closest('.bpr-video-wrapper');
                video = wrapper?.querySelector('video');
            }
            
            if (video) {
                video.muted = !video.muted;
                this.textContent = video.muted ? '🔇' : '🔊';
                this.dataset.userMuted = video.muted ? 'true' : 'false';
                
                // Add visual feedback
                this.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            }
        });
    }

    // Setup existing mute toggles
    document.querySelectorAll('.bpr-mute-toggle, .bpr-modal-mute-toggle').forEach(setupMuteToggle);

    // Modal functionality
    function openModal(sourceVideo) {
        const modal = document.querySelector('.bpr-modal');
        const fullVideo = document.getElementById('bpr-full-video');
        const modalMuteBtn = document.querySelector('.bpr-modal-mute-toggle');
        
        if (!modal || !fullVideo || !sourceVideo) return;
        
        const wrapper = sourceVideo.closest('.bpr-video-wrapper');
        const muteBtn = wrapper?.querySelector('.bpr-mute-toggle');
        const videoSrc = sourceVideo.querySelector('source')?.src || sourceVideo.src;
        
        if (videoSrc) {
            // Set video source and preserve mute state
            fullVideo.src = videoSrc;
            fullVideo.muted = sourceVideo.muted;
            
            // Update modal mute button to match source video state
            if (modalMuteBtn) {
                modalMuteBtn.textContent = sourceVideo.muted ? '🔇' : '🔊';
                modalMuteBtn.dataset.userMuted = sourceVideo.muted ? 'true' : 'false';
            }
            
            // Get post info
            const postId = wrapper?.dataset.postId;
            const titleElement = wrapper?.querySelector('.bpr-title');
            const descElement = wrapper?.querySelector('.bpr-description');
            
            // Update modal info
            const modalTitle = modal.querySelector('.bpr-modal-title');
            const modalDesc = modal.querySelector('.bpr-modal-description');
            
            if (modalTitle && titleElement) {
                modalTitle.textContent = titleElement.textContent;
            }
            if (modalDesc && descElement) {
                modalDesc.textContent = descElement.textContent;
            }
            
            // Show modal
            modal.classList.add('active');
            fullVideo.play().catch(e => {
                console.log('Modal video play prevented:', e);
            });
        }
    }

    // Modal close functionality
    const closeBtn = document.querySelector('.bpr-close');
    const modal = document.querySelector('.bpr-modal');
    const fullVideo = document.getElementById('bpr-full-video');
    
    if (closeBtn && modal && fullVideo) {
        closeBtn.addEventListener('click', function () {
            modal.classList.remove('active');
            fullVideo.pause();
            fullVideo.src = ''; // Clear source to stop loading
        });
        
        // Close modal when clicking outside video
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.remove('active');
                fullVideo.pause();
                fullVideo.src = '';
            }
        });
        
        // Prevent video clicks from closing modal
        fullVideo.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function (e) {
        const modal = document.querySelector('.bpr-modal');
        const fullVideo = document.getElementById('bpr-full-video');
        
        if (modal?.classList.contains('active') && fullVideo) {
            switch (e.key) {
                case 'Escape':
                    modal.classList.remove('active');
                    fullVideo.pause();
                    fullVideo.src = '';
                    break;
                case ' ':
                case 'k':
                    e.preventDefault();
                    if (fullVideo.paused) {
                        fullVideo.play();
                    } else {
                        fullVideo.pause();
                    }
                    break;
                case 'm':
                    e.preventDefault();
                    fullVideo.muted = !fullVideo.muted;
                    break;
            }
        }
    });
});

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

// Modern Grid View JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeModernGrid();
});

function initializeModernGrid() {
    // Initialize grid cards
    const gridCards = document.querySelectorAll('.bpr-grid-card');
    const gridModal = document.querySelector('.bpr-grid-modal');
    
    if (!gridCards.length || !gridModal) return;
    
    let currentVideoIndex = 0;
    let allVideos = Array.from(gridCards);
    
    // Calculate video durations
    calculateVideoDurations();
    
    // Setup grid card interactions
    gridCards.forEach((card, index) => {
        setupGridCard(card, index);
    });
    
    // Setup modal functionality
    setupGridModal();
    
    // Setup load more functionality
    setupLoadMore();
    
    function calculateVideoDurations() {
        const durationBadges = document.querySelectorAll('.bpr-duration-badge');
        
        durationBadges.forEach(badge => {
            const videoUrl = badge.dataset.videoUrl;
            if (videoUrl) {
                const video = document.createElement('video');
                video.preload = 'metadata';
                
                video.addEventListener('loadedmetadata', function() {
                    const duration = Math.round(this.duration);
                    const minutes = Math.floor(duration / 60);
                    const seconds = duration % 60;
                    badge.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                });
                
                video.src = videoUrl;
            }
        });
    }
    
    function setupGridCard(card, index) {
        const video = card.querySelector('.bpr-grid-video');
        
        // Hover to preview
        card.addEventListener('mouseenter', function() {
            if (video && !video.playing) {
                video.play().catch(() => {
                    // Silent fail for autoplay restrictions
                });
            }
        });
        
        card.addEventListener('mouseleave', function() {
            if (video) {
                video.pause();
                video.currentTime = 0;
            }
        });
        
        // Click to open modal
        card.addEventListener('click', function() {
            currentVideoIndex = index;
            openGridModal(card);
        });
        
        // Error handling
        if (video) {
            video.addEventListener('error', function() {
                console.error('Grid video loading error:', this.src);
                const cardMedia = card.querySelector('.bpr-card-media');
                if (cardMedia) {
                    cardMedia.innerHTML = `
                        <div class="bpr-video-error">
                            <p>Video unavailable</p>
                        </div>
                    `;
                }
            });
        }
    }
    
    function setupGridModal() {
        const modalClose = gridModal.querySelector('.bpr-modal-close');
        const modalBackdrop = gridModal.querySelector('.bpr-modal-backdrop');
        const modalVideo = gridModal.querySelector('.bpr-modal-video');
        const prevBtn = gridModal.querySelector('.bpr-prev-btn');
        const nextBtn = gridModal.querySelector('.bpr-next-btn');
        const muteToggle = gridModal.querySelector('.bpr-modal-mute-toggle');
        
        // Close modal events
        if (modalClose) {
            modalClose.addEventListener('click', closeGridModal);
        }
        
        if (modalBackdrop) {
            modalBackdrop.addEventListener('click', closeGridModal);
        }
        
        // Navigation
        if (prevBtn) {
            prevBtn.addEventListener('click', () => navigateModal(-1));
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => navigateModal(1));
        }
        
        // Mute toggle
        if (muteToggle) {
            muteToggle.addEventListener('click', function() {
                if (modalVideo) {
                    modalVideo.muted = !modalVideo.muted;
                    this.textContent = modalVideo.muted ? '🔇' : '🔊';
                    this.dataset.userMuted = modalVideo.muted ? 'true' : 'false';
                    
                    // Visual feedback
                    this.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                }
            });
        }
        
        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (!gridModal.classList.contains('active')) return;
            
            switch(e.key) {
                case 'Escape':
                    closeGridModal();
                    break;
                case 'ArrowLeft':
                    e.preventDefault();
                    navigateModal(-1);
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    navigateModal(1);
                    break;
                case ' ':
                case 'k':
                    e.preventDefault();
                    if (modalVideo) {
                        if (modalVideo.paused) {
                            modalVideo.play();
                        } else {
                            modalVideo.pause();
                        }
                    }
                    break;
                case 'm':
                    e.preventDefault();
                    if (muteToggle) {
                        muteToggle.click();
                    }
                    break;
            }
        });
    }
    
    function openGridModal(card) {
        const modalVideo = gridModal.querySelector('.bpr-modal-video');
        const videoUrl = card.dataset.videoUrl;
        
        if (!modalVideo || !videoUrl) return;
        
        // Set video source
        modalVideo.src = videoUrl;
        
        // Update modal content
        updateModalContent(card);
        
        // Update navigation buttons
        updateNavigationButtons();
        
        // Show modal
        gridModal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Play video
        modalVideo.play().catch(e => {
            console.log('Modal video autoplay prevented:', e);
        });
    }
    
    function closeGridModal() {
        const modalVideo = gridModal.querySelector('.bpr-modal-video');
        
        gridModal.classList.remove('active');
        document.body.style.overflow = '';
        
        if (modalVideo) {
            modalVideo.pause();
            modalVideo.src = '';
        }
    }
    
    function navigateModal(direction) {
        const newIndex = currentVideoIndex + direction;
        
        if (newIndex >= 0 && newIndex < allVideos.length) {
            currentVideoIndex = newIndex;
            openGridModal(allVideos[currentVideoIndex]);
        }
    }
    
    function updateModalContent(card) {
        // Update user info
        const username = gridModal.querySelector('.bpr-username');
        const postDate = gridModal.querySelector('.bpr-post-date');
        const userAvatar = gridModal.querySelector('.bpr-user-avatar');
        
        if (username) username.textContent = card.dataset.author || 'Unknown User';
        if (postDate) postDate.textContent = card.querySelector('.bpr-card-date')?.textContent || '';
        
        // Update content
        const contentTitle = gridModal.querySelector('.bpr-content-title');
        const contentText = gridModal.querySelector('.bpr-content-text');
        
        if (contentTitle) contentTitle.textContent = card.dataset.title || 'Untitled';
        if (contentText) {
            const description = card.dataset.description || 'No description available';
            contentText.textContent = description.length > 200 ? 
                description.substring(0, 200) + '...' : description;
        }
        
        // Update stats
        const viewsValue = gridModal.querySelector('[data-stat="views"]');
        const likesValue = gridModal.querySelector('[data-stat="likes"]');
        
        if (viewsValue) viewsValue.textContent = bpr_format_number_js(card.dataset.views || 0);
        if (likesValue) likesValue.textContent = bpr_format_number_js(card.dataset.likes || 0);
    }
    
    function updateNavigationButtons() {
        const prevBtn = gridModal.querySelector('.bpr-prev-btn');
        const nextBtn = gridModal.querySelector('.bpr-next-btn');
        
        if (prevBtn) {
            prevBtn.disabled = currentVideoIndex === 0;
        }
        
        if (nextBtn) {
            nextBtn.disabled = currentVideoIndex === allVideos.length - 1;
        }
    }
    
    function setupLoadMore() {
        const loadMoreBtn = document.querySelector('.bpr-load-more-btn');
        
        if (!loadMoreBtn) return;
        
        loadMoreBtn.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const loaded = parseInt(this.dataset.loaded) || 0;
            
            if (!userId) return;
            
            // Disable button and show loading
            this.disabled = true;
            this.innerHTML = '<span>Loading...</span>';
            
            // AJAX call to load more videos
            fetch(window.location.origin + '/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'bpr_load_more_grid',
                    user_id: userId,
                    offset: loaded,
                    limit: 12
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.html) {
                    // Add new cards to grid
                    const gridContainer = document.querySelector('.bpr-grid-container');
                    if (gridContainer) {
                        gridContainer.insertAdjacentHTML('beforeend', data.data.html);
                        
                        // Update loaded count
                        const newLoaded = loaded + data.data.count;
                        this.dataset.loaded = newLoaded;
                        
                        // Reinitialize new cards
                        const newCards = gridContainer.querySelectorAll('.bpr-grid-card:not([data-initialized])');
                        newCards.forEach((card, index) => {
                            card.dataset.initialized = 'true';
                            setupGridCard(card, allVideos.length + index);
                        });
                        
                        // Update allVideos array
                        allVideos = Array.from(document.querySelectorAll('.bpr-grid-card'));
                        
                        // Calculate durations for new videos
                        calculateVideoDurations();
                        
                        // Hide button if no more videos
                        if (!data.data.has_more) {
                            this.style.display = 'none';
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Load more error:', error);
            })
            .finally(() => {
                // Re-enable button
                this.disabled = false;
                this.innerHTML = `
                    <span>Load More Reels</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M19 13H13V19H11V13H5V11H11V5H13V11H19V13Z" fill="currentColor"/>
                    </svg>
                `;
            });
        });
    }
}

// Helper function for number formatting (JavaScript version)
function bpr_format_number_js(number) {
    const num = parseInt(number) || 0;
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
}

document.addEventListener('DOMContentLoaded', function () {
    console.log('BuddyPress Reels initialized');

    // Initialize all functionality
    initializeVideoControls();
    initializeGridFunctionality();
    initializeBuddyPressFunctionality();

    // Instagram-style video controls
    function initializeVideoControls() {
        const videos = document.querySelectorAll('.bpr-video');
        let globalMuted = true; // Start muted like Instagram
        
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.6 // Trigger when 60% visible
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const video = entry.target;
                const wrapper = video.closest('.bpr-video-wrapper');
                const pauseOverlay = wrapper.querySelector('.bpr-pause-overlay');

                if (entry.isIntersecting) {
                    // Pause all other videos first
                    videos.forEach(v => {
                        if (v !== video) {
                            v.pause();
                            const otherWrapper = v.closest('.bpr-video-wrapper');
                            const otherOverlay = otherWrapper?.querySelector('.bpr-pause-overlay');
                            if (otherOverlay) {
                                otherOverlay.style.display = 'flex';
                            }
                        }
                    });

                    // Play this video (muted by default, respect global mute state)
                    video.muted = globalMuted;
                    video.play().catch(e => console.log('Autoplay failed:', e));
                    
                    if (pauseOverlay) {
                        pauseOverlay.style.display = 'none';
                    }

                } else {
                    // Pause when out of view and show pause overlay
                    video.pause();
                    if (pauseOverlay) {
                        pauseOverlay.style.display = 'flex';
                    }
                }
            });
        }, observerOptions);

        videos.forEach(video => {
            observer.observe(video);
            
            // Make sure videos start muted and remove any hover behavior
            video.muted = globalMuted;
            
            // Remove any existing hover listeners that might cause muting
            video.onmouseenter = null;
            video.onmouseleave = null;
            
            // Instagram-style click to pause/resume
            video.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const wrapper = this.closest('.bpr-video-wrapper');
                const pauseOverlay = wrapper.querySelector('.bpr-pause-overlay');
                
                if (this.paused) {
                    // Resume
                    this.play().catch(e => console.log('Play failed:', e));
                    if (pauseOverlay) {
                        pauseOverlay.style.display = 'none';
                    }
                    showInstagramIcon(wrapper, '▶️', 'play');
                } else {
                    // Pause
                    this.pause();
                    if (pauseOverlay) {
                        pauseOverlay.style.display = 'flex';
                    }
                    showInstagramIcon(wrapper, '⏸️', 'pause');
                }
            });
        });

        // Handle mute/unmute buttons
        window.initializeMuteButtons = function() {
            document.querySelectorAll('.bpr-mute-toggle').forEach(button => {
                if (button.hasAttribute('data-initialized')) return;
                button.setAttribute('data-initialized', 'true');
                
                // Set initial state with enhanced emoji rendering
                button.innerHTML = globalMuted ? '🔇' : '🔊';
                button.setAttribute('data-muted', globalMuted ? 'true' : 'false');
                
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const wrapper = this.closest('.bpr-video-wrapper');
                    const video = wrapper.querySelector('video');
                    
                    if (video) {
                        // Toggle global mute state
                        globalMuted = !globalMuted;
                        
                        // Update ALL videos to match global mute state (Instagram behavior)
                        videos.forEach(v => {
                            v.muted = globalMuted;
                        });
                        
                        // Update ALL mute buttons to show consistent state
                        document.querySelectorAll('.bpr-mute-toggle').forEach(btn => {
                            btn.innerHTML = globalMuted ? '🔇' : '🔊';
                            btn.setAttribute('data-muted', globalMuted ? 'true' : 'false');
                        });
                        
                        // Show temporary feedback
                        showInstagramIcon(wrapper, globalMuted ? '🔇' : '🔊', 'mute');
                    }
                });
            });
        }
        
        // Initialize mute buttons
        initializeMuteButtons();

        // Instagram-style temporary icon animation (enhanced)
        function showInstagramIcon(wrapper, icon, type) {
            // Remove any existing temporary icons
            const existing = wrapper.querySelector('.bpr-temp-icon');
            if (existing) {
                existing.remove();
            }
            
            // Create new temporary icon with Instagram styling
            const tempIcon = document.createElement('div');
            tempIcon.className = `bpr-temp-icon bpr-temp-${type}`;
            tempIcon.innerHTML = icon;
            
            // Enhanced Instagram-style positioning and styling
            const styles = {
                position: 'absolute',
                top: '50%',
                left: '50%',
                transform: 'translate(-50%, -50%)',
                fontSize: type === 'mute' ? '48px' : '80px',
                color: 'white',
                textShadow: '0 4px 20px rgba(0,0,0,0.8), 0 2px 8px rgba(0,0,0,0.9)',
                zIndex: '1000',
                pointerEvents: 'none',
                animation: 'bpr-instagram-icon 1s ease-out forwards',
                background: type !== 'mute' ? 'rgba(0,0,0,0.5)' : 'transparent',
                borderRadius: type !== 'mute' ? '50%' : '0',
                width: type !== 'mute' ? '120px' : 'auto',
                height: type !== 'mute' ? '120px' : 'auto',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                backdropFilter: type !== 'mute' ? 'blur(10px)' : 'none'
            };
            
            Object.assign(tempIcon.style, styles);
            wrapper.appendChild(tempIcon);
            
            // Remove after animation with Instagram timing
            setTimeout(() => {
                if (tempIcon.parentNode) {
                    tempIcon.remove();
                }
            }, 1000);
        }
    }

    // Grid functionality (keep existing hover behavior for previews)
    function initializeGridFunctionality() {
        // Grid hover effects and video preview (this is different from main video hover)
        document.querySelectorAll('.bpr-grid-item').forEach(item => {
            if (item.hasAttribute('data-initialized')) return;
            item.setAttribute('data-initialized', 'true');
            
            const video = item.querySelector('.bpr-grid-video');
            if (!video) return;
            
            let hoverTimeout;
            
            item.addEventListener('mouseenter', function() {
                clearTimeout(hoverTimeout);
                hoverTimeout = setTimeout(() => {
                    video.currentTime = 0;
                    video.muted = true; // Grid previews are always muted
                    video.play().catch(() => {});
                }, 300);
            });
            
            item.addEventListener('mouseleave', function() {
                clearTimeout(hoverTimeout);
                video.pause();
                video.currentTime = 0;
            });
            
            // Click to open full video (you can customize this)
            item.addEventListener('click', function() {
                // Add your modal logic here if needed
                console.log('Grid item clicked:', item.dataset.postId);
            });
        });
        
        // Load more grid functionality
        const loadMoreGridBtn = document.querySelector('.bpr-load-more-grid');
        if (loadMoreGridBtn && !loadMoreGridBtn.hasAttribute('data-initialized')) {
            loadMoreGridBtn.setAttribute('data-initialized', 'true');
            loadMoreGridBtn.addEventListener('click', bpr_load_more_grid_reels);
        }
    }

    // Load more functionality for profile feed
    const loadMoreBtn = document.querySelector('.bpr-load-more');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const currentPage = parseInt(this.dataset.page);
            const maxPages = parseInt(this.dataset.maxPages);
            const userId = this.dataset.userId;
            
            if (currentPage >= maxPages) return;
            
            this.disabled = true;
            this.textContent = 'Loading...';
            
            // Make AJAX request
            const formData = new FormData();
            formData.append('action', 'bpr_load_more_profile_reels');
            formData.append('page', currentPage + 1);
            formData.append('user_id', userId);
            formData.append('nonce', bprSettings.nonce);
            
            fetch(bprSettings.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Append new content
                    const container = document.querySelector('.bpr-feed-container');
                    container.insertAdjacentHTML('beforeend', data.data.html);
                    
                    // Re-initialize mute buttons for new content
                    initializeMuteButtons();
                    
                    // Update button
                    this.dataset.page = currentPage + 1;
                    this.disabled = false;
                    this.textContent = 'Load More Reels';
                    
                    // Hide button if no more pages
                    if (currentPage + 1 >= maxPages) {
                        this.style.display = 'none';
                    }
                    
                    // Re-initialize new videos
                    initializeVideoControls();
                } else {
                    this.disabled = false;
                    this.textContent = 'Load More Reels';
                    console.error('Failed to load more reels');
                }
            })
            .catch(error => {
                this.disabled = false;
                this.textContent = 'Load More Reels';
                console.error('Error loading more reels:', error);
            });
        });
    }

    // Grid load more functionality
    function bpr_load_more_grid_reels() {
        const button = this;
        const currentPage = parseInt(button.dataset.page);
        const maxPages = parseInt(button.dataset.maxPages);
        const userId = button.dataset.userId;
        
        if (currentPage >= maxPages) return;
        
        button.disabled = true;
        button.textContent = 'Loading...';
        
        const formData = new FormData();
        formData.append('action', 'bpr_load_more_grid_reels');
        formData.append('page', currentPage + 1);
        formData.append('user_id', userId);
        formData.append('posts_per_page', 12);
        formData.append('nonce', bprSettings.nonce);
        
        fetch(bprSettings.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const container = document.querySelector('.bpr-grid-wrapper');
                container.insertAdjacentHTML('beforeend', data.data.html);
                
                // Re-initialize mute buttons for new content
                initializeMuteButtons();
                
                button.dataset.page = currentPage + 1;
                button.disabled = false;
                button.textContent = 'Load More Reels';
                
                if (currentPage + 1 >= maxPages) {
                    button.style.display = 'none';
                }
                
                // Re-initialize new grid items
                initializeGridFunctionality();
            } else {
                button.disabled = false;
                button.textContent = 'Load More Reels';
            }
        })
        .catch(error => {
            button.disabled = false;
            button.textContent = 'Load More Reels';
            console.error('Error:', error);
        });
    }

    // Mark new grid items as initialized to prevent duplicate processing
    document.querySelectorAll('.bpr-grid-item').forEach(item => {
        item.setAttribute('data-initialized', 'true');
    });

    function initializeBuddyPressFunctionality() {
        // Handle BuddyPress like buttons
        document.querySelectorAll('.bpr-bp-like-btn').forEach(button => {
            if (button.hasAttribute('data-bp-initialized')) return;
            button.setAttribute('data-bp-initialized', 'true');
            
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const activityId = this.dataset.activityId;
                const isFavorited = this.dataset.favorited === 'true';
                
                if (!activityId) return;
                
                this.disabled = true;
                
                const formData = new FormData();
                formData.append('action', 'bpr_bp_toggle_favorite');
                formData.append('activity_id', activityId);
                formData.append('nonce', bprSettings.nonce);
                
                fetch(bprSettings.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update button state
                        this.dataset.favorited = data.data.favorited ? 'true' : 'false';
                        const icon = this.querySelector('.bpr-bp-icon');
                        const label = this.querySelector('.bpr-bp-label');
                        
                        if (data.data.favorited) {
                            icon.textContent = '❤️';
                            label.textContent = 'Unlike';
                        } else {
                            icon.textContent = '🤍';
                            label.textContent = 'Like';
                        }
                        
                        // Update grid stats if present
                        const gridItem = this.closest('.bpr-grid-item');
                        if (gridItem) {
                            const statElement = gridItem.querySelector('.bpr-grid-stats .bpr-grid-stat:first-child span:last-child');
                            if (statElement) {
                                statElement.textContent = data.data.count.toLocaleString();
                            }
                        }
                        
                        this.disabled = false;
                    } else {
                        console.error('Failed to toggle favorite:', data.data);
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error toggling favorite:', error);
                    this.disabled = false;
                });
            });
        });
        
        // Handle BuddyPress comment buttons
        document.querySelectorAll('.bpr-bp-comment-btn').forEach(button => {
            if (button.hasAttribute('data-bp-initialized')) return;
            button.setAttribute('data-bp-initialized', 'true');
            
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const activityId = this.dataset.activityId;
                if (!activityId) return;
                
                // Simple prompt for now - can be enhanced with a modal later
                const comment = prompt('Enter your comment:');
                if (!comment || comment.trim() === '') return;
                
                this.disabled = true;
                
                const formData = new FormData();
                formData.append('action', 'bpr_bp_add_comment');
                formData.append('activity_id', activityId);
                formData.append('comment_content', comment.trim());
                formData.append('nonce', bprSettings.nonce);
                
                fetch(bprSettings.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update grid stats if present
                        const gridItem = this.closest('.bpr-grid-item');
                        if (gridItem) {
                            const statElement = gridItem.querySelector('.bpr-grid-stats .bpr-grid-stat:nth-child(2) span:last-child');
                            if (statElement) {
                                statElement.textContent = data.data.comment_count.toLocaleString();
                            }
                        }
                        
                        alert(data.data.message);
                        this.disabled = false;
                    } else {
                        alert('Failed to add comment: ' + (data.data || 'Unknown error'));
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error adding comment:', error);
                    alert('Error adding comment');
                    this.disabled = false;
                });
            });
        });
    }
});

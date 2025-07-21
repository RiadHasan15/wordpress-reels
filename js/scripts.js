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
            formData.append('user_id', userId);
            formData.append('page', currentPage + 1);
            formData.append('posts_per_page', 10);
            formData.append('nonce', bpr_ajax.nonce);
            
            fetch(bpr_ajax.url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Append new reels to the feed container
                    const feedContainer = document.querySelector('.bpr-feed-container');
                    if (feedContainer) {
                        feedContainer.insertAdjacentHTML('beforeend', data.data.html);
                        
                        // Initialize video observers for new videos
                        const newVideos = feedContainer.querySelectorAll('.bpr-video:not([data-observer-added])');
                        newVideos.forEach(video => {
                            observer.observe(video);
                            video.setAttribute('data-observer-added', 'true');
                            
                            // Add click event for new videos
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
                        
                        // Initialize mute buttons for new videos
                        const newMuteButtons = feedContainer.querySelectorAll('.bpr-mute-toggle:not([data-listener-added])');
                        newMuteButtons.forEach(button => {
                            button.dataset.userMuted = 'false';
                            button.dataset.listenerAdded = 'true';
                            button.addEventListener('click', function () {
                                const video = this.closest('.bpr-video-wrapper').querySelector('video');
                                if (video) {
                                    video.muted = !video.muted;
                                    this.textContent = video.muted ? '🔇' : '🔊';
                                    this.dataset.userMuted = video.muted ? 'true' : 'false';
                                }
                            });
                        });
                    }
                    
                    this.dataset.page = currentPage + 1;
                    
                    if (!data.data.has_more) {
                        this.textContent = 'All reels loaded';
                        this.disabled = true;
                    } else {
                        this.textContent = 'Load More Reels';
                        this.disabled = false;
                    }
                } else {
                    this.textContent = 'Error loading reels';
                    setTimeout(() => {
                        this.textContent = 'Load More Reels';
                        this.disabled = false;
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.textContent = 'Error loading reels';
                setTimeout(() => {
                    this.textContent = 'Load More Reels';
                    this.disabled = false;
                }, 2000);
            });
        });
    }

    // Grid functionality
    initializeGridFunctionality();

    function initializeGridFunctionality() {
        // Grid hover effects and video preview
        document.querySelectorAll('.bpr-grid-item').forEach(item => {
            const video = item.querySelector('.bpr-grid-video');
            
            if (!video) return;
            
            let hoverTimeout;
            
            item.addEventListener('mouseenter', function() {
                hoverTimeout = setTimeout(() => {
                    if (video.paused) {
                        video.play().catch(() => {});
                    }
                }, 300); // Delay to prevent accidental triggers
            });
            
            item.addEventListener('mouseleave', function() {
                clearTimeout(hoverTimeout);
                if (!video.paused) {
                    video.pause();
                    video.currentTime = 0; // Reset to beginning
                }
            });
            
            // Click to open video in modal/overlay (optional - can be expanded)
            item.addEventListener('click', function() {
                const postId = this.dataset.postId;
                if (postId) {
                    // For now, just play/pause the video
                    // In the future, this could open a full-screen modal
                    if (video.paused) {
                        video.play().catch(() => {});
                    } else {
                        video.pause();
                    }
                }
            });
        });

        // Grid load more functionality
        const gridLoadMoreBtn = document.querySelector('.bpr-load-more-grid');
        if (gridLoadMoreBtn) {
            gridLoadMoreBtn.addEventListener('click', function() {
                const currentPage = parseInt(this.dataset.page);
                const maxPages = parseInt(this.dataset.maxPages);
                const userId = this.dataset.userId;
                
                if (currentPage >= maxPages) return;
                
                this.disabled = true;
                this.textContent = 'Loading...';
                
                // Make AJAX request
                const formData = new FormData();
                formData.append('action', 'bpr_load_more_grid_reels');
                formData.append('user_id', userId);
                formData.append('page', currentPage + 1);
                formData.append('posts_per_page', 12);
                formData.append('nonce', bprSettings.nonce);
                
                fetch(bprSettings.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Append new grid items
                        const gridWrapper = document.querySelector('.bpr-grid-wrapper');
                        if (gridWrapper) {
                            gridWrapper.insertAdjacentHTML('beforeend', data.data.html);
                            
                            // Initialize grid functionality for new items
                            const newItems = gridWrapper.querySelectorAll('.bpr-grid-item:not([data-initialized])');
                            newItems.forEach(item => {
                                item.setAttribute('data-initialized', 'true');
                                const video = item.querySelector('.bpr-grid-video');
                                
                                if (!video) return;
                                
                                let hoverTimeout;
                                
                                item.addEventListener('mouseenter', function() {
                                    hoverTimeout = setTimeout(() => {
                                        if (video.paused) {
                                            video.play().catch(() => {});
                                        }
                                    }, 300);
                                });
                                
                                item.addEventListener('mouseleave', function() {
                                    clearTimeout(hoverTimeout);
                                    if (!video.paused) {
                                        video.pause();
                                        video.currentTime = 0;
                                    }
                                });
                                
                                item.addEventListener('click', function() {
                                    if (video.paused) {
                                        video.play().catch(() => {});
                                    } else {
                                        video.pause();
                                    }
                                });
                            });
                        }
                        
                        this.dataset.page = currentPage + 1;
                        
                        if (!data.data.has_more) {
                            this.textContent = 'All reels loaded';
                            this.disabled = true;
                        } else {
                            this.textContent = 'Load More Reels';
                            this.disabled = false;
                        }
                    } else {
                        this.textContent = 'Error loading reels';
                        setTimeout(() => {
                            this.textContent = 'Load More Reels';
                            this.disabled = false;
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.textContent = 'Error loading reels';
                    setTimeout(() => {
                        this.textContent = 'Load More Reels';
                        this.disabled = false;
                    }, 2000);
                });
            });
        }
    }

    // Mark existing grid items as initialized
    document.querySelectorAll('.bpr-grid-item').forEach(item => {
        item.setAttribute('data-initialized', 'true');
    });
});

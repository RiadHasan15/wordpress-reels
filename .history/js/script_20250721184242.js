jQuery(document).ready(function ($) {
    let isMuted = true;

    const toggleMute = () => {
        $('.bpr-video').each(function () {
            this.muted = isMuted;
        });
        $('.bpr-sound-toggle').text(isMuted ? '🔇' : '🔊');
    };

    $('.bpr-sound-toggle').on('click', function () {
        isMuted = !isMuted;
        toggleMute();
    });

    const pauseOtherVideos = (current) => {
        $('.bpr-video').each(function () {
            if (this !== current) {
                this.pause();
            }
        });
    };

    const checkVisibility = () => {
        $('.bpr-video').each(function () {
            const rect = this.getBoundingClientRect();
            if (rect.top >= 0 && rect.bottom <= window.innerHeight) {
                if (!this.paused) return;
                this.play();
                if (isMuted) this.muted = true;
            } else {
                this.pause();
            }
        });
    };

    $(window).on('scroll', checkVisibility);
    checkVisibility();

    $('.bpr-video').on('click', function () {
        const wrapper = $(this).closest('.bpr-reel-frame');
        if (this.paused) {
            this.play();
            wrapper.addClass('resumed').removeClass('paused');
        } else {
            this.pause();
            wrapper.addClass('paused').removeClass('resumed');
        }
        setTimeout(() => wrapper.removeClass('paused resumed'), 1000);
    });
});

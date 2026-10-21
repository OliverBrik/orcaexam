document.addEventListener('DOMContentLoaded', function () {
    const videoLink = document.querySelector('[data-contact-video]');
    const videoContainer = document.getElementById('contact-video-player');
    if (videoLink && videoContainer) {
        videoLink.addEventListener('click', function (event) {
            event.preventDefault();
            const video = document.createElement('video');
            video.controls = true;
            video.playsInline = true;
            video.preload = 'none';
            video.setAttribute('aria-label', videoContainer.dataset.videoLabel);
            video.setAttribute('tabindex', '0');
            video.src = videoLink.href;
            videoContainer.appendChild(video);
            videoContainer.hidden = false;
            videoLink.setAttribute('aria-expanded', 'true');
            videoLink.hidden = true;
            video.focus();
            video.play().catch(function () {
                // Native controls remain available if automatic playback is blocked.
            });
        }, { once: true });
    }
    const tabs = document.querySelectorAll('[data-contact-tab]');
    const panels = document.querySelectorAll('.orca-contact__panel');
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabs.forEach(function (item) { const active = item === tab; item.classList.toggle('is-active', active); item.setAttribute('aria-selected', active ? 'true' : 'false'); });
            panels.forEach(function (panel) { panel.hidden = panel.id !== 'panel-' + tab.dataset.contactTab; });
        });
    });
});

document.addEventListener("DOMContentLoaded", (event) => {
    const wrapperYoutubeEl = document.querySelector('.wrapper-youtube');
    if (wrapperYoutubeEl) {
        center(wrapperYoutubeEl);
    }
    window.addEventListener('scroll', () => center(wrapperYoutubeEl));
});

const center = (wrapperYoutubeEl) => {
    const breakPoint = 1024;
    if (breakPoint < document.documentElement.clientWidth) {
        const iframeEl = wrapperYoutubeEl.querySelector('iframe.youtube');
        iframeEl.style.width = wrapperYoutubeEl.clientWidth - 40 + 'px';
        iframeEl.style.left = '20px';
        const rect = wrapperYoutubeEl.getBoundingClientRect();
        let scrollY = window.scrollY;
        let top = rect.top;
        if (top < 46) {
            top = 46;
        }
        iframeEl.style.top = (document.documentElement.clientHeight - top - iframeEl.clientHeight) / 2 + top +'px';
        iframeEl.style.opacity = 1;
    }
}
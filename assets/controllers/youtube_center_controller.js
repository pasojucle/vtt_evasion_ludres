import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["iframe"];

    connect() {
        this.onScroll = () => this.center();
        
        this.center();

        window.addEventListener('scroll', this.onScroll);
        window.addEventListener('resize', this.onScroll);
    }

    disconnect() {
        window.removeEventListener('scroll', this.onScroll);
        window.removeEventListener('resize', this.onScroll);
    }

    center() {
        const breakPoint = 1024;
        const iframe = this.iframeTarget;

        if (breakPoint < document.documentElement.clientWidth) {
            iframe.style.width = (this.element.clientWidth - 40) + 'px';
            iframe.style.left = '20px';

            const rect = this.element.getBoundingClientRect();
            let top = (rect.top < 46) ? 46 : rect.top;

            const vh = document.documentElement.clientHeight;
            iframe.style.top = (vh - top - iframe.clientHeight) / 2 + top + 'px';
            iframe.style.opacity = 1;
        } else {
            iframe.style.opacity = "";
            iframe.style.top = "";
            iframe.style.width = "";
        }
    }
}
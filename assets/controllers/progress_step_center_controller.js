import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
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
        const elementMarginTop = this.element.style.marginTop;
        const rect = this.element.getBoundingClientRect();
        const offsetTop = rect.top - 100;
        let marginTop = 0;
        if (window.scrollY >= offsetTop) {
            marginTop = window.scrollY - 200;
        } else {
            marginTop = (elementMarginTop) ? parseInt(elementMarginTop.replace('px', '')) : 0;
        }
        this.element.style.marginTop = marginTop + "px";
    }
}
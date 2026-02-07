import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        threshold: { type: Number, default: 0.1 }
    }
    connect() {
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                } else {
                    entry.target.classList.remove('visible');
                }
            });
        }, { 
            root: null,
            rootMargin: '0px',
            threshold: this.thresholdValue 
        });

        this.observer.observe(this.element);
    }

    disconnect() {
        this.observer.disconnect();
    }
}
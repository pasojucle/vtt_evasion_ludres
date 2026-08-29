import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['bar'];
    static values = {
        percentage: Number,
    };

    connect() {
        console.log("progress barre");
        this.initialize();
        this.animateHandler = this.animateFromTab.bind(this);
        document.addEventListener("progress-bar:animateFromTab", this.animateHandler);
    }

    disconnect() {
        document.removeEventListener("progress-bar:animateFromTab", this.animateHandler);
    }

    init() {
        if (!this.hasBarTarget) return;

        this.barTarget.style.width = '0%'
    }

    percentageValueChanged(value, previousValue) {
        if (previousValue === undefined) return;

        this.animate();
    }

    animate() {
        console.log("amine ***");

        if (!this.hasBarTarget) return;

        const percentage = this.barTarget.dataset.percentage;
        requestAnimationFrame(() => {
            this.barTarget.style.width = `${this.percentageValue}%`;
        });
    }

    animateFromTab(event) {
        const { wrapper, isActive } = event.detail;

        if (!wrapper.contains(this.element)) return;

        if (isActive) {
            this.animate();
        } else {
            this.init();
        }
    }
}
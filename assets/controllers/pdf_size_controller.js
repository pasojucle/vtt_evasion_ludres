import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["media"];

    static values = {
        ratio: Number,
    }

    connect() {
        this.resize();
    }

    resize() {
        const computedStyle = getComputedStyle(this.element);
        const width = this.element.clientWidth - parseFloat(computedStyle.paddingLeft) - parseFloat(computedStyle.paddingRight);
        this.mediaTarget.width = width;
        this.mediaTarget.height = this.ratioValue * width;
    }
}
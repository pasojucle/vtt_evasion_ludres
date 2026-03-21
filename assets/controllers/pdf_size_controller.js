import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["media"];

    static values = {
        ratio: Number,
    }

    connect() {
        this.resizeHandler = this.resizePostUpload.bind(this);
        document.addEventListener("pdf-size:resize-post-upload", this.resizeHandler);
        const wrapper = this.element;
        const media = this.mediaTarget;
        this.resize(wrapper, media);
    }

    disconnect() {
        document.removeEventListener("pdf-size:resize-post-upload", this.resizeHandler);
    }

    resizeOnConnect() {
        const wrapper = this.element;
        const media = this.mediaTarget;

        if (media.getAttribute('data').trim() !== "") {
            // const computedStyle = getComputedStyle(this.element);
            // const width = this.element.clientWidth - parseFloat(computedStyle.paddingLeft) - parseFloat(computedStyle.paddingRight);
            // objectElement.width = width;
            // objectElement.height = this.ratioValue * width;
        }       
    }
    resizePostUpload(event) {
        const wrapper = event.detail.wrapper;
        const media = event.detail.media;
        console.log("resiePostUload", wrapper, media)
        this.resize(wrapper, media);
    }

    resize(wrapper, media) {
        if (media.getAttribute('data').trim() !== "") {
            const computedStyle = getComputedStyle(wrapper);
            const width = this.element.clientWidth - parseFloat(computedStyle.paddingLeft) - parseFloat(computedStyle.paddingRight);
            media.width = width;
            media.height = this.ratioValue * width;
        }
    }
}